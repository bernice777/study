<?php
// /health — 경량 상태 점검. DB 연결/charset 확인. (auto_prepend labkit 로드됨)
header('Content-Type: application/json; charset=utf-8');
$out = ['web' => 'ok', 'scope' => LAB_SCOPE, 'trace' => $GLOBALS['LAB_TRACE_ID']];
$host=getenv('LAB_DB_HOST')?:'db'; $u=getenv('LAB_DB_USER'); $p=getenv('LAB_DB_PASSWORD'); $n=getenv('LAB_DB_NAME');
$db=@mysqli_connect($host,$u,$p,$n);
if(!$db){ $out['db']='down'; http_response_code(503); echo json_encode($out); exit; }
mysqli_set_charset($db,'utf8mb4');
$out['db']='ok';
$out['db_version']=mysqli_get_server_info($db);
$r=mysqli_query($db,"select @@character_set_database cs, @@collation_database co");
$row=mysqli_fetch_assoc($r); $out['charset']=$row['cs']; $out['collation']=$row['co'];
http_response_code(200);
echo json_encode($out, JSON_UNESCAPED_UNICODE);
