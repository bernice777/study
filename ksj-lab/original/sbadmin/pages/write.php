                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?=$_GET['t']?></h1>
                    </div>

                    <form method="POST" enctype="multipart/form-data" action="./act/write.php">
                    <div class="row">
                        <input type="hidden" name="type" value="<?=$_GET['t']?>">
                        <div class="col-lg-12">
                            <table class="table table-hover">
                                <tbody>
                                    <tr>
                                        <td>TITLE</td>
                                        <td><input type="text" class="form-control" name="title"></td>
                                    </tr>
                                    <tr>
                                        <td>UPLOAD</td>
                                        <td><input type="file" class="form-control" name="upload"></td>
                                    </tr>
                                    <tr>
                                        <td colspan=2><textarea class="form-control" name="contents" rows=10></textarea></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-lg-12 text-right">
                            <button class="button btn-primary" type="submit">submit</button>
                        </div>

                    </div>
                    </form>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
