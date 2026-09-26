<?php require __DIR__.'/../_lib.php'; page_header('filter/keyword-blacklist — 블랙리스트');
$db=ladder_db(); $q=$_GET['q']??"admin@sbadmin.local"; $orig=$q;
// 순진한 블랙리스트: 일부 키워드를 통째로 제거 (대소문자/주석/공백 변형에 취약)
$black=[' union ',' or ',' and ','select','--',' '];
$filtered=str_ireplace($black,'',$q);
$sql="select idx,email from users where email='{$filtered}'";
$r=lab_query($db,$sql,__FILE__,__LINE__,['level'=>'9/blacklist','note'=>'keyword blacklist']); ?>
<h1>filter/keyword-blacklist</h1>
<div class="box">무엇을 막나: <code>union/or/and/select/--/공백</code> 문자열 제거. 왜 불완전: <b>대소문자·주석·중첩·인코딩</b> 등 표현 차이. 남는 컨텍스트: 문자열. 근본방어: Prepared Statement(블랙리스트는 보조).</div>
<div class="box"><form method="GET">email = <input name="q" value="<?=h($q)?>" size="70"> <button>조회</button></form>
<p>힌트: <code>'/**/oORr/**/1=1/**/-- -</code> 처럼 <b>중첩</b>으로 제거 후에도 키워드가 복원되게. (무제한 자동 도구 아님)</p></div>
<?php show_code("\$filtered=str_ireplace([' union ',' or ',' and ','select','--',' '],'',\$q);\n\$sql=\"...where email='{\$filtered}'\";");
echo '<p>입력(원본): <div class="sql">'.h($orig).'</div><p>필터 후: <div class="sql">'.h($filtered).'</div>';
show_sql($sql); show_error($r===false?mysqli_error($db):null); show_result($r); page_footer(); ?>
