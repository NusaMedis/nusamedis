<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."expAJAX.php");
	   require_once($LIB."tampilan.php");	
	   require_once($LIB."currency.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userData = $auth->GetUserData();
     $userName = $auth->GetUserName();
     
     $viewPage = "satuan_view.php";
     $editPage = "satuan_edit.php";
	
	   $plx = new expAJAX("CheckDataCustomerTipe,GetComboSatuanJual");
	
    /* if(!$auth->IsAllowed("inv_setup_sat_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("inv_setup_sat_barang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     if($_GET["klinik"]) { 
          $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
          $_POST["klinik"] = $_POST["klinik"]; 
      }
      $klinik = $_POST["klinik"]; 
	
	function CheckDataCustomerTipe($custTipeNama,$satuan)
	{
          global $dtaccess,$klinik;
          
          $sql = "SELECT a.satuan_id FROM logistik.logistik_item_satuan a 
                    WHERE upper(a.satuan_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama))."
                    and satuan_tipe = ".QuoteValue(DPE_CHAR,$satuan)." and id_dep =".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $datasatuan = $dtaccess->Fetch($rs);
          
		return $datasatuan["satuan_id"];
     }
     
  function GetComboSatuanJual() {
          global $dtaccess, $userData,$lokasi,$view,$klinik;
          $sql = "select * from logistik.logistik_item_satuan where satuan_tipe='J' and id_dep=".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataSatuanJual = $dtaccess->FetchAll($rs);
          $opt_satuan[0] = $view->RenderOption("--","[Pilih Satuan Jual]",$show);
          for($i=1,$n=count($dataSatuanJual);$i<=$n;$i++)
          { 
            unset($show);
            if($dataSatuanJual[$i-1]["satuan_id"] == $_POST["id_satuan_jual"]) $show="selected";
            $opt_satuan[$i] = $view->RenderOption($dataSatuanJual[$i-1]["satuan_id"],$dataSatuanJual[$i-1]["satuan_nama"],$show);
          }
          
          return $view->RenderComboBox("id_satuan_jual","id_satuan_jual",$opt_satuan,"inputField");
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["satuan_id"])  $satuanId = & $_POST["satuan_id"];
     

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
       if ($_GET["tambah"]) {
        $_POST["klinik"] = $_GET["tambah"];
        $kembali = "satuan_view.php?kembali=".$_POST["klinik"]; 
        //echo $_POST["klinik"];
     }
     
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $satuanId = & $_POST["satuan_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
               $dbTable = "logistik.logistik_item_satuan";
               
               $dbField[0] = "satuan_id";   // PK
               $dbField[1] = "satuan_nama";
               $dbField[2] = "id_dep";
               $dbField[3] = "satuan_tipe";
               $dbField[4] = "satuan_jumlah";
               
               if(!$satuanId) $satuanId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$satuanId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["satuan_nama"]); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
			         $dbValue[3] = QuoteValue(DPE_CHAR,'J');
			         $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["satuan_jumlah"]));
			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
                 
                $simpan="1"; 
                  
                //$kembali = "satuan_view.php?kembali=".$_POST["klinik"];
               
              // header("location:".$kembali);
              // exit();        
          }
          

     }
     
      // Data Departemen
     $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);

?>

<script language="javascript" type="text/javascript">
<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 

     if(!frm.satuan_tipe.value){
		alert('Tipe Satuan Obat Harus Diisi');
		frm.satuan_tipe.focus();
          return false;
	}
     
     if(!frm.satuan_nama.value){
		alert('Nama Satuan Obat Harus Diisi');
		frm.satuan_nama.focus();
          return false;
	}
	
	     if(!frm.satuan_jumlah.value){
		alert('Jumlah Obat Harus Diisi');
		frm.satuan_jumlah.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.satuan_nama.value,frm.satuan_tipe.value,'type=r')){
			alert('Nama Satuan Obat Sudah Ada');
			frm.satuan_nama.focus();
			frm.satuan_nama.select();
			return false;
		}
	}    
     document.frmEdit.submit();
     
}
<?php if($simpan==1){ ?>
     self.parent.getComboSatuanJual();
     self.parent.tb_remove();
     
     <?php } ?>
</script>
<?php //echo $view->RenderBody("ipad_depans.css",false,"SATUAN OBAT"); ?>
<body>
<br><br><br>
	
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
		 <tr>
			        <td align="right" class="tablecontent" width="30%">&nbsp;&nbsp;Tipe&nbsp;&nbsp;</td>
				      <td class="tablecontent-odd">
               <select name="satuan_tipe" id="satuan_tipe" >
							 <!--<option value="B"  <?php if($_POST["satuan_tipe"]=="B") echo "selected";?>>Beli</option>-->    
							 <option value="J" <?php if($_POST["satuan_tipe"]=="J") echo "selected";?>>Jual</option>
               </select>
               </td>
     </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("satuan_nama","satuan_nama","50","100",$_POST["satuan_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Jumlah</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("satuan_jumlah","satuan_jumlah","50","100",currency_format($_POST["satuan_jumlah"]),"inputField", null,true);?>
               </td>
          </tr> 
          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="submit" /></center>                    
               </td>
          </tr>       
     </table>
     </td>
</tr>
</table>
<input type="hidden" name="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.satuan_nama.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("satuan_id","satuan_id",$satuanId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

