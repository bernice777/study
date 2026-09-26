<?php require __DIR__.'/_lib.php'; page_header('Level 15 — 최소권한 비교');
$idx=$_GET['idx']??"0 union select idx,email,password,4 from users";
$base="select idx,title,regDt,hit from v_freeboard_public where idx={$idx}";
function run_as(callable $connect,string $sql,string $lvl){
  $db=$connect(); $r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>$lvl,'note'=>'min-priv compare']);
  $rows=[]; $err=($r===false)?mysqli_error($db):null; if($r instanceof mysqli_result) while($x=mysqli_fetch_assoc($r))$rows[]=$x;
  return [$rows,$err];
}
[$appRows,$appErr]=run_as('lab_connect',$base,'15/lab_app');
[$limRows,$limErr]=run_as('lab_connect_limited',$base,'15/lab_limited'); ?>
<h1>Level 15 — 최소권한 (lab_app vs lab_limited)</h1>
<div class="box goal"><b>목표</b>: 동일한 취약 요청을 서로 다른 권한 계정으로 실행 → 피해 범위 차이. 최소권한은 SQLi 를 <b>제거</b>하는 게 아니라 <b>피해를 제한</b>한다.</div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="80"> <button>비교 실행</button></form>
<p style="font-size:.82rem">대상은 View <code>v_freeboard_public</code>(4컬럼). 페이로드: <code>0 union select idx,email,password,4 from users</code></p></div>
<?php show_sql($base); ?>
<div class="box"><h2>① lab_app (SELECT/INSERT/UPDATE on sbadmin.*)</h2>
<?php show_error($appErr); echo '<table><tr><th>idx</th><th>title</th><th>regDt</th><th>hit</th></tr>'; foreach($appRows as $r){echo '<tr>';foreach($r as $v)echo '<td>'.h($v).'</td>';echo '</tr>';} echo '</table>';
if(!$appErr && count($appRows)>count($limRows)) echo '<p class="bad">→ users 테이블(이메일/비밀번호)까지 노출됨.</p>'; ?></div>
<div class="box"><h2>② lab_limited (지정 View 만 SELECT)</h2>
<?php show_error($limErr); echo '<table><tr><th>idx</th><th>title</th><th>regDt</th><th>hit</th></tr>'; foreach($limRows as $r){echo '<tr>';foreach($r as $v)echo '<td>'.h($v).'</td>';echo '</tr>';} echo '</table>';
if($limErr) echo '<p class="ok">→ users 접근 거부(권한 없음). SQLi 는 존재하지만 피해 범위가 제한됨.</p>'; ?></div>
<?php page_footer(); ?>
