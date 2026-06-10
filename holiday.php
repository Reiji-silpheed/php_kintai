<?php
require_once './loginClass.php';
require_once './messageClass.php';
require_once './dbClass.php';
session_start();
if($_SESSION['mail']==''){
    header('Location:./login.php');
}
$table=new dbClass();
$error=new Message();
$login=new login();
if(isset($_GET['searchBtn'])){
    $_SESSION['holidayDate']=$_GET['holidayDate'];
    $_SESSION['holidayName']=$_GET['holidayName'];
}
if(isset($_GET['cBtn'])){
    $_SESSION['holidayDate']='';
    $_SESSION['holidayName']='';
}
if(isset($_POST['newModalBtn'])){
    $_SESSION['newHolidayDate']=$_POST['newHolidayDate'];
    $_SESSION['newHolidayName']=$_POST['newHolidayName'];
    if($_SESSION['newHolidayDate']==''){
        $error->setError('newHolidayDate','日付が入力されていません');
    }
    if($_SESSION['newHolidayName']==''){
        $error->setError('newHolidayName','祝日名が入力されていません');
    }
    elseif(empty($error->error)){
        $table->begin();
        try{
            $table->dbAccess('INSERT INTO m_holiday (yyyymmdd,holiday_name) VALUES(:yyyymmdd,:holiday_name)',['yyyymmdd'=>$_SESSION['newHolidayDate'],'holiday_name'=>$_SESSION['newHolidayName']]);
            $table->commit();
        }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
    }
}
if(isset($_POST['updateModalBtn'])){
    $_SESSION['radio-update-id']=$_POST['radio-update-id'];
    $_SESSION['updateHolidayDate']=$_POST['updateHolidayDate'];
    $_SESSION['updateHolidayName']=$_POST['updateHolidayName'];
    if($_SESSION['updateHolidayDate']==''){
        $error->setError('updateHolidayDate','日付が入力されていません');
    }
    if($_SESSION['updateHolidayName']==''){
        $error->setError('updateHolidayName','祝日名が入力されていません');
    }
    elseif(empty($error->error)){
        $table->begin();
        try{
            $table->dbAccess('UPDATE m_holiday SET yyyymmdd=:yyyymmdd,holiday_name=:holiday_name WHERE id=:id',['yyyymmdd'=>$_SESSION['updateHolidayDate'],'holiday_name'=>$_SESSION['updateHolidayName'],'id'=>$_SESSION['radio-update-id']]);
            $table->commit();
        }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
    }
}
if(isset($_POST['delModalBtn'])){
    $_SESSION['radio-del-id']=$_POST['radio-del-id'];
    $table->begin();
    try{
        $table->dbAccess('DELETE FROM m_holiday WHERE id=:id',['id'=>$_SESSION['radio-del-id']]);
        $table->commit();
    }
    catch(Exception $ex){
        $table->rollback();
        exit();
    }

}

?>

<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- ②：BootstrapとjQueryで使うJavascriptを読み込む -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
    <title>祝日管理マスタ</title>
    <script>
        $(function(){
            $("#updateBtn").prop("disabled", true);
            $("#delBtn").prop("disabled", true);
            $(document).on("change", "input[name='radio']", function () {
                if ($("input[name='radio']:checked").length === 0) {
                    $("#updateBtn").prop("disabled", true);
                    $("#delBtn").prop("disabled", true);
                }
                else {
                    $("#updateBtn").prop("disabled", false);
                    $("#delBtn").prop("disabled", false);
                }
            })
            $(document).on("click","#updateBtn",function(){
                const selected = $("input[name='radio']:checked");
                const id=selected.val();
                $("#radio-update-id").val(id);
                const row = selected.closest("tr");
                const date = row.find("td").eq(1).text();
                $("#updateHolidayDate").val(date);
                const name = row.find("td").eq(2).text();
                $("#updateHolidayName").val(name);
            })
            $(document).on("click","#delBtn",function(){
                const selected = $("input[name='radio']:checked");
                const id=selected.val();
                $("#radio-del-id").val(id);
            })
        })
    </script>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand">勤怠管理システム</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="kintai.php">勤怠入力</a>
                    </li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        マスタ管理
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="employeeMaster.php">社員マスタ管理</a></li>
                        <li><a class="dropdown-item active" href="holiday.php">祝日マスタ管理</a></li>
                    </ul>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" href='login.php'>ログアウト</a>
                    </li>
                </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1>祝日マスタ管理</h1>
            <?php
            if(isset($_GET['searchBtn'])){
                if(!$login->holidaySearchCheck($_GET['holidayDate'],$_GET['holidayName'])){
                     echo $error->alert('alert-warning',"検索結果がありませんでした");
                }
            }
            ?> 
            <div class="card">
                <div class="card-header">
                    検索条件
                </div>
                <form method="GET">
                    <div class="card-body container">
                        <div class="row">
                            <div class="col-4">
                                <label class="form-label">日付:</label>
                                <input type="date" class="form-control" name="holidayDate" value='<?php if(isset($_GET['holidayDate'])){if($_SESSION['holidayDate']!==''){echo $_SESSION['holidayDate'];}else{echo '';}}?>'>
                            </div>
                            <div class="col-4">
                                <label class="form-label">祝日名:</label>
                                <input type="text" class="form-control" name="holidayName" value='<?php if(isset($_GET['holidayName'])){if($_SESSION['holidayName']!==''){echo $_SESSION['holidayName'];}else{echo '';}}?>'>
                            </div>
                        </div>
                        <div class="container">
                            <div class="d-grid gap-2 mt-2 d-md-flex justify-content-md-end">
                                <input type="submit" class="btn btn-warning" id="cBtn" name="cBtn" value="クリア">
                                <input type="submit" class="btn btn-info" name="searchBtn" value="検索">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    検索結果
                </div>
                <div class="card-body">
                <div class="container">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <!-- それぞれのボタンを押したときにモーダルが出るようにした -->
                        <button type="button" class="btn btn-success" id="newBtn" data-bs-toggle="modal"
                            data-bs-target="#newModal">新規</button>
                        <button type="button" class="btn btn-primary" id="updateBtn" data-bs-toggle="modal"
                            data-bs-target="#updateModal">更新</button>
                        <button type="button" class="btn btn-danger" id="delBtn" data-bs-toggle="modal"
                            data-bs-target="#delModal">削除</button>
                    </div>
                </div>

                <div class="container">
                    <table class="table mt-2">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">日付</th>
                                <th scope="col">祝日名</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $list=array();
                            $where='';
                            $url='';
                            $param=array();
                            if(isset($_GET['page'])){
                                if(is_numeric($_GET['page'])){
                                    $page=(int)$_GET['page'];
                                }
                            }
                            else{
                                $page=1;
                            }
                            $offset=5*($page-1);
                            $sql="SELECT * FROM m_holiday";
                            $count="SELECT count(id) FROM m_holiday";
                            if(isset($_GET['searchBtn'])){
                                if(!empty($_GET['holidayDate'])){
                                    $list[]='yyyymmdd=:yyyymmdd';
                                    $param['yyyymmdd']=$_GET['holidayDate'];
                                }
                                $url.="holidayDate={$_GET['holidayDate']}";
                                if(!empty($_GET['holidayName'])){
                                    $list[]='holiday_name=:holiday_name';
                                    $param['holiday_name']=$_GET['holidayName'];
                                }
                                $url.="&holidayName={$_GET['holidayName']}";
                                if(!empty($list)){
                                    $where=implode(' and ',$list);
                                    $sql.=" WHERE {$where}";
                                    $count.=" WHERE {$where}";
                                }
                                $url.="&searchBtn=検索";
                            }
                            $sql.=" order by id limit 5 offset {$offset}";
                            $rows=$table->select($sql,$param);
                            $countRows=$table->select($count,$param);
                            ?>
                            <?php foreach($rows as $row):?>
                            <tr>
                                <td scope="row">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="radio" id="<?php echo $row['id']?>" value="<?php echo $row['id'];?>" />
                                    </div>
                                </td>
                                <td><?php echo $row['yyyymmdd'];?></td>
                                <td><?php echo $row['holiday_name'];?></td>
                            </tr>
                            <?php endforeach?>
                        </tbody>
                    </table>
                </div>
                <!-- ページネイション -->
                <nav class="d-flex align-items-center justify-content-center">
                    <ul class="pagination">
                        <li class="page-item <?php if($page==1){echo 'disabled';}?>">
                            <a class="page-link" href="?<?php echo $url;?>&page=<?php echo $page-1; ?>">前</a>
                        </li>
                        <?php foreach($countRows as $countRow):?>
                            <?php for($i=1;$i<=ceil($countRow['count(id)']/5);$i++):?>
                                <li class="page-item <?php if($page==$i){echo 'active';}?>">
                                    <a class="page-link"  href="?<?php echo $url;?>&page=<?php echo $i; ?>"><?php echo $i;?></a>
                                </li>
                            <?php endfor?>
                        
                            <li class="page-item <?php if($page==ceil($countRow['count(id)']/5)){echo 'disabled';}?>">
                                <a class="page-link" href="?<?php echo $url;?>&page=<?php echo $page+1; ?>">次</a>
                            </li>
                        <?php endforeach?>
                    </ul>
                </nav>
            </div>
        </div>
    </main>
</body>

<!-- 新規モーダル -->
<?php if(isset($_POST['newModalBtn'])):?>
    <?php if(!empty($error->error)):?>
        <script>
            $(function(){
                $("#newModal").modal("show");
            });
        </script>
    <?php endif?>
<?php endif?>
<div class="modal fade" id="newModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog modal-xl">
    <form method="POST">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h1 class="modal-title fs-5 text-light">祝日登録</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header">
                        祝日情報
                    </div>
                    <div class="card-body container">
                        <div class="row">
                            <div class="col-4">
                                <label class="form-label">日付:</label>
                                <input type="date" class="form-control <?php echo $error->invalid('newHolidayDate');?>" name="newHolidayDate" value='<?php if(isset($_POST['newHolidayDate'])){if($_SESSION['newHolidayDate']!==''){echo $_SESSION['newHolidayDate'];}else{echo '';}}?>'>
                                <?php echo $error->getError('newHolidayDate');?>
                            </div>
                            
                            <div class="col-4">
                                <label class="form-label">祝日名:</label>
                                <input type="text" class="form-control <?php echo $error->invalid('newHolidayName');?>" name="newHolidayName" value='<?php if(isset($_POST['newholidayName'])){if($_SESSION['newHolidayName']!==''){echo $_SESSION['newHolidayName'];}else{echo '';}}?>'>
                                <?php echo $error->getError('newHolidayName');?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <input type="submit" name="newModalBtn" class="btn btn-success" value="登録">
            </div>
        </div><!-- /.modal-content -->
    </form>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- 更新モーダル -->
<?php if(isset($_POST['updateModalBtn'])):?>
    <?php if(!empty($error->error)):?>
        <script>
            $(function(){
                $("#updateModal").modal("show");
            });
        </script>
    <?php endif?>
<?php endif?>

<!-- 更新モーダル -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog modal-xl">
    <form method="POST">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h1 class="modal-title fs-5 text-light" >祝日更新</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header">
                        祝日情報
                    </div>
                    <div class="card-body container">
                        <div class="row">
                            <div class="col-4">
                                <label class="form-label">日付:</label>
                                <input type="date" class="form-control <?php echo $error->invalid('updateHolidayDate');?>" id="updateHolidayDate" name="updateHolidayDate" value='<?php if(isset($_POST['updateHolidayDate'])){if($_SESSION['updateHolidayDate']!==''){echo $_SESSION['updateHolidayDate'];}else{echo '';}}?>'>
                                <?php echo $error->getError('updateHolidayDate');?>
                            </div>
                            
                            <div class="col-4">
                                <label class="form-label">祝日名:</label>
                                <input type="text" class="form-control <?php echo $error->invalid('updateHolidayName');?>" id="updateHolidayName" name="updateHolidayName" value='<?php if(isset($_POST['updateholidayName'])){if($_SESSION['updateHolidayName']!==''){echo $_SESSION['updateHolidayName'];}else{echo '';}}?>'>
                                <?php echo $error->getError('updateHolidayName');?>
                            </div>
                            <input type="hidden" id="radio-update-id" name="radio-update-id" value="<?php if(isset($_POST['radio-update-id'])){echo $_SESSION['radio-update-id'];}else{echo '';}?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <input type="submit" name="updateModalBtn" class="btn btn-primary" value="更新">
            </div>
        </div><!-- /.modal-content -->
    </form>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- 削除モーダル -->
<!-- 切り替えボタンの設定 -->
<!-- モーダルの設定 -->
<div class="modal fade" id="delModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h1 class="modal-title fs-5 text-light">祝日削除</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
      </div>
      <div class="modal-body">
        <p>選択した祝日を削除しますか？</p>
      </div>
      <div class="modal-footer">
        <form method="POST">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
            <input type="submit" id="delModalBtn" name="delModalBtn" class="btn btn-danger" value="削除">
            <input type="hidden" id="radio-del-id" name="radio-del-id" value="<?php if(isset($_POST['delModalBtn'])){echo $_SESSION['radio-del-id'];}else{echo '';}?>">
        </form>
      </div><!-- /.modal-footer -->
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->