<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     
     $enc = new textEncrypt();
     $dtaccess = new DataAccess();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $auth = new CAuth();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");

     $roomPage   = "kamar_view.php";
     $editPage   = "bed_edit.php";
     $thisPage   = "bed_view.php";

       $editbed   = "bed_kosongkan.php";
       
       if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("man_ganti_password",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("man_ganti_password",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("man_ganti_password",PRIV_CREATE);
     
     if(!$_POST["btnSearch"]) $cari = 1;
     if($_GET["RoomId"]) $roomId = $enc->Decode($_GET["RoomId"]);
     
     if(isset($_POST["btnSearch"]) || $cari || ($_GET["_search"]=="yes")) {

          // -- search kode bed ---
          //if($_POST["bed_nama"]) $sql_where[] = "a.bed_nama like '".$_POST["bed_nama"]."%'";

          //if($_POST["id_kategori"] && $_POST["id_kategori"]!="") $sql_where[] = "a.id_kategori= ".QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
          

          $sql = "select * from klinik.klinik_kamar_bed where id_kamar=".QuoteValue(DPE_CHAR,$roomId); 
          $sql .= " order by UPPER(bed_kode) asc";
          //echo $sql;
          $rs = $dtaccess->Execute($sql);
          $dataTable = $dtaccess->FetchAll($rs); //echo $sql;
          
     }
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Detail Bed &nbsp;".$_GET["RoomName"];
     
     // --- construct new table ---- //
     $counter=0;
//     if($isAllowedDel){
          $tbHeader[0][$counter][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counter][TABLE_WIDTH] = "1%";
          $counter++;
//     }
     
//     if($isAllowedUpdate){
          
          $tbHeader[0][$counter][TABLE_ISI] = "Edit";
          $tbHeader[0][$counter][TABLE_WIDTH] = "1%"; 
          $counter++;  
//     }

     $tbHeader[0][$counter][TABLE_ISI] = "Nama Bed";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Virtual";
     $tbHeader[0][$counter][TABLE_WIDTH] = "2%";
     $counter++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
//          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["bed_id"]).'&RoomId='.$enc->Encode($roomId).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
//          }
          
//          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["bed_id"]).'&RoomId='.$enc->Encode($roomId).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
//          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          if($dataTable[$i]["bed_keterangan"]=='y'){
          $tbContent[$i][$counter][TABLE_ISI] = "<img hspace='2' width='18' height='18' alt='aktif' title='aktif' border='0' src='".$ROOT."gambar/aktif.png'>";
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "<img hspace='2' width='18' height='18' alt='non aktif' title='non aktif' border='0' src='".$ROOT."gambar/non_aktif.png'>";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
     }

          
//     if($isAllowedDel && !$isAllowedCreate){
          //$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
//     }
//     if($isAllowedCreate && !$isAllowedDel){
          $tbBottom[0][0][TABLE_ISI] = '<input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
//     }
//     if($isAllowedDel && $isAllowedCreate){
          $tbBottom[0][0][TABLE_ISI] = '<a href="'.$editPage.'?RoomId='.$enc->Encode($roomId).'&RoomName='.$_GET["RoomName"].'" style="border:none;text-decoration:none;"><input type="button" name="btnAdd" value="Tambah" class="button" ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
//     }
         
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

     
     if ($_POST["btnDelete"]) {
		$bedId = & $_POST["cbDelete"];
		for($i=0,$n=count($bedId);$i<$n;$i++) {
			$sql = "delete from klinik.klinik_kamar_bed 
                         where bed_id = ".QuoteValue(DPE_CHAR,$bedId[$i]);            
			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		}

		header("location:".$thisPage."?RoomId=".$enc->Encode($_POST["room_id"])."&RoomName=".$_POST["room_name"]);
		exit();     
	}

     
    /* // -- cari Kategori untuk combo box ---
     $sql = "select kategori_id,kategori_nama from klinik.klinik_kamar_kategori
               order by kategori_id";
     $rs = $dtaccess->Execute($sql);
     $datakategori = $dtaccess->FetchAll($rs);

     $kategori[0] = $view->RenderOption("","All",$show);
     for($i=0,$n=count($datakategori);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kategori"]==$datakategori[$i]["kategori_id"]) $show = "selected";
          $kategori[$i+1] = $view->RenderOption($datakategori[$i]["kategori_id"],$datakategori[$i]["kategori_nama"],$show);
     }
     */
?>

<?php //echo $view->RenderBody("module.css",true,false,"DETAIL BED ".$_GET["RoomName"]); ?>
<br /><br /><br /><br /><br />
<script language="JavaScript">
function DeleteDetil() {
	if(confirm('Anda Yakin Ingin Menghapus kamar ?')){
		document.frmView.btnDelete.value = 'Hapus';
		document.location.href='bed_view.php';
	}
}
</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td><?php echo $tableHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<?php echo $view->SetFocus("btnAdd");?>
<a href="<?php echo "$editPage?RoomId=".$enc->Encode($roomId)."&RoomName=".$_GET["RoomName"];?>" style="border:none;text-decoration:none;"><?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Tambah","button",false);?></a>&nbsp;
<?php echo $view->RenderButton(BTN_BUTTON,"btnKembali","btnKembali","Kembali","button",false,"onClick=\"document.location.href='".$roomPage."';\"" );?> &nbsp;

<!--<a href="<?php echo "$editbed?RoomId=".$enc->Encode($roomId)."&RoomName=".$_GET["RoomName"];?>" style="border:none;text-decoration:none;"><?php echo $view->RenderButton(BTN_BUTTON,"btnKosong","btnKosong","Kosongkan","button",false);?></a>-->

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?> 
<? //echo $roomId?>      
</form>
<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd();?>
