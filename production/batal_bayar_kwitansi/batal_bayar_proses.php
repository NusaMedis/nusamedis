<?php        
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."datamodel.php");
require_once($LIB."currency.php");
require_once($LIB."tampilan.php");

$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();

$jamSekarang = date("H:i:s");
     // KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
$_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];

       /*if(!$auth->IsAllowed("sirs_flow_batal_bayar",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("sirs_flow_batal_bayar",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
        }*/

        $_x_mode = "New";
        $thisPage = "batal_bayar_view.php";
        $delPage = "batal_bayar_proses.php?";

        $table = new InoTable("table","100%","left");

     //AMBIL DARI TOMBOL GANTI DATA di KLIK
      //AMBIL DARI TOMBOL GANTI DATA di KLIK
        if ($_GET["id_dokter"]) $_POST["id_dokter"]=$_GET["id_dokter"];
        if ($_GET["id_pelaksana"]) $_POST["id_pelaksana"]=$_GET["id_pelaksana"];
     //if ($_GET["id_poli"]) $_POST["id_poli"]=$_GET["id_poli"];
        if ($_GET["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"]=$_GET["reg_jenis_pasien"];
        if ($_GET["reg_shift"]) $_POST["reg_shift"]=$_GET["reg_shift"];    
        
        if ($_GET["id_pembayaran_det"]) $_POST["id_pembayaran_det"] = $_GET["id_pembayaran_det"];
     //else $_POST["reg_shift"]=1; 

//HAPUS PENATA JASA
        if ($_GET["delReg"]) 
        { 
         $regId = $_GET["id_reg"];
         
     // hapus registrasi salah untuk penata jasa --
         $sql = "delete from klinik.klinik_registrasi where reg_id =".QuoteValue(DPE_CHAR,$regId);
         $rs = $dtaccess->Execute($sql,DB_SCHEMA);
         $kembali = "batal_bayar_view.php";
         header("location:".$kembali);
         exit();    
       }
       
       if($_GET["id_reg"] || $_GET["pembayaran_id"]) {
        $sql = "SELECT a.reg_jenis_pasien, a.reg_shift,a.reg_tipe_layanan,a.id_poli,cust_usr_alamat, cust_usr_nama, cust_usr_kode, b.cust_usr_jenis_kelamin, cust_usr_foto, a.id_dokter,
        ((current_date - b.cust_usr_tanggal_lahir)/365) as umur, a.id_cust_usr, a.id_perusahaan, 
        a.id_jamkesda_kota, b.cust_usr_jkn, id_jbayar, c.who_when_update as petugas from  klinik.klinik_registrasi a 
        left join klinik.klinik_pembayaran_det c on a.id_pembayaran = c.id_pembayaran
        join  global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
        where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
    //echo $sql;
        $dataPasien= $dtaccess->Fetch($sql);

        $sql = "SELECT jbayar_nama as nama from global.global_jenis_bayar where jbayar_id = ".QuoteValue(DPE_CHAR, $dataPasien["id_jbayar"]);
        $jenis = $dtaccess->Fetch($sql);

        if(!$jenis['nama']){
          $sql = "SELECT perusahaan_nama as nama from global.global_perusahaan where perusahaan_id = ".QuoteValue(DPE_CHAR, $dataPasien["id_jbayar"]);
          $jenis = $dtaccess->Fetch($sql);

          if(!$jenis['nama']){
            $jenis['nama'] = $dataPasien["id_jbayar"];
          }
        }
        
        $_POST['fol_id'] = $_GET["fol_id"];
        $_POST["id_reg"] = $_GET["id_reg"]; 
        $_POST["id_biaya"] = $_GET["biaya"]; 
        $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
        $_POST["reg_status"] = $dataPasien["reg_status"];
        $_POST["reg_shift"] = $dataPasien["reg_shift"];
        $_POST["reg_tipe_layanan"] = $dataPasien["reg_tipe_layanan"];
        $_POST["id_pembayaran_lama"] = $dataPasien["id_pembayaran"];
        $_POST["reg_utama"] = $_GET["id_reg"];
        if (!$_POST["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
        $_POST["id_poli"] = $dataPasien["id_poli"];
        if (!$_POST["id_dokter"]) $_POST["id_dokter"] = $dataPasien["id_dokter"];
        if (!$_POST["id_pelaksana"]) $_POST["id_pelaksana"] = $dataPasien["id_pelaksana"];
        if (!$_POST["id_perusahaan"]) $_POST["id_perusahaan"] = $dataPasien["id_perusahaan"];
        if (!$_POST["id_jamkesda_kota"]) $_POST["id_jamkesda_kota"] = $dataPasien["id_jamkesda_kota"];
        if (!$_POST["cust_usr_jkn"]) $_POST["cust_usr_jkn"] = $dataPasien["cust_usr_jkn"];
        $_POST["reg_umur_bulan"] = $dataPasien["reg_umur_bulan"];
        $_POST["reg_kode_urut"] = $dataPasien["reg_kode_urut"];
        $_POST["reg_kode_trans"] = $dataPasien["reg_kode_trans"];
        $_POST["reg_rujukan_id"] = $dataPasien["reg_rujukan_id"];
        $_POST["reg_umur"] = $dataPasien["reg_umur"];
        $_POST["reg_umur_hari"] = $dataPasien["reg_umur_hari"];
        $_POST["reg_umur_bulan"] = $dataPasien["reg_umur_bulan"];
        $_POST['petugas_kasir'] = $dataPasien["petugas"];
        $_POST['jbayar_nama'] = $jenis['nama'];
        
        $sql = "select fol_keterangan from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $dataKet = $dtaccess->Fetch($sql);
        $_POST["fol_keterangan"] = $dataKet["fol_keterangan"];
        
        $lokasi = $ROOT."gambar/foto_pasien";
        
        $sql = "select sum(pembayaran_total) as total, sum(pembayaran_yg_dibayar) as dibayar from klinik.klinik_pembayaran a
        where pembayaran_flag = 'n' and pembayaran_jenis = 'C' and id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]); 
        $dataCicilan = $dtaccess->Fetch($sql);
        
        $sisaCicilan = $dataCicilan["total"] - $dataCicilan["dibayar"];   
      }
      
      $sql = "select a.*,b.usr_name as dokter_nama from  klinik.klinik_folio a
      left join global.global_auth_user b on a.id_dokter  = b.usr_id
      where a.fol_lunas='y' 
      and a.id_pembayaran_det = ".QuoteValue(DPE_CHAR,$_GET["id_pembayaran_det"])." 
      and a.id_dep =".QuoteValue(DPE_CHAR,$depId)."
      order by fol_waktu asc"; 
     //and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataTable = $dtaccess->FetchAll($rs_edit);
      
      $sql = "select * from klinik.klinik_pembayaran_det where pembayaran_det_id=".QuoteValue(DPE_CHAR,$_GET["id_pembayaran_det"]);
      $rs = $dtaccess->Execute($sql);
      $dataPembayaranDet = $dtaccess->Fetch($rs);

      $sql = "select sum(pembayaran_det_dibayar) as total from klinik.klinik_pembayaran_det where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
      $TotalBayar = $dtaccess->Fetch($sql);
      
     //Ambil Data Multipayment
     if ($dataPembayaranDet["id_pembayaran_det_multipayment"]) // JIKA MULTIPAYMENT
     {
       $sql = "select a.*,jbayar_nama,perusahaan_nama from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar left join global.global_perusahaan c on c.perusahaan_id = a.id_jbayar where 
       id_pembayaran_det_multipayment=".QuoteValue(DPE_CHAR,$dataPembayaranDet["id_pembayaran_det_multipayment"]);
       $rs = $dtaccess->Execute($sql);
       $dataMultiPayment = $dtaccess->FetchAll($rs);
     }
      //echo $sql;
     for($i=0,$n=count($dataTable);$i<$n;$i++){
      
          //if($dataTable[$i]["fol_jumlah"]){
            //$total = $dataTable[$i]["fol_jumlah"]*$dataTable[$i]["fol_nominal"];
          //}else{
      $total = $dataTable[$i]["fol_hrs_bayar"];
          //}
      $totalHarga+=$total;
      $minHarga = 0-$totalHarga;
      
      $grandTotalHarga = $totalHarga;
    }         
    
    if($_POST["btnBatal"]){
      $sql = "select sum(deposit_history_nominal) as total,id_cust_usr from klinik.klinik_deposit_history where deposit_history_flag != 'M' and
      id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." group by id_cust_usr";
        // echo $sql;
      $dataDeposit = $dtaccess->Fetch($sql);

      $SaldoDeposit = str_replace('-', '', $dataDeposit['total']);
      
      
        //$nilaiDeposit =  $dataDeposit["deposit_history_nominal"]
        //UPDATE KLINIK DEPOSIT
      $sql = "update klinik.klinik_deposit set deposit_nominal = ".QuoteValue(DPE_NUMERIC,$SaldoDeposit)." where id_cust_usr =".QuoteValue(DPE_CHAR,$dataDeposit["id_cust_usr"]);
        // echo $sql;die();
      $dtaccess->Execute($sql);
      
        //HAPUS DEPOSIT HISTORY
      $sql = "delete from klinik.klinik_deposit_history where deposit_history_flag != 'M' and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
      $dtaccess->Execute($sql);
      
        //HAPUS JURNAL DI AKUNTANSI        
      $sql = "delete from gl.gl_buffer_transaksi where id_pembayaran_det =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran_det"]);
      $dtaccess->Execute($sql);

      $sql = "delete from ar_ap.ar_trans where id_pembayaran = ".QuoteValue(DPE_CHAR, $_POST['id_pembayaran']);
      $dtaccess->Execute($sql);
      
        //HAPUS TRANSAKSI DI PEMBAYARAN DETAIL KALAU MULTIPAYMENT DIHAPUS SEMUA
        // if ($dataDet["id_pembayaran_det_multipayment"]) //Jika Mutipayment
        //      $sql = "delete from klinik.klinik_pembayaran_det where id_pembayaran_det_multipayment = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran_det"]);
        // else //jika tidak
      $sql = "delete from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);       
      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        //echo "masuk";
        //die();
       //untuk membuka menu item di apotik 
      $sql = "select fol_id from klinik.klinik_folio where
               id_pembayaran_det=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran_det"]);/*."
               and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);*/
               $dataFolio = $dtaccess->FetchAll($sql);

               for($i=0,$n=count($dataFolio);$i<$n;$i++)
               {  
                $sqlTerbayar = "update apotik.apotik_penjualan set 
                penjualan_terbayar = 'n' where id_fol = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);      
                $dtaccess->Execute($sqlTerbayar);
                unset($sqlTerbayar);
              } 
              
              $sql = "update klinik.klinik_folio set fol_lunas='n' where 
        id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);/*."
        and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);*/
        $dtaccess->Execute($sql,DB_SCHEMA);

        $sql = "select reg_tipe_rawat from klinik.klinik_registrasi where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $dataReg = $dtaccess->Fetch($sql);
        
        if ($dataReg["reg_tipe_rawat"] == 'I')
        {
          $sql = "update klinik.klinik_registrasi set reg_status ='I3' where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $reg = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $sql="delete from klinik.klinik_waktu_tunggu where id_reg=".QuoteValue(DPE_CHAR,$_POST['id_reg'])." and klinik_waktu_tunggu_status='K1' ";
          $result = $dtaccess->Execute($sql);
        }elseif ($dataReg['reg_tipe_rawat'] == 'J') {
          $sql = "update klinik.klinik_registrasi set reg_status ='E2' where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $reg = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        }elseif ($dataReg['reg_tipe_rawat'] == 'G') {
          $sql = "update klinik.klinik_registrasi set reg_status ='G2' where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $reg = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        }
        $sql="update klinik.klinik_waktu_tunggu set klinik_waktu_tunggu_status='',id_waktu_tunggu_status =''  where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and klinik_waktu_tunggu_status='K1'";
        $rs = $dtaccess->Execute($sql);
        
        $sql = "select * from klinik.klinik_pembayaran_det where
        pembayaran_det_id=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran_det"]);
        $rs = $dtaccess->Execute($sql);
        $dataDet = $dtaccess->Fetch($rs);
        //echo $sql;
        //die();
        
        $sql = "select * from klinik.klinik_pembayaran where 
        pembayaran_id=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
        $rs = $dtaccess->Execute($sql);
        $dataPemb = $dtaccess->Fetch($rs);
        
        
        
        $sql = "select * from global.global_customer_user where 
        cust_usr_id=".QuoteValue(DPE_CHAR,$dataPemb["id_cust_usr"]);
        $rs = $dtaccess->Execute($sql);
        $dataCust = $dtaccess->Fetch($rs);
        
        $total = $dataPemb["pembayaran_total"] - $dataDet["pembayaran_det_total"];
        $dibayar = $dataPemb["pembayaran_yg_dibayar"] - $dataDet["pembayaran_det_total"];
        $diskon = $dataPemb["pembayaran_diskon"] - $dataDet["pembayaran_det_diskon"];
        $diskonPersen = $dataPemb["pembayaran_diskon_persen"] - $dataDet["pembayaran_det_diskon_persen"];
        $hrsBayar = $dataPemb["pembayaran_hrs_bayar"] - $dataDet["pembayaran_det_hrs_bayar"];
        $bulat = $dataPemb["pembayaran_pembulatan"] - $dataDet["pembayaran_det_pembulatan"];
        $charge = $dataPemb["pembayaran_service_cash"] - $dataDet["pembayaran_det_service_cash"];
        
        // if($_POST["reg_jenis_pasien"]=="2"){
        $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', pembayaran_total = '0', 
        pembayaran_yg_dibayar = '0', pembayaran_hrs_bayar = '".StripCurrency($hrsBayar)."',
        pembayaran_service_cash='".StripCurrency($charge)."', pembayaran_diskon='".StripCurrency(0)."',
        pembayaran_diskon_persen='".StripCurrency($diskonPersen)."', pembayaran_pembulatan='".StripCurrency($bulat)."', 
        pembayaran_appv='n' where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
        // } else {
        // $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', pembayaran_total = '0', pembayaran_yg_dibayar = '0', 
        //         pembayaran_dijamin = '0',pembayaran_hrs_bayar = '0', pembayaran_service_cash=0, pembayaran_diskon=0, pembayaran_diskon_persen=0,
        //         pembayaran_pembulatan=0, pembayaran_subsidi=0, pembayaran_selisih_positif=0, pembayaran_selisih_negatif=0, pembayaran_appv='n' 
        //         where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
        // }
        //echo $sql; die();
        $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        
        $dbTable = "klinik.klinik_batal_bayar";
        $dbField[0] = "batal_bayar_id";
        $dbField[1] = "batal_bayar_tgl";
        $dbField[2] = "batal_bayar_create";
        $dbField[3] = "batal_bayar_keterangan";
        $dbField[4] = "batal_bayar_who_create";
        $dbField[5] = "id_pembayaran";
        $dbField[6] = "id_pembayaran_det";
        $dbField[7] = "batal_bayar_no_kwitansi";
        $dbField[8] = "batal_bayar_nama_pasien";
        $dbField[9] = "batal_bayar_no_rm";
        $dbField[10] = "batal_bayar_nominal";
        
        $batalId = $dtaccess->GetTransId();
        $dbValue[0] = QuoteValue(DPE_CHAR,$batalId);
        $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
        $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["batal_ket"]);
        $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
        $dbValue[6] = QuoteValue(DPE_CHAR,$dataDet["pembayaran_det_id"]);
        $dbValue[7] = QuoteValue(DPE_CHAR,$dataDet["pembayaran_det_kwitansi"]);
        $dbValue[8] = QuoteValue(DPE_CHAR,$dataCust["cust_usr_nama"]);
        $dbValue[9] = QuoteValue(DPE_CHAR,$dataCust["cust_usr_kode"]);
        $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($dataDet["pembayaran_det_total"]));
        
        $dbKey[0] = 0;
        
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        
        $dtmodel->Insert() or die("insert  error");
        
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
        
        //KALAU ADA DEPOSIT HISTORY DIKEMBALIKAN KEMBALI
        
        //AMBIL KLINIK DEPOSIT
        
        $kembali = "batal_bayar_view.php";
        header("location:".$kembali);
        exit();
      } 
      
      $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
      $rs_edit = $dtaccess->Execute($sql);
      $row_edit = $dtaccess->Fetch($rs_edit);
      $dtaccess->Clear($rs_edit);

      $_POST["dep_konf_reg"] = $row_edit["dep_konf_reg"];
      $_POST["dep_konf_kons"] = $row_edit["dep_konf_kons"];  
      
      $sql = "select a.* from klinik.klinik_kategori_tindakan_header a 
      left join klinik.klinik_biaya_poli c on 
      a.kategori_tindakan_header_id=c.id_kategori_tindakan_header
      where c.id_poli =".QuoteValue(DPE_CHAR,$_POST["id_poli"])."
      order by a.kategori_tindakan_header_nama"; 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataKategori = $dtaccess->FetchAll($rs_edit);    
      
      $sql = "select * from global.global_auth_user where (id_rol = '6' or id_rol='2') and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataDokter = $dtaccess->FetchAll($rs);       

      $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs);       

      $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataJenis = $dtaccess->FetchAll($rs);       

      $sql = "select * from global.global_shift order by shift_id";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataShift = $dtaccess->FetchAll($rs);       

      $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_nama";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataKota = $dtaccess->FetchAll($rs); 
      
      $sql = "select * from global.global_perusahaan order by perusahaan_nama";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPerusahaan = $dtaccess->FetchAll($rs); 
      
      $sql = "select * from global.global_jkn order by jkn_nama";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataJkn = $dtaccess->FetchAll($rs);

      $tableHeader = 'Proses Batal Bayar';
      
      ?>                                                                                                                                     

      <?php //echo $view->RenderBody("ipad_depans.css",true,"BATAL BAYAR KWITANSI"); ?>
      <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
      <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
      <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>     
      <script type="text/javascript" src="ajax.js"></script>
      <script src="<?php echo $ROOT;?>lib/script/selecttindakan1.js"></script>
      <script type="text/javascript">

        function CekBatal(){
          if(!document.getElementById('batal_ket').value){
            alert('Keterangan Batal Harus Diisi!!!');
            document.getElementById('batal_ket').focus();
            return false;
          }
          
          return true;
        }

        var grandTotal = '<?php echo $grandTotalHarga;?>';

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

<?php if($cetak=="y"){ ?>
//    if(confirm('Cetak Invoice?')) 
      // BukaWindow('penata_jasa_dot_cetak.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>','Kwitansi');
      document.location.href='<?php echo $thisPage;?>';
    <?php } ?>

  </script>
  <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
  <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>
  <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/kinetic-v3.js"></script>
  <script type="text/javascript" language="javascript" src="ajax.js"></script>
  <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.js"></script>
  <link rel="stylesheet" href="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.css" type="text/css" />

  <!--<body onLoad="GantiPengurangan('<?php echo $grandTotalHarga;?>',0)";>-->
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
              <div class="clearfix"></div>
              <!-- row filter -->
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>Proses Batal Bayar</h2>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <?php if($dataPasien) {  ?>
                        <form name="frmEdit" method="POST" autocomplete="off" action="<?php echo $_SERVER["PHP_SELF"]?>" >
                          <table width="100%" border="0" cellpadding="1" cellspacing="1">
                            <tr>
                             <td width="100%">
                               <fieldset>                                                     
                                 <legend><strong>Data Pasien</strong></legend>
                                 <div id="kasir">
                                  <table width="100%" border="0" class="table table-bordered" cellpadding="4" cellspacing="1">
                                    <tr>
                                     <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='7' || $dataPasien["reg_jenis_pasien"]=='18' || $dataPasien["reg_jenis_pasien"]=='26') { ?>
                                      <td width= "5%" align="center" class="tablecontent" rowspan="9"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                                    <?php } else {?>
                                     <td width= "5%" align="center" class="tablecontent" rowspan="8"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                                   <?php } ?>
                                   <td width= "15%" align="left" class="tablecontent">No. RM</td>
                                   <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
                                   <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='7' || $dataPasien["reg_jenis_pasien"]=='18' || $dataPasien["reg_jenis_pasien"]=='26') { ?>
                                     <td width= "40%" align="center" class="tablecontent" rowspan="9"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($TotalBayar['total']);?></span></font></td>
                                   <?php } else {?>
                                     <td width= "40%" align="center" class="tablecontent" rowspan="8"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($TotalBayar['total']);?></span></font></td>
                                   <?php } ?>
                                 </tr> 
                                 <tr>
                                   <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
                                   <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php if($dataPasien["umur"]) echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["umur"]." Tahun"; else echo $dataPasien["cust_usr_nama"]; ?></label></td>
                                 </tr>
                                 <tr>
                                   <td width= "15%" align="left" class="tablecontent">Alamat</td>
                                   <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
                                 </tr>
   <!--      <tr>
                <td width= "15%" align="left" class="tablecontent">Sudah Terima Dari</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                <input type="text" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                &nbsp;&nbsp;&nbsp;
                </td>
              </tr> -->
              <tr>
                <td width= "15%" align="left" class="tablecontent">Cara Bayar</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select name="reg_jenis_pasien" disabled id="reg_jenis_pasien" class="form-control" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Cara Bayar ]</option>         
                   <?php for($i=0,$n=count($dataJenis);$i<$n;$i++){ ?>
                     <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["reg_jenis_pasien"]==$dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
                   <?php } ?>
                 </select>
               </td>
             </tr>
             <tr>
              <td width= "15%" align="left" class="tablecontent">Jenis Bayar</td>
              <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
               <input type="text" class="form-control" name="jbayar_nama" value="<?=$_POST['jbayar_nama']?>" readonly>
             </td>
           </tr>
           <tr>
            <td width= "15%" align="left" class="tablecontent">Petugas Kasir</td>
            <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
             <input type="text" class="form-control" name="petugas_kasir" value="<?=$_POST['petugas_kasir']?>" readonly>
           </td>
         </tr>
         <?php if($_POST["reg_jenis_pasien"]==18){?>
          <tr>
            <td width= "15%" align="left" class="tablecontent">Nama Kota</td>
            <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
             <select name="id_jamkesda_kota" disabled id="id_jamkesda_kota" class="form-control" onKeyDown="return tabOnEnter(this, event);">
               <option value="--">[ Pilih Kota ]</option>           
               <?php for($i=0,$n=count($dataKota);$i<$n;$i++){ ?>
                 <option value="<?php echo $dataKota[$i]["jamkesda_kota_id"];?>" <?php if($_POST["id_jamkesda_kota"]==$dataKota[$i]["jamkesda_kota_id"]) echo "selected"; ?>><?php echo $dataKota[$i]["jamkesda_kota_nama"];?></option>
               <?php } ?>
             </select>
           </td>
         </tr>
       <?php } ?>
       <?php if($_POST["reg_jenis_pasien"]==7){?>
        <tr>
          <td width= "15%" align="left" class="tablecontent">Nama Perusahaan</td>
          <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
           <select name="id_perusahaan" disabled id="id_perusahaan" class="form-control" onKeyDown="return tabOnEnter(this, event);">
             <option value="--">[ Pilih Perusahaan ]</option>         
             <?php for($i=0,$n=count($dataPerusahaan);$i<$n;$i++){ ?>
               <option value="<?php echo $dataPerusahaan[$i]["perusahaan_id"];?>" <?php if($_POST["id_perusahaan"]==$dataPerusahaan[$i]["perusahaan_id"]) echo "selected"; ?>><?php echo $dataPerusahaan[$i]["perusahaan_nama"];?></option>
             <?php } ?>
           </select>
         </td>
       </tr>
     <?php } ?>
     <?php if($_POST["reg_jenis_pasien"]==5 || $_POST["reg_jenis_pasien"]=='26'){?>
      <tr>
        <td width= "15%" align="left" class="tablecontent">Nama Kategori JKN</td>
        <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
         <select name="cust_usr_jkn" disabled id="cust_usr_jkn" class="form-control" onKeyDown="return tabOnEnter(this, event);">
           <option value="--">[ Pilih Kategori JKN ]</option>           
           <?php for($i=0,$n=count($dataJkn);$i<$n;$i++){ ?>
             <option value="<?php echo $dataJkn[$i]["jkn_id"];?>" <?php if($_POST["cust_usr_jkn"]==$dataJkn[$i]["jkn_id"]) echo "selected"; ?>><?php echo $dataJkn[$i]["jkn_nama"];?></option>
           <?php } ?>
         </select>
       </td>
     </tr>
   <?php } ?>
   <tr>
    <td width= "15%" align="left" class="tablecontent">Shift</td>
    <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
     <select name="reg_shift" disabled id="reg_shift" class="form-control" onKeyDown="return tabOnEnter(this, event);">
       <option value="--">[ Pilih Shift ]</option>          
       <?php for($i=0,$n=count($dataShift);$i<$n;$i++){ ?>
         <option value="<?php echo $dataShift[$i]["shift_id"];?>" <?php if($_POST["reg_shift"]==$dataShift[$i]["shift_id"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_jam_awal"]."-".$dataShift[$i]["shift_jam_akhir"];?></option>
       <?php } ?>
     </select>
   </td>
 </tr>
 <tr>
  <td width= "15%" align="left" class="tablecontent">Instalasi</td>
  <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
   <select name="id_poli" id="id_poli" class="form-control" disabled onKeyDown="return tabOnEnter(this, event);">         
    <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
     <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
   <?php } ?>
 </select>
</td>
</tr>
<tr>
  <td width= "15%" align="left" class="tablecontent">Nama Dokter</td>
  <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
   <select name="id_dokter" id="id_dokter" class="form-control" disabled onKeyDown="return tabOnEnter(this, event);">
     <option value="--">[ Pilih Dokter ]</option>         
     <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
       <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($_POST["id_dokter"]==$dataDokter[$i]["usr_id"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
     <?php } ?>
   </select>
 </td>
</tr>
<tr>
  <td width= "15%" align="left" class="tablecontent">Keterangan Batal</td>
  <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
   <textarea id="batal_ket" name="batal_ket" class="form-control" cols="100" rows="3"><?php echo $_POST["batal_ket"]; ?></textarea>
 </td>
</tr>        
</table>
</div>
</fieldset>

<fieldset>
 <legend><strong>Data Pembayaran</strong></legend>
 <div id="kasir">
   <table width="100%" border="0" class="table table-bordered" cellpadding="4" cellspacing="1"> 
    <tr class="tablesmallheader">
      <!--<td width="3%" align='center'>Batal</td>-->
      <td width="3%" align='center'>No</td>
      <td width="25%" align='center'>Layanan</td>
      <td width="10%" align='center'>Biaya</td>
      <td width="3%" align='center'>Jml</td>
      <td width="10%" align='center'>Total Tagihan</td>
      <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='26') { ?>
        <td width="10%" align='center'>Dijamin</td>
        <td width="10%" align='center'>Subsidi</td>
        <td width="10%" align='center'>Iur Biaya</td>
        <td width="10%" align='center'>Hrs Bayar</td>
      <? } ?>
      <?php if($_POST["reg_jenis_pasien"]=='18') { ?>
        <td width="10%" align='center'>Dijamin Dinkes Prop</td>
        <td width="10%" align='center'>Dijamin Dinkes Kab</td>
        <td width="10%" align='center'>Iur Biaya</td>
        <td width="10%" align='center'>Hrs Bayar</td>
      <? } ?>
      <td width="10%" align='center'>DPJP</td>
      <td width="10%" align='center'>Pelaksana</td>
    </tr>
    
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
      <?
      $sql = "select c.usr_name as pelaksana_nama,a.id_usr as pelaksana_id from  klinik.klinik_folio_pelaksana a
      left join klinik.klinik_folio b on a.id_fol = b.fol_id
      left join global.global_auth_user c on a.id_usr  = c.usr_id
      where  a.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"])." 
      order by a.fol_pelaksana_tipe"; 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataPelaksana = $dtaccess->FetchAll($rs_edit);
      ?> 
      <tr class="tablecontent-odd">
        <!--<td width="3%" align="center"><?php if($dataTable[$i]["fol_jenis"]=="WA" || $dataTable[$i]["fol_jenis"]=="RJ" || $dataTable[$i]["fol_jenis"]=="RS" || $dataTable[$i]["fol_jenis"]=="RU" || $dataTable[$i]["fol_jenis"]=="RV") { echo "&nbsp;"; } else { echo '<a href="'.$delPage.'&del=1&id='.$dataTable[$i]["fol_id"].'&id_register='.$dataTable[$i]["id_reg"].'&id_biaya='.$dataTable[$i]["id_biaya"].'&id_pembayaran='.$_GET["pembayaran_id"].'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/b_drop.png" alt="Batal" title="Batal" border="0" onclick="javascript: return hapus();"/></a>';} ?></td>-->
        <td width="3%"><?php echo ($i+1).".";?></td>
        <td width="25%"><?php echo $dataTable[$i]["fol_nama"];?></td>
        <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
        <td width="3%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]);?></td>
        <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"])?></td>
        <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='26') { ?>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_subsidi"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_iur_bayar"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_hrs_bayar"])?></td>
        <? } ?>
        <?php if($_POST["reg_jenis_pasien"]=='18') { ?>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin1"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin2"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_iur_bayar"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_hrs_bayar"])?></td>
        <? } ?>
        <td width="10%" align='right'><?php echo $dataPelaksana[0]["pelaksana_nama"]?></td>
        <td width="10%" align='right'><?php echo $dataPelaksana[1]["pelaksana_nama"]?></td>
      </tr>
    <?php } ?>
    <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
      <tr>                                     
        <td align="right" width="20%" class="tablesmallheader" colspan='11'><strong>Total Harus Bayar</strong></td>
        <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_hrs_bayar"]);?></strong></td>
      </tr>
      <?} else { ?>              
        <tr>                                     
          <td align="right" width="20%" class="tablesmallheader" colspan='6'><strong>Total Harus Bayar</strong></td>
          <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_hrs_bayar"]);?></strong></td>
        </tr>
        <?}?>
        <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
          <tr>                                     
            <td align="right" width="20%" class="tablesmallheader" colspan='11'><strong>Diskon</strong></td>
            <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_diskon"]);?></strong></td>
          </tr>
          <?} else { ?>              
            <tr>                                     
              <td align="right" width="20%" class="tablesmallheader" colspan='6'><strong>Diskon</strong></td>
              <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_diskon"]);?></strong></td>
            </tr>
            <?}?>
            <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
              <tr>                                     
                <td align="right" width="20%" class="tablesmallheader" colspan='11'><strong>Total Pembayaran</strong></td>
                <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_total"]);?></strong></td>
              </tr>
              <?} else { ?>              
                <tr>                                     
                  <td align="right" width="20%" class="tablesmallheader" colspan='6'><strong>Total Pembayaran</strong></td>
                  <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_total"]);?></strong></td>
                </tr>
                <?}?>
                
              </div>
            </fieldset>
          </table>
          <? if ($dataMultiPayment) { //JIKA MULTIPAYMENT?>
           
            <fieldset>
             <legend><strong>Data Pembayaran Multipayemnt</strong></legend>
             <div id="kasir">
               <table width="100%" border="0" class="table table-bordered" cellpadding="4" cellspacing="1"> 
                <tr class="tablesmallheader">
                  <!--<td width="3%" align='center'>Batal</td>-->
                  <td width="3%" align='center'>No</td>
                  <td width="10%" align='center'>No Kwitansi</td>
                  <td width="25%" align='center'>Layanan</td>
                  <td width="10%" align='center'>Diskon</td>
                  <td width="10%" align='center'>Service Charge</td>
                  <td width="10%" align='center'>Deposit</td>
                  <td width="10%" align='center'>Total Pembayaran</td>
                  <td width="10%" align='center'>Jenis Bayar</td>
                  <td width="10%" align='center'>Tipe Penerimaan</td>
                </tr>
                
                <?php for($j=0,$k=count($dataMultiPayment);$j<$k;$j++) { ?>
                  <tr class="tablecontent-odd">
                    <td width="3%"><?php echo ($j+1).".";?></td>
                    <td width="10%"><?php echo $dataMultiPayment[$j]["pembayaran_det_kwitansi"];?></td>
                    <td width="25%"><?php echo $dataMultiPayment[$j]["pembayaran_det_ket"];?></td>             
                    <td width="10%" align='right'><?php echo currency_format($dataMultiPayment[$j]["pembayaran_det_diskon"]);?></td>
                    <td width="3%" align='right'><?php echo round($dataMultiPayment[$j]["pembayaran_det_service_cash"]);?></td>
                    <td width="10%" align='right'><?php echo currency_format($dataMultiPayment[$j]["pembayaran_det_deposit"])?></td>
                    <td width="10%" align='right'><?php echo currency_format($dataMultiPayment[$j]["pembayaran_det_dibayar"])?></td>
                    <?php if ($dataMultiPayment[$j]['perusahaan_nama']=='') { ?>
                      <td width="15%"><?php echo $dataMultiPayment[$j]['jbayar_nama']; ?></td> 
                    <?php } else { ?>
                      <td width="15%"><?php echo $dataMultiPayment[$j]['perusahaan_nama']; ?></td> 
                    <?php } ?>
                    <td width="15%"><?php echo $dataMultiPayment[$j]["pembayaran_det_flag"];?></td>  
                  </tr>
                <?php } ?>
                <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || 
                $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
                  <tr>                                     
                    <td align="right" width="20%" class="tablesmallheader" colspan='11'><strong>Total Harus Bayar</strong></td>
                    <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_hrs_bayar"]);?></strong></td>
                  </tr>
                  <?} else { ?>              
                    <tr>                                     
                      <td align="right" width="20%" class="tablesmallheader" colspan='6'><strong>Total Harus Bayar</strong></td>
                      <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_hrs_bayar"]);?></strong></td>
                      <td align="right" width="20%" class="tablesmallheader" colspan='2'>&nbsp;</td>
                    </tr>
                    <?}?>
                    <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
                      <tr>                                     
                        <td align="right" width="20%" class="tablesmallheader" colspan='11'><strong>Diskon</strong></td>
                        <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_diskon"]);?></strong></td>
                      </tr>
                      <?} else { ?>              
                        <tr>                                     
                          <td align="right" width="20%" class="tablesmallheader" colspan='6'><strong>Diskon</strong></td>
                          <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_diskon"]);?></strong></td>
                          <td align="right" width="20%" class="tablesmallheader" colspan='2'>&nbsp;</td>
                        </tr>
                        <?}?>
                        <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
                          <tr>                                     
                            <td align="right" width="20%" class="tablesmallheader" colspan='11'><strong>Total Pembayaran</strong></td>
                            <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_total"]);?></strong></td>
                            
                          </tr>
                          <?} else { ?>              
                            <tr>                                     
                              <td align="right" width="20%" class="tablesmallheader" colspan='6'><strong>Total Pembayaran</strong></td>
                              <td align="right"><strong><?php echo "Rp. ".currency_format($dataPembayaranDet["pembayaran_det_total"]);?></strong></td>
                              <td align="right" width="20%" class="tablesmallheader" colspan='2'>&nbsp;</td>
                            </tr>
                            
                            <?} ?>
                          </table>
                        </fieldset>  
            <? }   //AKHIR MULTIPAYMENT 
            
            ?>
            
            
            
            <table width="100%" border="0" cellpadding="4" cellspacing="1"> 

             <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='26') { ?>                
              <tr>                                     
                <td align="right" width="20%" class="tablesmallheader" colspan='12'>
                 <input type="submit" name="btnBatal" id="btnBatal" value="Batal Bayar" class="submit" onClick="javascript:return CekBatal();"/>
                 <input type="button" name="kembali" id="kembali" value="Kembali" class="submit" onClick="document.location.href='batal_bayar_view.php'";/>     
                 <!--         <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CekData();"/>   -->
               </td>
             </tr>
             <?} else { ?>              
              <tr>                                     
                <td align="right" width="20%" class="tablesmallheader" colspan='8'>
                 <input type="submit" name="btnBatal" id="btnBatal" value="Batal Bayar" class="btn btn-primary" onClick="javascript:return CekBatal();"/>
                 <input type="button" name="kembali" id="kembali" value="Kembali" class="btn btn-success" onClick="document.location.href='batal_bayar_view.php'";/>     
                 <!--     <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CekData();"/> -->
               </td>
             </tr>
             <?}?>
           </table>

           <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandTotalHarga;?>" /> 
           <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" />
           <input type="hidden" name="txtBiayaResep" id="txtBiayaResep" value="<?php echo $_POST["txtDiskon"]; ?>" />
           <input type="hidden" name="txtBiayaRacikan" id="txtBiayaRacikan" value="<?php echo $_POST["txtBiayaRacikan"]; ?>" />
           <input type="hidden" name="txtBiayaBhps" id="txtBiayaBhps" value="<?php echo $_POST["txtBiayaBhps"]; ?>" />
           <input type="hidden" name="txtBiayaPembulatan" id="txtBiayaPembulatan" value="<?php echo $_POST["txtBiayaPembulatan"]; ?>" />
           <input type="hidden" name="txtPPN" id="txtPPN" value="0">
             <!-- <input type="hidden" name="txtDiskonPersen" id="txtDiskonPersen" value="0">
              <input type="hidden" name="txtDiskon" id="txtDiskon" value="0">  -->
              <input type="hidden" name="txtcek" id="txtcek" value="<?php echo $_POST["txtcek"]; ?>">
              <input type="hidden" name="txtTotalDibayar" id="txtTotalDibayar" value="<?php echo $totalHarga?>">
              <!--<input type="hidden" name="txtDibayar" id="txtDibayar" value="<?php echo $_POST["txtDibayar"]; ?>">-->
              <input type="hidden" name="txtKembalian" id="txtKembalian" value="<?php echo $_POST["txtHargaTotal"]; ?>">
              <!--<input type="hidden" name="id_dokter" id="id_dokter" value="<?php echo $_POST["id_dokter"]; ?>"> -->
              <input type="hidden" name="pembayaran_id" id="pembayaran_id" value="<?php echo $_POST["pembayaran_id"]; ?>">
            </tr>
          </table>

          <script>document.frmEdit.CityAjax.focus();</script>
          
          <input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>" />
          <input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
          <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
          <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>
          <?php if($_GET["pembayaran_id"]) { ?>
            <input type="hidden" name="id_pembayaran" value="<?php echo $_GET["pembayaran_id"];?>"/>
          <?php } else { ?>
            <input type="hidden" name="id_pembayaran" value="<?php echo $_GET["id_pembayaran"];?>"/>
          <?php } ?>
          <input type="hidden" name="id_pembayaran_det" value="<?php echo $_POST["id_pembayaran_det"];?>"/>
          <input type="hidden" name="id_poli" value="<?php echo $_GET["id_poli"];?>"/>
          <input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>
          <input type="hidden" name="fol_id" value="<?php echo $_GET["fol_id"]; ?>"/>
          <input type="hidden" name="biaya_id" value="<?php echo $_GET["jenis"]; ?>"/>
          <input type="hidden" name="waktu" value="<?php echo $_GET["waktu"]; ?>"/>
          <input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"]; ?>"/>
          <input type="hidden" name="dep_bayar_reg" value="<?php echo $_POST["dep_bayar_reg"]; ?>"/>
        </form>

      <?php } ?>

    </div>
  </div>
        <!--table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
            <tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
            <td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
    </table>-->

    <?php ////echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
    <?php //echo $view->RenderBodyEnd(); ?>
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