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
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);                                                                                         
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
     $_POST["dep_posting_split"] = $konfigurasi["dep_posting_split"]; 
     $_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
     $_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];
     $_POST["dep_posting_beban"] = $konfigurasi["dep_posting_beban"];
     $_POST["dep_cetak_rincian"] = $konfigurasi["dep_cetak_rincian"];
     
     $_x_mode = "New";
     $thisPage = "kasir_pemeriksaan_view.php";
     $delPage = "kasir_pemeriksaan_proses.php?";

     $table = new InoTable("table","100%","left");

     
     if ($_GET["id_dokter"]) $_POST["id_dokter"]=$_GET["id_dokter"];
     if ($_GET["id_poli"]) $_POST["id_poli"]=$_GET["id_poli"];
     if ($_GET["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"]=$_GET["reg_jenis_pasien"];
     
	//UNTUK DATA AWAL
	if($_GET["id_reg"] || $_GET["pembayaran_id"]) 
  {
		$sql = "select a.reg_jenis_pasien, a.reg_tipe_rawat, a.reg_tipe_jkn, a.id_poli, a.id_dokter, a.id_cust_usr, a.id_perusahaan,
            a.id_jamkesda_kota, a.reg_tipe_layanan, a.id_poli, a.reg_tipe_paket, 
            a.reg_tipe_layanan, a.reg_shift, b.pembayaran_dijamin,  
            c.cust_usr_alamat, c.cust_usr_nama, c.cust_usr_kode, c.cust_usr_jenis_kelamin, 
            c.cust_usr_foto,  ((current_date - c.cust_usr_tanggal_lahir)/365) as umur, c.cust_usr_jkn,   
            d.fol_keterangan, e.perusahaan_diskon, e.perusahaan_plafon, f.* from  
            klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran b on b.pembayaran_id = a.id_pembayaran 
            join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
            left join klinik.klinik_folio d on d.id_reg=a.reg_id
            left join global.global_perusahaan e on e.perusahaan_id=a.id_perusahaan
            left join global.global_jamkesda_kota f on f.jamkesda_kota_id=a.id_jamkesda_kota
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId);
   $rs_pasien = $dtaccess->Execute($sql);
   $dataPasien= $dtaccess->Fetch($sql);
    
    $_POST['fol_id'] = $_GET["fol_id"];		
    $_POST["id_reg"] = $_GET["id_reg"];  
		$_POST["id_biaya"] = $_GET["biaya"]; 
    $_POST["pembayaran_id"] = $_GET["pembayaran_id"];

		$view->CreatePost($dataPasien);
		$lokasi = $ROOT."gambar/foto_pasien";
	}


     //AMBIL GRAND TOTAL
     /* Yang Lama ambil dari pembayaran
     $sql = "select a.*,b.usr_name,c.poli_nama from  klinik.klinik_folio a left join 
             global.global_auth_user b on a.id_dokter = b.usr_id
             left join global.global_auth_poli c on a.id_poli = c.poli_id
             left join global.global_auth_user_poli d on d.id_poli = a.id_poli 
             
			       where d.id_usr = ".QuoteValue(DPE_CHAR,$userId)." and a.fol_lunas='n' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by a.fol_waktu asc"; 
     */
     //  echo $sql;    
     
     if ($_POST["reg_tipe_rawat"]=='J' or $_POST["reg_tipe_rawat"]=='G')
     { // JIka Rawat Jalan atau Rawat Darurat
     $sql = "select a.*,b.usr_name,c.poli_nama from  klinik.klinik_folio a left join 
             global.global_auth_user b on a.id_dokter = b.usr_id
             left join global.global_auth_poli c on a.id_poli = c.poli_id
			       where  a.fol_lunas='n' and a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by a.fol_waktu asc"; 
    }
    else
    { // JIka Rawat Inap
     $sql = "select a.*,b.usr_name,c.poli_nama from  klinik.klinik_folio a left join 
             global.global_auth_user b on a.id_dokter = b.usr_id
             left join global.global_auth_poli c on a.id_poli = c.poli_id
			       where  a.fol_lunas='n' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
              order by a.fol_waktu asc"; 
    
    
    }
            //echo $sql;
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataTable = $dtaccess->FetchAll($rs_edit);
     
     
    for($i=0,$n=count($dataTable);$i<$n;$i++)
    {
 
              $total = $dataTable[$i]["fol_hrs_bayar"];
              $totalBiaya = $totalBiaya+$dataTable[$i]["fol_nominal"];
              $dijamin = $dataTable[$i]["fol_dijamin"];
              
              //Jika Paket Sementara ditutup
              //if($dataTable[$i]["biaya_paket"]=="n")
              //{
              //$totalNonPaket += $dataTable[$i]["fol_nominal"];
              //}
          //}
          $totalHarga+=$total;
          $minHarga = 0-$totalHarga;
          $totalDijamin+=$dijamin;
          //$grandTotalHarga = $totalHarga;
   } 
        
   //-- RUMUS PEMBULATAN dan Penambahan Uang Muka
    require_once('pembayaran_total_harga.php');
    
   //tampilan atas yang merah
   $grandTotalHarga = $totalHarga-$uangmuka["total"];   
   
   
   
   //echo "total ".$totalHarga."-".$inacbg["inacbg_topup"];
   
   if($uangmuka["total"]>0)
   {
     $retur = $uangmuka["total"] - $totalHarga;
     if($retur<0) $retur=0;
   } 	 


    
    if ($_POST["btnOk"])  //Jika klik tombol ganti data diatas
    {
      $sql = "update  klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
      
      $kembali = "kasir_pemeriksaan_proses.php?id_dokter=".$_POST["id_dokter"]."&reg_jenis_pasien=".$_POST["reg_jenis_pasien"]."&id_poli=".$_POST["id_poli"]."&id_reg=".$_POST["id_reg"]."&pembayaran_id=".$_POST["pembayaran_id"];
                  
                 
      header("location:".$kembali);
      exit();    
    }
	
	
	// Jika Klik tombol Bayar //
	if ($_POST["btnBayar"]) 
  {	
  
    // ---  AMBIL DATA AWAL YANG DIBUTUHKAN UNTUK SIMPAN PEMBAYARAN
    $sql = "select * from klinik.klinik_pembayaran where 
            id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $dataReg = $dtaccess->Fetch($sql);

    //--- AKHIR AMBIL DATA AWAL 

   //JIKA NAMA PENJAMIN BEDA MAKA SET FOLIO SEMUA DENGAN NAMA PENJAMIN
   if($_POST["fol_keterangan"])
   {
    $sql = "update klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
            where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   }

   
   
   
   //----     UPDATE STATUS REGISTRASI
    /* //SEMENTARA DITUTUP DULU NGGA USAH DIUPDATE STATUS REGISTRASINYA
    $sql = "select reg_id from klinik.klinik_registrasi where reg_utama = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $rs = $dtaccess->Execute($sql);
    $allReg = $dtaccess->FetchAll($rs);

    for($i=0,$n=count($allReg);$i<$n;$i++)
    {
  		$sql = "update klinik.klinik_registrasi set reg_waktu_pulang = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n', reg_status='E1',
              reg_tanggal_pulang=reg_tanggal where reg_id = ".QuoteValue(DPE_CHAR,$allReg[$i]["reg_id"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
    }
     
    //Update yg reg_utama
		$sql = "update klinik.klinik_registrasi set reg_waktu_pulang = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n', reg_status_kondisi='U', reg_status='E0',
            reg_tanggal_pulang=reg_tanggal where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);  
    $dtaccess->Execute($sql); */
    //--- AKHIR UPDATE STATUS REGISTRASI



    //Update klinik pembayaran kassa
    require_once('update_klinik_pembayaran_kassa.php');
    
    //-- INSERT PEMBAYARAN DET
    require_once('insert_pembayaran_det_kassa.php');
   //-- AKHIR INSERT PEMBAYARAN DET



   //-- AWAL PROSES UANG MUKA
   /* Pembayaran Uang Muka sementara ditutup
    require_once('pembayaran_uang_muka.php');
    */
    //-- AKHIR PROSES UANG MUKA         
    
    //---------   UPDATE KLINIK FOLIO UNTUK FOL DIBAYAR           
    $sql  = " update  klinik.klinik_folio set fol_dibayar = fol_nominal "; 
    $sql .= " , id_pembayaran_det = ".QuoteValue(DPE_CHAR,$pembDetId); //$pembDetId itu ID PEMBAYARAN DETAIL iinsertnya di insert_pembayaran_det_kassa.php
    $sql .= " , fol_dibayar_when = CURRENT_TIMESTAMP where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and fol_lunas='n'";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
    // --- AKHIR UPDATE KLINIK FOLIO fol_dibayar=fol_nominal 
    
     //Jika ada penjualan maka dibikin sudah lunas
          $sql = "update apotik.apotik_penjualan set 
                penjualan_terbayar ='y'
                where id_fol in (select fol_id from klinik.klinik_folio where id_pembayaran_det = ".QuoteValue(DPE_CHAR,$pembDetId).")";
       //  echo $sql; die();
         $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     
     //AWAL POSTING ke GL   
      /* Sementara ditutup untuk Posting GL nya
      require_once('posting_gl.php'); 
      */
     // AKHIR POSTING GL
                        
     //PERINTAH CETAK KWITANSI  
     
              
     $cetak = "y";
     
     // header("location:".$kembali);
  } // AKHIR PROSES PEMBAYARAN





       //DATA YANG DIGUNAKAN UNTUK VIEW DAN COMBO
		   
		   // buat ambil jenis bayar --
     	 $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_lowest<>'n' and jbayar_id = '01' order by jbayar_id asc";
		   $dataJenisBayar= $dtaccess->FetchAll($sql); 

       $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
		   $dataJenisBayar2= $dtaccess->FetchAll($sql);              
             

     $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_id asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTipeLayanan = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJenis = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKota = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from global.global_perusahaan order by perusahaan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPerusahaan = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from global.global_jkn order by jkn_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJkn = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_detail_paket";
     $rs = $dtaccess->Execute($sql);
     $dataPaket = $dtaccess->Fetch($rs);
     
     
     // data Order Poli
     $sql = "select reg_id,poli_nama,c.usr_name,d.usr_name as dokter_sender,reg_who_update
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id
            left join global.global_auth_user c on a.id_dokter = c.usr_id
            left join global.global_auth_user d on a.reg_dokter_sender = d.usr_id
            where a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and a.id_pembayaran =".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
     $sql .= " order by reg_tanggal, reg_waktu asc";
 		 $dataorderPoli= $dtaccess->FetchAll($sql);
     

     $tableHeader = "&nbsp;Proses Pembayaran Pasien";
                      
?>

<script type="text/javascript">

var grandTotal = '<?php echo $grandTotalHarga;?>';
function GantiDiskon() 
{
     var bayaren = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var kembalian;
    
     dibayar_int=bayaren*1;  //total tagihan
     diskon_int=diskon*1;
     bayarnya = dibayar_int-diskon_int;
     kembalian = dibayar_int-diskon_int-bayarnya;
  //   alert(formatCurrency(bayarnya));
     document.getElementById('txtDibayar0').value = formatCurrency(bayarnya);
     document.getElementById('txtIsi').innerHTML = formatCurrency(kembalian);
 
}

function GantiPengurangan() 
{
     var bayaren = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalnya = document.getElementById('txtDibayar0').value.toString().replace(/\,/g,"");
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var kembalian;
    
     dibayar_int=bayaren*1;  //total tagihan
     diskon_int=diskon*1;
     totalbayar = totalnya*1;     
    
     kembalian = dibayar_int-diskon_int-totalbayar;
     document.getElementById('txtIsi').innerHTML = formatCurrency(kembalian);
 
}



var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
//     $next = "kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];


</script>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
 <!-- <body  onLoad="GantiPembulatan('<?php echo $_POST["txtBiayaPembulatan"];?>','<?php echo $grandTotalHarga;?>')"; >-->
  <body class="nav-sm">
    <div class="container body">
      <div class="main_container">
        
		<?php require_once($LAY."sidebar.php"); ?>

        <!-- top navigation -->
		<?php require_once($LAY."topnav.php"); ?>
		<!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Pembayaran Pasien</h3>
              </div>
            </div>

            <div class="clearfix"></div>
            <div class="row"> 
      <!-- ==== BARIS ===== -->
			<!-- ==== kolom kiri ===== -->
			<!-- ==== mulai form ===== -->
			<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
      <div class="col-md-6 col-sm-6 col-xs-12">

			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No. RM
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
              						<input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_kode"]; ?>">
                        </div>
                      </div>                      
                      <?php if($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500') { ?>	
                   		<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">                          
        						          <input readonly type="text" class="form-control" value="<?php echo $dataPasien["fol_keterangan"]; ?>">
                    			</div>
                      </div>
                      <?php } else { ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
      						          <input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_nama"]; ?>">
                  			</div>
                      </div>
                      <?php } ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
      						          <input readonly type="text" class="form-control" value="<?php echo nl2br($dataPasien["cust_usr_alamat"]); ?>">
                  			</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Sudah Terima Dari
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                            <input type="text" class="form-control" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                		        <input type="submit" name="btnOk" value="Ganti Data" class="submit" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Cara Bayar
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        	<select readonly name="reg_jenis_pasien" class="select2_single form-control" disabled id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
                  			 <option value="--">[ Pilih Cara Bayar ]</option>			
				              <?php for($i=0,$n=count($dataJenis);$i<$n;$i++){ ?>
         	        			 <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["reg_jenis_pasien"]==$dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
				            <?php } ?>
			            </select>
			            </div>
                      </div>
                      <!--
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe Layanan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        	<select readonly name="reg_tipe_layanan" class="select2_single form-control" disabled id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);">
                   			<option value="--">[ Pilih Tipe Layanan ]</option>			
				              <?php for($i=0,$n=count($dataTipeLayanan);$i<$n;$i++){ ?>
         	         			<option value="<?php echo $dataTipeLayanan[$i]["tipe_biaya_id"];?>" <?php if($_POST["reg_tipe_layanan"]==$dataTipeLayanan[$i]["tipe_biaya_id"]) echo "selected"; ?>><?php echo $dataTipeLayanan[$i]["tipe_biaya_nama"];?></option>
				            <?php } ?>
			            		</select>&nbsp;<input type="submit" hidden name="btnOk" value="Ganti Data" class="submit" />
                			</div>
                      </div>-->
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Klinik
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <select readonly class="select2_single form-control" name="id_poli" disabled id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                   				<option value="--">[ Pilih Klinik ]</option>			
				                <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
         	        			 <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
				               <?php } ?>
				               </select>
				            </div>
                      </div>
                      
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jenis Bayar
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <select name="id_jbayar" class="select2_single form-control" id="id_jbayar" onKeyDown="return tabOnEnter(this, event);">		
                   					<?php if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php } ?>
                       				<?php $counter = -1;
                       				for($i=0,$n=count($dataJenisBayar2);$i<$n;$i++)
                               {
                           			unset($spacer); 
                    						$length = (strlen($dataJenisBayar2[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
                    						for($j=0;$j<$length;$j++) $spacer .= "..";  
                            			?>                                                                      
                     	  			<option value="<?php echo $dataJenisBayar2[$i]["jbayar_id"];?>" <?php if($_POST["id_jbayar"]==$dataJenisBayar2[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar2[$i]["jbayar_nama"];?></option>
            				    	<?php } ?>
            			     </select>
				            </div>
                      </div>

					 
                  </div>
                </div>
        			  <!-- ==== //panel putih ===== -->
        			  <!-- ==== panel putih ===== -->
        		    <!-- gk butuh foto pasien	
        				<div class="x_panel">
                  <div class="x_title">
                    <h2>Foto Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <td width= "5%" align="center" class="tablecontent" rowspan="10"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               		</div>					  
                  </div>
                </div>  -->

			  </div>
			  <!-- ==== // kolom kiri ===== -->
			  
        
          <?           
            $ttotal = currency_format($grandTotalHarga); //Ini Default Pengisian dari Text Box total pembayaran
            $totalPembayaran = $grandTotalHarga;     //Ini Tulisan Merah Total
          ?>
          
			  <!-- ==== kolom kanan ===== -->
              <div class="col-md-6 col-sm-6 col-xs-12">            
              <div class="x_panel">
                  <div class="x_title">
                    <h2>Total Tagihan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                           <table border="0">
                           <tr>         
            						     <td width= "40%" align="center" class="tablecontent" rowspan="5"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga-$totalPembayaran);?></span></font></td>
                           </tr>
                           </table>
            						</div>
                      </div>
                      
                       <div class="form-group">                        
                        <div class="col-md-6 col-sm-6 col-xs-12">           
                        <table border="0"> 
                        <tr>
                           <td class="tablecontent" align="center">&nbsp;</td>         
                           <td width= "60%" align="right" class="tablecontent-odd"><b>Diskon</b> </td>
                           <td class="tablecontent" align="center">&nbsp;:&nbsp;</td> 
                           <td class="tablecontent" colspan="4">          
                             <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","30","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiDiskon();');?>
                           </td>
                        </tr>        
                        <tr>
                           <td class="tablecontent" align="center">&nbsp;</td>         
                           <td width= "60%" align="right" class="tablecontent-odd"><b>Total Pembayaran</b> </td>
                           <td class="tablecontent" align="center">&nbsp;:&nbsp;</td> 
                           <td class="tablecontent" colspan="4">
                            <?php echo $view->RenderTextBox("txtDibayar[0]","txtDibayar0","30","30",$ttotal,"curedit", "",true,'onChange=GantiPengurangan();');?></td>
                        </tr>           
                     </table>
                     <br><br>
            				       <td width="50%" align="center">
                           			<?php if($dataTable){ ?>
                                 <input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="submit">     
                           			<?php } ?>
            				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
            				       </td>				      
                      </div>				 						 
                  </div>
                </div>       
              </div>
           </div>   
       
       
       
       
       
   <!-- TAMPILAN DATA ORDER --> 
   <!--                     
   <div class="x_panel">
    <div class="x_content">
    <fieldset>
     <legend><strong>Data Order</strong></legend>
     <div id="kasir">
    <table style="width: 100%;"  role="grid" class="table table-striped table-bordered" cellspacing="0" width="100%">
    <tr class="tablesmallheader">
            <td width="1%" align='center'>No</td>
            <td width="15%" align='center'>Poli</td>
            <td width="15%" align='center'>Dokter</td>
            <td width="3%" align='center'>Telah Dilayani</td>                            
        </tr>
      	<?php for($i=0,$n=count($dataorderPoli);$i<$n;$i++) { 
              $sql = "select fol_id from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$dataorderPoli[$i]["reg_id"]);
              $datalayani = $dtaccess->Fetch($sql); ?>                                                                      
      <!--
        <tr class="tablecontent-odd">
            <td width="1%" align='center'><?php echo ($i+1)."."; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["poli_nama"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["usr_name"]; ?></td>
            <?php if(!$datalayani) {?>
            <td width="3%" align='center'>&nbsp;</td>                            
            <?php }else{ ?>
            <td width="3%" align='center'><img hspace="2" width="20" height="20" src="<?php echo $ROOT.'gambar/aktif.png';?>" /></td>
            <?php } ?> 
        </tr>
        <?php } ?>  
     </table>
     </div>
     </fieldset>
     </div>
  </div> -->
              
              
              
              
              

              
              
                

			  <!-- ==== // KHUSUS BUTTON ===== -->
              </div>
                               
                  <div class="x_content">
                      <div class="form-group">
                        <fieldset>
         <legend><strong>Data Tagihan Yang Belum Dibayar</strong></legend>
         <div id="kasir">
            <table style="width: 100%;" role="grid" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <tr class="tablesmallheader">
                  <td width="3%" align='center'>No</td>
                  <td width="20%" align='center'>Layanan</td>
                  <td width="12%" align='center'>Klinik/Penunjang</td>
    			        <td width="17%" align='center'>Nama Dokter</td>
                  <td width="10%" align='center'>Biaya</td>
                  <td width="5%" align='center'>Quantity</td>
                  <td width="10%" align='center'>Tagihan</td>
    						  </tr>
    						  
    						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
						  
                        <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
                                || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
                                || $dataTable[$i]["fol_jenis"]=='I'){
                         $sql = "select c.item_nama, a.* ,satuan_nama
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                                $rs = $dtaccess->Execute($sql); 
                                $dataFarmasidetail  = $dtaccess->FetchAll($rs); 
                                 }         
                                
                         if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA' 
                              || $dataTable[$i]["fol_jenis"]=='RG' || $dataTable[$i]["fol_jenis"]=='RI' ){
                         $sql = "select c.item_nama, a.* ,satuan_nama
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql);
                                $dataReturdetail  = $dtaccess->FetchAll($rs);     }      ?> 
                                	
              <tr class="tablecontent-odd">
                <td width="3%"><?php echo ($i+1).".";?></td>
                <td width="20%">
                    <?php if($dataTable[$i]["fol_jenis"]=="O" || $dataTable[$i]["fol_jenis"]=="OA" || $dataTable[$i]["fol_jenis"]=="OG" || 
                             $dataTable[$i]["fol_jenis"]=="OI" || $dataTable[$i]["fol_jenis"]=="R" || $dataTable[$i]["fol_jenis"]=="RA" || 
                             $dataTable[$i]["fol_jenis"]=="RA" || $dataTable[$i]["fol_jenis"]=="RG" || $dataTable[$i]["fol_jenis"]=="RI"){
                            echo $dataTable[$i]["fol_nama"]." (".$dataTable[$i]["fol_catatan"].")";
                          } else echo $dataTable[$i]["fol_nama"];?>
                </td>
                <td width="12%"><?php echo $dataTable[$i]["poli_nama"];?></td>
				        <td width="17%"><?php echo $dataTable[$i]["usr_name"];?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
                <td width="5%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]);?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"])?></td>
						  </tr>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I' || $dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){  ?>

       <tr class="garis_atas garis_bawah"> 
<?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I') {
                              $sql = "select count(penjualan_detail_id) as total
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                              $rs = $dtaccess->Execute($sql);
                              $totalitem = $dtaccess->Fetch($rs);        
            }
            if($dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){
                               $sql = "select count(retur_penjualan_detail_id) as total
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql); 
            } ?>         
         <td align="left" rowspan="<?php echo $totalitem["total"]+1;?>" ></td>
        <td align="left">Nama Item/Obat</td>
        <td align="right">Harga Satuan</td>
        <td align="right">Quantity</td>                             
        <td align="right">Total</td>
        <td align="right"></td>
	    </tr>     

    <?php } ?>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I'){  ?>
    
    <?php for($x=0,$y=count($dataFarmasidetail);$x<$y;$x++) {?>
       <tr>

          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>
        <td align="right"></td>
       </tr>     
       <?php } ?>                 
  
       <?php } ?>
           <?php if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA'
           || $dataTable[$i]["fol_jenis"]=='RI' ||$dataTable[$i]["fol_jenis"]=='RG'){ ?>                        
    <?php for($x=0,$y=count($dataReturdetail);$x<$y;$x++) {?>
       <tr class="garis_atas garis_bawah">
          <td align="left"> -  <?php echo $dataReturdetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>     
       <?php } ?>                 
              
           <? } ?>        
     </tr>
     <?php 

          $totalPembayaran += $dataTable[$i]["fol_nominal"]; 
          $totalHarga=$totalBiaya-$dijaminHarga;
          if ($totalHarga<0) $totalHarga=0;
     
     														?>						  
						  			<?php } ?>
              
              						<tr>
                  				<td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Tagihan</b></td>              
                  				<td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($totalBiaya)."</b>";?></td>
						  		</tr>
 							</table>
							</div>
     					</fieldset>                          
                      </div>
                  </div>				 						 
                </div>

              <?php echo $view->RenderHidden("konf_reg_id","konf_reg_id",$_POST["konf_reg_id"]);?> 
              <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandTotalHarga;?>" />
              <input type="hidden" name="total_dijamin" id="total_dijamin" value="<?php echo $totalDijamin;?>" />
              <input type="hidden" name="total_biaya" id="total_biaya" value="<?php echo $totalBiaya;?>" /> 
              <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" /> 
              <input type="hidden" name="txtcek" id="txtcek" value="<?php echo $_POST["txtcek"]; ?>">
              <input type="hidden" name="txtTotalDibayar" id="txtTotalDibayar" value="<?php echo $totalHarga?>">
              <input type="hidden" name="txtKembalian" id="txtKembalian" value="<?php echo $_POST["txtHargaTotal"]; ?>">
              <input type="hidden" name="pembayaran_id" id="pembayaran_id" value="<?php echo $_POST["pembayaran_id"]; ?>">
              <input type="hidden" name="bayar" id="bayar" value="<?php echo $grandTotalHarga;?>" />
              <script>document.frmEdit.txtDibayar0.focus();</script>
              <input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
              <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
              <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>
              <input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>
              <input type="hidden" name="fol_id" value="<?php echo $_GET["fol_id"]; ?>"/>
              <input type="hidden" name="biaya_id" value="<?php echo $_GET["jenis"]; ?>"/>
              <input type="hidden" name="waktu" value="<?php echo $_GET["waktu"]; ?>"/>
              <input type="hidden" name="dep_bayar_reg" value="<?php echo $_POST["dep_bayar_reg"]; ?>"/>
              <input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"]; ?>"/>
              <input type="hidden" name="reg_tipe_layanan" value="<?php echo $_POST["reg_tipe_layanan"]; ?>"/>
              <input type="hidden" name="reg_tipe_rawat" value="<?php echo $_POST["reg_tipe_rawat"]; ?>"/>
              <input type="hidden" name="id_poli" value="<?php echo $_POST["id_poli"]; ?>"/>
              <input type="hidden" name="id_dokter" value="<?php echo $_POST["id_dokter"]; ?>"/>
              <input type="hidden" name="retur" value="<?php echo $retur; ?>"/>
              <input type="hidden" name="op" value="<?php echo $op["poli_id"]; ?>"/>
              <input type="hidden" name="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"]; ?>"/>
              <input type="hidden" name="reg_tipe_paket" value="<?php echo $_POST["reg_tipe_paket"]; ?>"/>
              <input type="hidden" name="dep_posting_beban" value="<?php echo $_POST["dep_posting_beban"];?>"/>
              <input type="hidden" name="operasi" value="<?php echo $operasi["preop_id"];?>"/>
              <input type="hidden" name="dep_cetak_rincian" value="<?php echo $_POST["dep_cetak_rincians"];?>"/>
              

			</form>	<!-- ==== Akhir form ===== -->
			<!-- ==== // kolom kanan ===== -->
            </div> <!-- ==== // BARIS ===== -->
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>
<script type="text/javascript">
/*
         function inputKwitansi($bayardet){
              //var id_det = $bayardet;
                    $.messager.prompt('Silahkan input', 'Nomor Kwitansi:', function(r){
                         if (r){
                              //alert(r);                             
                              $.post('update_slip.php',{id_det:$bayardet,pembayaran_det_slip:r},function(result){
                              if (result.success){
                                window.location.href='kasir_pemeriksaan_view.php';
                              } else {
                                $.messager.show({ // show error message
                                  title: 'Error',
                                  msg: result.errorMsg
                                });
                              }
                            },'json');
                         }
                    });
               
          }    */

<?php if($cetak=="y"){ ?>
//    if(confirm('Cetak Invoice?'))
      

       <?php if ($_POST["reg_tipe_rawat"]=='J' || $_POST["reg_tipe_rawat"]=='R') { ?> //JIKA RAWAT JALAN
       <?php if($_POST["dep_cetak_rincian"]=='y'){ ?>
       BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
       <?php } else if($_POST["dep_cetak_rincian"]=='a'){ ?>
       BukaWindow('cetak_kwitansi_rincian_a5.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
       <?php } else { ?>
           <? if($_POST["reg_jenis_pasien"]=='10'){ ?>
              BukaWindow('cetak_kwitansi_global_fund.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
           <?php } else { ?> 
              BukaWindow('cetak_kwitansi.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
           <?php } ?> 
       <?php } ?> 
       //inputKwitansi('<?php echo $pembDetId;?>'); 
       document.location.href='<?php echo $thisPage;?>';
       <?php } else ?>
       <?php if ($_POST["reg_tipe_rawat"]=='G') { ?> //JIKA RAWAT DARURAT
       <?php if($_POST["dep_cetak_rincian"]=='y'){ ?>
       BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
       <?php } else { ?>
           <? if($_POST["reg_jenis_pasien"]=='10'){ ?>
              BukaWindow('cetak_kwitansi_global_fund.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
           <?php } else { ?> 
              BukaWindow('cetak_kwitansi_igd.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
           <?php } ?> 
       <?php } ?> 
       //inputKwitansi('<?php echo $pembDetId;?>'); 
       document.location.href='<?php echo $thisPage;?>';
       <?php } else ?>
       <?php if ($_POST["reg_tipe_rawat"]=='I') { ?> //JIKA RAWAT INAP
       <?php if($_POST["dep_cetak_rincian"]=='y'){ ?>
       BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
       <?php } else { ?>
           <? if($_POST["reg_jenis_pasien"]=='10'){ ?>
              BukaWindow('cetak_kwitansi_global_fund.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
           <?php } else { ?> 
              BukaWindow('cetak_kwitansi_irna.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]);?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $pembDetId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
           <?php } ?> 
       <?php } ?> 
        //inputKwitansi('<?php echo $pembDetId;?>'); 
        
        
       document.location.href='<?php echo $thisPage;?>';
       <?php } ?>
   
<?php } ?>


</script>
  </body>
</html>