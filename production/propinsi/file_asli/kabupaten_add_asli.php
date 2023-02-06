<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
	   require_once($LIB."tampilan.php");	
     require_once($LIB."tree.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();     
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   //echo $depId;
     $tree = new CTree("global.global_sender_umum","sender_umum_id", TREE_LENGTH_CHILD);
     
    /* if(!$auth->IsAllowed("man_medis_kecamatan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kecamatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	

      $idProp = $_GET["prop"];  
	if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
 
      $thisPage = "tambah_prop.php?klinik=".$_POST["klinik"];
      $editPage = "propinsi_edit.php";


     if ($_POST["btnSave"]) { 

     $_POST["lokasi_propinsi"] = & $_POST["lokasi_propinsi"];
     
     $sql = "select cast(lokasi_kabupatenkota as integer) as kodekab  from global.global_lokasi where lokasi_propinsi ='".$_POST["lokasi_propinsi"]."' and lokasi_kabupatenkota <>'00'
             and lokasi_kecamatan ='00' and lokasi_kelurahan ='0000' order by lokasi_kabupatenkota desc";
     $rs = $dtaccess->Execute($sql);
     $lokasiKabTerakhir = $dtaccess->Fetch($rs); 
   //   echo $sql;
      $sql = "select * from global.global_lokasi order by lokasi_id desc";
      $rs = $dtaccess->Execute($sql);
      $lokasiId = $dtaccess->Fetch($rs);
                    
               $lokasiIdNew = $lokasiId["lokasi_id"]+1;
                $kabId = str_pad($lokasiKabTerakhir["kodekab"]+1,2,"0",STR_PAD_LEFT); 
                               
               $kode = $_POST["lokasi_propinsi"].".".$kabId.".00.0000";
               
               $dbTable = "global.global_lokasi";
               
               $dbField[0] = "lokasi_id";   // PK
               $dbField[1] = "lokasi_kode"; 
               $dbField[2] = "lokasi_nama";
               $dbField[3] = "lokasi_propinsi";              
               $dbField[4] = "lokasi_kabupatenkota";              
               $dbField[5] = "lokasi_kecamatan";              
               $dbField[6] = "lokasi_kelurahan";              
                                                 
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$lokasiIdNew);
               $dbValue[1] = QuoteValue(DPE_CHAR,$kode); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["lokasi_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["lokasi_propinsi"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$kabId);
               $dbValue[5] = QuoteValue(DPE_CHAR,'00');
               $dbValue[6] = QuoteValue(DPE_CHAR,'0000');

               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
             //  print_r($dbValue); die();

                    $dtmodel->Insert() or die("insert  error");	
               
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey); 

    $sql = "select * from global.global_lokasi where lokasi_propinsi = ".QuoteValue(DPE_CHAR,$_POST["lokasi_propinsi"])."
     and lokasi_kabupatenkota ='00'";
    $rs = $dtaccess->Execute($sql);
    $dataPropinsi = $dtaccess->Fetch($rs);
                 
                $back = "propinsi_detail.php?id=".$enc->Encode($dataPropinsi["lokasi_id"])."&prop=".$_POST["lokasi_propinsi"];
                header("location:".$back);
                exit();
                
     }
                                         
     
		// Nama Puskesmas
    $sql = "select dep_nama from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataPuskesmas = $dtaccess->Fetch($rs); 
    
     $sql = "select * from global.global_lokasi where lokasi_propinsi = ".QuoteValue(DPE_CHAR,$_GET["prop"])."
     and lokasi_kabupatenkota ='00'";
    $rs = $dtaccess->Execute($sql);
    $dataPropinsi = $dtaccess->Fetch($rs);
//    echo $sql;
      $viewpage = "propinsi_detail.php?id=".$enc->Encode($dataPropinsi["lokasi_id"])."&prop=".$idProp;
         
?>
<?php //echo $view->RenderBody("module.css",true,false,"MASTER KABUPATEN"); ?>
<br /><br /><br /><br />
<script language="javascript" type="text/javascript">

function CheckDataSave(frm) {
     
     if(!frm.lokasi_nama.value){
		alert('Nama Propinsi Harus Diisi');
		frm.lokasi_nama.focus();
          return false;
	} 
  
	
	/*else if (frm.kecamatan_kode.value>100){
				  	alert('Kode Kecamatan Harus Dibawah 100');
					frm.kecamatan_kode.focus();
						return false;
	}*/

  	return true;      
}

function Kembali() {

    document.location.href='<?php echo $viewpage;?>';
} 


function setParent(parent){
if(parent == "--"){
document.getElementById('get_parent').value='n';}
else{document.getElementById('get_parent').value='y';}
}
</script>

<div id="body">
<div id="scroller">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>MASTER KABUPATEN</strong></legend>
	<table width="100%" border="0" cellpadding="1" cellspacing="1">

<tr>
		<td width= "18%" align="left" class="tablecontent">Nama Propinsi</td>
		<td width= "45%" align="left" class="tablecontent-odd">
     <? echo $dataPropinsi["lokasi_nama"];?>
		</td>
	</tr>
  
<tr>
		<td width= "18%" align="left" class="tablecontent">Nama Kabupaten</td>
		<td width= "45%" align="left" class="tablecontent-odd">
			<input  type="text" name="lokasi_nama" id="lokasi_nama" size="30" maxlength="50" value="<?php echo $_POST["lokasi_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
	</tr>
  
     <tr>
		<td colspan="3" align="center" class="tablecontent-odd">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="3" align="center" class="tableheader">
               <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CheckDataSave(document.frmEdit);" />
               <input type="button" name="btnBack" id="btnBack" value="Batal" class="submit" onClick="return document.location.href='<?php echo $viewpage; ?>'" />
          </td>
    </tr>
</table>
</table>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<input type="hidden" name="lokasi_propinsi" id="lokasi_propinsi" value="<?php echo $idProp;?>" />
<script>document.frmEdit.lokasi_nama.focus();</script>
</form>
</div>
</div>

<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd(); ?>
