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
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depId = $auth->GetDepId();
     $thisPage = "report_setoran_loket.php";
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $lokasi = $ROOT."/gambar/img_cfg";
     
     if(!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else $_POST["klinik"] = $_POST["klinik"];    
     //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
   
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
       die("Maaf anda tidak berhak membuka halaman ini....");
       exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
       echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
       exit(1);
     } 
 
     // konfigurasi
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
          
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }
     
     if($_POST["tgl_awal"]) $sql_where[] = "c.reg_tanggal_pulang >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "c.reg_tanggal_pulang <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));  
     $sql_where[] = "1=1"; 

     
     $sql_where = implode(" and ",$sql_where);
     
     if($_POST["btnLanjut"] || $_POST["btnTutup"])   
     { 
       $sql = "select reg_id, pembayaran_id 
              from klinik.klinik_registrasi c 
              left join klinik.klinik_pembayaran a on c.reg_id = a.id_reg
              left join klinik.klinik_folio b on b.id_pembayaran = a.pembayaran_id
              where pembayaran_flag = 'n' and c.reg_jenis_pasien = '2' and fol_nominal_satuan <> '0' ";
      $sql .= " and ".$sql_where." group by reg_id, pembayaran_id order by c.reg_tanggal desc, c.reg_waktu desc";
      $dataTable = $dtaccess->FetchAll($sql);
     }

     if ($_POST['btnTutup']) {
      // echo "<pre>";
      // print_r($_POST);
      // echo "</pre>";
      $sql = "select * from gl.gl_konf";
      $Konf = $dtaccess->Fetch($sql);
      for ($i=0; $i < count($dataTable); $i++) { 
        $dbTable = "gl.gl_buffer_transaksi";
        $dbField[0]  = "id_tra";   // PK
        $dbField[1]  = "ref_tra";   
        $dbField[2]  = "tanggal_tra"; 
        $dbField[3]  = "ket_tra";
        $dbField[4]  = "namauser";
        $dbField[5]  = "real_time";
        $dbField[6]  = "dept_id";
        $dbField[7]  = "ref_tra_urut";
        $dbField[8]  = "id_pembayaran_det";
        $dbField[9]  = "flag_jurnal";
              
        $dateReal = date("Y-m-d H:i:s");
        $keterangan = "Jurnal Pendapatan Kurang Bayar an ".$_POST['cust_usr_nama'.($i+1)]." (".$_POST['cust_usr_kode'.($i+1)].")";
        
        $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
                where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'AC-%' 
                and tanggal_tra = ".QuoteValue(DPE_DATE,date('Y-m-d'))." 
                order by ref_tra_urut desc";
        $lastKode = $dtaccess->Fetch($sql);
        $noRef = $lastKode["kode"]+1;  

        $transaksiId = $dtaccess->GetTransId();
        $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
        $dbValue[1] = QuoteValue(DPE_CHAR,'AC'."-".date('ymd').$noRef);
        $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
        $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
        $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
        $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
        $dbValue[8] = QuoteValue(DPE_CHAR,$pembDetUtama); //ambil dari file insert_pembayaran_det_kassa.php saat insert klinik_pembayaran_det
      if ($_POST['reg_tipe_rawat'.($i+1)]=='J') {
        $dbValue[9] = QuoteValue(DPE_CHAR,'PEJ'); //Flag Pendapatan Rawat Jalan
      }elseif ($_POST['reg_tipe_rawat'.($i+1)]=='G') {
        $dbValue[9] = QuoteValue(DPE_CHAR,'PEG'); //Flag Pendapatan IGD
      }elseif ($_POST['reg_tipe_rawat'.($i+1)]=='I') {
        $dbValue[9] = QuoteValue(DPE_CHAR,'PEI'); //Flag Pendapatan IRNA
      }
        // print_r($dbValue); die();
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
        $dtmodel->Insert() or die("insert  error");
                                                                          
        unset($dbField);
        unset($dbValue); 

      $sql = "select * from global.global_jkn limit 1";
      $Jkn = $dtaccess->Fetch($sql);
      if ($_POST['reg_jenis_pasien'.($i+1)] == '2') {
        $Piutang = $Konf['dep_coa_piutang_perorangan'];
      }elseif ($_POST['reg_jenis_pasien'.($i+1)] == '5') {
        $Piutang = $Jkn['id_prk'];
      }elseif($_POST['reg_jenis_pasien'.($i+1)] == '7'){
        $Piutang = '01010101010106';
      }
      //SISI DEBET
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$Piutang);
          //$dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['total_tagihan'.($i+1)]));
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error"); 
            
          unset($dbField);
          unset($dbValue);
      //END SISI DEBET

      //SISI KREDIT
      $sql = "select * from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id'.($i+1)]);
      $dataFolio = $dtaccess->FetchAll($sql);
      for ($x=0; $x < count($dataFolio); $x++) { 
        $sql = "select * from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,$dataFolio[$x]['id_biaya']);
        $dataPrkFolio = $dtaccess->Fetch($sql);
         if ($_POST['reg_jenis_pasien'.($i+1)] == '2') {
            $prkId = $dataPrkFolio['id_prk'];
         }elseif ($_POST['reg_jenis_pasien'.($i+1)] == '5') {
            $prkId = $dataPrkFolio['id_prk_bpjs'];
         }elseif ($_POST['reg_jenis_pasien'.($i+1)] == '7') {
            $prkId = $dataPrkFolio['id_prk_asuransi'];
         }
        //TINDAKAN
        $dbTable = "gl.gl_buffer_transaksidetil";
        
        $dbField[0]  = "id_trad";   // PK
        $dbField[1]  = "tra_id";
        $dbField[2]  = "prk_id";
        $dbField[3]  = "ket_trad";
        $dbField[4]  = "job_id";
        $dbField[5]  = "dept_id";
        $dbField[6]  = "jumlah_trad";
        $dbField[7]  = "id_poli";
        $dbField[8]  = "id_instalasi";
        $dbField[9]  = "id_fol";

             $transaksiDetailId1 = $dtaccess->GetTransId();

        $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId1);
        $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
        $dbValue[2] = QuoteValue(DPE_CHAR,$prkId);
        $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
        $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
        $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
        if($dataFolio[$x]["fol_nominal"]<0){
        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolio[$x]["fol_nominal"])));
        } else {  
        $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolio[$x]["fol_nominal"]));
        }
        $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_poli"]);
        $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_instalasi"]);
        $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolio[$x]["fol_id"]);
        // print_r($dbValue);
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
        if ($dataFolio[$x]['id_biaya'] != '9999999') {
          $dtmodel->Insert() or die("insert  error"); 
        }
          
        unset($dbField);
        unset($dbValue);
        unset($dataPrkFolio);

        //FARMASI
        $sql = "select * from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id'.($i+1)]);
        $dataApotik = $dtaccess->Fetch($sql);
         if ($dataApotik['penjualan_total']!='') {
          if ($dataFolio[$x]['id_biaya']='9999999') {
           if ($_POST['reg_tipe_rawat'.($i+1)] == 'J') {
            if ($_POST['cust_usr_kode'.($i+1)] == '100') {
              $sql = "select * from klinik.klinik_registrasi a left join global.global_auth_poli b on b.poli_id = a.id_poli where reg_id = ".QuoteValue(DPE_CHAR,$_POST['reg_id'.($i+1)]);
              $dataRegistrasi = $dtaccess->Fetch($sql);
                if ($dataRegistrasi['poli_nama'] == 'Apotek RSIA') {
                 $prkId = $Konf['dep_coa_pendapatan_apotik_irna'];
                 $prkIdPPN = $Konf['dep_coa_ppn_keluar_apotik_irna'];
                 $prkIdTuslag = $Konf['dep_coa_tuslag_apotik_irna'];
                }elseif ($dataRegistrasi['poli_nama'] == 'Apotek GRAHA') {
                 $prkId = $Konf['dep_coa_pendapatan_apotik_irj'];
                 $prkIdPPN = $Konf['dep_coa_ppn_keluar_apotik_irj'];
                 $prkIdTuslag = $Konf['dep_coa_tuslag_apotik_irj'];
                }
            }else{
             $prkId = $Konf['dep_coa_pendapatan_apotik_irj'];
             $prkIdPPN = $Konf['dep_coa_ppn_keluar_apotik_irj'];
             $prkIdTuslag = $Konf['dep_coa_tuslag_apotik_irj'];
            }
           }elseif ($_POST['reg_tipe_rawat'] == 'I') {
             $prkId = $Konf['dep_coa_pendapatan_apotik_irna'];
             $prkIdPPN = $Konf['dep_coa_ppn_keluar_apotik_irna'];
             $prkIdTuslag = $Konf['dep_coa_tuslag_apotik_irna'];
           }elseif ($_POST['reg_tipe_rawat'] == 'G') {
             $prkId = $Konf['dep_coa_pendapatan_apotik_igd'];
             $prkIdPPN = $Konf['dep_coa_ppn_keluar_apotik_igd'];
             $prkIdTuslag = $Konf['dep_coa_tuslag_apotik_igd'];
           }
            //Pendapatan
            $dbTable = "gl.gl_buffer_transaksidetil";

            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
            $dbField[7]  = "id_poli";
            $dbField[8]  = "id_instalasi";
            $dbField[9]  = "id_fol";

            $transaksiDetailId2 = $dtaccess->GetTransId();

            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId2);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,$prkId);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
            if($dataApotik["penjualan_biaya_pokok"]<0){
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataApotik["penjualan_biaya_pokok"])));
            } else {  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataApotik["penjualan_biaya_pokok"]));
            }
            $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_poli"]);
            $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_instalasi"]);
            $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolio[$x]["fol_id"]);
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error"); 
              
            unset($dbField);
            unset($dbValue);
            unset($dataPrkFolio);

            //PPN
            $dbTable = "gl.gl_buffer_transaksidetil";

            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
            $dbField[7]  = "id_poli";
            $dbField[8]  = "id_instalasi";
            $dbField[9]  = "id_fol";

            $transaksiDetailId3 = $dtaccess->GetTransId();

            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId3);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdPPN);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
            if($dataApotik["penjualan_pajak"]<0){
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataApotik["penjualan_pajak"])));
            } else {  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataApotik["penjualan_pajak"]));
            }
            $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_poli"]);
            $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_instalasi"]);
            $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolio[$x]["fol_id"]);
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error"); 
              
            unset($dbField);
            unset($dbValue);
            unset($dataPrkFolio);

            //Tuslag
            $dbTable = "gl.gl_buffer_transaksidetil";

            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
            $dbField[7]  = "id_poli";
            $dbField[8]  = "id_instalasi";
            $dbField[9]  = "id_fol";

            $transaksiDetailId4 = $dtaccess->GetTransId();

            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId4);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdTuslag);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
            if($dataApotik["penjualan_tuslag"]<0){
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataApotik["penjualan_tuslag"])));
            } else {  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataApotik["penjualan_tuslag"]));
            }
            $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_poli"]);
            $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolio[$x]["id_instalasi"]);
            $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolio[$x]["fol_id"]);
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error"); 
              
            unset($dbField);
            unset($dbValue);
            unset($dataPrkFolio);

            $sql = "update apotik.apotik_penjualan set penjualan_terbayar ='y' where 
                    penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataApotik["penjualan_id"]);
            $rs = $dtaccess->Execute($sql); 
          }
         }
      }
      //END SISI KREDIT
      //INSERT PEMBAYARAN DETAIL
      if ($_POST['reg_jenis_pasien'.($i+1) == '2']) {
        $sql = "select pembayaran_det_kwitansi as kode from klinik.klinik_pembayaran_det a where pembayaran_det_tgl=".QuoteValue(DPE_DATE,$skr)." and  pembayaran_det_kwitansi is not null order by pembayaran_det_create desc";
        $lastKode = $dtaccess->Fetch($sql);
        
        $kode=explode(".",$lastKode["kode"]);
        $flg=$kode[0];
        $ins=$kode[1];
        $tgl=$kode[2];
        $no=$kode[3];
          
        if($_POST["reg_tipe_rawat".($i+1)]=="J"){
          $kw1 = "01";
        } elseif($_POST["reg_tipe_rawat".($i+1)]=="G"){
          $kw1 = "03";
        } elseif($_POST["reg_tipe_rawat".($i+1)]=="I"){
          $kw1 = "02";
        } 
          
        $_POST["kwitansi_nomor"] = $flag.".".$kw1.".".$tgl1.".".str_pad(($no+1),5,"0",STR_PAD_LEFT);
           
        $dbTable = "klinik.klinik_pembayaran_det";

        $dbField[0] = "pembayaran_det_id"; // PK
        $dbField[1] = "id_pembayaran";
        $dbField[2] = "pembayaran_det_create";
        $dbField[3] = "pembayaran_det_tgl";
        $dbField[4] = "pembayaran_det_ke";
        $dbField[5] = "pembayaran_det_total";
        $dbField[6] = "id_dep";
        $dbField[7] = "id_dokter";
        $dbField[8] = "who_when_update";
        $dbField[9] = "id_jbayar";
        $dbField[10] = "id_jenis_pasien";
        $dbField[11] = "pembayaran_det_flag";
        $dbField[12] = "pembayaran_det_tipe_piutang";
        $dbField[13] = "id_reg";
        $dbField[14] = "pembayaran_det_ket";
        $dbField[15] = "pembayaran_det_kwitansi";

        $pembDetIdNew2 = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetIdNew2);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
        $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
        $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+2));
        $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['total_tagihan'.($i+1)]));
        $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
        $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[9] = QuoteValue(DPE_CHAR,'01');
        $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien".($i+1)]);
        $dbValue[11] = QuoteValue(DPE_CHAR,"P");
        $dbValue[12] = QuoteValue(DPE_CHAR,'P');
        $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_id".($i+1)]);
        $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_ket"]);
        $dbValue[15] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
                 
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
        $dtmodel->Insert() or die("insert  error");
                 
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
      }else{
        $sql = "select pembayaran_det_kwitansi as kode from klinik.klinik_pembayaran_det a where pembayaran_det_tgl=".QuoteValue(DPE_DATE,$skr)." and  pembayaran_det_kwitansi is not null order by pembayaran_det_create desc";
        $lastKode = $dtaccess->Fetch($sql);
        
        $kode=explode(".",$lastKode["kode"]);
        $flg=$kode[0];
        $ins=$kode[1];
        $tgl=$kode[2];
        $no=$kode[3];
          
        if($_POST["reg_tipe_rawat".($i+1)]=="J"){
          $kw1 = "01";
        } elseif($_POST["reg_tipe_rawat".($i+1)]=="G"){
          $kw1 = "03";
        } elseif($_POST["reg_tipe_rawat".($i+1)]=="I"){
          $kw1 = "02";
        } 
          
        $_POST["kwitansi_nomor"] = $flag.".".$kw1.".".$tgl1.".".str_pad(($no+1),5,"0",STR_PAD_LEFT);
           
        $dbTable = "klinik.klinik_pembayaran_det";

        $dbField[0] = "pembayaran_det_id"; // PK
        $dbField[1] = "id_pembayaran";
        $dbField[2] = "pembayaran_det_create";
        $dbField[3] = "pembayaran_det_tgl";
        $dbField[4] = "pembayaran_det_ke";
        $dbField[5] = "pembayaran_det_total";
        $dbField[6] = "id_dep";
        $dbField[7] = "id_dokter";
        $dbField[8] = "who_when_update";
        $dbField[9] = "id_jbayar";
        $dbField[10] = "id_jenis_pasien";
        $dbField[11] = "pembayaran_det_flag";
        $dbField[12] = "pembayaran_det_tipe_piutang";
        $dbField[13] = "id_reg";
        $dbField[14] = "pembayaran_det_ket";
        $dbField[15] = "pembayaran_det_kwitansi";

        $pembDetIdNew2 = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetIdNew2);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
        $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
        $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+2));
        $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['total_tagihan'.($i+1)]));
        $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
        $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[9] = QuoteValue(DPE_CHAR,'01');
        $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien".($i+1)]);
        $dbValue[11] = QuoteValue(DPE_CHAR,"P");
        $dbValue[12] = QuoteValue(DPE_CHAR,'J');
        $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_id".($i+1)]);
        $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_ket"]);
        $dbValue[15] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
                 
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
        $dtmodel->Insert() or die("insert  error");
                 
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
      }

      $sql = "update klinik.klinik_folio set fol_lunas = 'y' where id_pembayaran ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id'.($i+1)]);
      $result = $dtaccess->Execute($sql);
     } // END FOR DATA TABLE
    }   
     
  $tableHeader = "Laporan Belum Posting";
  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_cicilan.xls');
  }  
     
?>

<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
     }
}

  window.onload = function() { TampilCombo(); }
  function TampilCombo(id)
    {        
         
         //alert(id);
         if(id=="7"){
              ush_id.disabled = false;
              //elm_combo.checked = true; 
                       
         } else {
              ush_id.disabled = true;
         }
    }   

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
    } else {
      _wnd_new.focus();
    }
  }
     return false;
}

<?php if($_x_mode=="cetak"){ ?>
  BukaWindow('tutup_kasir_cetak.php?perusahaan=<?php echo $perusahaan;?>&id_poli=<?php echo $_POST["id_poli"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&shift=<?php echo $_POST["reg_shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&js_biaya=<?php echo $_POST["js_biaya"];?>&jbayar=<?php echo $_POST["jbayar"];?>&kasir=<?php echo $kasir;?>&layanan=<?php echo $_POST["reg_tipe_layanan"]?>', '_blank');
  document.location.href='tutup_kasir.php';
<?php } ?>

</script>


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
            
            <!-- Row -->
            <div class="row">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Laporan Belum Posting</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content" >
                  <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
                    <table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
                          <input name="tgl_awal" type='text' class="form-control"
                          value="<?php if ($_POST['tgl_awal']) { echo $_POST['tgl_awal']; } else { echo date('d-m-Y'); } ?>"  />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>
                        
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker2'>
                          <input  name="tgl_akhir"  type='text' class="form-control"
                          value="<?php if ($_POST['tgl_akhir']) { echo $_POST['tgl_akhir']; } else { echo date('d-m-Y'); } ?>"  />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>
                      </div>
                      
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- <input type="submit" name="btnTutup" value="Posting" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-danger"> -->
                        <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-success">
                      </div>
                    </table>
                </div>
              </div>
            </div>
            <!-- END ROW  -->
            
            <!-- Row -->
            <div class="row">
              <div class="x_panel">
                <div class="x_title">
                  <h2></h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content" >
                  <!-- <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?> -->
                  <?php if ($_POST['btnLanjut']) { ?>
                    <table class="table table-bordered" width="100%">
                      <thead>
                        <tr>
                          <th style="text-align: center;">No</th>
                          <th style="text-align: center;">No RM</th>
                          <th style="text-align: center;">Nama</th>
                          <th style="text-align: center;">Tanggal Pulang</th>
                          <th style="text-align: center;">Waktu Pulang</th>
                          <th style="text-align: center;">Tipe Rawat</th>
                          <th style="text-align: center;">Klinik</th>
                          <th style="text-align: center;">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                          for ($i=0; $i < count($dataTable); $i++) { 
                            $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where 
                                    id_pembayaran=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"]);
                            $rs = $dtaccess->Execute($sql);
                            $total = $dtaccess->Fetch($rs);

                            $sql = "select * from klinik.klinik_registrasi a 
                                    left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                                    left join global.global_auth_poli c on c.poli_id = a.id_poli
                                    where reg_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]['reg_id']);
                            $dataPasien = $dtaccess->Fetch($sql);

                            if ($dataPasien['cust_usr_kode'] == '100') {
                              $NamaPasien = $dataPasien['reg_keterangan'];
                            }else{
                              $NamaPasien = $dataPasien['cust_usr_nama'];
                            }

                            if ($dataPasien['reg_tipe_rawat'] == 'I') {
                              $TipeRawat = "Rawat Inap";
                            }elseif ($dataPasien['reg_tipe_rawat'] == 'J') {
                              $TipeRawat = "Rawat Jalan";
                            }elseif ($dataPasien['reg_tipe_rawat'] == 'G') {
                              $TipeRawat = "Rawat Darurat";
                            }

                            $totalSeluruh += $total['total'];
                        ?>
                        <tr>
                          <td align="center">
                            <?php echo $i+1; ?>
                            <input type="hidden" readonly name="pembayaran_id<?=$i+1?>" id="pembayaran_id<?=$i+1?>" value="<?php echo $dataTable[$i]['pembayaran_id'] ?>" class="form-control">
                            <input type="hidden" readonly name="reg_id<?=$i+1?>" id="reg_id<?=$i+1?>" value="<?php echo $dataTable[$i]['reg_id'] ?>" class="form-control">
                          </td>
                          <td><input type="text" readonly name="cust_usr_kode<?=$i+1?>" id="cust_usr_kode<?=$i+1?>" value="<?php echo $dataPasien['cust_usr_kode'] ?>" class="form-control"></td>
                          <td><input type="text" readonly name="cust_usr_nama<?=$i+1?>" id="cust_usr_nama<?=$i+1?>" value="<?php echo $NamaPasien ?>" class="form-control"></td>
                          <td><input type="text" readonly name="reg_tanggal_pulang<?=$i+1?>" id="reg_tanggal_pulang<?=$i+1?>" value="<?php echo date_db($dataPasien['reg_tanggal_pulang']) ?>" class="form-control"></td>
                          <td><input type="text" readonly name="reg_waktu_pulang<?=$i+1?>" id="reg_waktu_pulang<?=$i+1?>" value="<?php echo $dataPasien['reg_waktu_pulang'] ?>" class="form-control"></td>
                          <td>
                            <input type="text" readonly name="tipe_rawat<?=$i+1?>" id="tipe_rawat<?=$i+1?>" value="<?php echo $TipeRawat ?>" class="form-control">
                            <input type="hidden" readonly name="reg_tipe_rawat<?=$i+1?>" id="reg_tipe_rawat<?=$i+1?>" value="<?php echo $dataPasien['reg_tipe_rawat'] ?>" class="form-control">
                            <input type="hidden" readonly name="reg_jenis_pasien<?=$i+1?>" id="reg_jenis_pasien<?=$i+1?>" value="<?php echo $dataPasien['reg_jenis_pasien'] ?>" class="form-control">
                          </td>
                          <td><input type="text" readonly name="klinik<?=$i+1?>" id="klinik<?=$i+1?>" value="<?php echo $dataPasien['poli_nama'] ?>" class="form-control"></td>
                          <td><input type="text" readonly name="total_tagihan<?=$i+1?>" id="total_tagihan<?=$i+1?>" value="<?php echo currency_format($total['total']) ?>" class="form-control"></td>
                        </tr>
                        <?php } ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="7" align="right">Total</td>
                          <td align="right"><?php echo currency_format($totalSeluruh) ?></td>
                        </tr>
                      </tfoot>
                    </table>
                  <?php } ?>
                  </form>
                </div>
              </div>
            </div>
            <!-- END ROW  -->
            
          </div>
        </div>
        <!-- /page content -->
        <!-- footer content -->
        <?php require_once($LAY."footer.php"); ?>
      </div>
    </div>
    <?php require_once($LAY."js.php"); ?>
  </body>
</html>