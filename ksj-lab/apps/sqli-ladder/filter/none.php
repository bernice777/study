<?php require __DIR__.'/../_lib.php'; page_header('filter/none — 방어 없음');
$db=ladder_db(); $q=$_GET['q']??"admin@sbadmin.local";
$sql="select idx,email from users where email='{$q}'";
$r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'9/none','note'=>'no filter']); ?>
<h1>filter/none</h1>
<div class="box">무엇을 막나: <b>없음</b>. 왜 불완전: 사용자 입력이 문자열로 그대로 결합됨. 남는 컨텍스트: 문자열. 근본방어: Prepared Statement.</div>
<div class="box"><form method="GET">email = <input name="q" value="<?=h($q)?>" size="60"> <button>조회</button></form>
<p>힌트: <code>' or 1=1 -- </code></p></div>
<?php show_code("\$sql=\"select idx,email from users where email='{\$q}'\";");
show_sql($sql); show_error($r===false?mysqli_error($db):null); show_result($r); page_footer(); ?>
