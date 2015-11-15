<?php
require_once "common.php";

$historyAry = $db->getHistory();

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>変更履歴</title>
<style type="text/css">
rt{
	font-size: 12px;
}
</style>
</head>
<body>
<H2>変更履歴</H2>
<HR>

<table>
<tr>
<th>変更日時</th><th>ID</th><th>内容</th>
</tr>
<?php
foreach($historyAry as $history) {
	echo "<tr>";
	echo "<td nowrap>" . $history['Date'] . "</td>";
	if($db->isExist($history['ID'])) {
		echo "<td nowrap><A href=\"view.php?type=view&ID=" . $history['ID'] . "\">" . $history['ID'] . "</A></td>";
	}
	else {
		echo "<td nowrap>" . $history['ID'] . "</td>";
	}
	echo "<td width=100%>" . mb_ereg_replace("\n", "<BR>", $history['Contents']) . "</td>";
	echo "</tr>\n";
}
?>
</table>

</body>
</html>

