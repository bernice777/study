<?php require __DIR__.'/../_lib.php'; page_header('filter/escape-only — Escape 만');
$db=ladder_db(); $idx=$_GET['idx']??'1';
$esc=mysqli_real_escape_string($db,$idx);           // 문자열용 escape
$sql="select idx,title from freeboard where idx={$esc}";  // 숫자 컨텍스트 → 따옴표 없어 escape 무력
$r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'9/escape','note'=>'escape-only, numeric ctx']); ?>
<h1>filter/escape-only</h1>
<div class="box">무엇을 막나: <code>mysqli_real_escape_string</code> 로 따옴표 등 escape. 왜 불완전: <b>숫자 컨텍스트엔 따옴표가 없다</b> → escape 는 숫자 페이로드를 못 막는다. 남는 컨텍스트: 숫자. 근본방어: 파라미터 바인딩 + 정수 캐스팅.</div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="60"> <button>조회</button></form>
<p>힌트: <code>0 or 1=1</code> (따옴표 불필요 → escape 통과)</p></div>
<?php show_code("\$esc=mysqli_real_escape_string(\$db,\$idx);\n\$sql=\"select idx,title from freeboard where idx={\$esc}\"; // 숫자 자리");
show_sql($sql); show_error($r===false?mysqli_error($db):null); show_result($r); page_footer(); ?>
