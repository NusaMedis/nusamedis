<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php"); 
     require_once($LIB."tree.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
     $editPage = "biaya_prk_edit.php";
     $thisPage = "biaya_prk_view.php";
     
   
	  /*if(!$auth->IsAllowed("akunt_master_setup_biaya_prk",PRIV_READ)){
         echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("akunt_master_setup_biaya_prk",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     } */
     
     
     if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
     
       if ($_GET["kembali"]) $_POST["klinik"]=$_GET["kembali"];
       if($_GET["id_tahun_tarif"]) $_POST["id_tahun_tarif"]=$_GET["id_tahun_tarif"];
       if($_GET["id_poli"]) $_POST["id_poli"] = $_GET["id_poli"];
     
//     if($_POST["paket_id"]) $sql_where [] = " paket_item_id = ".QuoteValue(DPE_CHAR,$_POST["paket_id"]); 
      //if($_POST["id_tahun_tarif"]) $sql_where[] = "a.id_tahun_tarif=".QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);
      if($_POST["id_poli"]) $sql_where[] = "a.id_poli=".QuoteValue(DPE_CHAR,$_POST["id_poli"]);   
       
     if($sql_where) $sql_where = implode(" and ",$sql_where);
     
     $addPage = "biaya_prk_edit.php?tambah=".$_POST["klinik"]."&id_poli=".$_POST["id_poli"]."&id_tahun_tarif=".$_POST["id_tahun_tarif"];
     
   if($_POST["btnLanjut"] || $_GET["id_poli"] || $_GET["kembali"]){  
     $sql = "select a.*, b.kategori_tindakan_nama, i.kategori_tindakan_header_nama, d.nama_prk as prk_debet , j.poli_nama, c.nama_prk as prk_pendapatan
                    from klinik.klinik_biaya_prk a
                    left join klinik.klinik_kategori_tindakan b on a.id_kategori_tindakan = b.kategori_tindakan_id
                    left join gl.gl_perkiraan c on a.id_prk = c.id_prk
                    left join gl.gl_perkiraan d on a.id_prk_debet = d.id_prk
                    left join klinik.klinik_kategori_tindakan_header i on i.kategori_tindakan_header_id = b.id_kategori_tindakan_header
                    left join global.global_auth_poli j on j.poli_id=a.id_poli";
     if($sql_where) $sql .= " where ".$sql_where;
     $sql .= " order by kategori_tindakan_id";
	   $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
//     echo $sql;
     //*-- config table ---*//
     $tableHeader = "&nbsp;Setup Barang";
     
     $isAllowedDel = $auth->IsAllowed("akunt_master_perkiraan_pasien_umum",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("akunt_master_perkiraan_pasien_umum",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("akunt_master_perkiraan_pasien_umum",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          //$tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
          $counterHeader++;
     //}
     
     //if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          //$tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
          $counterHeader++;
     //}
     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "&nbsp;";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++; */
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";     
     //$tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     //$tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
     
     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;*/
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     //$tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perkiraan Debet";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     //$tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perkiraan Pendapatan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
//     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";
     $counterHeader++;
/*
      $counterHead=0;
          $tbHeader[1][$counterHead][TABLE_ISI] = "Debet";
          $tbHeader[1][$counterHead][TABLE_WIDTH] = "5%";
          $counterHead++;
               
          $tbHeader[1][$counterHead][TABLE_ISI] = "Kredit";
          $tbHeader[1][$counterHead][TABLE_WIDTH] = "5%";
          $counterHead++;
*/
     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "PRK. PEMBAYARAN";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";
     $counterHeader++;  
     $counterHead=0;
      
          $tbHeader[1][$counterHead][TABLE_ISI] = "Debet";
          $tbHeader[1][$counterHead][TABLE_WIDTH] = "5%";
          $counterHead++;
               
          $tbHeader[1][$counterHead][TABLE_ISI] = "Kredit";
          $tbHeader[1][$counterHead][TABLE_WIDTH] = "5%";
          $counterHead++;*/
     
     for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0,$j++){
          
         
          //if($isAllowedDel) {
               $tbContent[$j][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["biaya_prk_id"]).'&klinik='.$dataTable[$i]["id_dep"].'&id_tahun_tarif='.$dataTable[$i]["id_tahun_tarif"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onClick="javascript:return CheckDel();"></a>';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          //}
          
          
          
          //if($isAllowedUpdate) {
               $tbContent[$j][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["biaya_prk_id"]).'&klinik='.$dataTable[$i]["id_dep"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          //}
          
          $tbContent[$j][$counter][TABLE_ISI] = $j+1; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["kategori_tindakan_nama"]."&nbsp;&nbsp;(".$dataTable[$i]["kategori_tindakan_header_nama"].")"; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          /*$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++; */
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["prk_debet"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["prk_pendapatan"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;

/*          

          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["nama_prk_piutang_debet"]."&nbsp;(".$dataTable[$i]["no_prk_piutang_debet"].")"; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["nama_prk_piutang_kredit"]."&nbsp;(".$dataTable[$i]["no_prk_piutang_kredit"].")"; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
 */        
          /*$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["nama_prk_lunas_debet"]."&nbsp;(".$dataTable[$i]["no_prk_lunas_debet"].")"; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
         
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["nama_prk_lunas_kredit"]."&nbsp;(".$dataTable[$i]["no_prk_lunas_kredit"].")"; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;*/
         
     }
     
    }     
     
     $colspan = count($tbHeader[0]);
        // KONFIGURASI
      if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }

      // cari jenis pasien e
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs);
     
      // cari jenis layanan e
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_id desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tipeBiayaLayanan = $dtaccess->FetchAll($rs);

     $sql = "select * from klinik.klinik_tahun_tarif order by tahun_tarif_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataTahun = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_auth_poli where poli_tipe='J' or poli_tipe='G' or poli_tipe='L' or poli_tipe='R' or poli_tipe='M' order by poli_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
     
?>

<script language="JavaScript">

function CheckDel(frm)
                    {
                           if (confirm("Semua transaksi yang terdapat di akun tersebut akan dihapus, Apakah anda yakin ingin menghapus akun?")==1)
                           {
                                document.frmView.submit();
                            } else { 
                         return false;
                        }
                  }
/*  function reklinik(kliniks) {
   document.location.href='item_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }  */
  
  function rejenis(jenis) {
   document.location.href='setup_gl_prk_pasien_umum.php?klinik='+jenis+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
</script>
<?php require_once($LAY."header.php") ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
<form name="frmView" method="POST" action="<?php echo $thisPage; ?>"> 
     <div class="col-md-4 col-sm-6 col-xs-12">
         <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Klinik&nbsp;&nbsp;</label>
        <div>
            <select name="id_poli" class="form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
    				    <option class="inputField" value="" >- Pilih Klinik -</option>
        				<?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
        				<option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo"selected"?>><?php echo $dataPoli[$i]["poli_nama"];?>&nbsp;</option>
        				<?php } ?>
    				</select>
        </div>
     </div>
     <!--<tr>
        <td align="right" class="tablecontent" width="10%">&nbsp;Tahun Tarif&nbsp;&nbsp;</td>
        <td class="tablecontent-odd" colspan="4">
            <select name="id_tahun_tarif" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
    				    <option class="inputField" value="" >- Semua Tahun Tarif -</option>
        				<?php for($i=0,$n=count($dataTahun);$i<$n;$i++){ ?>
        				<option class="inputField" value="<?php echo $dataTahun[$i]["tahun_tarif_id"];?>" <?php if($_POST["id_tahun_tarif"]==$dataTahun[$i]["tahun_tarif_id"]) echo"selected"?>><?php echo $dataTahun[$i]["tahun_tarif_nama"];?>&nbsp;</option>
        				<?php } ?>
    				</select>
        </td>
     </tr>-->
			<div class="col-md-4 col-sm-6 col-xs-12">
				<div>
            <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">					
            <input type="button" name="btnAdd" value="Tambah" id="button" class="pull-right btn btn-primary" onClick="document.location.href='<?php echo $addPage;?>'">
				</div>
      </div>
</form>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

		 </div>
		 </div>
    </div>
  </div>
 <?php require_once($LAY."footer.php") ?>
<?php require_once($LAY."js.php") ?>
  </body> 