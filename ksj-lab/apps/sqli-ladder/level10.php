<?php require __DIR__.'/_lib.php'; page_header('Level 10 — Prepared Statement 전/후');
$db=ladder_db(); $q=$_GET['q']??"admin@sbadmin.local' or '1'='1";
// 취약: 문자열 결합
$vsql="select idx,email from users where email='{$q}'";
$vr=lab_query($db,$vsql,__FILE__,__LINE__,['level'=>'10/vuln','note'=>'concat']);
$vrows=[]; if($vr instanceof mysqli_result) while($x=mysqli_fetch_assoc($vr))$vrows[]=$x;
// 수정: 실제 Placeholder + 타입 바인딩
$psafe=[]; $tmpl="select idx,email from users where email=?"; $st=mysqli_prepare($db,$tmpl);
mysqli_stmt_bind_param($st,'s',$q); mysqli_stmt_execute($st); $rs=mysqli_stmt_get_result($st);
while($rs&&$x=mysqli_fetch_assoc($rs))$psafe[]=$x;
lab_log(['type'=>'query','level'=>'10/prepared','src'=>basename(__FILE__).':'.__LINE__,'sql'=>$tmpl.' [bind s=>'.$q.']','account'=>$GLOBALS['LAB_DB_ACCOUNT'],'ok'=>true,'rows'=>count($psafe)]); ?>
<h1>Level 10 — Prepared Statement 전/후</h1>
<div class="box goal"><b>목표</b>: 같은 입력을 (1)문자열 결합 (2)Placeholder+바인딩 에 넣어 SQL 구조/데이터 분리를 관찰.</div>
<div class="box"><form method="GET">email = <input name="q" value="<?=h($q)?>" size="70"> <button>비교</button></form></div>
<div class="box"><h2>① 취약 (문자열 결합)</h2><?php show_sql($vsql); echo count($vrows).' 행'; foreach($vrows as $r) echo '<div class="sql">'.h(json_encode($r,JSON_UNESCAPED_UNICODE)).'</div>'; ?></div>
<div class="box"><h2>② 수정 (Prepared)</h2><div class="sql"><?=h($tmpl)?> &nbsp; [bind s =&gt; <?=h($q)?>]</div>
<p><?=count($psafe)?> 행 <?=count($psafe)?'':'<span class="ok">(주입 차단됨: 그 문자열의 email 이 없으므로 0행)</span>'?></p></div>
<?php page_footer(); ?>
