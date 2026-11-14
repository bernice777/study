# Write-up: Basic UAF Type Confusion

## 구조체 레이아웃

두 타입은 모두 48바이트이며 `_Static_assert`가 크기와 중요 오프셋의 동일성을 컴파일 때 검증한다. glibc의 64비트 정렬에서 48바이트 요청은 헤더를 포함한 `0x40` 크기 청크가 되어 두 객체가 같은 `0x40` tcache bin을 사용한다(사용자 영역에서 관찰되는 usable size는 구현상 56바이트일 수 있다).

| 오프셋 | Session A | Memo B | 크기 |
|---:|---|---|---:|
| 0x00 | `username` | `title` | 32 |
| 0x20 | `authenticated` | `category` | 8 |
| 0x28 | `access_level` | `value` | 8 |

glibc의 64비트 malloc에서는 48바이트 요청에 16바이트 헤더와 16바이트 정렬이 적용되어 청크 크기 필드는 통상 0x41(실제 크기 0x40과 플래그)이다. 따라서 두 타입은 같은 tcache 크기 클래스에 들어간다.

## UAF와 청크 재사용

`destroy_session()`은 `free(g_session)`를 한 번 호출하고 `g_session_freed`를 설정해 재해제를 막는다. 그러나 `g_session`을 NULL로 만들지 않아 해제된 주소가 남는다. 이것이 dangling pointer다.

그 뒤 다른 중간 힙 할당 없이 48바이트 Memo를 요청한다. 같은 크기 tcache bin에서 방금 반환된 청크가 LIFO 방식으로 우선 반환되어 `g_memo == (Memo *)g_session`이 된다. 권한 검사는 수명이 끝난 Session 포인터를 역참조해 활성 Memo의 offset 0x20과 0x28을 인증/권한 필드로 읽는다.

## Payload와 전체 순서

소스에 공개된 조건은 다음과 같다.

```text
AUTH_MAGIC  = 0x41555448454e5449 (4707761654862861385)
ADMIN_MAGIC = 0x41444d494e5f4f4b (4702968888124460875)
```

1. 메뉴 1로 Session A를 생성한다.
2. 메뉴 3으로 A를 정확히 한 번 해제한다.
3. 메뉴 4로 Memo B를 할당한다.
4. `category`에 AUTH_MAGIC, `value`에 ADMIN_MAGIC을 넣는다.
5. 메뉴 5에서 dangling A를 검사한다.
6. 두 조건이 참이 되어 `print_flag()`가 `FLAG_PATH`, 또는 기본 `./flag`를 읽는다.

`exploit.py`는 주소를 읽거나 계산하지 않으며 함수 포인터, GOT, ROP, 셸 코드, hook, Double Free, tcache poisoning을 사용하지 않는다.

## GDB 검증

```bash
gdb -q ./challenge -x debug.gdb
```

스크립트는 A 주소와 48바이트 크기, 0x20/0x28 오프셋, A 포인터에 대한 한 번의 `free`, 해제 후 dangling 포인터, B 주소와 동일성, B 입력 전후 6개 qword, 겹친 필드 값, 권한 조건과 flag 호출 직전 상태를 출력한다. ASLR 때문에 실제 주소는 실행마다 바뀌며 exploit은 주소를 사용하지 않는다.

2026-11-14 Docker 기반 GNU GDB 검증의 한 실행에서는 A가 `0x4052a0`에 할당되었고, `free(0x4052a0)`가 한 번 관찰된 뒤 dangling 포인터도 `0x4052a0`이었다. B 역시 `0x4052a0`을 반환받아 `same=1`이었다. 입력 후 offset 0x20/0x28에는 각각 `0x41555448454e5449`, `0x41444d494e5f4f4b`가 보였고, dangling A도 같은 값을 읽었다. 이어 `debug_before_flag` 중단점과 `FLAG: DH{basic_uaf_type_confusion}` 출력을 확인했다.

## 왜 UAF이고 Double Free가 아닌가

핵심은 해제된 A를 다시 읽는 동작이다. 상태 변수 때문에 같은 A 포인터의 두 번째 `free()`는 호출되지 않는다. B는 정상 `malloc()`으로 할당되고 정상 필드 범위만 쓰며 tcache 연결 포인터도 조작하지 않는다. 따라서 이는 UAF에 따른 타입 혼동 및 권한 우회다.

코드 포인터는 바뀌지 않고 데이터 필드 두 개만 다른 의미로 해석되므로 제어 흐름 탈취나 보호기법 우회가 필요 없다.

## 안전한 수정

- `free()` 직후 `g_session = NULL`로 초기화한다.
- 해제 상태에서는 모든 표시/검사 경로가 객체를 역참조하지 않게 한다.
- 객체 소유권과 수명을 한 곳에서 관리하고 해제 함수가 관련 참조를 모두 제거한다.
- 타입별 객체 관리 경로를 분리한다.
- 가능하면 동일 포인터를 여러 기능이 공유하지 않게 설계한다.
