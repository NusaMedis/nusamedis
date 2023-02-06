<?php session_start();
require_once("penghubung.inc.php");

DEFINE("PASSKEY","EXPRESSA");



class Crack
{
    var $_produkId;
    var $_serial;


    function Crack()
    {
        if(isset($_SERVER["WINDIR"])) $pathname = "C:\\WINDOWS";
        else $pathname = "/etc";

        $filename = $pathname."/host.dll";
        if (file_exists($filename)) { 
            $fd = fopen($filename, "rb");
            $tmp = fread($fd, filesize($filename));
            fclose($fd);
            $this->_produkId = base64_decode(trim($tmp)); 
        }
	}

	function GetProdukId()
	{
		return $this->_produkId;
	}
	
	function GetSerial()
	{
	    
        $produkID = explode("-",$this->_produkId);
        $_L1_serialID1 =  $produkID[0] ^ PASSKEY;
        $_L1_serialID2 =  $produkID[1] ^ PASSKEY;
        $_L1_serialID3 =  $produkID[2] ^ PASSKEY;
        $_L1_serialID4 =  $produkID[3] ^ PASSKEY;
        $_L1_serialID5 =  $produkID[4] ^ PASSKEY;
        
        //LEVEL 2 (MD5)
        $_L2_serialID1 = strtoupper(substr(md5($_L1_serialID1),1,4));
        $_L2_serialID2 = strtoupper(substr(md5($_L1_serialID2),3,4));
        $_L2_serialID3 = strtoupper(substr(md5($_L1_serialID3),5,4));
        $_L2_serialID4 = strtoupper(substr(md5($_L1_serialID4),2,4));
        $_L2_serialID5 = strtoupper(substr(md5($_L1_serialID5),1,4));
        
        $serial = $_L2_serialID1 . "-" . $_L2_serialID2  . "-" . $_L2_serialID3  . "-" . $_L2_serialID4  . "-" . $_L2_serialID5;
        return $serial;

	}

}

require_once($ROOT."lib/conf/host.php");

/*
if(!isset($_SESSION['INOSOFTSERIAL'])){
    if(!defined('CFG_HOST')){
    	die('Aplikasi tidak bisa berjalan ......  hubungi Administrator');
    	exit(1);
    }
    
	$iCrack = new Crack();
    if(!$iCrack->GetProdukId()){
    	die('Aplikasi tidak bisa berjalan ......  hubungi Administrator');
    	exit(1);
    }
    
    $crackSerial = $iCrack->GetSerial();
    if(strcmp($crackSerial,CFG_HOST)==0){
        $_SESSION['INOSOFTSERIAL'] = CFG_HOST;
    } else {
    	die('Aplikasi tidak bisa berjalan ......  hubungi Administrator');
    	exit(1);
    }  } */
  
?>
