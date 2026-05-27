<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <title>ログイン</title>
    <?php
        require_once './loginClass.php';
        $error=true;
        if(isset($_POST['mail-post']) && isset($_POST['password-post'])){
            $login=new login();
            if($login->loginCheck($_POST['mail-post'],$_POST['password-post'])){
                header('Location:../jquery_kintai/kintai.html');
            }
            else{
                $error=false;
            }
        }
    ?>
</head> 

<body>
    <!-- カードを中央にするように調整した-->    
    <div class="vh-100 d-flex justify-content-center align-items-center">
        <div class="card w-25">
            <div class="card-header">ログイン</div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="position-relative">
                        <label for="mail-post" class="form-label">メールアドレス:</label>
                        <!-- テキストに何も入力されていないとき以下のメッセージが出力されるようにした -->
                        <input type="text" class="form-control <?php if($error==false):?> is-invalid<?php endif?>" id="mail-post" name="mail-post" aria-describedby="error"
                            required>
                        <?php if($error==false):?>
                            <div id="error" class="invalid-feedback">
                                パスワードかメールアドレスが間違っています
                            </div>
                        <?php endif?>
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