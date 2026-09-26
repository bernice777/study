<?php require __DIR__.'/../_lib.php'; page_header('filter/prepared — 근본 방어');
$db=ladder_db(); $q=$_GET['q']??"admin@sbadmin.local"; $rows=[]; $err=null;
$tmpl="select idx,email from users where email=?";
$stmt=mysqli_prepare($db,$tmpl);
if($stmt){ mysqli_stmt_bind_param($stmt,'s',$q); mysqli_stmt_execute($stmt);
  $res=mysqli_stmt_get_result($stmt); while($res&&$row=mysqli_fetch_assoc($res))$rows[]=$row; }
else $err=mysqli_error($db);
lab_log(['type'=>'query','level'=>'9/prepared','src'=>basename(__FILE__).':'.__LINE__,'sql'=>$tmpl.'  [bind s => '.$q.']','account'=>$GLOBALS['LAB_DB_ACCOUNT'],'ok'=>$err===null,'error'=>$err,'rows'=>count($rows),'note'=>'real prepared statement']); ?>
<h1>filter/prepared ✅</h1>
<div class="box">무엇을 막나: SQL 구조와 데이터 분리(Placeholder <code>?</code> + 타입 바인딩). 왜 완전한가: 입력이 <b>데이터로만</b> 취급됨 → 구조 변경 불가. 남는 컨텍스트: 없음(값 자리). 식별자 자리는 Level 11 참고.</div>
<div class="box"><form method="GET">email = <input name="q" value="<?=h($q)?>" size="70"> <button>조회</button></form>
<p><code>' or 1=1 -- </code> 를 넣어도 그냥 그 문자열을 email 로 조회할 뿐 주입되지 않는다.</p></div>
<?php show_code("\$stmt=mysqli_prepare(\$db,\"select idx,email from users where email=?\");\nmysqli_stmt_bind_param(\$stmt,'s',\$q);\nmysqli_stmt_execute(\$stmt);");
echo '<p>템플릿(구조 고정): <div class="sql">'.h($tmpl).'</div><p>바인딩 값(데이터): <div class="sql">s => '.h($q).'</div>';
if($err) show_error($err);
if($rows){ echo '<table><tr><th>idx</th><th>email</th></tr>'; foreach($rows as $r) echo '<tr><td>'.h($r['idx']).'</td><td>'.h($r['email']).'</td></tr>'; echo '</table>'; } else echo '<p>0 행.</p>';
page_footer(); ?>
