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
	   $usrId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userData = $auth->GetUserData();                                          
     $userName = $auth->GetUserName();
	
	   $plx = new expAJAX("CheckData");

    /* if(!$auth->IsAllowed("inv_setup_kat_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("inv_setup_kat_barang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     if($_GET["klinik"]) { 
          $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
          $_POST["klinik"] = $_POST["klinik"]; 
      }
      $klinik = $_POST["klinik"]; 
 	   
	   $lokasi = $ROOT."gambar/item";
	   //echo $lokasi;
	function CheckData($grupmenuNama,$grupmenuId=null)//nanti ae
	{
          global $dtaccess,$klinik;
          
          $sql = "SELECT grup_item_id FROM logistik.logistik_grup_item a 
                  WHERE upper(a.grup_item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($grupmenuNama))." and id_dep = ".QuoteValue(DPE_CHAR,$klinik);
                    
          if ($grupmenuId) $sql .= " and a.grup_item_id <> ".QuoteValue(DPE_CHAR,$grupmenuId);
          
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataAdamenu = $dtaccess->Fetch($rs);
        
		return $dataAdamenu["grup_item_id"];
     }
     
       function GetCombo() {
          global $dtaccess, $userData,$lokasi,$view,$klinik;
          $sql = "select * from logistik.logistik_grup_item where id_dep=".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataKatItem = $dtaccess->FetchAll($rs);
          $opt_kat[0] = $view->RenderOption("--","[Pilih Kategori]",$show);
          for($i=1,$n=count($dataKatItem);$i<=$n;$i++)
          { 
            unset($show);
            if($dataKatItem[$i-1]["grup_item_id"] == $_POST["id_kategori"]) $show="selected";
            $opt_kat[$i] = $view->RenderOption($dataKatItem[$i-1]["grup_item_id"],$dataKatItem[$i-1]["grup_item_nama"],$show);
          }
          
          return $view->RenderComboBox("id_kategori","id_kategori",$opt_kat,"inputField");
     }
  
     //ambil data outlet dan data gudang
	   $sql = "select * from logistik.logistik_konfigurasi where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $konfigurasi = $dtaccess->Fetch($rs_edit);
    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["grup_item_id"])  $grupmenuId = & $_POST["grup_item_id"];
  
     $backPage = "grup_item_view.php?";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $grupmenuId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from logistik.logistik_grup_item where grup_item_id = ".QuoteValue(DPE_CHAR,$grupmenuId);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs);
          $dtaccess->Clear($rs_edit);
          
          $_POST["grup_item_nama"] = $row_edit["grup_item_nama"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $_POST["grup_pic"] = $row_edit["grup_pic"];
          $fotoName = $lokasi."/".$row_edit["grup_pic"];
          $kembali = "grup_item_view.php?kembali=".$_POST["klinik"];

     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
      if ($_GET["tambah"]) {
        $_POST["klinik"] = $_GET["tambah"]; 
        $kembali = "grup_item_view.php?kembali=".$_POST["klinik"];
     }
    
     //ambil get
    if ($_GET["transaksi"]) $penjualanId=$_GET["transaksi"]; 
    if ($_POST["penjualanId"]) $penjualanId=$_POST["penjualanId"];
   
     echo "penjualan id".$_GET["transaksi"];
     
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          $dateSekarang = date('Y-m-d H:i:s');
          
          $dbTable = "apotik.apotik_penjualan_detail";
          $dbField[0]  = "penjualan_detail_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "penjualan_detail_harga_jual";
          $dbField[4]  = "penjualan_detail_jumlah";
          $dbField[5]  = "penjualan_detail_total";
          $dbField[6]  = "penjualan_detail_flag";
          $dbField[7]  = "penjualan_detail_create";
          $dbField[8]  = "id_petunjuk";
          $dbField[9]  = "id_dep";
          $dbField[10]  = "penjualan_detail_sisa";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "penjualan_detail_tuslag";
          
          if (!$_POST["btn_edit"])         //jika tombol edit di klik
               $penjualanDetailId = $dtaccess->GetTransID();
          else
               $penjualanDetailId = $_POST["btn_edit"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_POST["btn_edit"])
            $dtmodel->Update() or die("insert  error");
          else
            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["btnSave"]);
          unset($_POST["obat_id"]);
          unset($_POST["obat_kode"]);
          unset($_POST["obat_nama"]);
          unset($_POST["txtTuslag"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaTotal"]);
          unset($_POST["txtDibayar"]);
          unset($_POST["txtBalik"]);
          unset($_POST["txtBack"]);
          unset($_POST["txtDiskon"]);
        
  
     }
     
?>
<?php //echo $view->RenderBody("module.css",true,true,"EDIT KATEGORI"); ?>
<br /><br /><br /><br /> 

<?php // //echo $view->InitUpload(); ?>
<script language="javascript" type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'grup_item_pic.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
						
                                   document.getElementById('grup_pic').value= data.file;
                                   document.img_grup_item.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     	
	if(!frm.grup_item_nama.value){
		alert('Nama Kategori menu Harus Diisi');
		frm.grup_item_nama.focus();
          return false;
	}		
	return true;
          
}

<?php if($simpan==1){ ?>
     self.parent.getCombo();
     self.parent.tb_remove();
     
     <?php } ?>
</script>
<body>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="80%" border="0" cellpadding="1" cellspacing="1">
<tr>
  <td>&nbsp;</td>
</tr>
<tr>
  <td>&nbsp;</td>
</tr>
<tr>
     <td>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("grup_item_nama","grup_item_nama","60","100",$_POST["grup_item_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
         
          <tr>
               <td align="right" class="tablecontent"><strong>Gambar</strong>&nbsp;</td>
               <td>
                    <img hspace="2" width="100" height="100" name="img_grup_item" id="img_grup_item" src="<?php echo $fotoName;?>" valign="middle" border="1">
                    <input type="hidden" name="grup_pic" id="grup_pic" value="<?php echo $_POST["grup_pic"];?>">
                    <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="inputField">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar</button>
                    <span id="loading" style="display:none;"><img width="25" height="25"  id="imgloading" src="<?php echo $ROOT;?>gambar/loading.gif"></span>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"self.parent.tb_remove();\"");?>                    
               </td>
          </tr>
     </table>
     </td>
</tr>
</table>
 <input type="hidden" name="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.grup_item_nama.focus();</script>
<?php echo $view->RenderHidden("grup_item_id","grup_item_id",$grupmenuId);?>
<?php echo $view->RenderHidden("penjualanId","penjualanId",$penjualanId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>
 <?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?> 
<?php //echo $view->RenderBodyEnd(); ?>
