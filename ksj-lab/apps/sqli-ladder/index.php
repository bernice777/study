<?php require __DIR__.'/_lib.php'; lab_log_request(['note'=>'ladder index']); page_header('SQLi Ladder — Level 0~15'); ?>
<h1>SQL Injection 단계별 실습</h1>
<div class="box warn">⚠️ 로컬 격리 교육 환경. 모든 데이터는 가짜입니다. 외부 대상 공격 금지.
평문 비밀번호·취약 코드는 <b>의도된 교육 구조</b>이며 실서비스에서는 Prepared Statement + password_hash 를 사용하세요.</div>
<div class="box">
<h2>레벨</h2>
<ol start="0" style="line-height:1.9">
<?php foreach(LADDER_LEVELS as $n=>$t): ?>
 <li value="<?=$n?>"><a href="/labs/level<?=$n?>.php"><b>Level <?=$n?></b> — <?=h($t)?></a></li>
<?php endforeach; ?>
</ol>
</div>
<div class="box"><h2>Level 9 필터 사다리 (고정 엔드포인트)</h2>
<a href="/labs/filter/none.php">none</a> ·
<a href="/labs/filter/client-only.php">client-only</a> ·
<a href="/labs/filter/escape-only.php">escape-only</a> ·
<a href="/labs/filter/keyword-blacklist.php">keyword-blacklist</a> ·
<a href="/labs/filter/prepared.php">prepared</a></div>
<?php page_footer(); ?>
