<?php
require_once './dbClass.php';
require_once './loginClass.php';
require_once './messageClass.php';
session_start();
/* ログインしていない状態で社員マスタ管理画面に入れないようにした */
if(isset($_SESSION['mail'])==""){
    header('Location:./login.php');
}
$_SESSION['search-authority']='';
if(isset($_GET['searchBtn'])){
    $_SESSION['search-number']=$_GET['number-search-get'];
    $_SESSION['search-name']=$_GET['name-search-get'];
    $_SESSION['search-mail']=$_GET['mail-search-get'];
    $_SESSION['search-calendar']=$_GET['calendar-search-get'];
    $_SESSION['search-authority']=$_GET['authority-search-get'];
}
if(isset($_GET['cBtn'])){
    $_SESSION['search-number']='';
    $_SESSION['search-name']='';
    $_SESSION['search-mail']='';
    $_SESSION['search-calendar']='';
    $_SESSION['url']='';
    $_SESSION['search-authority']='';
}
/* エラーメッセージが出たときに入力した文字がもう一度出るようにした */
if(isset($_POST['newAppModal-post'])){
    $_SESSION['new-number']=$_POST['number-new-post'];
    $_SESSION['new-name']=$_POST['name-new-post'];
    $_SESSION['new-calendar']=$_POST['calendar-new-post'];
    $_SESSION['new-mail']=$_POST['mail-new-post'];
    $_SESSION['new-password']=$_POST['password-new-post'];
    $_SESSION['new-confirmationPassword']=$_POST['confirmationPassword-new-post'];
    $_SESSION['new-authority']=$_POST['authority-new-post'];
}
if(isset($_POST['updateModalBtn'])){
    $_SESSION['radio-update-id']=$_POST['radio-update-id'];
    $_SESSION['update-number']=$_POST['number-update-post'];
    $_SESSION['update-name']=$_POST['name-update-post'];
    $_SESSION['update-calendar']=$_POST['calendar-update-post'];
    $_SESSION['update-mail']=$_POST['mail-update-post'];
    $_SESSION['update-password']=$_POST['password-update-post'];
    $_SESSION['update-confirmationPassword']=$_POST['confirmationPassword-update-post'];
    $_SESSION['update-authority']=$_POST['authority-update-post'];
}
if(isset($_POST['delModalBtn'])){
    $_SESSION['radio-del-id']=$_POST['radio-del-id'];
}
$error=new Message();
$table=new dbClass();
$login=new login();
$rows=$table->select('SELECT email FROM m_employee',[]);
$isCommit=false;
#array_colum:キーを変える。
$emails = array_column($rows, 'email');

#登録モーダル
if(isset($_POST['newAppModal-post'])){
    $table->begin();
    try{
        if($_SESSION['new-number']==""){
            $error->setError('new-number',"社員番号が入力されていません");
        }
        if($_SESSION['new-name']==""){
            $error->setError('new-name',"社員名が入力されていません");
        }
        if($_SESSION['new-calendar']==""){
            $error->setError('new-start_date',"入社日が入力されていません");
        }
        if($_SESSION['new-mail']==""){
            $error->setError('new-mail',"メールアドレスが入力されていません");
        }
        if($_SESSION['new-password']==""){
            $error->setError('new-password',"パスワードが入力されていません");
        }
        if($_SESSION['new-confirmationPassword']==""){
            $error->setError('new-confirmationPassword',"確認パスワードが入力されていません");
        }
        if(in_array($_SESSION['new-mail'],$emails,true)){
            $error->setError('new-mail','既に登録されたメールアドレスです');
        }
        if($_SESSION['new-password']!==$_SESSION['new-confirmationPassword']){
            $error->setError('new-password',"パスワードが一致しません");
        }
        if(empty($error->error)){
            $table->dbAccess("INSERT INTO m_employee (employee_no,employee_name,email,start_date,password,role_cd)VALUES(:employee_no,:employee_name,:email,:start_date,:password,:role_cd)",['employee_no'=>$_POST['number-new-post'],'employee_name'=>$_POST['name-new-post'],'email'=>$_POST['mail-new-post'],'start_date'=>$_POST['calendar-new-post'],'password'=>$_POST['password-new-post'],'role_cd'=>$_POST['authority-new-post']]);
            $table->commit();
            header("Location:employeeMaster.php?{$_SESSION['url']}");
            exit();
        }
    }
    catch(Exception $ex){
        $table->rollback();
    }
}
#更新モーダル
if(isset($_POST['updateModalBtn'])){
    $table->begin();
    try{
        if($_SESSION['update-number']==""){
        $error->setError('update-number',"社員番号が入力されていません");
        }
        if($_SESSION['update-name']==""){
            $error->setError('update-name',"社員名が入力されていません");
        }
        if($_SESSION['update-calendar']==""){
            $error->setError('update-start_date',"入社日が入力されていません");
        }
        if($_SESSION['update-password']!==$_SESSION['update-confirmationPassword']){
            $error->setError('update-password',"パスワードが一致していません");
        }
        if(empty($error->error) && $_SESSION['update-password']==''){
            $table->dbAccess("UPDATE m_employee SET employee_no=:employee_no,employee_name=:employee_name,start_date=:start_date,role_cd=:role_cd WHERE id=:id",['employee_no'=>$_POST['number-update-post'],'employee_name'=>$_POST['name-update-post'],'start_date'=>$_POST['calendar-update-post'],'role_cd'=>$_POST['authority-update-post'],'id'=>$_POST['radio-update-id']]);
            $table->commit();
            header("Location:employeeMaster.php?{$_SESSION['url']}");
            exit();
        }
        if(empty($error->error) && $_SESSION['update-password']!==''){
            $table->dbAccess("UPDATE m_employee SET employee_no=:employee_no,employee_name=:employee_name,start_date=:start_date,password=:password,role_cd=:role_cd WHERE id=:id",['employee_no'=>$_POST['number-update-post'],'employee_name'=>$_POST['name-update-post'],'start_date'=>$_POST['calendar-update-post'],'password'=>$_POST['password-update-post'],'role_cd'=>$_POST['authority-update-post'],'id'=>$_POST['radio-update-id']]);
            $table->commit();
            header("Location:employeeMaster.php?{$_SESSION['url']}");
            exit();
        }

    }
    catch(Exception $ex){
        $table->rollback();
    }
}

#削除モーダル
if(isset($_POST['delModalBtn'])){
    $table->begin();
    try{
        $table->dbAccess("DELETE FROM m_employee WHERE id=:id",['id'=>$_SESSION['radio-del-id']]);
        $table->commit();
        header("Location:employeeMaster.php?{$_SESSION['url']}");
        exit();
    }
    catch(Exception $ex){
        $table->rollback();
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
    <title>社員マスタ管理</title>
    
    <script>
        $(function () {
            $("#updateBtn").prop("disabled", true);
            $("#delBtn").prop("disabled", true);
            /* 更新ボタンを押したときに検索結果でラジオボタンのついた社員データをモーダルに表示させた */
            
            $(document).on("click", "#updateBtn", function () {
                const selected = $("input[name='radio']:checked");
                const id=selected.val();
                $("#radio-update-id").val(id);
                const row = selected.closest("tr");
                const number = row.find("td").eq(1).text();
                $("#number-update-post").val(number);
                const name = row.find("td").eq(2).text();
                $("#name-update-post").val(name);
                const calendar = row.find("td").eq(4).text();
                $("#calendar-update-post").val(calendar);
                const mail = row.find("td").eq(3).text();
                $("#mail-update-post-text").val(mail);
                $("#mail-update-post").val(mail);
                $("#mail-update-post-text").prop("disabled",true);
                const authority=row.find("td").eq(5).text();
                if(authority==='一般'){
                    $("#authority0").prop("selected",true);
                }
                if(authority==="管理者"){
                    $("#authority1").prop("selected",true);
                }
            })

        
            /* 検索結果でラジオボタンのついた社員を削除するようにした*/
            $(document).on("click", "#delModalBtn", function () {
                const selected = $("input[name='radio']:checked");
                const id=selected.val();
                $("#radio-del-id").val(id);
            })
            /* ラジオボタンが点灯したときに、更新ボタンと削除ボタンを押せるようにした */
            $(document).on("change", "input[name='radio']", function () {
                if ($("input[name='radio']:checked").length === 0) {
                    $("#updateBtn").prop("disabled", true);
                    $("#delBtn").prop("disabled", true);
                }
                else {
                    $("#updateBtn").prop("disabled", false);
                    $("#delBtn").prop("disabled", false)
                }
            })
        })
    </script>
</head>


<body>
    <!-- メニュー画面 -->
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
                            <a id="input" class="nav-link" aria-current="page" href="management.php">勤怠管理</a>
                        </li>
                        <li class="nav-item">
                            <a id="input" class="nav-link" aria-current="page" href="kintai.php">勤怠入力</a>
                        </li>
                        <!-- 今は社員マスタ画面を開いているため、マスタ管理と社員マスタ管理にactive-->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle active" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                マスタ管理
                            </a>
                            <ul class="dropdown-menu">
                                <li><a id="employeeMaster" class="dropdown-item active" href="#">社員マスタ管理</a></li>
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
    <!-- 社員マスタ管理画面 -->
    <main>
        <div class="container">
            <h1>社員マスタ管理</h1>
            <?php
            $alertMessage="";
            if(isset($_GET['searchBtn'])){
                if(!$login->searchCheck($_GET['number-search-get'],$_GET['name-search-get'],$_GET['mail-search-get'],$_GET['calendar-search-get'])){
                    echo $error->alert('alert-warning',"検索結果がありませんでした");
                }
            }
            ?>
            
           
            
            <!-- 検索条件カード -->
            <div class="container">
                <div class="card" id="searchCard">
                    <div class="card-header">
                        検索条件
                    </div>
                    <!-- 社員名とメールアドレスの枠を広くするために、グリッドシステムを使用した -->
                    <form method="GET">
                        <div class="card-body">
                            <div class="container">
                                <div class="row">
                                    <div class="col-2">
                                        <label for="number-search-get" class="form-label">社員番号:</label>
                                        <!-- 何も入力されていないときにエラーが出るようにした -->
                                        <input type="text" class="form-control" id="number-search-get" name="number-search-get" value="<?php if(isset($_GET['number-search-get'])){echo $_SESSION['search-number'];}else{echo '';}; ?>">
                                    </div>

                                    <div class="col-3">
                                        <label for="name-search-get" class="form-label">社員名:</label>
                                        <input type="text" class="form-control" id="name-search-get" name="name-search-get" value="<?php if(isset($_GET['name-search-get'])){echo $_SESSION['search-name'];}else{echo '';}?>">
                                    </div>

                                    <div class="col-3">
                                        <label for="mail-search-get" class="form-label">メールアドレス:</label>
                                        <input type="text" class="form-control" id="mail-search-get" name="mail-search-get" value="<?php if(isset($_GET['mail-search-get'])){echo $_SESSION['search-mail'];}else{echo '';}?>">
                                    </div>

                                    <div class="col-2">
                                        <label for="calendar-search-get" class="form-label">入社日:</label>
                                        <input type="date" class="form-control" id="calendar-search-get" name="calendar-search-get" value="<?php if(isset($_GET['calendar-search-get'])){echo $_SESSION['search-calendar'];}else{echo '';}?>">
                                    </div>
                                    <div class="col-2">
                                        <label for="authority-search-get" class="form-label">権限:</label>
                                        <select class="form-select" id="authority-search-get" name="authority-search-get">
                                            <option value="" <?php if($_SESSION['search-authority']==''){echo 'selected';}?> hidden></option>
                                            <option value=0 <?php if(isset($_GET['authority-search-get'])){if($_SESSION['search-authority']==0){echo 'selected';}}?>>一般</option>
                                            <option value=1 <?php if(isset($_GET['authority-search-get'])){if($_SESSION['search-authority']==1){echo 'selected';}}?>>管理者</option>
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
            

            <!-- 検索結果カード -->
            <div class="container">
                <div class="card mt-4">
                    <div class="card-header">
                        検索結果
                    </div>
                    <form method="POST">
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
                                            <th scope="col">社員番号</th>
                                            <th scope="col">社員名</th>
                                            <th scope="col">メールアドレス</th>
                                            <th scope="col">入社日</th>
                                            <th scope="col">権限</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $page=1;
                                        if(isset($_GET['page'])){
                                            if(is_numeric($_GET['page'])){
                                                $page=(int)$_GET['page'];
                                            }
                                        }
                                        else{
                                            $page=1;
                                        }
                                        $offset=5*($page-1);
                                        #urlを作ることでページネイションのボタンを押したときに検索されたものが表示されるようにしている。
                                        $url="";
                                        $list=array();
                                        $where="";
                                        $param=array();
                                        $sql="SELECT * FROM m_employee";
                                        #countを作ることでデータ数に応じてページ数が変わるようにした。
                                        $count="SELECT count(id) FROM m_employee";
                                        if(!$isCommit){
                                            $rows=$table->select("SELECT * FROM m_employee ORDER BY id LIMIT 5 OFFSET $offset",[]);
                                        }
                                        else{
                                            $rows=$table->select("SELECT * FROM m_employee WHERE employee_no=:employee_no and employee_name=:employee_name and email=:email and start_date=:start_date",['employee_no'=>$_POST['number-new-post'],'employee_name'=>$_POST['name-new-post'],'email'=>$_POST['mail-new-post'],'start_date'=>$_POST['calendar-new-post']]);
                                        }
                                        if(isset($_GET['searchBtn'])){
                                            if(!empty($_GET['number-search-get'])){
                                                $list[]='employee_no=:employee_no';
                                                $param['employee_no']=$_GET['number-search-get'];
                                            }
                                            $url.="number-search-get={$_GET['number-search-get']}";
                                            if(!empty($_GET['name-search-get'])){
                                                $list[]='employee_name=:employee_name';
                                                $param['employee_name']=$_GET['name-search-get'];
                                            }
                                            $url.="&name-search-get={$_GET['name-search-get']}";
                                            if(!empty($_GET['mail-search-get'])){
                                                $list[]='email=:email';
                                                $param['email']=$_GET['mail-search-get'];
                                            }
                                            $url.="&mail-search-get={$_GET['mail-search-get']}";
                                            if(!empty($_GET['calendar-search-get'])){
                                                $list[]='start_date=:start_date';
                                                $param['start_date']=$_GET['calendar-search-get'];
                                            }
                                            $url.="&calendar-search-get={$_GET['calendar-search-get']}";
                                            if($_GET['authority-search-get']!==''){
                                                $list[]='role_cd=:role_cd';
                                                $param['role_cd']=$_GET['authority-search-get'];
                                            }
                                            $url.="&authority-search-get={$_GET['authority-search-get']}";
                                            $url.="&searchBtn=検索";
                                            $_SESSION['url']=$url;
                                            if(!empty($list)){
                                                $where=implode(' and ',$list);
                                                $sql.=" WHERE {$where}";
                                                $count.=" WHERE {$where}";
                                            }
                                            $sql.=" ORDER BY id LIMIT 5 OFFSET {$offset}";
                                            $rows=$table->select($sql,$param);
                                        }
                                        
                                        $lengths=$table->select($count,$param);
                                        ?>
                                        
                                        <?php if($alertMessage==""):?>
                                            <?php foreach($rows as $row):?>
                                                <tr>
                                                    <td scope="row">
                                                        <div class="form-check">
                                                            <label for="radio">
                                                                <input class="form-check-input" type="radio" name="radio" id="<?php echo $row['id'];?>" value='<?php echo $row['id'];?>'/>
                                                            </label>
                                                        </div>
                                                    </td>
                                                <td><?php echo $row['employee_no'];?></td>
                                                <td><?php echo $row['employee_name'];?></td>
                                                <td><?php echo $row['email'];?></td>
                                                <td><?php echo $row['start_date'];?></td>
                                                <td><?php if($row['role_cd']==0){echo "一般";}elseif($row['role_cd']==1){echo "管理者";}?></td>
                                                </tr>   
                                            <?php endforeach?>
                                        <?php endif?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- ページネイション -->
                            <nav class="d-flex align-items-center justify-content-center">
                                <ul class="pagination">
                                    <li class="page-item <?php if($page==1){echo 'disabled';}?>">
                                        <a class="page-link" href="?<?php echo $url;?>&page=<?php echo $page-1; ?>">前</a>
                                    </li>
                                    <?php foreach($lengths as $length):?>
                                        <?php for ($i=1;$i<=ceil($length['count(id)']/5);$i++):?>
                                        <li class="page-item <?php if($page==$i){echo 'active';}?>">
                                            <a class="page-link" href="?<?php echo $url;?>&page=<?php echo $i;?>"><?php echo $i;?></a>
                                        </li>
                                        <?php endfor?>
                                        <li class="page-item <?php if($page==ceil($length['count(id)']/5)){echo 'disabled';}?>" >
                                            <a class="page-link" href="?<?php echo $url;?>&page=<?php echo $page+1;?>">次</a>
                                        </li>
                                    <?php endforeach?>
                                    
                                </ul>
                            </nav>
                        </div>
                    </form>
                    
                </div>

                <!-- 登録モーダル -->
                 <?php if(isset($_POST['newAppModal-post'])):?>
                    <?php if(!empty($error->error)):?>
                    <script>
                        $(function(){
                            $("#newModal").modal("show");
                        });
                    </script>
                    <?php endif?>
                <?php endif?>
                <div class="modal fade modal-xl" id="newModal" tabindex="-1" aria-labelledby="newModalLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <h1 class="modal-title fs-5 text-white" id="newModalLabel">社員登録</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="閉じる"></button>
                            </div>
                            <form method="POST">
                                <div class="card mx-2 my-3">
                                    <div class="card-header">
                                        社員情報
                                    </div>
                                    <div class="card-body">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-3">
                                                    <label for="number-new-post" class="form-label ">社員番号:</label>
                                                    <input type="text" class="form-control <?php echo $error->invalid('new-number');?>" id="number-new-post" name="number-new-post" value="<?php if(isset($_POST['number-new-post'])){echo $_SESSION['new-number'];}else{echo "";}?>" >
                                                    <?php echo $error->getError('new-number');?>
                                                </div>

                                                <div class="col-4">
                                                    <label for="name-new-post" class="form-label">社員名:</label>
                                                    <input type="text" class="form-control <?php echo $error->invalid('new-name');?>" id="name-new-post" name="name-new-post" value="<?php if(isset($_POST['name-new-post'])){echo $_SESSION['new-name'];}else{echo "";}?>">
                                                    <?php echo $error->getError('new-name');?>
                                                </div>

                                                <div class="col-3">
                                                    <label for="calendar-new-post" class="form-label?>">入社日:</label>
                                                    <input type="date" class="form-control <?php echo $error->invalid('new-start_date');?>" id="calendar-new-post" name="calendar-new-post" value="<?php if(isset($_POST['calendar-new-post'])){echo $_SESSION['new-calendar'];}else{echo "";}?>">
                                                    <?php echo $error->getError('new-start_date');?>
                                                </div>
                                                <div class="col-2">
                                                    <label for="authority-new-post" class="form-label">権限:</label>
                                                    <select class="form-select" id="authority-new-post" name="authority-new-post">
                                                        <option value="0" <?php if(isset($_POST['authority-new-post'])){if($_SESSION['new-authority']==0){echo 'selected';}}?>>一般</option>
                                                        <option value="1" <?php if(isset($_POST['authority-new-post'])){if($_SESSION['new-authority']==1){echo 'selected';}}?>>管理者</option>
                                                    </select>
                                                </div>

                                                <div class="col-4 mt-2">
                                                    <label for="mail-new-post" class="form-label ">メールアドレス:</label>
                                                    <input type="text" class="form-control <?php echo $error->invalid('new-mail');?>" id="mail-new-post" name="mail-new-post" value="<?php if(isset($_POST['mail-new-post'])){echo $_SESSION['new-mail'];}else{echo "";}?>">
                                                    <?php echo $error->getError('new-mail');?>
                                                </div>

                                                <div class="col-4 mt-2">
                                                    <label for="password-new-post" class="form-label">パスワード:</label>
                                                    <input type="password" class="form-control <?php echo $error->invalid('new-password');?>" id="password-new-post" name="password-new-post" value="<?php if(isset($_POST['password-new-post'])){echo $_SESSION['new-password'];}else{echo "";}?>">
                                                    <?php echo $error->getError('new-password');?>
                                                </div>

                                                <div class="col-4 mt-2">
                                                    <label for="confirmationPassword-new-post" class="form-label ">確認用パスワード:</label>
                                                    <input type="password" class="form-control <?php echo $error->invalid('new-confirmationPassword');?>" id="confirmationPassword-new-post" name="confirmationPassword-new-post" value="<?php if(isset($_POST['confirmationPassword-new-post'])){echo $_SESSION['new-confirmationPassword'];}else{echo "";}?>">
                                                    <?php echo $error->getError('new-confirmationPassword');?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <label for="cancelNew-post">
                                        <button type="button" id="cancelNew-post" name="cancelNew-post" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                    </label>
                                    <label for="newAppModal-post">
                                        <input type="submit" id="newAppModal-post" name="newAppModal-post" class="btn btn-success" value="登録">
                                    </label>
                                </div><!-- /.modal-footer -->
                            </form>
                        </div><!-- /.modal-content -->
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

                <div class="modal fade modal-xl" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <h1 class="modal-title fs-5 text-white" id="newModalLabel">社員更新</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="閉じる"></button>
                            </div>
                            <div class="card mx-2 my-3">
                                <form method="POST">
                                    <div class="card-header">
                                        社員情報
                                    </div>
                                    <div class="card-body">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-3">
                                                    <label for="number-update-post" class="form-label">社員番号:</label>
                                                    <input type="text" class="form-control <?php echo $error->invalid('update-number');?>" id="number-update-post" name="number-update-post" value="<?php if(isset($_POST['number-update-post'])){echo $_SESSION['update-number'];}else{echo '';}?>">
                                                    <?php echo $error->getError('update-number');?>
                                                </div>

                                                <div class="col-4">
                                                    <label for="name-update-post" class="form-label">社員名:</label>
                                                    <input type="text" class="form-control <?php echo $error->invalid('update-name')?>" id="name-update-post" name="name-update-post" value="<?php if(isset($_POST['name-update-post'])){echo $_SESSION['update-name'];}else{echo '';}?>">
                                                    <?php echo $error->getError('update-name');?>
                                                </div>

                                                <div class="col-3">
                                                    <label for="calendar-update-post" class="form-label ">入社日:</label>
                                                    <input type="date" class="form-control <?php echo $error->invalid('update-start_date')?>" id="calendar-update-post" name="calendar-update-post" value="<?php if(isset($_POST['calendar-update-post'])){echo $_SESSION['update-calendar'];}else{echo '';}?>">
                                                    <?php echo $error->getError('update-start_date');?>
                                                </div>
                                                <div class="col-2">
                                                    <label for="authority-update-post" class="form-label">権限:</label>
                                                    <select class="form-select" id="authority-update-post" name="authority-update-post">
                                                        <option id="authority0" value="0" <?php if(isset($_POST['authority-update-post'])){if($_SESSION['update-authority']){echo 'selected';}}?>>一般</option>
                                                        <option id="authority1" value="1" <?php if(isset($_POST['authority-update-post'])){if($_SESSION['update-authority']){echo 'selected';}}?>>管理者</option>
                                                    </select>
                                                </div>

                                                <div class="col-4 mt-2">
                                                    <label for="mail-update-post" class="form-label ">メールアドレス:</label>
                                                    <input type="text" class="form-control"  id="mail-update-post-text" value="<?php if(isset($_SESSION['update-mail'])){echo $_SESSION['update-mail'];}?> " disabled>
                                                    <input type="hidden" id="mail-update-post" name="mail-update-post" value="<?php if(isset($_POST['mail-update-post'])){echo $_POST['mail-update-post'];}?>">
                                                </div>

                                                <div class="col-4 mt-2">
                                                    <label for="password-update-post" class="form-label ">パスワード:</label>
                                                    <input type="password" class="form-control <?php echo $error->invalid('update-password');?>" id="password-update-post" name="password-update-post" value="<?php if(isset($_POST['password-update-post'])){echo $_SESSION['update-password'];}else{echo '';}?>">
                                                    <?php echo $error->getError('update-password');?>
                                                </div>

                                                <div class="col-4 mt-2">
                                                    <label for="confirmationPassword-update-post" class="form-label ">確認用パスワード:</label>
                                                    <input type="password" class="form-control <?php echo $error->invalid('update-confirmationPassword');?>"
                                                        id="confirmationPassword-update-post" name="confirmationPassword-update-post" value="<?php if(isset($_POST['confirmationPassword-update-post'])){echo $_SESSION['update-confirmationPassword'];}else{echo '';}?>">
                                                    <?php echo $error->getError('update-confirmationPassword');?>
                                                </div>
                                                <!-- type=hidden:非表示にする。ラジオボタンのvalue値をここに格納し、送信する-->
                                                <input type="hidden" id="radio-update-id" name="radio-update-id" value="<?php if(isset($_POST['radio-update-id'])){echo $_SESSION['radio-update-id'];}else{echo '';}?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                        <label for="updateModalBtn">
                                            <input id="updateModalBtn" name="updateModalBtn" type="submit" class="btn btn-primary" value="更新">
                                        </label>
                                    </div><!-- /.modal-footer -->
                                </form>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>
                <!-- 削除モーダル -->
                <div class="modal fade" id="delModal" tabindex="-1" aria-labelledby="delModalLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <h1 class="modal-title fs-5 text-white" id="delModalLabel">社員削除</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="閉じる"></button>
                            </div>
                            <div class="modal-body">
                                選択した社員を削除しますか？
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                <form method="POST">
                                    <input type="submit" class="btn btn-danger" data-bs-dismiss="modal"
                                    id="delModalBtn" name="delModalBtn" value="削除">
                                    <input type="hidden" id="radio-del-id" name="radio-del-id" value="<?php if(isset($_POST['radio-del-id'])){echo $_SESSION['radio-del-id'];}else{echo '';}?>">
                                </form>
                            </div><!-- /.modal-footer -->
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            </div>
            <!-- </div> -->
        </div>
    </main>
</body>