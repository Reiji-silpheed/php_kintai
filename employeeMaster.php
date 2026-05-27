<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- ②：BootstrapとjQueryで使うJavascriptを読み込む -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
    <title>社員マスタ管理</title>
    <?php
    session_start();
    if(isset($_SESSION['mail'])==""){
        header('Location:./login.php');
    }
    if(isset($_POST['cBtn'])){
        $_POST['number-post']="";
        $_POST['name-post']="";
        $_POST['mail-post']="";
        $_POST['calendar-post']="";
    }
    ?>
    <script>
        $(function () {
            /* クリアボタンを押したときに、テキストを初期化した */
            /* $(document).on("click", "#cBtn", function () {
                $("#searchNumber").val("");
                $("#searchName").val("");
                $("#searchMail").val("");
                $("#searchCalendar").val("");
            })  */
            $("#updateBtn").prop("disabled", true);
            $("#delBtn").prop("disabled", true)
            /* 検索ボタンを押してテキストが空だった時にエラーメッセージが出るようにした */
            $(document).on("click", "#searchBtn", function () {
                let isValid = true;
                /* テキスト取得 */
                $("#searchNumber").removeClass("is-invalid");
                $("#searchName").removeClass("is-invalid");
                $("#searchMail").removeClass("is-invalid");
                $("#searchCalendar").removeClass("is-invalid");
                const searchNumber = $("#searchNumber").val();
                const searchName = $("#searchName").val();
                const searchMail = $("#searchMail").val();
                const searchCalendar = $("#searchCalendar").val();
                if (searchNumber === "") {
                    $("#searchNumber").addClass("is-invalid");
                    isValid = false;
                }
                if (searchName === "") {
                    $("#searchName").addClass("is-invalid");
                    isValid = false;
                }
                if (searchMail === "") {
                    $("#searchMail").addClass("is-invalid");
                    isValid = false;
                }
                if (searchCalendar === "") {
                    $("#searchCalendar").addClass("is-invalid");
                    isValid = false;
                }
                if (isValid === true) {
                    $("#searchCard").before('<div class="alert alert-warning" role="alert">検索結果がありませんでした。</div>')
                }
            })
            /* 更新ボタンを押したときに検索結果でラジオボタンのついた社員データをモーダルに表示させた */
            $(document).on("click", "#updateBtn", function () {
                const selected = $("input[name='radio']:checked")
                const row = selected.closest("tr");
                const number = row.find("td").eq(1).text();
                $("#updateNumber").val(number);
                const name = row.find("td").eq(2).text();
                $("#updateName").val(name);
                const calender = row.find("td").eq(4).text();
                $("#updateCalender").val(calender);
                const mail = row.find("td").eq(3).text();
                $("#updateMail").val(mail);
                $("#updateMail").prop("disabled", true)
            })
            /* 登録ボタンを押して、テキストが空だったらエラーメッセージが出るようにした。（パスワードが一致していないときも同様） */
            $(document).on("click", "#newAppModalBtn", function () {
                let isValid = true;
                /* テキスト取得 */
                $("#newNumber").removeClass("is-invalid");
                $("#newName").removeClass("is-invalid");
                $("#newMail").removeClass("is-invalid");
                $("#newCalendar").removeClass("is-invalid");
                $("#newPassword").removeClass("is-invalid");
                $("#newConfirmationPassword").removeClass("is-invalid");
                const number = $("#newNumber").val();
                const name = $("#newName").val();
                const mail = $("#newMail").val();
                const calendar = $("#newCalendar").val();
                const password = $("#newPassword").val();
                const confirmationPassword = $("#newConfirmationPassword").val();
                if (number === "") {
                    $("#newNumber").addClass("is-invalid");
                    isValid = false;
                }
                if (name === "") {
                    $("#newName").addClass("is-invalid");
                    isValid = false;
                }
                if (mail === "") {
                    $("#newMail").addClass("is-invalid");
                    isValid = false;
                }
                if (calendar === "") {
                    $("#newCalendar").addClass("is-invalid");
                    isValid = false;
                }
                if (password === "") {
                    $("#newPassword").addClass("is-invalid");
                    isValid = false;
                }
                if (confirmationPassword === "") {
                    $("#newConfirmationPassword").addClass("is-invalid");
                    isValid = false;
                }
                if (password !== confirmationPassword) {
                    $("#newPasswordError").text("パスワードが一致していません。");
                    $("#newConfirmationPasswordError").text("パスワードが一致していません。");
                    $("#newPassword").addClass("is-invalid");
                    $("#newConfirmationPassword").addClass("is-invalid");
                    isValid = false;
                }
                if (isValid === true) {
                    $("#newModal").modal("hide");
                }
            });
            /*更新ボタンを押して、テキストが空だったらエラーメッセージが出るようにした。（パスワードが一致していないときも同様） */
            $(document).on("click", "#updateModalBtn", function () {
                let isValid = true;
                /* テキスト取得 */
                $("#updateNumber").removeClass("is-invalid");
                $("#updateName").removeClass("is-invalid");
                $("#updateMail").removeClass("is-invalid");
                $("#updateCalender").removeClass("is-invalid");
                $("#updatePassword").removeClass("is-invalid");
                $("#updateConfirmationPassword").removeClass("is-invalid");

                const number = $("#updateNumber").val();
                const name = $("#updateName").val();
                const mail = $("#updateMail").val();
                const calender = $("#updateCalender").val();
                const password = $("#updatePassword").val();
                const confirmationPassword = $("#updateConfirmationPassword").val();
                if (number === "") {
                    $("#updateNumber").addClass("is-invalid");
                    isValid = false;
                }
                if (name === "") {
                    $("#updateName").addClass("is-invalid");
                    isValid = false;
                }
                if (mail === "") {
                    $("#updateMail").addClass("is-invalid");
                    isValid = false;
                }
                if (calender === "") {
                    $("#updateCalender").addClass("is-invalid");
                    isValid = false;
                }

                if (password !== confirmationPassword) {
                    $("#updatePassword").addClass("is-invalid");
                    $("#updateConfirmationPassword").addClass("is-invalid");
                    isValid = false;
                }
                if (isValid === true) {
                    $("#updateModal").modal("hide");
                    $("#updateBtn").prop("disabled", true);
                    $("#delBtn").prop("disabled", true)
                }
            });
            /* 検索結果でラジオボタンのついた社員を削除するようにした*/
            $(document).on("click", "#delModalBtn", function () {
                // チェックされているラジオボタンを取得
                const selected = $("input[name='radio']:checked");
                selected.closest("tr").remove();
                $("#updateBtn").prop("disabled", true);
                $("#delBtn").prop("disabled", true)
            });
            /* ラジオボタンが点灯したときに、更新ボタンと削除ボタンを押せるようにした */
            $(document).on("change", "input[name='radio']", function () {
                if ($("input[name='radio']:checked").length === 0) {
                    $("#updateBtn").prop("disabled", true);
                    $("#delBtn").prop("disabled", true)
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
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" id="logout" class="nav-link" aria-disabled="true">ログアウト</a>
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
            <!-- 検索条件カード -->
            <div class="container">
                <div class="card" id="searchCard">
                    <div class="card-header">
                        検索条件
                    </div>
                    <!-- 社員名とメールアドレスの枠を広くするために、グリッドシステムを使用した -->
                    <form method="POST">
                        <div class="card-body">
                            <div class="container">
                                <div class="row">
                                    <div class="col-2">
                                        <label for="number-post" class="form-label">社員番号:</label>
                                        <!-- 何も入力されていないときにエラーが出るようにした -->
                                        <input type="text" class="form-control" id="number-post" name="number-post" value="<?php if(isset($_POST['number-post'])){echo $_POST['number-post'];}?>">
                                        <div class="invalid-feedback">
                                            社員番号が入力されていません。
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <label for="name-post" class="form-label">社員名:</label>
                                        <input type="text" class="form-control" id="name-post" name="name-post" value="<?php if(isset($_POST['name-post'])){echo $_POST['name-post'];}?>">
                                        <div class="invalid-feedback">
                                            社員名が入力されていません。
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <label for="mail-post" class="form-label">メールアドレス:</label>
                                        <input type="text" class="form-control" id="mail-post" name="mail-post" value="<?php if(isset($_POST['mail-post'])){echo $_POST['mail-post'];}?>">
                                        <div id="error" class="invalid-feedback">
                                            メールアドレスが入力されていません。
                                        </div>
                                    </div>

                                    <div class="col-2">
                                        <label for="calendar-post" class="form-label">入社日:</label>
                                        <input type="date" class="form-control" id="calendar-post" name="calendar-post" value="<?php if(isset($_POST['calendar-post'])){echo $_POST['calendar-post'];}?>">
                                        <div class="invalid-feedback">
                                            カレンダーが入力されていません。
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="container">
                                <div class="d-grid gap-2 mt-2 d-md-flex justify-content-md-end">
                                    <input type="submit" class="btn btn-warning" name="cBtn" value="クリア">
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="radio" id="radio1" />
                                        </td>
                                        <td>001</td>
                                        <td>研修太朗</td>
                                        <td>kensyuu.tarou@example.com</td>
                                        <td>2020-04-01</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="radio" id="radio2" />
                                        </td>
                                        <td>002</td>
                                        <td>研修次郎</td>
                                        <td>kensyuu.jirou@example.com</td>
                                        <td>2020-04-01</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="radio" id="radio3" />
                                        </td>
                                        <td>003</td>
                                        <td>研修三朗</td>
                                        <td>kensyuu.saburou@example.com</td>
                                        <td>2020-04-01</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="radio" id="radio4" />
                                        </td>
                                        <td>004</td>
                                        <td>研修四子</td>
                                        <td>kensyuu.yoshiko@example.com</td>
                                        <td>2020-04-01</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="radio" id="radio5" />
                                        </td>
                                        <td>005</td>
                                        <td>研修五子</td>
                                        <td>kensyuu.itsuko@example.com</td>
                                        <td>2020-04-01</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- ページネイション -->
                        <nav class="d-flex align-items-center justify-content-center">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a class="page-link">前</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-current="page">2</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">次</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>


                <!-- 登録モーダル -->
                <div class="modal fade modal-xl" id="newModal" tabindex="-1" aria-labelledby="newModalLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <h1 class="modal-title fs-5 text-white" id="newModalLabel">社員登録</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="閉じる"></button>
                            </div>
                            <div class="card mx-2 my-3">
                                <div class="card-header">
                                    社員情報
                                </div>
                                <div class="card-body">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-4">
                                                <label class="form-label">社員番号:</label>
                                                <input type="text" class="form-control" id="newNumber">
                                                <div class="invalid-feedback">
                                                    社員番号が入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <label class="form-label">社員名:</label>
                                                <input type="text" class="form-control" id="newName">
                                                <div class="invalid-feedback">
                                                    社員名が入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <label class="form-label">入社日:</label>
                                                <input type="date" class="form-control" id="newCalendar">
                                                <div class="invalid-feedback">
                                                    入社日が入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4 mt-2">
                                                <label class="form-label">メールアドレス:</label>
                                                <input type="text" class="form-control" id="newMail">
                                                <div class="invalid-feedback">
                                                    メールアドレスが入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4 mt-2">
                                                <label class="form-label">パスワード:</label>
                                                <input type="password" class="form-control" id="newPassword">
                                                <div class="invalid-feedback" id="newPasswordError">
                                                    パスワードが入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4 mt-2">
                                                <label class="form-label">確認用パスワード:</label>
                                                <input type="password" class="form-control"
                                                    id="newConfirmationPassword">
                                                <div class="invalid-feedback" id="newConfirmationPasswordError">
                                                    確認用パスワードが入力されていません。
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                <button id="newAppModalBtn" type="button" class="btn btn-success">登録</button>
                            </div><!-- /.modal-footer -->
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <!-- 更新モーダル -->
                <div class="modal fade modal-xl" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info">
                                <h1 class="modal-title fs-5 text-white" id="newModalLabel">社員登録</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="閉じる"></button>
                            </div>
                            <div class="card mx-2 my-3">
                                <div class="card-header">
                                    社員情報
                                </div>
                                <div class="card-body">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-4">
                                                <label class="form-label">社員番号:</label>
                                                <input type="text" class="form-control" id="updateNumber">
                                                <div class="invalid-feedback">
                                                    社員番号が入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <label class="form-label">社員名:</label>
                                                <input type="text" class="form-control" id="updateName">
                                                <div class="invalid-feedback">
                                                    社員名が入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <label class="form-label">入社日:</label>
                                                <input type="date" class="form-control" id="updateCalender">
                                                <div class="invalid-feedback">
                                                    カレンダーが入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4 mt-2">
                                                <label class="form-label">メールアドレス:</label>
                                                <input type="text" class="form-control" id="updateMail">
                                                <div class="invalid-feedback">
                                                    メールアドレスが入力されていません。
                                                </div>
                                            </div>

                                            <div class="col-4 mt-2">
                                                <label class="form-label">パスワード:</label>
                                                <input type="password" class="form-control" id="updatePassword">
                                                <div class="invalid-feedback" id="updatePasswordError">
                                                    パスワードが間違っています。
                                                </div>
                                            </div>

                                            <div class="col-4 mt-2">
                                                <label class="form-label">確認用パスワード:</label>
                                                <input type="password" class="form-control"
                                                    id="updateConfirmationPassword">
                                                <div class="invalid-feedback" id="updateConfirmationPasswordError">
                                                    パスワードが間違っています。
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                <button id="updateModalBtn" type="button" class="btn btn-primary">更新</button>
                            </div><!-- /.modal-footer -->
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

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
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                                    id="delModalBtn">削除</button>
                            </div><!-- /.modal-footer -->
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            </div>
            <!-- </div> -->
        </div>

    </main>
</body>