<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/bit.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/expAJAX.php");
	   require_once($ROOT."lib/tampilan.php");	
	   require_once($ROOT."lib/currency.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
     $depId = $auth->GetDepId();
     
     $userData = $auth->GetUserData();     
     $viewPage = "transfer_stok_view.php";
     $editPage = "transfer_stok_edit.php";
     $transPage = "transfer_stok_detail_view.php";
     $theDep = $auth->GetNamaLogistik();
	
     /*if(!$auth->IsAllowed("transfer_stok",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("transfer_stok",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	

	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["transfer_id"])  $transferId = & $_POST["transfer_id"];
	 if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $transferId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from logistik_transfer_stok a where a.transfer_id = ".QuoteValue(DPE_CHAR,$transferId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["transfer_nomor"] = $row_edit["transfer_nomor"];
          $_POST["transfer_tanggal_permintaan"] = format_date($row_edit["transfer_tanggal_permintaan"]);
          $_POST["transfer_tanggal_keluar"] = format_date($row_edit["transfer_tanggal_keluar"]);
          $_POST["id_asal"] = $row_edit["id_asal"];
          $_POST["id_tujuan"] = $row_edit["id_tujuan"];
          $_POST["transfer_pengirim"] = $row_edit["transfer_pengirim"];
          $_POST["transfer_pengirim_nip"] = $row_edit["transfer_pengirim_nip"];
          
          $_POST["id_pengirim"] = $row_edit["id_pengirim"];
          $_POST["id_penerima"] = $row_edit["id_penerima"];
          
          $_POST["transfer_penerima"] = $row_edit["transfer_penerima"];
          $_POST["transfer_penerima_nip"] = $row_edit["transfer_penerima_nip"];
          $_POST["transfer_sumber_dana"] = $row_edit["transfer_sumber_dana"];
          
          $_POST["transfer_keterangan"] = $row_edit["transfer_keterangan"];
          $_POST["transfer_when_update"] = $row_edit["transfer_when_update"];
          $_POST["transfer_who_update"] = $row_edit["transfer_who_update"];
          $_POST["transfer_flag"] = $row_edit["transfer_flag"];
          $_POST["transfer_no"] = $row_edit["transfer_no"];
          $_POST["klinik"] = $row_edit["id_dep"];
     }
    
    //Jika Baru maka create nomer 
   	if($_x_mode=="New")
     {  
    $sql = "select max(transfer_no) as nomer from logistik.logistik_transfer_stok where id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);            
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataNomer = $dtaccess->Fetch($rs);
        
        $nomer=$dataNomer["nomer"]+1;
        
        $skr = date("Y-m-d");
      
        for($i=0,$n=strlen($nomer);$i<5-$n;$i++) $daftarNomer=$daftarNomer."0";
           $daftarNomer = $daftarNomer.$nomer;
        
        $_POST["transfer_nomor"]=$daftarNomer;
                
        //BigInt
        $sql = "select max(transfer_no) as nomer from logistik.logistik_transfer_stok where id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);            
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $nomer = $dtaccess->Fetch($rs);
        
        $_POST["transfer_no"]=$nomer["nomer"]+1;   
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
               $transferId = & $_POST["transfer_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
               $dbTable = "logistik_transfer_stok";
               
               $dbField[0] = "transfer_id";   // PK
               $dbField[1] = "transfer_nomor";
               $dbField[2] = "transfer_tanggal_permintaan";
               $dbField[3] = "transfer_tanggal_keluar";
               $dbField[4] = "id_asal";
               $dbField[5] = "id_tujuan";
               $dbField[6] = "transfer_pengirim";
               $dbField[7] = "transfer_penerima";
               $dbField[8] = "transfer_keterangan";
               $dbField[9] = "transfer_when_update";
               $dbField[10] = "transfer_who_update";
               $dbField[11] = "transfer_flag";
               $dbField[12] = "transfer_no";
               $dbField[13] = "id_dep";  
               $dbField[14] = "transfer_sumber_dana";     
               $dbField[15] = "transfer_pengirim_nip";
               $dbField[16] = "transfer_penerima_nip";
               $dbField[17] = "id_pengirim";
               $dbField[18] = "id_penerima";
               
               $sql = "select pengirim_nama,pengirim_nip from logistik.logistik_pengirim where pengirim_id =".QuoteValue(DPE_CHAR,$_POST["id_pengirim"]);
               $rs = $dtaccess->Execute($sql);
               $Pengirim = $dtaccess->Fetch($rs);
               
               $sql = "select pengirim_nama,pengirim_nip from logistik.logistik_pengirim where pengirim_id =".QuoteValue(DPE_CHAR,$_POST["id_penerima"]);
               $rs = $dtaccess->Execute($sql);
               $Penerima = $dtaccess->Fetch($rs);
     
               
			
               if(!$transferId) $transferId = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR,$transferId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["transfer_nomor"]);
               $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST["transfer_tanggal_permintaan"]));
               $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["transfer_tanggal_keluar"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$Pengirim["pengirim_nama"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$Penerima["pengirim_nama"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["transfer_keterangan"]);
               $dbValue[9] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[10] = QuoteValue(DPE_CHAR,$userData['name']);
               $dbValue[11] = QuoteValue(DPE_CHAR,"n"); 
               $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["transfer_no"]);
			         $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["transfer_sumber_dana"]);
               $dbValue[15] = QuoteValue(DPE_CHAR,$Pengirim["pengirim_nip"]);
               $dbValue[16] = QuoteValue(DPE_CHAR,$Penerima["pengirim_nip"]);               
               $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["id_pengirim"]);
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["id_penerima"]);
			         
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
                  
            if($_POST["btnSave"]){   
                  header("location:".$transPage."?klinik=".$_POST["klinik"]."&id=".$enc->Encode($transferId));
                  exit();
               }else if($_POST["btnUpdate"]){
                  header("location:".$transPage."?klinik=".$_POST["klinik"]."&id=".$enc->Encode($transferId));
                  exit();
               }
          
          }
     }
     
       if ($_GET["del"]) {
          $transferId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from logistik_transfer_stok where transfer_id = ".QuoteValue(DPE_CHAR,$transferId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    
    
          header("location:".$viewPage);
          exit();    
     }
     
     //-- bikin combo box untuk Asal -- //
     $sql = "select * from logistik.logistik_gudang order by gudang_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataGudangAsal = $dtaccess->FetchAll($rs);
     
     //-- bikin combo box untuk Tujuan --//
     $sql = "select * from logistik.logistik_gudang where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])." and gudang_id =".QuoteValue(DPE_CHAR,$theDep)." order by gudang_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataGudangTujuan = $dtaccess->FetchAll($rs);
     
          $sql = "select dep_nama from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $rs = $dtaccess->Execute($sql);
          $Datakliniks = $dtaccess->Fetch($rs);
          
     $sql = "select * from logistik.logistik_pengirim where id_dep =".QuoteValue(DPE_CHAR,$depId)." and id_gudang= ".QuoteValue(DPE_CHAR,$_POST["id_asal"])." order by pengirim_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataPengirim = $dtaccess->FetchAll($rs);
     
     $sql = "select * from logistik.logistik_pengirim where id_dep =".QuoteValue(DPE_CHAR,$depId)." and id_gudang= ".QuoteValue(DPE_CHAR,$_POST["id_tujuan"])." order by pengirim_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataPenerima = $dtaccess->FetchAll($rs);
   /*  unset($pengirim);$i=1;
     $pengirim[0] = $view->RenderOption("--","[Pilih Pengirim]",$show);
     while($dataPengirim = $dtaccess->Fetch($rs)){
     unset($show);
        if($dataPengirim["pgw_id"] == $_POST["id_pengirim"]) $show="selected";
        $pengirim[$i] = $view->RenderOption($dataPengirim["pgw_id"],$dataPengirim["pgw_nama"],$show);
        $i++;
     }  */
     
          
?>

<?php echo $view->RenderBody("ipad_depans.css",true,"INPUT PENGIRIMAN BARANG"); ?>
<?php echo $view->InitThickBox(); ?>
<script language="javascript" type="text/javascript">

function CheckDataSave(frm)
{ 
     
     if(!frm.transfer_nomor.value){
		alert('No. Transfer Harus Diisi');
		frm.po_nomor.focus();
          return false;
	}
	

     document.frmEdit.submit();     
}

function pengirim(nip) {

      document.getElementById("transfer_pengirim_nip").value = nip;
            
}
</script>
<script type="text/javascript" language="javascript" src="dokter.js"></script>
<script type="text/javascript" language="javascript" src="pengirim.js"></script>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6">INPUT PENGIRIMAN BARANG</font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>DETAIL INPUT PENGIRIMAN BARANG</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Rumah Sakit</strong>&nbsp;</td>
               <td width="70%">&nbsp;&nbsp;<b><?php echo $Datakliniks["dep_nama"];?></b>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nomer</strong>&nbsp;</td>
               <td width="70%" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("transfer_nomor","transfer_nomor","50","100",$_POST["transfer_nomor"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent">&nbsp;Tanggal Permintaan</td>
               <td align="left" width="15%" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("transfer_tanggal_permintaan","transfer_tanggal_permintaan","15","30",$_POST["transfer_tanggal_permintaan"],"inputField", "readonly",null,false);?>
                    <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_permintaan_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">&nbsp;Tanggal Keluar</td>
               <td align="left" width="15%" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("transfer_tanggal_keluar","transfer_tanggal_keluar","15","30",$_POST["transfer_tanggal_keluar"],"inputField", "readonly",null,false);?>
                    <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_keluar_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Asal&nbsp;</td>
          		  <td width="37%" class="tablecontent-odd"><select name="id_asal" id="id_asal" onChange="pengirimdata(id_asal.value)">
                	<?php for($i=0,$n=count($dataGudangAsal);$i<$n;$i++){ ?>
               	<option value="<?php echo $dataGudangAsal[$i]["gudang_id"];?>" <?php if($dataGudangAsal[$i]["gudang_id"]==$_POST["id_asal"]) echo "selected"; ?>><?php echo $dataGudangAsal[$i]["gudang_nama"];?></option>
				<?php } ?>

               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Tujuan&nbsp;</td>
          		  <td width="37%" class="tablecontent-odd"><select name="id_tujuan" id="id_tujuan" onChange="dokter(id_tujuan.value)">
                <option value="--" >- | Pilih Pengirim | -</option>
                	<?php for($i=0,$n=count($dataGudangTujuan);$i<$n;$i++){ ?>
               	<option value="<?php echo $dataGudangTujuan[$i]["gudang_id"];?>" <?php if($dataGudangTujuan[$i]["gudang_id"]==$_POST["id_tujuan"]) echo "selected"; ?>><?php echo $dataGudangTujuan[$i]["gudang_nama"];?></option>
				<?php } ?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Pengirim</strong>&nbsp;</td>	
               <td width="70%" class="tablecontent-odd"><?php if($_POST["id_pengirim"]) { ?>
         <select name="id_pengirim" id="id_pengirim" onKeyDown="return tabOnEnter(this, event);">			
				<?php for($i=0,$n=count($dataPengirim);$i<$n;$i++){ ?>
          <option value="<?php echo $dataPengirim[$i]["pengirim_id"];?>" <?php if($dataPengirim[$i]["pengirim_id"]==$_POST["id_pengirim"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPengirim[$i]["pengirim_nama"]."&nbsp;NIP :&nbsp;".$dataPengirim[$i]["pengirim_nip"];?></option>
				    <?php } ?>
			       </select>
			    <?php } else { ?>     			     
        <div id="pengirim-view"></div>
       <?php } ?>
    	      <a href="<?php echo "pengirim_edit.php"?>?tambah=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=200&width=450&modal=true" class="thickbox" title="Tambah Pengirim"><img src="<?php echo $ROOT;?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Pengirim" alt="Tambah Pengirim" /></a>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Penerima</strong>&nbsp;</td>
               <td width="70%" class="tablecontent-odd"><?php if($_POST["id_penerima"]) { ?>
         <select name="id_penerima" id="id_penerima" onKeyDown="return tabOnEnter(this, event);">			
				<?php for($i=0,$n=count($dataPenerima);$i<$n;$i++){ ?>
          <option value="<?php echo $dataPenerima[$i]["pengirim_id"];?>" <?php if($dataPenerima[$i]["pengirim_id"]==$_POST["id_penerima"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPenerima[$i]["pengirim_nama"]."&nbsp;NIP :&nbsp;".$dataPenerima[$i]["pengirim_nip"];?></option>
				    <?php } ?>
			       </select>
			    <?php } else { ?>     			     
        <div id="dokter-view"></div>
       <?php } ?>
  	      <a href="<?php echo "pengirim_edit.php"?>?tambah=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=200&width=450&modal=true" class="thickbox" title="Tambah Penerima"><img src="<?php echo $ROOT;?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Penerima" alt="Tambah Penerima" /></a>
          </td>
          </tr>
<!--          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Sumber Dana</strong>&nbsp;</td>
               <td width="70%" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("transfer_sumber_dana","transfer_sumber_dana","50","100",$_POST["transfer_sumber_dana"],"inputField", null,false);?>
               </td>
          </tr>
-->
          <tr>
               <td align="right" class="tablecontent" width="20%">Keterangan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("transfer_keterangan","transfer_keterangan","3","30",$_POST["transfer_keterangan"],"inputField", null,false);?></td>
              </tr> 
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='transfer_stok_view.php?klinik=".$_POST["klinik"]."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.transfer_nomor.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("transfer_id","transfer_id",$transferId);?>
<?php echo $view->RenderHidden("klinik","klinik",$_POST["klinik"]);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
<?php echo $view->RenderHidden("transfer_no","transfer_no",$_POST["transfer_no"]);?>
<?php echo $view->RenderHidden("klinik","klinik",$_POST["klinik"]);?>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "transfer_tanggal_permintaan",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_permintaan_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "transfer_tanggal_keluar",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_keluar_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
</form>
</div>
</div>

<?php echo $view->RenderBodyEnd(); ?>
