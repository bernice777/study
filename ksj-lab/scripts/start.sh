#!/usr/bin/env bash
source "$(dirname "$0")/_common.sh"
load_env
echo "[*] 이미지 빌드"
$DC build web observer
echo "[*] DB 기동"
$DC up -d db
wait_db
apply_sql
echo "[*] web/observer 기동"
$DC up -d web observer
echo
echo "[✓] 실행 완료"
echo "  학생용 웹      : http://127.0.0.1:${WEB_PORT:-8000}/"
echo "  KSJ sbadmin    : http://127.0.0.1:${WEB_PORT:-8000}/ksj/"
echo "  SQLi Labs      : http://127.0.0.1:${WEB_PORT:-8000}/labs/"
echo "  Observer       : http://127.0.0.1:${OBSERVER_PORT:-8002}/"
echo "  강사 phpMyAdmin: docker compose --profile instructor up -d phpmyadmin  → http://127.0.0.1:${PMA_PORT:-8001}/ (lab_instructor)"
