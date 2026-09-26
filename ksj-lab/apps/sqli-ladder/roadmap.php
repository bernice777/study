<?php require __DIR__.'/_lib.php'; lab_log_request(['note'=>'roadmap']); page_header('공격·방어 사다리 & PS 경계'); ?>
<h1>공격·방어 사다리 (Roadmap)</h1>
<div class="box warn">WAF·에러 숨김은 <b>보조 방어</b>다. 근본 방어는 <b>파라미터 바인딩 · 식별자 화이트리스트 · 최소권한</b>이다.</div>
<table><thead><tr><th>단계</th><th>공격자 착안점</th><th>추가된 표면 방어</th><th>남는 우회/허점</th><th>근본 방어</th><th>실습</th></tr></thead><tbody>
<?php
$rows=[
 ['1. 클라이언트 검증','서버는 무방비','type=email/JS','Burp/직접요청','서버측 바인딩','<a href="/labs/filter/client-only.php">9/client</a>'],
 ['2. 문자열 결합','따옴표 탈출','-','전부','Prepared','<a href="/labs/level1.php">L1</a>'],
 ['3. Escape 처리','숫자 컨텍스트','real_escape_string','숫자 자리','바인딩+캐스팅','<a href="/labs/filter/escape-only.php">9/escape</a>'],
 ['4. 특수문자 블랙리스트','인코딩/치환','문자 제거','대소문자/주석/중첩','Prepared','<a href="/labs/filter/keyword-blacklist.php">9/blacklist</a>'],
 ['5. 키워드 필터','표현 차이','union/select 차단','uNioN, /**/','Prepared','<a href="/labs/filter/keyword-blacklist.php">9/blacklist</a>'],
 ['6. UNION 결과 출력','컬럼 수/자료형','-','출력 컬럼','바인딩+권한','<a href="/labs/level3.php">L3</a>'],
 ['7. 에러 메시지 노출','오류 채널','에러 표준화','Blind 전환','바인딩(원천)','<a href="/labs/level7.php">L7</a>'],
 ['8. Boolean Blind','참/거짓 차이','응답 통일','추론 가능','바인딩','<a href="/labs/level6.php">L6</a>'],
 ['9. Time-based Blind','응답 시간','응답 통일','시간 채널','바인딩','<a href="/labs/level8.php">L8</a>'],
 ['10. WAF','시그니처','패턴 차단','인코딩/우회','바인딩(보조로 WAF)','<a href="/labs/level9.php">L9</a>'],
 ['11. Prepared Statement','값 자리','구조/데이터 분리','식별자 자리','올바른 바인딩','<a href="/labs/level10.php">L10</a>'],
 ['12. 식별자 화이트리스트','ORDER BY/컬럼','바인딩 불가 자리','직접 결합 시','화이트리스트','<a href="/labs/level11.php">L11</a>'],
 ['13. 가짜 PS','format 선삽입','겉모습만 PS','실행 전 완성','실제 Placeholder','<a href="/labs/level12.php">L12</a>'],
 ['14. 2차 SQLi','저장→재사용','저장시 바인딩','사용시 재결합','사용시 재바인딩','<a href="/labs/level13.php">L13</a>'],
 ['15. 저장 프로시저','SP 내부 동적SQL','호출부만 안전','내부 결합','SP 내부 파라미터화','<a href="/labs/level14.php">L14</a>'],
 ['16. 최소권한','권한 범위','피해 제한','SQLi 자체 잔존','최소권한+View','<a href="/labs/level15.php">L15</a>'],
];
foreach($rows as $r){echo '<tr>';foreach($r as $i=>$c)echo '<td>'.($i>=5?$c:h($c)).'</td>';echo '</tr>';}
?>
</tbody></table>

<h2>Prepared Statement 의 적용 경계와 잘못된 사용</h2>
<table><thead><tr><th>유형</th><th>문제가 발생한 이유</th><th>풀이 착안점</th><th>근본 방어</th></tr></thead><tbody>
<tr><td>식별자 자리</td><td>ORDER BY·컬럼명은 값으로 바인딩할 수 없음</td><td>정렬 파라미터 흐름</td><td>식별자 화이트리스트</td></tr>
<tr><td>가짜 PS</td><td>입력값을 format·문자열 결합으로 먼저 삽입</td><td>실행 전 최종 SQL</td><td>실제 Placeholder와 바인딩</td></tr>
<tr><td>2차 SQLi</td><td>저장값을 꺼내 동적 SQL로 재조립</td><td>저장 후 재사용 흐름</td><td>사용할 때마다 다시 바인딩</td></tr>
<tr><td>SP 내부</td><td>프로시저 내부에서 동적 SQL 조립</td><td>PREPARE/EXECUTE 흐름</td><td>SP 내부도 파라미터화</td></tr>
</tbody></table>
<div class="box">정상적인 값 바인딩은 단순히 우회할 수 있는 것이 아니다. 위 4가지는 "바인딩이 닿지 않는 자리"이거나 "바인딩을 실제로 쓰지 않은 경우"다.</div>
<?php page_footer(); ?>
