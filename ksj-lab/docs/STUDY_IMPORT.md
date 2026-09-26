# KSJ 프로젝트의 study 저장소 정리 기록

작성일: 2026-09-26

## 포함한 범위

기존 로컬 SQL Injection Lab의 현재 작업물을 `ksj-lab/`로 복사했습니다.
KSJ 실행본·원본, SQLi Level 0~15 및 필터 비교, SQL Observer, DB 복원 SQL,
Docker Compose, 실행/검사 스크립트, 예제와 수업 문서 전체를 포함합니다.
phpMyAdmin과 WebGoat/WebWolf의 선택 실행 설정도 포함합니다.
기존 로컬 프로젝트와 실행 중인 `sqli-lab` 컨테이너/볼륨은 수정하지 않았습니다.

- 원본 프로젝트 Git 커밋: `a05212e26ecd96686226fe5afd2786f25cadd255`
- 기존 study 업로드 커밋(정리 이전): `ee6da6f`
- 독립 Blind SQLi 워게임의 루트 경로와 실행 명령은 유지했습니다.

## 공개 복사본에서 정리한 내용

- 실제 `.env`, Docker 인증 설정, 실행 로그, 기존 Git 이력은 복사하지 않았습니다.
- 개인 강의 메모 `references/메모.txt`와 `source/*.tgz` 원본 압축은 제외했습니다.
  `original/sbadmin/`의 풀린 소스와 실행에 필요한 정적 리소스·라이선스는 포함했습니다.
  `SOURCE_HASHES.md`는 기존 압축 파일의 출처 확인 기록이며 저장소에 압축이 있다는 뜻은 아닙니다.
- 교육 메모에서 가져온 특정 이메일을 `learner@example.com`으로 바꿨습니다.
  예제 데이터·실습 화면·강사 문서의 목표 계정도 함께 맞췄습니다.
- Compose 프로젝트 이름을 `study-ksj`로 분리했습니다.
- 호스트 포트를 `.env`에서 변경할 수 있게 했고 Bash 실행 안내·자동 검사가 이 값을 사용합니다.
- PowerShell의 한글 SQL 입출력을 UTF-8로 명시하고 빌드·기동·SQL 실패 시 중단하도록 했습니다.
- 루트 README에 전체 구성을 추가하고 KSJ README에 최초 실행/재실행/초기화/포트 충돌을 설명했습니다.
- `.dockerignore`에 로컬 설정 및 불필요한 빌드 파일을 제외했습니다.
- 원본 `original/sbadmin/` 파일은 기존 로컬 보존본과 바이트 단위로 일치함을 확인했습니다.

## 기존 파일 대비 수정 목록

- `.env.example`
- `README.md`
- `apps/sqli-ladder/level1.php`
- `apps/sqli-ladder/level5.php`
- `db/init/002-seed.sql`
- `docker-compose.yml`
- `docs/INSPECTION_REPORT.md`
- `docs/INSTRUCTOR_GUIDE.md`
- `docs/PPT_CONTENT_MAP.md`
- `docs/STUDENT_GUIDE.md`
- `docs/TROUBLESHOOTING.md`
- `scripts/_common.ps1`
- `scripts/reset.ps1`
- `scripts/start.ps1`
- `scripts/start.sh`
- `scripts/stop.ps1`
- `scripts/test.ps1`
- `tests/run_tests.sh`

이외에 이 기록과 `ksj-lab/.dockerignore`를 새로 추가했고,
저장소 루트의 `README.md`, `.dockerignore`를 수정했습니다.

## 검증

별도 프로젝트 `study-ksj-verify`, 포트 18000/18002와 새 DB 볼륨으로 빌드·기동했습니다.
- Docker 이미지 새 빌드 및 DB 초기화·웹/Observer 기동 성공.
- 기존 자동 검사 **29/29 PASS, 0 FAIL**: 정상 로그인·게시판, SQLi, Prepared/최소권한 비교,
  SQL Observer, localhost 바인딩, 초기화 검증 포함.
- KSJ 시작 화면·실습 목록·로드맵·Level 0~15 페이지 HTTP 200 확인.
- 모든 선택 프로필을 포함한 Compose 설정 확인: localhost 포트, DB 호스트 포트 없음.
- README 내부 파일 링크와 Bash 스크립트 문법 확인.
- PowerShell 스크립트는 이번 Linux 환경에서 실행하지 못했습니다.
  선택 프로필 phpMyAdmin/WebGoat의 새 컨테이너 기동은 이번 검증에 포함하지 않았습니다.
- 검증용 컨테이너와 볼륨은 검사 후 제거했습니다. 기존 실습 서비스는 그대로 유지했습니다.

## 되돌리기

이전 상태는 Git 커밋 `ee6da6f`에 남아 있습니다.
업로드한 추가 커밋을 `git log --oneline -- ksj-lab`에서 찾은 뒤
`git revert <추가_커밋_SHA>`로 취소 커밋을 만들고 `git push`하면 됩니다.
이 방법은 기존 Git 이력을 보존합니다.
원본 로컬 KSJ 프로젝트에서 복사했기 때문에 해당 원본의 복원 작업은 필요하지 않습니다.

Docker의 새 실습 환경도 제거하려면 먼저 `ksj-lab/`에서 실행합니다.

```shell
docker compose --profile instructor --profile extra down
```

이 명령은 DB 볼륨을 보존합니다. 새 프로젝트의 실습 데이터도 버릴 때에만 `down -v`를 사용하세요.
기존 `sqli-lab`의 환경과 독립 Blind SQLi 컨테이너에는 영향을 주지 않습니다.
