<?php
	require_once("penghubung.inc.php");
	require_once($ROOT."lib/upload.php");

	$fileElementName = "fileToUpload";
	$lokasi = $ROOT."gambar/item";
     
     $arr_mime = array("image/gif","image/pjpeg","image/jpeg","image/png", "image/x-icon");
	
	$error = InoUpload($_FILES[$fileElementName],$lokasi,null,$newName,$arr_mime);

	$msg .= "Upload Berhasil...";
	$msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
	$msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);

	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "',\n";
	echo				"file: '" . $newName . "'\n";
	echo "}";
?>
