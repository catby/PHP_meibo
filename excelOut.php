<?php
//set_include_path(get_include_path().PATH_SEPARATOR.$_SERVER["DOCUMENT_ROOT"].'/Classes/');
set_include_path(get_include_path().PATH_SEPARATOR.'./Classes/');
include_once('PHPExcel.php');
require_once "common.php";

$maxRow = 40;
mb_internal_encoding("UTF-8");


if(!array_key_exists("output", $_REQUEST)) {	// Excel作成条件設定
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<script type="text/javascript">
<!--

function defaultSetting()
{
	for(var i = 4 ; i <= 10 ; i++) {
		var id = "column" + i;
		document.getElementById(id).style.display = "none";
	}
	checkWidth();
}

function newColumn(idx)
{
	var id = "column" + idx;
	document.getElementById(id).style.display = "";
}

function checkWidth()
{
	var size = 5 + 16;
	for(var i = 3 ; i <= 10 ; i++) {
		var name = "size_" + i;
		if(document.Main.elements[name].value != "") {
			size = size + parseInt(document.Main.elements[name].value);
		}
	}
	document.getElementById("widthSize").innerHTML = (87 - size);
}

// -->
</script>


</head>
<body onLoad="defaultSetting();">
<H2>班員名簿作成 (Excelファイル)</H2>
<HR>
<form action="excelOut.php" method="post" name="Main">
<input type="hidden" name="output" value="true">
<table class="normal">
<tr><td>
<b>タイトル：</b><input type="text" name="title" size="50">
</td></tr>
<tr><td>
<table class="noFullSize">
<tr><th></th><th>ラベル</th><th>フォーマット</th><th>配置</th><th>列の幅</th></tr>
<tr><th>1列目</th><td><input type="text" name="label_1" size="10" value="班" disabled></td><td><?= getFormatSelect("format_1", "default", false) ?></td><td><?= getLayoutSelect("layout_1", "center", false) ?></td><td><input type="text" name="size_1" size="10" value="5" disabled></td></tr>
<tr><th>2列目</th><td><input type="text" name="label_2" size="10" value="名前" disabled></td><td><?= getFormatSelect("format_1", "default", false) ?></td><td><?= getLayoutSelect("layout_2", "default", false) ?></td><td><input type="text" name="size_2" size="10" value="16" disabled></td></tr>
<?php
for($i = 3 ; $i <= 10 ; $i++) {
?>
<tr id="column<?= $i ?>"><th><?= $i ?>列目</th><td><input type="text" name="label_<?= $i ?>" size="10" onchange="newColumn(<?= $i+1 ?>)"></td><td><?= getFormatSelect("format_".$i, "default") ?></td><td><?= getLayoutSelect("layout_".$i, "default") ?></td><td><input type="text" name="size_<?= $i ?>" size="10" style="ime-mode:disabled;" onChange="checkWidth();"></td></tr>
<?php
}
?>
</table>
<table class="normal" style="font-size: 90%;">
<tr><td style="vertical-align: top; padding: 0px;">※</td>
<td style=" padding: 0px;">
列の幅の合計値は1,2列目も含めて87以内にしてください。(現在の合計値：<span id="widthSize"></span>)<BR>
幅の指定が無い、または0指定の場合は、その列に残りの幅を均等配分します。
</td></tr>
</table>
<BR>
<DIV align="center"><input type="submit" value="Excelファイル作成" style="width=200px;"></DIV>
</td></tr>
</table>
<table class="normal" style="font-size: 90%;">
<tr><td style="vertical-align: top; padding: 0px;">※</td>
<td style=" padding: 0px;">
[Excelファイル作成]ボタンを押した際に表示されるダイアログ・ボックスでは[保存]を選択してください。<BR>
[開く]を選択した場合、プログラムの制約によりファイルの取得に失敗します。
</td></tr>
</table>
</form>
</body>
</html>
<?php
}
else {											// Excel作成
	$item = Array();
	$itemWidth = 0;
	$itemAutoWidth = 0;
	for($i = 3 ; $i <= 10 ; $i++) {
		if(strlen($_REQUEST["label_".$i])) {
			$tmp = array(
						"label"		=> $_REQUEST["label_".$i],
						"format"	=> $_REQUEST["format_".$i],
						"layout"	=> $_REQUEST["layout_".$i],
						"size"		=> intval($_REQUEST["size_".$i]),
						);
			$itemWidth += $tmp["size"];
			if($tmp["size"] == 0) {
				$itemAutoWidth++;
			}
			array_push($item, $tmp);
		}
		else {
			break;
		}
	}
	if($itemAutoWidth) {
		$autoWidth = (66 - $itemWidth) / $itemAutoWidth;
		for($i = 0 ; $i < count($item) ; $i++) {
			if($item[$i]["size"] == 0) {
				$item[$i]["size"] = $autoWidth;
			}
		}
	}
	
	$excel = new PHPExcel();
	$excel->removeSheetByIndex(0);
	
	$dioceseAry = $db->getDiocese();
	foreach($dioceseAry as $diocese) {
		$dioceseID   = $diocese["ID"];
		$dioceseName = $diocese["Name"];
		
		$teamAry = $db->getTeamNo($dioceseID);
		if(count($teamAry)) {
			$sheet = $excel->createSheet();
			$cnt = 0;
			$row = 1;
			sheetInitialize($sheet, $dioceseName);
			printHeader($sheet, $_REQUEST["title"], $dioceseName, $row, $item);
			
			foreach($teamAry as $team) {
				$row_TeamBegin = $row;
				$members = $db->getTeamMember($dioceseID, $team);
				if(($cnt + count($members) + 1) > $maxRow) {
					$cnt = 0;
					$sheet->setBreakByColumnAndRow(0, $row-1, PHPExcel_Worksheet::BREAK_ROW);
				}
				for($j = 0 ; $j < count($members) ; $j++) {
					printMember($sheet, $team, $members[$j]["Name"], $row, $item, ($j == 0));
					$row++;
				}
				printMember($sheet, $team, "", $row, $item, false);
				$sheet->getStyle(getRangeStr(0, $row_TeamBegin, 0, $row))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$row++;
				$cnt += count($members) + 1;
			}
		}
		$sheet->setSelectedCell("A1");
	}
	$excel->setActiveSheetIndex(0);
	
	$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
	//header('Content-Type: application/octet-stream');
	header('Content-Type: application/vnd.ms-excel');
	if(strlen($_REQUEST["title"])) {
		header('Content-Disposition: attachment;filename="' . mb_convert_encoding($_REQUEST["title"], "SJIS") . '.xls"');
	}
	else {
		header('Content-Disposition: attachment;filename="' . mb_convert_encoding("班員名簿", "SJIS") . '.xls"');
	}
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');

	exit;
}


function getFormatSelect($name, $defaultVal, $isEnable = true)
{
	$format = array(
		"default"	=> "標準",
		"date"		=> "日付",
		"yen"		=> "金額",
		"int"		=> "数値",
	);
	
	$retStr = "";
	$retStr .= "<select name=\"$name\" style=\"width:100px\";" . ($isEnable ? ">" : " disabled>");
	foreach($format as $key => $value) {
		if($defaultVal == $key) {
			$retStr .= "<option value=\"$key\" selected>$value</option>";
		}
		else {
			$retStr .= "<option value=\"$key\">$value</option>";
		}
	}
	$retStr .= "</select>";
	return $retStr;
}

function getLayoutSelect($name, $defaultVal, $isEnable = true)
{
	$layout = array(
		"default"	=> "標準",
		"left"		=> "左詰め",
		"center"	=> "中央揃え",
		"right"		=> "右詰め",
	);
	
	$retStr = "";
	$retStr .= "<select name=\"$name\" style=\"width:100px\";" . ($isEnable ? ">" : " disabled>");
	foreach($layout as $key => $value) {
		if($defaultVal == $key) {
			$retStr .= "<option value=\"$key\" selected>$value</option>";
		}
		else {
			$retStr .= "<option value=\"$key\">$value</option>";
		}
	}
	$retStr .= "</select>";
	return $retStr;
}



function sheetInitialize($sheet, $sheetName)
{
	// 印刷設定
	$sheet->getPageSetup()->setHorizontalCentered(true);
	$sheet->getPageMargins()->setLeft(CentimetersToInch(1.5));
	$sheet->getPageMargins()->setRight(CentimetersToInch(1.5));
	$sheet->getPageMargins()->setTop(CentimetersToInch(1.8));
	$sheet->getPageMargins()->setBottom(CentimetersToInch(1.8));
	$sheet->getPageMargins()->setHeader(CentimetersToInch(0.9));
	$sheet->getPageMargins()->setFooter(CentimetersToInch(0.9));
	$sheet->getHeaderFooter()->setOddFooter("&P / &N ページ");
	$sheet->getHeaderFooter()->setEvenFooter("&P / &N ページ");

	// フォント設定
	$sheet->getDefaultStyle()->getFont()->setName('ＭＳ 明朝');
	
	// セル設定
	$sheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getDefaultRowDimension()->setRowHeight(18);

	// シート名前設定
	$sheet->setTitle($sheetName . "教区");
}

function printHeader($sheet, $title, $dioceseName, &$row, $items)
{
	// ラベル行の設定
	$row = 2;
	$col = 0;
	$sheet->getCellByColumnAndRow($col, $row)->setValue("班");
	$sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(5*1.05);
	$col++;
	$sheet->getCellByColumnAndRow($col, $row)->setValue("名前");
	$sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(16*1.05);
	
	// 追加ラベル
	foreach($items as $item) {
		$col++;
		$sheet->getCellByColumnAndRow($col, $row)->setValue($item["label"]);
		$sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth($item["size"]*1.05);
	}
	$sheet->getStyle(getRangeStr(0, $row, $col, $row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$sheet->getStyle(getRangeStr(0, $row, $col, $row))->getFill()->getStartColor()->setRGB('dddddd');
	$sheet->setAutoFilterByColumnAndRow(0, $row, $col, $row);

	// タイトル行の設定
	$row = 1;
	$sheet->mergeCells(getRangeStr(0, $row, $col, $row));
	$sheet->getCellByColumnAndRow(0, $row)->setValue($title ."(" . $dioceseName ."教区)");
	$sheet->getStyleByColumnAndRow(0, $row)->getFont()->setSize(16);

	// ヘッダ全体の設定
	$sheet->getStyle(getRangeStr(0, 1, $col, 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 2);

	$row = 3;
	$sheet->freezePaneByColumnAndRow(0, $row);
}

function printMember($sheet, $team, $Name, $row, $items, $isLeader)
{
	// 班番号
	$col = 0;
	$sheet->getCellByColumnAndRow($col, $row)->setValue($team);
	$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	if(!$isLeader) {
		$sheet->getStyleByColumnAndRow($col, $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
	}

	// 名前
	$col++;
	$sheet->getCellByColumnAndRow($col, $row)->setValue($Name);
	$sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	if($isLeader) {
		$sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
	}

	foreach($items as $item) {
		$col++;
		// 罫線
		$sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		// レイアウト
		if($item["layout"] == "left") {
			$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}
		else if($item["layout"] == "center") {
			$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		else if($item["layout"] == "left") {
			$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		}
		
		// フォーマット
		if($item["format"] == "date") {
			$sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode("mm/dd");
		}
		else if($item["format"] == "yen") {
			$sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('"\"#,##0;"\"-#,##0');
		}
		else if($item["format"] == "int") {
			$sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
		}
	}
}

function getCellStr($col, $row)
{
	$str  = PHPExcel_Cell::stringFromColumnIndex($col);
	$str .= $row;
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
