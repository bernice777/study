# KSJ 복원 사이트 — SQL Injection Lab

K-Shield Jr 교육용 `sbadmin` PHP/MySQL 코드를 복원한 로컬 Docker 실습입니다.
로그인·회원가입·자유게시판·공지·자료실과 SQLi Level 0~15, SQL 실행 관찰 화면을 포함합니다.
원본 코드, 복원 근거, 강사·학생 문서도 함께 보관합니다.

수업 2·3차 사이트인 **XE 1.5.1.10 / KimsQ RB 1.2.1**도 선택 실행할 수 있습니다.
각각 PHP 5.6과 별도 DB를 사용합니다. [XE/RB 설치 안내](docs/CMS_SETUP.md)를 참고하세요.

## 준비

- Windows: Docker Desktop 실행, Linux 컨테이너 모드. PowerShell에서 진행합니다.
- Linux/WSL: Docker Engine 또는 Docker Desktop 연결, Compose v2, Bash, curl, `envsubst`가 필요합니다.
  Ubuntu에서는 `sudo apt install gettext-base curl`로 보조 도구를 설치합니다.
- `docker version`에 Client/Server가 모두 나오고 `docker compose version`이 실행되어야 합니다.
- 첫 빌드에는 인터넷 연결이 필요합니다. Git 없이 GitHub의 Code → Download ZIP을 사용해도 됩니다.
- 아래 명령은 모두 **`study/ksj-lab` 폴더 안에서** 실행합니다.

## 1. 처음 설치하고 켜기

Windows PowerShell:

```powershell
cd study/ksj-lab
Copy-Item .env.example .env
notepad .env
.\scripts\start.ps1
```

Linux / WSL:

```bash
cd study/ksj-lab
cp .env.example .env
# 편집기로 .env의 비밀번호와 토큰 값을 바꿉니다.
bash scripts/start.sh
```

이미 이 폴더에 들어와 있다면 `cd`는 생략합니다.
`.env`의 `change-me-...` 값들을 서로 다른 로컬 실습용 값으로 바꾸세요.
SQL 초기화 스크립트와 Bash/PowerShell 양쪽 호환을 위해 영문·숫자·하이픈으로 구성하면 편합니다.
`LAB_DB_NAME=sbadmin`은 유지합니다(복원 SQL에서 사용하는 고정 DB 이름).
개인 계정 비밀번호를 사용하지 마세요. `.env`는 Git에서 제외됩니다.

스크립트는 이미지 빌드 → DB 준비 → 스키마·예제 데이터 초기화 → 웹/Observer 기동 순서로 실행합니다.
**`start.sh` / `start.ps1`을 다시 실행해도 DB가 초기화됩니다.**
기존 데이터가 있으면 아래의 재시작 명령을 사용하세요.

PowerShell에서 스크립트 실행 정책으로 차단될 때는 해당 실행에 한해 다음을 사용합니다.

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\start.ps1
```

## 2. 접속과 정상 기능 확인

| 화면 | 기본 URL |
|---|---|
| 전체 실습 안내 | http://127.0.0.1:8000/ |
| **KSJ 복원 사이트** | **http://127.0.0.1:8000/ksj/** |
| SQLi Level 0~15 | http://127.0.0.1:8000/labs/ |
| 공격·방어 비교 순서 | http://127.0.0.1:8000/labs/roadmap.php |
| SQL Query Observer | http://127.0.0.1:8002/ |
| XE (`cms` 프로필, 첫 설치 필요) | http://127.0.0.1:8003/xe/ |
| KimsQ RB (`cms` 프로필, 첫 설치 필요) | http://127.0.0.1:8004/rb/ |

예제 로그인: `alice@example.com` / `alicepw`.
관리자 실습 계정: `admin@sbadmin.local` / `admin_pw_2024`.
모두 실습용 가짜 데이터이며 `db/init/002-seed.sql`에서 확인할 수 있습니다.

1. KSJ 사이트에서 로그인하거나 새 실습 계정을 가입합니다.
2. 자유게시판·공지·자료실이 열리는지 확인합니다.
3. SQLi Labs에서 학습할 레벨을 엽니다.
4. 응답의 `X-Lab-Trace-ID`를 Observer에서 찾아 SQL과 입력의 관계를 관찰합니다.
   Blind 실습에서는 학생 모드에 SQL이 숨겨집니다.

## 3. 다음에 켜기 / 끄기

데이터를 유지하면서 다시 켜기:

```shell
docker compose up -d
```

중지 / 상태 / 로그:

```shell
docker compose stop
docker compose ps
docker compose logs --tail 50 web db observer
```

코드·Docker 설정을 바꿨을 때는 `docker compose up -d --build`를 사용합니다.
기존 실행 스크립트 `scripts/stop.sh` / `scripts/stop.ps1`은 컨테이너를 제거하고 DB 볼륨은 보존합니다.

## 4. 실습 초기화와 검사

**초기화는 이 프로젝트의 실습 DB와 Observer 로그를 지웁니다.**

```bash
# Linux / WSL
bash scripts/reset.sh
bash scripts/test.sh
```

```powershell
# Windows PowerShell
.\scripts\reset.ps1
# 자동 검사는 Bash + curl + envsubst가 있는 WSL에서 실행 권장
```

자동 검사에는 회원가입, 게시판, SQLi, Prepared/최소권한 비교, Observer와 **DB 초기화 검증**이 포함됩니다.
검사가 끝나면 실습 데이터가 초기 상태로 돌아가므로 보존해야 할 데이터가 있는 환경에서 실행하지 마세요.
`test.ps1`도 Bash를 호출하므로 PowerShell만 설치된 PC에서는 자동 검사를 실행할 수 없습니다.

## 포트 충돌

다른 KSJ 환경이 켜져 있으면 `.env`의 포트를 바꿉니다.

```dotenv
WEB_PORT=18000
OBSERVER_PORT=18002
PMA_PORT=18001
WEBGOAT_PORT=18083
WEBWOLF_PORT=19090
XE_PORT=18003
RB_PORT=18004
```

그 뒤 `docker compose up -d`를 실행하고 KSJ는 `http://127.0.0.1:18000/ksj/`, Observer는 `http://127.0.0.1:18002/`로 접속합니다.
이 프로젝트 이름은 `study-ksj`입니다. 기존 `sqli-lab` 프로젝트와 DB 볼륨을 공유하지 않습니다.
동시에 여러 복사본을 켜려면 각 `.env`에 서로 다른 `COMPOSE_PROJECT_NAME`과 포트를 지정하세요.

## 선택 도구

XE / KimsQ RB:

```shell
docker compose --profile cms up -d --build xe rb
```

기존 sbadmin DB를 초기화하지 않습니다. 처음에는 각 사이트에서 설치를 진행합니다.
DB 호스트·계정 등은 [설치 안내](docs/CMS_SETUP.md)에 있습니다.
다음 실행에도 `--profile cms`를 지정하며 데이터는 유지됩니다.

강사용 phpMyAdmin:

```shell
docker compose --profile instructor up -d phpmyadmin
```

http://127.0.0.1:8001/ 에서 `.env`의 `LAB_INSTRUCTOR_USER` / `LAB_INSTRUCTOR_PASSWORD`로 로그인합니다.

추가 실습 WebGoat / WebWolf:

```shell
docker compose --profile extra up -d webgoat
```

- WebGoat: http://127.0.0.1:8083/WebGoat
- WebWolf: http://127.0.0.1:9090/WebWolf
- 첫 접속 시 실습용 계정을 직접 만듭니다. 기본 기동에는 포함되지 않습니다.

선택 도구까지 모두 중지: `docker compose --profile instructor --profile extra --profile cms stop`.
포트를 바꿨다면 위 접속 주소에도 변경한 값을 적용합니다.

## 수업과 복원 문서

| 문서 | 내용 |
|---|---|
| [학생 가이드](docs/STUDENT_GUIDE.md) | Burp, 요청 관찰, 보고서 양식 |
| [강사 가이드](docs/INSTRUCTOR_GUIDE.md) | 실습 진행과 설명 |
| [수업 흐름](docs/TEACHING_FLOW.md) | 학습 순서 |
| [실습 목록](docs/LAB_MATRIX.md) | Level별 입력·원리·방어 |
| [발표 내용 연결](docs/PPT_CONTENT_MAP.md) | 발표와 실습 대응 |
| [원본 분석](docs/INSPECTION_REPORT.md) | 사이트 구조와 DB 복원 근거 |
| [원본 변경점](docs/ORIGINAL_DIFF.md) | 계측과 실행본 변경 |
| [sqlmap 보고서 가이드](docs/SQLMAP_REPORT_GUIDE.md) | 로컬 실습 보고서 |
| [실습 범위](docs/SECURITY.md) | SQLI 모드·격리 설정 |
| [문제 해결](docs/TROUBLESHOOTING.md) | Docker·DB·포트 문제 |
| [저장소 정리 기록](docs/STUDY_IMPORT.md) | 가져온 범위·변경·검증·복원 |
| [XE/RB 설치 안내](docs/CMS_SETUP.md) | 두 CMS의 실행·설치·원본 변경·검증 상태 |

## 구성과 사용 범위

`web`: PHP 8.2/Apache, `db`: MariaDB 10.11, `observer`: SQL 기록 조회.
`original/sbadmin`은 보존용 원본이며 실제 서비스는 `apps/ksj-sbadmin`을 사용합니다.
DB 스키마는 원본 PHP와 교육 메모를 근거로 재구성했고 예제 데이터로 실행합니다.
개인 강의 메모와 원본 압축 파일은 저장소에 포함하지 않았습니다.
XE/RB는 옛 세션·설정을 제거한 설치용 압축본을 `docker/legacy/archives/`에 포함합니다.

교육용으로 SQL Injection과 평문 비밀번호 저장이 의도적으로 남아 있습니다.
`LAB_SCOPE=SQLI`에서는 임의 파일 다운로드·LFI 등을 제한합니다.
웹 포트는 localhost에만 열고 DB 포트는 호스트에 공개하지 않습니다. 공개 서버로 배포하지 마세요.
