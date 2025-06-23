<?php
//Require tập tin autoload.php để tự động nạp thư viện PhpSpreadsheet
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require 'vendor/autoload.php';
// require '../../../../../wp-load.php';
//Khai báo sử dụng các thư viện cần thiết

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

//Khởi tạo đối tượng reader
$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx();

//Khai báo chỉ đọc nội dung dữ liệu (Tức không đọc định dạng)
// $reader->setReadDataOnly(true);
$spreadsheet = $reader->load(AFF_PATH . 'helpers/excel/income.xlsx'); 

//Đọc tập tin Excel

$sheet = $spreadsheet->getActiveSheet();
                      
$styleArray = array(
    'borders' => array(
        'allBorders' => array(
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => array('argb' => '#000'),
        ),
    ),
);
$filters = isset($_GET['filters']) ? $_GET['filters'] : '';
if($filters){
    $filters = base64_decode($filters); 
    $filters = json_decode($filters, true);
}

$data = AFF_User_Order::getUserIncome($filters, 1, 1000);


if(isset($data['data']) && sizeof($data['data'])){
    $count = 2;
    
    foreach ($data['data'] as $key => $d) {

        if($count != 2)
            $sheet->insertNewRowBefore($count);


        $sheet->setCellValue('A'. $count, $d['user_login']);
        $sheet->setCellValue('B'. $count, $d['user']['user_email']);
        $sheet->setCellValue('C'. $count, $d['user']['user_phone']);
        $sheet->setCellValue('D'. $count, $d['total']);
        $sheet->setCellValue('E'. $count, $d['total_directly']);
        $sheet->setCellValue('F'. $count, $d['total_descendants']);
        $sheet->setCellValue('G'. $count, $d['commission']);

        $count++;
    }

    $count++;

    

}

$writer = new Xlsx( $spreadsheet );
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="doanh-thu.xlsx"');
$writer->save('php://output');