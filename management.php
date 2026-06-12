<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- ②：BootstrapとjQueryで使うJavascriptを読み込む -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
    <title>勤怠管理</title>
</head>
<?php
require_once './loginClass.php';
require_once './messageClass.php';
require_once './dbClass.php';
session_start();
date_default_timezone_set('Asia/Tokyo');
$login=new dbClass();
$error=new Message();
$table=new dbClass();
$date=new DateTime();
?>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom border-body" data-bs-theme="dark">
            <div class=" container-fluid">
                <a class="navbar-brand">勤怠管理システム</a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#Navber"
                    aria-controls="Navber" aria-expanded="false" aria-label="ナビゲーションの切替">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="Navber">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a id="input" class="nav-link active" aria-current="page" href="management.php">勤怠管理</a>
                        </li>
                        <li class="nav-item">
                            <a id="input" class="nav-link" aria-current="page" href="kintai.php">勤怠入力</a>
                        </li>
                        <!-- 今は社員マスタ画面を開いているため、マスタ管理と社員マスタ管理にactive-->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                マスタ管理
                            </a>
                            <ul class="dropdown-menu">
                                <li><a id="employeeMaster" class="dropdown-item" href="employeeMaster.php">社員マスタ管理</a></li>
                                <li><a class="dropdown-item" href="holiday.php">祝日マスタ管理</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="login.php" id="logout" class="nav-link" aria-disabled="true">ログアウト</a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
    </header>
    <?php
        $select='';
        $select0='';
        $select1='';
        $select2='';
        $select3='';
        $sql="SELECT * FROM t_attendance_head LEFT JOIN m_employee on m_employee.id=t_attendance_head.employee_id";
        $list=[];
        $where='';
        $param=[];
    if(isset($_GET['searchBtn'])){
        $_SESSION['searchDate']=$_GET['searchDate'];
        if($_GET['searchDate']!==''){
            $firstParts=explode("-",$_GET['searchDate']);
            $yyyy=$firstParts[0];
            $mm=$firstParts[1];
            $yyyymm="{$yyyy}{$mm}";
        }
        $_SESSION['searchNumber']=$_GET['searchNumber'];
        $_SESSION['searchName']=$_GET['searchName'];
        if(!empty($_GET['searchDate'])){
            $list[]='yyyymm=:yyyymm';
            $param['yyyymm']=$yyyymm;
        }
        if(!empty($_GET['searchNumber'])){
            $list[]='employee_no=:employee_no';
            $param['employee_no']=$_GET['searchNumber'];
        }
        if(!empty($_GET['searchName'])){
            $list[]='employee_name=:employee_name';
            $param['employee_name']=$_GET['searchName'];
        }
        if($_GET['searchStatus']!==''){
            $list[]='status=:status';
            $param['status']=$_GET['searchStatus'];
        }
        if($_GET['searchStatus']==0){
            $select0='selected';
        }
        if($_GET['searchStatus']==1){
            $select1='selected';
        }
        if($_GET['searchStatus']==2){
            $select2='selected';
        }
        if($_GET['searchStatus']==3){
            $select3='selected';
        }
        if(!empty($list)){
            $where=implode(' and ',$list);
            $sql.=" WHERE {$where}";
        }
        $rows=$table->select($sql,$param);
    }
    if(isset($_GET['cBtn'])){
        $select='';
        $_SESSION['searchDate']='';
        $_SESSION['searchNumber']='';
        $_SESSION['searchName']='';
        $_SESSION['searchStatus']='';
    }
    ?>
    <main>
        <div class="container">
            <h1>勤怠管理</h1>
            <?php
            if(isset($_GET['searchBtn'])){
                if(empty($rows)){
                    echo $error->alert('alert-warning','検索結果がありませんでした');
                }
            }
            ?>
            <div class="card">
                <div class="card-header">
                    検索条件
                </div>
                <form method="GET">
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-3">
                                    <label class="form-label">年月:</label>
                                    <input type="month" class="form-control" name="searchDate" value="<?php if(isset($_GET['searchDate'])){echo $_SESSION['searchDate'];}else{echo $date->format('Y-m');}?>">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">社員番号:</label>
                                    <input type="text" class="form-control" name="searchNumber" value="<?php if(isset($_GET['searchNumber'])){echo $_SESSION['searchNumber'];}?>">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">社員名:</label>
                                    <input type="text" class="form-control" name="searchName" value="<?php if(isset($_GET['searchName'])){echo $_SESSION['searchName'];}?>">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">ステータス</label>
                                    <select class="form-select" id="searchStatus" name="searchStatus">
                                        <option hidden <?php echo $select;?>></option>
                                        <option value="0" <?php echo $select0;?>>入力中</option>
                                        <option value="1" <?php echo $select1;?>>申請中</option>
                                        <option value="2" <?php echo $select2;?>>差戻中</option>
                                        <option value="3" <?php echo $select3;?>>承認済</option>
                                    </select>
                                </div>
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
        </div>
    </main>
</body>