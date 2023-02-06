<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $PageJenisBiaya = "page_jenis_biaya.php";    

     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     
     /*if(!$auth->IsAllowed("man_tarif_tarif_tindakan_semua_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_tindakan_semua_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
	  $backPage = "tindakan_view.php"; 
    
    //Keterangan CITO
    $ketCito["C"] = "CITO";
    $ketCito["E"] = "Non CITO";
     
    	$isAllowedCreate=1;
    	$isAllowedUpdate=1;
    	$isAllowedDel=1;
      $tipeRawat["TA"] = "IRJ";
      $tipeRawat["TG"] = "IGD";
      $tipeRawat["TI"] = "IRNA";
     
     if($_POST["id_kategori_tindakan_header_instalasi"]) { 
       $idKategoriTindakanHeaderInstalasi = $_POST["id_kategori_tindakan_header_instalasi"];
       $_POST["id_kategori_tindakan_header_instalasi"] = $_POST["id_kategori_tindakan_header_instalasi"];
       }
     
     if($_GET["id_kategori_tindakan_header_instalasi"]) { 
       $idKategoriTindakanHeaderInstalasi = $_GET["id_kategori_tindakan_header_instalasi"];
       $_POST["id_kategori_tindakan_header_instalasi"] = $_GET["id_kategori_tindakan_header_instalasi"];
     }

     if($_POST["id_kategori_tindakan_header"]) { 
       $idKategoriTindakanHeader = $_POST["id_kategori_tindakan_header"];
       $_POST["id_kategori_tindakan_header"] = $_POST["id_kategori_tindakan_header"];
       }
     
     if($_GET["id_kategori_tindakan_header"]) { 
       $idKategoriTindakanHeader = $_GET["id_kategori_tindakan_header"];
       $_POST["id_kategori_tindakan_header"] = $_GET["id_kategori_tindakan_header"];
     }

     if($_POST["biaya_kategori"]) { 
       $idKategori = $_POST["biaya_kategori"];
       $_POST["biaya_kategori"] = $_POST["biaya_kategori"];
       }
     
     if($_GET["biaya_kategori"]) { 
       $idKategori = $_GET["biaya_kategori"];
       $_POST["biaya_kategori"] = $_GET["biaya_kategori"];
     }
     
     if($_POST["biaya_jenis"]) { 
       $biayaJenis = $_POST["biaya_jenis"];
       $_POST["biaya_jenis"] = $_POST["biaya_jenis"];
       }
     
     if($_GET["biaya_jenis"]) { 
       $biayaJenis = $_GET["biaya_jenis"];
       $_POST["biaya_jenis"] = $_GET["biaya_jenis"];
     }
     
     if($_POST["biaya_id"]) { 
       $biayaId = $_POST["biaya_id"];
       $_POST["biaya_id"] = $_POST["biaya_id"];
       }
     
     if($_GET["id"]) { 
       $biayaId = $_GET["id"];
       $_POST["id"] = $_GET["id"];
     }                                                            
     $biayaId = $enc->Decode($_GET["id"]);
     $excel = $_POST["btnExcel"];
     $cetak = $_POST["btnCetak"];
     
     $addPage = "tindakan_detail_add.php";
     $kembaliPage = "tindakan_view.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
     $editPage = "tindakan_detail_add.php?";
     $thisPage = "tindakan_view.php";
     $isAllowedUpdate=1;
		
		$sql_where[] = "1=1"; 
	    //if($in_nama) $sql_where[] = "UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
	    //$sql_where[] = " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
	    //if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
      if($idKategoriTindakanHeader && $idKategoriTindakanHeader!="--") $sql_where[] = "b.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$idKategoriTindakanHeader);
      if($idKategori && $idKategori!="--" ) $sql_where[] = "a.biaya_kategori = ".QuoteValue(DPE_CHAR,$idKategori);
      if($biayaJenis && $biayaJenis!="--") $sql_where[] = "a.biaya_jenis = ".QuoteValue(DPE_CHAR,$biayaJenis); 
      if($biayaId && $biayaId!="--") $sql_where[] = "h.id_biaya = ".QuoteValue(DPE_CHAR,$biayaId);    
      $sql_where = implode(" and ",$sql_where);

// QUERY PERKIRAAN NANTI DULU
//              f.no_prk as no_prk_beban, 
//              e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, 
//            left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
//              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
    
   	  $sql = "select h.*,a.*, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, c.dep_nama, 
              d.kegiatan_kategori_nama, 
              g.kategori_tindakan_header_nama,i.kelas_nama
              from  klinik.klinik_biaya_tarif h            
              left join klinik.klinik_biaya a on h.id_biaya = a.biaya_id     
              left join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join global.global_departemen c on c.dep_id = a.id_dep
              left join klinik.klinik_kegiatan_kategori_tindakan d on d.kegiatan_kategori_id = a.id_kegiatan_kategori 
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              left join klinik.klinik_kelas i on h.id_kelas = i.kelas_id
              where ".$sql_where;
      $sql .= " order by g.kategori_tindakan_header_urut,b.kategori_urut,a.biaya_urut";
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataTindakan = $dtaccess->FetchAll($rs);
  	//echo $sql;

      if($isAllowedUpdate){
         $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$addPage.'?id_biaya='.$enc->Encode($dataTindakan[$i]["biaya_id"]).'\'"></button>';
        $tombolKembali = '<input type="button" name="btnKembali" value="Kembali" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$kembaliPage.'\'"></button>';
      }

      $sql = "select * FROM klinik.klinik_hasil_lab"; 
      $sql .= " ORDER BY hasil_lab_kode asc";
      //WHERE LENGTH(id_prk) = 2 ORDER BY order_prk";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GL);
     $dataTable = $dtaccess->FetchAll($rs);
		
	 	$counterHeader = 0;
     $tableHeader = "Manajemen - Tarif Tindakan";
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;  
	 
	   //$tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     //$counterHeader++;  
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     //$counterHeader++;  
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Hasil Lab";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
	 
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
    // $counterHeader++; 
	 	 
//  	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Pendapatan";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++; 
	 
//	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Beban";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++; 
	  
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tambah";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; 

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Anak";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
    $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
     $counterHeader++; 
          
     //TOTAL HEADER TABLE
     $jumHeader= $counterHeader;
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	
	          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
	          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	          $counter++;
			  
			      //$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kategori_tindakan_nama"];
	          //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          //$counter++;

			      //$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_nama"];
	          //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          //$counter++;
            
            //$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$tipeRawat[$dataTable[$i]["biaya_tarif_jenis"]];
	          //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          //$counter++;
            
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["hasil_lab_kode"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
            
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["hasil_lab_nama"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
            
			  
            

		//	  if($dataTable[$i]["biaya_jenis"]=='TA'){
	//		  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Jalan";	
//			  }elseif($dataTable[$i]["biaya_jenis"]=='TG'){
//			  $tbContent[$i][$counter][TABLE_ISI] = "I G D";	
//			  }elseif($dataTable[$i]["biaya_jenis"]=='TI'){
//	          $tbContent[$i][$counter][TABLE_ISI] = "Rawat Inap";
//			  }
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//	          $counter++;   
	          
		//	  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
	  //        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	  //        $counter++;
		//	  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
	  //        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	  //        $counter++;
			  //if($isAllowedUpdate && $dataTable[$i]["biaya_tarif_id"]=='')
       // {
       //        $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$addPage.'&id='.$enc->Encode($dataTable[$i]["biaya_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0"></a>';
       // } else {
        //       $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';        
        //}

        //       $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        //       $counter++;
               
          
              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTindakan[$i]["biaya_id"])."&hasil_lab_id=".$dataTable[$i]["hasil_lab_id"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;

              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'parent='.$enc->Encode($dataTable[$i]["hasil_lab_template_kode"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/anak.png" alt="Tambah Anak" title="Tambah Anak" border="0"></a>';               
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;

              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id_del='.$dataTable[$i]["biaya_id"].'&del=1"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
    }
		
		
     if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=tarif_all.xls');
     }
     
     if($_POST["btnCetak"]){
      $_x_mode = "cetak" ;      
     }

     // Data Kategori Tindakan Header Instalasi//
     /*if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_instalasi[] = "a.klinik_kategori_tindakan_header_instalasi_id = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);*/
     $sql_instalasi = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a where 1=1 and klinik_kategori_tindakan_header_instalasi_id='07'";
     //if ($sql_where_instalasi) $sql_instalasi .= " and ".implode(" and ",$sql_where_instalasi);
     $sql_instalasi .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs_instalasi = $dtaccess->Execute($sql_instalasi);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->Fetch($rs_instalasi);
     //echo $sql_instalasi;
      // Data Kategori Tindakan Header //
     if($_POST['id_kategori_tindakan_header']) $sql_where_header[] = "a.kategori_tindakan_header_id = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->Fetch($rs_header);

     // Data Kategori Tindakan Header //
     
     if($_POST['biaya_kategori']) $sql_where_tindakan[] = "kategori_tindakan_id = ".QuoteValue(DPE_CHAR,$_POST['biaya_kategori']);
     $sql_tindakan = "select * from  klinik.klinik_kategori_tindakan where 1=1";
     if ($sql_where_tindakan) $sql_tindakan .= " and ".implode(" and ",$sql_where_tindakan);
     $sql_tindakan .= " order by kategori_urut asc";
     $rs_tindakan = $dtaccess->Execute($sql_tindakan);
     $dataKategoriTindakan = $dtaccess->Fetch($rs_tindakan);

     
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
                    <h2>Nama Tindakan : <?php echo $dataTindakan[0]["biaya_nama"];?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
   					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<?php echo "$tombolAdd"; ?>
				    </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<?php echo "$tombolKembali"; ?>
				    </div>				    
					<div class="clearfix"></div>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

            <div class="row">

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

