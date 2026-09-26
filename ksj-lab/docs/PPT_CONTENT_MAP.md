# PPT_CONTENT_MAP — 슬라이드 제작용 매핑

각 슬라이드 = (제목 / 실습 URL / 보여줄 PHP / 정상요청 / 취약요청 / 생성 SQL / 방어코드 / 촬영화면 / 핵심문장).

## S1. 웹 구조와 요청
- URL: `/labs/level0.php` | 화면: GET/POST/Cookie/Trace | 핵심: "입력은 서버에서 SQL 로 흘러간다."

## S2. 문자열형 로그인 SQLi
- URL: `/labs/level1.php` | PHP: `where email='{$email}' and password='{$password}'`
- 정상: `admin@sbadmin.local`/`admin_pw_2024` | 취약: email=`' or 1=1 -- `
- SQL: `... where email='' or 1=1 -- ' and password=''` | 방어: Prepared+`password_verify`
- 촬영: 로그인 성공 화면 + Observer SQL | 핵심: "따옴표를 닫고 논리를 참으로."

## S3. 숫자형 SQLi
- URL: `/labs/level2.php?idx=1` | PHP: `where idx={$idx}` | 취약: `0 or 1=1`
- 방어: 정수 캐스팅/바인딩 | 촬영: 전체 행 노출 | 핵심: "숫자 자리엔 따옴표가 없다 → escape 무력."

## S4. ORDER BY & UNION
- URL: `/labs/level3.php` | 취약: `1 order by 8` / `9`, `0 union select 1..8`
- 촬영: `Unknown column '9'` + union 숫자 출력 | 핵심: "컬럼 수를 세고 출력 자리를 찾는다."

## S5. 스키마 탐색
- URL: `/labs/level4.php` | 취약: `union ... information_schema.tables/columns`, `group_concat`
- 촬영: db명/테이블/컬럼/계정 추출 | 핵심: "메타데이터 DB 로 구조를 캔다."

## S6. 변경된 로그인 루틴
- URL: `/labs/level5.php` | PHP: email 만 조회 + PHP 가 password 비교
- 취약: email=`x' union select 6,'learner@example.com','p' -- ` / pw=`p`
- 핵심: "SQL 결과와 후속 코드 로직을 함께 봐야 한다."

## S7. Blind (Boolean/Time)
- URL: `/labs/level6.php`, `/labs/level8.php` | 촬영: true/false 화면차, 응답시간
- 핵심: "결과가 안 보여도 참/거짓·시간으로 새어 나온다." (학생 모드에선 SQL 숨김)

## S8. Error-based
- URL: `/labs/level7.php` | 취약: `extractvalue(1,concat(0x7e,(select database())))`
- 핵심: "오류 메시지가 데이터 채널이 된다. (DBMS별 상이)"

## S9. 필터 사다리
- URL: `/labs/level9.php`, `/labs/filter/*` | 핵심: "표면 방어는 컨텍스트/표현차로 뚫린다."

## S10. Prepared Statement 와 경계
- URL: `/labs/level10.php`~`level14.php`, `/labs/roadmap.php`
- 촬영: 동일 입력 차단 / 식별자 화이트리스트 / 가짜 PS / 2차 / SP 내부
- 핵심: "바인딩이 닿지 않는 자리(식별자)와 안 쓴 경우(가짜PS/2차/SP)를 구분."

## S11. 최소권한
- URL: `/labs/level15.php` | 촬영: lab_app 노출 vs lab_limited `denied`
- 핵심: "최소권한은 SQLi 를 없애지 않지만 피해를 줄인다."

## 부록: 촬영 팁
- 각 화면에 하단 Trace ID + Observer(강사 모드) 병치 캡처를 권장.
