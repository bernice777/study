<?php
    $type = $_GET['t'] ?? 'freeboard';
    $idx = $_GET['i'] ?? 0;
    // [LAB] 숫자형 SQLi 유지 (idx 는 따옴표 없음). lab_query 로 최종 SQL/결과 기록.
    $result = lab_query($db, "select * from {$type} where idx={$idx}", __FILE__, __LINE__, ['level'=>'2','note'=>'read (numeric)']);
    $row = ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : null;

    lab_query($db, "update {$type} set hit = hit + 1 where idx={$idx}", __FILE__, __LINE__, ['note'=>'hit++']);

    // [LAB] 저장된 uIdx 를 다시 쿼리에 사용 (2차성 조회 흐름).
    $r2 = lab_query($db, "select * from users where idx='{$row['uIdx']}'", __FILE__, __LINE__, ['note'=>'writer lookup (stored value -> query)']);
    $writer = ($r2 instanceof mysqli_result) ? (mysqli_fetch_assoc($r2)['email'] ?? '') : '';
?>
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?=$_GET['t']?></h1>
                    </div>

                    <div class="row">
                        <input type="hidden" name="type" value="<?=$_GET['t']?>">
                        <div class="col-lg-12">
                            <table class="table table-hover">
                                <tbody>
                                    <tr>
                                        <td>TITLE</td>
                                        <td><?=$row['title']?></td>
                                    </tr>
                                    <tr>
                                        <td>Writer</td>
                                        <td><?=$writer?></td>
                                    </tr>
                                    <tr>
                                        <td>RegDt</td>
                                        <td><?=$row['regDt']?></td>
                                    </tr>
                                    <?php if(!empty($row['upName'])): ?>
                                    <tr>
                                        <td>UPLOAD</td>
                                        <td><a href='./act/download.php?t=<?=$type?>&i=<?=$idx?>'><?=$row['upName']?></a></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan=2><textarea class="form-control" name="contents" rows=10 readonly><?=$row['contents']?></textarea></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-lg-12 text-right">
                            <button class="button btn-primary" type="button" onclick="history.back(-1);">list</button>
                        </div>

                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
