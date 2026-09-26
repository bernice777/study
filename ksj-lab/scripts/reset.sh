#!/usr/bin/env bash
source "$(dirname "$0")/_common.sh"
load_env
echo "[*] 이 프로젝트 실습 스키마만 초기화합니다 (다른 Docker 프로젝트/볼륨 건드리지 않음)."
$DC up -d db >/dev/null
wait_db
apply_sql
# 이 프로젝트 로그만 비움
$DC exec -T web sh -c ': > /var/lab-logs/observer.jsonl' 2>/dev/null || true
echo "[✓] reset 완료: Seed/AUTO_INCREMENT 초기 상태 복원, 실습 로그 초기화."
