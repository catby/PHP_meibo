<?php
require_once "common.php";


$dataA = count($db->get_list("a"));
$dataK = count($db->get_list("k"));
$dataS = count($db->get_list("s"));
$dataT = count($db->get_list("t"));
$dataN = count($db->get_list("n"));
$dataH = count($db->get_list("h"));
$dataM = count($db->get_list("m"));
$dataY = count($db->get_list("y"));
$dataR = count($db->get_list("r"));
$dataW = count($db->get_list("w"));
$dataX = count($db->get_list("x"));
$total = $dataA + $dataK + $dataS + $dataT + $dataN + $dataH + $dataM + $dataY + $dataR + $dataW + $dataX;

$tokudoNum  = $db->getTokudoNum();
$teacherNum = $db->getTeacherNum();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>～～～寺 檀家名簿</title>

<style type="text/css">
H1 {
	text-align:center;
}

div {
	text-align:center;
}

table {
	width:80%;
	border:0px;
	margin-left: auto;
	margin-right: auto;
	font-size: 115%;
}
td, th {
	border:1px #2b2b2b solid;
	text-align:center;
}

</style>

</head>
<body>
<BR>
<BR>
<BR>
<H1>～～～寺<BR>檀家名簿</H1>
<BR>
<div>
登録件数：<?= $total ?>件<BR>
得度授戒者数：<?= $tokudoNum ?>人<BR>
教師拝受者数：<?= $teacherNum ?>人<BR>
<table>
<tr>
	<td><a href="list.php?initial=a">あ～お(<?= $dataA ?>)</A></td>
	<td><a href="list.php?initial=k">か～こ(<?= $dataK ?>)</A></td>
	<td><a href="list.php?initial=s">さ～そ(<?= $dataS ?>)</A></td>
	<td><a href="list.php?initial=t">た～と(<?= $dataT ?>)</A></td>
</tr>
<tr>
	<td><a href="list.php?initial=n">な～の(<?= $dataN ?>)</A></td>
	<td><a href="list.php?initial=h">は～ほ(<?= $dataH ?>)</A></td>
	<td><a href="list.php?initial=m">ま～も(<?= $dataM ?>)</A></td>
	<td><a href="list.php?initial=y">や～よ(<?= $dataY ?>)</A></td>
</tr>
<tr>
	<td><a href="list.php?initial=r">ら～ろ(<?= $dataR ?>)</A></td>
	<td><a href="list.php?initial=w">わ～ん(<?= $dataW ?>)</A></td>
	<td><a href="list.php?initial=x">その他(<?= $dataX ?>)</A></td>
	<td></td>
</tr>
</table>
</div>
</body>
</html>
