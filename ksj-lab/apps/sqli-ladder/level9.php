<?php require __DIR__.'/_lib.php'; lab_log_request(['level'=>'9','note'=>'filter ladder index']); page_header('Level 9 — 필터 우회 사다리'); ?>
<h1>Level 9 — 필터 우회 사다리</h1>
<div class="box goal"><b>목표</b>: 표면 방어가 왜 불완전한지, 남는 입력 컨텍스트와 근본 방어를 단계로 본다. 각 단계는 <b>고정 엔드포인트</b>라 결과가 재현된다.</div>
<div class="box"><ol>
<li><a href="/labs/filter/none.php">none</a> — 방어 없음(문자열 결합)</li>
<li><a href="/labs/filter/client-only.php">client-only</a> — 클라이언트 검증만(서버 무방비)</li>
<li><a href="/labs/filter/escape-only.php">escape-only</a> — Escape 만(숫자 컨텍스트에 무력)</li>
<li><a href="/labs/filter/keyword-blacklist.php">keyword-blacklist</a> — 키워드 블랙리스트(표현 차이로 우회)</li>
<li><a href="/labs/filter/prepared.php">prepared</a> — 근본 방어(Placeholder+바인딩)</li>
</ol></div>
<?php page_footer(); ?>
