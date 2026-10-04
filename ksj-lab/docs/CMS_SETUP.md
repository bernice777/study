# XE · KimsQ RB 실습 사이트

수업 원본 `xe.tgz`(XE **1.5.1.10**)와 `rb.tgz`(KimsQ RB **1.2.1**)를 실행합니다.
sbadmin과 함께 사용하되 PHP·DB·데이터 볼륨은 분리합니다. 원본에 DB 덤프가 없어
수업 당시 게시글·계정을 복원하지는 않습니다. **최초 1회 브라우저 설치가 필요합니다.**

## 실행

Docker 엔진을 실행하고 `study/ksj-lab`로 이동합니다.
기존 `.env`가 있으면 그대로 사용하세요. 없으면 `.env.example`을 복사하고 값을 바꿉니다.
기존 sbadmin의 `start.sh`는 DB를 초기화하므로 XE/RB 추가에는 실행하지 않습니다.

```shell
docker compose --profile cms up -d --build xe rb
```

같은 작업을 Linux/WSL에서는 `bash scripts/start-cms.sh`, Windows PowerShell에서는
`.\scripts\start-cms.ps1`로 실행할 수 있습니다. 첫 빌드는 인터넷 연결이 필요합니다.
기존 sbadmin도 켜려면 `docker compose up -d web observer`를 사용하세요.
안내 화면의 새 링크를 적용하려면 `docker compose up -d --build web`을 실행합니다.

| 사이트 | 접속 주소 | DB 호스트 | DB 이름 | DB 사용자 | 접두사 |
|---|---|---|---|---|---|
| XE | http://127.0.0.1:8003/xe/ | `xe-db` | `xe_db` | `xe` | `xe` (최종 테이블은 `xe_…`) |
| KimsQ RB | http://127.0.0.1:8004/rb/ | `rb-db` | `kimsq` | `rb` | `rb` |

두 DB 모두 포트는 `3306`, 비밀번호는 **자신의 `.env`에 있는 `LAB_APP_PASSWORD` 값**입니다.
`localhost`나 옛 원본의 `root/1234`를 입력하지 마세요.
CMS 관리자 계정은 설치 화면에서 새로 만드는 계정으로, DB 계정과 다릅니다.

## XE 첫 설치

1. `/xe/`에 접속해 한국어와 라이선스 동의를 선택합니다. 환경정보 전송은 선택하지 않습니다.
2. 환경 검사 후 MySQL을 선택하고 위 표의 DB 정보를 입력합니다.
3. 테이블 접두사는 기본값 `xe`를 유지합니다. Rewrite는 사용하지 않고, 시간대는 한국을 선택합니다.
4. 실습용 관리자 이메일·아이디·비밀번호를 만들어 설치를 완료합니다.
5. 관리자 페이지 `/xe/index.php?module=admin`과 회원가입 화면을 확인합니다.
   게시판이 필요하면 관리자에서 포함된 게시판 모듈로 새 게시판을 만듭니다.

## RB 첫 설치

1. `/rb/`에 접속해 설치를 시작합니다.
2. 위 표의 DB 정보와 접두사 `rb`, DB 종류 `MyISAM`을 사용합니다.
3. 사이트명과 실습용 관리자 계정을 정하고 설치를 완료합니다.
4. `/rb/?m=admin`에서 로그인하고 게시판을 생성합니다.
5. `/rb/?m=member&front=join`에서 회원가입 동작을 확인합니다.

## 재시작 · 중지 · 확인

```shell
# 설치 정보와 게시글을 유지하면서 다시 켜기
docker compose --profile cms up -d xe rb
# CMS만 중지
docker compose --profile cms stop xe rb xe-db rb-db
# 상태와 오류 확인
docker compose --profile cms ps
docker compose --profile cms logs --tail 80 xe rb xe-db rb-db
```

기본 `docker compose up -d`는 `cms` 프로필을 시작하지 않습니다.
`scripts/stop.sh` / `stop.ps1`은 CMS를 포함한 전체 실습 컨테이너를 내리고 볼륨은 보존합니다.
기존 `scripts/reset.sh`와 `reset.ps1`은 sbadmin/SQLi 전용이며 CMS를 초기화하지 않습니다.
`down -v`는 다른 실습 DB까지 지울 수 있으므로 단순 재시작에 사용하지 마세요.

WSL에서 비파괴 실행 검사:

```bash
bash scripts/test-cms.sh
# 두 사이트 설치를 마친 뒤 DB 테이블과 관리자 생성까지 검사
bash scripts/test-cms.sh --installed
```

`.env`의 `XE_PORT=8003`, `RB_PORT=8004`를 바꾸면 충돌을 피할 수 있습니다.
XE 설치 후 포트를 바꾼 경우 XE 관리자 기본 URL도 변경해야 합니다.
DB 비밀번호는 첫 DB 볼륨 생성 때 적용됩니다. 이후 `.env`만 바꾸면 DB 계정과 CMS 설정은
자동으로 변경되지 않습니다.

## 원본과 실행본 차이

- 출처: Google Drive `K-Shield 주니어/취약점 분석 본트랙/웹해킹`.
  [XE 원본](https://drive.google.com/file/d/1BO5byIZr73Ca2l0RHSOMrk_XI3AyyFE4/view),
  [RB 원본](https://drive.google.com/file/d/17Le0E5qjOfdSr0gPmDu1hBGfvLDsDRnw/view).
- 원본 SHA-256은 [SOURCE_HASHES.md](SOURCE_HASHES.md)에 기록된 값과 일치합니다.
- 실행용 압축본은 `docker/legacy/archives/`에 포함하므로 클론 후 Drive 다운로드는 필요 없습니다.
  원본에서 재생성하려면 `python3 scripts/prepare-cms-sources.py <원본 압축본 폴더>`를 실행합니다.
  Python은 일반적인 Docker 실행에는 필요 없습니다.
- XE `files/`의 옛 설정·캐시·사용자 파일과 RB 세션·캐시·DB 설정·사용자 파일을 제거했습니다.
  디렉터리 구조와 라이선스 파일은 보존합니다.
- RB의 `db.table.php.done`을 `db.table.php`로 복구해 설치기가 스키마를 생성할 수 있게 했습니다.
  기본 페이지의 과거 외부 iframe은 로컬 실습 안내로 바꿨습니다.
- PHP 5.6.40의 `mysql`, `mysqli`, `mbstring`, `gd`를 사용합니다.
  Debian 보관 저장소의 패키지 서명 검증을 위해 Bookworm 이미지의 키링을 가져옵니다.
  DB는 MariaDB 10.11이며 옛 스키마의 기본값과 호환되도록 해당 DB만 strict mode를 끕니다.
- 웹 포트는 `127.0.0.1`에만 공개하며, 각 CMS는 별도 웹 네트워크와 내부 DB 네트워크를 사용합니다.
  웹 컨테이너는 외부 통신이 가능한 bridge에 연결됩니다. 내부 네트워크만 연결하면
  Docker Engine 29에서는 웹 포트가 공개되지 않아 브라우저로 접속할 수 없습니다.
  DB 포트는 공개하지 않고 CMS 계정에는 각자의 DB 권한만 부여합니다.
- 원본 CMS의 취약점은 유지합니다. sbadmin의 SQLI 범위 제한과 Observer 계측은 적용되지 않습니다.
  업로드 폴더의 PHP 실행은 차단합니다. PHP 캐시·설치 설정은 각 사이트 볼륨에 저장합니다.
- 두 CMS의 PHP 세션 쿠키 이름을 분리했습니다. 원본 앱이 직접 설정하는 쿠키도 있으므로
  동시에 로그인하며 비교할 때는 별도 브라우저 프로필 사용을 권장합니다.

## 검증 상태

2026-10-04: Docker Desktop의 Ubuntu WSL 연동을 복구한 뒤 실제 이미지 빌드와
XE/RB 컨테이너 기동을 완료했습니다. 두 DB의 healthy 상태, 필요한 PHP 확장,
Windows와 WSL 양쪽의 설치 화면 HTTP 200을 확인했고 `scripts/test-cms.sh`도 통과했습니다.
Compose 구성, 셸 문법, 원본 해시, 설치용 압축본의 상태 제거와 RB 설치 스키마 복구도 검사했습니다.
아직 CMS 설치 마법사를 완료하지 않았으므로 로그인·회원가입과 `--installed` 검사는 남아 있습니다.
