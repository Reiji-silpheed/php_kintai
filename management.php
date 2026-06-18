<head>
    <meta http-equiv="content-type" charset="utf-8" />
    <!-- ①：Bootstrapで使うCSSを読み込む -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- ②：BootstrapとjQueryで使うJavascriptを読み込む -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="./style2.css">
    <title>勤怠管理</title>
    <script>
        $(function(){
            $(document).on('click','#approvalBtn',function(){
                $(".alert").remove();
                const checks=[];
                $(':checkbox[name="checkbox"]:checked').each(function(){
                   checks.push($(this).val());
                })
                if(checks.length===0){
                    $("#title").after('<div class="alert alert-danger" role="alert">勤怠を選択してください</div>');
                }
                else{
                    $("#approvalModal").modal("show");
                }
            })
            /* モーダルでエラーが出て再表示するときも値を入れるために、モーダルを開いたときに値を入れるようにしている */
            $('#approvalModal').on('show.bs.modal',function(){
                $(".alert").remove();
                const checks=[];
                $(':checkbox[name="checkbox"]:checked').each(function(){
                   checks.push($(this).val());
                })
                var status=checks.join(",");
                $(".approvalCheck").val(status);
            })
            $(document).on("click","#backBtn",function(){
                $(".alert").remove();
                const checks=[];
                $(':checkbox[name="checkbox"]:checked').each(function(){
                    checks.push($(this).val());
                })
                if(checks.length===0){
                    $("#title").after('<div class="alert alert-danger" role="alert">勤怠を選択してください</div>');
                }
                else{
                    $("#backModal").modal("show");
                }
            })
            $('#backModal').on('show.bs.modal',function(){
                $(".alert").remove();
                const checks=[];
                $(':checkbox[name="checkbox"]:checked').each(function(){
                    checks.push($(this).val());
                })
                var status=checks.join(",");
                $(".backCheck").val(status);
            }) 
            $(document).on("click","#excelBtn",function(){
                $(".alert").remove();
                $(':checkbox[name="checkbox"]:checked').each(function(){
                    checks.push($(this).val());
                })
                if(checks.length===0){
                    $("#title").after('<div class="alert alert-danger" role="alert">勤怠を選択してください</div>');
                }
                else{
                    $("#excelModal").modal("show");
                    var status=checks.join(",");
                    $("#excelCheck").val(status);
                }
            })
        })
    </script>
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
if($_SESSION['mail']==''){
    header('Location:./login.php');
}
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
    $sql="SELECT *,t_attendance_head.id AS head_id FROM t_attendance_head LEFT JOIN m_employee on m_employee.id=t_attendance_head.employee_id";
    $where='';
    $url='?';
    $_SESSION['management_url']=$url;
    $list=[];
    $param=[];
    if(isset($_GET['searchBtn'])){
        $_SESSION['searchDate']=$_GET['searchDate'];
        if($_GET['searchDate']!==''){
            $firstParts=explode("-",$_GET['searchDate']);
            $yyyy=$firstParts[0];
            $mm=$firstParts[1];
            $yyyymm="{$yyyy}{$mm}";
        }
        $url.="searchDate={$_GET['searchDate']}";
        $_SESSION['searchNumber']=$_GET['searchNumber'];
        $_SESSION['searchName']=$_GET['searchName'];
        if(!empty($_GET['searchDate'])){
            $list[]='yyyymm=:yyyymm';
            $param['yyyymm']=$yyyymm;
        }
        $url.="&searchNumber={$_GET['searchNumber']}";
        if(!empty($_GET['searchNumber'])){
            $list[]='employee_no=:employee_no';
            $param['employee_no']=$_GET['searchNumber'];
        }
        $url.="&searchName={$_GET['searchName']}";
        if(!empty($_GET['searchName'])){
            $list[]='employee_name=:employee_name';
            $param['employee_name']=$_GET['searchName'];
        }
        if($_GET['searchStatus']!==''){
            $list[]='status=:status';
            $param['status']=$_GET['searchStatus'];
        }
        $url.="&searchStatus={$_GET['searchStatus']}";
        $url.="&searchBtn=検索";
        $_SESSION['management_url']=$url;
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
        $_SESSION['management_url']=$url;
    }
    ?>
    <main>
        <div class="container">
            <h1 id="title">勤怠管理</h1>
            <?php
            $judgment=false;
            $tabooLists=[];
            $values=[];
            $message="";
            if(isset($_GET['searchBtn'])){
                if(empty($rows)){
                    echo $error->alert('alert-warning','検索結果がありませんでした');
                }
            }
            if(isset($_POST['approvalModalBtn'])){
                $values=explode(",",$_POST['approvalCheck']);
                $_SESSION['check']=$values;
                foreach($values as $value){
                    $Checks=$table->select("SELECT * FROM t_attendance_head WHERE id=:id",['id'=>$value]);
                    foreach($Checks as $Check){
                        if((int)$Check['status']!==1){
                            $judgment=true;
                            $tabooLists[]=$Check['id'];
                        }
                    }
                }
                if($judgment){
                    foreach($tabooLists as $tabooList){
                        $statusChecks=$table->select("SELECT * FROM t_attendance_head LEFT JOIN m_employee ON m_employee.id=t_attendance_head.employee_id WHERE t_attendance_head.id=:head_id",['head_id'=>$tabooList]);
                        foreach($statusChecks as $statusCheck){
                            $message.="<br>年月:{$statusCheck['yyyymm']},社員番号:{$statusCheck['employee_no']},社員名:{$statusCheck['employee_name']}";
                        }
                    }
                    echo $error->alert("alert-primary","「申請中」以外が含まれています{$message}");
                }
                if(!empty($_POST['approvalCheck']) && !$judgment){
                    $table->begin();
                    try{
                        foreach($values as $value){
                            $table->dbAccess('UPDATE t_attendance_head SET status=3 WHERE id=:id',['id'=>$value]);
                        }
                        $_SESSION['check']=[];
                        $table->commit();
                    }
                    catch(Exception $ex){
                        $table->rollback();
                        exit();
                    }
                    echo $error->alert("alert-success","承認処理が完了しました");
                }
            }
            if(isset($_POST['backModalBtn'])){
                $values=explode(",",$_POST['backCheck']);
                $_SESSION['check']=$values;
                if($_POST['backReason']==''){
                    $error->setError('reason',"差戻理由が入力されていません");
                }
                else{
                    foreach($values as $value){
                        $Checks=$table->select("SELECT * FROM t_attendance_head WHERE id=:id",['id'=>$value]);
                        foreach($Checks as $Check){
                            if((int)$Check['status']!==1){
                                $judgment=true;
                                $tabooLists[]=$Check['id'];
                            }
                        }
                    }
                    if($judgment){
                        foreach($tabooLists as $tabooList){
                            $statusChecks=$table->select("SELECT * FROM t_attendance_head LEFT JOIN m_employee ON m_employee.id=t_attendance_head.employee_id WHERE t_attendance_head.id=:head_id",['head_id'=>$tabooList]);
                            foreach($statusChecks as $statusCheck){
                                $message.="<br>年月:{$statusCheck['yyyymm']},社員番号:{$statusCheck['employee_no']},社員名:{$statusCheck['employee_name']}";
                            }
                        }
                        echo $error->alert("alert-primary","「申請中」以外が含まれています{$message}");
                    }
                    if(!empty($_POST['backCheck'] && !$judgment)){
                        $table->begin();
                        try{
                            foreach($values as $value){
                                $table->dbAccess('UPDATE t_attendance_head SET status=2,reject_comment=:reject_comment WHERE id=:id',['reject_comment'=>$_POST['backReason'],'id'=>$value]);
                            }
                            $_SESSION['check']=[];
                            $table->commit();
                        }
                        catch(Exception $ex){
                            $table->rollback();
                            exit();
                        }
                        echo $error->alert("alert-success","承認処理が完了しました");
                    }
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
            <div class="card mt-2">
                <div class="card-header">
                    検索結果
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-success" id="approvalBtn">承認</button>
                            <button type="button" class="btn btn-danger" id="backBtn">差戻</button>
                            <button type="button" class="btn btn-light" id="excelBtn">Excel出力</button>
                            <button type="button" class="btn btn-light" id="PDFBtn" data-bs-toggle="modal"
                                data-bs-target="#PDFModal">PDF出力</button>
                        </div>
                    </div>
                    <div class="container">
                        <table class="table mt-2">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">年月</th>
                                    <th scope="col">社員番号</th>
                                    <th scope="col">社員名</th>
                                    <th scope="col">ステータス</th>
                                    <th scope="col">確認</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset($_GET['page'])):?>
                                    <script>
                                        const checks=[];
                                        $(':checkbox[name="checkbox"]:checked').each(function(){
                                            checks.push($(this).val());
                                        })
                                        var status=checks.join(",");
                                        $("#pageCheck").val(status);
                                    </script>
                                <?php endif?>
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
                                $url='';
                                $count="SELECT count(m_employee.id) FROM t_attendance_head left join m_employee on m_employee.id=t_attendance_head.employee_id";
                                $sql.=" ORDER BY m_employee.id limit 5 offset {$offset}";
                                if(!empty($where)){
                                    $count.= " WHERE {$where}";
                                }
                                $rows=$table->select($sql,$param);
                                $countRows=$table->select($count,$param);
                                ?>
                                <?php foreach($rows as $row):?>
                                    <tr>
                                        <input type="hidden" class="head_id" value="<?php echo $row['head_id'];?>">
                                        <td scope="row">
                                            <div class="form-check">
                                                <label for="checkbox">
                                                    <input class="form-check-input" type="checkbox" name="checkbox" id="<?php echo $row['head_id'];?>" value='<?php echo $row['head_id'];?>' <?php if(isset($_POST['approvalModalBtn']) || isset($_POST['backModalBtn'])){if(in_array($row['head_id'],$_SESSION['check'])){echo 'checked';}}?>/>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="date"><?php echo $row['yyyymm'];?></td>
                                        <td class="number"><?php echo $row['employee_no'];?></td>
                                        <td class="name"><?php echo $row['employee_name'];?></td>
                                        <td class="status"><?php if($row['status']==0){echo "入力中";}elseif($row['status']==1){echo "申請中";}elseif($row['status']==2){echo "差戻中";}elseif($row['status']==3){echo "承認済み";}?></td>
                                        <td>
                                            <!-- head_idを取得するためにurlで送信している -->
                                            <a class="btn btn-info" href="<?php echo $_SESSION['management_url'];?>&page=<?php echo $page;?>&head_id=<?php echo $row['head_id']; ?>">確認</a>
                                        </td>
                                    </tr>   
                                <?php endforeach?>
                            </tbody>
                        </table>    
                    </div>
                </div>
                <input type="hidden" id="pageCheck" name="pageCheck">
                <nav class="d-flex align-items-center justify-content-center">
                    <ul class="pagination">
                        <li class="page-item <?php if($page==1){echo 'disabled';}?>">
                            <a class="page-link" href="<?php echo $_SESSION['management_url'];?>&page=<?php echo $page-1;?>">前</a>
                        </li>
                        <?php foreach($countRows as $countRow):?>
                            <?php for($i=1;$i<=ceil($countRow['count(m_employee.id)']/5);$i++):?>
                                <li class="page-item <?php if($page==$i){echo 'active';}?>">
                                    <a class="page-link" href="<?php echo $_SESSION['management_url'];?>&page=<?php echo $i;?>"><?php echo $i;?></a>
                                </li>
                            <?php endfor?>
                            <li class="page-item <?php if($page==ceil($countRow['count(m_employee.id)']/5)){echo 'disabled';}?>">
                                <a class="page-link" href="<?php echo $_SESSION['management_url'];?>&page=<?php echo $page+1;?>">次</a>
                            </li>
                        <?php endforeach?>
                    </ul>
                </nav>
            </div>
        </div>
    </main>
</body>

<?php if(isset($_GET['head_id'])):?>
    <script>
        $(function(){
            $("#checkModal").modal("show");
        });
    </script>          
<?php endif?>
<div class="modal fade" id="checkModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h1 class="modal-title text-light fs-5">勤怠確認</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <?php
                $attendanceDetails=$table->select('SELECT * FROM t_attendance_detail WHERE head_id=:head_id',['head_id'=>$_GET['head_id']]);
                $attendanceHeads=$table->select('SELECT * FROM t_attendance_head WHERE id=:id',['id'=>$_GET['head_id']]);
                $weeks=['日','月','火','水','木','金','土'];
                foreach($attendanceHeads as $attendanceHead){
                    $date = DateTime::createFromFormat('Ym', $attendanceHead['yyyymm']);
                    $yyyy_mm=$date->format('Y-m');
                }
                $weeks=['日','月','火','水','木','金','土'];
                $lastDateOfMonth=date('d',strtotime('last day of '.$yyyy_mm));
                $firstWeekDay=date('w',strtotime($yyyy_mm.'-01'));
                $CalendarElement=
                    "<table class='table mt-2'>
                        <thead class='table-dark'>
                            <tr>
                                <th class='text-center align-middle'>日</th>
                                <th class='text-center align-middle'>曜日</th>
                                <th class='text-center align-middle'>区分</th>
                                <th class='text-center align-middle'>開始時間</th>
                                <th class='text-center align-middle'>終了時間</th>
                                <th class='text-center align-middle'>昼休憩時間</th>
                                <th class='text-center align-middle'>夜休憩時間</th>
                                <th class='text-center align-middle'>勤務時間</th>
                                <th class='text-center align-middle'>残業時間</th>
                                <th class='text-center align-middle'>備考</th>
                            </tr>
                        </thead>";
                $CalendarElement .= "<tbody>";
                $holidays=$table->select('SELECT * FROM m_holiday',[]);
                foreach($attendanceDetails as $detail){
                    $start_time='';
                    $end_time='';
                    $rest_time='';
                    $night_rest_time='';
                    $work_time='';
                    $over_time='';
                    $remarks='';
                    $kbn='';
                    $holiday=false;
                    $holidayValue='';
                    $day='0'.$detail['day'];
                    $colorClass="";
                    $date=($firstWeekDay+$detail['day']-1)%7;
                    $dd=substr($day,-2);
                    $fullDate="{$yyyy_mm}-{$dd}";
                    foreach($holidays as $holidayRow){
                        if($fullDate==$holidayRow['yyyymmdd']){
                            $holiday=true;
                            $holidayValue=$holidayRow['holiday_name'];
                        }
                    }
                    $remarks=$detail['remarks'];
                    if($detail['kbn']==1){
                        $kbn='出勤';
                        /* H:i:sをH:iに変更 */
                        $start=new DateTime("{$detail['start_time']}");
                        $start_time=$start->format("H:i");
                        $end=new DateTime("{$detail['end_time']}");
                        $end_time=$end->format("H:i");
                        $rest=new DateTime("{$detail['rest_time']}");
                        $rest_time=$rest->format("H:i");
                        $night_rest=new DateTime("{$detail['night_rest_time']}");
                        $night_rest_time=$night_rest->format("H:i");
                        $work=new DateTime("{$detail['work_time']}");
                        $work_time=$work->format("H:i");
                        $over=new DateTime("{$detail['over_time']}");
                        $over_time=$over->format("H:i");
                    }
                    if($detail['kbn']==2){
                        $kbn='休日';
                        $colorClass='bg-danger-subtle text-danger';
                    }
                    if($detail['kbn']==3){
                        $kbn='有給';
                        $colorClass='bg-danger-subtle text-danger';

                    }
                    if($detail['kbn']==4){
                        $kbn='休出';
                        $start=new DateTime("{$detail['start_time']}");
                        $start_time=$start->format("H:i");
                        $end=new DateTime("{$detail['end_time']}");
                        $end_time=$end->format("H:i");
                        $rest=new DateTime("{$detail['rest_time']}");
                        $rest_time=$rest->format("H:i");
                        $night_rest=new DateTime("{$detail['night_rest_time']}");
                        $night_rest_time=$night_rest->format("H:i");
                        $work=new DateTime("{$detail['work_time']}");
                        $work_time=$work->format("H:i");
                        $over=new DateTime("{$detail['over_time']}");
                        $over_time=$over->format("H:i");
                    }
                    if($detail['kbn']==5){
                        $kbn='欠勤';
                        $colorClass='bg-danger-subtle text-danger';
                    }
                    if($detail['kbn']==6){
                        $kbn='特休';
                        $colorClass='bg-danger-subtle text-danger';
                    }
                    if($detail['kbn']==7){
                        $kbn='代休';
                        $colorClass='bg-danger-subtle text-danger';
                    }
                    if($detail['kbn']==8){
                        $kbn='振休';
                        $colorClass='bg-danger-subtle text-danger';
                    }
                    if((int)$detail['kbn']!==1 && (int)$detail['kbn']!==4 && $date==6){
                        $colorClass = 'bg-primary-subtle text-primary';
                    }
                    if((int)$detail['kbn']!==1 && (int)$detail['kbn']!==4 && ($date==0 || $holiday)){
                        $colorClass = 'bg-danger-subtle text-danger';
                    }
                    $CalendarElement.="<input type='hidden' name='day[]' value={$detail['day']}>";
                    $CalendarElement .= "<tr>";
                    $CalendarElement .= "<td class='$colorClass text-center align-middle'>{$detail['day']}</td>";
                    $CalendarElement .= "<td class='$colorClass text-center align-middle'>$weeks[$date]</td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                                {$kbn}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                                {$start_time}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                                {$end_time}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                                {$rest_time}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                                {$night_rest_time}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                                {$work_time}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-center align-middle'>
                                               {$over_time}
                                            </td>";
                        $CalendarElement .= "<td class='$colorClass text-body align-middle'>
                                                {$remarks}
                                            </td>";
                    $CalendarElement .= "</tr>";
                }
                $CalendarElement .= "</tbody></table>";
                echo $CalendarElement;              

                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <h1 class="modal-title fs-5 text-light" id="exampleModalLabel">承認</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>
        <div class="modal-body">
            <p>選択した勤怠の承認を行いますか?</p>
        </div>
        <div class="modal-footer">
            <form method="POST" action="management.php<?php echo $_SESSION['management_url'];?>">
                <input type="hidden" id="approvalCheck" class="approvalCheck" name="approvalCheck" value="">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <input type="submit" name="approvalModalBtn" class="btn btn-success" value="承認">
            </form>
        </div><!-- /.modal-footer -->
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php if(isset($_POST['backModalBtn'])):?>
    <?php if($_POST['backReason']==''):?>
        <script>
            $(function(){
                $("#backModal").modal("show");
            })
        </script>
    <?php endif?>
<?php endif?>
<div class="modal fade" id="backModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <h1 class="modal-title fs-5 text-light" id="exampleModalLabel">差戻</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
         </div>
        <form method="POST" action="management.php<?php echo $_SESSION['management_url'];?>">
            <div class="modal-body">
                <p>選択した勤怠の差戻を行いますか？</p>
                <label for="backReason">差戻理由:</label>
                <textarea class="form-control <?php echo $error->invalid('reason');?>" name="backReason" id="backReason" rows="4"></textarea>
                <?php echo $error->getError('reason');?>
            </div>
            <div class="modal-footer">
                    <input type="hidden" id="backCheck" class="backCheck" name="backCheck" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <input type="submit" name="backModalBtn" class="btn btn-danger" value="差戻">
            </div><!-- /.modal-footer -->
        </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h1 class="modal-title fs-5 text-light" id="exampleModalLabel">Excel出力</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
      </div>
      <div class="modal-body">
        <p>選択した勤怠のExcel出力を行いますか?</p>
      </div>
      <form method="POST" action="management.php<?php echo $_SESSION['management_url'];?>">
        <div class="modal-footer">
            <input type="hidden" id="excelCheck" name="excelCheck">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
            <button type="button" class="btn btn-success">出力</button>
        </div><!-- /.modal-footer -->
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->