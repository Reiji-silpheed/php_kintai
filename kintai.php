<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- ②：BootstrapとjQueryで使うJavascriptを読み込む -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="./style.css">
    <title>勤怠管理</title>
    <?php
    session_start();
    if($_SESSION['mail']==""){
        header('Location:./login.php');
    } 
    ?>
    <script>
        $(function () {
            let firstDate = new Date()
            let firstYear = firstDate.getFullYear();
            let firstMonth = firstDate.getMonth() + 1;
            let firstDay = firstDate.getDay()
            if (firstMonth < 10) {
                firstMonth = "0" + firstMonth
            }
            let firstValue = firstYear + "-" + firstMonth;
            $("#month").val(firstValue);
            let firstParts = firstValue.split("-");
            const year = firstParts[0];
            const month = firstParts[1];
            let holidays = {};
            $.ajax({
                url: 'https://holidays-jp.github.io/api/v1/date.json',
                method: 'get',
                dataType: 'json',
            }).then(function (json) {
                holidays = json;
                console.log(holidays);
                $("#calendar").empty();
                $("#calendar").append(CreateCalendar(year, month));
            })

            /* カレンダーのテーブルを作る関数 */
            function CreateCalendar(year, month) {
                const weeks = ['日', '月', '火', '水', '木', '金', '土']
                /* 月の初めの曜日を取得 */
                const startDateOfMonth = new Date(year, month - 1, 1).getDay();
                /* 月の最後の日付を取得 */
                const lastDateOfMonth = new Date(year, month, 0).getDate()
                var CalendarElement =
                    `<table class='table mt-2'>
                                <thead class='table-dark'>
                                    <tr>
                                        <th>日</th>
                                        <th>曜日</th>
                                        <th>区分</th>
                                        <th>開始時間</th>
                                        <th>終了時間</th>
                                        <th>昼休憩時間</th>
                                        <th>夜休憩時間</th>
                                        <th>備考</th>
                                    </tr>
                                </thead>`
                CalendarElement += "<tbody>"


                for (let w = 1; w <= lastDateOfMonth; w++) {
                    let day = w
                    let colorClass = ""
                    let date = (startDateOfMonth + w - 1) % 7
                    let week = weeks[date]
                    let yyyy = year;
                    let mm = ("0" + month).slice(-2);
                    let dd = ("0" + day).slice(-2);
                    let fullDate = `${yyyy}-${mm}-${dd}`;
                    let isHoliday = holidays[fullDate];
                    let holidayName = holidays[fullDate] || "";
                    if (date === 6) {
                        colorClass += 'bg-primary-subtle text-primary';
                    }
                    else if (date === 0 || isHoliday) {
                        colorClass += 'bg-danger-subtle text-danger';
                    }
                    CalendarElement += "<tr>"
                    CalendarElement += "<td class='" + colorClass + "'>" + day + "</td>" + "<td class='" + colorClass + "'>" + week + "</td>"
                    if (date === 0 || date === 6 || isHoliday) {
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                    <select class="select1a form-select" name="fill" readonly>
                                        <option class="holiday">休日</option>
                                        <option class="work">休出</option>
                                    </select>
                                </label>`
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="startWork" type="time" name="fill" class="form-control" readonly>
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="finishWork" type="time" name="fill" class="form-control" readonly>
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="lunch" type="time" name="fill" class="form-control" readonly>
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="dinner" type="time" name="fill" class="form-control" readonly>
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input type="text" name="fill" class="form-control"  value="${holidayName}" readonly>
                                    </label>`
                            + "</td>"
                    }
                    else {
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <select class="select1a form-select" name="fill">
                                            <option class="work">出勤</option>
                                            <option class="holiday">有給</option>
                                            <option class="holiday">欠勤</option>
                                            <option class="holiday">特休</option>
                                            <option class="holiday">代休</option>
                                            <option class="holiday">振休</option>
                                        </select>
                                    </label>`
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="startWork" type="time" name="fill" class="form-control" value="09:00">
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="finishWork" type="time" name="fill" class="form-control" value="18:00">
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="lunch" type="time" name="fill" class="form-control" value="01:00">
                                        </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input id="dinner" type="time" name="fill" class="form-control" value="00:00">
                                    </label>`
                            + "</td>"
                        CalendarElement += "<td class='" + colorClass + "'>" +
                            `<label>
                                        <input  type="text" name="fill" class="form-control" value="${holidayName}">
                                    </label>`
                            + "</td>"
                    }

                    CalendarElement += "</tr>"
                }
                CalendarElement += "</tbody></table>";
                return CalendarElement;
            }

            $(document).on("click", "#displayBtn", function () {
                let value = $("#month").val();
                $("table").remove();
                if (value === "") {
                    $("#alert").remove()
                    $("#dateCard").before('<div id="alert" class="alert alert-danger" role="alert">年月が選択されていません。</div>')
                }
                else {
                    let fullValue = $("#month").val();
                    /* カレンダーのvalue値を-で区切り、年と月に分けた */
                    let parts = fullValue.split("-");
                    const year = parts[0];
                    const month = parts[1];
                    $("#calendar").append(CreateCalendar(year, month))
                }
            })
            /* 更新ボタン */
            $(document).on("click", "#saveBtn", function () {
                $("#alert").remove()
                $("#dateCard").before('<div id="alert" class="alert alert-primary" role="alert">保存が完了しました。</div>');
                $("#saveModal").modal("hide");
            })
            /* 削除ボタン */
            $(document).on("click", "#appModalBtn", function () {
                $("#alert").remove()
                $("#dateCard").before('<div id="alert" class="alert alert-success" role="alert">申請が完了しました。</div>');
                $("#appModal").modal("hide");
                $('[name="fill"]').prop("disabled", true);
            })
            /* 働かない日は時間を入力できないようにする */
            $(document).on("change", ".select1a", function () {
                var selectedClass = $(this).find('option:selected').attr('class');
                let row = $(this).closest("tr");

                if (selectedClass === "holiday") {
                    row.find("input").val("");
                    row.find("input").prop("readonly", true);
                    row.find("td").removeClass();
                    row.find("td").addClass("bg-danger-subtle text-danger");
                } else {
                    row.find("#startWork").val("09:00");
                    row.find("#finishWork").val("18:00");
                    row.find("#lunch").val("01:00");
                    row.find("#dinner").val("01:00");
                    row.find("input").prop("readonly", false);
                    row.find("td").removeClass();
                }
            })
        })

    </script>
</head>


<body>
    <!-- メニュー画面 -->
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom border-body" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand">勤怠管理システム</a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#Navber"
                    aria-controls="Navber" aria-expanded="false" aria-label="ナビゲーションの切替">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="Navber">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <!-- 今は勤怠入力画面を開いているから勤怠入力にactive -->
                        <li class="nav-item">
                            <a id="input" class="nav-link active" aria-current="page" href="#">勤怠入力</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                マスタ管理
                            </a>
                            <ul class="dropdown-menu">
                                <li><a id="employeeMaster" class="dropdown-item" href="employeeMaster.php">社員マスタ管理</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" id="logout" class="nav-link" aria-disabled="true">ログアウト</a>
                        </label>
                            </form>
                        </li>
                        
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
    </header>
    <!-- 勤怠入力画面 -->
    <main>
        <div class="container">
            <h1>勤怠入力</h1>
            <div id="dateCard" class="card">
                <div class="card-header">
                    入力
                </div>
                <div class="card-body">
                    <label for="date">年月:</label>
                    <div class="input-group w-25">
                        <input type="month" id="month" class="form-control">
                    </div>
                    <div class="m-2 d-flex justify-content-end">
                        <button class="btn btn-info" id="displayBtn" type="button">表示</button>
                    </div>
                </div>
            </div>
            <div id="calendarCard" class="card mt-2">
                <div class="card-header">
                    カレンダー
                </div>
                <div class="card-body container">
                    <div class="m-2 d-flex justify-content-end gap-2">
                        <button class="btn btn-primary" id="saveBtn" name="fill" type="button">保存</button>
                        <button class="btn  btn-success" id="appBtn" name="fill" type="button" data-bs-toggle="modal"
                            data-bs-target="#appModal">申請</button>
                    </div>
                    <div id="calendar" class="mt-2"></div>
                </div>
            </div>
        </div>

        <!-- モーダル -->
        <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h1 class="modal-title fs-5 text-white" id="appModalLabel">勤怠申請</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                    </div>
                    <div class="modal-body">
                        <p>入力した勤怠を申請しますか？</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button id="appModalBtn" type="button" class="btn btn-success">申請</button>
                    </div><!-- /.modal-footer -->
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </main>
</body>