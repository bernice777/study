# TROUBLESHOOTING

## Windows Docker는 되는데 WSL에서 연결 실패
증상: `/var/run/docker.sock`이 없거나 `/usr/bin/docker: Input/output error`가 발생합니다.
Windows의 `docker.exe version`은 Server까지 나오는데 WSL의 `docker version`만 실패하면
Docker Desktop → Settings → Resources → WSL Integration에서 사용하는 Ubuntu 배포판을 켜고
Apply & Restart를 적용하세요. 재시작은 실행 중인 컨테이너에 영향을 줄 수 있습니다.
복구 후 WSL에서 `docker version`의 Client/Server와 `docker ps`를 확인합니다.

## Docker Desktop / WSL 자격증명 오류 (pull 실패)
증상: `error getting credentials ... docker-credential-desktop.exe: exec format error`.
원인: WSL 에서 Windows 자격증명 헬퍼 실행 실패(`~/.docker/config.json` 의 `credsStore: desktop.exe`).
해결: 스크립트가 자동으로 프로젝트-로컬 빈 `DOCKER_CONFIG`(`.docker/`)를 사용하도록 폴백합니다(공개 이미지 전용, 전역 설정 미변경).
수동으로는 `export DOCKER_CONFIG="$PWD/.docker"` 후 실행.

## web 컨테이너가 healthy 가 안 됨
- `/health` 200 확인: `curl -s http://127.0.0.1:8000/health`.
- DB 가 먼저 healthy 여야 함. `docker compose ps` 로 상태 확인. `start.sh` 는 DB→SQL→web 순.

## DB 접속 실패 (web 로그)
- `.env` 의 `LAB_APP_*` 와 003 에서 만든 계정이 일치해야 함. `reset.sh` 로 계정 재생성.

## 포트 충돌 (8000/8002)
- 이미 사용 중이면 `.env`의 `WEB_PORT` / `OBSERVER_PORT` 값을 변경하고 `docker compose up -d web observer`를 실행.

## Time-based(L8) 가 즉시 응답
- `.env` 의 `LAB_TIME_MAX_SECONDS`(기본 2) 확인. 동시요청 2 슬롯 초과 시 "busy" → 순차 실행.

## Error-based(L7) 페이로드가 안 먹힘
- `extractvalue/updatexml` 는 MySQL/MariaDB 계열 함수. 다른 DBMS 에서는 동작하지 않음(호환성 안내 참고).

## 초기화하고 싶다
- `./scripts/reset.sh` (스키마+시드+AUTO_INCREMENT+로그 초기화). 다른 Docker 프로젝트/볼륨은 건드리지 않음.

## 완전 제거
- `docker compose --profile instructor --profile extra --profile cms down -v`는
  **이 프로젝트의 모든 볼륨**을 삭제합니다(sbadmin DB, 로그, XE/RB DB와 사이트 파일 포함).
  단순 중지에는 `-v`를 사용하지 마세요. 다른 프로젝트 볼륨은 삭제하지 않습니다.
