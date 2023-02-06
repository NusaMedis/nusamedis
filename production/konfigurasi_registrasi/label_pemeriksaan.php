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
     $viewPage = "label_pemeriksaan.php";
     
     
      // Untuk update flag Gigi
      if ($_GET["ganti_aktif_pemeriksaan"]) $ganti_aktif_pemeriksaan = $_GET["ganti_aktif_pemeriksaan"];
      if ($_GET["ganti_noaktif_pemeriksaan"]) $ganti_no_naktif_pemeriksaan = $_GET["ganti_noaktif_pemeriksaan"];
      if ($_GET["label_id"]) $label_id = $_GET["label_id"];
      // jika data label pemriksaan di aktifkan //
      if ($ganti_aktif_pemeriksaan && $label_id) {
    
           $sql = "update global.global_label_pemeriksaan set label_pemeriksaan_flag = 'y' where label_pemeriksaan_id = ".QuoteValue(DPE_CHAR,$label_id);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);

           header("location:".$viewPage);
           exit();    
      }
      // jika data label pemeriksaan di non aktifkan //
      if ($ganti_no_naktif_pemeriksaan && $label_id) {
    
           $sql = "update global.global_label_pemeriksaan set label_pemeriksaan_flag = 'n' where label_pemeriksaan_id = ".QuoteValue(DPE_CHAR,$label_id);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);

           header("location:".$viewPage);
           exit();    
     }
     
     // jika data di update // 
     if ($_GET["id"]) {
               
               $_x_mode = "Edit";
               $labelPemeriksaanId = $_GET["id"];
               
          // buat nampilin data yg sudah di simpan di database
          $sql = "select * from global.global_label_pemeriksaan where label_pemeriksaan_id = ".QuoteValue(DPE_CHAR,$labelPemeriksaanId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["label_pemeriksaan_nama"] = $row_edit["label_pemeriksaan_nama"];
          $_POST["label_pemeriksaan_id"] = $row_edit["label_pemeriksaan_id"];
          $_POST["label_pemeriksaan_tipe"] = $row_edit["label_pemeriksaan_tipe"];
     }
     
       
     // buat insert atau update data
     if ($_POST["btnSimpanPemriksaan"] || $_POST["btnSaveLabel"]) {   
               $periksaId = & $_POST["label_pemeriksaan_id"];
               $_x_mode = "Edit";     
  
               $dbTable = "global.global_label_pemeriksaan";
               
               $dbField[0] = "label_pemeriksaan_id";   // PK
               $dbField[1] = "label_pemeriksaan_nama"; 
               $dbField[2] = "label_pemeriksaan_tipe";
               $dbField[3] = "id_dep";
               if($_POST["btnSaveLabel"]){
               $dbField[4] = "label_pemeriksaan_flag";
               }
			
               if(!$periksaId) $periksaId = $dtaccess->GetNewID("global.global_label_pemeriksaan","label_pemeriksaan_id");  
               $dbValue[0] = QuoteValue(DPE_CHAR,$periksaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["label_pemeriksaan_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["label_pemeriksaan_tipe"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$depId); 
               if($_POST["btnSaveLabel"]){
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["label_pemeriksaan_flag"]);
               }
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               if($_POST["btnSaveLabel"]){
               $dtmodel->Insert() or die("insert  error");
               } else { 
               $dtmodel->Update() or die("update  error");
               }	
               
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage);
                  exit();
          
     }
     
     // Cari Data Label Pemeriksaan //
     $sql = "select a.* from global.global_label_pemeriksaan a order by a.label_pemeriksaan_id asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);             
     $dataPemeriksaan = $dtaccess->FetchAll($rs);
     
?>

<script language="javascript" type="text/javascript">

    function CheckTindakan(frm)
    { 
        if(!frm.label_pemeriksaan_nama.value){
    		alert('Nama Label Harus Diisi');
    		frm.label_pemeriksaan_nama.focus();
        return false;
    	}
         	return true;      
    }

</script>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

     <table width="100%" border="0" style="font-size:11px; font-family:sans-serif; font-weight:bold;">
          <!--<tr>
          <?php //if(!$_POST["btnAdd"]) { ?>
          <td align="right" colspan="6"><input type="submit" name="btnAdd" id="btnAdd" value="Tambah" class="submit" /></td>
          <?php //} ?>
          </tr>-->
          
          <tr class="tablesmallheader">
          <td width="5%">Edit</td>
          <td width="3%" align="center">No</td>
          <td width="55%">Nama Label Pemeriksaan</td>
          <td width="15%">Tipe Inputan</td>
          <td width="15%">Status</td>
          <td width="20%" align="center">Rubah Status</td>
          </tr>
          
          <?php //if($_POST["btnAdd"]) { ?>
          <!--<tr>
          <td width="55%" colspan="3"><?php echo $view->RenderTextBox("label_pemeriksaan_nama","label_pemeriksaan_nama","30","255",$_POST["label_pemeriksaan_nama"],"inputField", null,false);?></td>
          <td width="15%">
            <select id="label_pemeriksaan_tipe" name="label_pemeriksaan_tipe" onKeyDown="return tabOnEnter(this,event)">
              <option value="box" <?php if($_POST["label_pemeriksaan_tipe"]=='box') echo "selected"; ?>>Textbox</option>
              <option value="area" <?php if($_POST["label_pemeriksaan_tipe"]=='area') echo "selected"; ?>>Textarea</option>
            </select>
          </td>
          <td width="5%">
            <select id="label_pemeriksaan_flag" name="label_pemeriksaan_flag" onKeyDown="return tabOnEnter(this,event)">
              <option value="y" <?php if($_POST["label_pemeriksaan_flag"]=='y') echo "selected"; ?>>Aktif</option>
              <option value="n" <?php if($_POST["label_pemeriksaan_flag"]=='n') echo "selected"; ?>>Tidak Aktif</option>
            </select>
          </td>
          <td width="20%" align="center"><input type="submit" name="btnSaveLabel" id="btnSaveLabel" value="Simpan" class="submit" /><?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$viewPage."';\"");?></td>
          </tr>-->
          <?php //} ?>
          
          <?php if($_POST["label_pemeriksaan_nama"]) { ?>
          <tr>
          <td width="55%" colspan="3"><?php echo $view->RenderTextBox("label_pemeriksaan_nama","label_pemeriksaan_nama","30","255",$_POST["label_pemeriksaan_nama"],"inputField", null,false);?></td>
          <td width="15%">
            <select id="label_pemeriksaan_tipe" name="label_pemeriksaan_tipe" onKeyDown="return tabOnEnter(this,event)">
              <option value="box" <?php if($_POST["label_pemeriksaan_tipe"]=='box') echo "selected"; ?>>Textbox</option>
              <option value="area" <?php if($_POST["label_pemeriksaan_tipe"]=='area') echo "selected"; ?>>Textarea</option>
            </select>
          </td>
          <td width="15%"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnSimpanPemriksaan":"btnSimpanPemriksaan","btnSimpanPemriksaan","Simpan","submit",false,"onClick=\"javascript:return CheckTindakan(document.frmEdit);\"");?></td>
          <td width="20%" align="center"><?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$viewPage."';\"");?></td>
          </tr>
          <?php } ?>
          
          <?php for($i=0,$n=count($dataPemeriksaan);$i<$n;$i++){ ?>
          <tr>
          <td width="5%"><?php echo '<a href="'.$viewPage.'?id='.$dataPemeriksaan[$i]["label_pemeriksaan_id"].'"> <img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0" ></a>';?></td>
          <td width="3%" align="center"><?php echo $i+1;?></td>
          <td width="55%"><?php echo $dataPemeriksaan[$i]["label_pemeriksaan_nama"];?></td>
          <td width="15%"><?php if($dataPemeriksaan[$i]["label_pemeriksaan_tipe"]=='box') { echo "Textbox"; } else { echo "Textarea"; } ?></td>
          <?php if($dataPemeriksaan[$i]["label_pemeriksaan_flag"]=="y") { ?>
          <td width="15%">Aktif</td>
          <?php } else { ?>
          <td width="15%">Non Aktif</td>
          <?php } ?>
          
          <?php if($dataPemeriksaan[$i]["label_pemeriksaan_flag"]=="n") { ?>
          <td width="20%" align="center"><?php echo '<a href="'.$viewPage.'?ganti_aktif_pemeriksaan=1&label_id='.$dataPemeriksaan[$i]["label_pemeriksaan_id"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/non_aktif.png" alt="Non Aktif" title="Non Aktif" border="0"></a>';?></td>
          <?php } else { ?>
          <td width="20%" align="center"><?php echo '<a href="'.$viewPage.'?ganti_noaktif_pemeriksaan=1&label_id='.$dataPemeriksaan[$i]["label_pemeriksaan_id"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/aktif.png" alt="Aktif" title="Aktif" border="0"></a>';?></td>
          <?php } ?>
          </tr>
          <?php } ?>
     </table>
     
     <script>document.frmEdit.label_pemeriksaan_nama.focus();</script>
     <input type="hidden" name="label_pemeriksaan_id" value="<?php echo $_POST["label_pemeriksaan_id"];?>" />
</form>