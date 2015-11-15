<?php
require_once "common.php";

mb_internal_encoding("UTF-8");

function getRangeStr()
{
	switch($_REQUEST["initial"]) {
	case "a":
		return "あ～お";
		break;
	case "k":
		return "か～こ";
		break;
	case "s":
		return "さ～そ";
		break;
	case "t":
		return "た～と";
		break;
	case "n":
		return "な～の";
		break;
	case "h":
		return "は～ほ";
		break;
	case "m":
		return "ま～も";
		break;
	case "y":
		return "や～よ";
		break;
	case "r":
		return "ら～ろ";
		break;
	case "w":
		return "わ～ん";
		break;
	default:
		return "その他";
		break;
	}
}


$dioceseAry = $db->getDiocese();

$dataAry = $db->get_list($_REQUEST["initial"]);

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>参照・編集</title>
<style type="text/css">
rt{
	font-size: 12px;
}
</style>
</head>
<body>
<div>
<div class="left"><H2>参照・編集[<?= getRangeStr() ?>]</H2></div>
<div  style="padding:6px;">(<?= count($dataAry) ?>件)</div>
<HR>

<table>
<tr>
<th>登録番号</th><th>名前</th><th>ふりがな</th><th>教区</th><th>班</th>
</tr>
<?php
foreach($dataAry as $data) {
	echo "<tr>";
	echo "<td align=center><A href=\"view.php?type=view&ID=" . $data['ID'] . "\">" . $data['ID'] . "</A></td>";
	echo "<td><A Name=\"" . $data['ID'] . "\">" . $data['Name'] . "</A></td>";
	echo "<td>" . $data['Phonetic'] . "</td>";
	if(strlen($data['Diocese'])) {
		echo "<td>" . getDioceseName($data['Diocese']) . "</td>";
	}
	else {
		echo "<td>-</td>";
	}
	if(strlen($data['Team'])) {
		echo "<td>" . $data['Team'] . "班(班長:" . getLeaderName($db, $data['Diocese'], $data['Team']) . ")</td>";
	}
	else {
		echo "<td>-</td>";
	}
	echo "</tr>\n";
}
?>
</table>

</body>
</html>

<?php

function getDioceseName($id)
{
	global $db, $dioceseAry;
	
	foreach($dioceseAry as $diocese) {
		if($diocese['ID'] == $id) {
			return $diocese['Name'];
		}
	}
	return $id;
}

?>
