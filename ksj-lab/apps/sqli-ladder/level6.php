<?php require __DIR__.'/_lib.php'; page_header('Level 6 — Boolean-based Blind');
$db=ladder_db(); $idx=$_GET['idx']??'1';
// 참/거짓만 화면에 반영. SQL/결과는 학생에게 숨긴다(blind).
$sql="select idx from freeboard where idx={$idx}";
$result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'6','blind'=>true,'note'=>'boolean blind']);
$true=($result instanceof mysqli_result)&&mysqli_num_rows($result)>0; ?>
<h1>Level 6 — Boolean-based Blind</h1>
<div class="box goal"><b>목표</b>: 참이면 "게시글 있음", 거짓이면 "없음". 화면 차이만으로 한 문자씩 추론.
가짜 secret: <code>lab_secret.secret (id=1)</code>. SQL/DB결과는 숨겨져 있다(강사 모드 Observer 로만 확인).<br>
힌트: <code>1 and substr((select secret from lab_secret where id=1),1,1)='L'</code></div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="70"> <button>조회</button></form></div>
<div class="box"><h2>응답</h2>
<?php if($true): ?><p class="ok" style="font-size:1.2rem">🟢 게시글이 있습니다. (TRUE)</p>
<?php else: ?><p class="bad" style="font-size:1.2rem">🔴 게시글이 없습니다. (FALSE)</p><?php endif; ?>
<p style="color:#888;font-size:.8rem">(이 레벨은 최종 SQL·DB 결과를 표시하지 않습니다. Trace: <?=h($GLOBALS['LAB_TRACE_ID'])?>)</p></div>
<?php page_footer(); ?>
