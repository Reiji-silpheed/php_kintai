<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <title>ログイン</title>
    <?php
    require_once './loginClass.php';
    require_once './messageClass.php';
    require_once './dbClass.php';
    session_start();
    date_default_timezone_set('Asia/Tokyo');
    $firstDate=new DateTime();
    $error=new Message();
    $table=new dbClass();
    if(isset($_POST['mail-post']) && isset($_POST['password-post'])){
        $login=new login();
        if($login->loginCheck($_POST['mail-post'],$_POST['password-post'])){
            $_SESSION['mail']=$_POST['mail-post'];
            $rows=$table->select("SELECT * from m_employee WHERE email=:email",['email'=>$_SESSION['mail']]);
            foreach($rows as $row){
                $_SESSION['id']=$row['id'];
            }
            /* 勤怠画面に現在の年月を初期値にするように設定 */
            $firstValue=$firstDate->format('Y-m');
            $yyyy=$firstValue[0];
            $mm=$firstValue[1];
            $yyyymm="{$yyyy}{$mm}";
            $_SESSION['yyyymm']=$yyyymm;
            header('Location:./kintai.php');
        }
        else{
            $error->setError('login',"メールアドレスかパスワードが間違っています");
        }
    }
    ?>
</head> 

<body>
    <!-- カードを中央にするように調整した-->    
    <div class="vh-100 d-flex justify-content-center align-items-center">
        <div class="card w-25 position-absolute top-0 start-50 translate-middle-x">
            <div class="card-header">ログイン</div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="position-relative">
                        <label for="mail-post" class="form-label">メールアドレス:</label>
                        <!-- テキストに何も入力されていないとき以下のメッセージが出力されるようにした -->
                        <input type="text" class="form-control <?php echo $error->invalid('login');?>" id="mail-post" name="mail-post" aria-describedby="error"
                            required>
                        <?php echo $error->getError('login');?>
                    </div>
                    <div>
                        <label for="password-post" class="form-label">Password:</label>
                        <!-- type="password":入力した文字を見えなくさせる -->
                        <input type="password" class="form-control" id="password-post" name="password-post"
                            aria-describedby="validationServer01Feedback" required>
                    </div>
                    <div class="m-2 d-flex justify-content-end">
                        <input class="btn btn-success" id="loginBtn" type="submit" value="ログイン">
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>