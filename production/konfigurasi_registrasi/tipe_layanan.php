<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
	   require_once($LIB."tampilan.php");	
     
     // INISIALISASY LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();   
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
     $viewPage = "tipe_layanan.php";
     
     
      // Untuk update flag Gigi
      if ($_GET["ganti_aktif_Layanan"]) $ganti_aktif_Layanan = $_GET["ganti_aktif_Layanan"];
      if ($_GET["ganti_noaktif_Layanan"]) $ganti_no_naktif_Layanan = $_GET["ganti_noaktif_Layanan"];
      if ($_GET["layanan_id"]) $Layanan_id = $_GET["layanan_id"];
      // jika data label pemriksaan di aktifkan //
      if ($ganti_aktif_Layanan && $Layanan_id) {
    
           $sql = "update global.global_tipe_biaya set tipe_biaya_aktif = 'y' where tipe_biaya_id = ".QuoteValue(DPE_CHAR,$Layanan_id);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);

           header("location:".$viewPage);
           exit();    
      }
      // jika data label pemeriksaan di non aktifkan //
      if ($_GET['ganti_noaktif_Layanan']) {
    
           $sql = "update global.global_tipe_biaya set tipe_biaya_aktif = 'n' where tipe_biaya_id = ".QuoteValue(DPE_CHAR,$Layanan_id);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
           header("location:".$viewPage);
           exit();    
     }
     
     // jika data di update // 
     if ($_GET["id"]) {
               
               $_x_mode = "Edit";
               $labelLayananId = $_GET["id"];
               
          // buat nampilin data yg sudah di simpan di database
          $sql = "select * from global.global_tipe_biaya where tipe_biaya_id = ".QuoteValue(DPE_CHAR,$labelLayananId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["tipe_biaya_nama"] = $row_edit["tipe_biaya_nama"];
          $_POST["tipe_biaya_id"] = $row_edit["tipe_biaya_id"];
     }
     
       
     // buat insert atau update data
     if ($_POST["btnSimpanLayanan"]) {   
               $layananId = & $_POST["tipe_biaya_id"];
               $_x_mode = "Edit";     
  
               $dbTable = "global.global_tipe_biaya";
               
               $dbField[0] = "tipe_biaya_id";   // PK
               $dbField[1] = "tipe_biaya_nama"; 
               $dbField[2] = "id_dep"; 
			
               if(!$layananId) $layananId = $dtaccess->GetNewID("global.global_tipe_biaya","tipe_biaya_id");  
               $dbValue[0] = QuoteValue(DPE_CHAR,$layananId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["tipe_biaya_nama"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               $dtmodel->Update() or die("update  error");	
               
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage);
                  exit();
          
     }
     
     // Cari Data Label Pemeriksaan //
     $sql = "select a.* from global.global_tipe_biaya a order by a.tipe_biaya_id asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);             
     $dataLayanan = $dtaccess->FetchAll($rs);
     
?>

<script language="javascript" type="text/javascript">

    function CheckLayanan(frm)
    { 
        if(!frm.tipe_biaya_nama.value){
    		alert('Nama Label Harus Diisi');
    		frm.tipe_biaya_nama.focus();
        return false;
    	}
         	return true;      
    }

</script>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

     <table width="100%" border="0" style="font-size:11px; font-family:sans-serif; font-weight:bold;">
          <tr class="tablesmallheader">
          <td width="5%">Edit</td>
          <td width="3%" align="center">No</td>
          <td width="55%">Nama Tipe Layanan</td>
          <td width="15%">Status</td>
          <td width="20%" align="center">Rubah Status</td>
          </tr>
          
          <?php if($_POST["tipe_biaya_nama"]) { ?>
          <tr>
          <td width="55%" colspan="3"><?php echo $view->RenderTextBox("tipe_biaya_nama","tipe_biaya_nama","30","255",$_POST["tipe_biaya_nama"],"inputField", null,false);?></td>
          <td width="15%"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnSimpanLayanan":"btnSimpanLayanan","btnSimpanLayanan","Simpan","submit",false,"onClick=\"javascript:return CheckTindakan(document.frmEdit);\"");?></td>
          <td width="20%" align="center"><?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$viewPage."';\"");?></td>
          </tr>
          <?php } ?>
          
          <?php for($i=0,$n=count($dataLayanan);$i<$n;$i++){ ?>
          <tr>
          <td width="5%"><?php echo '<a href="'.$viewPage.'?id='.$dataLayanan[$i]["tipe_biaya_id"].'"> <img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0" ></a>';?></td>
          <td width="3%" align="center"><?php echo $i+1;?></td>
          <td width="55%"><?php echo $dataLayanan[$i]["tipe_biaya_nama"];?></td>
          <?php if($dataLayanan[$i]["tipe_biaya_aktif"]=="y") { ?>
          <td width="15%">Aktif</td>
          <?php } else { ?>
          <td width="15%">Non Aktif</td>
          <?php } ?>
          
          <?php if($dataLayanan[$i]["tipe_biaya_aktif"]=="y") { ?>
          <td width="20%" align="center"><?php echo '<a href="'.$viewPage.'?&ganti_noaktif_Layanan=1&layanan_id='.$dataLayanan[$i]["tipe_biaya_id"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/aktif.png" alt="Aktif" title="Aktif" border="0"></a>';?></td>
          <?php } else { ?>
          <td width="20%" align="center"><?php echo '<a href="'.$viewPage.'?&ganti_aktif_Layanan=1&layanan_id='.$dataLayanan[$i]["tipe_biaya_id"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/non_aktif.png" alt="Non Aktif" title="Non Aktif" border="0"></a>';?></td>
          <?php } ?>
          </tr>
          <?php } ?>
     </table>
     
     <script>document.frmEdit.tipe_biaya_nama.focus();</script>
     <input type="hidden" name="tipe_biaya_id" value="<?php echo $_POST["tipe_biaya_id"];?>" />
</form>