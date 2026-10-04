# Blind SQL Injection Advanced

웹해킹 기초 스터디를 위한 로컬 Blind SQL Injection 워게임입니다.
Flask 웹앱과 MariaDB를 Docker 컨테이너 하나에서 실행합니다.

## 필요한 프로그램

- Windows: **Docker Desktop**을 설치하고 실행합니다. Linux 컨테이너 모드를 사용합니다.
- Linux: Docker Engine을 설치하고 실행합니다.
- 저장소를 내려받으려면 Git이 필요합니다. Git 없이 GitHub의 **Code → Download ZIP**으로 받아 압축을 풀어도 됩니다.
- 첫 빌드에는 이미지를 내려받을 인터넷 연결이 필요합니다.

Docker 실행 확인:

```shell
docker version
```

Client와 Server 정보가 모두 나오면 준비된 상태입니다.

## 1. 파일 내려받기

```shell
git clone https://github.com/bernice777/study.git
cd study/blind-sqli
```

ZIP으로 받았다면 PowerShell/터미널에서 압축을 푼 폴더의 `blind-sqli` 하위 폴더로 이동합니다.
이후 명령은 **Dockerfile이 있는 폴더**에서 실행합니다.

## 2. 이미지 만들기 — 처음 한 번

```shell
docker build -t blind-sqli-advanced:local .
```

Dockerfile을 읽어 Python, Flask, MariaDB와 워게임 코드를 설치한 이미지를 만듭니다.
끝의 점(`.`)은 현재 폴더를 빌드에 사용한다는 뜻입니다.

## 3. 컨테이너 만들고 실행하기 — 처음 한 번

```shell
docker run -d --init --name blind-sqli-advanced-local -p 127.0.0.1:8010:5000 blind-sqli-advanced:local
```

- `-d`: 백그라운드 실행
- `--name`: 이후 켜고 끌 때 사용할 이름
- `127.0.0.1:8010:5000`: 내 PC의 8010번 포트를 컨테이너의 5000번 포트에 연결

DB가 준비되고 웹 서버가 실행될 때까지 잠시 기다린 뒤 접속합니다.

**http://127.0.0.1:8010/**

`uid`에 `guest`를 입력하면 존재 메시지가 표시됩니다. 존재하지 않는 이름은 메시지가 표시되지 않습니다.
응답 차이를 관찰하는 실습이며, 비밀번호를 입력하는 로그인 폼은 아닙니다.
`deploy/init.sql`의 관리자 값은 배포용 예시 값입니다. 온라인 워게임의 실제 정답을 제공하지 않습니다.

## 4. 다음에 다시 켜기 / 끄기

이미 만든 컨테이너에는 `run` 대신 `start`를 사용합니다.

```shell
# 다시 켜기
docker start blind-sqli-advanced-local

# 끄기 — 데이터는 유지
docker stop blind-sqli-advanced-local

# 상태 확인
docker ps -a

# 최근 실행 로그
docker logs --tail 50 blind-sqli-advanced-local
```

Docker Desktop을 종료했다면 다시 실행한 뒤 `docker start`를 입력합니다.

## 5. 실습 데이터 초기화

다음 명령은 **이 워게임 컨테이너의 데이터만 삭제**하고 새로 만듭니다.
별도 데이터 볼륨을 사용하지 않으므로 컨테이너 삭제 시 DB도 초기화됩니다.

```shell
docker rm -f blind-sqli-advanced-local
docker run -d --init --name blind-sqli-advanced-local -p 127.0.0.1:8010:5000 blind-sqli-advanced:local
```

코드나 Dockerfile을 수정했다면 먼저 `docker build -t blind-sqli-advanced:local .`을 다시 실행하고 컨테이너를 재생성합니다.

## 문제 해결

| 증상 | 확인 방법 |
|---|---|
| Docker daemon 연결 실패 | Docker Desktop/Engine이 실행 중인지 확인합니다. |
| 컨테이너 이름이 이미 사용 중 | `docker start blind-sqli-advanced-local`을 사용합니다. |
| 8010 포트가 사용 중 | 새 컨테이너를 만들 때 `127.0.0.1:8011:5000`으로 바꾸고 8011에 접속합니다. |
| 웹페이지가 열리지 않음 | `docker ps -a`와 `docker logs --tail 50 blind-sqli-advanced-local`을 확인합니다. |
| WSL에서 `docker-credential-desktop.exe: exec format error` | Windows PowerShell에서 Docker 명령을 실행합니다. |

## 구성과 원본 변경 범위

```text
Dockerfile          # 로컬 실행용 Python 3.10 / Debian bookworm 이미지
run-local.sh        # MariaDB 준비 대기, 최초 DB 초기화, Flask 실행
deploy/app.py       # 배포받은 워게임 코드
deploy/init.sql    # 초기 DB와 예시 계정
deploy/requirements.txt
deploy/run.sh      # 배포받은 기존 실행 스크립트 (현재 CMD에서는 사용하지 않음)
original/Dockerfile # 배포받은 기존 Dockerfile 보존본
```

배포받은 `deploy/` 내용은 변경하지 않았습니다. 원본 Dockerfile 대신 로컬 호환 Dockerfile을 기본으로 두고, 고정 시간 대기 대신 DB 준비 확인 후 앱을 시작하도록 했습니다.
원본에서 패키지 버전을 지정하지 않았으므로 새 빌드의 의존성 버전은 달라질 수 있습니다.

## 검증

같은 로컬 실행 구성으로 홈 HTTP 200, 존재/미존재 사용자, 참/거짓 조건에 따른 응답 차이를 확인했습니다.
이 저장소에서 새로 빌드한 이미지와 별도 임시 컨테이너에서도 홈 HTTP 200 및 위 4개 응답 검사를 통과했습니다. 검사 컨테이너는 종료·삭제했습니다.

## 사용 범위

의도적으로 SQL Injection 취약점이 있는 교육용 앱입니다. 로컬 실습에 사용하며 외부 공개 서버로 배포하지 않습니다.
웹 포트는 127.0.0.1에만 연결하고 MariaDB 포트는 호스트에 공개하지 않습니다.
DB 사용자와 비밀번호는 워게임용 고정 예시 값이며 개인 계정의 비밀번호가 아닙니다.

[전체 스터디 안내로 돌아가기](../README.md)
