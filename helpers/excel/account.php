<?php
//Require tập tin autoload.php để tự động nạp thư viện PhpSpreadsheet
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require '../../../../../wp-load.php';
//Khai báo sử dụng các thư viện cần thiết

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

//Khởi tạo đối tượng reader
$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx();

//Khai báo chỉ đọc nội dung dữ liệu (Tức không đọc định dạng)
// $reader->setReadDataOnly(true);
$spreadsheet = $reader->load('account.xlsx'); 

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
$filters = [];
// $start = isset($_GET['start']) ? $_GET['start'] : '';
// $end = isset($_GET['end']) ? $_GET['end'] : '';

// if($start && $end)
//     $filters['date_range'] = [$start, $end]; 
$data = AFF_User::getList($filters, 1, 10000);
$user_level = AFF_Config::getConfig('commission_user_levels');
$user_level = json_decode($user_level, true);

if(isset($data['data']) && sizeof($data['data'])){
    $count = 2;
    
    foreach ($data['data'] as $key => $user) {
        if($count != 2)
            $sheet->insertNewRowBefore($count);


        $sheet->setCellValue('A'. $count, $user['ID']);
        $sheet->setCellValue('B'. $count, $user['user_login']);
        $sheet->setCellValue('C'. $count, $user['display_name']);
        $sheet->setCellValue('D'. $count, $user['user_email']);
        $sheet->setCellValue('E'. $count, $user['user_phone']);
        $sheet->setCellValue('F'. $count, $user['balance']);
        $sheet->setCellValue('G'. $count, $user['income']);
        $sheet->setCellValue('H'. $count, $user_level[$user['level']]['name']);

        // $total['commission'] += intval($user['commission']);
        // $total['income'] += intval($user['income']);
        // $total['balance'] += intval($user['balance']);
        // $total['order_total'] += intval($user['order_total']);

        $count++;
    }

    $count++;

    // $sheet->setCellValue('D'. ($count + 1), $total['commission']);
    // $sheet->setCellValue('E'. ($count + 1), $total['balance']);
    // $sheet->setCellValue('F'. ($count + 1), $total['income']);
    // $sheet->setCellValue('G'. ($count + 1), $total['order_total']);

    // *Số liệu thống kế tính từ ngày...tháng...năm....Đến ngày....tháng...năm         
    

}

$writer = new Xlsx( $spreadsheet );
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Cộng tác viên.xlsx"');
$writer->save('php://output');