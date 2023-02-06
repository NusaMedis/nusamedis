<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
	   require_once($ROOT."lib/tampilan.php");	
     require_once($ROOT."lib/tree.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   //echo $depId;
     //$tree = new CTree("global.global_sender_umum","sender_umum_id", TREE_LENGTH_CHILD);
     
     if(!$auth->IsAllowed("man_medis_kecamatan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kecamatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["sender_umum_id"])  $senderUmumId = & $_POST["sender_umum_id"];
	if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
 
      $thisPage = "detail_view.php?klinik=".$_POST["klinik"]."&idkec=".$_GET["idkec"];
      $editPage = "detail_edit.php";
 
     if ($_GET["id"]) {
     	
     	
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $kelId = $enc->Decode($_GET["id"]);
          }
          
          $_POST["klinik"] = $_GET["klinik"];
          $sql = "select * from global.global_kelurahan where kel_id = ".QuoteValue(DPE_CHAR,$kelId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          //echo $sql;
          $_POST["kelurahan_nama"] = $row_edit["kel_nama"];
          $_POST["kelurahan_kode"] = $row_edit["kel_kode"];
          $_POST["kelurahan_id"] = $row_edit["kel_id"];
  
          $back = "detail_view.php?klinik=".$_POST["klinik"]."&idkec=".$_GET["idkec"]; 

     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     	 if ($_GET["tambah"]) {
        
        $back = "lucky_friend_view.php?";
     } //else $back = "lucky_friend_view.php";
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $kelId = & $_POST["kelurahan_id"];
               $_x_mode = "Edit";
               }
                  
               $dbTable = "global.global_kelurahan";
               
               $dbField[0] = "kel_id";   // PK
               $dbField[1] = "kel_kode"; 
               $dbField[2] = "kel_nama";
               $dbField[3] = "id_kec";
               $dbField[4] = "id_dep";

					     if(!$kelId) $kelId = $dtaccess->GetTransId();                                                
               $dbValue[0] = QuoteValue(DPE_CHAR,$kelId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kelurahan_kode"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["kelurahan_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$enc->Decode($_POST["id_kec"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey); 
                  
                  $back = "detail_view.php?klinik=".$klinik."&idkec=".$_POST["id_kec"]; 
                  header("location:".$back);
                  exit();
     }
                                         
     if ($_GET["del"]) {
          $kelId = $enc->Decode($_GET["id"]);

               $sql = "delete from global.global_kelurahan where kel_id = ".QuoteValue(DPE_CHAR,$kelId);//[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA);

          $back = "detail_view.php?klinik=".$_GET["klinik"]."&idkec=".$_GET["idkec"]; 
          header("location:".$back);
          exit();    
     }
     
 // --- cari kecamatan ---

     $sql = "select kec_nama from global.global_kecamatan where kec_id =".QuoteValue(DPE_CHAR,$enc->Decode($_GET["idkec"]));
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKec = $dtaccess->Fetch($rs);       
?>

<script language="javascript" type="text/javascript">

function CheckSimpan(frm) {
     
     /*if(!document.getElementById('sender_umum_nama').value) {
         alert('Nama sender harap diisi');
         document.getElementById('sender_umum_nama').focus();
         return false;
     }
     
          if(!document.getElementById('sender_umum_no_rek').value) {
         alert('No rekening  harap diisi');
         document.getElementById('sender_umum_no_rek').focus();
         return false;
     }*/
}

function CheckDataSave(frm){

	if(!frm.kelurahan_kode.value){
		alert('Kode Kelurahan Harus Diisi');
		frm.kelurahan_kode.focus();
			return false;
		}

	 if (!frm.kelurahan_nama.value){
						alert('Nama Kelurahan Harus Diisi');
						frm.kelurahan_nama.focus();
						return false;
		}
    
    return true;
}

function Kembali() {

    document.location.href='<?php echo $thisPage;?>';
} 


function setParent(parent){
if(parent == "--"){
document.getElementById('get_parent').value='n';}
else{document.getElementById('get_parent').value='y';}
}
</script>

<?php echo $view->RenderBody("module.css",true,false,"EDIT KELURAHAN"); ?>
<br /><br />
<div id="body">
<div id="scroller">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>MASTER KELURAHAN</strong></legend>

	<table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
               <td align="left" class="tablesmallheader" width="15%"><strong>Nama Kecamatan</strong>&nbsp;</td>
               <td width="85%" colspan="">
               <?php echo $dataKec["kec_nama"];?>
               </td>
          </tr>     
    <tr>
			<td width= "18%" align="left" class="tablecontent">Kode</td>
		<td width= "45%" align="left" class="tablecontent-odd">
               <input  type="text" name="kelurahan_kode" id="kelurahan_kode" size="30" maxlength="50" value="<?php echo $_POST["kelurahan_kode"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <font color="red">*</font>
               <input type="hidden" name="id_kec" id="id_kec" value="<?php echo $_GET["idkec"];?>">
		</td> 
	</tr>

<tr>
		<td width= "18%" align="left" class="tablecontent">Nama Kelurahan</td>
		<td width= "45%" align="left" class="tablecontent-odd">
			<input  type="text" name="kelurahan_nama" id="kelurahan_nama" size="30" maxlength="50" value="<?php echo $_POST["kelurahan_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td> 
	</tr>
  
     <tr>
		<td colspan="3" align="center" class="tablecontent-odd">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="3" align="center" class="tableheader">
               <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
               <input type="button" name="btnBack" id="btnBack" value="Batal" class="submit" onClick="javascript: Kembali();" />
          </td>
    </tr>
</table>
</table>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.kelurahan_kode.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("kelurahan_id","kelurahan_id",$_POST["kelurahan_id"]);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode","20","60",$_x_mode);?>
</form>
</div>
</div>

<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
