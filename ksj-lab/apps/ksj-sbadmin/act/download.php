<?php
    include("../init.php");

    // [LAB-MOD] 이 엔드포인트는 select 후 file_get_contents($row['upPath']) 로
    //           임의 파일을 읽어 반환한다(LFI/임의파일읽기). SQLI 범위 밖 → 차단.
    lab_scope_deny('file_download');

    $type = $_GET['t'] ?? 'freeboard';
    $idx = $_GET['i'] ?? 0;

    $sql = "select * from {$type} where idx={$idx}";
    $result = lab_query($db, $sql, __FILE__, __LINE__, ['level'=>'2','note'=>'ksj download (numeric)']);
    $row = ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : null;

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename={$row['upName']}");

    echo file_get_contents($row['upPath']);
