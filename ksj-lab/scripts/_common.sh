#!/usr/bin/env bash
# 공통 함수. 프로젝트 루트로 이동 + SQL 적용(envsubst) 헬퍼.
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"
DC="docker compose"

# WSL+Docker Desktop 의 credsStore(desktop.exe) 가 공개 이미지 pull 까지 깨는 경우 대비:
# 사용자가 DOCKER_CONFIG 를 지정하지 않았고 해당 설정이 감지되면, 프로젝트-로컬 빈 config 사용.
# (전역 ~/.docker/config.json 은 건드리지 않음. 이 환경 이미지는 모두 공개 이미지)
if [ -z "${DOCKER_CONFIG:-}" ] && grep -q 'desktop.exe' "${HOME}/.docker/config.json" 2>/dev/null; then
  export DOCKER_CONFIG="$ROOT_DIR/.docker"
  mkdir -p "$DOCKER_CONFIG"; [ -f "$DOCKER_CONFIG/config.json" ] || echo '{}' > "$DOCKER_CONFIG/config.json"
fi
SUBST_VARS='$LAB_APP_USER $LAB_APP_PASSWORD $LAB_LIMITED_USER $LAB_LIMITED_PASSWORD $LAB_OBSERVER_USER $LAB_OBSERVER_PASSWORD $LAB_INSTRUCTOR_USER $LAB_INSTRUCTOR_PASSWORD'

load_env(){
  if [ ! -f .env ]; then
    cp .env.example .env
    echo "[!] .env 가 없어 .env.example 로 생성했습니다. 비밀값을 반드시 변경하세요."
  fi
  set -a; . ./.env; set +a
}

wait_db(){
  echo -n "[*] DB healthy 대기"
  for i in $(seq 1 60); do
    if $DC exec -T db healthcheck.sh --connect >/dev/null 2>&1; then echo " ok"; return 0; fi
    echo -n "."; sleep 2
  done
  echo " timeout"; return 1
}

apply_sql(){
  echo "[*] db/init/*.sql 적용 (envsubst 로 .env 비밀 주입, 하드코딩 없음)"
  for f in db/init/0*.sql; do
    echo "    - $f"
    envsubst "$SUBST_VARS" < "$f" | $DC exec -T db mariadb -uroot -p"${MARIADB_ROOT_PASSWORD}"
  done
}
