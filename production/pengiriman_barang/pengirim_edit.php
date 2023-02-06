<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userData = $auth->GetUserData();
     $userName = $auth->GetUserName();
     $monthName = array("--","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
     $viewPage = "pengirim_view.php";
     $editPage = "pengirim_edit.php";
	   $findPage = "akun_pengirim_find.php?";
	
	   $plx = new expAJAX("CheckDataCustomerTipe");
	
    /* if(!$auth->IsAllowed("apo_master_pengirim",PRIV_READ)){
          echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_master_pengirim",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     if($_POST["klinik"]) $_POST["klinik"] = $_POST["klinik"]; 
     
      if ($_GET["tambah"]) {
        $_POST["klinik"] = $_GET["tambah"];
        $kembali = "pengirim_view.php?kembali=".$_POST["klinik"]; 
        //echo $_POST["klinik"];
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.pengirim_id FROM logistik.logistik_pengirim a 
                    WHERE upper(a.pengirim_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $datapengirim = $dtaccess->Fetch($rs);
          
		return $datapengirim["pengirim_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["pengirim_id"])  $supId = & $_POST["pengirim_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $supId = $enc->Decode($_GET["id"]);
          }
          $_POST["klinik"] = $_GET["klinik"];
          $sql = "select a.* from logistik.logistik_pengirim a  
				          where a.pengirim_id = ".QuoteValue(DPE_CHAR,$supId)." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["pengirim_nama"] = $row_edit["pengirim_nama"];
          $_POST["pengirim_nip"] = $row_edit["pengirim_nip"];
          


      //     $kembali = "pengirim_view.php?kembali=".$_POST["klinik"];
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
	         
     
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $supId = & $_POST["pengirim_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
               $dbTable = "logistik.logistik_pengirim";
               
               $dbField[0] = "pengirim_id";   // PK
               $dbField[1] = "pengirim_nama";
			         $dbField[2] = "pengirim_nip";
			         $dbField[3] = "id_dep";
			         $dbField[4] = "id_gudang";
               
               //if(!$supId) $supId = $dtaccess->GetNewId("logistik.logistik_pengirim","pengirim_id",DB_SCHEMA);   
               if(!$supId) $supId = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR,$supId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pengirim_nama"]); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pengirim_nip"]);  
			         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
			         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);

			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
         //          $kembali = "pengirim_view.php?kembali=".$_POST["klinik"];
               
        //       header("location:".$kembali);
               exit(); 
          }
     }
 
      if ($_GET["del"]) {
          $supId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from logistik.logistik_pengirim where pengirim_id = ".QuoteValue(DPE_CHAR,$supId);
           $dtaccess->Execute($sql);
          
     //      $kembali = "pengirim_view.php?kembali=".$_POST["klinik"];
               
      //     header("location:".$kembali);
           exit();   
     } 
     
    // -- cari propinsi ---
     $sql = "select * from global.global_propinsi order by prop_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPropinsi = $dtaccess->FetchAll($rs);   
     
    // -- cari kota ---
     $sql = "select a.*,b.* from global.global_kota a 
     join global.global_propinsi b on a.id_prop = b.prop_id order by b.prop_nama, a.kota_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKota = $dtaccess->FetchAll($rs);
     //echo $sql; 
     
      // Data Departemen
     $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);

      // Data Gudang
     $sql = "select * from logistik.logistik_gudang where id_dep like '".$_POST["klinik"]."' order by gudang_id";
     $rs = $dtaccess->Execute($sql);
     $dataGudang = $dtaccess->FetchAll($rs);
        
?> 
<?php echo $view->RenderBody("ipad_depans.css",true,"SETUP PENGIRIM & PENERIMA"); ?>
<?php echo $view->InitThickBox(); ?>
<script language="javascript" type="text/javascript">
<? $plx->Run(); ?>

function SetKotaBeli(selected) 
{
    var inForm = document.frmEdit.id_kota;
    if(selected!="0") var selectedArray = eval("ArrSubKat"+selected);
    
    while (1 < inForm.options.length) {
        inForm.options[(inForm.options.length - 1)] = null;
    }
    
    if(selected!="0") {
        for (var i=0; i < selectedArray.length; i++) {
            eval("inForm.options[i]=" + "new Option" + selectedArray[i]);
        }
    }
} 

function CheckDataSave(frm)
{ 
     
  if(!frm.pengirim_nip.value){
		alert('Kode pengirim Harus Diisi');
		frm.pengirim_nip.focus();
          return false;
	}
  
  if(!frm.pengirim_nama.value){
		alert('Nama pengirim Harus Diisi');
		frm.pengirim_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.pengirim_nama.value,'type=r')){
			alert('Nama pengirim Sudah Ada');
			frm.pengirim_nama.focus();
			frm.pengirim_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
function Tutup() {
self.parent.tb_remove();
	
}

</script>
<body>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6">SETUP PENERIMA</font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">
 <br />
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
      <tr class="tablecontent">
       <td align="right" class="tablecontent" width="15%">&nbsp;Nama Klinik&nbsp;</td>
			 <td class="tablecontent"> 
       <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onchange="rejenis(this.value);">
				<option class="inputField" value="" >- Pilih Klinik -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";
				?>
				<option class="inputField" value="<?php echo $dataKlinik[$i]["dep_id"];?>"<?php if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
		 </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama</strong>&nbsp;</td>
               <td width="80%" colspan="3" >
                 <?php echo $view->RenderTextBox("pengirim_nama","pengirim_nama","30","100",$_POST["pengirim_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>NIP</strong>&nbsp;</td>
               <td colspan="3">
                  <?php echo $view->RenderTextBox("pengirim_nip","pengirim_nip","20","100",$_POST["pengirim_nip"],"inputField", null,false);?>
               </td>
          </tr> 
      <tr class="tablecontent">
       <td align="right" class="tablecontent" width="15%">&nbsp;Nama Departemen&nbsp;</td>
			 <td class="tablecontent"> 
       <select name="id_gudang" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onchange="rejenis(this.value);">
				<option class="inputField" value="" >- Pilih Departemen -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataGudang);$i<$n;$i++){
				?>
				<option class="inputField" value="<?php echo $dataGudang[$i]["gudang_id"];?>"<?php if ($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) echo"selected"?>><?php echo $dataGudang[$i]["gudang_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
		 </tr>

          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Tutup","submit",false,"onClick=\"javascript:return Tutup();\"");?>                    
               </td>
          </tr>
     </table>
     </td>
</tr>
</table>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.pengirim_nip.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("pengirim_id","pengirim_id",$supId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

		 </div>
		 </div>

  		<!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>--> 
			
<?php echo $view->RenderBodyEnd(); ?>
