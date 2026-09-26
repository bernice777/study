<?php
/**
 * labkit.php — 교육용 계측 키트 (auto_prepend 로 전역 로드).
 *
 * 원칙:
 *  - 취약점을 "제거"하지 않는다. 최종 SQL/메타데이터만 관찰·기록한다.
 *  - 비밀번호/세션 쿠키는 로그에서 마스킹한다.
 *  - 로그는 웹루트 밖(/var/lab-logs)에 JSONL 로 기록한다.
 */

if (!defined('LABKIT_LOADED')) {
    define('LABKIT_LOADED', true);

    // PHP 8.1+ 기본값은 mysqli 예외 throw. 교육상 '오류 반환+mysqli_error()' 관찰이 필요하므로 OFF.
    if (function_exists('mysqli_report')) { mysqli_report(MYSQLI_REPORT_OFF); }

    define('LAB_LOG_DIR', '/var/lab-logs');
    define('LAB_LOG_FILE', LAB_LOG_DIR . '/observer.jsonl');
    define('LAB_SCOPE', getenv('LAB_SCOPE') ?: 'SQLI');
    define('LAB_TIME_MAX', (int)(getenv('LAB_TIME_MAX_SECONDS') ?: 2));

    // ── Trace ID (요청당 1개, 헤더로 노출) ──────────────────────────
    $GLOBALS['LAB_TRACE_ID'] = bin2hex(random_bytes(8));
    if (!headers_sent()) {
        header('X-Lab-Trace-ID: ' . $GLOBALS['LAB_TRACE_ID']);
    }
    $GLOBALS['LAB_DB_ACCOUNT'] = null;   // 연결 시 채워짐
    $GLOBALS['LAB_REQ_T0'] = microtime(true);

    // ── 민감정보 마스킹 ────────────────────────────────────────────
    function lab_mask(array $arr): array {
        $sensitive = ['password','passwd','pw','pwd','pass'];
        $out = [];
        foreach ($arr as $k => $v) {
            if (in_array(strtolower((string)$k), $sensitive, true)) {
                $out[$k] = '***masked***';
            } elseif (is_array($v)) {
                $out[$k] = lab_mask($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    // ── JSONL 기록 ─────────────────────────────────────────────────
    function lab_log(array $rec): void {
        $rec = array_merge([
            'ts'      => date('c'),
            'trace'   => $GLOBALS['LAB_TRACE_ID'] ?? null,
            'method'  => $_SERVER['REQUEST_METHOD'] ?? null,
            'url'     => $_SERVER['REQUEST_URI'] ?? null,
            'get'     => lab_mask($_GET ?? []),
            'post'    => lab_mask($_POST ?? []),
        ], $rec);
        @file_put_contents(
            LAB_LOG_FILE,
            json_encode($rec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    // ── DB 연결 (환경변수 기반, 최소권한 lab_app) ─────────────────────
    function lab_connect(): mysqli {
        $host = getenv('LAB_DB_HOST') ?: 'db';
        $user = getenv('LAB_DB_USER') ?: 'lab_app';
        $pass = getenv('LAB_DB_PASSWORD') ?: '';
        $name = getenv('LAB_DB_NAME') ?: 'sbadmin';
        $db = mysqli_connect($host, $user, $pass, $name);
        if (!$db) { http_response_code(500); die('DB connect failed'); }
        mysqli_set_charset($db, 'utf8mb4');
        $GLOBALS['LAB_DB_ACCOUNT'] = $user;
        return $db;
    }

    // 최소권한 비교(Level 15): 제한 계정 연결
    function lab_connect_limited(): mysqli {
        $host = getenv('LAB_DB_HOST') ?: 'db';
        $user = getenv('LAB_LIMITED_USER') ?: 'lab_limited';
        $pass = getenv('LAB_LIMITED_PASSWORD') ?: '';
        $name = getenv('LAB_DB_NAME') ?: 'sbadmin';
        $db = mysqli_connect($host, $user, $pass, $name);
        if (!$db) { http_response_code(500); die('DB connect failed (limited)'); }
        mysqli_set_charset($db, 'utf8mb4');
        $GLOBALS['LAB_DB_ACCOUNT'] = $user;
        return $db;
    }

    /**
     * lab_query — 취약점을 유지한 채 최종 SQL과 결과 메타를 기록하는 래퍼.
     *   $opts: ['level'=>'2','blind'=>true,'note'=>'...']
     *          blind=true 이면 Observer 학생 모드에서 SQL/DB결과를 숨긴다.
     */
    function lab_query(mysqli $db, string $sql, string $file, int $line, array $opts = []) {
        $t0 = microtime(true);
        $result = @mysqli_query($db, $sql);       // ← 취약 쿼리 그대로 실행
        $ms = round((microtime(true) - $t0) * 1000, 2);

        $err = $result === false ? mysqli_error($db) : null;
        $rows = null;
        if ($result instanceof mysqli_result) {
            $rows = mysqli_num_rows($result);
        } elseif ($result === true) {
            $rows = mysqli_affected_rows($db);
        }

        lab_log([
            'type'    => 'query',
            'level'   => $opts['level']   ?? null,
            'blind'   => (bool)($opts['blind'] ?? false),
            'note'    => $opts['note']    ?? null,
            'src'     => basename($file) . ':' . $line,
            'src_full'=> $file . ':' . $line,
            'sql'     => $sql,
            'account' => $GLOBALS['LAB_DB_ACCOUNT'],
            'ok'      => $err === null,
            'error'   => $err,
            'rows'    => $rows,
            'ms'      => $ms,
        ]);
        return $result;
    }

    // 요청 자체 기록 (Level 0 요청관찰 / SQL 없는 요청도 추적)
    function lab_log_request(array $extra = []): void {
        lab_log(array_merge(['type' => 'request'], $extra));
    }

    // ── SQLI 범위 제어 (§9) ────────────────────────────────────────
    // 이번 수업 범위 밖 기능(LFI/업로드/다운로드/OUTFILE) 접근 거부.
    function lab_scope_deny(string $feature): void {
        if (LAB_SCOPE === 'SQLI') {
            lab_log(['type'=>'scope_block','feature'=>$feature]);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            die("[LAB_SCOPE=SQLI] '{$feature}' 기능은 이번 수업(SQL Injection) 범위에서 비활성화되어 있습니다.\n"
              . "원본 코드는 original/ 에 보존되어 있으며, 다른 수업에서 LAB_SCOPE 변경으로 활성화할 수 있습니다.");
        }
    }

    // pages include 화이트리스트 (index.php 의 ?p= LFI 표면 차단)
    function lab_allowed_pages(): array {
        return ['home.php','board.php','read.php','write.php','login.php','logout.php','register.php'];
    }
}
