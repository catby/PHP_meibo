<?php
require_once "common.php";


mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

$keys = array_keys($_REQUEST);
foreach($keys as $key) {
	$_REQUEST[$key] = mb_ereg_replace("^[\s　]+", "", $_REQUEST[$key]);
	$_REQUEST[$key] = mb_ereg_replace("[\s　]+$", "", $_REQUEST[$key]);
}
if(array_key_exists("search", $_REQUEST) && (($_REQUEST["search"] == "done") || ($_REQUEST["search"] == "export"))) {

	if($_REQUEST["search"] == "export") {
		$keys = array_keys($_REQUEST);
		foreach($keys as $key) {
			$_REQUEST[$key] = base64_decode($_REQUEST[$key]);
		}
	}

	$condition = array("range" => $_REQUEST["range"]);
	if(strlen($_REQUEST["ID"]) != 0) {
		$condition["ID"] = $_REQUEST["ID"];
	}
	if(strlen($_REQUEST["Phonetic"]) != 0) {
		$_REQUEST["Phonetic"] = mb_convert_kana($_REQUEST["Phonetic"], "ASHc");
	}
	if($_REQUEST["ageKind"] == "age") {
		$ageMin = 0;
		$ageMax = 0;
		if(strlen($_REQUEST["ageMin"]) != 0) {
			$ageMin = $_REQUEST["ageMin"];
		}
		if(strlen($_REQUEST["ageMax"]) != 0) {
			$ageMax = $_REQUEST["ageMax"];
		}
		if($ageMin > $ageMax) {
			list($ageMin, $ageMax) = array($ageMax, $ageMin);
		}
		$condition["ageMin"] = (date('Y') - $ageMin) . date('md');
		$condition["ageMax"] = (date('Y') - ($ageMax+1)) . date('md', strtotime("-1 day"));
	}
	elseif($_REQUEST["ageKind"] == "schoolYear") {
		$year = date('Y');
		if($_REQUEST["schoolYear"] == "Junio") {
			$year -= 15;
		}
		elseif($_REQUEST["schoolYear"] == "Elementary") {
			$year -= 12;
		}
		elseif($_REQUEST["schoolYear"] == "Preschooler") {
			$year -= 6;
		}
		$base = "";
		if(date('n') < 4) {
			$base = ($year-1) . "0401";
		}
		else {
			$base = $year . "0401";
		}
		$condition["schoolYear"] = $base;
	}
	elseif($_REQUEST["ageKind"] == "birthMonth") {
		$condition["birthMonth"] = $_REQUEST["birthMonth"];
	}
	if(array_key_exists("tokudo", $_REQUEST)) {
		$condition["tokudo"] = 1;
	}
	if(array_key_exists("teacher", $_REQUEST)) {
		$condition["teacher"] = 1;
	}
	if(strlen($_REQUEST["diocese"]) != 0) {
		$condition["diocese"] = $_REQUEST["diocese"];
		if(strlen($_REQUEST["team"]) != 0) {
			$condition["team"] = $_REQUEST["team"];
		}
	}
	if(array_key_exists("leader", $_REQUEST)) {
		$condition["leader"] = 1;
	}
	
	$dataAry = $db->find($condition);

	if(strlen($_REQUEST["Phonetic"]) != 0) {
		$tmpAry = $dataAry;
		unset($dataAry);
		$dataAry = Array();
		
		foreach($tmpAry as $tmp) {
			if(mb_ereg($_REQUEST["Phonetic"], $tmp["Phonetic"])) {
				array_push($dataAry, $tmp);
			}
		}
	}

	if(count($dataAry) == 1) {
		header('location: /meibo/view.php?type=view&ID=' . $dataAry[0]['ID']);
		exit();
	}

	if($_REQUEST["search"] == "done") {
		?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>検索結果</title>
</head>
<body>
		<?php
		$url = "search.php?";
		foreach($_REQUEST as $key => $value) {
			if($key == "search") {
				$url .= "search=setting&";
			}
			$url .= "$key=" . base64_encode($value) . "&";
		}
		echo "<A href=\"$url\">検索条件設定に戻る</A>\n";
		echo "<H2>検索結果</H2>\n";
		echo "<HR>\n";
		echo "検索条件：検索範囲=" . ($_REQUEST["range"] == "master" ? "筆頭者のみ" : "筆頭者＋家族") . "　";
	
		if(strlen($_REQUEST["ID"]) != 0) {
			echo "登録番号=" . $_REQUEST["ID"] . "　";
		}
		if(strlen($_REQUEST["Phonetic"]) != 0) {
			echo "名前=" . $_REQUEST["Phonetic"] . "　";
		}
		if($_REQUEST["ageKind"] == "age") {
			$ageMin = 0;
			$ageMax = 0;
			if(strlen($_REQUEST["ageMin"]) != 0) {
				$ageMin = $_REQUEST["ageMin"];
			}
			if(strlen($_REQUEST["ageMax"]) != 0) {
				$ageMax = $_REQUEST["ageMax"];
			}
			if($ageMin > $ageMax) {
				list($ageMin, $ageMax) = array($ageMax, $ageMin);
			}
			echo "年齢=" . $ageMin . "歳～" . $ageMax . "歳　";
		}
		elseif($_REQUEST["ageKind"] == "schoolYear") {
			if($_REQUEST["schoolYear"] == "Junio") {
				echo "学年=中学生以下　";
			}
			elseif($_REQUEST["schoolYear"] == "Elementary") {
				echo "学年=小学生以下　";
			}
			elseif($_REQUEST["schoolYear"] == "Preschooler") {
				echo "学年=未就学児　";
			}
		}
		elseif($_REQUEST["ageKind"] == "birthMonth") {
			echo "誕生月=" . $_REQUEST["birthMonth"] . "月　";
		}
		if(array_key_exists("tokudo", $_REQUEST)) {
			echo "得度授戒者";
		}
		if(array_key_exists("teacher", $_REQUEST)) {
			echo "教師拝受者";
		}
		if(strlen($_REQUEST["diocese"]) != 0) {
			echo "教区=" . $db->getDioceseName($_REQUEST["diocese"]) . "　";
			if(strlen($_REQUEST["team"]) != 0) {
				echo $_REQUEST["team"] . "班　";
			}
		}
		if(array_key_exists("leader", $_REQUEST)) {
				echo "班長";
		}
		?>
<BR>
ヒット数：<?= count($dataAry) ?>件<BR>

<form style="margin:0; margin-top:10px" method="post" action="search.php">
<input type="hidden" name="search" value="export">
		<?php
			foreach($_REQUEST as $key => $value) {
				if($key != "search") {
					echo "<input type=\"hidden\" name=\"" . $key . "\" value=\"" . base64_encode($value) . "\">\n";
				}
			}
		?>
<input type="submit" value="Excel(csvファイル)にダウンロード">(必ず[保存]を選択してください)<BR>
<div style="font: normal normal bold 8pt/normal; color:#FF0000;">※外部ファイルにダウンロードするとセキュリティ保護が行えません。取り扱いには注意してください。</div>
</form>

<table>
		<?php
		echo "<tr>\n";
		echo "  <th width=40>詳細</th>\n";
		if(array_key_exists("dispID", $_REQUEST)) {
			echo "  <th>登録番号</th>\n";
		}
		if(array_key_exists("dispName", $_REQUEST)) {
			echo "  <th>名前</th>\n";
		}
		if(array_key_exists("dispPhonetic", $_REQUEST)) {
			echo "  <th>ふりがな</th>\n";
		}
		if(array_key_exists("dispSex", $_REQUEST)) {
			echo "  <th>性別</th>\n";
		}
		if(array_key_exists("dispBirth", $_REQUEST)) {
			echo "  <th>誕生日(年齢)</th>\n";
		}
		if(array_key_exists("dispSchoolYear", $_REQUEST)) {
			echo "  <th>学年</th>\n";
		}
		if(array_key_exists("dispZIP", $_REQUEST)) {
			echo "  <th>郵便番号</th>\n";
		}
		if(array_key_exists("dispAddress", $_REQUEST)) {
			echo "  <th>住所</th>\n";
		}
		if(array_key_exists("dispTEL", $_REQUEST)) {
			echo "  <th>電話番号</th>\n";
		}
		if(array_key_exists("dispTokudo", $_REQUEST)) {
			echo "  <th>得度</th>\n";
		}
		if(array_key_exists("dispTeacher", $_REQUEST)) {
			echo "  <th>教師</th>\n";
		}
		if(array_key_exists("dispDiocese", $_REQUEST)) {
			echo "  <th>教区</th>\n";
		}
		
		
		echo "</tr>\n";
		foreach($dataAry as $data) {
			echo "<tr>";
			echo "<td align=center><A href=\"view.php?type=view&ID=" . $data['ID'] . "\">■</A></td>";
			if(array_key_exists("dispID", $_REQUEST)) {
				echo "<td>" . $data['ID'] . "</td>";
			}
			if(array_key_exists("dispName", $_REQUEST)) {
				echo "<td>" . $data['Name'] . "</td>";
			}
			if(array_key_exists("dispPhonetic", $_REQUEST)) {
				echo "<td>" . $data['Phonetic'] . "</td>";
			}
			if(array_key_exists("dispSex", $_REQUEST)) {
				echo "<td>" . $data['Sex'] . "</td>";
			}
			if(array_key_exists("dispBirth", $_REQUEST)) {
				$year  = $data['BirthYear'];
				$month = $data['BirthMonth'];
				$day   = $data['BirthDay'];
				echo "<td>$year" . "年(" . getWareki($year, $month, $day) . ")$month" . "月$day" . "日 (". getAge($year, $month, $day) . "歳)</td>";
			}
			if(array_key_exists("dispSchoolYear", $_REQUEST)) {
				echo "<td>" . getSchoolYear($data['BirthYear'], $data['BirthMonth'], $data['BirthDay']) . "</td>";
			}
			if(array_key_exists("dispZIP", $_REQUEST)) {
				echo "<td>" . substr($data["ZIP"], 0, 3) . "-" . substr($data["ZIP"], 3, 4) . "</td>";
			}
			if(array_key_exists("dispAddress", $_REQUEST)) {
				echo "<td>" . $data['Pref'] . $data['Address'] . "</td>";
			}
			if(array_key_exists("dispTEL", $_REQUEST)) {
				echo "<td>" . $data['TEL'] . "</td>";
			}
			if(array_key_exists("dispTokudo", $_REQUEST)) {
				echo "<td>" . getDateStr($data['Tokudo']) . "</td>";
			}
			if(array_key_exists("dispTeacher", $_REQUEST)) {
				echo "<td>" . getDateStr($data['Teacher']) . "</td>";
			}
			if(array_key_exists("dispDiocese", $_REQUEST)) {
				echo "<td>";
				if(strlen($data['Diocese'])) {
					echo $db->getDioceseName($data['Diocese']);
					if(strlen($data['Team'])) {
					echo "　" . $data['Team'] . "班(班長:" . getLeaderName($db, $data['Diocese'], $data['Team']) . ")";
					}
				}
				else {
					echo "-";
				}
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</table>\n";
	}
	else {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename="' . mb_convert_encoding("検索結果[取り扱い中]", "SJIS") . '.csv"');
		header('Cache-Control: max-age=0');
	
		if(array_key_exists("dispID", $_REQUEST)) {
			echo mb_convert_encoding("登録番号", "SJIS") . ",";
		}
		if(array_key_exists("dispName", $_REQUEST)) {
			echo mb_convert_encoding("名前", "SJIS") . ",";
		}
		if(array_key_exists("dispPhonetic", $_REQUEST)) {
			echo mb_convert_encoding("ふりがな", "SJIS") . ",";
		}
		if(array_key_exists("dispSex", $_REQUEST)) {
			echo mb_convert_encoding("性別", "SJIS") . ",";
		}
		if(array_key_exists("dispBirth", $_REQUEST)) {
			echo mb_convert_encoding("誕生日(年齢)", "SJIS") . ",";
		}
		if(array_key_exists("dispSchoolYear", $_REQUEST)) {
			echo mb_convert_encoding("学年", "SJIS") . ",";
		}
		if(array_key_exists("dispZIP", $_REQUEST)) {
			echo mb_convert_encoding("郵便番号", "SJIS") . ",";
		}
		if(array_key_exists("dispAddress", $_REQUEST)) {
			echo mb_convert_encoding("住所", "SJIS") . ",";
		}
		if(array_key_exists("dispTEL", $_REQUEST)) {
			echo mb_convert_encoding("電話番号", "SJIS") . ",";
		}
		if(array_key_exists("dispTokudo", $_REQUEST)) {
			echo mb_convert_encoding("得度", "SJIS") . ",";
		}
		if(array_key_exists("dispTeacher", $_REQUEST)) {
			echo mb_convert_encoding("教師", "SJIS") . ",";
		}
		if(array_key_exists("dispDiocese", $_REQUEST)) {
			echo mb_convert_encoding("教区", "SJIS") . ",";
		}
		echo "\n";
		
		foreach($dataAry as $data) {
			if(array_key_exists("dispID", $_REQUEST)) {
				echo mb_convert_encoding($data['ID'], "SJIS") . ",";
			}
			if(array_key_exists("dispName", $_REQUEST)) {
				echo mb_convert_encoding($data['Name'], "SJIS") . ",";
			}
			if(array_key_exists("dispPhonetic", $_REQUEST)) {
				echo mb_convert_encoding($data['Phonetic'], "SJIS") . ",";
			}
			if(array_key_exists("dispSex", $_REQUEST)) {
				echo mb_convert_encoding($data['Sex'], "SJIS") . ",";
			}
			if(array_key_exists("dispBirth", $_REQUEST)) {
				$year  = $data['BirthYear'];
				$month = $data['BirthMonth'];
				$day   = $data['BirthDay'];
				echo mb_convert_encoding("$year" . "年(" . getWareki($year, $month, $day) . ")$month" . "月$day" . "日 (". getAge($year, $month, $day) . "歳)", "SJIS") . ",";
			}
			if(array_key_exists("dispSchoolYear", $_REQUEST)) {
				echo mb_convert_encoding(getSchoolYear($data['BirthYear'], $data['BirthMonth'], $data['BirthDay']), "SJIS") . ",";
			}
			if(array_key_exists("dispZIP", $_REQUEST)) {
				echo mb_convert_encoding(substr($data["ZIP"], 0, 3) . "-" . substr($data["ZIP"], 3, 4), "SJIS") . ",";
			}
			if(array_key_exists("dispAddress", $_REQUEST)) {
				echo mb_convert_encoding($data['Pref'] . $data['Address'], "SJIS") . ",";
			}
			if(array_key_exists("dispTEL", $_REQUEST)) {
				echo mb_convert_encoding($data['TEL'], "SJIS") . ",";
			}
			if(array_key_exists("dispTokudo", $_REQUEST)) {
				echo mb_convert_encoding(getDateStr($data['Tokudo']), "SJIS") . ",";
			}
			if(array_key_exists("dispTeacher", $_REQUEST)) {
				echo mb_convert_encoding(getDateStr($data['Teacher']), "SJIS") . ",";
			}
			if(array_key_exists("dispDiocese", $_REQUEST)) {
				if(strlen($data['Diocese'])) {
					echo mb_convert_encoding($db->getDioceseName($data['Diocese']), "SJIS");
					if(strlen($data['Team'])) {
						echo mb_convert_encoding("　" . $data['Team'] . "班(班長:" . getLeaderName($db, $data['Diocese'], $data['Team']) . ")", "SJIS");
					}
				}
				else {
					echo "-";
				}
			}
			echo "\n";
		}
	
		exit;
	}
}
else {
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>検索</title>

<script type="text/javascript">
<!--

function radioChecked(label)
{
	var radioList = document.getElementsByName("ageKind");
	for(var i = 0 ; i<radioList.length ; i++) {
		if(radioList[i].value == label) {
			radioList[i].checked = true;
		}
	}
}

// -->
</script>

</head>
<body>
<?php
	$keys = array_keys($_REQUEST);
	foreach($keys as $key) {
		$_REQUEST[$key] = base64_decode($_REQUEST[$key]);
	}
?>
<H2>検索条件</H2>
<HR>
<form method="post" action="search.php">
<input type="hidden" name="search" value="done">
<table>
	<tr><th width=150>検索範囲</th><td><input type="radio" name="range" value="master" <?= (!array_key_exists("range", $_REQUEST) || ($_REQUEST["range"] == "master")) ? "checked" : "" ?>>筆頭者のみ　　
	                                   <input type="radio" name="range" value="all"    <?= ( array_key_exists("range", $_REQUEST) && ($_REQUEST["range"] == "all")) ? "checked" : "" ?>>筆頭者＋家族</td></tr>
	<tr><th width=150>登録番号</th><td><input type="text" name="ID" value="<?= array_key_exists("ID", $_REQUEST) ? $_REQUEST["ID"] : "" ?>" style="ime-mode:disabled;"></td></tr>
	<tr><th width=150>名前</th><td><input type="text" name="Phonetic" value="<?= array_key_exists("Phonetic", $_REQUEST) ? $_REQUEST["Phonetic"] : "" ?>"> (全角かな)</td></tr>
	<tr><th width=150>年齢</th><td><input type="radio" name="ageKind" value="no"         <?= (!array_key_exists("ageKind", $_REQUEST) || ($_REQUEST["ageKind"] == "no"))         ? "checked" : "" ?>>無条件<BR>
	                               <input type="radio" name="ageKind" value="age"        <?= ( array_key_exists("ageKind", $_REQUEST) && ($_REQUEST["ageKind"] == "age"))        ? "checked" : "" ?>>年齢
	                                   <input type="text" name="ageMin" size=5 value="<?= array_key_exists("ageMin", $_REQUEST) ? $_REQUEST["ageMin"] : "" ?>" onclick=radioChecked("age")>～
	                                   <input type="text" name="ageMax" size=5 value="<?= array_key_exists("ageMax", $_REQUEST) ? $_REQUEST["ageMax"] : "" ?>" onclick=radioChecked("age")>歳<BR>
	                               <input type="radio" name="ageKind" value="schoolYear" <?= ( array_key_exists("ageKind", $_REQUEST) && ($_REQUEST["ageKind"] == "schoolYear")) ? "checked" : "" ?>>学年
	                                   <select name="schoolYear" onclick=radioChecked("schoolYear")><option value="Junio"       <?= (!array_key_exists("schoolYear", $_REQUEST) || ($_REQUEST["schoolYear"] == "Junio"))       ? "selected" : "" ?>>中学生以下</option>
	                                                                                                <option value="Elementary"  <?= ( array_key_exists("schoolYear", $_REQUEST) && ($_REQUEST["schoolYear"] == "Elementary"))  ? "selected" : "" ?>>小学校以下</option>
	                                                                                                <option value="Preschooler" <?= ( array_key_exists("schoolYear", $_REQUEST) && ($_REQUEST["schoolYear"] == "Preschooler")) ? "selected" : "" ?>>未就学児</option></select><BR>
	                               <input type="radio" name="ageKind" value="birthMonth" <?= ( array_key_exists("ageKind", $_REQUEST) && ($_REQUEST["ageKind"] == "birthMonth")) ? "checked" : "" ?>>誕生月
	                                   <?= strSelectMonth("birthMonth") ?><BR>
	                               </td></tr>
	<tr><th width=150>拝受</th><td>	<input type="checkbox" name="tokudo" value="1"  <?= array_key_exists("tokudo",  $_REQUEST) ? "checked" : "" ?>>得度<BR>
									<input type="checkbox" name="teacher" value="1" <?= array_key_exists("teacher", $_REQUEST) ? "checked" : "" ?>>教師<BR>
	<tr><th width=150>教区</th><td><select name="diocese">
									<?php
									echo "<option value=\"\">--</option>\n";
									$dioceseAry = $db->getDiocese();
									foreach($dioceseAry as $diocese) {
										echo '<option value="' . $diocese['ID'] . '"';
										if(array_key_exists("diocese", $_REQUEST) && ($_REQUEST["diocese"] == $diocese['ID'])) {
											echo " selected";
										}
										echo '>' . $diocese['Name'] . "</option>\n";
									}
									?>
									</select>　
									<input type="text" name="team" size=5 value="<?= array_key_exists("team", $_REQUEST) ? $_REQUEST["team"] : "" ?>"  style="ime-mode:disabled;">班<BR>
									<input type="checkbox" name="leader" value="1" <?= array_key_exists("leader", $_REQUEST) ? "checked" : "" ?>>班長</td></tr>
	<tr><th width=150>表示項目</th><td><input type="checkbox" name="dispID"         <?= array_key_exists("dispID",         $_REQUEST) ? "checked" : "" ?> >登録番号　　
	                                   <input type="checkbox" name="dispName"       <?= (!array_key_exists("search", $_REQUEST) || array_key_exists("dispName", $_REQUEST)) ? "checked" : "" ?> >名前　　
	                                   <input type="checkbox" name="dispPhonetic"   <?= (!array_key_exists("search", $_REQUEST) || array_key_exists("dispPhonetic", $_REQUEST)) ? "checked" : "" ?> >ふりがな　　
	                                   <input type="checkbox" name="dispSex"        <?= array_key_exists("dispSex",        $_REQUEST) ? "checked" : "" ?> >性別　　
	                                   <input type="checkbox" name="dispBirth"      <?= array_key_exists("dispBirth",      $_REQUEST) ? "checked" : "" ?> >誕生日(年齢)　　
	                                   <input type="checkbox" name="dispSchoolYear" <?= array_key_exists("dispSchoolYear", $_REQUEST) ? "checked" : "" ?> >学年<BR>
	                                   <input type="checkbox" name="dispZIP"        <?= array_key_exists("dispZIP",        $_REQUEST) ? "checked" : "" ?> >郵便番号　　
	                                   <input type="checkbox" name="dispAddress"    <?= array_key_exists("dispAddress",    $_REQUEST) ? "checked" : "" ?> >住所　　
	                                   <input type="checkbox" name="dispTEL"        <?= array_key_exists("dispTEL",        $_REQUEST) ? "checked" : "" ?> >電話番号　　
	                                   <input type="checkbox" name="dispTokudo"     <?= array_key_exists("dispTokudo",     $_REQUEST) ? "checked" : "" ?> >得度　　
	                                   <input type="checkbox" name="dispTeacher"    <?= array_key_exists("dispTeacher",    $_REQUEST) ? "checked" : "" ?> >教師　　
	                                   <input type="checkbox" name="dispDiocese"    <?= array_key_exists("dispDiocese",    $_REQUEST) ? "checked" : "" ?> >教区　　
	                                   </td></tr>
</table>
<input type="submit" value="検索">
</form>
<?php
}
?>
</body>
</html>


<?php
function strSelectMonth($keyword)
{
	$str  = "<select name=\"$keyword\" onclick=radioChecked(\"birthMonth\")>";
	for($i = 1 ; $i <= 12 ; $i++) {
		$str .= "<option value=\"$i\"";
		if(array_key_exists($keyword, $_REQUEST) && ($_REQUEST[$keyword] == $i)) {
			$str .= " selected";
		}
		$str .= ">$i 月</option>";
	}
	$str .= "</select>";
	
	return $str;
}

?>
