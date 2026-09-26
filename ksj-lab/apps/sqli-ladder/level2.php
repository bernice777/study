<?php require __DIR__.'/_lib.php'; page_header('Level 2 — 숫자형 SQLi');
$db=ladder_db(); $idx=$_GET['idx']??'1';
$sql="select idx,uIdx,title,contents,upPath,upName,regDt,hit from freeboard where idx={$idx}";
$result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'2','note'=>'numeric']); ?>
<h1>Level 2 — 숫자형 SQLi</h1>
<div class="box goal"><b>목표</b>: 숫자 컨텍스트엔 따옴표가 없다 → Escape 만으로 못 막는다. 참/거짓 조건.
힌트: <code>1 and 1=1</code> vs <code>1 and 1=2</code>, <code>0 or 1=1</code>.</div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>"> <button>조회</button></form></div>
<?php show_code("\$sql = \"select * from freeboard where idx={\$idx}\";  // 따옴표 없음(숫자 컨텍스트)", 'pages/read.php 형태');
show_sql($sql); show_error($result===false?mysqli_error($db):null); show_result($result); page_footer(); ?>
