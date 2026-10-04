#!/usr/bin/env bash
source "$(dirname "$0")/_common.sh"
load_env
$DC --profile cms up -d --build xe rb
echo 'XE: http://127.0.0.1:'"${XE_PORT:-8003}"'/xe/'
echo 'RB: http://127.0.0.1:'"${RB_PORT:-8004}"'/rb/'
echo '최초 1회 설치 값: docs/CMS_SETUP.md (재실행 시 데이터 보존)'
