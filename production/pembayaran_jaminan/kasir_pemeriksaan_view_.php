<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $depNama = $auth->GetDepNama();
     $plx = new expAJAX("KurangBayar");
 	 
       if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 
     //if(!$auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE) && !$auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)){
     //     die("access_denied");
     //     exit(1);
     //} else if($auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE)===1 || $auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)===1){
     //     echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Login First'</script>";
     //     exit(1);
     //}

      //AJAX KURANG BAYAR
      function KurangBayar($id)
       {
          global $dtaccess, $depId, $view, $auth, $table;
          
          $sql = "select * from klinik.klinik_pembayaran where id_cust_usr=".QuoteValue(DPE_CHAR,$id)." 
                  and (pembayaran_flag='k' or pembayaran_flag='p' or pembayaran_total<>pembayaran_yg_dibayar)";
          $kurang = $dtaccess->Fetch($sql);
                   
          if($kurang["pembayaran_id"]) {
            return format_date($kurang["pembayaran_tanggal"]);
          }
          else
          {
            return 0;
          }
       }


     $_x_mode = "New";
     $thisPage = "kasir_pemeriksaan_view.php";
     $editPage = "kasir_pemeriksaan_proses.php";
     $cicilanPage = "kasir_pemeriksaan_proses_cicilan.php";
     $bayarCicilan = "kasir_pemeriksaan_proses_byr_cicilan.php";
                                                                                       
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_kasir_reg_bayar"] = $konfigurasi["dep_kasir_reg_bayar"];
     $_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
     $_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];
    
     $table = new InoTable("table","100%","left");
     $skr = date("d-m-Y");
       
     if($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
     if($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];  
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     

     if($_POST["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
//     if($_POST["cust_usr_nama"])  {
//      $sql_where[] = "(UPPER(b.cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%")." 
//                      or UPPER(h.fol_keterangan) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%").")";
//     }
     if($_POST["cust_usr_nama"])  
     {
      $sql_where[] = "(UPPER(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%')";
     }

     if($_POST["reg_jenis_pasien"])  $sql_where[] = "c.reg_jenis_pasien =".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);

     if($_POST["reg_tipe_rawat"] <> '--')  $sql_where[] = "c.reg_tipe_rawat =".QuoteValue(DPE_CHAR,$_POST["reg_tipe_rawat"]);

     $sql_where[] = "DATE(reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "DATE(reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     
     if($_POST["btnLanjut"] || $_POST["btnExcel"] )   
     {                                                 

         $sql = "select * from global.global_auth_poli where poli_tipe='P'";
         $rs = $dtaccess->Execute($sql);
         $poliOP = $dtaccess->Fetch($rs);

         if ($_POST["reg_tipe_rawat"] == 'J' || $_POST["reg_tipe_rawat"] == 'G') 
         {            
         $sql = "select a.pembayaran_id,a.pembayaran_total,a.pembayaran_flag, 
                 b.cust_usr_nama, b.cust_usr_kode,
                 c.id_dokter,c.reg_id,c.id_poli,c.reg_tanggal,c.reg_waktu,c.reg_keterangan,
	               c.reg_status, c.reg_jenis_pasien, c.reg_tipe_jkn, c.reg_tipe_rawat,
                 d.poli_nama, e.usr_name, f.jenis_nama,g.tipe_biaya_nama
                 from klinik.klinik_registrasi c join 
                 klinik.klinik_pembayaran a on c.id_pembayaran = a.pembayaran_id
                 join global.global_customer_user b on c.id_cust_usr = b.cust_usr_id
                 left join global.global_auth_poli d on d.poli_id = c.id_poli
                 left join global.global_auth_user e on e.usr_id = c.id_dokter
                 left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
                 left join global.global_tipe_biaya g on c.reg_tipe_layanan = g.tipe_biaya_id
                 where (c.reg_jenis_pasien=".TIPE_PASIEN_UMUM.") 
                 and c.reg_batal is null ";
            if($poliOP) $sql .= " and c.id_poli<>".QuoteValue(DPE_CHAR,$poliOP["poli_id"]);
            $sql .= " and ".$sql_where." order by c.reg_tanggal, c.reg_waktu desc";
            } 
             else 
            {  //Jika Rawat Inap
               $sql = "select a.pembayaran_id,a.pembayaran_total,a.pembayaran_flag, 
                   b.cust_usr_nama, b.cust_usr_kode,
                   c.id_dokter,c.reg_id,c.id_poli,c.reg_tanggal,c.reg_waktu,c.reg_keterangan,
  	               c.reg_status, c.reg_jenis_pasien, c.reg_tipe_jkn, c.reg_tipe_rawat,
                   d.poli_nama, e.usr_name, f.jenis_nama,g.tipe_biaya_nama
                   from 
                   klinik.klinik_pembayaran a 
                   join klinik.klinik_registrasi c  on c.id_pembayaran = a.pembayaran_id
                   join global.global_customer_user b on c.id_cust_usr = b.cust_usr_id
                   left join global.global_auth_poli d on d.poli_id = c.id_poli
                   left join global.global_auth_user e on e.usr_id = c.id_dokter
                   left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
                   left join global.global_tipe_biaya g on c.reg_tipe_layanan = g.tipe_biaya_id
                   where (c.reg_utama is NULL or c.reg_utama = '') and c.reg_jenis_pasien=".TIPE_PASIEN_UMUM." and a.id_dep =".QuoteValue(DPE_CHAR,$depId)."
                   and c.reg_batal is null ";
              if($poliOP) $sql .= " and c.id_poli<>".QuoteValue(DPE_CHAR,$poliOP["poli_id"]);
              $sql .= " and ".$sql_where." order by c.reg_tanggal, c.reg_waktu desc";
            
            }
            echo $sql;
            $rs = $dtaccess->Execute($sql);
            $dataTable = $dtaccess->FetchAll($rs); 
		
    } 
    
    if($_POST["btnExcel"])
    {
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=kasir.xls');
    }  
    
      
	        $tableHeader = "&nbsp;Pembayaran Pasien";
          $counterHeader = 0;

          if(!$_POST["btnExcel"])
          {
            $tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
            $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
            $counterHeader++;

          }
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
          $counterHeader++; 
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tagihan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
      	 for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

          $jumHeader= $counterHeader;
          if ($dataTable[$i]["reg_tipe_rawat"]=='J' || $dataTable[$i]["reg_tipe_rawat"]=='G')
          { //Rawat Darurat dan Jalan
          $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where 
                  id_reg=".QuoteValue(DPE_CHAR,$dataTable[$i]["reg_id"])."
                  and fol_lunas='n'";
          }
          else
          {  //Rawat Inap
          $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where 
                  id_pembayaran=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"])."
                  and fol_lunas='n'";
          
          }
          $rs = $dtaccess->Execute($sql);
          $total = $dtaccess->Fetch($rs);   
          
          if($dataTable[$i]["reg_jenis_pasien"]==TIPE_PASIEN_UMUM)
          {
           if($_POST["dep_konf_bulat_ribuan"]=="y"){
              $totalint = substr(currency_format($total["total"]),-3);   
              $selisih = 1000-$totalint; 
              if($selisih<>1000){ $_POST["bulat"] = $selisih;} else $_POST["bulat"]=0;
              $totalHarga = $total["total"] + $_POST["bulat"];
           } else{  
              if($_POST["dep_konf_bulat_ratusan"]=="y") { 
                $totalint = substr(currency_format($total["total"]),-2);
                $selisih = 100-$totalint;  
                if($selisih<>100){ $_POST["bulat"] = $selisih;} else $_POST["bulat"]=0; 
                $totalHarga = $total["total"] + $_POST["bulat"];
              } else {
                $totalHarga = $total["total"];
              } 
           }
         }
               
			    $editPage = "kasir_pemeriksaan_proses.php?id_dokter=".$dataTable[$i]["id_dokter"]."&id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"];
			    $viewPage = "kasir_lihat_proses.php?id_dokter=".$dataTable[$i]["id_dokter"]."&id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"];
			    $kurangBayarPage = "kasir_pemeriksaan_kurang_bayar.php?id_dokter=".$dataTable[$i]["id_dokter"]."&id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"];
          $piutangPage = "kasir_pemeriksaan_view.php?piutang=1&id_dokter=".$dataTable[$i]["id_dokter"]."&id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"];
        
        //if(!$_POST["btnExcel"]){  
         //   if($dataTable[$i]["pembayaran_yg_dibayar"]<$dataTable[$i]["pembayaran_total"] && $dataTable[$i]["reg_jenis_pasien"]==PASIEN_BAYAR_UMUM) {
      	//		$tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
         //   } else {
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cari.png" alt="Bayar" title="Bayar" border="0" onclick="javascript: return CekData('.QuoteValue(DPE_CHAR,$cust[$data[$i]]).');" /></a>';               
      	//		}
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
       //   }
        	 
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			if($dataTable[$i]["cust_usr_kode"]=="100" || $dataTable[$i]["cust_usr_kode"]=="500")
          {
            $sql = "select reg_keterangan, fol_keterangan from klinik.klinik_folio a
                    left join klinik.klinik_registrasi b on a.id_reg = b.reg_id where a.id_reg =".QuoteValue(DPE_CHAR,$dataTable[$i]["reg_id"]);
            $rs = $dtaccess->Execute($sql);
            $namalain = $dtaccess->Fetch($rs);
            if($namalain["fol_keterangan"]=='' || $namalain["fol_keterangan"]==null)
            {          
      			   $tbContent[$i][$counter][TABLE_ISI] = $namalain["reg_keterangan"];
            }
            else
            {
      			$tbContent[$i][$counter][TABLE_ISI] = $namalain["fol_keterangan"];          
           }
          }
          else //jIKA BUKAN KODE 500
          {
    			   $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          }
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          //Label Tipe Rawat
          $TipeRawat["J"] = "Rawat Jalan";
    			$TipeRawat["I"] = "Rawat Inap";
          $TipeRawat["G"] = "Rawat Darurat";
          $tbContent[$i][$counter][TABLE_ISI] = $TipeRawat[$dataTable[$i]["reg_tipe_rawat"]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalHarga);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          /*
          if($dataTable[$i]["reg_jenis_pasien"]==PASIEN_BAYAR_BPJS && $dataTable[$i]["reg_tipe_jkn"]==PASIEN_BAYAR_BPJS_NON_PBI){
          $tbContent[$i][$counter][TABLE_ISI] = "JKN Non PBI";
          } elseif($dataTable[$i]["reg_jenis_pasien"]==PASIEN_BAYAR_BPJS && $dataTable[$i]["reg_tipe_jkn"]==PASIEN_BAYAR_BPJS_PBI){
          $tbContent[$i][$counter][TABLE_ISI] = "JKN PBI";
          } elseif($lunas[$data[$i]]=='n' || $lunas[$data[$i]]==''){
          $tbContent[$i][$counter][TABLE_ISI] = "Belum Lunas";                         
          } elseif($dataTable[$i]["pembayaran_flag"]=='k'){
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas (Kurang Bayar)";
          } elseif($dataTable[$i]["pembayaran_flag"]=='p'){
          $tbContent[$i][$counter][TABLE_ISI] = "Piutang";
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
          }
          */
                  
          if($totalHarga>0){
          $tbContent[$i][$counter][TABLE_ISI] = "Belum Lunas";                         
          }  else {
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
          }
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          unset($totalHarga);
      } 
    
     // cari jenis pasien e
     $sql = "select * from global.global_jenis_pasien where (jenis_id='2') and jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs);
	
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php if($cetak=="y"){ ?>
//    if(confirm('Cetak Invoice?')) 
       BukaWindow('tutup_kasir_cetak.php?tgl_awal=<?php echo $_POST["tanggal_awal"];?>&tgl_akhir=<?php echo $_POST["tanggal_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&js_biaya=<?php echo $_POST["js_biaya"];?>&jbayar=<?php echo $_POST["jbayar"]?>', '_blank');
	 document.location.href='<?php echo $thisPage;?>';
<?php } ?> 







<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <body class="nav-sm">
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
                <h3>Manajemen</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Pembayaran </h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" class="">
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Masuk(DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input  id="tanggal_awal" name="tanggal_awal" type='text' class="form-control" value="<?php echo $_POST["tanggal_awal"] ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal Masuk(DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  id="tanggal_akhir" name="tanggal_akhir"  type='text' class="form-control" value="<?php echo $_POST["tanggal_akhir"] ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
            
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
						<input type="text" name="cust_usr_kode" id="cust_usr_kode" class="form-control" value="<?php echo $_POST["cust_usr_kode"];?>">
						
						
				    </div>
            
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
						<input type="text" name="cust_usr_nama" id="cust_usr_nama" class="form-control" value="<?php echo $_POST["cust_usr_nama"];?>">
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat </label>
							<select name="reg_tipe_rawat" class="select2_single form-control" id="reg_tipe_rawat"> 
        					<!--<option value="--" <?php if('--'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Semua Tipe Rawat";?></option> -->
        					<option value="J" <?php if('J'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Rawat Jalan";?></option>
        					<option value="I" <?php if('I'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Rawat Inap";?></option>
        					<option value="G" <?php if('G'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Rawat Darurat";?></option>
			    		</select>
				    </div>
            
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar </label>
						
							<select name="reg_jenis_pasien" class="select2_single form-control" id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          					<!--<option value="" >[ Pilih Cara Bayar ]</option>-->
          					<?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
          					<option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["reg_jenis_pasien"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
						  <?php } ?>
			    		</select>
				    </div>
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-success" style="display:none;">
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-success">
				    </div>
					<div class="clearfix"></div>
					<? if($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]){?>
					<?}?>
					<? if ($_x_mode == "Edit"){ ?>
					<?php echo $view->RenderHidden("kategori_tindakan_id","kategori_tindakan_id",$biayaId);?>
					<? } ?>
					
					<script type="text/javascript">
    				Calendar.setup({
       			 	inputField     :    "tanggal_awal",      // id of the input field
        			ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        			showsTime      :    false,            // will display a time selector
        			button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        			singleClick    :    true,           // double-click mode
        			step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    				});
    
    				Calendar.setup({
        			inputField     :    "tanggal_akhir",      // id of the input field
        			ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        			showsTime      :    false,            // will display a time selector
        			button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        			singleClick    :    true,           // double-click mode
        			step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    				});
					</script>
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
					         <table  class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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

</script>
<script type="text/javascript" language="JavaScript">
<? $plx->Run(); ?>

var mTimer;
function CekData(id){
var hasil;
  //alert(id);
  hasil=KurangBayar(id,'type=r');
  if (hasil != 0)
  {
     if(id <> '500' || id <> '100'){
     alert('Pasien ada tunggakan tanggal '+ hasil);
     return false;
     }
  } else
  {
    return true;
  }
  //SetPerawatan(id,loket,'type=r');
}
</script>

<? } ?>

















