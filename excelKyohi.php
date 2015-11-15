<?php
//set_include_path(get_include_path().PATH_SEPARATOR.$_SERVER["DOCUMENT_ROOT"].'/Classes/');
set_include_path(get_include_path().PATH_SEPARATOR.'./Classes/');
include_once('PHPExcel.php');
require_once "common.php";

$maxRow = 48;
mb_internal_encoding("UTF-8");


if(!array_key_exists("output", $_REQUEST)) {
	?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="REFRESH" content="1;URL=excelKyohi.php?output=1">
</head>
<body>
<br>
<B>Excelファイルを生成します。</B><br>
<B>必ず[保存]を選択してください。[開く]を選択するとファイルの取得に失敗します。</B><br>
<div style="font: normal normal bold 8pt/normal; color:#FF0000;">※外部ファイルにダウンロードするとセキュリティ保護が行えません。取り扱いには注意してください。</div>
</body>
</html>

	<?php
}
else {
	$excel = new PHPExcel();
	$excel->removeSheetByIndex(0);
	
	$sheet = $excel->createSheet();
	sheetInitialize($sheet);
	
	$rowHead = 0;
	printHeader($sheet, $rowHead);
	
	$lineCnt = 0;
	$currentRow = 0;
	$offsetRow = 0;
	$offsetCol = 0;
	$dioceseAry = $db->getDiocese();
	foreach($dioceseAry as $diocese) {
		$dioceseID   = $diocese["ID"];
		$dioceseName = $diocese["Name"];
	
		writeBorders($sheet);
		
		$teamAry = $db->getTeamNo($dioceseID);
		foreach($teamAry as $team) {
			$members = $db->getTeamMember($dioceseID, $team);
			$memberCnt = count($members) + 2;
			if($currentRow == $maxRow) {
				$lineCnt++;
				$currentRow = 0;
				writeBorders($sheet);
			}
			if(($currentRow + $memberCnt) <= $maxRow) {
				printMember($sheet, $dioceseName, $team, $members, $offsetRow + $currentRow, $offsetCol, 0, $memberCnt);
				$currentRow += $memberCnt;
			}
			else {
				$remineMember = ($currentRow + $memberCnt) - $maxRow;
				printMember($sheet, $dioceseName, $team, $members, $offsetRow + $currentRow, $offsetCol, 0, $memberCnt - $remineMember);
				$lineCnt++;
				$currentRow = 0;
				writeBorders($sheet);
				printMember($sheet, $dioceseName, $team, $members, $offsetRow + $currentRow, $offsetCol, $memberCnt - $remineMember, $remineMember);
				$currentRow = $remineMember;
			}
		}
		$lineCnt++;
		$currentRow = 0;
	}
	$sheet->setSelectedCell("A1");
	$excel->setActiveSheetIndex(0);
	
//	$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
	$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
//	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Type: application/vnd.ms-excel');
//	header('Content-Disposition: attachment;filename="' . mb_convert_encoding("教費台帳", "SJIS") . '.xlsx"');
	header('Content-Disposition: attachment;filename="' . mb_convert_encoding("教費台帳", "SJIS") . '.xls"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
}
exit;



function sheetInitialize($sheet)
{
	// 印刷設定
	$sheet->getPageSetup()->setHorizontalCentered(true);
	$sheet->getPageMargins()->setLeft(CentimetersToInch(0.5));
	$sheet->getPageMargins()->setRight(CentimetersToInch(0.5));
	$sheet->getPageMargins()->setTop(CentimetersToInch(0.8));
	$sheet->getPageMargins()->setBottom(CentimetersToInch(1.0));
	$sheet->getPageMargins()->setHeader(CentimetersToInch(0.4));
	$sheet->getPageMargins()->setFooter(CentimetersToInch(0.4));
	$sheet->getHeaderFooter()->setOddFooter("&P / &N ページ");
	$sheet->getHeaderFooter()->setEvenFooter("&P / &N ページ");

	// フォント設定
	$sheet->getDefaultStyle()->getFont()->setName('ＭＳ 明朝');
	$sheet->getDefaultStyle()->getFont()->setSize(13);
	
	// セル設定
	$sheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getDefaultRowDimension()->setRowHeight(14);
}

function printHeader($sheet, &$row)
{
	// ラベル行の設定
	$row = 3;
	$col = 0;
	for($i = 0 ; $i < 2; $i++) {
		$sheet->getCellByColumnAndRow($col, $row)->setValue("班名");
		$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(11*1.05);
		$col++;
		$sheet->getCellByColumnAndRow($col, $row)->setValue("No");
		$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(4*1.05);
		$col++;
		$sheet->getCellByColumnAndRow($col, $row)->setValue("筆頭者");
		$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(15*1.05);
		$col++;
		$sheet->getCellByColumnAndRow($col, $row)->setValue("");
		$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(6*1.05);
		$col++;
		$sheet->getCellByColumnAndRow($col, $row)->setValue("");
		$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(6*1.05);
		$col++;
	}
	$col--;
	$sheet->getStyle(getRangeStr(0, $row, 4, $row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle(getRangeStr(0, $row, 4, $row))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

	$sheet->getStyle(getRangeStr(5, $row, 9, $row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle(getRangeStr(5, $row, 9, $row))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
	
	// タイトル行の設定
	$row = 1;
	$sheet->mergeCells(getRangeStr(0, $row, $col, $row));
	$sheet->getCellByColumnAndRow(0, $row)->setValue("教費台帳");
	$sheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
	$sheet->getStyleByColumnAndRow(0, $row)->getFont()->setSize(16);
	$sheet->getRowDimension($row)->setRowHeight(18);

	// ヘッダ全体の設定
	$sheet->getStyle(getRangeStr(0, 1, $col, 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

	// 作成日の設定
	$year  = date('Y');
	$month = date('m');
	$day   = date('d');
	$row = 2;
	$sheet->getCellByColumnAndRow($col, $row)->setValue(getWareki($year, $month, $day) . $month . "月" . $day . "日 作成");
	$sheet->getStyle(getRangeStr($col, $row, $col, $row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	$row = 4;
	$sheet->freezePaneByColumnAndRow(0, $row);
}

function writeBorders($sheet)
{
	global $rowHead, $lineCnt, $maxRow, $offsetRow, $offsetCol;
	$offsetRow = $rowHead + intval($lineCnt / 2) * $maxRow;
	$offsetCol = ($lineCnt % 2) * 5;
	if($lineCnt != 0 && ($lineCnt % 2) == 0) {
		$sheet->setBreakByColumnAndRow(0, $offsetRow - 1, PHPExcel_Worksheet::BREAK_ROW);
	}

	$sheet->getStyle(getRangeStr(0, $offsetRow, 4, $offsetRow + $maxRow - 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle(getRangeStr(0, $offsetRow, 4, $offsetRow + $maxRow - 1))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

	$sheet->getStyle(getRangeStr(5, $offsetRow, 9, $offsetRow + $maxRow - 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle(getRangeStr(5, $offsetRow, 9, $offsetRow + $maxRow - 1))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
}

function printMember($sheet, $dioceseName, $team, $members, $row, $col, $begin, $cnt)
{
	// 班名
	$sheet->getCellByColumnAndRow($col, $row)->setValue($dioceseName . $team . "班");
	$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->mergeCellsByColumnAndRow($col, $row, $col, $row + $cnt - 1);
	$col++;

	// 班員
	$arrayEnd = count($members) >= ($begin + $cnt) ? ($begin + $cnt) : count($members);
	for($i = $begin ; $i < $arrayEnd ; $i++) {
		$sheet->getCellByColumnAndRow($col, $row)->setValue($i + 1);
		$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$sheet->getCellByColumnAndRow($col + 1, $row)->setValue($members[$i]["Name"]);
		if($members[$i]["isLeader"] == 1) {
			$sheet->getStyleByColumnAndRow($col + 1, $row)->getFont()->setBold(true);
		}
		$row++;
	}
}

function getRangeStr($colBegin, $rowBegin, $colEnd, $rowEnd)
{
	$str  = ($colBegin != -1) ? PHPExcel_Cell::stringFromColumnIndex($colBegin) : "";
	$str .= ($rowBegin != -1) ? "$rowBegin:"  : ":";
	$str .= ($colEnd != -1)   ? PHPExcel_Cell::stringFromColumnIndex($colEnd) : "";
	$str .= ($rowEnd != -1)   ? $rowEnd : "";
	
	return $str;
}

function CentimetersToInch($centi)
{
	return $centi * 0.39130434782608695652173913043478;
}

?>
