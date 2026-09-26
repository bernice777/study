<?php
    $type = $_GET['t'];
    $idx = $_GET['i'];
    $result = mysqli_query($db, "select * from {$type} where idx={$idx}");
    $row = mysqli_fetch_assoc($result);

    mysqli_query($db, "update {$type} set hit = hit + 1 where idx={$idx}");
    
    $result = mysqli_query($db, "select * from users where idx='{$row['uIdx']}'");
    $writer = mysqli_fetch_assoc($result)['email'];
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
