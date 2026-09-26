<?php require __DIR__.'/_lib.php';
lab_log_request(['level'=>'0','note'=>'request observation']);
if(isset($_GET['setcookie'])){ setcookie('lab_demo','hello-'.date('H:i:s'),0,'/'); header('Location: /labs/level0.php'); exit; }
page_header('Level 0 — 요청 관찰'); ?>
<h1>Level 0 — 요청 관찰</h1>
<div class="box goal"><b>학습 목표</b>: GET/POST 차이, Form action, Cookie/Session, Burp Repeater 재전송. 공격 기능 없음 — 요청 구조만 본다.</div>
<div class="box"><h2>GET form</h2>
<form method="GET" action="/labs/level0.php"><input name="q" placeholder="q 값"> <input name="page" placeholder="page"> <button>GET 전송</button></form></div>
<div class="box"><h2>POST form</h2>
<form method="POST" action="/labs/level0.php"><input name="msg" placeholder="msg 값"> <button>POST 전송</button></form></div>
<div class="box"><h2>Cookie</h2><a href="/labs/level0.php?setcookie=1"><button type="button">쿠키 설정</button></a></div>
<div class="box"><h2>수신한 요청</h2>
<p>Method: <b><?=h($_SERVER['REQUEST_METHOD'])?></b></p>
<p>GET: <div class="sql"><?=h(json_encode($_GET,JSON_UNESCAPED_UNICODE))?></div>
<p>POST: <div class="sql"><?=h(json_encode($_POST,JSON_UNESCAPED_UNICODE))?></div>
<p>Cookie: <div class="sql"><?=h(json_encode($_COOKIE,JSON_UNESCAPED_UNICODE))?></div>
<p>Session id: <code><?=h(session_id()?:'(none)')?></code></p></div>
<?php page_footer(); ?>
