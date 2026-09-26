#!/usr/bin/env bash
source "$(dirname "$0")/_common.sh"
echo "[*] 컨테이너 중지 (볼륨은 보존)"
$DC --profile instructor down
echo "[✓] 중지 완료. (데이터 볼륨 db_data/lab_logs 는 유지됩니다)"
