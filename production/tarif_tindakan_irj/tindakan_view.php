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
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }
     
	  $backPage = "tindakan_view.php"; 
     
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
     
     $excel = $_POST["btnExcel"];
     $cetak = $_POST["btnCetak"];
     
     $addPage = "tindakan_add.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"]."&tambah=1";
     $editPage = "tindakan_edit.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
     $detailPage = "tindakan_detail_view.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
     $thisPage = "tindakan_view.php";

     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$addPage.'\'"></button>';
		
		$sql_where[] = "1=1"; 
	    //if($in_nama) $sql_where[] = "UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
	    //$sql_where[] = " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
	    //if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
      if($idKategoriTindakanHeader && $idKategoriTindakanHeader!="--") $sql_where[] = "b.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$idKategoriTindakanHeader);
      if($idKategori && $idKategori!="--" ) $sql_where[] = "a.biaya_kategori = ".QuoteValue(DPE_CHAR,$idKategori);
      if($biayaJenis && $biayaJenis!="--") $sql_where[] = "a.biaya_jenis = ".QuoteValue(DPE_CHAR,$biayaJenis);    
     // if ($_POST["jenis_pasien"] != "") { $sql_where[] = "e.id_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["jenis_pasien"]); }  
      $sql_where = implode(" and ",$sql_where);

// QUERY PERKIRAAN NANTI DULU
//              f.no_prk as no_prk_beban, 
//              e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, 
//            left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
//              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
    
   	  $sql = "select a.*, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, c.dep_nama, 
              d.kegiatan_kategori_nama, 
              g.kategori_tindakan_header_nama
              from klinik.klinik_biaya a
              left join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join global.global_departemen c on c.dep_id = a.id_dep
              left join klinik.klinik_kegiatan_kategori_tindakan d on d.kegiatan_kategori_id = a.id_kegiatan_kategori 
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              where ".$sql_where;
      $sql .= " order by g.kategori_tindakan_header_urut,b.kategori_urut,a.biaya_urut";
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataTable = $dtaccess->FetchAll($rs);
  	  //echo $sql;
		
	 	$counterHeader = 0;
     $tableHeader = "Manajemen - Tarif Tindakan";
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;  
	 
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;  
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     $counterHeader++;  
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++;
     
//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
//     $counterHeader++;
     
//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
//     $counterHeader++;

//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Awal";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
//     $counterHeader++;

//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Akhir";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
//     $counterHeader++;
	 
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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; 
	   
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
     //$counterHeader++; 
          
     //TOTAL HEADER TABLE
     $jumHeader= $counterHeader;
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	
	          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
	          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	          $counter++;
			  
			      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kategori_tindakan_nama"];
            $check = $dataTable[$i]["kategori_tindakan_nama"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
           

			      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_nama"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
            
            //$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$tipeRawat[$dataTable[$i]["biaya_tarif_jenis"]];
	          //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          //$counter++;
            
//            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kelas_nama"];
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//	          $counter++;
            
//            $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["biaya_total"]);
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//	          $counter++;
			  
//            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_tarif_tgl_awal"];
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
//	          $counter++;
            
//            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_tarif_tgl_akhir"];
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
//	          $counter++;
            

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
        //{
        //       $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$addPage.'&id='.$enc->Encode($dataTable[$i]["biaya_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0"></a>';
        //} else {
        //       $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';        
        //}
        // 
        //       $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        //       $counter++;
               
          
        //if($isAllowedUpdate && $dataTable[$i]["biaya_tarif_id"]) 
        if($isAllowedUpdate)
        {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detailPage.'&check='.$enc->Encode($check).'&biaya_id='.$dataTable[$i]["biaya_id"].'&id='.$enc->Encode($dataTable[$i]["biaya_tarif_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/cari.png" alt="Edit" title="Edit" border="0"></a>';
        } else {
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';                
        }
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          
     //     if($isAllowedDel){
     //          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id_del='.$dataTable[$i]["biaya_tarif_id"].'&del=1"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
     //          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
     //          $counter++;
     //     }			    


				                            
                    
     }
		
		
		
		
     if($_POST["btnExcel"]){
          $_x_mode = "excel" ;
     }
     
     if($_POST["btnCetak"]){
      $_x_mode = "cetak" ;      
     }

     // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
     $sql .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);

    
      // Data Kategori Tindakan Header //
      $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1 and ";
      $sql_header .= "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
      $sql_header .= " order by kategori_tindakan_header_urut asc";
      $rs_header = $dtaccess->Execute($sql_header);
      $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);

     // Data Kategori Tindakan  //
     $sql_header = "select * from  klinik.klinik_kategori_tindakan a where 1=1 and ";
     $sql_header .= "a.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);
     $sql_header .= " order by kategori_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakan = $dtaccess->FetchAll($rs_header);

     //Jenis Pasien
     /*
     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataJenisPasien = $dtaccess->FetchAll($rs);  */

     
?>



<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  <script type="text/javascript">
    function getHeaderTindakan(isi) {
      // alert(isi);
      $.getJSON('get_header_tindakan.php?id='+isi, function(nilai) {
        // alert(nilai);
        $("#id_kategori_tindakan_header").html(`<option>- Pilih Kategori Tindakan Header -</option>`);
        $.each(nilai, function(index, val) {
          $("#id_kategori_tindakan_header").append(
            "<option value = '"+val.kategori_tindakan_header_id+"'>"+val.kategori_tindakan_header_nama+"</option>");
        });
      });
    }
    function getKategoriTindakan(isi) {
      // alert(isi);
      $.getJSON('get_kategori_tindakan.php?id='+isi, function(nilai) {
        // alert(nilai);
        $("#biaya_kategori").html(`<option>- Pilih Kategori Tindakan  -</option>`);
        $.each(nilai, function(index, val) {
          $("#biaya_kategori").append(
            "<option value = '"+val.kategori_tindakan_id+"'>"+val.kategori_tindakan_nama+"</option>");
        });
      });
    }
    
  </script>
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
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					 <!--
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>
						<select name="biaya_jenis" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    		<option class="inputField" value="" >- Pilih Tipe Rawat -</option>
				    				<option class="inputField" value="TA" <?php if ($_POST["biaya_jenis"]=="TA") echo"selected"?>>Rawat Jalan&nbsp;</option> 
				   					 <option class="inputField" value="TI" <?php if ($_POST["biaya_jenis"]=="TI") echo"selected"?>>Rawat Inap&nbsp;</option>
           							 <option class="inputField" value="TG" <?php if ($_POST["biaya_jenis"]=="TG") echo"selected"?>>IGD&nbsp;</option>
				  		</select> 				  		
				    </div> -->
            <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan Header Instalasi</label>
						<select name="id_kategori_tindakan_header_instalasi" class="select2_single form-control" onchange="getHeaderTindakan(this.value)" >
						    <option class="inputField" value=" " >- Pilih Kategori Tindakan Header Instalasi-</option>
				     		<?php for($i=0,$n=count($dataKategoriTindakanHeaderInstalasi);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"];?>"<?php if ($_POST["id_kategori_tindakan_header_instalasi"]==$dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]) echo"selected"?>><?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select> 
				    </div>
            
				    <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan Header</label>
						<select name="id_kategori_tindakan_header" id="id_kategori_tindakan_header" class="select2_single form-control" onchange="getKategoriTindakan(this.value)" >
						    <option class="inputField" value="" >- Pilih Kategori Tindakan Header -</option>
				    		 <?php for($i=0,$n=count($dataKategoriTindakanHeader);$i<$n;$i++){ ?>
				   			 <option class="inputField" value="<?php echo $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"];?>"<?php if ($_POST["id_kategori_tindakan_header"]==$dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"]) echo"selected"?>><?php echo $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_nama"];?>&nbsp;</option>
				  			 <?php } ?>
				  		</select> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan</label>
						<select name="biaya_kategori" id="biaya_kategori" class="select2_single form-control"  >
						    <option class="inputField" value="" >- Pilih Kategori Tindakan -</option>
				     		<?php for($i=0,$n=count($dataKategoriTindakan);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKategoriTindakan[$i]["kategori_tindakan_id"];?>"<?php if ($_POST["biaya_kategori"]==$dataKategoriTindakan[$i]["kategori_tindakan_id"]) echo"selected"?>><?php echo $dataKategoriTindakan[$i]["kategori_tindakan_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select> 
				    </div>
            <!--
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
            <select name="jenis_pasien" class="select2_single form-control" >
                <option class="inputField" value="" >- Pilih Jenis Pasien -</option>
                <?php for($i=0,$n=count($dataJenisPasien);$i<$n;$i++){ ?>
                <option class="inputField" value="<?php echo 'test'?>"<?php if ($_POST["jenis_pasien"]==$dataJenisPasien[$i]["jenis_id"]) echo"selected"?>><?php echo $dataJenisPasien[$i]["jenis_nama"];?>&nbsp;</option>
                <?php } ?>
              </select> 
            </div>    -->
				    			    
					<div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
            <input type="submit" name="btnLanjut" id="btnLanjut" value="Lanjut" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">           
          </div>
          <!--
             <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
            <?php echo "$tombolAdd"; ?>
            </div> -->
          <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">           
				  </div>
					
          
					<div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
            <input type="submit" name="btnExcel" id="btnUrut" value="Export Excel" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">			
				  </div> 
				
          
				    
					<div class="clearfix"></div>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

            <?php if ($_POST["btnLanjut"]) { ?>
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
            
            <? } ?>
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

<script language="JavaScript">

<?php if($_x_mode=="cetak"){ ?>	
  window.open('tindakan_cetak.php?id_kategori_tindakan_header_instalasi=<?php echo $_POST["id_kategori_tindakan_header_instalasi"];?>&id_kategori_tindakan_header=<?php echo $_POST["id_kategori_tindakan_header"];?>&biaya_kategori=<?php echo $_POST["biaya_kategori"];?>&cetak=y', '_blank');
<?php }
      else if($_x_mode=="excel"){ ?>
  window.open('tindakan_cetak.php?id_kategori_tindakan_header_instalasi=<?php echo $_POST["id_kategori_tindakan_header_instalasi"];?>&id_kategori_tindakan_header=<?php echo $_POST["id_kategori_tindakan_header"];?>&biaya_kategori=<?php echo $_POST["biaya_kategori"];?>&excel=y', '_blank');
<?php } ?>
</script>
