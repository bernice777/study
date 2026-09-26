<?php require __DIR__.'/_lib.php'; page_header('Level 5 — 변경된 로그인 루틴');
$db=ladder_db(); $email=$_POST['email']??''; $password=$_POST['password']??''; $sql=$result=null; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  // SQL 은 email 만 검색. 비밀번호 비교는 PHP 가 한다.
  $sql="select * from users where email='{$email}'";
  $result=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'5','note'=>'email-only + php compare']);
  $row=($result instanceof mysqli_result)?mysqli_fetch_assoc($result):null;
  if($row && isset($row['password']) && $row['password']===$password){
    $msg='<p class="ok">✅ 로그인 성공 — idx='.h($row['idx']).', email='.h($row['email']).'</p>';
  } else $msg='<p class="bad">로그인 실패 (row 없음 또는 password 불일치)</p>';
} ?>
<h1>Level 5 — 변경된 로그인 루틴 (UNION 위조)</h1>
<div class="box goal"><b>목표</b>: SQL 은 email 만 조회하고, 반환된 row 의 password 를 PHP 가 입력값과 비교한다.
UNION 으로 <b>앱이 기대하는 행</b>(원하는 password)을 직접 구성하라. users 컬럼=3 (idx,email,password).<br>
힌트: 이메일칸에 <code>x' union select 6,'learner@example.com','pass123' -- </code>, 비밀번호칸에 <code>pass123</code>.</div>
<div class="box"><form method="POST"><input name="email" placeholder="email" size="60" value="<?=h($email)?>"> <input name="password" placeholder="password"> <button>로그인</button></form></div>
<?php show_code("\$sql=\"select * from users where email='{\$email}'\";\n\$row=fetch(...);\nif(\$row['password'] === \$_POST['password']) // PHP 비교", 'SQL 결과 + 후속 PHP 조건문');
if($sql){ show_sql($sql); show_error($result===false?mysqli_error($db):null); echo $msg; }
page_footer(); ?>
