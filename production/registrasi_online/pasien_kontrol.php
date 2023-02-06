<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
   
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $poliId = $auth->IdPoli();
 	   /*if(!$auth->IsAllowed("kas_pembayaran_pemeriksaan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kas_pembayaran_pemeriksaan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }  */

     $_x_mode = "New";
     //$thisPage = "penata_jasa_view.php";
     //$editPage = "penata_jasa_proses.php";
     $TipeRegistrasiArray["O"] = "Registrasi Online";
     $TipeRegistrasiArray["S"] = "SMS Gateway";
          
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];

    
      $table = new InoTable("table","100%","left");
       $skr = date("d-m-Y");
        //kunjungan
        if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
        if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
        if($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
        if($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];
        
     if($_POST["cust_usr_kode"])  $sql_where[] = "c.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
     if($_POST["cust_usr_nama"])  $sql_where[] = "upper(c.cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_nama"]."%");
     if($_POST["reg_buffer_no_antrian"])  $sql_where[] = " a.reg_buffer_no_antrian like ".QuoteValue(DPE_CHAR,"%".$_POST["reg_buffer_no_antrian"]."%");
     if($_POST["reg_buffer_flag"])  $sql_where[] = "a.reg_buffer_flag = ".QuoteValue(DPE_CHAR,$_POST["reg_buffer_flag"]);
     if($_POST["is_daftar"]){
      if ($_POST["is_daftar"]=="y" or $_POST["is_daftar"]=="n" ) {
        # code...
         $sql_where[] = "a.is_daftar = ".QuoteValue(DPE_CHAR,$_POST["is_daftar"]);
      }
      else{
        $sql_where[] = "a.reg_buffer_batal = 'y' ";
      }

     } 
     if (!$_POST["is_daftar"]) {
       # code...
      $sql_where[]="(reg_buffer_batal ='n' or reg_buffer_batal is null )";

     }
     if($_POST["poli_id"])  $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["poli_id"]);
     if($_POST["dokter"])  $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["dokter"]);
     
     $sql_where[] = "DATE(a.reg_buffer_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "DATE(a.reg_buffer_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));

     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
      
          $sql = "select a.*, e.usr_name, d.poli_nama,c.cust_usr_nama as nama,c.cust_usr_alamat,c.cust_usr_kode as rm, cust_usr_no_hp, f.jenis_nama, g.tipe_biaya_nama,c.cust_usr_id,x.*
          from klinik.klinik_registrasi_buffer a 
          left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
          left join global.global_auth_poli d on d.poli_id = a.id_poli
          left join global.global_auth_user e on e.usr_id = a.id_dokter
          left join global.global_jenis_pasien f on a.reg_buffer_jenis_pasien = f.jenis_id
          left join global.global_tipe_biaya g on a.reg_buffer_tipe_layanan = g.tipe_biaya_id
          left join global.global_login_cust x on a.cust_id_login = x.login_cust_id
          where   reg_buffer_flag <>' ' and ( reg_buffer_flag = ' ' or reg_buffer_flag = 'W' ) and (reg_buffer_utama='' or reg_buffer_utama is null) and
          ".$sql_where."
                  order by a.reg_buffer_no_antrian asc";
          // echo $sql;
          $dataTable = $dtaccess->FetchAll($sql);
//                  a.pembayaran_flag = 'n' and 
//sementara dihilangkan

          $tableHeader = "&nbsp;Daftar Pasien Kontrol";
          $counterHeader = 0;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Reg";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Register";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;


          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Medrec";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
		  
		 
		 
      

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "23%";
          $counterHeader++;

           $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "23%";
          $counterHeader++;


          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nomor Handphone";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          if ($_POST["is_daftar"]=="k"){
             $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Pembatalan";
            
          }
          else{
             $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Registrasi";

          }
          
         
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Kunjungan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Kunjungan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++; 
                              
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "22%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Registrasi";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;


          
          
      for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
          
          $editPage = "reg_pas_lama.php?idbuffer=".$dataTable[$i]["reg_buffer_id"]."&cust_usr_id=".$dataTable[$i]["id_cust_usr"]."&id_poli=".$dataTable[$i]["id_poli"]."&jenis_pasien=".$dataTable[$i]["reg_buffer_jenis_pasien"]."&tipe=".$dataTable[$i]["reg_buffer_tipe_rawat"]."&dokter=".$dataTable[$i]["id_dokter"]."&no_antrian=".$dataTable[$i]["reg_buffer_no_antrian"]."&id_reg=".$dataTable[$i]["id_reg"]."&nama=".$dataTable[$i]["nama"]."&tanggal=".$dataTable[$i]["reg_buffer_tanggal"]."&buffer_flag=".$dataTable[$i]["reg_buffer_flag"]."&id_perusahaan=".$dataTable[$i]["perusahaan_id"];
          $bpjs = "../sep/create.php?instalasi='I'&idbuffer=".$dataTable[$i]["reg_buffer_id"]."&cust_usr_id=".$dataTable[$i]["id_cust_usr"]."&id_poli=".$dataTable[$i]["id_poli"]."&jenis_pasien=".$dataTable[$i]["reg_buffer_jenis_pasien"]."&tipe=".$dataTable[$i]["reg_buffer_tipe_rawat"]."&dokter=".$dataTable[$i]["id_dokter"]."&no_antrian=".$dataTable[$i]["reg_buffer_no_antrian"]."&reg_id=".$dataTable[$i]["id_reg"]."&nama=".$dataTable[$i]["nama"]."&tanggal=".$dataTable[$i]["reg_buffer_no_antrian"];
          $cetak_barcode="cetak_reg.php?id_reg=".$regId."&id=".$custUsrId;
          $delPage = "registrasi_online.php?del=1&idbuffer=".$dataTable[$i]["reg_buffer_id"]."&id_reg=".$dataTable[$i]["id_reg"];

          if($dataTable[$i]["is_daftar"]=='n' && $dataTable[$i]["reg_buffer_batal"]=='n' ){
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Daftar" title="Daftar" border="0"/></a>';               
          }
          elseif ($dataTable[$i]["reg_buffer_batal"]=='y') {
             $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
           } else {
          $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$delPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" onclick="javascript:return Hapus();" alt="Hapus" title="Hapus" border="0"/></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_buffer_id"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rm"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
	  			$counter++;
				
			

       
    			
    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


            if ($dataTable[$i]["reg_buffer_flag"]=="W") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] =  $dataTable[$i]["cust_usr_no_hp"];
          }
          elseif ($dataTable[$i]["reg_buffer_flag"]=="A") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["login_cust_phone_number"];
          }
       
          else{
             $tbContent[$i][$counter][TABLE_ISI] = " ";
          }
         
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


    			// $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["login_cust_phone_number"];
    			// $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			// $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";

          if (($dataTable[$i]["reg_buffer_batal"]=='y')) {
           $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["reg_buffer_when_update_batal"]);
          }
    			else{
            $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["reg_buffer_when_update"]);
          }
    			
    			
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;


          
    			$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["reg_buffer_tanggal"]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++; 
          
    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_buffer_waktu"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;  
                       			  			
    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          if ($dataTable[$i]["reg_buffer_flag"]=="W") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "Pasien Kontrol";
          }
          elseif ($dataTable[$i]["reg_buffer_flag"]=="A") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "Android";
          }
          elseif ($dataTable[$i]["reg_buffer_flag"]=="S") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "SMS";
          }
          else{
             $tbContent[$i][$counter][TABLE_ISI] = " ";
          }
         
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			if ($dataTable[$i]["is_daftar"]=='y') {
    				# code...
    				 $tbContent[$i][$counter][TABLE_ISI] = "Sudah Verif";
    			}
          elseif ($dataTable[$i]["reg_buffer_batal"]=='y') {
            # code...
            $tbContent[$i][$counter][TABLE_ISI] = "Batal Registrsi";
          }
    			else{
    				$tbContent[$i][$counter][TABLE_ISI] = "Belum Verif";
    			}

    		 	$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          
//  		 		} //END JIKA SUDAH BAYAR   SEMENTARA DIHILANGKAN

          unset($sqlBayar);
          unset($dataBayar);
      } 
      
          //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;

    $sql = "SELECT * from global.global_auth_poli";
    $dataPoli = $dtaccess->FetchAll($sql);

    $sql = "select * from global.global_auth_user a
left join global.global_auth_role b on a.id_rol = b.rol_id
where (rol_jabatan = 'D') order by usr_name asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataDokter = $dtaccess->FetchAll($rs);


    if($_GET["del"]){
      // $sql = "delete from klinik.klinik_registrasi_buffer where reg_buffer_id = ".QuoteValue(DPE_CHAR,$_GET["idbuffer"]);
      // $rs = $dtaccess->Execute($sql);

      // $sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
      // $rs = $dtaccess->Execute($sql);
      $reg_buffer_when_update = date("Y-m-d H:i:s");
       $sql="UPDATE klinik.klinik_registrasi_buffer  set reg_buffer_batal='y',reg_buffer_when_update='$reg_buffer_when_update' where reg_buffer_id= ".QuoteValue(DPE_CHAR,$_GET["idbuffer"]);
       $rs = $dtaccess->Execute($sql);
      header('location:registrasi_online.php');
    }
    if($_POST["btnExcel"]){
        
        $_x_mode = "excel";
      }  
	
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

<!-- /////////////////// -->
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
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><? echo $tableHeader?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

          <form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Kunjungan (DD-MM-YYYY)</label>
                        <div class='input-group date'>
              <input name="tanggal_awal" type='text' class="form-control" data-inputmask="'mask': '99-99-9999'"
              value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
            </div>                   
      
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
            <div class='input-group date'>
              <input  name="tanggal_akhir"  type='text' class="form-control" data-inputmask="'mask': '99-99-9999'"
              value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
            </div>             
            </div>
          <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
              <input class="form-control col-md-7 col-xs-12" type="text"  id="cust_usr_kode" name="cust_usr_kode" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_kode"];?>"/>            
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
              <input class="form-control col-md-7 col-xs-12" type="text"  id="cust_usr_nama" name="cust_usr_nama" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_nama"];?>"/>            
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Kode Booking</label>
              <input class="form-control col-md-7 col-xs-12" type="text"  id="reg_buffer_no_antrian" name="reg_buffer_no_antrian" size="15" maxlength="10" value="<?php echo $_POST["reg_buffer_no_antrian"];?>"/>            
            </div>
    	
			
<!-- 			<div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Registrasi</label>
  			<select class="select2_single form-control" name="reg_buffer_flag" id="reg_buffer_flag" onKeyDown="return tabOnEnter(this, event);">
        	<option value="">--Pilih Cara Registrasi--</option>
  				<option value="A" <?php if($_POST["reg_buffer_flag"]=="A")echo "selected";?>>Android</option>
  				<option value="S" <?php if($_POST["reg_buffer_flag"]=="S")echo "selected";?>>S M S</option>
  				<option value="W" <?php if($_POST["reg_buffer_flag"]=="W")echo "selected";?>>Website</option>
  			</select>
				</div> -->

			<!-- 	<div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Status</label>
  			<select class="select2_single form-control" name="is_daftar" id="is_daftar" onKeyDown="return tabOnEnter(this, event);">
        	<option value="">--Pilih Cara Registrasi--</option>
  				<option value="y" <?php if($_POST["is_daftar"]=="y")echo "selected";?>>Sudah Verif</option>
  				<option value="n" <?php if($_POST["is_daftar"]=="n")echo "selected";?>>Belum Verif</option>
          <option value="k" <?php if($_POST["is_daftar"]=="k")echo "selected";?>>Batal Registrasi</option>
  				
  			</select>
				</div> -->
        <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Poli</label>
        <select class="select2_single form-control" name="poli_id">
          <option value="">--Pilih Poli--</option>
          <?php for($i = 0; $i < count($dataPoli); $i++) {?>
            <option value="<?=$dataPoli[$i]['poli_id']?>" <?=($_POST['poli_id'] == $dataPoli[$i]['poli_id']) ? "selected" : "" ?>><?=$dataPoli[$i]['poli_nama']?></option>
          <?php }?>
        </select>
        </div>

         <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Pilih Dokter</label>
        <select class="select2_single form-control" name="dokter">
          <option value="">--Pilih Dokter--</option>
          <?php for($i = 0; $i < count($dataDokter); $i++) {?>
            <option value="<?=$dataDokter[$i]['usr_id']?>" <?=($_POST['dokter'] == $dataDokter[$i]['usr_id']) ? "selected" : "" ?>><?=$dataDokter[$i]['usr_name']?></option>
          <?php }?>
        </select>
        </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">

            <input type="submit" name="btnExcel" value="Excel" class="pull-right btn btn-success">
            </div>
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
                    <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    
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
<script>



<?php if($_x_mode=="excel"){ ?> 
 window.open('lap_pasien_kontrol.php?tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&cust_usr_nama=<?php echo $_POST["cust_usr_nama"];?>&cust_usr_kode=<?php echo $_POST["cust_usr_kode"];?>&reg_buffer_no_antrian=<?php echo $_POST["reg_buffer_no_antrian"];?>&reg_buffer_flag=<?php echo $_POST["reg_buffer_flag"];?>&is_daftar=<?php echo $_POST["is_daftar"];?>&dokter=<?php echo $_POST["dokter"];?>&excel=y', '_blank');
<?php } ?>

</script>

