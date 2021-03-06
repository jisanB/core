<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/studentsSettings.php" ;

if (isActionAccessible($guid, $connection2, "/modules/School Admin/studentsSettings.php")==FALSE) {
	//Fail 0
	$URL.="&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	$extendedBriefProfile=$_POST["extendedBriefProfile"] ;
	$studentAgreementOptions="" ; 
	foreach (explode(",", $_POST["studentAgreementOptions"]) as $agreement) {
		$studentAgreementOptions.=trim($agreement) . "," ;
	}
	$studentAgreementOptions=substr($studentAgreementOptions,0,-1) ;
	
	//Write to database
	$fail=FALSE ;
	
	try {
		$data=array("value"=>$extendedBriefProfile); 
		$sql="UPDATE gibbonSetting SET value=:value WHERE scope='Students' AND name='extendedBriefProfile'" ;
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		$fail=TRUE ;
	}
	
	try {
		$data=array("value"=>$studentAgreementOptions); 
		$sql="UPDATE gibbonSetting SET value=:value WHERE scope='School Admin' AND name='studentAgreementOptions'" ;
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		$fail=TRUE ;
	}
	
	if ($fail==TRUE) {
		//Fail 2
		$URL.="&updateReturn=fail2" ;
		header("Location: {$URL}");
	}
	else {
		//Success 0
		getSystemSettings($guid, $connection2) ;
		$URL.="&updateReturn=success0" ;
		header("Location: {$URL}");
	}
}
?>