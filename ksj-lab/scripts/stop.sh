#!/usr/bin/env bash
source "$(dirname "$0")/_common.sh"
echo "[*] 컨테이너 중지 (볼륨은 보존)"
$DC --profile instructor --profile extra --profile cms down
echo "[✓] 중지 완료. (sbadmin/XE/RB DB와 사이트 데이터 볼륨은 유지됩니다)"
