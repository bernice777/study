# Basic UAF Type Confusion

64비트 Linux의 힙 객체 수명 오류를 다루는 입문용 pwnable이다. 의도된 순서는 `A 생성 → A 해제 → B 생성/입력 → dangling A 권한 검사`이다. A 해제 후 상태 변수가 두 번째 `free()`를 차단하지만, 취약점 학습을 위해 포인터 자체는 NULL로 만들지 않는다.

## 학습 목표

Use-After-Free, dangling pointer, 동일 크기 청크 재사용, 서로 다른 구조체의 메모리 중첩과 타입 혼동, 객체 수명 오류에 의한 권한 우회를 학습한다.

## 빌드와 실행

필요 패키지는 GCC, GNU Make, Python 3, pwntools이며 디버깅에는 GDB가 필요하다.

```bash
make clean all
./challenge
python3 exploit.py
gdb -q ./challenge -x debug.gdb
```

Docker 서비스:

```bash
docker compose up --build -d
python3 exploit.py REMOTE HOST=127.0.0.1 PORT=31337
docker compose down
```

참가자 배포본은 `make dist`로 만든다. 실제 flag, exploit, write-up, GDB 스크립트는 `dist/`에 포함되지 않는다. 배포용 Dockerfile은 빈 flag 파일만 만들며 운영 시 별도 secret/volume으로 flag를 주입한다.

## 입력 설계

Memo B는 제목(최대 31바이트)과 두 개의 64비트 정수를 각각 입력받는다. 숫자 필드 방식을 택해 정확한 오프셋을 유지하면서 구조체 밖 쓰기와 부분 입력의 불안정성을 피했다. 구체적인 정답 payload는 README에 공개하지 않으며 소스/바이너리 분석 또는 WRITEUP에서 확인할 수 있다.

## 파일 구조

```text
src/challenge.c    문제 소스
exploit.py         로컬/원격 풀이
debug.gdb          재현 가능한 GDB 검증
gdb-input.txt      GDB 자동 입력
Dockerfile         서비스 이미지
Dockerfile.dist    flag를 제외한 참가자 배포 이미지
docker-compose.yml 서비스 구성
flag               로컬 검증용 더미 flag
dist/              참가자용 배포본
WRITEUP.md         상세 풀이
```

## 출제 의도와 분류

주 취약점은 UAF다. `free(g_session)` 뒤 `g_session = NULL`을 하지 않아 참조가 객체 수명보다 오래 남고, Memo가 그 청크를 재사용하면 dangling Session 포인터가 Memo 데이터를 인증 상태로 오해한다.

Double Free와는 다르다. Session 청크에 대한 `free()`는 정확히 한 번뿐이고 두 번째 요청은 상태 변수로 거절된다. exploit도 allocator 메타데이터를 수정하거나 같은 포인터를 두 번 해제하지 않는다.

PIE 우회, 주소 유출, 함수 포인터, GOT overwrite, ROP, 셸 코드, tcache poisoning은 필요 없다. 실행 흐름을 탈취하지 않고 겹친 객체의 논리적 상태만 바꾸므로 NX, Canary, RELRO 여부도 풀이 요소가 아니다.
