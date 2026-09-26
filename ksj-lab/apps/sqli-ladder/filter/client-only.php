<?php require __DIR__.'/../_lib.php'; page_header('filter/client-only — 클라이언트 검증만');
$db=ladder_db(); $q=$_GET['q']??"admin@sbadmin.local";
$sql="select idx,email from users where email='{$q}'"; // 서버는 무방비
$r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'9/client','note'=>'client validation only']); ?>
<h1>filter/client-only</h1>
<div class="box">무엇을 막나: 브라우저 <code>type=email</code>/JS 패턴. 왜 불완전: <b>서버는 그대로 결합</b> → Burp/직접요청으로 우회. 남는 컨텍스트: 문자열. 근본방어: 서버측 Prepared Statement.</div>
<div class="box"><form method="GET" onsubmit="return /.+@.+/.test(this.q.value)||(alert('이메일 형식!'),false)">
email = <input name="q" type="email" value="<?=h($q)?>" size="60"> <button>조회</button></form>
<p>JS 검증은 브라우저에서만. <code>curl "http://127.0.0.1:8000/labs/filter/client-only.php?q=' or 1=1 -- "</code> 로 우회.</p></div>
<?php show_code("// 클라이언트: <input type=email> + JS 정규식\n// 서버:\n\$sql=\"select idx,email from users where email='{\$q}'\";");
show_sql($sql); show_error($r===false?mysqli_error($db):null); show_result($r); page_footer(); ?>
