<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."expAJAX.php");
	   require_once($LIB."tampilan.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
	   $auth = new CAuth(); 
     $userData = $auth->GetUserData();  
    // $backPage = "password_edit.php";
	   $usrId = $userData["id"];
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
    
	   //$plx = new expAJAX("CheckPassBaru,CheckPassLama");

	  /* function CheckPassLama($pass)
	   {
          global $dtaccess,$usrId;
          
          $sql = "SELECT a.usr_id FROM global_auth_user a 
                  WHERE a.usr_password = ".QuoteValue(DPE_CHAR,md5($pass))." and usr_id = ".QuoteValue(DPE_NUMERIC,$usrId); 
          $rs = $dtaccess->Execute($sql);
          $dataUser = $dtaccess->Fetch($rs);
          
		 return $dataUser["usr_id"];
     }*/
	
 /*function CheckPassBaru($passLama,$passBaru)
	  {
          global $dtaccess;
          if($passBaru!=$passLama) {
			    $passSama = 1;
		}
          
		return $passSama;
     } */
	
	
     if ($_POST["btnSave"]) {           
          $cfgId = & $_POST["cfg_id"];
          
           // cek userloginName ee di isi ato gak??
           $sql = "select * from global.global_auth_user where usr_loginname =".QuoteValue(DPE_CHAR,$_POST["usr_name"]);
           $rs = $dtaccess->Execute($sql);
           $CekLoginNames = $dtaccess->Fetch($rs);
           
           // cek userName ee di isi ato gak??
           $sql = "select * from global.global_auth_user where usr_name =".QuoteValue(DPE_CHAR,$_POST["nama_user"]);
           $rs = $dtaccess->Execute($sql);
           $CekUserNames = $dtaccess->Fetch($rs);
         
          if ($err_code == 0) {
               $dbTable = "global.global_auth_user";
               
               $dbField[0] = "usr_id";   // PK
               $dbField[1] = "usr_password";
               $dbField[2] = "usr_loginname";
               $dbField[3] = "usr_name";  
			 
               $dbValue[0] = QuoteValue(DPE_CHAR,$usrId);
               $dbValue[1] = QuoteValue(DPE_CHAR,md5($_POST["password_baru"]));
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["nama_user"]);   
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
               $dtmodel->Update() or die("update  error");	 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
			
        //unset($_POST["usr_password_lama"]);
			  unset($_POST["password_baru"]);
			  unset($_POST["confirm_password"]);
				unset($_POST["usr_name"]);
        unset($_POST["nama_user"]);  
          }
     }
     
     $sql = "select * from global.global_auth_user where usr_id =".QuoteValue(DPE_CHAR,$usrId);
     $rs = $dtaccess->Execute($sql);
     $Useree = $dtaccess->Fetch($rs);
     $_POST["usr_name"] = $Useree["usr_loginname"];
     $_POST["nama_user"] = $Useree["usr_name"];
     
     
     //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
    
?>

<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>/lib/javascript/jquery.js"></script>
<script src="<?php echo $ROOT;?>/lib/javascript/jquery.easyui.min.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
 <script type="text/javascript">
   $("#confirm_password").change(function(){
     if($(this).val() != $("#password_baru").val()){
               alert("Password Tidak sama");
               //more processing here
     }
});
 </script> 
<script type="text/javascript">

function CheckDataSave(frm)
{   
 
  if(!frm.nama_user.value){
		alert('Nama User harus di isi');
		frm.nama_user.focus();
          return false;
	}
  
  if(!frm.usr_name.value){
		alert('User Login harus di isi');
		frm.usr_name.focus();
          return false;
	}
     if(!frm.password_baru.value){
		alert('Password baru harus di isi');
		frm.usr_password_lama.focus();
          return false;
	} 

	return true;
          
}
</script>
<body>

<div id="body">
<div id="scroller">
<form name="frmEdit" id="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%"> 
<tr>
     <td>
     <table width="100%" >
     <tr>
     <td colspan="2"></td>
     </tr>
     <tr>
          <td class="tablesmallheader" width="30%" align="right"><strong>Nama User</strong>&nbsp;</td>
          <td width="70%">
          <?php //echo $view->RenderTextBox("usr_name","usr_name","30","50",$_POST["usr_name"],"inputField", null,false);?>
          <input type="text" name="nama_user" id="nama_user" maxlength="50" style="top :178px; left :70px; width :550px; height :25px;" value="<?php echo $_POST["nama_user"];?>" readonly="readonly">
          </td>
          </tr>
     <tr>
     <td colspan="2"></td>
     </tr>
     <tr>
          <td class="tablesmallheader" width="30%" align="right"><strong>User Login</strong>&nbsp;</td>
          <td width="70%">
          <?php //echo $view->RenderTextBox("usr_name","usr_name","30","50",$_POST["usr_name"],"inputField", null,false);?>
          <input type="text" name="usr_name" id="usr_name" maxlength="50" style="top :178px; left :70px; width :550px; height :25px;" value="<?php echo $_POST["usr_name"];?>" readonly="readonly">
          </td>
          </tr>
     <tr>
     <td colspan="2"></td>
     </tr>
         <!-- <tr>
               <td align="right" class="tablesmallheader" width="30%"><strong>Password Lama</strong>&nbsp;</td>
               <td width="70%">
                    <?php //echo $view->RenderPassword("usr_password_lama","usr_password_lama","50","50",$_POST["usr_password_lama"],"inputField", null,false);?>
               <input type="password" name="usr_password_lama" id="usr_password_lama" maxlength="50" style="top :178px; left :70px; width :550px; height :25px;" value="<?php echo $_POST["usr_password_lama"];?>">
               </td>
          </tr> -->
          <tr>
     <td colspan="2"></td>
     </tr>  
          <tr>
               <td align="right" class="tablesmallheader" width="30%"><strong>Password Baru</strong>&nbsp;</td>
               <td width="70%">
                    <?php //echo $view->RenderPassword("password_baru","password_baru","50","50",$_POST["password_baru"],"inputField", null,false);?>
               <input name="password_baru" type="password" id="password_baru" maxlength="30" placeholder="Ketik Password" class="text-input required password">

               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="30%"><strong>Konfirmasi Password</strong>&nbsp;</td>
               <td width="70%">
                    <?php //echo $view->RenderPassword("password_baru","password_baru","50","50",$_POST["password_baru"],"inputField", null,false);?>
               <input name="confirm_password" type="password" id="confirm_password" placeholder="Ketik Lagi" class="text-input required password">
               </td>
          </tr>
          <tr>
     <td colspan="2"></td>
     </tr>  
          <!--<tr>
               <td align="right" class="tablesmallheader" width="30%"><strong>Konfirmasi Password</strong>&nbsp;</td>
               <td width="70%">
                    <?php //echo $view->RenderPassword("usr_password_ulang","usr_password_ulang","50","50",$_POST["usr_password_ulang"],"inputField", null,false);?>
               <input type="password" name="usr_password_ulang" id="usr_password_ulang" maxlength="50" style="top :178px; left :70px; width :550px; height :25px;" value="<?php echo $_POST["usr_password_ulang"];?>">
               </td>
          </tr>-->
          <tr>
     <td colspan="2"></td>
     </tr>   
          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <input type="button" name="btnBack" value="Batal" onclick="window.close();">
               </td>
          </tr>
     </table>

     </td>
</tr>
</table>
<script>document.frmEdit.nama_user.focus();</script>

<?php if($_POST["btnSave"] && !$CekUserNames) { ?>
<font color="red" size="2">Nama User sudah di ganti </font>
<br />
<?php } ?>

<?php if($_POST["btnSave"] && !$CekLoginNames) { ?>
<font color="red" size="2">UserLogin sudah di ganti </font>
<br />
<?php } ?>
<?php if($_POST["btnSave"]) { ?>
<font color="red" size="2">Password sudah di ganti </font>
<?php } ?>
</form>
</div>
</div>

<?php if($konfigurasi["dep_konf_dento"]=='y') { ;?>    
<!--------Buat Helpicon----------->
<script type="text/javascript">
function showHideGB(){
var gb = document.getElementById("gb");
var w = gb.offsetWidth;
gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
gb.opened = !gb.opened;
}
function moveGB(x0, xf){
var gb = document.getElementById("gb");
var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
<a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
<a rel="sepur" href="<?php echo $ROOT;?>demo/ganti_password.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>
