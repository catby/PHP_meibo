<?php
require_once "common.php";

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

$prevInitial = Array(
	"a" => "",
	"k" => "a",
	"s" => "k",
	"t" => "s",
	"n" => "t",
	"h" => "n",
	"m" => "h",
	"y" => "m",
	"r" => "y",
	"w" => "r",
	"x" => "w",
);
$nextInitial = Array(
	"a" => "k",
	"k" => "s",
	"s" => "t",
	"t" => "n",
	"n" => "h",
	"h" => "m",
	"m" => "y",
	"y" => "r",
	"r" => "w",
	"w" => "x",
	"x" => "",
);

$zipData   = array();
$adrsData  = array();
$birthData = array();

$getData = array();
$orgData = array();
$isView = false;
if(array_key_exists("type", $_REQUEST) && strcasecmp($_REQUEST["type"], "view") == 0) {
	$isView = true;
	$getData = $db->get($_REQUEST["ID"]);
	$orgData = $getData;
}
elseif(array_key_exists("type", $_REQUEST) && strcasecmp($_REQUEST["type"], "new") == 0) {
	$getData = convertToArray($_REQUEST);
	$orgData = $getData;
}
else {
	$getData = convertToArray($_REQUEST);
	$orgData = $db->get($_REQUEST["ID"]);
}

$newEntryDay = "";
if(array_key_exists("EntryDay", $getData[0])) {
	$newEntryDay = getDateStr($getData[0]["EntryDay"]);
}
$orgEntryDay = "";
if(array_key_exists("EntryDay", $orgData[0])) {
	$orgEntryDay = getDateStr($orgData[0]["EntryDay"]);
}
$newTeam     = getTeamStr($getData[0]);
$orgTeam     = getTeamStr($orgData[0]);

for($num = 0 ; $num <= 10 ; $num++) {
	if(!array_key_exists($num, $getData)) {
		continue;
	}
	if(strlen($getData[$num]["ZIP"]) == 7) {
		$zipData[$num] = substr($getData[$num]["ZIP"], 0, 3) . "-" . substr($getData[$num]["ZIP"], 3, 4);
	}
	else {
		$zipData[$num] = "";
	}
	$adrsData[$num]  = $getData[$num]["Pref"] . $getData[$num]["Address"];
	$birthData[$num] = getBirthDayStr($getData[$num]["JointBirthDay"]);
}
for($num = 0 ; $num <= 10 ; $num++) {
	if(!array_key_exists($num, $orgData)) {
		if(array_key_exists($num, $getData)) {
			$orgData[$num]["Name"]          = "";
			$orgData[$num]["Phonetic"]      = "";
			$orgData[$num]["Sex"]           = "";
			$orgData[$num]["Relationship"]  = "";
			$orgData[$num]["JointBirthDay"] = "";
			$orgData[$num]["ZIP"]           = "";
			$orgData[$num]["Pref"]          = "";
			$orgData[$num]["Address"]       = "";
			$orgData[$num]["TEL"]           = "";
			$orgData[$num]["Tokudo"]        = "";
			$orgData[$num]["Teacher"]       = "";
			$orgData[$num]["Name"]          = "";
		}
		else {
			continue;
		}
	}
	if(strlen($orgData[$num]["ZIP"]) == 7) {
		$zipData[100 + $num] = substr($orgData[$num]["ZIP"], 0, 3) . "-" . substr($orgData[$num]["ZIP"], 3, 4);
	}
	else {
		$zipData[100 + $num] = "";
	}
	$adrsData[100 + $num]  = $orgData[$num]["Pref"] . $orgData[$num]["Address"];
	$birthData[100 + $num] = getBirthDayStr($orgData[$num]["JointBirthDay"]);
}

$template = file_get_contents("template_viewMaster.html");
if($isView) {
	$initial = $getData[0]["Initial"];
	$dataAry = $db->get_list($initial);
	for($i = 0 ; $i < count($dataAry) ; $i++) {
		if($dataAry[$i]["ID"] == $_REQUEST["ID"]) {
			$prevID = $dataAry[$i]["ID"];
			if($i == 0) {
				for($tmpInitial = $prevInitial[$initial] ; $tmpInitial != "" ; $tmpInitial = $prevInitial[$tmpInitial]) {
					$tmpAry = $db->get_list($tmpInitial);
					if(count($tmpAry)) {
						$prevID = $tmpAry[count($tmpAry)-1]["ID"];
						break;
					}
				}
			}
			else {
				$prevID = $dataAry[$i-1]["ID"];
			}
			
			$nextID = $dataAry[$i]["ID"];
			if(($i+1) == count($dataAry)) {
				for($tmpInitial = $nextInitial[$initial] ; $tmpInitial != "" ; $tmpInitial = $nextInitial[$tmpInitial]) {
					$tmpAry = $db->get_list($tmpInitial);
					if(count($tmpAry)) {
						$nextID = $tmpAry[0]["ID"];
						break;
					}
				}
			}
			else {
				$nextID = $dataAry[$i+1]["ID"];
			}
			break;
		}
	}
	$tmp = "";
	$tmp .= '<A href="view.php?type=view&ID=' . $prevID . '"><img src="./img/Prev.png" width=30px alt="１つ前の名簿を表示" border=0></A>　' . "\n";
	$tmp .= '<A href="list.php?initial=' . $initial . "#" . $_REQUEST['ID'] . '"><img src="./img/up.png" width=30px alt="リストに戻る" border=0></A>　' . "\n";
	$tmp .= '<A href="view.php?type=view&ID=' . $nextID . '"><img src="./img/Next.png" width=30px alt="１つ後の名簿を表示" border=0></A>' . "\n";
	$template = str_replace('#header1#', $tmp, $template);

	$tmp = "";
	$tmp .= '<div class="right">' . "\n";
	$tmp .= '<form method="post" name ="Main" action="input.php">' . "\n";
	$tmp .= '[<a href="#" onclick="document.Main.submit()">この名簿を編集する</a>]　' . "\n";
	$tmp .= '[<a href="#" onclick="checkDelete()">この名簿を削除する</a>]' . "\n";
	$tmp .= '<input type="hidden" name="type" value="edit">' . "\n";
	$tmp .= '<input type="hidden" name="ID" value="' . $_REQUEST['ID'] . '">' . "\n";
	$tmp .= '</form>' . "\n";
	$tmp .= '</div>' . "\n";
	$template = str_replace('#header2#', $tmp, $template);
}
else {
	$tmp = "";
	$tmp .= '<form method="post" action="input.php">' . "\n";
	$tmp .= '<input type="submit" value="戻る">' . "\n";
	foreach ($_REQUEST as $key => $value) {
		$tmp .= "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n";
	}
	if(array_key_exists("type", $_REQUEST) && strcasecmp($_REQUEST["type"], "edit") == 0) {
		$tmp .= "<input type=\"hidden\" name=\"type\" value=\"reedit\">\n";
	}
	$tmp .= '</form>' . "\n";
	$tmp .= "\n";
	$tmp .= '<form method="post" action="regist.php">' . "\n";
	foreach ($_REQUEST as $key => $value) {
		$tmp .= "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n";
	}
	$tmp .= '<input type="submit" value="登録">' . "\n";
	$tmp .= '<BR>' . "\n";
	$template = str_replace('#header1#', $tmp, $template);
	$template = str_replace('#header2#', "<div class=\"right\">　</div>",   $template);
}

$template = str_replace('#idx#',        $_REQUEST['ID'],                     $template);
$template = str_replace('#Name0#',      getPrintStr($getData[0]["Name"],     $orgData[0]["Name"]),     $template);
$template = str_replace('#Phonetic0#',  getPrintStr($getData[0]["Phonetic"], $orgData[0]["Phonetic"]), $template);
$template = str_replace('#Sex0#',       getPrintStr($getData[0]["Sex"],      $orgData[0]["Sex"]),      $template);
$template = str_replace('#TEL0#',       getPrintStr($getData[0]["TEL"],      $orgData[0]["TEL"]),      $template);
$template = str_replace('#ZIP0#',       getPrintStr($zipData[0],             $zipData[100]),           $template);
$template = str_replace('#Address0#',   getPrintStr($adrsData[0],            $adrsData[100]),          $template);
$template = str_replace('#EntryDay#',   getPrintStr($newEntryDay,            $orgEntryDay),            $template);
$template = str_replace('#BirthDay0#',  getPrintStr($birthData[0],           $birthData[100]),         $template);
$template = str_replace('#Team#',       getPrintStr($newTeam,                $orgTeam),                $template);

if($getData[0]["Tokudo"] + $orgData[0]["Tokudo"] + $getData[0]["Teacher"]+ $orgData[0]["Teacher"] != 0) {
	$tmp  = " 	<tr><th>拝受</th><td colspan=5>\n";
	$tmp .= "#Tokudo#<BR>\n";
	$tmp .= "#Teacher#\n";
	$tmp .= "</td></tr>\n";
	$template = str_replace('#Haiju0#',  $tmp, $template);
	$newTokudo = getDateStr($getData[0]["Tokudo"]);
	$orgTokudo = getDateStr($orgData[0]["Tokudo"]);
	if(($newTokudo . $orgTokudo) != "") {
		$template = str_replace('#Tokudo#',  "<b>得度：</b>" . getPrintStr($newTokudo, $orgTokudo), $template);
	}
	else {
		$template = str_replace('#Tokudo#',  "", $template);
	}
	$newTeacher = getDateStr($getData[0]["Teacher"]);
	$orgTeacher = getDateStr($orgData[0]["Teacher"]);
	if(($newTeacher . $orgTeacher) != "") {
		$template = str_replace('#Teacher#', "<b>教師：</b>" . getPrintStr($newTeacher, $orgTeacher), $template);
	}
	else {
		$template = str_replace('#Teacher#',  "", $template);
	}
}
else {
	$template = str_replace('#Haiju0#', "", $template);
}


echo $template;

?>
</table>
<BR>
<H2>家族欄</H2>
<HR>
<table>
<?php
for($num = 1 ; $num <= 10 ; $num++) {
	if(!array_key_exists($num, $getData) || strlen($getData[$num]["Name"]) == 0) {
		continue;
	}
	$template = file_get_contents("template_viewFamiry.html");
	$template = preg_replace('/^[\s\S]+<table>/', "", $template);
	$template = preg_replace('/<\/table>[\s\S]+/', "", $template);
	
	$rowSpanVal = 2;

	$template = str_replace('#Num#',          $num,                                                                        $template);
	$template = str_replace('#Name#',         getPrintStr($getData[$num]["Name"],         $orgData[$num]["Name"]),         $template);
	$template = str_replace('#Phonetic#',     getPrintStr($getData[$num]["Phonetic"],     $orgData[$num]["Phonetic"]),     $template);
	$template = str_replace('#Sex#',          getPrintStr($getData[$num]["Sex"],          $orgData[$num]["Sex"]),          $template);
	$template = str_replace('#Relationship#', getPrintStr($getData[$num]["Relationship"], $orgData[$num]["Relationship"]), $template);
	$template = str_replace('#BirthDay#',     getPrintStr($birthData[$num],               $birthData[100 + $num]),         $template);
	
	if(strlen($getData[$num]["Address"]) + strlen($getData[$num]["TEL"])) {
		$rowSpanVal++;
		
		$tmp  = "";
		$tmp .= '		<tr><th width=8%>住所</th><td colspan="3">	〒#ZIP#<BR>' . "\n";
		$tmp .= '													#Address#</td>' . "\n";
		$tmp .= '			<th width=10%>電話番号</th><td>#TEL#</td>' . "\n";
		
		$template = str_replace('#Address#', $tmp,                                                      $template);
		$template = str_replace('#ZIP#',     getPrintStr($zipData[$num],        $zipData[100 + $num]),  $template);
		$template = str_replace('#Address#', getPrintStr($adrsData[$num],       $adrsData[100 + $num]), $template);
		$template = str_replace('#TEL#',     getPrintStr($getData[$num]["TEL"], $orgData[$num]["TEL"]), $template);
	}
	else {
		$template = str_replace('#Address#', "", $template);
	}
	
	if($getData[$num]["Tokudo"] + $orgData[$num]["Tokudo"] + $getData[$num]["Teacher"]+ $orgData[$num]["Teacher"] != 0) {
		$rowSpanVal++;
		
		$tmp  = " 	<tr><th>拝受</th><td colspan=5>\n";
		$tmp .= "#Tokudo#<BR>\n";
		$tmp .= "#Teacher#\n";
		$tmp .= "</td></tr>\n";
		$template = str_replace('#Haiju#',  $tmp, $template);
		$newTokudo = getDateStr($getData[$num]["Tokudo"]);
		$orgTokudo = getDateStr($orgData[$num]["Tokudo"]);
		if(($newTokudo . $orgTokudo) != "") {
			$template = str_replace('#Tokudo#',  "<b>得度：</b>" . getPrintStr($newTokudo, $orgTokudo), $template);
		}
		else {
			$template = str_replace('#Tokudo#',  "", $template);
		}
		$newTeacher = getDateStr($getData[$num]["Teacher"]);
		$orgTeacher = getDateStr($orgData[$num]["Teacher"]);
		if(($newTeacher . $orgTeacher) != "") {
			$template = str_replace('#Teacher#', "<b>教師：</b>" . getPrintStr($newTeacher, $orgTeacher), $template);
		}
		else {
			$template = str_replace('#Teacher#',  "", $template);
		}
	}
	else {
		$template = str_replace('#Haiju#', "", $template);
	}
	$template = str_replace('#rowSpanVal#', $rowSpanVal, $template);
	echo $template;
}
?>
</table>

<BR>
<H2>備考欄</H2>
<HR>
<table>
<tr><td><pre><?= strlen($getData[0]["Memo"]) != 0 ? $getData[0]["Memo"] : "&nbsp;" ?></pre></td></tr>
</table>

</form>
</table>

</body>
</html>

<?php

function getBirthDayStr($date)
{
	if(strlen($date) != 8) {
		return "";
	}
	$year  = substr($date, 0, 4);
	$month = substr($date, 4, 2);
	$day   = substr($date, 6, 2);
	
	$str  = getWareki($year, $month, $day);
	$str .= sprintf("%02d月", $month);
	$str .= sprintf("%02d日", $day);
	$str .= "　[" . getAge($year, $month, $day) . "歳" . getSchoolYear($year, $month, $day) ."]";
	$str .= "　[数え年：" . getOldAge($year) . "歳]";
	
	return $str;
}

function getTeamStr($data)
{
	global $db;

	$tmp = "";
	if(array_key_exists("Diocese", $data) && ($data["Diocese"] != "")) {
		$tmp .= $db->getDioceseName($data["Diocese"]);
		if(array_key_exists("Team", $data) && ($data["Team"] != "")) {
			$tmp .= " " . $data["Team"] . "班";
			$leaderID = $db->getLeaderID($data["Diocese"], $data["Team"]);
			if($leaderID != 0) {
				$leader = $db->get($leaderID);
				$tmp .= " (班長 : <A href=\"view.php?type=view&ID=$leaderID\">" . $leader[0]["Name"] . "</A> )";
			}
		}
	}
	return $tmp;
}

function getPrintStr($newStr, $orgStr)
{
	if(strcmp($newStr, $orgStr) == 0) {
		return $newStr;
	}
	else {
		if(strlen($orgStr) == 0) {
			return "<span style=\"color:#FF0000\">$newStr</span>";
		}
		else {
			return "<span style=\"color:#FF0000\">$orgStr ⇒ $newStr</span>";
		}
	}
}

?>
