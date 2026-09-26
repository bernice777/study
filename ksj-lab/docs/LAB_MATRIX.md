# LAB_MATRIX — 레벨별 실습 매트릭스

| 레벨 | 취약점 | 사용 코드 | 입력 위치 | 관찰할 SQL | 표면 방어 | 우회 원리 | 근본 방어 | 완료 조건 |
|---|---|---|---|---|---|---|---|---|
| 0 | (없음) | level0.php | GET/POST/Cookie | (없음) | - | - | - | 요청 구조/Trace 확인 |
| 1 | 문자열 로그인 | level1.php (ksj act/login.php) | POST email/pw | `... where email='..' and password='..'` | 클라 email 형식 | 따옴표+주석 | Prepared | `' or 1=1 -- ` 로 로그인 |
| 2 | 숫자형 | level2.php (ksj read.php) | GET idx | `... where idx={idx}` | - | 따옴표 불필요 | 바인딩+캐스팅 | `0 or 1=1` 다중행 |
| 3 | ORDER BY/UNION | level3.php | GET idx | `... where idx={idx}` | - | 컬럼수/자료형 | 바인딩 | `order by 8` ok / `9` fail, 8컬럼 union |
| 4 | 스키마 탐색 | level4.php | GET idx | `union ... information_schema` | - | 메타DB | 최소권한 | db→table→column 확인 |
| 5 | 변경 로그인 | level5.php | POST email/pw | `... where email='..'` (+PHP 비교) | - | UNION 행 위조 | 바인딩 | union 으로 bughela 로그인 |
| 6 | Boolean Blind | level6.php | GET idx | (숨김) | 결과 통일 | 참/거짓 화면차 | 바인딩 | true/false 구분 |
| 7 | Error-based | level7.php | GET idx | `extractvalue/updatexml` | 에러 표준화 | 오류 채널 | 바인딩 | 에러에 데이터 노출 |
| 8 | Time Blind | level8.php | GET idx | (숨김) | 응답 통일 | 시간 채널 | 바인딩 | sleep 지연 관찰 |
| 9 | 필터 사다리 | filter/*.php | GET q/idx | 각 단계 | client/escape/blacklist | 컨텍스트/표현차 | prepared | 각 엔드포인트 재현 |
| 10 | PS 전/후 | level10.php | GET q | concat vs `?` bind | - | - | Placeholder | 동일 입력 차단 확인 |
| 11 | 식별자 자리 | level11.php | GET sort | `order by {sort}` | - | 식별자 바인딩 불가 | 화이트리스트 | good 모드 폴백 확인 |
| 12 | 가짜 PS | level12.php | GET q | concat/sprintf/real | - | 실행전 완성 | 실제 bind | 3방식 차이 확인 |
| 13 | 2차 SQLi | level13.php | POST nickname→GET id | 저장값 재결합 | 저장시 bind | 사용시 재결합 | 사용시 재bind | vuln>safe 행수 |
| 14 | SP 내부 | level14.php (005 procs) | GET kw | SP 내부 PREPARE/EXEC | 호출부만 | 내부 결합 | SP 내부 파라미터화 | vuln>safe 행수 |
| 15 | 최소권한 | level15.php | GET idx | `union ... from users` | - | 권한 범위 | 최소권한+View | app 노출/limited 거부 |
