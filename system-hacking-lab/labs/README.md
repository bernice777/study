# K-Shield Jr. 3일 시스템 해킹 실습

이 디렉터리는 기존 `system-hacking-lab` 이미지에 추가되는 오프라인 실습 묶음이다. 원본 KSJ 바이너리를 복제하지 않고 `memo.txt`에 남은 취약점·익스플로잇 흐름을 재현했다.

## 시작

호스트에서 기존 Compose를 그대로 사용한다.

```bash
cd system-hacking-lab
docker compose build
docker compose run --rm lab
```

컨테이너 안에서:

```bash
cd /home/exp/ksj-labs
make all
make list
```

각 문제는 해당 디렉터리에서 `make`, `./<binary>`, `pwndbg ./<binary>` 순서로 시작한다. `solve_reference.py`는 강사용 검증 풀이이므로 학생 배포본에서는 별도로 보관할 수 있다.

학생용 문제 목표와 문제별 GDB 관찰 지점은 [STUDENT_GUIDE.md](STUDENT_GUIDE.md)에 정리되어 있다.

## 3일 배치

| 일차 | 문제 | 핵심 |
|---:|---|---|
| 1 | 기존 `/home/exp/exam/2.calling_convention/argument` | 프롤로그, 에필로그, 호출 규약 |
| 1 | `day1/00-foundations`의 4개 바이너리 | 함수 호출, Integer Overflow, OOB, Off-by-One |
| 1 | `day1/01-fsb-read/01` (`fsb32_read` 별칭) | 32-bit FSB를 이용한 전역 문자열 유출 |
| 1 | `day1/02-fsb-write/02` (`fsb32_write` 별칭) | `%7$n`을 이용해 `changeMe=327` 쓰기 |
| 1 | `day1/03-bof-ret2win/bof_ret2win` | saved RIP를 `win`으로 변경 |
| 1 | `day1/04-stack-shellcode` | NX OFF 성공과 NX ON 차단 비교 |
| 1 | `day1/05-fake-rbp/fake_rbp` | `buf-8` fake RBP로 `system("/bin/sh")` 민감 함수 실행 |
| 1 | `day3/01-canary-leak/canary_leak` | Canary 7바이트 leak 후 2차 BOF |
| 2 | `day2/00-heap-basics`의 4개 바이너리 | 구조체 정렬, 잔존 데이터, Double Free, Type Confusion |
| 2 | `day2/01-heap-overflow/heap_overflow` | 인접 객체의 포인터를 덮어 AAW 구성 |
| 2 | `day2/02-use-after-free/use_after_free` | dangling pointer와 동일 크기 타입 혼동 |
| 3 | `day3/00-protections` | ASLR, PIE OFF/ON, PLT/GOT Lazy Binding |
| 3 | `day3/02-got-overwrite/got_overwrite` | partial RELRO와 `puts@GOT` 변조 |
| 3 | `day3/02-got-overwrite/got_overwrite_full` | Full RELRO에서 같은 쓰기 차단 |
| 3 | `day3/03-rop-ret2libc/rop_ret2libc` | GOT leak, libc base, 2-stage ROP |

## 전체 검증

```bash
cd /home/exp/exam/2.calling_convention
make check

cd /home/exp/ksj-labs
make verify
```

`make verify`는 각 바이너리를 다시 컴파일하고, 강사용 pwntools 풀이로 성공 문자열을 확인하고, 일반 GDB가 실제로 실행해 `main`에서 중단하는지 검사한다. 성공 문자열을 얻지 못한 문제는 PASS로 처리하지 않는다.

이미지에는 Python 3, pwntools, GDB, pwndbg가 기본 설치된다. FSB 슬라이드의 기존 원라이너를 그대로 재현하기 위한 호환 런타임으로 Python 2.7.18도 포함하지만, 새 풀이 스크립트는 Python 3과 pwntools를 사용한다.

## 학생용 GDB 공통 절차

```bash
pwndbg ./<binary>
checksec
break main
run
disassemble /r main
```

취약 함수에서는 `info registers`, `x/32gx $rsp`, `x/32gx <heap-address>`, `heap`, `got`을 문제에 맞게 사용한다. 주소나 오프셋은 기준 풀이에서 복사하지 말고 자신의 바이너리에서 `p &symbol`, `cyclic`, `cyclic_find`, `disassemble`로 다시 확인한다.
