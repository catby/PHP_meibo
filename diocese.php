<?php
require_once "common.php";

if(array_key_exists("type", $_REQUEST)) {
	if($_REQUEST["type"] == "regist") {
		$dioceseAry = $db->getDiocese();
		$isExist = false;
		foreach($dioceseAry as $diocese) {
			if($diocese["Name"] == $_REQUEST["name"]) {
				$isExist = true;
				break;
			}
		}
		if(!$isExist) {
			$db->addDiocese($_REQUEST["name"]);
		}
	}
	else if($_REQUEST["type"] == "edit") {
		$db->renameDiocese($_REQUEST["diocese"], $_REQUEST["newName"]);
	}
	else if($_REQUEST["type"] == "delete") {
		$db->delDiocese($_REQUEST["diocese"]);
	}
	if($_REQUEST["type"] == "move") {
		$ID   = $_REQUEST["ID"];
		$move = $_REQUEST["move"];
		$db->moveDioceseID($ID, $move);
		
		$retStr = "";
		$dioceseAry = $db->getDiocese();
		foreach($dioceseAry as $diocese) {
			$retStr .= $diocese["ID"] . ":" . $diocese["Name"] . ";";
		}
		echo substr($retStr, 0, -1);
		return;
	}
}
$dioceseAry = $db->getDiocese();

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<script type="text/javascript">
<!--
function editName()
{
	var idx = document.Main.elements["diocese"].selectedIndex;
	var name = document.Main.elements["diocese"].options[idx].text;
	
	var newName = prompt("新しい教区名を入力してください", name);
	if(newName != null && newName != "") {
		document.Main.elements["newName"].value = newName;
		document.Main.submit();
	}
}

function checkDelete()
{
	var idx  = document.Main.elements["diocese"].selectedIndex;
	var ID   = document.Main.elements["diocese"].value;
	var name = document.Main.elements["diocese"].options[idx].text;
	
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("GET", "http://catby.webcrow.jp/meibo/inputCheck.php?Type=DioceseNum&diocese="+ID, false);
	xmlHttp.send(null);

	if(xmlHttp.responseText == 0) {
		var ret = confirm("教区 [" + name + "] を削除してよろしいですか？");
		if(ret == true) {
			document.Main.elements["type"].value = "delete";
			document.Main.submit();
		}
	}
	else {
		alert("教区 [" + name + "] は" + xmlHttp.responseText + "箇所で使用されているので削除できません");
	}
}
function moveDiocese(move)
{
	var select = document.Main.elements["diocese"];
	var idx = select.selectedIndex;
	var ID = parseInt(select.value);
	if(idx == -1) {
		return;
	}
	var newIdx = idx + move;
	if((newIdx < 0) || (newIdx >= select.length)) {
		return;
	}

	document.Main.elements["upButton"].disabled = true;
	document.Main.elements["downButton"].disabled = true;
	
	var moveStr = (move == 1) ? "up" : "down";
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("GET", "http://catby.webcrow.jp/meibo/diocese.php?type=move&ID="+ID+"&move="+moveStr, false);
	xmlHttp.send(null);

	select.length = 0;
	var ary = xmlHttp.responseText.split(";");
	for(var i = 0 ; i < ary.length ; i++) {
		var pair = ary[i].split(":");
		select.options[i] = new Option(pair[1], pair[0]);
	}
	select.selectedIndex = newIdx;
	
	document.Main.elements["upButton"].disabled = false;
	document.Main.elements["downButton"].disabled = false;
}

// -->
</script>

</head>
<body>
<H2>教区設定</H2>
<HR>
<div>
<div class="left" style="margin-right=70px;">
<B>教区 修正/削除・並び替え</B><BR>
<form name="Main" method="post" action="diocese.php">
<input type="hidden" name="type" value="edit">
<input type="hidden" name="newName" value="">
<table class="normal">
<tr><td rowspan=2>
<select name="diocese" size=<?= count($dioceseAry); ?> style="width:100px;">
<?php
foreach($dioceseAry as $diocese) {
	echo "<option value=\"" . $diocese["ID"] . "\">" . $diocese["Name"] . "</option>\n";
}
?>
</select>
</td>
<td valign="bottom"><input type="button" name ="upButton" value="↑" onclick="moveDiocese(-1)"></td></tr>
<tr>
<td valign="top"><input type="button" name ="downButton" value="↓" onclick="moveDiocese(1)"></td></tr>
</table>
<BR>
<input type="button" onclick="editName();" value="修正">
<input type="button" onclick="checkDelete();" value="削除">
</form>
</div>
<div>
<B>教区 追加</B><BR>
<form name="regist" method="post" action="diocese.php">
<input type="hidden" name="type" value="regist">
<input type="text" name="name"><BR>
<input type="submit" value="登録">
</form>
</div>

</div>

</body>
</html>
