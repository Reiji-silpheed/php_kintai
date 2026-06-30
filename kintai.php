<?php
require_once './messageClass.php';
require_once './dbClass.php';
require_once './loginClass.php';
date_default_timezone_set('Asia/Tokyo');
$firstDate=new DateTime();
$error=new Message();
$table=new dbClass();
$login=new login();
session_start();
$firstValue=$firstDate->format('Y-m');
if($_SESSION['mail']==""){
    header('Location:./login.php');
} 
if(isset($_POST['displayBtn'])){
    $_SESSION['display-month']=$_POST['month-post'];
}
/* 初期状態のときに今月の勤怠を検索するようにする */
if(!isset($_POST['displayBtn']) && !isset($_POST['saveBtn']) && !isset($_POST['appModalBtn'])){
    $_SESSION['display-month']=$firstValue;
}
$firstParts=explode("-",$_SESSION['display-month']);
$yyyy=$firstParts[0];
$mm=$firstParts[1];
$yyyymm="{$yyyy}{$mm}";
$_SESSION['yyyymm']=$yyyymm;
/* 表示されている年月のステータスを確認 */
$attendanceHeads=$table->select('SELECT * 
                            FROM t_attendance_head 
                            WHERE employee_id=:employee_id and yyyymm=:yyyymm'
                            ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
/* 時間を秒に変換する関数 */
function timeToSeconds($rest){
    if(empty($rest)){
        return 0;
    }
    else{
        $list=explode(":",$rest);
        return (int)$list[0]*3600+(int)$list[1]*60;
    }
} 
$holidays=$table->select('SELECT * FROM m_holiday',[]);
/* カレンダーを表示する関数 */
function createCalendar($holidays){
    $table=new dbClass();
    $weeks=['日','月','火','水','木','金','土'];
    /* ajaxは使用しないという指定があったので、国民の祝日APIを使用*/
    $apiUrl = "https://holidays-jp.github.io/api/v1/date.json";
    $json = @file_get_contents($apiUrl);
    $nationalHolidays = json_decode($json, true);
    $lastDateOfMonth=date('d',strtotime('last day of '.$_SESSION['display-month']));
    $firstWeekDay=date('w',strtotime($_SESSION['display-month'].'-01'));
    $firstParts=explode("-",$_SESSION['display-month']);
    $yyyy=$firstParts[0];
    $mm=$firstParts[1];
    $yyyymm="{$yyyy}{$mm}";
    $attendanceHeads=$table->select("SELECT * 
                    FROM t_attendance_head
                    WHERE  employee_id=:employee_id and yyyymm=:yyyymm"
                    ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$yyyymm]);

    foreach($attendanceHeads as $attendanceHead){
            $_SESSION['head_id']=$attendanceHead['id'];
    }
    if(empty($attendanceHeads)){
        $CalendarElement=
        "<table class='table mt-2'>
            <thead class='table-dark'>
                <tr>
                    <th class='text-start'>日</th>
                    <th class='text-start'>曜日</th>
                    <th class='text-start'>区分</th>
                    <th class='text-start'>開始時間</th>
                    <th class='text-start'>終了時間</th>
                    <th class='text-start'>昼休憩時間</th>
                    <th class='text-start'>夜休憩時間</th>
                    <th class='text-start'>勤務時間</th>
                    <th class='text-start'>残業時間</th>
                    <th class='text-start'>備考</th>
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
            foreach($holidays as $holidayRow){
                if($fullDate==$holidayRow['yyyymmdd']){
                    $holiday=true;
                    $holidayValue=$holidayRow['holiday_name'];
                }
            }
            if(isset($nationalHolidays[$fullDate])){
                $holidayValue=$nationalHolidays[$fullDate];
            }
            /* テキストエリアの行数を計算 */
            $len=floor(mb_strlen($holidayValue)/20)+1;
            if($date==6){
                $colorClass = 'bg-primary-subtle text-primary';
            }
            if($date==0 || $holiday || isset($nationalHolidays[$fullDate])){
                $colorClass = 'bg-danger-subtle text-danger';
            }
            $CalendarElement.="<input type='hidden' class='$colorClass' name='day[]' value={$w}>";
            $CalendarElement .= "<tr>";
            $CalendarElement .= "<td class='$colorClass' scope='row'>$w</td>";
            $CalendarElement .= "<td class='$colorClass'>$weeks[$date]</td>";
            if ($date === 0 || $date === 6 || $holiday ||isset($nationalHolidays[$fullDate])) {
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
                                            <input id='work_time' type='time' name='work_time[]' class='form-control' readonly>
                                        </label>
                                    </td>";
                $CalendarElement .= "<td class='$colorClass'>
                                        <label>
                                            <input id='over_time' type='time' name='over_time[]' class='form-control' readonly>
                                        </label>
                                    </td>";
                $CalendarElement .= "<td class='$colorClass'>
                                        <textarea name='text[]' class='form-control' rows={$len}>$holidayValue</textarea>
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
                                            <input id='work_time' type='time' name='work_time[]' class='form-control' readonly value='08:00'>
                                        </label>
                                    </td>";
                $CalendarElement .= "<td class='$colorClass'>
                                        <label>
                                            <input id='over_time' type='time' name='over_time[]' class='form-control' readonly value='00:00'>
                                        </label>
                                    </td>";

                $CalendarElement .= "<td class='$colorClass'>
                                        <textarea name='text[]' class='form-control' rows={$len}>$holidayValue</textarea>
                                    </td>";
            }
            $CalendarElement .= "</tr>";
        }
        $CalendarElement .= "</tbody></table>";
        return $CalendarElement;
    }
    else{
        $disabled='';
        foreach($attendanceHeads as $attendanceHead){
            if($attendanceHead['status']==1 || $attendanceHead['status']==3){
                $disabled='disabled';
            }
        }
        $CalendarElement=
        "<table class='table mt-2'>
            <thead class='table-dark'>
                <tr>
                    <th class='text-start'>日</th>
                    <th class='text-start'>曜日</th>
                    <th class='text-start'>区分</th>
                    <th class='text-start'>開始時間</th>
                    <th class='text-start'>終了時間</th>
                    <th class='text-start'>昼休憩時間</th>
                    <th class='text-start'>夜休憩時間</th>
                    <th class='text-start'>勤務時間</th>
                    <th class='text-start'>残業時間</th>
                    <th class='text-start'>備考</th>
            </thead>";

        $CalendarElement .= "<tbody>";
        for($w=1;$w<=$lastDateOfMonth;$w++){
            $start_time='';
            $end_time='';
            $rest_time='';
            $night_rest_time='';
            $work_time='';
            $new_work_time='';
            $over_time='';
            $new_over_time='';
            $remarks='';
            $selected1='';
            $selected2='';
            $selected3='';
            $selected4='';
            $selected5='';
            $selected6='';
            $selected7='';
            $selected8='';
            $readonly='';
            $attendanceDetails=$table->select("SELECT * FROM t_attendance_detail WHERE head_id=:head_id and day=:day",['head_id'=>$_SESSION['head_id'],'day'=>$w]);
            foreach($attendanceDetails as $detail){
                $holiday=false;
                $day='0'.$w;
                $colorClass="";
                $date=($firstWeekDay+$w-1)%7;
                $yyyy=$firstParts[0];
                $mm=$firstParts[1];
                $dd=substr($day,-2);
                $remarks=$detail['remarks'];
                $len=floor(mb_strlen($remarks)/20)+1;
                $fullDate="{$yyyy}-{$mm}-{$dd}";
                foreach($holidays as $holidayRow){
                    if($fullDate==$holidayRow['yyyymmdd']){
                        $holiday=true;
                        $remarks=$holidayRow['holiday_name'];
                    }
                }
                if(isset($nationalHolidays[$fullDate])){
                    $remarks=$nationalHolidays[$fullDate];
                }
                if($detail['kbn']==1){
                    $selected1='selected';
                    $start_time=$detail['start_time'];
                    $end_time=$detail['end_time'];
                    $rest_time=$detail['rest_time'];
                    $night_rest_time=$detail['night_rest_time'];
                    $work_time=$detail['work_time'];
                    $over_time=$detail['over_time'];
                }
                if($detail['kbn']==2){
                    $selected2='selected';
                    $readonly='readonly';
                    $colorClass='bg-danger-subtle text-danger';
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $work_time='';
                    $over_time='';
                }
                if($detail['kbn']==3){
                    $selected3='selected';
                    $readonly='readonly';
                    $colorClass='bg-danger-subtle text-danger';
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $work_time='';
                    $over_time='';
                    
                }
                if($detail['kbn']==4){
                    $selected4='selected';
                    $start_time=$detail['start_time'];
                    $end_time=$detail['end_time'];
                    $rest_time=$detail['rest_time'];
                    $night_rest_time=$detail['night_rest_time'];
                    $work_time=$detail['work_time'];
                    $over_time=$detail['over_time'];
                }
                if($detail['kbn']==5){
                    $selected5='selected';
                    $readonly='readonly';
                    $colorClass='bg-danger-subtle text-danger';
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $work_time='';
                    $over_time='';
                }
                if($detail['kbn']==6){
                    $selected6='selected';
                    $readonly='readonly';
                    $colorClass='bg-danger-subtle text-danger';
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $work_time='';
                    $over_time='';
                }
                if($detail['kbn']==7){
                    $selected7='selected';
                    $readonly='readonly';
                    $colorClass='bg-danger-subtle text-danger';
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                }
                if($detail['kbn']==8){
                    $selected8='selected';
                    $readonly='readonly';
                    $colorClass='bg-danger-subtle text-danger';
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $work_time='';
                    $over_time='';
                }
                if((int)$detail['kbn']!==1 && (int)$detail['kbn']!==4 && $date==6){
                    $colorClass = 'bg-primary-subtle text-primary';
                }
                if((int)$detail['kbn']!==1 && (int)$detail['kbn']!==4 && ($date==0 || $holiday || isset($nationalHolidays[$fullDate]))){
                    $colorClass = 'bg-danger-subtle text-danger';
                }
                $CalendarElement.="<input type='hidden' name='day[]' value={$detail['day']}>";
                $CalendarElement .= "<tr>";
                $CalendarElement .= "<td class='$colorClass' scope='row'>$w</td>";
                $CalendarElement .= "<td class='$colorClass'>$weeks[$date]</td>";
                if ($date === 0 || $date === 6 || $holiday || isset($nationalHolidays[$fullDate])) {
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <select class='select1a form-select' name='kbn[]' value={$detail['kbn']} {$readonly} {$disabled}>
                                                    <option class='holiday' value=2 {$selected2}>休日</option>
                                                    <option class='work' value=4 {$selected4}>休出</option>
                                                </select>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='startWork' type='time' name='start[]' class='form-control' value='{$start_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='finishWork' type='time' name='end[]' class='form-control' value='{$end_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='lunch' type='time' name='lunch[]' class='form-control' value='{$rest_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='dinner' type='time' name='dinner[]' class='form-control' value='{$night_rest_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='work_time' type='time' name='work_time[]' class='form-control' value='{$work_time}' readonly {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='over_time' type='time' name='over_time[]' class='form-control' value='{$over_time}' readonly {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <textarea name='text[]' class='form-control' rows={$len} {$disabled}>$remarks</textarea>
                                        </td>";
                }
                else {
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <select class='select1a form-select' name='kbn[]' value={$detail['kbn']} {$disabled}>
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
                                                <input id='startWork' type='time' name='start[]' class='form-control' value='{$start_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='finishWork' type='time' name='end[]' class='form-control' value='{$end_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='lunch' type='time' name='lunch[]' class='form-control' value='{$rest_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='dinner' type='time' name='dinner[]' class='form-control' value='{$night_rest_time}' {$readonly} {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='work_time' type='time' name='work_time[]' class='form-control' value='{$work_time}' readonly {$disabled}>
                                            </label>
                                        </td>";
                    $CalendarElement .= "<td class='$colorClass'>
                                            <label>
                                                <input id='over_time' type='time' name='over_time[]' class='form-control' value='{$over_time}' readonly {$disabled}>
                                            </label>
                                        </td>";

                    $CalendarElement .= "<td class='$colorClass'>
                                            <textarea name='text[]' class='form-control' rows={$len} {$disabled}>$remarks</textarea>
                                        </td>";
                }
                $CalendarElement .= "</tr>";
            }
        }
        $CalendarElement .= "</tbody></table>";
        return $CalendarElement;        
    }        
}
/* 保存ボタンを押したときの処理 */
if (isset($_POST['saveBtn'])) {
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
    $over_time=[];
    $operation=[];
    if(empty($attendanceHeads)){
        try{
            $table->begin();
            $table->dbAccess('INSERT INTO t_attendance_head (employee_id,yyyymm,status) VALUES(:employee_id,:yyyymm,0)',['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
            $head_ids=$table->select('SELECT * FROM t_attendance_head WHERE employee_id=:employee_id',['employee_id'=>$_SESSION['id']]);
            foreach($head_ids as $head_id){
                $_SESSION['head_id']=$head_id['id'];
            }
            for($i=0;$i<$lastDateOfMonth;$i++){
                /* 稼働時間を秒にして計算 */
                $work_time[]=strtotime($end_time[$i])-strtotime($start_time[$i])-timeToSeconds($rest_time[$i])-timeToSeconds($night_rest_time[$i]);
                /* 稼働時間を秒から時間に変換 */
                $operation[]=gmdate('H:i:s',$work_time[$i]);
                if($work_time[$i]>28800){
                    $over_time[]=gmdate('H:i:s',$work_time[$i]-28800);
                }
                else{
                    $over_time[$i]='00:00';
                }
                $table->dbAccess('INSERT INTO t_attendance_detail 
                (head_id,day,kbn,start_time,end_time,rest_time,night_rest_time,work_time,over_time,remarks)
                VALUES(:head_id,:day,:kbn,:start_time,:end_time,:rest_time,:night_rest_time,:work_time,:over_time,:remarks)'
                ,['head_id'=>$_SESSION['head_id'],'day'=>$day[$i],'kbn'=>$kbn[$i],'start_time'=>$start_time[$i],'end_time'=>$end_time[$i],'rest_time'=>$rest_time[$i],'night_rest_time'=>$night_rest_time[$i],'work_time'=>$operation[$i],'over_time'=>$over_time[$i],'remarks'=>$remarks[$i]]);
            }
            $table->commit();
            }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
        /* attendanceHeadsを更新することでステータスの判定間違いを防止 */
        $attendanceHeads=$table->select('SELECT * 
        FROM t_attendance_head 
        WHERE employee_id=:employee_id and yyyymm=:yyyymm'
        ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
    }
    else{
        try{
            $table->begin();
            $table->dbAccess('UPDATE t_attendance_head SET status=0 WHERE employee_id=:employee_id and yyyymm=:yyyymm',['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
            for($i=0;$i<$lastDateOfMonth;$i++){
                $work_time[]=strtotime($end_time[$i])-strtotime($start_time[$i])-timeToSeconds($rest_time[$i])-timeToSeconds($night_rest_time[$i]);
                $operation[]=gmdate('H:i:s',$work_time[$i]);
                if($work_time[$i]>28800){
                    $over_time[]=gmdate('H:i:s',$work_time[$i]-28800);
                }
                else{
                    $over_time[$i]='00:00:00';
                }
                $table->dbAccess('UPDATE t_attendance_detail
                set kbn=:kbn,start_time=:start_time,end_time=:end_time,rest_time=:rest_time,night_rest_time=:night_rest_time,work_time=:work_time,over_time=:over_time,remarks=:remarks
                WHERE head_id=:head_id and day=:day;'
                ,['day'=>$day[$i],'kbn'=>$kbn[$i],'start_time'=>$start_time[$i],'end_time'=>$end_time[$i],'rest_time'=>$rest_time[$i],'night_rest_time'=>$night_rest_time[$i],'remarks'=>$remarks[$i],'work_time'=>$operation[$i],'over_time'=>$over_time[$i],'head_id'=>$_SESSION['head_id']]);
            }
            $table->commit();
        }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
    }
}
/* 申請ボタンを押したときの処理 */
if (isset($_POST['appModalBtn'])) {
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
    $over_time=[];
    if(empty($attendanceHeads)){
        try{
            $table->begin();
            $table->dbAccess('INSERT INTO t_attendance_head (employee_id,yyyymm,status) VALUES(:employee_id,:yyyymm,1)',['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
            $head_ids=$table->select('SELECT * FROM t_attendance_head WHERE employee_id=:employee_id',['employee_id'=>$_SESSION['id']]);
            foreach($head_ids as $head_id){
                $_SESSION['head_id']=$head_id['id'];
            }
            for($i=0;$i<$lastDateOfMonth;$i++){
                $work_time[]=strtotime($end_time[$i])-strtotime($start_time[$i])-timeToSeconds($rest_time[$i])-timeToSeconds($night_rest_time[$i]);
                $operation[]=gmdate('H:i:s',$work_time[$i]);
                if($work_time[$i]>28800){
                    $over_time[]=gmdate('H:i:s',$work_time[$i]-28800);
                }
                else{
                    $over_time[$i]='00:00';
                }
                $table->dbAccess('INSERT INTO t_attendance_detail 
                (head_id,day,kbn,start_time,end_time,rest_time,night_rest_time,work_time,over_time,remarks)
                VALUES(:head_id,:day,:kbn,:start_time,:end_time,:rest_time,:night_rest_time,:work_time,:over_time,:remarks)'
                ,['head_id'=>$_SESSION['head_id'],'day'=>$day[$i],'kbn'=>$kbn[$i],'start_time'=>$start_time[$i],'end_time'=>$end_time[$i],'rest_time'=>$rest_time[$i],'night_rest_time'=>$night_rest_time[$i],'work_time'=>$operation[$i],'over_time'=>$over_time[$i],'remarks'=>$remarks[$i]]);
            }
            $table->commit();
            }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
        /* attendanceHeadsを更新することでステータスの判定間違いを防止 */
        $attendanceHeads=$table->select('SELECT * 
        FROM t_attendance_head 
        WHERE employee_id=:employee_id and yyyymm=:yyyymm'
        ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
    }
    else{
        try{
            $table->begin();
            $table->dbAccess('UPDATE t_attendance_head SET status=1,reject_comment=null WHERE employee_id=:employee_id and yyyymm=:yyyymm',['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
            for($i=0;$i<$lastDateOfMonth;$i++){
                $work_time[]=strtotime($end_time[$i])-strtotime($start_time[$i])-timeToSeconds($rest_time[$i])-timeToSeconds($night_rest_time[$i]);
                $operation[]=gmdate('H:i:s',$work_time[$i]);
                if($work_time[$i]>28800){
                    $over_time[]=gmdate('H:i:s',$work_time[$i]-28800);
                }
                else{
                    $over_time[$i]='00:00:00';
                }
                $table->dbAccess('UPDATE t_attendance_detail
                set kbn=:kbn,start_time=:start_time,end_time=:end_time,rest_time=:rest_time,night_rest_time=:night_rest_time,work_time=:work_time,over_time=:over_time,remarks=:remarks
                WHERE head_id=:head_id and day=:day;'
                ,['day'=>$day[$i],'kbn'=>$kbn[$i],'start_time'=>$start_time[$i],'end_time'=>$end_time[$i],'rest_time'=>$rest_time[$i],'night_rest_time'=>$night_rest_time[$i],'remarks'=>$remarks[$i],'work_time'=>$operation[$i],'over_time'=>$over_time[$i],'head_id'=>$_SESSION['head_id']]);
            }
            $table->commit();
        }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
        catch(Exception $ex){
            $table->rollback();
            exit();
        }
        /* attendanceHeadsを更新することでステータスの判定間違いを防止 */
        $attendanceHeads=$table->select('SELECT * 
        FROM t_attendance_head 
        WHERE employee_id=:employee_id and yyyymm=:yyyymm'
        ,['employee_id'=>$_SESSION['id'],'yyyymm'=>$_SESSION['yyyymm']]);
    }
}
/* 差戻理由が書いてあるかチェック */
$rows=$table->select("SELECT * FROM t_attendance_head WHERE employee_id=:employee_id and reject_comment is not null",["employee_id"=>$_SESSION['id']]);
?> 

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
</head>


<body>
    <!-- メニュー画面 -->
    <?php
    if($_SESSION['authority']==0){
        echo '<header>
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
                        <li class="nav-item">
                            <a href="login.php" id="logout" class="nav-link" aria-disabled="true">ログアウト</a>
                        </li>
                        
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
    </header>';
    }
    elseif($_SESSION['authority']==1){
        echo '<header>
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
                            <a id="input" class="nav-link" aria-current="page" href="management.php">勤怠管理</a>
                        </li>
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
    </header>';
    }
    ?>
    <!-- 勤怠入力画面 -->
    <main>
        <div class="container">
            <h1>勤怠入力</h1>
            <?php
            if(isset($_POST['displayBtn'])){
                if($_SESSION['display-month']==''){
                    echo $error->alert('alert-danger','年月が選択されていません');
                }
            }
            if(isset($_POST['saveBtn'])){
                echo $error->alert('alert-primary',"保存が完了しました。");
            }
                                               
                
            if(isset($_POST['appModalBtn'])){
                echo $error->alert('alert-success','申請が完了しました。');
            }
            ?>
            <div class="container">
                <div class="card mt-4" <?php if(empty($rows)){echo 'hidden';}?>>
                    <div class="card-header">
                        差戻一覧
                    </div>
                    <form method="POST">
                    <div class="card-body">
                        <div class="container">
                            <table class="table mt-2">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">日付</th>
                                        <th scope="col">理由</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rows as $row):?>
                                        <tr>
                                            <td><?php echo $row['yyyymm'];?></td>
                                            <td><?php echo $row['reject_comment'];?></td>
                                        </tr>   
                                    <?php endforeach?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>

            <form method="POST">
                <div id="dateCard" class="card mt-2">
                    <div class="card-header">
                        入力
                    </div>
                    <div class="card-body">
                        <label for="date">年月:</label>
                        <div class="input-group w-25">
                            <input type="month" id="month-post" name="month-post" class="form-control" value="<?php echo $_SESSION['display-month'];?>">
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
                            <input class="btn btn-primary" id="saveBtn" name="saveBtn" type="submit" value="保存" <?php foreach($attendanceHeads as $attendanceHead){if($attendanceHead['status']==1 || $attendanceHead['status']==3){echo 'disabled';}}?>>
                            <button class="btn  btn-success" id="appBtn" name="appBtn" type="button" data-bs-toggle="modal"
                                data-bs-target="#appModal" <?php foreach($attendanceHeads as $attendanceHead){if($attendanceHead['status']==1 || $attendanceHead['status']==3){echo 'disabled';}}?>>申請</button>
                        </div>
                        <div id="calendar" class="mt-2">
                            <?php
                            if($_SESSION['display-month']!==''){
                                echo createCalendar($holidays);
                            }
                            ?>
                        </div>
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
                            <input id="appModalBtn" name="appModalBtn" type="submit" class="btn btn-success" value="申請">
                        </div><!-- /.modal-footer -->
                        </form>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </main>
    </body>

    <script>
        $(function () {
            $(document).on("change", ".select1a", function () {
                var selectedClass = $(this).find('option:selected').attr('class');
                let row = $(this).closest("tr");
                if (selectedClass === "holiday") {
                    row.find("#startWork").val('');
                    row.find("#finishWork").val('');
                    row.find("#lunch").val('');
                    row.find("#dinner").val('');
                    row.find("#work_time").val("");
                    row.find("#over_time").val("");
                    row.find("#startWork").prop("readonly", true);
                    row.find("#finishWork").prop("readonly", true);
                    row.find("#lunch").prop("readonly", true);
                    row.find("#dinner").prop("readonly", true);
                    row.find("#work_time").prop("readonly", true);
                    row.find("#over_time").prop("readonly", true);
                    row.find("td").removeClass();
                    row.find("td").addClass("bg-danger-subtle text-danger");
                } else {
                    row.find("#startWork").val("09:00");
                    row.find("#finishWork").val("18:00");
                    row.find("#lunch").val("01:00");
                    row.find("#dinner").val("00:00");
                    row.find("#work_time").val("08:00");
                    row.find("#over_time").val("00:00");
                    row.find("#startWork").prop("readonly", false);
                    row.find("#finishWork").prop("readonly", false);
                    row.find("#lunch").prop("readonly", false);
                    row.find("#dinner").prop("readonly", false);
                    row.find("#work_time").prop("readonly", true);
                    row.find("#over_time").prop("readonly", true);
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
            })
            
        })

    </script>