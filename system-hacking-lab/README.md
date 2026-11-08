# 시스템 해킹 입문 실습: 함수 프롤로그·에필로그와 호출 규약

`basic_system.pdf` 39~49쪽의 `argument` 예제를 x86-64 Linux와 pwndbg에서 따라가는 수업용 Docker 실습이다. 실습 대상은 본 저장소에 포함된 교육용 프로그램 하나이며, 외부 시스템을 공격하지 않는다.

## 1. 준비와 실행

필수 프로그램은 Docker Desktop 또는 Docker Engine과 Docker Compose v2이다. Windows에서 WSL을 사용한다면 Docker Desktop의 WSL Integration도 켜야 한다.

```bash
cd system-hacking-lab
docker compose build
docker compose run --rm lab
```

컨테이너 안에서 현재 위치와 파일을 확인한다.

```bash
pwd
ls -l
./argument
```

예상 위치는 `/home/exp/exam/2.calling_convention`이고, 프로그램은 1부터 7까지의 인자를 출력한다.

> 이 실습은 교재의 `RDI`~`R9` 레지스터를 그대로 보기 위해 `linux/amd64`로 고정되어 있다. Apple Silicon에서는 Docker의 x86-64 에뮬레이션으로 실행되므로 시작이 조금 느릴 수 있다.

## 2. 수업 목표

수업을 마치면 학생은 다음을 설명할 수 있어야 한다.

- 함수 프롤로그가 이전 `RBP`를 보존하고 새 스택 프레임을 만드는 과정
- x86-64 System V ABI에서 정수형 인자 1~6이 전달되는 레지스터 순서
- 7번째 인자가 스택으로 전달되는 위치
- `call`이 복귀 주소를 스택에 저장하고 `ret`이 그 주소를 `RIP`로 가져오는 과정
- `leave; ret` 또는 동등한 명령열이 스택 프레임을 정리하는 과정

## 3. 교재 39~49쪽 따라가기

### 39~41쪽: 호출 규약과 프로그램 확인

64비트 Linux의 System V 호출 규약에서 정수형 인자 전달 순서는 다음과 같다.

| 인자 | 값 | 전달 위치 |
|---:|---:|---|
| 1 | 1 | `RDI` (`EDI`) |
| 2 | 2 | `RSI` (`ESI`) |
| 3 | 3 | `RDX` (`EDX`) |
| 4 | 4 | `RCX` (`ECX`) |
| 5 | 5 | `R8` (`R8D`) |
| 6 | 6 | `R9` (`R9D`) |
| 7 | 7 | 스택 |

소스와 바이너리를 확인한 다음 pwndbg를 시작한다.

```bash
sed -n '1,120p' argument.c
file argument
pwndbg ./argument
```

수업에서는 일반 GDB와 혼동하지 않도록 반드시 `pwndbg ./argument`로 실행한다.

### 42~44쪽: main의 큰 구조 찾기

```gdb
set disassembly-flavor intel
disassemble /r main
```

출력에서 다음 세 구간을 먼저 표시한다.

1. 프롤로그: `push rbp`, `mov rbp,rsp`, 필요하면 `sub rsp,...`
2. 호출 준비와 호출: `push 0x7`, `mov ...`, `call func1`
3. 에필로그: `leave`, `ret` 또는 `mov rsp,rbp; pop rbp; ret`

`endbr64`가 보이면 Intel CET용 간접 분기 보호 명령이다. 프롤로그 자체에는 포함하지 않는다.

### 45~49쪽: 한 명령씩 인자 전달 확인

이 빌드는 교재와 동일하게 `main+12`가 `push 0x7`이 되도록 고정했다. 먼저 반드시 자신의 디스어셈블 결과로 주소를 확인한다.

```gdb
break *main+12
run
```

현재 명령과 스택을 확인한다.

```gdb
x/i $pc
info registers rsp
x/6gx $rsp
```

`ni`를 한 번 실행하면 7이 스택에 놓인다.

```gdb
ni
x/i $pc
x/6gx $rsp
```

이후 `ni`를 반복하면서 레지스터가 채워지는 순서를 기록한다.

```gdb
ni
info registers rdi rsi rdx rcx r8 r9
```

`call func1` 위에 도착하면 `ni` 대신 `si`로 함수 안에 진입한다.

```gdb
x/i $pc
info registers rdi rsi rdx rcx r8 r9 rsp
x/4gx $rsp
si
x/i $pc
x/4gx $rsp
```

`si` 직후 스택의 맨 위인 `[$rsp]`에는 `main`으로 돌아갈 복귀 주소가 있고, 7번째 인자는 `[$rsp+8]`에 있다. `func1`의 프롤로그까지 실행한 뒤에는 `RSP`가 바뀌므로 주소를 기계적으로 외우지 말고 현재 명령과 스택 변화를 함께 추적한다.

## 4. func1 프롤로그와 main 에필로그 추적

`func1` 진입 직전에 중단하고 호출 시점의 상태를 본다.

```gdb
delete breakpoints
break *func1
run
info registers rdi rsi rdx rcx r8 r9 rsp rbp
x/4gx $rsp
disassemble /r func1
```

`break *func1`은 프롤로그보다 앞에서 멈춘다. 이 순간 GDB가 소스 인자명을 `first=0`처럼 잘못 표시할 수 있지만 아직 인자를 로컬 스택 위치에 저장하기 전이기 때문이다. 이 단계에서는 `info registers rdi rsi rdx rcx r8 r9`로 확인한 레지스터 값이 실제 전달값이다.

그다음 `si`와 아래 관찰 명령을 번갈아 실행한다.

```gdb
si
x/i $pc
info registers rsp rbp
x/8gx $rsp
```

`func1`이 반환하기 직전에는 `finish`로 호출자에게 돌아갈 수 있다. `main`의 에필로그는 별도 브레이크포인트로 관찰한다. 아래 주소의 오프셋은 `disassemble /r main`에서 자신의 `leave` 명령 위치로 바꾼다.

```gdb
finish
disassemble /r main
break *main+<leave의 오프셋>
continue
info registers rip rsp rbp
x/6gx $rsp
si
info registers rip rsp rbp
si
info registers rip rsp rbp
```

## 5. 빠른 시연과 점검

첫 화면과 디스어셈블을 자동으로 준비하려면 다음처럼 실행한다.

```bash
pwndbg -x gdb/auto.gdb ./argument
```

`func1` 진입 시 인자와 스택을 한 번에 확인하려면 다음을 사용한다.

```bash
pwndbg -x gdb/func1-entry.gdb ./argument
```

컴파일 결과가 수업 의도와 맞는지는 다음 명령으로 점검한다.

```bash
make check
```

## 6. 학생 활동지

다음 표를 직접 채운다.

| 관찰 시점 | `RSP` 변화 | `RBP` 변화 | 스택 최상단 값 | 설명 |
|---|---|---|---|---|
| `main` 진입 | | | | |
| `push rbp` 직후 | | | | |
| `push 0x7` 직후 | | | | |
| `call func1` 직후 | | | | |
| `func1`의 `mov rbp,rsp` 직후 | | | | |
| `leave` 직후 | | | | |
| `ret` 직후 | | | | |

확인 질문:

1. 32비트 레지스터 `EDI`에 값을 쓰면 64비트 `RDI`의 상위 32비트는 어떻게 되는가?
2. `call` 직전 스택 정렬을 위해 `sub rsp,8`이 필요한 이유는 무엇인가?
3. 7번째 인자의 스택 위치가 `func1` 진입 전후에 달라 보이는 이유는 무엇인가?
4. 호출자 `main`은 함수 반환 뒤 어떤 명령으로 스택 인자와 정렬 공간을 회수하는가?
5. `leave`는 어떤 두 명령과 같은 효과를 내는가?

## 7. 자주 생기는 문제

- `ptrace: Operation not permitted`: 반드시 `docker compose run --rm lab`으로 실행한다. Compose 파일에 `SYS_PTRACE`와 `seccomp:unconfined`가 들어 있다.
- `main+12`의 명령이 다름: `make clean all` 후 다시 확인한다. 다른 컴파일러나 플래그를 사용했다면 고정 오프셋 대신 디스어셈블에서 `push 0x7` 주소를 찾아 중단한다.
- 주소가 실행할 때마다 바뀜: PIE와 ASLR의 정상 동작이다. `main+12` 같은 심볼 상대 주소는 그대로 쓸 수 있다.
- 화면이 너무 자주 갱신됨: `context` 화면의 현재 명령, 레지스터, 스택 영역만 먼저 읽고 필요할 때 `set pagination off`를 사용한다.

종료는 pwndbg에서 `quit`, 컨테이너 셸에서 `exit`를 입력한다.

강사용 진행안과 정답은 [INSTRUCTOR.md](INSTRUCTOR.md)에 별도로 정리되어 있다.
