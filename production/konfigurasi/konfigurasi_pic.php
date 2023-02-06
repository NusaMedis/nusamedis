<?php
	require_once("../penghubung.inc.php");
	require_once($LIB."upload.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
  //require_once($LIB."currency.php");

	$fileElementName = "fileToUpload";
	$lokasi = $ROOT."gambar/img_cfg";
  //echo $lokasi;
  //die();   
  $arr_mime = array("image/gif","image/pjpeg","image/jpeg","image/png","image/x-icon");
	
	$error = InoUpload($_FILES[$fileElementName],$lokasi,null,$newName,$arr_mime);

	$msg .= "Upload Success...";
	$msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
	$msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);

	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "',\n";
	echo				"file: '" . $newName . "'\n";
	echo "}";
?>
