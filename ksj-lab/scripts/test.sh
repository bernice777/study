#!/usr/bin/env bash
# 자동 테스트. 로컬 환경만 대상. (start.sh 실행 후 사용)
source "$(dirname "$0")/_common.sh"
load_env
exec bash tests/run_tests.sh
