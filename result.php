<?php

require_once ('vendor/autoload.php');

ini_set('error_reporting', true);
error_reporting(E_ALL);

$holidays = yaml_parse(filter_var($_POST['holidays'], FILTER_SANITIZE_STRING)) ?: yaml_parse_file('ferien.yaml');

foreach ($holidays as $key => $holiday) {
    $holidays[$key]['start'] = new Carbon\Carbon($holidays[$key]['start'].' 0:00:00');
    $holidays[$key]['end'] = new Carbon\Carbon($holidays[$key]['end'].' 23:59:59');
    $holidays[$key]['name'] = $key;
    $holidays[$key]['done'] = false;
}

$timesTemp = explode("\n", filter_var($_POST['times'], FILTER_SANITIZE_STRING));

$times = [];
$days = ['so', 'mo', 'di', 'mi', 'do', 'fr', 'sa'];
foreach ($timesTemp as $thisTime) {
    if (trim($thisTime)) {
        $tmp = explode(' ', $thisTime);
        $tmp2 = explode(':', $tmp[1]);
        $times[] = [array_search(strtolower($tmp[0]), $days), $tmp2[0], $tmp2[1]];
    }
}

$start = new \Carbon\Carbon(filter_var($_POST['yearStart'], FILTER_SANITIZE_STRING).' 0:00:00');
$end = new \Carbon\Carbon(filter_var($_POST['yearEnd'], FILTER_SANITIZE_STRING).' 23:59:59');

$courseName = filter_var($_POST['course'], FILTER_SANITIZE_STRING);

function isInHoliday(\Carbon\Carbon $d) {
    global $holidays;
    foreach ($holidays as $name => $holiday) {
        if (($holiday['start'] <= $d) && ($holiday['end'] >= $d)) return $holiday;
    }
    return false;
}

function lessonLine($line, $week, $thisTime) {
    global $spreadsheet;
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A'.$line, $week)
        ->setCellValue('B'.$line, $thisTime->formatLocalized('%a'))
        ->setCellValue('C'.$line, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($thisTime))
        ->setCellValue('D'.$line, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($thisTime));

    $spreadsheet->getActiveSheet()->getStyle('C'.$line)->getNumberFormat()->setFormatCode('dd.mm.');
    $spreadsheet->getActiveSheet()->getStyle('D'.$line)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME3);
}

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$range = $start->format('Y').'-'.$end->format('y');
$fileName = $range.' Planung '.$courseName;

$spreadsheet->getProperties()
    ->setTitle($fileName)
    ->setSubject('Schule')
    ->setDescription('Unterrichtsplanung '.$courseName)
    ->setCreator('Schuljahr -- (c) 2021 Christoph Fischer');


$spreadsheet->setActiveSheetIndex(0);
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(3, 'cm');
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(3, 'cm');
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(6, 'cm');
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(6, 'cm');

$current = $start->copy();

setlocale(LC_ALL, 'de_DE.utf8');
\Carbon\Carbon::setLocale('de');

$week = 1;
$line = 1;

while ($current <= $end) {
    $weekHasLessons = false;
    foreach ($times as $time) {
        $thisTime = $current->copy()->next($time[0])->setTime($time[1], $time[2], 0);
        if (!($thisHoliday = isInHoliday($thisTime))) {
            lessonLine($line, $week, $thisTime);
            $weekHasLessons = true;
            $line++;
        } else {
            if ($thisHoliday['done']) break;

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$line, $thisHoliday['name'].' '.$thisHoliday['start']->formatLocalized('%d.%m.%Y').' - '. $thisHoliday['end']->formatLocalized('%d.%m.%Y'));

            $spreadsheet
                ->getActiveSheet()
                ->getStyle('A'.$line.':'.'Z'.$line)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('ffed4c05');
            $spreadsheet
                ->getActiveSheet()
                ->getStyle('A'.$line.':'.'Z'.$line)
                ->getFont()
                ->setBold(true)
                ->setItalic(true);

            $holidays[$thisHoliday['name']]['done'] = true;
            $line++;
            break;
        }
    }
    $current->addWeek(1);
    if ($weekHasLessons) $week++;
}

$fileType = filter_var($_POST['filetype'], FILTER_SANITIZE_STRING) ?: 'Xlsx';

$contentTypes = [
    'Xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'Xls' => 'application/vnd.ms-excel',
    'Ods' => 'application/vnd.oasis.opendocument.spreadsheet',
];

header('Content-Type: '.$contentTypes[$fileType]);
header('Content-Disposition: attachment;filename="'.$fileName.'.'.strtolower($fileType).'"');
header('Cache-Control: max-age=0');
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $fileType);
$writer->save('php://output');
exit;