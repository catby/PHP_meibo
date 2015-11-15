<?php
require_once "common.php";

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

$prefAry = array(
	"北海道",
	"青森県",
	"岩手県",
	"宮城県",
	"秋田県",
	"山形県",
	"福島県",
	"茨城県",
	"栃木県",
	"群馬県",
	"埼玉県",
	"千葉県",
	"東京都",
	"神奈川県",
	"新潟県",
	"富山県",
	"石川県",
	"福井県",
	"山梨県",
	"長野県",
	"岐阜県",
	"静岡県",
	"愛知県",
	"三重県",
	"滋賀県",
	"京都府",
	"大阪府",
	"兵庫県",
	"奈良県",
	"和歌山県",
	"鳥取県",
	"島根県",
	"岡山県",
	"広島県",
	"山口県",
	"徳島県",
	"香川県",
	"愛媛県",
	"高知県",
	"福岡県",
	"佐賀県",
	"長崎県",
	"熊本県",
	"大分県",
	"宮崎県",
	"鹿児島県",
	"沖縄県"
);
$masterFamily = file_get_contents("template_inputFamily.html");
$masterFamily = preg_replace('/^[\s\S]+<body>/', "", $masterFamily);


if(array_key_exists("type", $_REQUEST) and ($_REQUEST["type"] == "delete")) {
	$db->del($_REQUEST["ID"]);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="refresh" CONTENT="0; URL=main.php">
<LINK rel="stylesheet" type="text/css" href="base.css">
</head>
<body>
</body>
</html>
<?php
	exit();
}

$getData = array();
$isEdit = false;
if(array_key_exists("type", $_REQUEST)) {
	if(strcasecmp($_REQUEST["type"], "edit") == 0) {
		$isEdit = true;
		$getData = $db->get($_REQUEST["ID"]);
	}
	if(strcasecmp($_REQUEST["type"], "reedit") == 0) {
		$isEdit = true;
		$getData = convertToArray($_REQUEST);
	}
}
else if(array_key_exists("ID", $_REQUEST)) {
	$getData = convertToArray($_REQUEST);
}
else {
	$_REQUEST["ID"] = "";
}
createBlankEntry($getData);

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>信徒名簿 登録・編集</title>

<script src="script/prototype.js" type="text/javascript"></script>
<script src="script/autoKana.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
var isSubmit = false;

function defaultSetting()
{
	for(idx = 1 ; idx <= 10 ; idx++) {
		var name = "name_" + idx;
		if(document.Main.elements[name].value == "") {
			break;
		}
	}
	idx++;
	for(; idx <= 10 ; idx++) {
		var id = "family" + idx;
		document.getElementById(id).style.display = "none";
	}
	
	checkLeader();
	
	document.Main.elements["ID"].focus();
}

function resetAgree()
{
	return confirm("内容をリセットしますか?");
}

function submitCheck()
{
	if(document.Main.elements["ID"].value == "") {
		alert("登録番号が入力されていません。");
		return false;
	}
	
	if(document.Main.elements["Name_0"].value == "") {
		alert("氏名が入力されていません。");
		return false;
	}
	if(document.Main.elements["Phonetic_0"].value == "") {
		alert("ふりがなが入力されていません。");
		return false;
	}
	if(document.Main.elements["BirthMonth_0"].value == "") {
		alert("生年月日が入力されていません。");
		return false;
	}
	if(document.Main.elements["BirthDay_0"].value == "") {
		alert("生年月日が入力されていません。");
		return false;
	}
	var radioList = document.getElementsByName("Sex_0");
	var isCheckd = 0;
	for(var i=0; i<radioList.length; i++) {
		if(radioList[i].checked) {
			isCheckd = 1;
		}
	}
	if(isCheckd == 0) {
		alert("性別が入力されていません。");
		return false;
	}
//	if(document.Main.elements["Diocese_0"].value == "") {
//		alert("教区が入力されていません。");
//		return false;
//	}
	if((document.Main.elements["EntryMonth_0"].value != "") || (document.Main.elements["EntryDay_0"].value != "")) {
		if(document.Main.elements["EntryMonth_0"].value == "") {
			alert("'入信'月が入力されていません。");
			return false;
		}
		if(document.Main.elements["EntryDay_0"].value == "") {
			alert("'入信'日が入力されていません。");
			return false;
		}
	}
	if((document.Main.elements["TokudoMonth_0"].value != "") || (document.Main.elements["TokudoDay_0"].value != "")) {
		if(document.Main.elements["TokudoMonth_0"].value == "") {
			alert("'得度'月が入力されていません。");
			return false;
		}
		if(document.Main.elements["TokudoDay_0"].value == "") {
			alert("'得度'日が入力されていません。");
			return false;
		}
	}
	if((document.Main.elements["TeacherMonth_0"].value != "") || (document.Main.elements["TeacherDay_0"].value != "")) {
		if(document.Main.elements["TeacherMonth_0"].value == "") {
			alert("'教師'月が入力されていません。");
			return false;
		}
		if(document.Main.elements["TeacherDay_0"].value == "") {
			alert("'教師'日が入力されていません。");
			return false;
		}
	}

	for(var i = 1 ; i <= 10  ; i++) {
		if(document.Main.elements["Name_" + i].value != "") {
			if(document.Main.elements["Phonetic_" + i].value == "") {
				alert("家族欄 No." + i + "のふりがなが入力されていません。");
				return false;
			}
//			if(document.Main.elements["BirthMonth_" + i].value == "") {
//				alert("家族欄 No." + i + "の生年月日が入力されていません。");
//				return false;
//			}
//			if(document.Main.elements["BirthDay_" + i].value == "") {
//				alert("家族欄 No." + i + "の生年月日が入力されていません。");
//				return false;
//			}
			var radioList = document.getElementsByName("Sex_" + i);
			var isCheckd = 0;
			for(var j=0; j<radioList.length; j++) {
				if(radioList[j].checked) {
					isCheckd = 1;
				}
			}
			if(isCheckd == 0) {
				alert("家族欄 No." + i + "の性別が入力されていません。");
				return false;
			}
			if((document.Main.elements["TokudoMonth_" + i].value != "") || (document.Main.elements["TokudoDay_" + i].value != "")) {
				if(document.Main.elements["TokudoMonth_" + i].value == "") {
					alert("家族欄 No." + i + "の'得度'月が入力されていません。");
					return false;
				}
				if(document.Main.elements["TokudoDay_" + i].value == "") {
					alert("家族欄 No." + i + "の'得度'日が入力されていません。");
					return false;
				}
			}
			if((document.Main.elements["TeacherMonth_" + i].value != "") || (document.Main.elements["TeacherDay_" + i].value != "")) {
				if(document.Main.elements["TeacherMonth_" + i].value == "") {
					alert("家族欄 No." + i + "の'教師'月が入力されていません。");
					return false;
				}
				if(document.Main.elements["TeacherDay_" + i].value == "") {
					alert("家族欄 No." + i + "の'教師'日が入力されていません。");
					return false;
				}
			}
		}
	}

<?php
if(!$isEdit) {
?>
	var ID = document.Main.elements["ID"].value;
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("GET", "http://catby.webcrow.jp/meibo/inputCheck.php?Type=ID&ID="+ID, false);
	xmlHttp.send(null);

	if(xmlHttp.responseText == 0) {
		alert("登録番号[" + ID + "]は既に登録されています。");
		return false;
	}
<?php
}
?>
	isSubmit = true;
	return true;
}

function loadAddress(in1, in2, out1, out2)
{
	var zipCode = document.Main.elements[in1].value + document.Main.elements[in2].value;

	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("GET", "http://catby.webcrow.jp/meibo/ZIPCode.php?ZipCode="+zipCode, false);
	xmlHttp.send(null);
	
	if(xmlHttp.responseText.length != 0) {
		var ary = xmlHttp.responseText.split("<>");
	
		if(out1 == out2) {
			document.Main.elements[out1].focus();
			document.Main.elements[out1].value = ary[0] + ary[1];
		}
		else {
			document.Main.elements[out2].focus();
			document.Main.elements[out1].value = ary[0];
			document.Main.elements[out2].value = ary[1];
		}
	}
}

function checkLeader()
{
	var Diocese = document.Main.elements["Diocese_0"].value;
	var Team    = document.Main.elements["Team_0"].value;
	
	document.getElementById("leaderSet").style.display = "";
	document.getElementById("leaderView").style.display = "none";
	if(Diocese != "" && Team != "") {
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("GET", "http://catby.webcrow.jp/meibo/inputCheck.php?Type=TeamLeader&Diocese="+Diocese+"&Team="+Team, false);
		xmlHttp.send(null);
		if(xmlHttp.responseText.length != 0) {
			var ary = xmlHttp.responseText.split(":");
			if(ary[0] == document.Main.elements["ID"].value) {
				document.Main.elements["isLeader_0"].checked = true;
			}
			else {
				document.getElementById("leaderView").innerHTML = "　　(班長：" + ary[1] + ")";
				document.Main.elements["isLeader_0"].checked = false;
	
				document.getElementById("leaderSet").style.display = "none";
				document.getElementById("leaderView").style.display = "";
			}
		}
	}
}

function checkFamilyEntry(idx)
{
	var name = "name_" + idx;
	if(document.Main.elements[name].value != "") {
		var id = "family" + (idx + 1);
		document.getElementById(id).style.display = "";
	}
}

function initValue(target)
{
	var childs = target.childNodes;
	if(childs.length != 0) {
		for(var i = 0 ; i < childs.length ; i++) {
			if(childs(i).tagName == "NaN") {
				continue;
			}
			if(childs(i).tagName == "INPUT" && childs(i).getAttribute("type") == "text") {
				if(childs(i).value != childs(i).defaultValue) {
					return false;
				}
			}
			if(childs(i).tagName == "TEXTAREA") {
				if(childs(i).value != childs(i).defaultValue) {
					return false;
				}
			}
			if(childs(i).tagName == "OPTION") {
				if(childs(i).selected != childs(i).defaultSelected) {
					return false;
				}
			}
			if(childs(i).tagName == "INPUT" && childs(i).getAttribute("type") == "checkbox") {
				if(childs(i).checked != childs(i).defaultChecked) {
					if(childs(i).id.indexOf("isLivingTogether_") == -1) {
						return false;
					}
				}
			}
			var nextChilds = childs(i).childNodes;
			if(nextChilds.length != 0) {
				if(initValue(childs(i)) == false) {
//					alert(childs(i).tagName + " - " + childs(i).name);
					return false;
				}
			}
		}
	}
	return true;
}

window.onbeforeunload = function(event){
	if(!isSubmit && !initValue(document.Main)) {
		event = event || window.event; 
		event.returnValue = '編集中のデータは破棄されてしまいます';
	}
}
// -->
</script>

</head>
<body onLoad="defaultSetting();">
<?php
if($isEdit) {
	echo "<div class=\"left\">\n";
	echo "<img src=\"./img/up.png\" width=30px alt=\"編集のキャンセル(名簿表示に戻る)\" border=0 onClick=\"history.go(-1);\">\n";
	echo "</div>\n";
	echo "<H1>名簿編集</H1>";
}
else {
	echo "<H1>新規登録</H1>";
}
?>

<form method="post" name="Main" action="view.php" onreset="return resetAgree();" onsubmit="return submitCheck();">
<input type="submit" value="確認">　　
<input type="reset" value="クリア"><BR>
<?php
if($isEdit) {
?>
<input type="hidden" name="type" value="edit">
<input type="hidden" name="ID" value="<?= $_REQUEST['ID'] ?>">
<?php
}
else {
?>
<input type="hidden" name="type" value="new">
<?php
}

for($num = 0 ; $num <= 10 ; $num++) {
	if($num == 0) {
		echo "<BR>\n";
		echo "<H2>筆頭者欄</H2>\n";
		echo "<HR>\n";
	}
	if($num == 1) {
		echo "<BR>\n";
		echo "<H2>家族欄</H2>\n";
		echo "<HR>\n";
		echo "<table>\n";
	}
	
	if($num == 0) {
		$template = file_get_contents("template_inputMaster.html");
		$template = preg_replace('/^[\s\S]+<body>/', "", $template);

		$template = str_replace('#ID#',       $_REQUEST['ID'],        $template);
		$template = str_replace('#IDopt#',    $isEdit ? 'disabled' : 'name="ID"' , $template);
		$template = str_replace('#Team#',     $getData[$num]["Team"], $template);
		$template = str_replace('#isLeader#', ($getData[$num]["isLeader"] == "1") ? 'checked' : '', $template);
		preg_match('#EntryDay%(\d+)#', $template, $retArr);
		$template = preg_replace('/#EntryDay%(\d+)#/', getDateForm($getData[$num]["EntryDay"], "Entry", 0, $retArr[1]), $template);

		$dioceseStr = "";
		$selectDiocese = $getData[$num]["Diocese"];
		$dioceseAry = $db->getDiocese();
		if($selectDiocese == 0) {
			$dioceseStr .= '<option value="" selected>--</option>' . "\n";
		}
		else {
			$dioceseStr .= '<option value="">--</option>' . "\n";
		}
		foreach($dioceseAry as $diocese) {
			$dioceseStr .= '<option value="' . $diocese['ID'] . ($selectDiocese == $diocese['ID'] ? '" selected>' : '">') . $diocese['Name'] . "</option>\n";
		}
		$template = str_replace('#Diocese#', $dioceseStr, $template);
	}
	else {
		echo '<tr id="family' . $num . '"><td align="center">' . $num . '</td><td style="padding: 0px;">' . "\n";
		$template = $masterFamily;
		$template = preg_replace('/#idx#/', $num, $template);
		while(preg_match('#Tab%(\d+)#', $template, $retArr) == 1) {
			$template = preg_replace('/#Tab%(\d+)#/', $num * 100 + $retArr[1], $template, 1);
		}
		$template = str_replace('#Relationship#', $getData[$num]["Relationship"], $template);
	}
	$template = str_replace('#Name#',         $getData[$num]["Name"],         $template);
	$template = str_replace('#Phonetic#',     $getData[$num]["Phonetic"],     $template);
	$template = str_replace('#TEL#',          $getData[$num]["TEL"],          $template);
	$template = str_replace('#ZIP_3#',        substr($getData[$num]["ZIP"], 0, 3),    $template);
	$template = str_replace('#ZIP_4#',        substr($getData[$num]["ZIP"], 3, 4),    $template);
	$template = str_replace('#Pref#',         getPrefSelect($getData[$num]["Pref"]),  $template);
	$template = str_replace('#Address#',      $getData[$num]["Address"],  $template);
	$template = str_replace('#Sex_M#',        ($getData[$num]["Sex"] == "男") ? 'checked' : '', $template);
	$template = str_replace('#Sex_W#',        ($getData[$num]["Sex"] == "女") ? 'checked' : '', $template);
	preg_match('#BirthDay%(\d+)#', $template, $retArr);
	$template = preg_replace('/#BirthDay%(\d+)#/', getDateForm($getData[$num]["JointBirthDay"], "Birth", $num, $num * 100 + $retArr[1]), $template);
	preg_match('#TokudoDate%(\d+)#', $template, $retArr);
	$template = preg_replace('/#TokudoDate%(\d+)#/', getDateForm($getData[$num]["Tokudo"], "Tokudo", $num, $num * 100 + $retArr[1]), $template);
	preg_match('#TeacherDate%(\d+)#', $template, $retArr);
	$template = preg_replace('/#TeacherDate%(\d+)#/', getDateForm($getData[$num]["Teacher"], "Teacher", $num, $num * 100 + $retArr[1]), $template);

	echo $template;

	if($num != 0) {
		echo "</td></tr>\n";
	}
	echo "\n";
}
?>
</table>

<BR>
<H2>備考欄</H2>
<HR>
<textarea name="Memo_0" cols=100 rows=5><?= $getData[0]["Memo"] ?></textarea>
</form>
</body>
</html>


<?php
function getDefaultValue($key)
{
	if(array_key_exists($key, $_REQUEST)) {
		return $_REQUEST[$key];
	}
	else {
		return "";
	}
}

function getDateForm($input, $formName, $idx, $tabIndex)
{
	$retStr = "";
	$nengoAry   = getNengoArray();
	$yearValue  = intval(substr($input, 0, 4));
	$monthValue = intval(substr($input, 4, 2));
	$dayValue   = intval(substr($input, 6, 2));
	$nengoValue = getNengo($yearValue, $monthValue, $dayValue);

	$retStr .= '<SELECT name="' . $formName . 'Nengo_' . $idx . '" tabindex=' . $tabIndex . ' style="ime-mode:disabled;">';
	if($nengoValue == "") {
		$nengoValue = $nengoAry[count($nengoAry) - 2];
	}
	foreach($nengoAry as $nengo) {
		if(mb_ereg($nengo, $nengoValue)) {
			$retStr .= "<option value=\"$nengo\" selected>$nengo</option>";
		}
		else {
			$retStr .= "<option value=\"$nengo\">$nengo</option>";
		}
	}
	$retStr .= "</SELECT>\n";

	$retStr .= '<input type="text" name="' . $formName . 'Year_' . $idx . '" tabindex=' . ($tabIndex+1) . ' size="3" maxlength="2" value="' . (($yearValue != 0) ? $yearValue : "") . '" style="ime-mode:disabled;"/>年' . "\n";

	$retStr .= '<SELECT name="' . $formName . 'Month_' . $idx . '" tabindex=' . ($tabIndex+2) . ' style="ime-mode:disabled;">';
	$retStr .= '<option value=""' . (($monthValue == 0) ? ' selected' : '') . '>--</option>';
	for($month = 1 ; $month <= 12 ; $month++) {
		if($month == $monthValue) {
			$retStr .= "<option value=\"$month\" selected>$month</option>";
		}
		else {
			$retStr .= "<option value=\"$month\">$month</option>";
		}
	}
	$retStr .= "</SELECT>月\n";
	 
	$retStr .= '<SELECT name="' . $formName . 'Day_' . $idx . '"  tabindex=' . ($tabIndex+3) . ' style="ime-mode:disabled;">';
	$retStr .= '<option value=""' . (($dayValue == 0) ? ' selected' : '') . '>--</option>';
	for($day = 1 ; $day <= 31 ; $day++) {
		if($day == $dayValue) {
			$retStr .= "<option value=\"$day\" selected>$day</option>";
		}
		else {
			$retStr .= "<option value=\"$day\">$day</option>";
		}
	}
	$retStr .= "</SELECT>日\n";
	
	return $retStr;
}

function getPrefSelect($selected)
{
	global $prefAry;
	
	$retStr = "";
	if($selected == "") {
		$retStr .= "<option value=\"\" selected>都道府県</option>";
	}
	else {
		$retStr .= "<option value=\"\">都道府県</option>";
	}
	foreach($prefAry as $pref) {
		if($selected == $pref) {
			$retStr .= "<option value=\"$pref\" selected>$pref</option>";
		}
		else {
			$retStr .= "<option value=\"$pref\">$pref</option>";
		}
	}
	
	return $retStr . "\n";
}

?>
