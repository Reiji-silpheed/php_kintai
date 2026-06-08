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
    require_once './messageClass.php';
    require_once './dbClass.php';
    require_once './loginClass.php';
    date_default_timezone_set('Asia/Tokyo');
    $firstDate=new DateTime();
    $firstValue=$firstDate->format('Y-m');
    session_start();
    if($_SESSION['mail']==""){
        header('Location:./login.php');
    } 
    if(isset($_POST['displayBtn'])){
        $_SESSION['display-month']=$_POST['month-post'];
    }
    $error=new Message();
    $table=new dbClass();
    $login=new login();
    $rows=$table->select('SELECT * FROM m_holiday',[]);
    function createCalendar($rows){
        $table=new dbClass();
        $weeks=['日','月','火','水','木','金','土'];
        $lastDateOfMonth=date('d',strtotime('last day of '.$_SESSION['display-month']));
        $firstWeekDay=date('w',strtotime($_SESSION['display-month'].'-01'));
        $firstParts=explode("-",$_SESSION['display-month']);
        $yyyy=$firstParts[0];
        $mm=$firstParts[1];
        $yyyymm="{$yyyy}{$mm}";
        $calendars=$table->select("SELECT * 
                        FROM t_attendance_head
                        WHERE  employee_id=:employee_id and yyyymm=:yyyymm"
                        ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$yyyymm]);
        if(empty($calendars)){
            $CalendarElement=
            "<table class='table mt-2'>
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
                </thead>";
            $CalendarElement .= "<tbody>";
            for($w=1;$w<=$lastDateOfMonth;$w++){
                $holiday=false;
                $holidayValue='';
                $day='0'.$w;
                $colorClass="";
                $date=($firstWeekDay+$w-1)%7;
                $yyyy=$firstParts[0];
                $mm=$firstParts[1];
                $dd=substr($day,-2);
                $fullDate="{$yyyy}-{$mm}-{$dd}";
                foreach($rows as $row){
                    if($fullDate==$row['yyyymmdd']){
                        $holiday=true;
                        $holidayValue=$row['holiday_name'];
                    }
                }
                if($date==6){
                    $colorClass = 'bg-primary-subtle text-primary';
                }
                if($date==0 || $holiday){
                    $colorClass = 'bg-danger-subtle text-danger';
                }
                $CalendarElement .= "<tr>";
                $CalendarElement .= "<td class='$colorClass'>$w</td>";
                $CalendarElement .= "<label><input type='hidden' class='$colorClass' name='day[]' value={$w}></label>";
                $CalendarElement .= "<td class='$colorClass'>$weeks[$date]</td>";
                if ($date === 0 || $date === 6 || $holiday) {
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <select class='select1a form-select' name='kbn[]' readonly>
                                                    <option class='holiday' value=2 >休日</option>
                                                    <option class='work' value=4 >休出</option>
                                                </select>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='startWork' type='time' name='start[]' class='form-control' readonly>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='finishWork' type='time' name='end[]' class='form-control' readonly>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='lunch' type='time' name='lunch[]' class='form-control' readonly>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='dinner' type='time' name='dinner[]' class='form-control' readonly>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input type='text' name='text[]' class='form-control' value='$holidayValue' readonly>
                                            </label>
                                        </td>";
                }
                
                else {
                    
                        $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <select class='select1a form-select' name='kbn[]'>
                                                    <option class='work'value=1 >出勤</option>
                                                    <option class='holiday' value=3>有給</option>
                                                    <option class='holiday'value=5>欠勤</option>
                                                    <option class='holiday' value=6>特休</option>
                                                    <option class='holiday' value=7>代休</option>
                                                    <option class='holiday' value=8>振休</option>
                                                </select>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='startWork' type='time' name='start[]' class='form-control' value='09:00'>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='finishWork' type='time' name='end[]' class='form-control' value='18:00'>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='lunch' type='time' name='lunch[]' class='form-control' value='01:00'>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='dinner' type='time' name='dinner[]' class='form-control' value='00:00'>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input type='text' name='text[]' class='form-control' value='$holidayValue'>
                                            </label>
                                        </td>";
                }
                $CalendarElement .= "</tr>";
            }
            $CalendarElement .= "</tbody></table>";
            return $CalendarElement;
        }
        foreach($calendars as $calendar){
            if($calendar['status']==0){
                $CalendarElement=
                "<table class='table mt-2'>
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
                    </thead>";

                $CalendarElement .= "<tbody>";
                for($w=1;$w<=$lastDateOfMonth;$w++){
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $selected1='';
                    $selected2='';
                    $selected3='';
                    $selected4='';
                    $selected5='';
                    $selected6='';
                    $selected7='';
                    $selected8='';
                    $readonly='';
                    $lists=$table->select("SELECT * FROM t_attendance_detail WHERE head_id=:head_id and day=:day",['head_id'=>$_SESSION['head_id'],'day'=>$w]);
                    foreach($lists as $list){
                        $holiday=false;
                        $holidayValue='';
                        $day='0'.$w;
                        $colorClass="";
                        $date=($firstWeekDay+$w-1)%7;
                        $yyyy=$firstParts[0];
                        $mm=$firstParts[1];
                        $dd=substr($day,-2);
                        $fullDate="{$yyyy}-{$mm}-{$dd}";
                        foreach($rows as $row){
                            if($fullDate==$row['yyyymmdd']){
                                $holiday=true;
                                $holidayValue=$row['holiday_name'];
                            }
                        }
                        if($list['kbn']==1){
                            $selected1='selected';
                            $start_time=$list['start_time'];
                            $end_time=$list['end_time'];
                            $rest_time=$list['rest_time'];
                            $night_rest_time=$list['night_rest_time'];
                        }
                        if($list['kbn']==2){
                            $selected2='selected';
                            $readonly='readonly';
                            $colorClass='bg-danger-subtle text-danger';
                            $start_time='';
                            $end_time='';
                            $rest_time='';
                            $night_rest_time='';
                        }
                        if($list['kbn']==3){
                            $selected3='selected';
                            $readonly='readonly';
                            $colorClass='bg-danger-subtle text-danger';
                            $start_time='';
                            $end_time='';
                            $rest_time='';
                            $night_rest_time='';
                        }
                        if($list['kbn']==4){
                            $selected4='selected';
                            $start_time=$list['start_time'];
                            $end_time=$list['end_time'];
                            $rest_time=$list['rest_time'];
                            $night_rest_time=$list['night_rest_time'];
                        }
                        if($list['kbn']==5){
                            $selected5='selected';
                            $readonly='readonly';
                            $colorClass='bg-danger-subtle text-danger';
                        }
                        if($list['kbn']==6){
                            $selected6='selected';
                            $readonly='readonly';
                            $colorClass='bg-danger-subtle text-danger';
                            $start_time='';
                            $end_time='';
                            $rest_time='';
                            $night_rest_time='';
                        }
                        if($list['kbn']==7){
                            $selected7='selected';
                            $readonly='readonly';
                            $colorClass='bg-danger-subtle text-danger';
                            $start_time='';
                            $end_time='';
                            $rest_time='';
                            $night_rest_time='';
                        }
                        if($list['kbn']==8){
                            $selected8='selected';
                            $readonly='readonly';
                            $colorClass='bg-danger-subtle text-danger';
                            $start_time='';
                            $end_time='';
                            $rest_time='';
                            $night_rest_time='';
                        }
                        if((int)$list['kbn']!==1 && (int)$list['kbn']!==4 && $date==6){
                            $colorClass = 'bg-primary-subtle text-primary';
                        }
                        if((int)$list['kbn']!==1 && (int)$list['kbn']!==4 && ($date==0 || $holiday)){
                            $colorClass = 'bg-danger-subtle text-danger';
                        }
                        $CalendarElement .= "<tr>";
                        $CalendarElement .= "<td class='$colorClass'>$w</td>";
                        $CalendarElement .= "<label><input type='hidden' name='day[]' value={$list['day']}></label>";
                        $CalendarElement .= "<td class='$colorClass'>$weeks[$date]</td>";
                        if ($date === 0 || $date === 6 || $holiday) {
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <select class='select1a form-select' name='kbn[]' value={$list['kbn']} {$readonly}>
                                                            <option class='holiday' value=2 {$selected2}>休日</option>
                                                            <option class='work' value=4 {$selected4}>休出</option>
                                                        </select>
                                                    </label>
                                                </td>";
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='startWork' type='time' name='start[]' class='form-control' value='{$start_time}' {$readonly}>
                                                    </label>
                                                </td>";
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='finishWork' type='time' name='end[]' class='form-control' value='{$end_time}' {$readonly}>
                                                    </label>
                                                </td>";
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='lunch' type='time' name='lunch[]' class='form-control' value='{$rest_time}' {$readonly}>
                                                    </label>
                                                </td>";
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='dinner' type='time' name='dinner[]' class='form-control' value='{$night_rest_time}' {$readonly}>
                                                    </label>
                                                </td>";
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input type='text' name='text[]' class='form-control' value={$holidayValue}>
                                                    </label>
                                                </td>";
                        }
                        else {
                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <select class='select1a form-select' name='kbn[]' value={$list['kbn']}>
                                                            <option class='work'value=1 {$selected1}>出勤</option>
                                                            <option class='holiday' value=3 {$selected3}>有給</option>
                                                            <option class='holiday' value=5 {$selected5}>欠勤</option>
                                                            <option class='holiday' value=6 {$selected6}>特休</option>
                                                            <option class='holiday' value=7 {$selected7}>代休</option>
                                                            <option class='holiday' value=8 {$selected8}>振休</option>
                                                        </select>
                                                    </label>
                                                </td>";

                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='startWork' type='time' name='start[]' class='form-control' value='{$start_time}' {$readonly}>
                                                    </label>
                                                </td>";

                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='finishWork' type='time' name='end[]' class='form-control' value='{$end_time}' {$readonly}>
                                                    </label>
                                                </td>";

                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='lunch' type='time' name='lunch[]' class='form-control' value='{$rest_time}' {$readonly}>
                                                    </label>
                                                </td>";

                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input id='dinner' type='time' name='dinner[]' class='form-control' value='{$night_rest_time}' {$readonly}>
                                                    </label>
                                                </td>";

                            $CalendarElement .= "<td class='$colorClass'>
                                                    <label>
                                                        <input type='text' name='text[]' class='form-control' value='{$holidayValue}'>
                                                    </label>
                                                </td>";
                        }
                        $CalendarElement .= "</tr>";
                    }
                }
                $CalendarElement .= "</tbody></table>";
                return $CalendarElement;        
            }
            if($calendar['status']==1){
                    $CalendarElement=
                    "<table class='table mt-2'>
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
                        </thead>";

                    $CalendarElement .= "<tbody>";
                    for($w=1;$w<=$lastDateOfMonth;$w++){
                        $start_time='';
                        $end_time='';
                        $rest_time='';
                        $night_rest_time='';
                        $selected1='';
                        $selected2='';
                        $selected3='';
                        $selected4='';
                        $selected5='';
                        $selected6='';
                        $selected7='';
                        $selected8='';
                        $readonly='';
                        $lists=$table->select("SELECT * FROM t_attendance_detail WHERE head_id=:head_id and day=:day",['head_id'=>$_SESSION['head_id'],'day'=>$w]);
                        foreach($lists as $list){
                            $holiday=false;
                            $holidayValue='';
                            $day='0'.$w;
                            $colorClass="";
                            $date=($firstWeekDay+$w-1)%7;
                            $yyyy=$firstParts[0];
                            $mm=$firstParts[1];
                            $dd=substr($day,-2);
                            $fullDate="{$yyyy}-{$mm}-{$dd}";
                            foreach($rows as $row){
                                if($fullDate==$row['yyyymmdd']){
                                    $holiday=true;
                                    $holidayValue=$row['holiday_name'];
                                }
                            }
                            if($list['kbn']==1){
                                $selected1='selected';
                                $start_time=$list['start_time'];
                                $end_time=$list['end_time'];
                                $rest_time=$list['rest_time'];
                                $night_rest_time=$list['night_rest_time'];
                            }
                            if($list['kbn']==2){
                                $selected2='selected';
                                $readonly='readonly';
                                $colorClass='bg-danger-subtle text-danger';
                                $start_time='';
                                $end_time='';
                                $rest_time='';
                                $night_rest_time='';
                            }
                            if($list['kbn']==3){
                                $selected3='selected';
                                $readonly='readonly';
                                $colorClass='bg-danger-subtle text-danger';
                                $start_time='';
                                $end_time='';
                                $rest_time='';
                                $night_rest_time='';
                            }
                            if($list['kbn']==4){
                                $selected4='selected';
                                $start_time=$list['start_time'];
                                $end_time=$list['end_time'];
                                $rest_time=$list['rest_time'];
                                $night_rest_time=$list['night_rest_time'];
                            }
                            if($list['kbn']==5){
                                $selected5='selected';
                                $readonly='readonly';
                                $colorClass='bg-danger-subtle text-danger';
                            }
                            if($list['kbn']==6){
                                $selected6='selected';
                                $readonly='readonly';
                                $colorClass='bg-danger-subtle text-danger';
                                $start_time='';
                                $end_time='';
                                $rest_time='';
                                $night_rest_time='';
                            }
                            if($list['kbn']==7){
                                $selected7='selected';
                                $readonly='readonly';
                                $colorClass='bg-danger-subtle text-danger';
                                $start_time='';
                                $end_time='';
                                $rest_time='';
                                $night_rest_time='';
                            }
                            if($list['kbn']==8){
                                $selected8='selected';
                                $readonly='readonly';
                                $colorClass='bg-danger-subtle text-danger';
                                $start_time='';
                                $end_time='';
                                $rest_time='';
                                $night_rest_time='';
                            }
                            if((int)$list['kbn']!==1 && (int)$list['kbn']!==4 && $date==6){
                                $colorClass = 'bg-primary-subtle text-primary';
                            }
                            if((int)$list['kbn']!==1 && (int)$list['kbn']!==4 && ($date==0 || $holiday)){
                                $colorClass = 'bg-danger-subtle text-danger';
                            }
                            $CalendarElement .= "<tr>";
                            $CalendarElement .= "<td class='$colorClass'>$w</td>";
                            $CalendarElement .= "<label><input type='hidden' name='day[]' value={$list['day']}></label>";
                            $CalendarElement .= "<td class='$colorClass'>$weeks[$date]</td>";
                            if ($date === 0 || $date === 6 || $holiday) {
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <select class='select1a form-select' name='kbn[]' value={$list['kbn']} {$readonly} disabled>
                                                                <option class='holiday' value=2 {$selected2}>休日</option>
                                                                <option class='work' value=4 {$selected4}>休出</option>
                                                            </select>
                                                        </label>
                                                    </td>";
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='startWork' type='time' name='start[]' class='form-control' value='{$start_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='finishWork' type='time' name='end[]' class='form-control' value='{$end_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='lunch' type='time' name='lunch[]' class='form-control' value='{$rest_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='dinner' type='time' name='dinner[]' class='form-control' value='{$night_rest_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input type='text' name='text[]' class='form-control' value='{$holidayValue}' disabled>
                                                        </label>
                                                    </td>";
                            }
                            else {
                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <select class='select1a form-select' name='kbn[]' value={$list['kbn']} disabled>
                                                                <option class='work'value=1 {$selected1}>出勤</option>
                                                                <option class='holiday' value=3 {$selected3}>有給</option>
                                                                <option class='holiday' value=5 {$selected5}>欠勤</option>
                                                                <option class='holiday' value=6 {$selected6}>特休</option>
                                                                <option class='holiday' value=7 {$selected7}>代休</option>
                                                                <option class='holiday' value=8 {$selected8}>振休</option>
                                                            </select>
                                                        </label>
                                                    </td>";

                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='startWork' type='time' name='start[]' class='form-control' value='{$start_time}' {$readonly} disabled> 
                                                        </label>
                                                    </td>";

                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='finishWork' type='time' name='end[]' class='form-control' value='{$end_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";

                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='lunch' type='time' name='lunch[]' class='form-control' value='{$rest_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";

                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input id='dinner' type='time' name='dinner[]' class='form-control' value='{$night_rest_time}' {$readonly} disabled>
                                                        </label>
                                                    </td>";

                                $CalendarElement .= "<td class='$colorClass'>
                                                        <label>
                                                            <input type='text' name='text[]' class='form-control' value='{$holidayValue}' disabled>
                                                        </label>
                                                    </td>";
                            }
                            $CalendarElement .= "</tr>";
                        }
                    }  
                    $CalendarElement .= "</tbody></table>";      
                    return $CalendarElement;       
                }
        }
    }
    ?> 
    <script>
        $(function () {
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
                    row.find("#dinner").val("00:00");
                    row.find("input").prop("readonly", false);
                    row.find("td").removeClass();
                }
            })
            $(document).on("click","#displayBtn",function(){
                $("#alert").remove();
            })
            $(document).on("click","#saveBtn",function(){
                $("#alert").remove();
            })
            $(document).on("click","#appBtn",function(){
                $('#alert').remove();
            })
            /* 申請ボタン */
            $(document).on("click", "#appModalBtn", function () {
                $("#alert").remove();
                $("#appModal").modal("hide");
                $('[name="fill"]').prop("disabled", true);
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
            <?php
            if(isset($_POST['displayBtn'])){
                if($_SESSION['display-month']==''){
                    echo $error->alert('alert-danger',"年月が選択されていません。");
                }
            }
            if(isset($_POST['saveBtn'])){
                echo $error->alert('alert-primary',"保存が完了しました。");
            }
            if(isset($_POST['appModalBtn'])){
                echo $error->alert('alert-success','申請が完了しました。');
                $table->iud('UPDATE t_attendance_head SET status=1 WHERE employee_id=:employee_id and yyyymm=:yyyymm',['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
            }
            ?>
            <form method="POST">
                <div id="dateCard" class="card">
                    <div class="card-header">
                        入力
                    </div>
                    <div class="card-body">
                        <label for="date">年月:</label>
                        <div class="input-group w-25">
                            <input type="month" id="month-post" name="month-post" class="form-control" value="<?php if(isset($_POST['month-post'])){echo $_SESSION['display-month'];}elseif(isset($_POST['saveBtn'])){echo $_SESSION['display-month'];}else{echo $firstValue;}?>">
                        </div>
                        <div class="m-2 d-flex justify-content-end">
                            <input class="btn btn-info" id="displayBtn" name="displayBtn" type="submit" value="表示">
                        </div>
                    </div>
                </div>
            </form>
            <div id="calendarCard" class="card mt-2">
                <form method="POST">
                    <div class="card-header">
                    カレンダー
                    </div>
                    <div class="card-body container">
                        <div class="m-2 d-flex justify-content-end gap-2">
                            <input class="btn btn-primary" id="saveBtn" name="saveBtn" type="submit" value="保存">
                            <button class="btn  btn-success" id="appBtn" name="appBtn" type="button" data-bs-toggle="modal"
                                data-bs-target="#appModal">申請</button>
                        </div>
                        <div id="calendar" class="mt-2">
                            <?php
                            if(isset($_POST['displayBtn'])){
                                if($_SESSION['display-month']!==''){
                                    $firstParts=explode("-",$_SESSION['display-month']);
                                    $yyyy=$firstParts[0];
                                    $mm=$firstParts[1];
                                    $yyyymm="{$yyyy}{$mm}";
                                    $_SESSION['yyyymm']=$yyyymm;
                                    echo createCalendar($rows);
                                }
                            }
                            ?>
                            <?php
                            if(isset($_POST['saveBtn'])){
                                function timeToSeconds($rest){
                                    if(empty($rest)){
                                        return 0;
                                    }
                                    else{
                                        $list=explode(":",$rest);
                                        return (int)$list[0]*3600+(int)$list[1]*60;
                                    }
                                }                                
                                $calendar=$table->select('SELECT * 
                                FROM t_attendance_head 
                                WHERE employee_id=:employee_id and yyyymm=:yyyymm'
                                ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
                                $lastDateOfMonth=date('d',strtotime('last day of '.$_SESSION['display-month']));
                                $day=[];
                                foreach($_POST['day'] as $value){
                                    $day[]=$value;
                                }
                                $kbn=[];
                                foreach($_POST['kbn'] as $value){
                                    $kbn[]=$value;
                                }
                                $start_time=[];
                                foreach($_POST['start'] as $value){
                                    $start_time[]=$value;
                                }
                                $end_time=[];
                                foreach($_POST['end'] as $value){
                                    $end_time[]=$value;
                                }
                                $rest_time=[];
                                foreach($_POST['lunch'] as $value){
                                    $rest_time[]=$value;
                                }
                                $night_rest_time=[];
                                foreach($_POST['dinner'] as $value){
                                    $night_rest_time[]=$value;
                                }
                                $remarks=[];
                                foreach($_POST['text'] as $value){
                                    $remarks[]=$value;
                                }
                                $work_time=[];
                                $operation=[];
                                $over=[];
                                $over_time=[];
                                if(empty($calendar)){
                                    try{
                                        $table->begin();
                                        $table->iud('INSERT INTO t_attendance_head (employee_id,yyyymm,status) VALUES(:employee_id,:yyyymm,0)',['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
                                        $head_ids=$table->select('SELECT * FROM t_attendance_head WHERE employee_id=:employee_id',['employee_id'=>$_SESSION['id']]);
                                        foreach($head_ids as $head_id){
                                            $_SESSION['head_id']=$head_id['id'];
                                        }
                                        for($i=0;$i<$lastDateOfMonth;$i++){
                                            $work_time[]=strtotime($end_time[$i])-strtotime($start_time[$i])-timeToSeconds($rest_time[$i])-timeToSeconds($night_rest_time[$i]);
                                            $operation[]=gmdate('H:i:s',$work_time[$i]);
                                            if($work_time[$i]>28800){
                                                $over[]=$work_time[$i]-28800;
                                                $over_time[]=gmdate('H:i:s',$over[$i]);
                                            }
                                            else{
                                                $over_time[$i]='00:00';
                                            }
                                            $table->iud('INSERT INTO t_attendance_detail 
                                            (head_id,day,kbn,start_time,end_time,rest_time,night_rest_time,work_time,over_time,remarks)
                                            VALUES(:head_id,:day,:kbn,:start_time,:end_time,:rest_time,:night_rest_time,:work_time,:over_time,:remarks)'
                                            ,['head_id'=>$_SESSION['head_id'],'day'=>$day[$i],'kbn'=>$kbn[$i],'start_time'=>$start_time[$i],'end_time'=>$end_time[$i],'rest_time'=>$rest_time[$i],'night_rest_time'=>$night_rest_time[$i],'work_time'=>$operation[$i],'over_time'=>$over_time[$i],'remarks'=>$remarks[$i]]);
                                        }
                                        $table->cmt();
                                        echo createCalendar($rows);
                                        exit();
                                        }
                                    catch(Exception $ex){
                                        $table->rlb();
                                        exit();
                                    }
                                }
                                else{
                                    try{
                                        $table->begin();
                                        for($i=0;$i<$lastDateOfMonth;$i++){
                                            $work_time[]=strtotime($end_time[$i])-strtotime($start_time[$i])-timeToSeconds($rest_time[$i])-timeToSeconds($night_rest_time[$i]);
                                            $operation[]=gmdate('H:i:s',$work_time[$i]);
                                            if($work_time[$i]>28800){
                                                $over[]=$work_time[$i]-28800;
                                                $over_time[]=gmdate('H:i:s',$over[$i]);
                                            }
                                            else{
                                                $over_time[$i]='00:00:00';
                                            }
                                            $table->iud('UPDATE t_attendance_detail
                                            set day=:day,kbn=:kbn,start_time=:start_time,end_time=:end_time,rest_time=:rest_time,night_rest_time=:night_rest_time,work_time=:work_time,over_time=:over_time,remarks=:remarks
                                            WHERE head_id=:head_id and day=:day;'
                                            ,['day'=>$day[$i],'kbn'=>$kbn[$i],'start_time'=>$start_time[$i],'end_time'=>$end_time[$i],'rest_time'=>$rest_time[$i],'night_rest_time'=>$night_rest_time[$i],'remarks'=>$remarks[$i],'work_time'=>$operation[$i],'over_time'=>$over_time[$i],'head_id'=>$_SESSION['head_id']]);
                                        }
                                        $table->cmt();
                                        echo createCalendar($rows);
                                        exit();
                                    }
                                    catch(Exception $ex){
                                        $table->rlb();
                                        exit();
                                    }
                                }
                            }
                            if(isset($_POST['appModalBtn'])){
                                echo createCalendar($rows);
                            }
                            ?>
                        </div>
                    </div>
                </form>
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
                        <form method="POST">
                            <input id="appModalBtn" name="appModalBtn" type="submit" class="btn btn-success" value="申請">
                        </form>
                        
                    </div><!-- /.modal-footer -->
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </main>
</body>