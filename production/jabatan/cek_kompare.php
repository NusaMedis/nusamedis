<?php
     require_once("../penghubung.inc.php");
	 require_once($LIB."bit.php");							  
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();    
   
     $editPage = "role_edit.php";
    
     if(!$auth->IsAllowed("man_user_jabatan",PRIV_READ) && !$auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("man_user_jabatan",PRIV_READ)===1 || $auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
	   if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	   else $_x_mode = "New";
	
	  $plx = new expAJAX("CheckRole");
    
	function CheckRole($rolName,$idDep,$rolId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.rol_id FROM global.global_auth_role a 
                  WHERE a.id_dep =  ".QuoteValue(DPE_CHAR,strtoupper($idDep))."
                  and upper(a.rol_name) = ".QuoteValue(DPE_CHAR,strtoupper($rolName));  
          if($rolId) $sql .= " and a.rol_id <> ".QuoteValue(DPE_NUMERIC,$rolId);      
          $rs = $dtaccess->Execute($sql);
          $dataRole = $dtaccess->Fetch($rs);
          
		return $dataRole["rol_id"];
  }

   if($_POST["rol_id"])  $rolId = & $_POST["rol_id"];
 
     if ($_GET["id"]) {

          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $rolId = $enc->Decode($_GET["id"]);
          }
          
          $sql = "select * from global.global_auth_role where rol_id = ".$rolId;
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          //echo $sql;
          
          $dtaccess->Clear($rs_edit);
          $_POST["rol_id"] = $row_edit["rol_id"];
          $_POST["rol_name"] = $row_edit["rol_name"];
          $_POST["dep_id"] = $row_edit["id_dep"];
          $_POST["rol_jabatan"] = $row_edit["rol_jabatan"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $back = "role_view.php";
          
     }
     
     if($_x_mode=="New") $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;   

	   
     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     /*  if ($_GET["tambah"]) {
      $_POST["klinik"]=$_GET["tambah"];
      $back = "role_view.php?kembali=".$_POST["klinik"];
     //echo $_GET["tambah"];
     } */
     
    if ($_POST["btnSave"]) {
    
          $err_code = 3;
          //--- Checking Data ---//
          if ($_POST["rol_name"]) $err_code = clearbit($err_code,1); 
          else $err_code = setbit($err_code,1);
          
          if ($_POST["btnSave"]) 
               $sql = "SELECT rol_id FROM global.global_auth_role WHERE rol_name = ".QuoteValue(DPE_CHAR,$_POST["rol_name"])." and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          else
               $sql = "SELECT rol_id FROM global.global_auth_role WHERE rol_name = ".QuoteValue(DPE_CHAR,$_POST["rol_name"])." and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])." and rol_id <> ".QuoteValue(DPE_NUMERIC,$_POST["rol_id"]);
              
          $rs_check = $dtaccess->Execute($sql);
          
          if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
          else $err_code = clearbit($err_code,2); 
          
          $dtaccess->Clear($rs_check);

          if ($err_code == 0) {
               $dbTable = "global.global_auth_role";
               
               $dbField[0] = "rol_id";   // PK
               $dbField[1] = "rol_name";
               $dbField[2] = "id_app";
               $dbField[3] = "id_dep";
               $dbField[4] = "rol_jabatan";
   
               $rolId = $dtaccess->GetNewID("global.global_auth_role","rol_id",DB_SCHEMA_GLOBAL);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$rolId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rol_name"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,'10');
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["rol_jabatan"]);
               
               //print_r($dbValue);
               //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Insert() or die("insert  error");	

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               //$back = "role_view.php?kembali=".$_POST["klinik"]."";
               $back = "role_view.php";
               header("location:".$back);
               exit();        
            }
     }
     
      if ($_POST["btnUpdate"]) {

          		if($_POST["btnUpdate"]){
                         $rolId = & $_POST["rol_id"];
                         $_x_mode = "Edit";
          		}

               $dbTable = "global.global_auth_role";
               
               $dbField[0] = "rol_id";   // PK
               $dbField[1] = "rol_name";
               $dbField[2] = "id_app";
   
               if(!$rolId) $rolId = $dtaccess->GetNewID("global.global_auth_role","rol_id",DB_SCHEMA_GLOBAL);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$rolId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rol_name"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,'10');
               
               //print_r($dbValue);
               //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Update() or die("update  error");	

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               

               //$back = "role_view.php?kembali=".$_POST["klinik"]."";
               $back = "role_view.php";
               header("location:".$back);
               exit();        

     }

    if ($_GET["del"]) {
        $rolId = $enc->Decode($_GET["id"]);

            $sql = "delete from global.global_auth_role where rol_id = ".$rolId;
            $dtaccess->Execute($sql);
            
               //$back = "role_view.php?kembali=".$_POST["klinik"]."";
               $back = "role_view.php";
               header("location:".$back);
               exit();    
    }

      //Query Nama Klinik
      $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_POST["klinik"]."'";
      $rs = $dtaccess->Execute($sql);
      $namaKlinik = $dtaccess->Fetch($rs);
      
      $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_POST["dep_id"]."'";
      $rs = $dtaccess->Execute($sql);
      $namaKlinikEdit = $dtaccess->Fetch($rs);
      
      //Nama Sekolah
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
      $klinikHeaderEdit = "Klinik : ".$namaKlinikEdit["dep_nama"];
      // echo $klinikHeaderEdit;
?>

<script type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{  
   var dep='<?php echo $_POST["klinik"];?>';

   if(!frm.rol_name.value)
    {
		alert('Role Name harus di isi');
		frm.rol_name.focus();
          return false;
	 }

	if(CheckRole(frm.rol_name.value,dep,frm.rol_id.value,'type=r')) 
  { 
		alert('Nama Jabatan tidak boleh sama');
		frm.rol_name.focus();
		return false;
	} 
	
	return true;
          
}
	</script>
	
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/elements.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/func_curr.js"></script>
</head>

<body>
<div id="body">
<div id="scroller">
<br />

<body>
<table width="80%">
     <!--<tr>
          <?php if($_x_mode == "New") { ?>
          <td align="right" colspan=2 >&nbsp;<?php echo "<b>".$klinikHeader."</b>";?></td>
          <?php } else { ?>
          <td align="right" colspan=2 >&nbsp;<?php echo "<b>".$klinikHeaderEdit."</b>";?></td>
          <?php } ?>
     </tr>-->
</table>
<?php if($_x_mode == "Edit") { ?>
<form name="frmEdit" method="POST" action="<?php echo $editPage;?>">
<table width="80%" border="1" cellpadding="1" cellspacing="1">
<tr>
    <td>
    <fieldset>
    <legend><strong>Edit Jabatan</strong></legend>
			<table width="100%" border="1" cellpadding="1" cellspacing="1">
			   <tr>
			        <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Nama Jabatan</strong></td>
			        <td width="70%"><input onKeyDown="return tabOnEnter(this, event);" type="text" name="rol_name" id="rol_name" size="50" maxlength="100" value="<?php echo $_POST["rol_name"];?>"/></td>
			    </tr>
			    <tr>
			        <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Jabatan</strong></td>
			        <td width="70%">
              <select name="rol_jabatan" id="rol_jabatan" onKeyDown="return tabOnEnter(this, event);">								
                <option value="<?php echo STS_JAB_DOKTER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_DOKTER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_DOKTER];?></option>
                <option value="<?php echo STS_JAB_PPDS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PPDS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PPDS];?></option>
                <option value="<?php echo STS_JAB_PERAWAT;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PERAWAT) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PERAWAT];?></option>
                <option value="<?php echo STS_JAB_STAFF;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_STAFF) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_STAFF];?></option>
                <option value="<?php echo STS_JAB_ANALIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANALIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANALIS];?></option>
                <option value="<?php echo STS_JAB_RADIOGRAFER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_RADIOGRAFER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_RADIOGRAFER];?></option>
                <option value="<?php echo STS_JAB_FISIOTERAPIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_FISIOTERAPIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_FISIOTERAPIS];?></option>
                <option value="<?php echo STS_JAB_ANESTESIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANESTESIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANESTESIS];?></option>
                </select>
              </td>
			    </tr>
			    <tr>
			        <td colspan="2" align="right">
					     <input type="submit" name="btnUpdate" value="Simpan" class="submit" onclick="javascript:return CheckDataSave(this.form);"/>
						    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",true,"onClick=\"document.location.href='".$back."';\"");?> 
			        </td>
			    </tr>
			</table>
  </fieldset>
  </td>
</tr>
</table> 


<script>document.frmEdit.rol_name.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="rol_id" value="<?php echo $rolId?>" />
<input type="hidden" name="id_dep" value="<?php echo $_POST["dep_id"];?>">
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>">
<? } ?>

</form>

<?php } ?>


<?php if($_x_mode == "New") { ?>

<form name="frmEdit" method="POST" action="<?php echo $newPage;?>">
<table width="80%" border="0" cellpadding="1" cellspacing="1">
<tr>
    <td>
    <fieldset>
    <legend><strong>Edit Jabatan</strong></legend>
			<table width="100%" border="0" cellpadding="1" cellspacing="1">
			   <tr>
			        <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Nama Jabatan :&nbsp;&nbsp;</strong></td>
			        <td width="70%"><input onKeyDown="return tabOnEnter(this, event);" type="text" name="rol_name" size="50" maxlength="100" value="<?php echo $_POST["rol_name"];?>"/></td>
			    </tr>
			    <tr>
			        <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Jabatan :&nbsp;&nbsp;</strong></td>
			        <td width="70%">
              <select name="rol_jabatan" id="rol_jabatan" onKeyDown="return tabOnEnter(this, event);">								
                <option value="<?php echo STS_JAB_DOKTER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_DOKTER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_DOKTER];?></option>
                <option value="<?php echo STS_JAB_PPDS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PPDS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PPDS];?></option>
                <option value="<?php echo STS_JAB_PERAWAT;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PERAWAT) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PERAWAT];?></option>
                <option value="<?php echo STS_JAB_STAFF;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_STAFF) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_STAFF];?></option>
                <option value="<?php echo STS_JAB_ANALIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANALIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANALIS];?></option>
                <option value="<?php echo STS_JAB_RADIOGRAFER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_RADIOGRAFER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_RADIOGRAFER];?></option>
                <option value="<?php echo STS_JAB_FISIOTERAPIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_FISIOTERAPIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_FISIOTERAPIS];?></option>
                <option value="<?php echo STS_JAB_ANESTESIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANESTESIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANESTESIS];?></option>
                </select>
              </td>
			    </tr>
			    <tr>
			        <td colspan="2" align="right">
					  <input type="submit" class="submit" name="btnSave" value="Simpan" onclick="javascript:return CheckDataSave(this.form);"/>
												<?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='role_view.php';\"");?> 
			        </td>
			    </tr>
			</table> 
  </fieldset>
  </td>
</tr>
</table>
<script>document.frmEdit.rol_name.focus();</script>


<input type="hidden" name="id_dep" value="<?php echo $_POST["klinik"];?>">
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>">

</form>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>&nbsp;Hint&nbsp;:&nbsp;Role Name Sudah Ada</strong></font>
<? } ?> 
<?php } ?>
</body>
</html>

</div>
		 </div>
		 
<?
    $dtaccess->Close();
?>