<?php
require_once "common.php";

mb_internal_encoding("UTF-8");


$type = $_REQUEST['Type'];

if($type == "ID") {
	if($db->isExist($_REQUEST['ID'])) {
		echo "0";
	}
	else {
		echo "1";
	}
}
else if($type == "DioceseNum") {
	$condition = array("range" => "master", "diocese" => $_REQUEST["diocese"]);
	$dataAry = $db->find($condition);
	
	echo count($dataAry);
}
else if($type == "TeamLeader") {
	$diocese = $_REQUEST["Diocese"];
	$team    = $_REQUEST["Team"];
	$ID = $db->getLeaderID($diocese, $team);
	if($ID != 0) {
		$leader = $db->get($ID);
		echo $ID . ":" . $leader[0]["Name"];
	}
}

?>
