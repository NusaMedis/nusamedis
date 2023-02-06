                                                 <?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/encrypt.php");
    require_once($ROOT."lib/upload.php");
    require_once($ROOT."lib/tampilan.php");
    
    $dtaccess = new DataAccess();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);  
     $enc = new TextEncrypt();
    $auth = new CAuth();
    $err_code = 2;

    $sql = "select cust_usr_ktp from global.global_customer_user where cust_usr_kode like ".QuoteValue(DPE_CHAR,$_GET["nama"]."%")."
            order by cust_usr_when_update desc";
    $rs = $dtaccess->Execute($sql);
    $custFoto = $dtaccess->Fetch($rs);
    
    $foto = explode("_",$custFoto["cust_usr_ktp"]); 
        
    // --- buat foto ---
    if(!$_GET["orifoto"]) $oriFoto = "ktp_pasien_".$foto[2];
    if($_GET["orifoto"]) $oriFoto = $_GET["orifoto"];
    elseif($_POST["orifoto"]) $oriFoto = $_POST["orifoto"];
    
	if(!$_GET["nama"]) $namaFoto = "foto";
  if($_GET["nama"]) $namaFoto = & $_GET["nama"];
	elseif($_POST["nama"]) $namaFoto = & $_POST["nama"];

    $lokasi = $ROOT."gambar/foto_pasien";
    $maxSize = 500000;
    // --- ---
    //echo $oriFoto." - ".$namaFoto;

    if($_POST["btnSave"]){
        $temp = explode("_",$oriFoto);
        $counter = ($temp[2]+1);
        // -- check foto --
        if($_FILES["fotopas"]["tmp_name"]){
            switch($_FILES["fotopas"]["type"]){
                case "image/gif":
                    $destName = "ktp_pasien_".$namaFoto."_1.gif";
                    break;
                case "image/jpeg":
                case "image/pjpeg":
                    $destName = "ktp_pasien_".$namaFoto."_1.jpg";
                    break;
                case "image/png":
                    $destName = "ktp_pasien_".$namaFoto."_1.png";
                    break;
            }
            if(CheckUpload($_FILES["fotopas"], $lokasi, $maxSize, $destName)){
                $err_code = 0; 
                if($oriFoto) move_uploaded_file($destName,$lokasi."/".$oriFoto);
            } else $err_code = 1;
        }
    }
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php echo $view->RenderBody("ipad_depans.css",false," "); ?>
<?php if($err_code == 0){ ?>
    <script>
        window.opener.document.frmEdit.cust_usr_ktp.value='<?php echo $destName;?>';
        window.opener.document.original1.src='<?php echo $lokasi."/".$destName;?>';
        window.close();
    </script>
<?php } ?>

</head>

<body>
<form name="frmEdit" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>">
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
        <td align="left" colspan=2 class="tablesmallheader">UPLOAD FOTO</td>
    </tr>
    <tr>
        <td width="30%" align="right" class="tablecontent-odd"><strong>Foto</strong></td>
        <td width="70%" class="tablecontent"><input type="file" name="fotopas" size="20"></td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" name="btnSave" value="Upload" class="inputField"/>
        </td>
    </tr>

    </table>
<input type="hidden" name="orifoto" value="<?php echo $oriFoto;?>">
<input type="hidden" name="nama" value="<?php echo $namaFoto;?>">
<?php if($err_code==1){ ?>
    <font color="red">Upload Gagal!</font>
<?php } ?>
</form>

</body>
</html>
<?
    $dtaccess->Close();
?>
