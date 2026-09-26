# SECURITY — 격리·안전 설계

## 로컬 격리
- 모든 노출 포트는 `127.0.0.1` 바인딩: web 8000, phpMyAdmin 8001(강사 profile), Observer 8002.
- **DB 포트 미공개**(compose 에 ports 없음). 컨테이너 네트워크 내부에서만 접근.
- 외부 터널(ngrok 등)·`0.0.0.0` 공개·Docker Socket 마운트·호스트 루트/홈 마운트·privileged **미사용**.
- `security_opt: no-new-privileges:true` 전 서비스 적용.
- 앱 코드는 web 컨테이너에 **읽기전용(:ro)** 마운트. 호스트 임의 파일은 보이지 않음.

## DB 계정/권한 (최소권한)
| 계정 | 권한 | 용도 |
|---|---|---|
| `lab_app` | `SELECT,INSERT,UPDATE,EXECUTE` on `sbadmin.*` | 웹 앱 (FILE/SUPER/CREATE USER 없음) |
| `lab_limited` | 지정 View 만 `SELECT` | 최소권한 비교(Level 15) |
| `lab_observer` | View `SELECT` (읽기 전용) | 로그/조회 역할 |
| `lab_instructor` | `ALL` on `sbadmin.*` | 강사 관리(phpMyAdmin) |
| `root` | 전역 | **DB 초기화 전용, 웹 앱 사용 금지** |

- 비밀값은 `.env`(git 제외)에서만. init SQL 은 `envsubst` 로 주입 → **하드코딩 없음**.
- `INSTRUCTOR_TOKEN` 도 `.env`. Observer 강사 모드는 `hash_equals` 비교.

## SQLI 범위 제어 (`LAB_SCOPE=SQLI`)
이번 수업 범위 밖 기능은 차단(코드는 `original/` 에 보존, 다른 수업에서 재활성 가능):
- LFI/임의 파일 include (`index.php ?p=` 화이트리스트)
- 임의 파일 다운로드/읽기 (`act/download.php` 403)
- 파일 업로드/웹셸 (`act/write.php` 업로드 차단 + `uploads/` PHP 실행 차단)
- `INTO OUTFILE`/`load_file` → `lab_app` 에 `FILE` 미부여로 원천 차단

## 관찰(Observer)
- SQL 실행 기록은 **웹루트 밖**(`/var/lab-logs/observer.jsonl`). Observer 는 읽기 전용 마운트.
- 비밀번호/세션 쿠키는 로그에서 마스킹. 출력은 전부 escape(Observer 자체 XSS 방지).
- Blind 레벨(6/8)은 학생 모드에서 최종 SQL·DB결과 숨김.

## 파괴적 명령 미사용
- `reset` 은 이 프로젝트 스키마만 재적용(idempotent SQL). 볼륨 삭제·`docker system prune`·광범위 `rm -rf` 미사용.
