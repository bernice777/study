<?php require __DIR__.'/_lib.php'; page_header('Level 13 — 2차 SQL Injection');
$db=ladder_db(); $msg=''; $mode=$_GET['mode']??'vuln';
// 1) 저장: nickname 을 안전하게(바인딩) 저장 → 저장 시점엔 구조 안 바뀜
if($_SERVER['REQUEST_METHOD']==='POST'){
  $u=$_POST['username']??''; $n=$_POST['nickname']??'';
  $st=mysqli_prepare($db,"insert into ladder_members (username,nickname,role) values (?,?, 'user')");
  mysqli_stmt_bind_param($st,'ss',$u,$n); mysqli_stmt_execute($st);
  lab_log(['type'=>'query','level'=>'13/store','src'=>basename(__FILE__).':'.__LINE__,'sql'=>"insert ... values(?,?) [bind ss=>$u,$n]",'account'=>$GLOBALS['LAB_DB_ACCOUNT'],'ok'=>true,'rows'=>1,'note'=>'2nd-order store (safe insert)']);
  $msg='<p class="ok">저장됨: '.h($u).' / nickname='.h($n).'</p>';
}
// 2) 관리 기능: 저장된 nickname 을 꺼내 동적 SQL 로 재조립
$adminOut=[]; $adminSql=''; $err=null; $target=$_GET['id']??'';
if($target!==''){
  $g=mysqli_query($db,"select nickname from ladder_members where id=".(int)$target);
  $stored=($g&&$row=mysqli_fetch_assoc($g))?$row['nickname']:'';
  if($mode==='vuln'){
    // 취약: 저장값을 그대로 결합 → 2차 주입
    $adminSql="select id,username,nickname from ladder_members where nickname='{$stored}'";
    $r=lab_query($db,$adminSql,__FILE__,__LINE__,['level'=>'13/vuln','note'=>'2nd-order use (concat stored value)']);
    if($r instanceof mysqli_result) while($x=mysqli_fetch_assoc($r))$adminOut[]=$x; else $err=mysqli_error($db);
  } else {
    // 수정: 저장된 데이터라도 사용할 때 다시 바인딩
    $st=mysqli_prepare($db,"select id,username,nickname from ladder_members where nickname=?");
    mysqli_stmt_bind_param($st,'s',$stored); mysqli_stmt_execute($st); $rs=mysqli_stmt_get_result($st);
    while($rs&&$x=mysqli_fetch_assoc($rs))$adminOut[]=$x;
    $adminSql="select ... where nickname=?  [bind s=>{$stored}]";
    lab_log(['type'=>'query','level'=>'13/safe','src'=>basename(__FILE__).':'.__LINE__,'sql'=>$adminSql,'account'=>$GLOBALS['LAB_DB_ACCOUNT'],'ok'=>true,'rows'=>count($adminOut),'note'=>'2nd-order use (rebind)']);
  }
} ?>
<h1>Level 13 — 2차 SQL Injection</h1>
<div class="box goal"><b>목표</b>: 저장 시점엔 안전하지만, 관리 기능이 <b>저장값을 꺼내 동적 SQL 로 재조립</b>할 때 취약. 근본방어: 저장 데이터도 <b>사용할 때 다시 바인딩</b>.</div>
<div class="box"><h3>1) 가입(저장)</h3><form method="POST">username <input name="username" value="attacker"> nickname <input name="nickname" size="50" value="' or '1'='1"> <button>저장</button></form><?=$msg?>
<p style="font-size:.82rem">저장 payload 예: <code>' union select role from ladder_members -- </code></p></div>
<div class="box"><h3>2) 관리 기능(조회)</h3><form method="GET">member id <input name="id" value="<?=h($target)?>">
 <select name="mode"><option value="vuln"<?=$mode==='vuln'?' selected':''?>>vuln(재결합)</option><option value="safe"<?=$mode==='safe'?' selected':''?>>safe(재바인딩)</option></select> <button>조회</button></form></div>
<?php if($target!==''){ echo '<div class="box">'; show_code("\$stored = fetch nickname where id=".(int)$target."; // 저장값\n// vuln: where nickname='{\$stored}'\n// safe: where nickname=?  bind(\$stored)");
 if($mode==='vuln') show_sql($adminSql); else echo '<div class="sql">'.h($adminSql).'</div>'; show_error($err);
 echo '<table><tr><th>id</th><th>username</th><th>nickname</th></tr>'; foreach($adminOut as $r) echo '<tr><td>'.h($r['id']).'</td><td>'.h($r['username']).'</td><td>'.h($r['nickname']).'</td></tr>'; echo '</table></div>'; }
page_footer(); ?>
