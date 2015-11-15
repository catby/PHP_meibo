<?php
require_once "common.php";

if(array_key_exists("type", $_REQUEST)) {
	if($_REQUEST["type"] == "regist") {
		$dioceseAry = $db->getNengoData();
		$isExist = false;
		foreach($dioceseAry as $diocese) {
			if($diocese["Nengo"] == $_REQUEST["Nengo"]) {
				$isExist = true;
				break;
			}
		}
		if(!$isExist) {
			$db->registNengo($_REQUEST["Nengo"], $_REQUEST["Year"], $_REQUEST["Month"], $_REQUEST["Day"]);
		}
	}
	else if($_REQUEST["type"] == "delete") {
		$db->delNengoData($_REQUEST["Nengo"]);
	}
}
$nengoAry = $db->getNengoData();

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<script type="text/javascript">
<!--
var existNengo = [];
<?php
	foreach($nengoAry as $nengo) {
		echo "existNengo.push(\"" . $nengo["Nengo"] . "\");\n";
	}
?>

function checkNengo()
{
	if(document.regist.elements["Nengo"].value == "") {
		alert("年号を入力してください");
		return false;
	}
	if(document.regist.elements["Year"].value == "") {
		alert("開始年を入力してください");
		return false;
	}
	if(document.regist.elements["Month"].value == "") {
		alert("開始月を入力してください");
		return false;
	}
	if(document.regist.elements["Day"].value == "") {
		alert("開始日を入力してください");
		return false;
	}

	var nengo = document.regist.elements["Nengo"].value;
	for(var i = 0 ; i < existNengo.length ; i++) {
		if(existNengo[i] == nengo) {
			alert("年号[" + nengo + "]は既に登録されています");
			return false;
		}
	}
	
	return true;
}

// -->
</script>

</head>
<body>
<H2>和暦年号 設定</H2>
<HR>
<div>
<div class="left" style="margin-right=70px;">
<B>年号 削除</B><BR>
<form name="Main" method="post" action="nengo.php">
<input type="hidden" name="type" value="delete">
<select name="Nengo" size=<?= count($nengoAry); ?> style="width:200px;">
<?php
foreach($nengoAry as $nengo) {
	echo "<option value=\"" . $nengo["Nengo"] . "\">" . $nengo["Nengo"] . "　(" . $nengo["Year"] . "年" . $nengo["Month"] . "月" . $nengo["Day"] . "日～)</option>\n";
}
?>
</select>
<BR>
<input type="submit" value="削除">
</form>
</div>
<div>
<B>年号 追加</B><BR>
<form name="regist" method="post" action="nengo.php" onsubmit="return checkNengo();">
<input type="hidden" name="type" value="regist">
<table style="width:auto;">
<tr><th>年号</th><th>開始年月日(西暦)</th></tr>
<tr>
<td>
	<input type="text" name="Nengo" size="5">
</td>
<td>
<input type="text" name="Year" size="5" maxlength="4">年
<input type="text" name="Month" size="3" maxlength="2">月
<input type="text" name="Day" size="3" maxlength="2">日～
</td>
</tr>
</table>
<input type="submit" value="登録">
</form>
</div>

</div>

</body>
</html>
