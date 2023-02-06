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
	

   
	if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
	if($_GET["prop"]) $_POST["lokasi_propinsi"] = $_GET["prop"];
	if($_GET["kab"]) $_POST["lokasi_kabupatenkota"] = $_GET["kab"];
	if($_GET["kec"]) $_POST["lokasi_kecamatan"] = $_GET["kec"];
      
      $thisPage = "kelurahan_edit.php?klinik=".$_POST["klinik"];
      $editPage = "kelurahan_edit.php";

 
     if ($_GET["id"]) {     	
     	
           $kelId = $enc->Decode($_GET["id"]);
          
          //$_POST["klinik"] = $_GET["klinik"];
          $sql = "select * from global.global_lokasi where lokasi_id = ".QuoteValue(DPE_CHAR,$kelId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
       //   echo $sql;
          $_POST["lokasi_id"] = $row_edit["lokasi_id"];
          $_POST["lokasi_kode"] = $row_edit["lokasi_kode"];
          $_POST["lokasi_nama"] = $row_edit["lokasi_nama"];
          $_POST["lokasi_propinsi"] = $row_edit["lokasi_propinsi"];
          $_POST["lokasi_kabupatenkota"] = $row_edit["lokasi_kabupatenkota"];
          $_POST["lokasi_kecamatan"] = $row_edit["lokasi_kecamatan"];
          $_POST["lokasi_kelurahan"] = $row_edit["lokasi_kelurahan"];          
          
          $back = "kecamatan_view.php?klinik=".$_POST["klinik"]; 

     }
            if($_GET["new"]){
           
             $sql = "select cast(lokasi_kelurahan as integer) as kodekab  from global.global_lokasi where lokasi_propinsi ='".$_POST["lokasi_propinsi"]."' and lokasi_kabupatenkota ='".$_POST["lokasi_kabupatenkota"]."'
                     and lokasi_kecamatan = '".$_GET["kec"]."' and lokasi_kelurahan <>'0000' order by lokasi_kelurahan desc";
             $rs = $dtaccess->Execute($sql);
             $lokasiKecTerakhir = $dtaccess->Fetch($rs); 
           //  echo $sql;
              $sql = "select * from global.global_lokasi order by lokasi_id desc";
              $rs = $dtaccess->Execute($sql);
              $lokasiId = $dtaccess->Fetch($rs);
                    
               $lokasiIdNew = $lokasiId["lokasi_id"]+1;
                $lokLurId = str_pad($lokasiKecTerakhir["kodekab"]+1,4,"0",STR_PAD_LEFT);                
               $kode = $_POST["lokasi_propinsi"].".".$_POST["lokasi_kabupatenkota"].".".$_GET["kec"].".".$lokLurId; 
               
               $_POST["lokasi_kode"]=$kode;      
       }
     if ($_POST["btnUpdate"] || $_POST["btnSave"] ) { 
             
              $sql = "select * from global.global_lokasi order by lokasi_id desc";
              $rs = $dtaccess->Execute($sql);
              $lokasiId = $dtaccess->Fetch($rs);
                    
               $lokasiIdNew = $lokasiId["lokasi_id"]+1;

               $kelId = & $_POST["lokasi_id"];
               $propId = $_POST["lokasi_propinsi"];
               $kabId = $_POST["lokasi_kabupatenkota"];
                $kecId = $_POST["lokasi_kecamatan"];

             $sql = "select cast(lokasi_kelurahan as integer) as kodekab  from global.global_lokasi where lokasi_propinsi ='".$propId."' and lokasi_kabupatenkota ='".$kabId."'
                     and lokasi_kecamatan = '".$kecId."' and lokasi_kelurahan <>'0000' order by lokasi_kelurahan desc";
             $rs = $dtaccess->Execute($sql);
             $lokasiKecTerakhir = $dtaccess->Fetch($rs); 

             $loklurbaru = $lokasiKecTerakhir["kodekab"]+1;
                                  
              $sql = "select lokasi_id from global.global_lokasi where lokasi_propinsi = '".$propId."' and lokasi_kabupatenkota='".$kabId."'
              and lokasi_kecamatan ='".$kecId."' and lokasi_kelurahan ='0000'";
              $rs = $dtaccess->Execute($sql);
              $dataKecamatanBack = $dtaccess->Fetch($rs);               
                                
               $dbTable = "global.global_lokasi";
               
               $dbField[0] = "lokasi_id";   // PK
               $dbField[1] = "lokasi_kode"; 
               $dbField[2] = "lokasi_nama";
              if($_POST["btnSave"]){
               $dbField[3] = "lokasi_propinsi";
               $dbField[4] = "lokasi_kabupatenkota";
               $dbField[5] = "lokasi_kecamatan";
               $dbField[6] = "lokasi_kelurahan";                             
              }
               
               if(!$kelId) $kelId=$lokasiIdNew;                                  
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$kelId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["lokasi_kode"]);               
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["lokasi_nama"]);
              if($_POST["btnSave"]){
               $dbValue[3] = QuoteValue(DPE_CHAR,$propId);
               $dbValue[4] = QuoteValue(DPE_CHAR,$kabId);
               $dbValue[5] = QuoteValue(DPE_CHAR,$kecId);
               $dbValue[6] = QuoteValue(DPE_CHAR,$loklurbaru);                              
              }
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              // print_r($dbValue); die();

                if($_POST["btnSave"]){  
                $dtmodel->Insert() or die("insert  error");	
                 }else{
                $dtmodel->Update() or die("update  error");               
                 }
               
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey); 
                  
                $backpage = "kecamatan_detail.php?id=".$enc->Encode($dataKecamatanBack["lokasi_id"])."&prop=".$propId."&kab=".$kabId."&kec=".$kecId;
                header("location:".$backpage);
                exit();
                
     }
                                         
     
		// Nama Puskesmas
    $sql = "select dep_nama from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataPuskesmas = $dtaccess->Fetch($rs); 

    $sql = "select lokasi_nama from global.global_lokasi where lokasi_propinsi = '".$_GET["prop"]."' and lokasi_kabupatenkota='00'";
    $rs = $dtaccess->Execute($sql);
    $dataPropinsi = $dtaccess->Fetch($rs); 
    
    $sql = "select lokasi_nama from global.global_lokasi where lokasi_propinsi = '".$_GET["prop"]."' and lokasi_kabupatenkota='".$_GET["kab"]."'
            and lokasi_kecamatan='00'";
    $rs = $dtaccess->Execute($sql);
    $dataKabupaten = $dtaccess->Fetch($rs);               

    $sql = "select * from global.global_lokasi where lokasi_propinsi = '".$_GET["prop"]."' and lokasi_kabupatenkota='".$_GET["kab"]."'
            and lokasi_kecamatan='".$_GET["kec"]."' and lokasi_kelurahan ='0000'";
    $rs = $dtaccess->Execute($sql);
    $dataKecamatan = $dtaccess->Fetch($rs);               
 //  echo $sql;
      $viewpage = "kecamatan_detail.php?id=".$enc->Encode($dataKecamatan["lokasi_id"])."&kec=".$_GET["kec"]."&prop=".$_GET["prop"]."&kab=".$_GET["kab"];
?>
<?php //echo $view->RenderBody("module.css",true,false,"MASTER KABUPATEN/KOTA"); ?>
<br /><br /><br /><br />
<script language="javascript" type="text/javascript">

function CheckDataSave(frm) {
     
     if(!frm.lokasi_nama.value){
		alert('Nama Propinsi Harus Diisi');
		frm.lokasi_nama.focus();
          return false;
	} 
  
  if (!frm.lokasi_kode.value){
				  	alert('Kode Propinsi Harus Diisi');
					frm.lokasi_kode.focus();
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
     <legend><strong>MASTER KABUPATEN / KOTA</strong></legend>
	<table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
			<td width= "18%" align="left" class="tablecontent">Propinsi</td>
		  <td width= "45%" align="left" class="tablecontent-odd">
<b><? echo $dataPropinsi["lokasi_nama"]; ?><b>
		</td> 
	</tr>
    <tr>
			<td width= "18%" align="left" class="tablecontent">Kabupaten</td>
		  <td width= "45%" align="left" class="tablecontent-odd">
<b><? echo $dataKabupaten["lokasi_nama"]; ?><b>
		</td> 
	</tr>
    <tr>
			<td width= "18%" align="left" class="tablecontent">Kecamatan</td>
		  <td width= "45%" align="left" class="tablecontent-odd">
<b><? echo $dataKecamatan["lokasi_nama"]; ?><b>
		</td> 
	</tr>  
    <tr>
			<td width= "18%" align="left" class="tablecontent">Kode</td>
		  <td width= "45%" align="left" class="tablecontent-odd">
      <input  readonly type="text" name="lokasi_kode" id="lokasi_kode" size="30" maxlength="50" value="<?php echo $_POST["lokasi_kode"];?>" onKeyDown="return tabOnEnter(this, event);"/>
      <font color="red">*</font>
		</td> 
	</tr>

<tr>
		<td width= "18%" align="left" class="tablecontent">Nama Kelurahan</td>
		<td width= "45%" align="left" class="tablecontent-odd">
			<input  type="text" name="lokasi_nama" id="lokasi_nama" size="30" maxlength="50" value="<?php echo $_POST["lokasi_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
			<input  type="hidden" name="lokasi_id" id="lokasi_id" size="30" maxlength="50" value="<?php echo $_POST["lokasi_id"];?>" onKeyDown="return tabOnEnter(this, event);"/>
			<input  type="hidden" name="lokasi_propinsi" id="lokasi_propinsi" size="30" maxlength="50" value="<?php echo $_POST["lokasi_propinsi"];?>" onKeyDown="return tabOnEnter(this, event);"/>
			<input  type="hidden" name="lokasi_kabupatenkota" id="lokasi_kabupatenkota" size="30" maxlength="50" value="<?php echo $_POST["lokasi_kabupatenkota"];?>" onKeyDown="return tabOnEnter(this, event);"/>
			<input  type="hidden" name="lokasi_kecamatan" id="lokasi_kecamatan" size="30" maxlength="50" value="<?php echo $_POST["lokasi_kecamatan"];?>" onKeyDown="return tabOnEnter(this, event);"/>
	</tr>
  
     <tr>
		<td colspan="3" align="center" class="tablecontent-odd">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="3" align="center" class="tableheader">
               <?php if($_GET["new"]){?>
               <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CheckDataSave(document.frmEdit);" />
                <?}else{?>
               <input type="submit" name="btnUpdate" id="btnUpdate" value="Simpan" class="submit" onClick="javascript:return CheckDataSave(document.frmEdit);" />
               <?}?>
               <input type="button" name="btnBack" id="btnBack" value="Batal" class="submit" onClick="return document.location.href='<?php echo $viewpage; ?>'" />
          </td>
    </tr>
</table>
</table>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.kecamatan_kode.focus();</script>
<?php echo $view->RenderHidden("lokasi_id","lokasi_id",$_POST["lokasi_id"]);?>
</form>
</div>
</div>

<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php// echo $view->RenderBodyEnd(); ?>
