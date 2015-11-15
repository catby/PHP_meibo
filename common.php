<?php
require_once "meiboDB.php";

$db = new meiboDB("./meibo.db");

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");


function getYYYYMMDD($ary, $kind, $num)
{
	$nengo = $ary[$kind . "Nengo_" . $num];
	$year  = $ary[$kind . "Year_"  . $num];
	$month = $ary[$kind . "Month_" . $num];
	$day   = $ary[$kind . "Day_"   . $num];
	
	if(strlen($year) && strlen($month) && strlen($day)) {
		return sprintf("%04d%02d%02d", getAD($nengo, $year), $month, $day);
	}
	return "";
}

function createBlankEntry(&$ary)
{
	for($i = count($ary) ; $i <= 10 ; $i++) {
		$tmp = array();
		$tmp["Name"]          = "";
		$tmp["Phonetic"]      = "";
		$tmp["Sex"]           = "";
		$tmp["BirthYear"]     = "";
		$tmp["BirthMonth"]    = "";
		$tmp["BirthDay"]      = "";
		$tmp["JointBirthDay"] = "";
		$tmp["ZIP"]           = "";
		$tmp["Pref"]          = "";
		$tmp["Address"]       = "";
		$tmp["TEL"]           = "";
		$tmp["Tokudo"]        = "";
		$tmp["Teacher"]       = "";
		
		if($i == 0) {
			$tmp["EntryDay"] = "";
			$tmp["Diocese"]  = "";
			$tmp["Team"]     = "";
			$tmp["isLeader"] = "";
			$tmp["Memo"]     = "";
		}
		else {
			$tmp["Relationship"] = "";
		}
		array_push($ary, $tmp);
	}
}

function convertToArray(&$input)
{
	$keys = array_keys($input);
	foreach($keys as $key) {
		$input[$key] = mb_ereg_replace("^[\s　]+", "", $input[$key]);
		$input[$key] = mb_ereg_replace("[\s　]+$", "", $input[$key]);
	}

	$output = array();
	for($i = 0 ; $i <= 10 ; $i++) {
		if(array_key_exists("Name_$i", $input) && strlen($input["Name_$i"])) {
			$tmp = array();
			$tmp["ID"]            = $input["ID"];
			$tmp["Name"]          = mb_convert_kana($input["Name_$i"], "S");
			$tmp["Phonetic"]      = mb_convert_kana($input["Phonetic_$i"], "ASHcV");
//			$tmp["Initial"]       = getInitial($tmp["Phonetic"]);
			$tmp["Sex"]           = $input["Sex_$i"];
			$tmp["BirthYear"]     = getAD($input["BirthNengo_$i"], $input["BirthYear_$i"]);
			$tmp["BirthMonth"]    = $input["BirthMonth_$i"];
			$tmp["BirthDay"]      = $input["BirthDay_$i"];
			$tmp["JointBirthDay"] = getYYYYMMDD($input, "Birth", $i);
			$tmp["ZIP"]           = array_key_exists("Address_$i", $_REQUEST) && strlen($input["ZIP3_$i"]) ? sprintf("%03d%04d", $input["ZIP3_$i"], $input["ZIP4_$i"]) : "";
			$tmp["Pref"]          = array_key_exists("Address_$i", $_REQUEST) ? $input["Pref_$i"] : "";
			$tmp["Address"]       = array_key_exists("Address_$i", $_REQUEST) ? mb_convert_kana($input["Address_$i"], "a") : "";
			$tmp["TEL"]           = $input["TEL_$i"];
			$tmp["Tokudo"]        = getYYYYMMDD($input, "Tokudo",  $i);
			$tmp["Teacher"]       = getYYYYMMDD($input, "Teacher", $i);
			
			if($i == 0) {
				$tmp["EntryDay"] = getYYYYMMDD($input, "Entry", $i);
				$tmp["Diocese"]  = $input["Diocese_$i"];
				$tmp["Team"]     = (($tmp["Diocese"] != "") && array_key_exists("Team_$i",     $input)) ? $input["Team_$i"] : "";
				$tmp["isLeader"] = (($tmp["Diocese"] != "") && array_key_exists("isLeader_$i", $input)) ? $input["isLeader_$i"] : "";
				$tmp["Memo"]     = $input["Memo_$i"];
			}
			else {
				$tmp["Relationship"] = $input["Relationship_$i"];
			}
			array_push($output, $tmp);
		}
	}
	return $output;
}

function getNengoArray()
{
	global $db;
	$nengoAry = $db->getNengoData();

	$output = array();
	foreach($nengoAry as $nengo) {
		array_push($output, $nengo["Nengo"]);
	}
	return $output;
}

function getAD($nengo, $year)
{
	if($year == "") {
		return 0;
	}
	global $db;
	$nengoAry = $db->getNengoData();
	
	foreach($nengoAry as $tmp) {
		if(mb_ereg($tmp["Nengo"], $nengo)) {
			return ($tmp["Year"] - 1) + $year;
		}
	}
}

function getNengo(&$year, $month, $day)
{
	$nengo = "";
	if($year == 0) {
		return $nengo;
	}
	
	$date = strtotime("$year-$month-$day");

	global $db;
	$nengoAry = $db->getNengoData();
	for($i = count($nengoAry) - 1 ; $i >= 0 ; $i--) {
		$target = strtotime($nengoAry[$i]["Year"] . "-" . $nengoAry[$i]["Month"] . "-" . $nengoAry[$i]["Day"]);
		if($date >= $target) {
			$nengo = $nengoAry[$i]["Nengo"];
			$year -= (intval($nengoAry[$i]["Year"]) - 1);
			return $nengo;
		}
	}
	return $nengo;
}

function getWareki($year, $month, $day)
{
	$era = getNengo($year, $month, $day);

	if($year == 1) {
		return "$era" . "元年";
	}
	else {
		return sprintf("%s%02d年", $era, $year);
	}
}

function getAge($year, $month, $day)
{
	$now = date('Ymd');
	$birthday = date('Ymd', strtotime("$year-$month-$day"));
	return floor(($now-$birthday)/10000);
}

function getOldAge($year)
{
	$now = date('Ymd');
	$birthday = date('Ymd', strtotime("$year-1-1"));
	return floor(($now-$birthday)/10000) + 1;
}

function getSchoolYear($year, $month, $day)
{
	$base = "";
	if(date('n') < 4) {
		$base = (date('Y')-1) . "0401";
	}
	else {
		$base = date('Y') . "0401";
	}
	$birthday = date('Ymd', strtotime("$year-$month-$day"));
	$schoolYear = floor(($base-$birthday)/10000);
	if($schoolYear < 6) {
		return "(未就学)";
	}
	if($schoolYear < 12) {
		return "(小学" . ($schoolYear-5) . "年)";
	}
	if($schoolYear < 15) {
		return "(中学" . ($schoolYear-11) . "年)";
	}
	return "";
}

function getLeaderName($db, $diocese, $team)
{
	$ID = $db->getLeaderID($diocese, $team);
	if($ID != 0) {
		$ret = $db->get($ID);
		return $ret[0]["Name"];
	}
	return "-";
}

function getDateStr($date, $header="")
{
	if($date == "") {
		return "";
	}
	$year  = substr($date, 0, 4);
	$month = substr($date, 4, 2);
	$day   = substr($date, 6, 2);
	
	$str = "";
	if(strlen($header)) {
		$str  = "<b>$header" . "：</b>";
	}
	$str .= getWareki($year, $month, $day);
	$str .= sprintf("%02d月", $month);
	$str .= sprintf("%02d日", $day);
	
	return $str;
}


?>
