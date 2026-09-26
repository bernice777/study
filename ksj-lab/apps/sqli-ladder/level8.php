<?php require __DIR__.'/_lib.php'; page_header('Level 8 — Time-based Blind');
$db=ladder_db(); $idx=$_GET['idx']??'1'; $cap=(int)LAB_TIME_MAX; $busy=false; $elapsed=null;
// 동시 요청 제한(컨테이너 과부하 방지): N개 슬롯 flock (비차단)
$SLOTS=2; $fh=null;
for($i=0;$i<$SLOTS;$i++){ $f=@fopen("/tmp/lab_time_slot_$i.lock","c"); if($f && flock($f,LOCK_EX|LOCK_NB)){ $fh=$f; break; } if($f) fclose($f); }
if($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['idx'])){
  if(!$fh){ $busy=true; }
  else {
    // 안전장치: 삽입된 sleep 인자를 상한으로 클램프 (수업시간 고려)
    $safe=preg_replace_callback('/sleep\(\s*(\d+(?:\.\d+)?)\s*\)/i', function($m) use($cap){ $v=min((float)$m[1],$cap); return "sleep($v)"; }, $idx);
    $sql="select idx from freeboard where idx={$safe}";
    $t0=microtime(true);
    lab_query($db,$sql,__FILE__,__LINE__,['level'=>'8','blind'=>true,'note'=>'time-based (cap='.$cap.'s)']);
    $elapsed=round((microtime(true)-$t0)*1000);
    flock($fh,LOCK_UN); fclose($fh);
  }
} ?>
<h1>Level 8 — Time-based Blind</h1>
<div class="box goal"><b>목표</b>: 참 조건에서만 지연 발생 → 응답 시간으로 추론. SQL 은 학생에게 숨김. 지연 상한 <b><?=$cap?>초</b>.<br>
힌트: <code>1 and if(substr((select secret from lab_secret where id=1),1,1)='L',sleep(<?=$cap?>),0)</code></div>
<div class="box"><form method="GET">idx = <input name="idx" value="<?=h($idx)?>" size="80"> <button>실행</button></form></div>
<div class="box"><h2>응답</h2>
<?php if($busy): ?><p class="bad">⏳ 동시 요청 한도(<?=$SLOTS?>) 초과 — 잠시 후 재시도.</p>
<?php elseif($elapsed!==null): ?><p>응답 시간: <b><?=$elapsed?> ms</b> <?=($elapsed>=($cap*1000-200))?'<span class="ok">(지연 감지 → 조건 TRUE 추정)</span>':'<span>(즉시 → FALSE 추정)</span>'?></p>
<p style="color:#888;font-size:.8rem">(최종 SQL 은 표시하지 않음. Trace: <?=h($GLOBALS['LAB_TRACE_ID'])?>)</p><?php endif; ?></div>
<?php page_footer(); ?>
