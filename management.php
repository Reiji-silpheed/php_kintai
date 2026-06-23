<?php
require 'vendor/autoload.php'; // Composerでインストールした場合
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

if(isset($_POST['excelModalBtn'])){
    $fileNames=[];
    $values=explode(",",$_POST['excelCheck']);
    foreach($values as $value){
        $excelChecks=$table->select('SELECT * FROM t_attendance_head LEFT JOIN m_employee on m_employee.id=t_attendance_head.employee_id WHERE t_attendance_head.id=:head_id',['head_id'=>$value]);
        $excelDetails=$table->select('SELECT * FROM t_attendance_detail WHERE head_id=:head_id',['head_id'=>$value]);
        /* 勤務時間と残業時間の秒の合計を取得 */
        $sumExcelDetails=$table->select('SELECT SUM(TIME_TO_SEC(work_time)) as sum_work,SUM(TIME_TO_SEC(over_time)) as sum_over FROM t_attendance_detail WHERE head_id=:head_id',['head_id'=>$value]);

        // 新しいスプレッドシート作成
        foreach($excelChecks as $excelCheck){
            $date = DateTime::createFromFormat('Ym', $excelCheck['yyyymm']);
            $yyyy_mm=$date->format('Y-m');
            $part=explode('-',$yyyy_mm);
            $yyyy=$part[0];
            $mm=$part[1];
            $weeks=['日','月','火','水','木','金','土'];
            $firstWeekDay=date('w',strtotime($yyyy_mm.'-01'));

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            /* 文字の大きさを調整 */
            $spreadsheet->getDefaultStyle()->getFont()->setSize(5);

            //行と列の幅を設定
            $sheet->getDefaultColumnDimension()->setWidth(15);
            $sheet->getDefaultRowDimension()->setRowHeight(10);
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('K')->setWidth(50);
            /* セルを結合 */
            $sheet->mergeCells('H2:I2');
            $sheet->mergeCells('H3:I3');
            /* |で折り返すようにした */
            $headers = [['日', '曜日', '区分', '開始|時刻','終了|時刻','昼休憩|時間(h)','夜休憩|時間(h)','勤務|時間','残業|時間','備考']];
            foreach($excelDetails as $excelDetail){
                $date=($firstWeekDay+$excelDetail['day']-1)%7;
                $kbn='';
                $start_time='';
                $end_time='';
                $rest_time='';
                $night_rest_time='';
                $work_time='';
                $over_time='';
                if($excelDetail['kbn']==1){
                    $kbn="出勤";
                    $start=new DateTime("{$excelDetail['start_time']}");
                    $start_time=$start->format("H:i");
                    $end=new DateTime("{$excelDetail['end_time']}");
                    $end_time=$end->format("H:i");
                    $rest=new DateTime("{$excelDetail['rest_time']}");
                    $rest_time=$rest->format("H:i");
                    $night_rest=new DateTime("{$excelDetail['night_rest_time']}");
                    $night_rest_time=$night_rest->format("H:i");
                    $work=new DateTime("{$excelDetail['work_time']}");
                    $work_time=$work->format("H:i");
                    $over=new DateTime("{$excelDetail['over_time']}");
                    $over_time=$over->format("H:i");
                }
                if($excelDetail['kbn']==2){
                    $kbn="休日";
                }
                if($excelDetail['kbn']==3){
                    $kbn="有給";
                }
                if($excelDetail['kbn']==4){
                    $kbn="休出";
                    $start=new DateTime("{$excelDetail['start_time']}");
                    $start_time=$start->format("H:i");
                    $end=new DateTime("{$excelDetail['end_time']}");
                    $end_time=$end->format("H:i");
                    $rest=new DateTime("{$excelDetail['rest_time']}");
                    $rest_time=$rest->format("H:i");
                    $night_rest=new DateTime("{$excelDetail['night_rest_time']}");
                    $night_rest_time=$night_rest->format("H:i");
                    $work=new DateTime("{$excelDetail['work_time']}");
                    $work_time=$work->format("H:i");
                    $over=new DateTime("{$excelDetail['over_time']}");
                    $over_time=$over->format("H:i");
                }
                if($excelDetail['kbn']==5){
                    $kbn="欠勤";
                }
                if($excelDetail['kbn']==6){
                    $kbn="特休";
                }
                if($excelDetail['kbn']==7){
                    $kbn="代休";
                }
                if($excelDetail['kbn']==8){
                    $kbn="振休";
                }
                $headers[]=[$excelDetail['day'],$weeks[$date],$kbn,$start_time,$end_time,$rest_time,$night_rest_time,$work_time,$over_time,$excelDetail['remarks']];
            }
            
            $employee=[['社員番号',"{$excelCheck['employee_no']}"]
                        ,['氏名',"{$excelCheck['employee_name']}"]
                        ];
            /* 勤務時間合計と残業時間合計を秒からH:iに変形 */
            foreach($sumExcelDetails as $sumExcelDetail){
                $work_H=floor($sumExcelDetail['sum_work']/3600);
                $work_i=floor(($sumExcelDetail['sum_work']%3600)/60);
                $over_H=floor(($sumExcelDetail['sum_over']/3600));
                $over_i=floor(($sumExcelDetail['sum_over']%3600)/60);
                if($work_H<10){
                    $work_H="0{$work_H}";
                }
                if($work_i<10){
                    $work_i="0{$work_i}";
                }
                if($over_H<10){
                    $over_H="0{$over_H}";
                }
                if($over_i<10){
                    $over_i="0{$over_i}";
                }
            }
            $sum=[['勤務時間合計','',"{$work_H}:{$work_i}"]
                        ,['残業時間合計','',"{$over_H}:{$over_i}"]
                        ];
            /* |で改行できるように設定 */
            $data = array_map(fn($v) => str_replace('|', "\n", $v), $headers);
            $row = 7;
            
            foreach ($data as $rowData) {
                $col = 'B';
                $remarks=floor(mb_strlen($rowData[9])/30)+1;
                foreach ($rowData as $cellValue) {
                    /* データ挿入 */
                    $sheet->setCellValue($col . $row, $cellValue);
                    $col++;
                }
                /* セルの高さ調整 */
                if($row==7){
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                else{
                    $sheet->getRowDimension($row)->setRowHeight(12*$remarks);
                }
                $row++;
            }

            $employeeRow=2;
            foreach ($employee as $rowData) {
                $employeeCol = 'B';
                foreach ($rowData as $cellValue) {
                    $sheet->setCellValue($employeeCol . $employeeRow, $cellValue);
                    $employeeCol++;
                }
                $employeeRow++;
                for ($i = 2; $i < $employeeRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(10);
                }
            }

            $sumRow=2;
            foreach ($sum as $rowData) {
                $sumCol = 'H';
                foreach ($rowData as $cellValue) {
                    $sheet->setCellValue($sumCol . $sumRow, $cellValue);
                    $sumCol++;
                }
                $sumRow++;
                for ($i = 2; $i < $sumRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(10);
                }
            }

            /* 背景を変更 */
            $sheet->getStyle('B7:K7')->applyFromArray([
                
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'B0C4DE']
                ]
            ]);
            
            $sheet->getStyle('B7:K' . ($row - 1))->applyFromArray([
                /* 上下中央寄せ */
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER
                ],
                /* 表の枠を作成 */
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            /* 背景を変更 */
            $sheet->getStyle('B2:B3')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C0C0C0']
                ]
            ]);

            /* 表の枠を作成 */
            $sheet->getStyle('B2:C3')->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            $sheet->getStyle('H2:H3')->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '98FB98']
                ]
            ]);

            $sheet->getStyle('H2:J3')->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
            /* 文字を右寄せ */
            $sheet->getStyle('B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            /* 折り返しアリにする */
            $sheet->getStyle('B7:K7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('K7:K'. ($row - 1))->getAlignment()->setWrapText(true);
            $sheet->setCellValue('B6', "{$yyyy}年{$mm}月");
            $tempFile = sys_get_temp_dir() . "/{$excelCheck['yyyymm']}_{$excelCheck['employee_name']}.xlsx";
            $fileNames[]=$tempFile;
            /* チェックが一つの時はexcelファイルをダウンロードさせる */
            if(count($values)==1){
                #Excelファイルをブラウザに直接ダウンロードさせる
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header("Content-Disposition: attachment;filename={$excelCheck['yyyymm']}_{$excelCheck['employee_name']}.xlsx");
                header('Cache-Control: max-age=0');
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                exit;
            }
            else{
                #一時ディレクトリに保存
                $writer = new Xlsx($spreadsheet);
                $writer->save($tempFile);
            }
        }
    }
    if(count($values)>1){
        $zipFile = __DIR__ . '/excels_' . date('Ymd_His') . '.zip';
        $zip = new ZipArchive();
        /* zipファイルを作成する */
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        /* zipファイルにexcelファイルを入れる */
        foreach ($fileNames as $file) {
            $zip->addFile($file,basename($file)); // ZIP内のファイル名はbasename
        }
        /* zipファイルを閉じる */
        $zip->close();

        #ダウンロード用ヘッダ送信
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        unlink($excelPath);
        unlink($zipPath);
        rmdir($tempDir);
        exit;
    }
}

if(isset($_POST['pdfModalBtn'])){
    $fileNames=[];
    $values=explode(",",$_POST['pdfCheck']);
    foreach($values as $value){
        $pdfChecks=$table->select('SELECT * FROM t_attendance_head LEFT JOIN m_employee on m_employee.id=t_attendance_head.employee_id WHERE t_attendance_head.id=:head_id',['head_id'=>$value]);
        $pdfDetails=$table->select('SELECT * FROM t_attendance_detail WHERE head_id=:head_id',['head_id'=>$value]);
        /* 勤務時間と残業時間の秒の合計を取得 */
        $sumPdfDetails=$table->select('SELECT SUM(TIME_TO_SEC(work_time)) as sum_work,SUM(TIME_TO_SEC(over_time)) as sum_over FROM t_attendance_detail WHERE head_id=:head_id',['head_id'=>$value]);

        // 新しいスプレッドシート作成
        foreach($pdfChecks as $pdfCheck){
            $date = DateTime::createFromFormat('Ym', $pdfCheck['yyyymm']);
            $yyyy_mm=$date->format('Y-m');
            $part=explode('-',$yyyy_mm);
            $yyyy=$part[0];
            $mm=$part[1];
            $weeks=['日','月','火','水','木','金','土'];
            $firstWeekDay=date('w',strtotime($yyyy_mm.'-01'));

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            /* 文字の大きさを調整 */
            $spreadsheet->getDefaultStyle()->getFont()->setSize(6);

            //行と列の幅を設定
            $sheet->getDefaultColumnDimension()->setWidth(10);
            $sheet->getDefaultRowDimension()->setRowHeight(10);
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('K')->setWidth(50);
            /* セルを結合 */
            $sheet->mergeCells('H2:I2');
            $sheet->mergeCells('H3:I3');
            /* |で折り返すようにした */
            $headers = [['日', '曜日', '区分', '開始|時刻','終了|時刻','昼休憩|時間(h)','夜休憩|時間(h)','勤務|時間','残業|時間','備考']];
            foreach($pdfDetails as $pdfDetail){
                $date=($firstWeekDay+$pdfDetail['day']-1)%7;
                $kbn='';
                $start_time='';
                $end_time='';
                $rest_time='';
                $night_rest_time='';
                $work_time='';
                $over_time='';
                if($pdfDetail['kbn']==1){
                    $kbn="出勤";
                    $start=new DateTime("{$pdfDetail['start_time']}");
                    $start_time=$start->format("H:i");
                    $end=new DateTime("{$pdfDetail['end_time']}");
                    $end_time=$end->format("H:i");
                    $rest=new DateTime("{$pdfDetail['rest_time']}");
                    $rest_time=$rest->format("H:i");
                    $night_rest=new DateTime("{$pdfDetail['night_rest_time']}");
                    $night_rest_time=$night_rest->format("H:i");
                    $work=new DateTime("{$pdfDetail['work_time']}");
                    $work_time=$work->format("H:i");
                    $over=new DateTime("{$pdfDetail['over_time']}");
                    $over_time=$over->format("H:i");
                }
                if($pdfDetail['kbn']==2){
                    $kbn="休日";
                }
                if($pdfDetail['kbn']==3){
                    $kbn="有給";
                }
                if($pdfDetail['kbn']==4){
                    $kbn="休出";
                    $start=new DateTime("{$pdfDetail['start_time']}");
                    $start_time=$start->format("H:i");
                    $end=new DateTime("{$pdfDetail['end_time']}");
                    $end_time=$end->format("H:i");
                    $rest=new DateTime("{$pdfDetail['rest_time']}");
                    $rest_time=$rest->format("H:i");
                    $night_rest=new DateTime("{$pdfDetail['night_rest_time']}");
                    $night_rest_time=$night_rest->format("H:i");
                    $work=new DateTime("{$pdfDetail['work_time']}");
                    $work_time=$work->format("H:i");
                    $over=new DateTime("{$pdfDetail['over_time']}");
                    $over_time=$over->format("H:i");
                }
                if($pdfDetail['kbn']==5){
                    $kbn="欠勤";
                }
                if($pdfDetail['kbn']==6){
                    $kbn="特休";
                }
                if($pdfDetail['kbn']==7){
                    $kbn="代休";
                }
                if($pdfDetail['kbn']==8){
                    $kbn="振休";
                }
                $headers[]=[$pdfDetail['day'],$weeks[$date],$kbn,$start_time,$end_time,$rest_time,$night_rest_time,$work_time,$over_time,$pdfDetail['remarks']];
            }
            
            $employee=[['社員番号',"{$pdfCheck['employee_no']}"]
                        ,['氏名',"{$pdfCheck['employee_name']}"]
                        ];
            /* 勤務時間合計と残業時間合計を秒からH:iに変形 */
            foreach($sumPdfDetails as $sumPdfDetail){
                $work_H=floor($sumPdfDetail['sum_work']/3600);
                $work_i=floor(($sumPdfDetail['sum_work']%3600)/60);
                $over_H=floor(($sumPdfDetail['sum_over']/3600));
                $over_i=floor(($sumPdfDetail['sum_over']%3600)/60);
                if($work_H<10){
                    $work_H="0{$work_H}";
                }
                if($work_i<10){
                    $work_i="0{$work_i}";
                }
                if($over_H<10){
                    $over_H="0{$over_H}";
                }
                if($over_i<10){
                    $over_i="0{$over_i}";
                }
            }
            $sum=[['勤務時間合計','',"{$work_H}:{$work_i}"]
                        ,['残業時間合計','',"{$over_H}:{$over_i}"]
                        ];
            /* |で改行できるように設定 */
            $data = array_map(fn($v) => str_replace('|', "\n", $v), $headers);
            $row = 7;
            
            foreach ($data as $rowData) {
                $col = 'B';
                $remarks=floor(mb_strlen($rowData[9])/30)+1;
                foreach ($rowData as $cellValue) {
                    /* データ挿入 */
                    $sheet->setCellValue($col . $row, $cellValue);
                    $col++;
                }
                /* セルの高さ調整 */
                if($row==7){
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                else{
                    $sheet->getRowDimension($row)->setRowHeight(12*$remarks);
                }
                $row++;
            }

            $employeeRow=2;
            foreach ($employee as $rowData) {
                $employeeCol = 'B';
                foreach ($rowData as $cellValue) {
                    $sheet->setCellValue($employeeCol . $employeeRow, $cellValue);
                    $employeeCol++;
                }
                $employeeRow++;
                for ($i = 2; $i < $employeeRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(10);
                }
            }

            $sumRow=2;
            foreach ($sum as $rowData) {
                $sumCol = 'H';
                foreach ($rowData as $cellValue) {
                    $sheet->setCellValue($sumCol . $sumRow, $cellValue);
                    $sumCol++;
                }
                $sumRow++;
                for ($i = 2; $i < $sumRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(10);
                }
            }

            /* 背景を変更 */
            $sheet->getStyle('B7:K7')->applyFromArray([
                
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'B0C4DE']
                ]
            ]);
            
            $sheet->getStyle('B7:K' . ($row - 1))->applyFromArray([
                /* 上下中央寄せ */
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER
                ],
                /* 表の枠を作成 */
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            /* 背景を変更 */
            $sheet->getStyle('B2:B3')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C0C0C0']
                ]
            ]);

            /* 表の枠を作成 */
            $sheet->getStyle('B2:C3')->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            $sheet->getStyle('H2:H3')->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '98FB98']
                ]
            ]);

            $sheet->getStyle('H2:J3')->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
            /* 文字を右寄せ */
            $sheet->getStyle('B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            /* 折り返しアリにする */
            $sheet->getStyle('B7:K7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('K7:K'. ($row - 1))->getAlignment()->setWrapText(true);
            $sheet->setCellValue('B6', "{$yyyy}年{$mm}月");

            $tempDir = sys_get_temp_dir();
            $excelPath = "{$tempDir}/{$pdfCheck['yyyymm']}_{$pdfCheck['employee_name']}.xlsx";

            // Excel保存
            $writer = new Xlsx($spreadsheet);
            $writer->save($excelPath);

            // 2. LibreOffice CLIでPDF変換
            $cmd = sprintf(
                '"C:\\Program Files\\LibreOffice\\program\\soffice.exe" --headless --convert-to pdf --outdir %s %s',
                escapeshellarg($tempDir),
                escapeshellarg($excelPath)
            );
            exec($cmd, $output, $returnVar);

            // LibreOfficeは同じファイル名で.pdfを出力するので取得
            $generatedPdfPath = str_replace('.xlsx', '.pdf', $excelPath);
            $fileNames[]=$generatedPdfPath;
            /* チェックが一つの時はpdfファイルをダウンロードする */
            if(count($values)==1){
                // 3. PDFをダウンロードとして出力
                header('Content-Type: application/pdf');
                header("Content-Disposition: attachment; filename={$pdfCheck['yyyymm']}_{$pdfCheck['employee_name']}.pdf");
                header('Content-Length: ' . filesize($generatedPdfPath));
                readfile($generatedPdfPath);
            }
        }
    }
    if(count($values)>1){
        $zipFile = __DIR__ . '/pdf_' . date('Ymd_His') . '.zip';
        /* zipファイルを作成する */
        $zip = new ZipArchive();
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        /* zipファイルにpdfファイルを入れる */
        foreach ($fileNames as $file) {
            $zip->addFile($file,basename($file)); // ZIP内のファイル名はbasename
        }
        /* zipファイルを閉じる */
        $zip->close();

        #ダウンロード用ヘッダ送信
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        unlink($excelPath);
        unlink($zipPath);
        rmdir($tempDir);
        exit;
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
                const checks=[];
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

            $(document).on("click","#pdfBtn",function(){
                $(".alert").remove();
                const checks=[];
                $(':checkbox[name="checkbox"]:checked').each(function(){
                    checks.push($(this).val());
                })
                if(checks.length===0){
                    $("#title").after('<div class="alert alert-danger" role="alert">勤怠を選択してください</div>');
                }
                else{
                    $("#pdfModal").modal("show");
                    var status=checks.join(",");
                    $("#pdfCheck").val(status);
                }
            })
        })
    </script>
</head>

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
                        echo $error->alert("alert-success","差戻処理が完了しました");
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
                            <button type="button" class="btn btn-light" id="pdfBtn">PDF出力</button>
                        </div>
                    </div>
                    <div class="container">
                        <table class="table mt-2">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col align-text-top">#</th>
                                    <th scope="col align-text-top">年月</th>
                                    <th scope="col align-text-top">社員番号</th>
                                    <th scope="col align-text-top">社員名</th>
                                    <th scope="col align-text-top">ステータス</th>
                                    <th scope="col align-text-top">確認</th>
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
            <input type="submit" class="btn btn-success" name="excelModalBtn" value="出力">
        </div><!-- /.modal-footer -->
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h1 class="modal-title fs-5 text-light" id="exampleModalLabel">PDF出力</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
      </div>
      <div class="modal-body">
        <p>選択した勤怠のPDF出力を行いますか?</p>
      </div>
      <form method="POST" action="management.php<?php echo $_SESSION['management_url'];?>">
        <div class="modal-footer">
            <input type="hidden" id="pdfCheck" name="pdfCheck">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
            <input type="submit" class="btn btn-success" name="pdfModalBtn" value="出力">
        </div><!-- /.modal-footer -->
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->