<?php
// SQL Query Observer — JSONL(/var/lab-logs/observer.jsonl) 조회.
// 학생 모드(기본): blind 레벨의 SQL/DB결과 숨김. 강사 모드: INSTRUCTOR_TOKEN 으로 전체 공개.
// 모든 출력은 escape → Observer 자체 XSS 방지.
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$LOG='/var/lab-logs/observer.jsonl';
$token=$_GET['token']??'';
$expected=getenv('INSTRUCTOR_TOKEN')?:'';
$instructor = ($expected!=='' && hash_equals($expected,$token));
$traceFilter=$_GET['trace']??'';

$recs=[];
if(is_readable($LOG)){
  $lines=array_slice(file($LOG,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES),-500);
  foreach($lines as $ln){ $r=json_decode($ln,true); if(is_array($r)) $recs[]=$r; }
}
$recs=array_reverse($recs); // 최신 먼저
if($traceFilter!=='') $recs=array_filter($recs,fn($r)=>($r['trace']??'')===$traceFilter);
?>
<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SQL Query Observer</title><style>
 body{font-family:system-ui,'Segoe UI',sans-serif;margin:1rem;color:#1a1a1a;background:#fff}
 h1{border-bottom:2px solid #4e73df;padding-bottom:.2rem}
 .mode{display:inline-block;padding:.15rem .6rem;border-radius:12px;font-size:.8rem;color:#fff}
 .stu{background:#6c757d}.ins{background:#dc3545}
 table{border-collapse:collapse;width:100%;font-size:.8rem;margin-top:.6rem}
 th,td{border:1px solid #ccc;padding:.3rem .45rem;vertical-align:top;word-break:break-all}
 th{background:#f1f3f9;position:sticky;top:0}
 .sql{font-family:ui-monospace,Consolas,monospace;background:#f6f8fa}
 .err{color:#a00}.ok{color:#0a0}.blind{color:#b8860b}
 .hidden-note{color:#999;font-style:italic}
 code{background:#eef;padding:0 .2rem}
 tr.request{background:#fbfbe8}tr.block{background:#fde}
</style></head><body>
<h1>👁 SQL Query Observer
 <?php if($instructor): ?><span class="mode ins">강사 모드</span><?php else: ?><span class="mode stu">학생 모드</span><?php endif; ?></h1>
<p>
 로그: <code><?=h($LOG)?></code> · 표시 <?=count($recs)?>건(최근 500 중)
 <?php if($traceFilter): ?> · 필터 trace=<code><?=h($traceFilter)?></code> <a href="?">해제</a><?php endif; ?>
 · <a href="?<?=$instructor?'token='.h($token):''?>">새로고침</a>
</p>
<?php if(!$instructor): ?>
<p class="hidden-note">학생 모드: Blind 레벨(6/8)의 최종 SQL·DB결과는 숨겨집니다. 강사 모드는 <code>?token=…</code>.</p>
<?php endif; ?>
<table>
<thead><tr><th>시각</th><th>Trace</th><th>Method</th><th>URL</th><th>GET</th><th>POST</th><th>소스</th><th>최종 SQL</th><th>계정</th><th>결과</th><th>행</th><th>ms</th></tr></thead>
<tbody>
<?php foreach($recs as $r):
  $type=$r['type']??'query'; $blind=!empty($r['blind']);
  $hideSql = (!$instructor && $blind);
  $cls=$type==='request'?'request':($type==='scope_block'?'block':'');
?>
<tr class="<?=$cls?>">
 <td><?=h(substr($r['ts']??'',11,8))?></td>
 <td><a href="?<?=$instructor?'token='.h($token).'&':''?>trace=<?=h($r['trace']??'')?>"><?=h(substr($r['trace']??'',0,8))?></a></td>
 <td><?=h($r['method']??'')?></td>
 <td><?=h($r['url']??'')?></td>
 <td class="sql"><?=h(json_encode($r['get']??[],JSON_UNESCAPED_UNICODE))?></td>
 <td class="sql"><?=h(json_encode($r['post']??[],JSON_UNESCAPED_UNICODE))?></td>
 <td><?=h($r['src']??'')?><?=$type==='scope_block'?'<br><b>SCOPE BLOCK: '.h($r['feature']??'').'</b>':''?></td>
 <td class="sql"><?php
    if($type!=='query'){ echo '<span class="hidden-note">('.h($type).')</span>'; }
    elseif($hideSql){ echo '<span class="blind">🔒 blind — 숨김</span>'; }
    else { echo h($r['sql']??''); }
 ?></td>
 <td><?=h($r['account']??'')?></td>
 <td><?php
    if($type!=='query'){ echo ''; }
    elseif($hideSql){ echo '<span class="blind">🔒</span>'; }
    elseif(!empty($r['error'])){ echo '<span class="err">ERR: '.h($r['error']).'</span>'; }
    else{ echo '<span class="ok">ok</span>'; }
 ?></td>
 <td><?=($hideSql)?'🔒':h($r['rows']??'')?></td>
 <td><?=h($r['ms']??'')?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php if(!$recs) echo '<p>아직 기록이 없습니다. 실습 페이지에서 요청을 발생시키세요.</p>'; ?>
</body></html>
