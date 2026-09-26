<?php require __DIR__.'/_lib.php'; page_header('Level 3 — ORDER BY & UNION');
$db=ladder_db(); $idx=$_GET['idx']??'1';
$sql="select * from freeboard where idx={$idx}";
$result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'3','note'=>'order by / union']); ?>
<h1>Level 3 — ORDER BY & UNION</h1>
<div class="box goal"><b>목표</b>: 컬럼 수 8개. <code>order by 8</code> 성공 / <code>order by 9</code> 실패로 컬럼 수 확인 →
<code>0 union select 1,2,3,4,5,6,7,8</code> 로 출력 컬럼 찾기. UNION 은 컬럼 수·자료형이 맞아야 한다.</div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="60"> <button>조회</button></form>
<p style="font-size:.85rem">예: <code>1 order by 8</code>, <code>1 order by 9</code>, <code>0 union select 1,2,3,4,5,6,7,8</code></p></div>
<?php show_code("\$sql = \"select * from freeboard where idx={\$idx}\";  // 8컬럼", 'UNION 대상');
show_sql($sql); show_error($result===false?mysqli_error($db):null); show_result($result); page_footer(); ?>
