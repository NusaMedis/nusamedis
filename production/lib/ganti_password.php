<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/expAJAX.php");
	   require_once($APLICATION_ROOT."lib/tampilan.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
	   $auth = new CAuth(); 
     $userData = $auth->GetUserData();  
     $backPage = "password_edit.php";
	   $usrId = $userData["id"];
	
	   $plx = new expAJAX("CheckPassBaru,CheckPassLama");

	   function CheckPassLama($pass)
	   {
          global $dtaccess,$usrId;
          
          $sql = "SELECT a.usr_id FROM global_auth_user a 
                    WHERE a.usr_password = ".QuoteValue(DPE_CHAR,md5($pass))." and usr_id = ".QuoteValue(DPE_NUMERIC,$usrId); 
          $rs = $dtaccess->Execute($sql);
          $dataUser = $dtaccess->Fetch($rs);
          
		 return $dataUser["usr_id"];
     }
	
  function CheckPassBaru($passLama,$passBaru)
	  {
          global $dtaccess;
          if($passBaru!=$passLama) {
			    $passSama = 1;
		}
          
		return $passSama;
     }
	
	
     if ($_POST["btnSave"]) {           
          $cfgId = & $_POST["cfg_id"]; 
         
          if ($err_code == 0) {
               $dbTable = "global.global_auth_user";
               
               $dbField[0] = "usr_id";   // PK
               $dbField[1] = "usr_password";  
			 
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
               $dbValue[1] = QuoteValue(DPE_CHAR,md5($_POST["usr_password_baru"]));  
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
               $dtmodel->Update() or die("update  error");	 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
			
      unset($_POST["usr_password_lama"]);
			unset($_POST["usr_password_baru"]);
			unset($_POST["usr_password_ulang"]);
          
          }
     }
?>
<?php echo $view->RenderBody("ipad_depans.css",true); ?>
<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{   
     if(!frm.usr_password_lama.value){
		alert('Password lama harus di isi');
		frm.usr_password_lama.focus();
          return false;
	}
     if(!frm.usr_password_lama.value){
		alert('Password baru harus di isi');
		frm.usr_password_lama.focus();
          return false;
	}
     if(!frm.usr_password_ulang.value){
		alert('Konfirmasi password harus di isi');
		frm.usr_password_ulang.focus();
          return false;
	} 
		 
	if(!CheckPassLama(frm.usr_password_lama.value,'type=r')) {
		alert('Password Lama tidak sama');
		frm.usr_password_lama.focus();
		return false;
	}
	
	if(CheckPassBaru(frm.usr_password_baru.value,frm.usr_password_ulang.value,'type=r')) { 
		alert('Konfirmasi password tidak sama');
		frm.usr_password_lama.focus();
		return false;
	}
	
	return true;
          
}
</script>
<body>
<div id="header"></div>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%"> 
<tr>
     <td>
     <fieldset>
     <legend><strong>Edit Password</strong></legend>
     <table width="100%" >
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Password Lama</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderPassword("usr_password_lama","usr_password_lama","20","50",$_POST["usr_password_lama"],"inputField", null,false);?>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Password Baru</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderPassword("usr_password_baru","usr_password_baru","20","50",$_POST["usr_password_baru"],"inputField", null,false);?>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Konfirmasi Password</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderPassword("usr_password_ulang","usr_password_ulang","20","50",$_POST["usr_password_ulang"],"inputField", null,false);?>
               </td>
          </tr>   
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>
<script>document.frmEdit.usr_password_lama.focus();</script>
<?php if($_POST["btnSave"]) { ?>
<font color="red" size="2">Password sudah di ganti </font>
<?php } ?>
</form>
<div id="footer"></div>
<?php echo $view->RenderBodyEnd(); ?>
