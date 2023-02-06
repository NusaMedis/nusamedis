<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."expAJAX.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $tables = new inoTable("table","800","left");
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName(); 
     $depLowest = $auth->GetDepLowest();
     $tahunTarif = $auth->GetTahunTarif();
     
     $thisPage = "kat_tindakan_view.php";
     $PageJenisBiaya = "page_jenis_biaya.php";  
     $findPage = "akun_prk.php?"; 
     $findPage2 = "akun_prk2.php?";  
     
         if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
    
	
	
	/*  if(!$auth->IsAllowed("man_tarif_kat_tindakan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_kat_tindakan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
	 
          
     if($_POST["id_kategori_tindakan_header_instalasi"]) { 
       $kategoriTindakanHeaderInstalasi = $_POST["id_kategori_tindakan_header_instalasi"];
       $_POST["id_kategori_tindakan_header_instalasi"] = $_POST["id_kategori_tindakan_header_instalasi"];
       }
     
     if($_GET["id_kategori_tindakan_header"]) { 
       $kategoriTindakanHeader = $_GET["id_kategori_tindakan_header"];
       $_POST["id_kategori_tindakan_header"] = $_GET["id_kategori_tindakan_header"];
     }

//     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);     
     if($_POST['id_kategori_tindakan_header']) $sql_where[] = "a.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);

     $editPage = "kat_tindakan_edit.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"];


     $tableHeader = "Manajemen-Kat.&nbsp;Tindakan";

     if($_POST["btnLanjut"] && $_POST['id_kategori_tindakan_header']){ 
	   $sql = "select a.*, b.dep_nama, c.no_prk as no_prk_pendapatan, c.nama_prk as nama_prk_pendapatan, d.no_prk as no_prk_biaya,
             d.nama_prk as nama_prk_biaya, e.kategori_tindakan_header_nama from klinik.klinik_kategori_tindakan a
             left join global.global_departemen b on b.dep_id = a.id_dep
             left join gl.gl_perkiraan c on c.id_prk=a.id_prk_pendapatan
             left join gl.gl_perkiraan d on d.id_prk=a.id_prk_biaya
             left join klinik.klinik_kategori_tindakan_header e on e.kategori_tindakan_header_id=a.id_kategori_tindakan_header
             where 1=1";
     if ($sql_where) $sql .= " and ".implode(" and ",$sql_where);
     $sql .= " order by a.kategori_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataTable= $dtaccess->FetchAll($rs); 
 
     //*-- config table ---*//

     
     
     // --- construct new table ---- //
     $counterHeader=0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan Header";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
	 //$tbHeader[0][$counterHeader][TABLE_ISI] = "Akun Perkiraan Pendapatan";
   //  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
   //  $counterHeader++;
	
	// $tbHeader[0][$counterHeader][TABLE_ISI] = "Akun Perkiraan Biaya";
  //   $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
  //   $counterHeader++;

	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
	/*

	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
*/
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
	
	
	$jumHeader= $counterHeader;
     
      //TOTAL HEADER TABLE
      
      
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_tindakan_header_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_tindakan_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_tindakan_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

		//  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["nama_prk_pendapatan"]."(".$dataTable[$i]["id_prk_pendapatan"].")";
    //      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //      $counter++;
		  
		  
		//  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["nama_prk_biaya"]."(".$dataTable[$i]["id_prk_biaya"].")";
    //      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //      $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kategori_tindakan_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;               
/*
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["kategori_tindakan_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/cari.png" alt="Edit" title="Edit" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
*/
          
          $tbContent[$i][$counter][TABLE_ISI] ='<a href="'.$editPage.'&del=1&id='.$enc->Encode($dataTable[$i]["kategori_tindakan_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';           
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kategori_tindakan_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kategori_tindakan_id"]).'&del=1&id_poli='.$dataTable[$i]["id_poli"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
       }
     }
          
     // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
     $sql .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);
     

     // Data Kategori Tindakan Header //
     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_header[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);
     
      
      
     

?>





<!DOCTYPE html>
<html lang="en">
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
            <div class="page-title">
              <div class="title_left">
                <h3><?php echo $tableHeader;?></h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Filter</h2>
                    <div class="clearfix"></div>
                  </div>                                     
                  <div class="x_content">
				  <form name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
           <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan Header Instalasi</label>
						<select name="id_kategori_tindakan_header_instalasi" class="select2_single form-control"  onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Kategori Tindakan Header Instalasi-</option>
				     		<?php for($i=0,$n=count($dataKategoriTindakanHeaderInstalasi);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"];?>"<?php if ($_POST["id_kategori_tindakan_header_instalasi"]==$dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]) echo"selected"?>><?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select> 
				    </div>
            
					<div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan Header</label>
						<select name="id_kategori_tindakan_header" class="select2_single form-control" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Kategori Tindakan Header -</option>
				    		 <?php for($i=0,$n=count($dataKategoriTindakanHeader);$i<$n;$i++){ ?>
				   			 <option class="inputField" value="<?php echo $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"];?>"<?php if ($_POST["id_kategori_tindakan_header"]==$dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"]) echo"selected"?>><?php echo $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_nama"];?></option>
				  			 <?php } ?>
				  		</select> 
				    </div>
				    
				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						
						<input type="button" name="tambah" value="Tambah" class="col-md-3 col-sm-6 col-xs-6 btn btn-primary" onClick="document.location.href='<?php echo $editPage; ?>'">
						<input type="submit" name="btnLanjut" value="Cari" class="col-md-3 col-sm-5 col-xs-5 btn btn-success">
            <a href="import_kategori.php" class="btn btn-danger col-md-3">Import</a>
				    </div>
					<div class="clearfix"></div>
					<? if($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]){?>
					<?}?>
					<? if ($_x_mode == "Edit"){ ?>
					<?php echo $view->RenderHidden("kategori_tindakan_id","kategori_tindakan_id",$biayaId);?>
					<? } ?>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->


              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table class="table" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                            <? } ?>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
                          
                          <tr class="even pointer">
                            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                            <? } ?>
                            
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
					
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>