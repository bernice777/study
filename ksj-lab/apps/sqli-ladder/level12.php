<?php require __DIR__.'/_lib.php'; page_header('Level 12 — 가짜 Prepared Statement');
$db=ladder_db(); $q=$_GET['q']??"x' or '1'='1"; ?>
<h1>Level 12 — 가짜 Prepared Statement</h1>
<div class="box goal"><b>목표</b>: "prepare 처럼 보이지만" 실제로는 값을 먼저 문자열에 끼워넣는 코드와 진짜 바인딩을 구분. 실행 전 <b>최종 SQL</b>과 <b>바인딩 정보</b>를 나눠서 본다.</div>
<div class="box"><form method="GET">q = <input name="q" value="<?=h($q)?>" size="60"> <button>비교</button></form></div>
<?php
// A) 순수 문자열 연결
$a="select idx,email from users where email='{$q}'";
$ra=lab_query($db,$a,__FILE__,__LINE__,['level'=>'12/concat','note'=>'string concat']);
// B) 가짜 PS: sprintf 로 미리 삽입 (Placeholder 처럼 %s 를 쓰지만 실제 바인딩 아님)
$b=sprintf("select idx,email from users where email='%s'",$q);
$rb=lab_query($db,$b,__FILE__,__LINE__,['level'=>'12/fake','note'=>'sprintf fake-PS']);
// C) 진짜 PS
$c="select idx,email from users where email=?"; $st=mysqli_prepare($db,$c); mysqli_stmt_bind_param($st,'s',$q);
mysqli_stmt_execute($st); $rs=mysqli_stmt_get_result($st); $cn=0; while($rs&&mysqli_fetch_assoc($rs))$cn++;
lab_log(['type'=>'query','level'=>'12/real','src'=>basename(__FILE__).':'.__LINE__,'sql'=>$c.' [bind s=>'.$q.']','account'=>$GLOBALS['LAB_DB_ACCOUNT'],'ok'=>true,'rows'=>$cn,'note'=>'real prepared']);
?>
<div class="box"><h3>A) 문자열 연결</h3><?php show_sql($a); echo '주입 '.($ra instanceof mysqli_result&&mysqli_num_rows($ra)>1?'<span class="bad">발생</span>':'').''; ?></div>
<div class="box"><h3>B) 가짜 PS (sprintf/format)</h3><?php show_sql($b); ?><p class="bad">%s 를 썼지만 실행 전에 이미 문자열이 완성됨 → 주입 동일하게 발생.</p></div>
<div class="box"><h3>C) 진짜 PS (prepare+bind_param)</h3><div class="sql"><?=h($c)?> &nbsp;[bind s =&gt; <?=h($q)?>]</div><p class="ok">구조 고정 + 데이터 분리 → <?=$cn?> 행(주입 안 됨).</p></div>
<div class="box warn">정상적인 값 바인딩은 "단순 우회"할 수 없다. 문제는 바인딩 <b>이전에</b> 문자열을 완성해버린 데 있다.</div>
<?php page_footer(); ?>
