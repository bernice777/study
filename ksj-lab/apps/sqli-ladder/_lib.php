<?php
// _lib.php — sqli-ladder 공유 헬퍼. labkit(auto_prepend)의 lab_connect/lab_query 사용.
function ladder_db(): mysqli { static $d=null; if($d===null)$d=lab_connect(); return $d; }
function ladder_db_limited(): mysqli { return lab_connect_limited(); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

const LADDER_LEVELS = [
 '0'=>'요청 관찰 (GET/POST/Cookie)',
 '1'=>'문자열형 로그인 SQLi',
 '2'=>'숫자형 SQLi',
 '3'=>'ORDER BY & UNION',
 '4'=>'스키마 탐색 (information_schema)',
 '5'=>'변경된 로그인 루틴 (UNION 위조)',
 '6'=>'Boolean-based Blind',
 '7'=>'Error-based',
 '8'=>'Time-based Blind',
 '9'=>'필터 우회 사다리',
 '10'=>'Prepared Statement 전/후',
 '11'=>'식별자 자리 (화이트리스트)',
 '12'=>'가짜 Prepared Statement',
 '13'=>'2차 SQL Injection',
 '14'=>'저장 프로시저 내부 동적 SQL',
 '15'=>'최소권한 (lab_app vs lab_limited)',
];

function page_header($title){ ?>
<!doctype html><html lang="ko"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><title><?=h($title)?></title>
<style>
 body{font-family:system-ui,'Segoe UI',sans-serif;max-width:960px;margin:1.2rem auto;padding:0 1rem;line-height:1.55;color:#1a1a1a}
 h1,h2{border-bottom:2px solid #4e73df;padding-bottom:.2rem}
 nav a{font-size:.8rem;margin-right:.4rem;color:#4e73df;text-decoration:none}
 .box{border:1px solid #ddd;border-radius:8px;padding:.8rem 1rem;margin:.8rem 0;background:#fafbff}
 .goal{background:#eef7ff;border-color:#9cc}
 .code{background:#1e1e2e;color:#e6e6e6;padding:.7rem;border-radius:6px;overflow:auto;font-family:ui-monospace,Consolas,monospace;font-size:.82rem;white-space:pre}
 .sql{background:#f6f8fa;border:1px solid #ddd;padding:.5rem;border-radius:6px;font-family:ui-monospace,monospace;font-size:.85rem;word-break:break-all}
 .err{background:#fff0f0;border:1px solid #f3b0b0;padding:.5rem;border-radius:6px;color:#a00;font-family:ui-monospace,monospace;font-size:.85rem}
 .ok{color:#0a0}.bad{color:#a00}
 table{border-collapse:collapse;width:100%;font-size:.85rem}th,td{border:1px solid #ccc;padding:.3rem .5rem;text-align:left}
 input,select{padding:.35rem;font-size:.9rem}button{padding:.4rem .8rem;background:#4e73df;color:#fff;border:0;border-radius:5px;cursor:pointer}
 .warn{background:#fff8e1;border-color:#f0c36d}
 .pill{display:inline-block;font-size:.7rem;background:#4e73df;color:#fff;border-radius:10px;padding:.05rem .5rem}
</style></head><body>
<nav><a href="/">🏠 Home</a> | <a href="/labs/">Labs</a>
<?php foreach(LADDER_LEVELS as $n=>$t): ?> <a href="/labs/level<?=$n?>.php">L<?=$n?></a><?php endforeach; ?>
 | <a href="/labs/roadmap.php">🗺 Roadmap</a> | <a href="http://127.0.0.1:8002/" target="_blank">👁 Observer</a></nav>
<?php }

function page_footer(){
  echo '<p style="color:#888;font-size:.8rem;margin-top:2rem">Trace ID: <code>'.h($GLOBALS['LAB_TRACE_ID']).'</code> — 이 요청의 SQL 은 Observer 에서 이 Trace 로 확인하세요.</p></body></html>';
}

// 취약 코드 스니펫 표시
function show_code(string $code, string $caption='취약 코드'){
  echo '<p><span class="pill">'.h($caption).'</span></p><div class="code">'.h($code).'</div>';
}
// 최종 SQL 표시 (blind 레벨에서는 호출하지 않음)
function show_sql(string $sql){ echo '<p>최종 SQL:</p><div class="sql">'.h($sql).'</div>'; }
function show_error(?string $e){ if($e) echo '<p>DB 오류:</p><div class="err">'.h($e).'</div>'; }
// 결과 테이블 (출력 escape → Observer/실습 페이지 자체 XSS 방지)
function show_result($result){
  if(!($result instanceof mysqli_result)){ echo '<p class="bad">결과 없음 또는 오류.</p>'; return; }
  $rows=[]; while($r=mysqli_fetch_assoc($result)) $rows[]=$r;
  if(!$rows){ echo '<p>0 행.</p>'; return; }
  echo '<table><thead><tr>'; foreach(array_keys($rows[0]) as $k) echo '<th>'.h($k).'</th>'; echo '</tr></thead><tbody>';
  foreach($rows as $r){ echo '<tr>'; foreach($r as $v) echo '<td>'.h($v).'</td>'; echo '</tr>'; }
  echo '</tbody></table><p style="font-size:.8rem;color:#666">'.count($rows).' 행</p>';
}
