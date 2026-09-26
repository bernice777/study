<?php require __DIR__.'/_lib.php'; page_header('Level 1 — 문자열형 로그인 SQLi');
$db=ladder_db(); $email=$_POST['email']??''; $password=$_POST['password']??''; $sql=$result=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $sql="select * from users where email='{$email}' and password='{$password}'";
  $result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'1','note'=>'string login']);
} ?>
<h1>Level 1 — 문자열형 로그인 SQLi</h1>
<div class="box goal"><b>목표</b>: 문자열 컨텍스트/따옴표/논리조건/주석(<code>-- </code>,<code>#</code>). 첫 결과 행이 로그인에 미치는 영향.
힌트: <code>learner@example.com' -- </code> (이메일칸) 또는 <code>' or 1=1 -- </code>. type=email 제한은 Burp 로 우회.</div>
<div class="box"><form method="POST"><input name="email" placeholder="email" value="<?=h($email)?>"> <input name="password" placeholder="password"> <button>로그인 시도</button></form></div>
<?php show_code("\$sql = \"select * from users where email='{\$email}' and password='{\$password}'\";", 'act/login.php 형태 (문자열 결합)');
if($sql){ show_sql($sql); show_error($result===false?mysqli_error($db):null);
  if($result instanceof mysqli_result){ $row=mysqli_fetch_assoc($result);
    if(isset($row['idx'])) echo '<p class="ok">✅ 로그인 성공 — idx='.h($row['idx']).', email='.h($row['email']).' (첫 행 기준)</p>';
    else echo '<p class="bad">login failed (행 없음)</p>'; } }
page_footer(); ?>
