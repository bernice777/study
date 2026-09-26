#!/bin/sh
set -e
# 볼륨(root 소유)에 www-data 가 JSONL 을 쓸 수 있도록 소유권 교정
mkdir -p /var/lab-logs
chown -R www-data:www-data /var/lab-logs || true
chmod 750 /var/lab-logs || true
exec "$@"
