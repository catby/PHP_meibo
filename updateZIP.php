<?php
if(!array_key_exists("cmd", $_REQUEST)) {
?>


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="base.css">
<title>郵便番号更新</title>
<script type="text/javascript">
<!--
var timer;
var progressCnt;
function pushButton()
{
	document.getElementById('button').disabled = true;
	document.getElementById('button').value = "...データベース更新中...";
	progressCnt = 0;

	timer = setInterval("updateZIP()",50);
}

function updateZIP()
{
	var progress = document.getElementById('progress');
	var error    = document.getElementById('error');
	var ret;

	if(error.innerHTML.length != 0) {
		clearTimeout(timer);
		document.getElementById('button').disabled = false;
		document.getElementById('button').value = "データベース更新開始";
		return;
	}
	
	switch(progressCnt) {
	case 0:
		progress.innerHTML = "郵便番号データのダウンロード・・・";
		break;
	case 1:
		ret = sendServerCmd("download");
		if(ret.length == 0) {
			progress.innerHTML += "完了<BR>";
		}
		else {
			error.innerHTML = ret;
			return;
		}
		break;
	case 2:
		progress.innerHTML += "郵便番号データの解凍・・・";
		break;
	case 3:
		ret = sendServerCmd("extract");
		if(ret.length == 0) {
			progress.innerHTML += "完了<BR>";
		}
		else {
			error.innerHTML = ret;
			return;
		}
		break;
	case 4:
		progress.innerHTML += "郵便番号データベースの作成・・・";
		break;
	case 5:
		clearTimeout(timer);
		ret = sendServerCmd("makeDB");
		if(ret.length == 0) {
			progress.innerHTML += "完了<BR><BR>";
			progress.innerHTML += "<H3>データベースの更新が正常に完了しました。<H3>";
			progress.innerHTML += "3秒後にこのウィンドウは閉じます";
			setTimeout("window.close()", 3000);
			
			document.getElementById('button').disabled = false;
			document.getElementById('button').value = "データベース更新開始";
		}
		else {
			error.innerHTML = ret;
			return;
		}
		break;
	}
	progressCnt++;
}

function sendServerCmd(cmd)
{
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("GET", "http://catby.webcrow.jp/meibo/updateZIP.php?cmd=" + cmd, false);
	xmlHttp.send(null);
	
	return xmlHttp.responseText;
}
-->
</script>
</head>
<body>

<H2>郵便番号データベース 更新</H2>
<HR>
郵便番号データベースの更新を行います。<BR>
<input type="button" value="データベース更新開始" style="width=250px;" id="button" onClick="pushButton();"><BR><BR>
<span id="progress"></span><span id="error" style="color: #ff0000;"></span>
</body>
</html>
<?php
}
else if($_REQUEST["cmd"] == "download") {
	if(!file_exists('../ZipCode/')) {
		if(mkdir('../ZipCode/') == FALSE) {
			echo "出力先フォルダに作成に失敗しました";
			exit();
		}
	}
	$image_path = file_get_contents("http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip", FILE_BINARY);
	if($image_path == FALSE) {
		echo "ダウンロードに失敗しました(file_get_contents)";
		exit();
	}
	if(!file_put_contents("../ZipCode/ken_all.zip", $image_path)) {
		echo "ダウンロードに失敗しました(file_put_contents)";
		exit();
	}
}
else if($_REQUEST["cmd"] == "extract") {
	$zip = new ZipArchive();
	$res = $zip->open("../ZipCode/ken_all.zip");
	if (!$res) {
		echo "解凍に失敗しました(ZipArchive::open)";
		exit();
	}
	if($zip->extractTo('../ZipCode/') == FALSE) {
		echo "解凍に失敗しました(ZipArchive::extractTo)";
		exit();
	}
	$zip->close();
}
else if($_REQUEST["cmd"] == "makeDB") {
	$DB = new PDO("sqlite:../ZipCode/newZipCode.db");
	$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$DB->exec("CREATE TABLE ZIP(ZipCode TEXT, Pref TEXT, Adrs TEXT)");

	$allData = file_get_contents("../ZipCode/KEN_ALL.CSV");
	$allData = mb_convert_encoding($allData, "UTF-8", "SJIS");
	$temp = tmpfile();
	fwrite($temp, $allData);
	rewind($temp);

	$DB->beginTransaction();
	while (($data = fgetcsv($temp)) !== FALSE) {
		$DB->exec("INSERT INTO ZIP(ZipCode, Pref, Adrs) VALUES('$data[2]', '$data[6]', '$data[7]$data[8]')");
	}
	$DB->commit();
	unset($DB);
	
	if(file_exists("../ZipCode/ZipCode.db")) {
		if(!unlink("../ZipCode/ZipCode.db")) {
			echo "データベースの更新に失敗しました(unlink)";
			exit();
		}
	}
	if(!rename("../ZipCode/newZipCode.db", "../ZipCode/ZipCode.db")) {
		echo "データベースの更新に失敗しました(rename)";
		exit();
	}
}

?>