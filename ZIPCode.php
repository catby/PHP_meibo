<?php

$ZipCode = $_REQUEST['ZipCode'];

$DB = new PDO("sqlite:../ZipCode/ZipCode.db");
$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rs = $DB->query("SELECT Pref, Adrs FROM ZIP WHERE ZipCode='$ZipCode'");
$SQLOut = $rs->fetchAll(PDO::FETCH_ASSOC);
if(count($SQLOut)) {
	echo $SQLOut[0]['Pref'] . "<>" . $SQLOut[0]['Adrs'];
}

?>
