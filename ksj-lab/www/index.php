<?php /* 랜딩. labkit auto_prepend 로드됨 */ ?>
<!doctype html><html lang="ko"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SQL Injection Lab</title>
<style>
 body{font-family:system-ui,'Segoe UI',sans-serif;max-width:900px;margin:2rem auto;padding:0 1rem;line-height:1.6;color:#1a1a1a}
 h1{border-bottom:3px solid #4e73df;padding-bottom:.3rem}
 .card{border:1px solid #ddd;border-radius:8px;padding:1rem 1.2rem;margin:1rem 0;background:#fafbff}
 .warn{background:#fff8e1;border-color:#f0c36d}
 a.btn{display:inline-block;background:#4e73df;color:#fff;padding:.5rem 1rem;border-radius:6px;text-decoration:none;margin:.2rem .3rem .2rem 0}
 code{background:#eef;padding:.1rem .3rem;border-radius:4px}
 .trace{color:#666;font-size:.85rem}
</style></head><body>
<h1>SQL Injection Lab <small style="font-size:.5em;color:#888">MariaDB/MySQL 호환 실습환경</small></h1>
<p>보안 교육용 <b>로컬 격리</b> 실습환경입니다. 외부 서비스에 연결되지 않습니다.
현재 범위: <code>LAB_SCOPE=<?=htmlspecialchars(LAB_SCOPE)?></code></p>

<div class="card warn">
 ⚠️ <b>취약한 레거시 교육 구조</b>입니다. 이 환경의 로그인/게시판 코드는 의도적으로 SQL Injection 에 취약하며,
 비밀번호를 평문으로 저장합니다. <b>실제 서비스에서는 반드시 <code>password_hash()</code>/<code>password_verify()</code>와
 Prepared Statement 를 사용</b>해야 합니다.
</div>

<div class="card">
 <h3>실습 경로</h3>
 <a class="btn" href="/ksj/">KSJ sbadmin (복구 원본)</a>
 <a class="btn" href="/labs/">SQLi 단계별 실습 (Level 0~15)</a>
 <a class="btn" href="/labs/roadmap.php">공격·방어 사다리</a>
 <a class="btn" href="http://127.0.0.1:8002/" target="_blank">SQL Query Observer</a>
</div>

<div class="card">
 <h3>DBMS 호환성 안내</h3>
 <p>이 환경은 <b>MariaDB 10.11</b> 기준입니다. MySQL 계열과 대부분 호환되나,
 <b>Error-based 페이로드는 DBMS/버전에 따라 동작이 달라질 수 있습니다</b>
 (예: <code>extractvalue</code>, <code>updatexml</code> 지원 여부·메시지 포맷 차이). Level 7 문서를 참고하세요.</p>
</div>

<p class="trace">Trace ID: <code><?=htmlspecialchars($GLOBALS['LAB_TRACE_ID'])?></code> —
응답 헤더 <code>X-Lab-Trace-ID</code> 로도 확인 가능. <a href="/health">/health</a></p>
</body></html>
