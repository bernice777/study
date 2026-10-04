#!/usr/bin/env bash
set -euo pipefail

install -d -o mysql -g mysql /run/mysqld
mariadbd --user=mysql --bind-address=127.0.0.1 &
db_pid=$!

ready=0
for attempt in {1..60}; do
    if mariadb-admin --protocol=socket -uroot ping --silent >/dev/null 2>&1; then
        ready=1
        break
    fi
    if ! kill -0 "$db_pid" 2>/dev/null; then
        echo 'MariaDB stopped during startup.' >&2
        exit 1
    fi
    sleep 1
done
if [[ "$ready" != 1 ]]; then
    echo 'MariaDB startup timed out.' >&2
    exit 1
fi

if ! mariadb --protocol=socket -uroot -e 'USE user_db' >/dev/null 2>&1; then
    mariadb --protocol=socket -uroot < /app/init.sql
fi
exec python app.py
