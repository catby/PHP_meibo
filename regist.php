<?php
require_once "common.php";

mb_internal_encoding("UTF-8");

function getInitial($name)
{
	$kana = mb_convert_kana($name, "askh");
	if(strlen($kana) == 0) {
		return "x";
	}
	$initial = mb_substr($kana, 0, 1);
	if($initial >= "ｱ" && $initial <= "ｵ") {
		$initial = "a";
	}
	else if($initial >= "ｶ" && $initial <= "ｺ") {
		$initial = "k";
	}
	else if($initial >= "ｻ" && $initial <= "ｿ") {
		$initial = "s";
	}
	else if($initial >= "ﾀ" && $initial <= "ﾄ") {
		$initial = "t";
	}
	else if($initial >= "ﾅ" && $initial <= "ﾉ") {
		$initial = "n";
	}
	else if($initial >= "ﾊ" && $initial <= "ﾎ") {
		$initial = "h";
	}
	else if($initial >= "ﾏ" && $initial <= "ﾓ") {
		$initial = "m";
	}
	else if($initial >= "ﾔ" && $initial <= "ﾖ") {
		$initial = "y";
	}
	else if($initial >= "ﾗ" && $initial <= "ﾛ") {
		$initial = "r";
	}
	else if($initial >= "ﾜ" && $initial <= "ﾝ") {
		$initial = "w";
	}
	else {
		$initial = "x";
	}
	
	return $initial;
}


$data = convertToArray($_REQUEST);
for($i = 0 ; $i < count($data) ; $i++) {
	$data[$i]["Initial"] = getInitial($data[$i]["Phonetic"]);

	if(($data[$i]["BirthYear"] == 0) || ($data[$i]["BirthMonth"] == 0) || ($data[$i]["BirthDay"] == 0)) {
		$data[$i]["BirthYear"] = 0;
		$data[$i]["BirthMonth"] = 0;
		$data[$i]["BirthDay"] = 0;
	}
}

$db->regist($data);

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="refresh" CONTENT="2; URL=/meibo/view.php?type=view&ID=<?= $_REQUEST["ID"] ?>">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>登録処理</title>
</head>
<body>
<H1>登録/更新が完了しました</H1>
</body>
</html>
