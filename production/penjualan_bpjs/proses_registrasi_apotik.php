<?php    
     $sql = "select * from klinik.klinik_registrasi where reg_id=".QuoteValue(DPE_CHAR,$_POST["id_reg_lama"]);
     $rs = $dtaccess->Execute($sql);
     $regLama = $dtaccess->Fetch($rs);

     $sql = "select poli_tipe, id_gudang from global.global_auth_poli where poli_id = ".QuoteValue(DPE_CHAR,$_POST['apotik']);
     $dataApotik = $dtaccess->Fetch($sql);
       $theDep = $dataApotik['id_gudang'];
     //  echo $sql;
      // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_konf_loket_antrian_poli"] = $konfigurasi["dep_konf_loket_antrian_poli"];
     $_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
     $postPoli = $_POST["dep_posting_poli"];
     $_POST["dep_konf_kode_instalasi"] = $konfigurasi["dep_konf_kode_instalasi"];
     $_POST["dep_konf_kode_poli"] = $konfigurasi["dep_konf_kode_poli"];
     $_POST["dep_konf_urut_registrasi"] = $konfigurasi["dep_konf_urut_registrasi"];
     $_POST["dep_konf_urut_pasien"] = $konfigurasi["dep_konf_urut_pasien"];
     $_POST["dep_konf_reg_poli"] = $konfigurasi["dep_konf_reg_poli"];
     $_POST["dep_konf_kode_sub_instalasi"] = $konfigurasi["dep_konf_kode_sub_instalasi"];
      
      //Post Klinik diambilkan dari Poli
      $_POST["klinik"] = $poli;
      $tgl = date('ymd');
          $sql = "select count(reg_id) as nomorurut from klinik.klinik_registrasi where reg_tanggal = ".QuoteValue(DPE_DATE,date('Y-m-d'));
          $noUrut = $dtaccess->Fetch($sql);
          $noantri =  $noUrut["nomorurut"]+1;
          $kodeUrutReg =  $noUrut["nomorurut"]+1;
          $kodeUrutReg = str_pad($kodeUrutReg,4,"0",STR_PAD_LEFT);

    $kodeTrans = 'R'.$tgl.$kodeUrutReg;
      //require_once('reg_kode_trans.php');
      //INSERT REG BARU
                // ---- insert ke registrasi ----
                $dbTable = "klinik.klinik_registrasi";
              
                $dbField[0] = "reg_id";   // PK
                $dbField[1] = "reg_tanggal";
                $dbField[2] = "reg_waktu";
                $dbField[3] = "id_cust_usr";
                $dbField[4] = "reg_status";
                $dbField[5] = "reg_who_update";
                $dbField[6] = "reg_when_update";
                $dbField[7] = "reg_jenis_pasien";
                $dbField[8] = "reg_status_pasien";
                $dbField[9] = "id_poli";
                $dbField[10] = "id_dep";
                $dbField[11] = "reg_no_antrian";
                $dbField[12] = "reg_status_cetak_kartu";
                $dbField[13] = "id_jam";
                $dbField[14] = "id_dokter";
                $dbField[15] = "id_info";
                $dbField[16] = "reg_asal";
                $dbField[17] = "reg_umur";
                $dbField[18] = "reg_umur_hari";
                $dbField[19] = "reg_kartu";
                $dbField[20] = "reg_program";
                $dbField[21] = "reg_rujukan_id";
                $dbField[22] = "id_prop";
                $dbField[23] = "id_kota";
                $dbField[24] = "reg_utama";
                $dbField[25] = "id_pembayaran";
                $dbField[26] = "reg_shift";
                $dbField[27] = "reg_tipe_layanan";
                $dbField[28] = "reg_umur_bulan";
                $dbField[29] = "reg_kode_urut";
                $dbField[30] = "reg_kode_trans";
                $dbField[31] = "id_instalasi";
                $dbField[32] = "reg_tipe_rawat";
                $dbField[33] = "reg_urut";
                $dbField[34] = "id_poli_asal";
                $dbField[35] = "reg_tracer";
                $dbField[36] = "reg_tracer_riwayat";
                $dbField[37] = "reg_tracer_barcode";
                $dbField[38] = "reg_tracer_barcode_besar";
                $dbField[39] = "reg_tracer_registrasi";

                if($regLama["reg_jenis_pasien"]=='7') { 
                $dbField[40] = "id_perusahaan";
                }elseif($regLama["reg_jenis_pasien"]=='5' || $regLama["reg_jenis_pasien"]=='26') { 
                $dbField[40] = "reg_tipe_jkn";
                $dbField[41] = "reg_no_sep";
                }elseif($regLama["reg_jenis_pasien"]=='18') { 
                $dbField[40] = "id_jamkesda_kota";
                }elseif($regLama["reg_jenis_pasien"]=='25'){
                $dbField[40] = "reg_tipe_paket";
                }
                /*
                if ($regLama["reg_tipe_rawat"]=='J') $status = 'E0';  // Status Rawat Jalan --
                if ($regLama["reg_tipe_rawat"]=='G') $status = 'G1';  // Status Rawat Darurat --
                if ($regLama["reg_tipe_rawat"]=='I')  $status = 'I2';  // Status Rawat Inap --
                */

                $status = "A0";  //Status  Proses Pembelian Apotik

                if ($regLama["reg_tipe_rawat"]=='J') $tipeRawat = 'J';
                if ($regLama["reg_tipe_rawat"]=='G') $tipeRawat = 'G';
                if ($regLama["reg_tipe_rawat"]=='I') $tipeRawat = 'I';
                
               if ($regLama["reg_tipe_rawat"]==null || $regLama["reg_tipe_rawat"]=='') {
                    $status = 'E0';
                    $tipeRawat = 'J';
                }
                 
                $sqlreg = "select * from klinik.klinik_registrasi 
                     where id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"])." and id_poli = '".$poli."'";
                $datastatuspasien= $dtaccess->Fetch($sqlreg);
                 //echo $sqlreg;

                if ($datastatuspasien["id_poli"]){
                $statusPasien = "L";
                } else
                {
                $statusPasien = "B";
                }
               
               /* if($_POST["btnSave"]) $statusPasien =$_POST["reg_status_pasien"];
                else $statusPasien = 'L';   */
      
                $sql = "select max(reg_urut) as urut from klinik.klinik_registrasi where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
                $rs = $dtaccess->Execute($sql);
                $max = $dtaccess->Fetch($rs);
                $regUrut = $max["urut"]+1;
      
                $regId = $dtaccess->GetTransID();
                
                if ($_POST["dep_konf_loket_antrian_poli"]=='n') //apabila cetak antrian tidak per klinik
                   $sql = "select max(reg_no_antrian) as nomore from klinik.klinik_registrasi 
                          where reg_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"))." and id_dep = ".QuoteValue(DPE_CHAR,$depId);          
                else
                   $sql = "select max(reg_no_antrian) as nomore from klinik.klinik_registrasi 
                          where reg_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"))." and id_poli = ".QuoteValue(DPE_CHAR,$poli)." 
                          and id_dep = ".QuoteValue(DPE_CHAR,$depId);
                $noAntrian = $dtaccess->Fetch($sql);
          	    $noantri =  ($noAntrian["nomore"]+1);
          	    
                $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
                $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
                $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
                $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
                $dbValue[4] = QuoteValue(DPE_CHAR,$status);
                $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
                $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$regLama["reg_jenis_pasien"]);
                $dbValue[8] = QuoteValue(DPE_CHAR,$statusPasien);
                $dbValue[9] = QuoteValue(DPE_CHAR,$_POST['apotik']);
                $dbValue[10] = QuoteValue(DPE_CHAR,$depId);
                $dbValue[11] = QuoteValue(DPE_NUMERIC,$noantri);
                $dbValue[12] = QuoteValue(DPE_CHAR,'n');
                $dbValue[13] = QuoteValue(DPE_CHAR,$regLama["id_jam"]);
                if(!$_POST["usr"]){
                $dbValue[14] = QuoteValue(DPE_CHAR,$regLama["id_dokter"]);
                } else {
                $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["usr"]);
                }
                $dbValue[15] = QuoteValue(DPE_CHAR,$regLama["id_info"]);
                $dbValue[16] = QuoteValue(DPE_CHAR,$regLama["reg_asal"]);
                $dbValue[17] = QuoteValue(DPE_NUMERIC,$regLama["reg_umur"]);
                $dbValue[18] = QuoteValue(DPE_NUMERIC,$regLama["reg_umur_hari"]);
                $dbValue[19] = QuoteValue(DPE_CHAR,$regLama["reg_kartu"]);
                $dbValue[20] = QuoteValue(DPE_CHAR,$regLama["id_prog"]);
                $dbValue[21] = QuoteValue(DPE_CHAR,$regLama["reg_rujukan_id"]);
                $dbValue[22] = QuoteValue(DPE_CHAR,$regLama["id_prop"]);
                $dbValue[23] = QuoteValue(DPE_CHAR,$regLama["id_kota"]);
                $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["id_reg_lama"]);
                $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
                $dbValue[26] = QuoteValue(DPE_CHAR,$regLama["reg_shift"]);
                $dbValue[27] = QuoteValue(DPE_CHAR,$regLama["reg_tipe_layanan"]);
                $dbValue[28] = QuoteValue(DPE_NUMERIC,$regLama["reg_umur_bulan"]);                
                $dbValue[29] = QuoteValue(DPE_NUMERIC,$kodeUrutReg);
                $dbValue[30] = QuoteValue(DPE_CHAR,$kodeTrans);
                $dbValue[31] = QuoteValue(DPE_CHAR,$poliKodeFetch["id_instalasi"]);               
                $dbValue[32] = QuoteValue(DPE_CHAR,$tipeRawat);
                $dbValue[33] = QuoteValue(DPE_NUMERIC,$regUrut);
                $dbValue[34] = QuoteValue(DPE_CHAR,$regLama["id_poli"]);
                $dbValue[35] = QuoteValue(DPE_CHAR,'y');
                $dbValue[36] = QuoteValue(DPE_CHAR,'y');
                $dbValue[37] = QuoteValue(DPE_CHAR,'y');
                $dbValue[38] = QuoteValue(DPE_CHAR,'y');
                $dbValue[39] = QuoteValue(DPE_CHAR,'y');

                if($regLama["reg_jenis_pasien"]=='7') {
                $dbValue[40] = QuoteValue(DPE_CHAR,$regLama["id_perusahaan"]);
                }elseif($regLama["reg_jenis_pasien"]=='5' || $regLama["reg_jenis_pasien"]=='26') { 
                $dbValue[40] = QuoteValue(DPE_CHAR,$regLama["reg_tipe_jkn"]);
                $dbValue[41] = QuoteValue(DPE_CHAR,$regLama["reg_no_sep"]);
                }elseif($regLama["reg_jenis_pasien"]=='18') { 
                $dbValue[40] = QuoteValue(DPE_CHAR,$regLama["id_jamkesda_kota"]);
                }elseif($regLama["reg_jenis_pasien"]=='25'){
                $dbValue[40] = QuoteValue(DPE_CHAR,$regLama["reg_tipe_paket"]);
                }
                //echo $_POST['apotik'];
                //print_r($dbValue);
                //die();
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                
                $dtmodel->Insert() or die("insert error");
                
                //echo $cek_nya."<br />";
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);  

                // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
          $dbField[8] = "id_waktu_tunggu_status";
            
          $waktuTungguId = $dtaccess->GetTransID(); 
             
          $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$status);
          $dbValue[6] = QuoteValue(DPE_CHAR,"Pembelian / Input Data Farmasi");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$status);
          
                
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");    

         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);     
?>