<?php require __DIR__.'/_lib.php'; page_header('Level 14 — 저장 프로시저 내부 동적 SQL');
$db=ladder_db(); $kw=$_GET['kw']??"' or '1'='1"; $which=$_GET['sp']??'vuln';
$out=[]; $err=null; $proc=$which==='vuln'?'sp_search_member_vuln':'sp_search_member_safe';
// 프로시저 본문 먼저 조회 (CALL 뒤엔 결과셋이 남아 out-of-sync 발생)
$bodies=[]; $q=mysqli_query($db,"select routine_name,routine_definition from information_schema.routines where routine_schema=database() and routine_name in ('sp_search_member_vuln','sp_search_member_safe')");
while($q instanceof mysqli_result && $b=mysqli_fetch_assoc($q))$bodies[$b['routine_name']]=$b['routine_definition'];
// CALL 은 마지막에. 여러 결과셋을 남기므로 소비 후 drain.
$sql="call {$proc}('".mysqli_real_escape_string($db,$kw)."')";
$r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'14/'.$which,'note'=>'stored proc dynamic sql']);
if($r instanceof mysqli_result){ while($x=mysqli_fetch_assoc($r))$out[]=$x; mysqli_free_result($r); } else $err=mysqli_error($db);
while(mysqli_more_results($db)){ mysqli_next_result($db); if($rr=mysqli_store_result($db)) mysqli_free_result($rr); } ?>
<h1>Level 14 — 저장 프로시저 내부 동적 SQL</h1>
<div class="box goal"><b>목표</b>: SP 호출부만 안전해 보여선 안 된다. <b>프로시저 내부</b>에서 문자열을 조합해 PREPARE/EXECUTE 하면 취약. 근본방어: SP 내부도 파라미터화(<code>EXECUTE ... USING ?</code>).</div>
<div class="box"><form method="GET">kw = <input name="kw" value="<?=h($kw)?>" size="50">
 <select name="sp"><option value="vuln"<?=$which==='vuln'?' selected':''?>>vuln</option><option value="safe"<?=$which==='safe'?' selected':''?>>safe</option></select> <button>호출</button></form>
<p style="font-size:.82rem">참고: 호출 인자 자체는 escape 했지만, vuln 프로시저는 <b>내부에서</b> 다시 결합하므로 <code>%' or '1'='1</code> 류로 주입된다.</p></div>
<div class="box"><h3>프로시저 내부 코드</h3>
<div class="code">-- vuln
<?=h($bodies['sp_search_member_vuln']??'(n/a)')?>

-- safe
<?=h($bodies['sp_search_member_safe']??'(n/a)')?></div></div>
<?php echo '<div class="box">'; show_sql($sql); show_error($err);
echo '<table><tr><th>id</th><th>username</th><th>nickname</th><th>role</th></tr>'; foreach($out as $r) echo '<tr><td>'.h($r['id']).'</td><td>'.h($r['username']).'</td><td>'.h($r['nickname']).'</td><td>'.h($r['role']).'</td></tr>'; echo '</table></div>';
page_footer(); ?>
