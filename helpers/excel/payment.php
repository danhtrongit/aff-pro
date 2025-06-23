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
$spreadsheet = $reader->load(AFF_PATH . 'helpers/excel/payment.xlsx'); 

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

$data = AFF_Payment::getPayments($filters, 1, 1000);

$payment_statuses = [
    0 => 'Chờ duyệt',
    1 => 'Thành công',
    2 => 'Đã hủy',
];
if(isset($data['data']) && sizeof($data['data'])){
    $count = 2;
    
    foreach ($data['data'] as $key => $d) {

        $d['bank_info'] = json_decode($d['bank_info'], true);
        if($count != 2)
            $sheet->insertNewRowBefore($count);


        $sheet->setCellValue('A'. $count, $d['id']);
        $sheet->setCellValue('B'. $count, $d['user_login']);
        $sheet->setCellValue('C'. $count, $d['amount']);
        $sheet->setCellValue('D'. $count, $d['bank_info']['bank_name']);
        $sheet->setCellValue('E'. $count, $d['bank_info']['bank_number']);
        $sheet->setCellValue('F'. $count, $d['bank_info']['bank_owner']);
        $sheet->setCellValue('G'. $count, $d['description']);
        $sheet->setCellValue('H'. $count, date( 'm/d/Y H:i', strtotime($d['date']) ));
        $sheet->setCellValue('I'. $count, $payment_statuses[$d['status']]);

        $count++;
    }

    $count++;

    

}

$writer = new Xlsx( $spreadsheet );
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="thanh-toan.xlsx"');
$writer->save('php://output');