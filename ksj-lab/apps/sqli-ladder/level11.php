<?php require __DIR__.'/_lib.php'; page_header('Level 11 — 식별자 자리 (화이트리스트)');
$db=ladder_db(); $sort=$_GET['sort']??'created'; $mode=$_GET['mode']??'bad';
$out=[]; $err=null; $sql='';
if($mode==='bad'){ // 잘못됨: 입력을 식별자로 직접 결합
  $sql="select idx,title,regDt from freeboard order by {$sort}";
  $r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'11/bad','note'=>'identifier concat']);
  if($r instanceof mysqli_result) while($x=mysqli_fetch_assoc($r))$out[]=$x; else $err=mysqli_error($db);
} else { // 올바름: 화이트리스트 매핑
  $allowed=['name'=>'title','created'=>'regDt','hits'=>'hit'];
  $col=$allowed[$sort]??'idx';
  $sql="select idx,title,regDt from freeboard order by {$col}  -- (whitelist: {$sort}=>{$col})";
  $r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'11/good','note'=>'identifier whitelist']);
  if($r instanceof mysqli_result) while($x=mysqli_fetch_assoc($r))$out[]=$x;
} ?>
<h1>Level 11 — 식별자 자리</h1>
<div class="box goal"><b>목표</b>: 컬럼명·테이블명·정렬방향은 <b>값 Placeholder 로 바인딩 불가</b>. ORDER BY 에 입력을 직접 결합하면 주입된다. 근본방어: <b>화이트리스트 매핑</b>.</div>
<div class="box"><form method="GET">sort = <input name="sort" value="<?=h($sort)?>" size="40">
 <select name="mode"><option value="bad"<?=$mode==='bad'?' selected':''?>>bad(직접결합)</option><option value="good"<?=$mode==='good'?' selected':''?>>good(화이트리스트)</option></select> <button>정렬</button></form>
<p>bad 힌트: <code>sort=(select case when 1=1 then idx else title end)</code> 또는 <code>sort=idx desc-- </code>. good: 허용 밖 값은 idx 로 폴백.</p></div>
<?php show_code("// bad:\n\$sql=\"... order by {\$sort}\";\n// good:\n\$allowed=['name'=>'title','created'=>'regDt','hits'=>'hit'];\n\$col=\$allowed[\$sort]??'idx';\n\$sql=\"... order by {\$col}\";");
show_sql($sql); show_error($err);
echo '<table><tr><th>idx</th><th>title</th><th>regDt</th></tr>'; foreach($out as $r) echo '<tr><td>'.h($r['idx']).'</td><td>'.h($r['title']).'</td><td>'.h($r['regDt']).'</td></tr>'; echo '</table>';
page_footer(); ?>
