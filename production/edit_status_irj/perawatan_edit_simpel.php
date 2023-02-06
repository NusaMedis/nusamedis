<?php
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."currency.php");
require_once($LIB."expAJAX.php"); 

require_once($LIB."bit.php");
require_once($LIB."tree.php");

require_once($LIB."tampilan.php");

$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$tgl = date("d-m-Y");
$userData = $auth->GetUserData();
$userId = $auth->GetUserId(); 
$userName = $auth->GetUserName();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$poliId = $auth->IdPoli();

    /* if(!$auth->IsAllowed("sirs_flow_status_pasien_irj",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("sirs_flow_status_pasien_irj",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
        } */

        $plx = new expAJAX("SimpanOdontogram");   
        $_x_mode = "New";
        $thisPage = "perawatan_edit_simpel.php";
        $backPage = "pasien_view.php";    
        $kembali = "kedatangan_pasien.php?"; 
        $lokasiFoto = $ROOT."gambar/foto_pasien";
        $lokTakeFoto = $ROOT."/gambar/foto_gigi";  
        $lokasi = $ROOT."/gambar/foto_gigi";
        $lokasiXray = $ROOT."/gambar/foto_xray_gigi";
        $reg = $_GET["id"];

        function SimpanOdontogram($gigiKe,$tindakan,$idReg)
        {                                               
          global $dtaccess,$depId,$reg;
          if($tindakan==1){
            $gigi='O';
          } else if($tindakan==2){
            $gigi='P';
          } else if($tindakan==3){
            $gigi='A';
          } else if($tindakan==4){
            $gigi='I';
          } else if($tindakan==5){
            $gigi='C';
          } else if($tindakan==6){
            $gigi='S';
          } else if($tindakan==7){
            $gigi='N';
          } else {
            $gigi='N';
          }

          

/*            if ($tindakan=='1' || $tindakan=='2' || $tindakan=='4' || $tindakan=='5' || $tindakan=='7') {
                 $sql = "UPDATE global.global_customer_user set cust_usr_gigi".$gigiKe." = ".QuoteValue(DPE_CHAR,$gigi); 
                 $sql .= " where cust_usr_id = ".QuoteValue(DPE_CHAR,$dataPasien["id_cust_usr"]);                  
                 $rs = $dtaccess->Execute($sql); 
          //  return $sql;
             }    
            if ($tindakan=='3' || $tindakan=='6' || $tindakan=='7') {
                 $sql = "UPDATE global.global_customer_user set cust_usr_gigi".$gigiKe."b = ".QuoteValue(DPE_CHAR,$gigi);          
                 $sql .= " where cust_usr_id = ".QuoteValue(DPE_CHAR,$dataPasien["id_cust_usr"]);  
                 $rs = $dtaccess->Execute($sql);
               }  */


             }  
  //---Untuk Hapus TIndakan--/     
           // buat hapus tindakannya jika ada //
             if ($_GET["del"]) { 

               $rawatId = $_GET["id"];
               $sql = "delete from  klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR,$rawatId);
               $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
               $kembali = $ROOT."management/module/admin_edit/registrasi/edit_kedatangan_view.php?";
               header("location:".$kembali);
               exit();    
             }

             $sql = "select id_cust_usr from klinik.klinik_registrasi 
             where reg_id = ".QuoteValue(DPE_CHAR,$idReg)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataPasien= $dtaccess->Fetch($sql);


             if ($_GET["id"] || $_GET["id_reg"]) {

          // === buat ngedit ---          
              if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
             } else { 
               $_x_mode = "Edit";
               $_POST["rawat_id"] = $enc->Decode($_GET["id"]); 
               $_POST["id_reg"] = $_GET["id_reg"]; 
             }

             $sql = "select c.*, b.reg_tanggal,b.id_pembayaran, b.id_dep as dep,b.reg_bayar, b.reg_kelas, b.reg_status, 
             b.id_dokter, b.reg_no_sep, b.reg_shift, b.id_perusahaan, b.id_jamkesda_kota, b.id_poli, b.reg_waktu, b.reg_jenis_pasien, reg_status_kondisi
             from klinik.klinik_registrasi b                     
             join global.global_customer_user c on b.id_cust_usr = c.cust_usr_id
             left join klinik.klinik_jadwal d on d.id_reg = b.reg_id
             left join global.global_auth_poli e on b.id_poli = e.poli_id
             left join global.global_shift f on f.shift_id = b.reg_shift
             where b.reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
             $row_edit = $dtaccess->Fetch($sql);
         // echo $sql;


             $_POST["reg_bayar"] = $row_edit["reg_bayar"];
             $_POST["pasien_pemeriksaan_fisik"] = $row_edit["rawat_pemeriksaan_fisik"];
             $_POST["pasien_penunjang"] = $row_edit["rawat_penunjang"];
             $_POST["rawat_id"] = $row_edit["rawat_id"];
             $_POST["cust_usr_jenis"]  = $row_edit["reg_jenis_pasien"];
             $_POST["klinik"]  = $row_edit["dep"];
             $_POST["reg_status"] = $row_edit["reg_status"];
             $_POST["reg_asal"]  = $row_edit["reg_asal"];
             $_POST["cust_usr_no_identitas"]  = $row_edit["reg_kartu"];
             $_POST["reg_shift"]  = $row_edit["reg_shift"];
             $_POST["shift_nama"]  = $row_edit["shift_nama"];
             $_POST["shift_jam_awal"]  = $row_edit["shift_jam_awal"];
             $_POST["shift_jam_akhir"]  = $row_edit["shift_jam_akhir"];
             $_POST["reg_kelas"] = $row_edit["reg_kelas"]; 

             $_POST["id_perusahaan"] = $row_edit["id_perusahaan"];
             $_POST["id_pembayaran"] = $row_edit["id_pembayaran"];
             $_POST["id_jamkesda_kota"] = $row_edit["id_jamkesda_kota"];
             $_POST["cust_usr_jkn"] = $row_edit["cust_usr_jkn"];
             $_POST["reg_status_kondisi"] = $row_edit["reg_status_kondisi"];

         // $_POST["id_reg"] = $row_edit["reg_id"];
             $_POST["cust_usr_kode"] = $row_edit["cust_usr_kode"];
             $_POST["cust_usr_nama"] = $row_edit["cust_usr_nama"];
             $_POST["cust_usr_alamat"] = $row_edit["cust_usr_alamat"];
             $_POST["cust_usr_no_hp"] = $row_edit["cust_usr_no_hp"];
             $_POST["id_cust_usr"] = $row_edit["cust_usr_id"];
             $_POST["reg_jenis_pasien"] = $row_edit["reg_jenis_pasien"];
             $_POST["cust_usr_jenis_kelamin"] = $row_edit["cust_usr_jenis_kelamin"];
             $umurPasien = explode("~",$row_edit["cust_usr_umur"]);
             $_POST["tahun"] = $umurPasien[0];   

             $_POST["rawat_id"] = $row_edit["rawat_id"];
             $_POST["id_dokter"] = $row_edit["id_dokter"];          
             $_POST["reg_no_sep"] = $row_edit["reg_no_sep"];
             $_POST["rawat_tanggal"] = format_date($row_edit["rawat_tanggal"]);          
             $_POST["reg_tanggal"] = format_date($row_edit["reg_tanggal"]);
             $_POST['reg_waktu'] = $row_edit['reg_waktu'];
             $_POST["rawat_anamnesa"] = $row_edit["rawat_anamnesa"]; 
             $_POST["rawat_keluhan"] = $row_edit["rawat_keluhan"];
             $_POST["rawat_terapi"] = $row_edit["rawat_terapi"];
             $_POST["rawat_catatan"] = $row_edit["rawat_catatan"];
             $_POST["jadwal_selanjutnya"] = format_date($row_edit["jadwal_tanggal"]);
             $_POST["jadwal_id"] = $row_edit["jadwal_id"];
             $_POST["id_jam"] = $row_edit["id_jam"];
             $_POST["poli_nama"] = $row_edit["poli_nama"];
             $_POST["id_poli"] = $row_edit["id_poli"];    

             $penting = explode ("-", $row_edit["rawat_penting"]);
             $_POST["rawat_penting_anamnesa"] = $penting[0];
             $_POST["rawat_penting_keluhan"] = $penting[1];
             $_POST["rawat_penting_terapi"] = $penting[2];
             $_POST["rawat_penting_catatan"] = $penting[3];

             $rWaktu = explode(" ", $row_edit["rawat_waktu"]);
             $xTime = explode(":", $rWaktu[1]);

             $_POST["jam"] = $xTime[0];
             $_POST["menit"] = $xTime[1];
             $_POST["detik"] = $xTime[2];


             if($_POST["jam"]){
               $_POST["jam"] = $_POST["jam"];
             }else{
               $_POST["jam"]= date('H');
             }

             if($_POST["menit"]){
               $_POST["menit"]= $_POST["menit"];
             }else{
               $_POST["menit"]= date('i');
             }

             if($_POST["detik"]){
               $_POST["detik"]= $_POST["detik"];
             }else{
               $_POST["detik"]= date('s');
             }


             $sql = "select id_cust_usr, id_dokter from klinik.klinik_registrasi 
             where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataPasien= $dtaccess->Fetch($sql);

        //$_POST["id_dokter"] = $dataPasien["id_dokter"];


     // cari jenis pasien ee --
             $sql = "select a.* from global.global_jenis_pasien a where jenis_id =".QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"])." order by a.jenis_id asc ";
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
             $dataJnsPasien = $dtaccess->Fetch($rs);

   // cari shift pasien ee --
             $sql = "select a.* from global.global_shift a where shift_id =".QuoteValue(DPE_CHAR,$_POST["reg_shift"])." order by a.shift_id asc ";
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
             $shift = $dtaccess->Fetch($rs);

    // cari biaya pembayarannnya //
             $sql = "select a.* from klinik.klinik_pembayaran a where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
             $dataPembayaran = $dtaccess->Fetch($rs);

     // siapa yg update //
             $sql = "select a.* from global.global_auth_user a where usr_name =".QuoteValue(DPE_CHAR,$_POST["reg_who_update"]);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
             $whoIsUpdate = $dtaccess->Fetch($rs);    

             $sql = "select b.*, a.reg_no_antrian, c.shift_nama, c.shift_jam_awal, c.shift_jam_akhir, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
             b.cust_usr_jenis, cust_usr_tanggal_lahir, a.id_cust_usr, a.reg_periksa_gratis, b.cust_usr_alamat, cust_usr_no_hp, 
             ((current_date - cust_usr_tanggal_lahir)/365) as umur, a.reg_shift
             from klinik.klinik_registrasi a
             left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
             left join global.global_shift c on c.shift_id = a.reg_shift               
             where a.reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
             $dataPasien= $dtaccess->Fetch($sql); 

             $lokasiFoto = $ROOT."gambar/foto_pasien";
             $lokTakeFoto = $ROOT."/gambar/foto_gigi";

             $sql = "select * from global.global_departemen a where dep_id = ".QuoteValue(DPE_CHAR,$depId);
             $rs = $dtaccess->Execute($sql); 
             $dataLabel = $dtaccess->FetchAll($rs);
           }      


  // ----- simpan data ----- //
           if ($_POST["btnSave"]) {

/*          if($_POST["rawat_penting_anamnesa"]) $anamnesa = "1";  else $anamnesa = "0";
          if($_POST["rawat_penting_keluhan"]) $keluhan = "1"; else $keluhan = "0";
          if($_POST["rawat_penting_terapi"]) $terapi = "1"; else $terapi = "0";
          if($_POST["rawat_penting_catatan"]) $catatan = "1"; else $catatan = "0";
          $hasil = $anamnesa."-".$keluhan."-".$terapi."-".$catatan;
          
           // Rawat Diagnosa Utama pada klinik perawatan //
           $sql = "select * from klinik.klinik_icd where icd_id = '".$_POST["id_icd"]."'";
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
           $dataicdRawat = $dtaccess->Fetch($rs);
           
           $_POST["icd_nama"] = $dataicdRawat["icd_nama"];
           $_POST["pasien_diagnosa_utama"] = $dataicdRawat["icd_nama"];

            */      
            // cek jika pasien ee bukan pasien berbayar --
           if($_POST["cust_usr_jenis"]!='2') {

               // JIKA pasien gratis dan SKTM //
             if($_POST["cust_usr_jenis"]=='5' || $_POST["cust_usr_jenis"]=='6') {
               $sql = "update klinik.klinik_folio set who_when_update = ".QuoteValue(DPE_NUMERIC,$siUpdate["who_when_update"])." , fol_nominal =".QuoteValue(DPE_NUMERIC,'0.00')." , fol_dibayar =".QuoteValue(DPE_NUMERIC,'0.00')." where fol_id =".QuoteValue(DPE_CHAR,$folId);
               $rs = $dtaccess->Execute($sql);
             }             
           }

          // update tanggal registrasinya //
           $sql = "update klinik.klinik_registrasi set reg_tanggal=".QuoteValue(DPE_DATE,date_db($_POST["reg_tanggal"])).", reg_waktu=".QuoteValue(DPE_CHAR,$_POST["reg_waktu"])." 
           ,id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
           reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"])."
           where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          //echo $sql;
         // die();        
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

    //update status pasien
           $sql = "update klinik.klinik_registrasi set reg_status=".QuoteValue(DPE_CHAR,$_POST["reg_status"])." 
           ,id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
           reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]).", reg_status_kondisi = ".QuoteValue(DPE_CHAR, $_POST['reg_status_kondisi'])."
           where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);           
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

            //update shift pasien
           $sql = "update klinik.klinik_registrasi set reg_shift=".QuoteValue(DPE_CHAR,$_POST["reg_shift"])." 
           ,id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
           reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"])."
           where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);           
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

          //update status pasien
           $sql = "update klinik.klinik_registrasi set reg_kelas=".QuoteValue(DPE_CHAR,$_POST["reg_kelas"])." 
           ,id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
           reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"])."                  
           where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);           
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);


            $sql = "update klinik.klinik_preop set id_kelas=".QuoteValue(DPE_CHAR,$_POST["reg_kelas"])." 
                         
           where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);           
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

           $sql = "update klinik.klinik_registrasi set reg_no_sep = ".QuoteValue(DPE_CHAR,$_POST["noSep"]).",
           id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]).",
           id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
           reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]).",
           id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]).",
           id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]).",
           reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"])."                  
           where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);           
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 

           $sql = "update global.global_customer_user set cust_usr_jenis = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]).",
           cust_usr_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"]).",
           id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"])."
           where cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);





           // Proses Edit FOlio Bppjs

           if($_POST["cust_usr_jenis"]=="5" || $_POST["cust_usr_jenis"]=="7" || $_POST["cust_usr_jenis"]=="26"){
            $sql = "select fol_nominal,id_biaya_tarif, fol_id from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
            $dataFolio = $dtaccess->FetchAll($sql);
                //print_r($dataFolio); die();
            for($i=0,$n=count($dataFolio);$i<$n;$i++){
              $sql = "update klinik.klinik_folio set fol_dijamin=".QuoteValue(DPE_NUMERIC,StripCurrency($dataFolio[$i]["fol_nominal"])).",  
              fol_hrs_bayar='0', fol_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_jenis"])." where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." and fol_id=".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
              $dtaccess->Execute($sql);
                  //echo $sql; die();
            }

            // $sql = "select * where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." and fol_jenis_sem='VT'";
            // $dataFolio = $dtaccess->FetchAll($sql);
            // print_r($dataFolio); die();
            for($i=0,$n=count($dataFolio);$i<$n;$i++){

              $sql = "select a.biaya_id,a.biaya_nama, b.biaya_total, b.biaya_tarif_id
              from klinik.klinik_biaya a
              left join klinik.klinik_biaya_tarif b on a.biaya_id = b.id_biaya  
              where b.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["id_biaya_tarif"]);
              $rs = $dtaccess->Execute($sql);
              $biaya = $dtaccess->Fetch($rs);  

                #cari data fol pel id lama
              $sql = "select fol_pelaksana_id from klinik.klinik_folio_pelaksana
              where id_fol = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
              $rs = $dtaccess->Execute($sql);
              $pel_lama = $dtaccess->FetchAll($rs);

  //cari split biaya
              $sql = "select id_split, bea_split_nominal from klinik.klinik_biaya_split   
              where id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["id_biaya_tarif"]);
              $rs = $dtaccess->Execute($sql);
              $biayaSplit = $dtaccess->Fetch($rs); 

              $sql = "select fol_id, fol_nominal, fol_jumlah from klinik.klinik_folio
              where fol_id = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
              $rs = $dtaccess->Execute($sql);
              $folio = $dtaccess->Fetch($rs); 

              $sql = "select id_split ,id_biaya, bea_split_persen,bea_split_nominal from klinik.klinik_biaya_split a  
              left join klinik.klinik_split b on a.id_split = b.split_id
              where a.id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["id_biaya_tarif"]);
              $rs = $dtaccess->Execute($sql);
              $biayasplit = $dtaccess->FetchAll($rs);
// echo $sql;
              // for ($i = 0; $i < count ($biayasplit); $i++) 
              // {
        //         echo $biayasplit[$i]["id_split"];

        //         $dbTable = "klinik.klinik_folio_split";
        // $dbField[0] = "folsplit_id";   // PK
        // $dbField[1] = "id_fol";
        // $dbField[2] = "id_split";
        // $dbField[3] = "folsplit_nominal";
        // $dbField[4] = "folsplit_nominal_satuan";
        // $dbField[5] = "folsplit_jumlah";
        // $dbField[6] = "id_dep";
        // //$dbField[7] = "id_fol_pelaksana";

        // // $folSplitId = $dtaccess->GetTransID();  

        // $hasilSatuan = $biayaSplit[$i]["bea_split_nominal"];
        // $hasil = ($hasilSatuan)*$dataFolio[$i]["fol_jumlah"];

        // $dbValue[0] = QuoteValue(DPE_CHAR,$folSplitId);
        // $dbValue[1] = QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
        // $dbValue[2] = QuoteValue(DPE_CHAR,$biayasplit[$i]["id_split"]);
        // $dbValue[3] = QuoteValue(DPE_NUMERIC,$hasil);
        // $dbValue[4] = QuoteValue(DPE_NUMERIC,$biayasplit[$i]["bea"]*$hasilSatuan);
        // $dbValue[5] = QuoteValue(DPE_NUMERIC,$dataFolio[$i]["fol_jumlah"]);
        // $dbValue[6] = QuoteValue(DPE_NUMERIC,$depId);
        // //$dbValue[7] = QuoteValue(DPE_CHAR,$folPelId);
        // //print_r($dbValue); die();
        // $dbKey[1] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        // $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        // $dtmodel->Update() or die("insert  error"); 

        // unset($dtmodel);
        // unset($dbField);
        // unset($dbValue);
        // unset($dbKey);

          // $sql = "update klinik.klinik_folio_split set folsplit_nominal=".QuoteValue(DPE_NUMERIC,$hasil).",  
          //     folsplit_nominal_satuan=".QuoteValue(DPE_NUMERIC,$biayasplit[$i]["bea"]*$hasilSatuan).",folsplit_jumlah=".QuoteValue(DPE_NUMERIC,$dataFolio[$i]["fol_jumlah"])." where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." and fol_id=".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
      // }


            }
// end edit fol bpjs







          } 
// edit fol umum
          elseif($_POST["cust_usr_jenis"]=="2"){
            $sql = "select fol_nominal,id_biaya_tarif, fol_id from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
            $dataFolio = $dtaccess->FetchAll($sql);
                //print_r($dataFolio); die();
            for($i=0,$n=count($dataFolio);$i<$n;$i++){
              $sql = "update klinik.klinik_folio set fol_hrs_bayar=".QuoteValue(DPE_NUMERIC,StripCurrency($dataFolio[$i]["fol_nominal"])).",  
              fol_dijamin='0', fol_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_jenis"])." where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_id=".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
              $dtaccess->Execute($sql);
                  //echo $sql; die();
            }

            for($i=0,$n=count($dataFolio);$i<$n;$i++){

              $sql = "select a.biaya_id,a.biaya_nama, b.biaya_total, b.biaya_tarif_id
              from klinik.klinik_biaya a
              left join klinik.klinik_biaya_tarif b on a.biaya_id = b.id_biaya  
              where b.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["id_biaya_tarif"]);
              $rs = $dtaccess->Execute($sql);
              $biaya = $dtaccess->Fetch($rs);  

                #cari data fol pel id lama
              $sql = "select fol_pelaksana_id from klinik.klinik_folio_pelaksana
              where id_fol = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
              $rs = $dtaccess->Execute($sql);
              $pel_lama = $dtaccess->FetchAll($rs);

  //cari split biaya
              $sql = "select id_split, bea_split_nominal from klinik.klinik_biaya_split   
              where id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["id_biaya_tarif"]);
              $rs = $dtaccess->Execute($sql);
              $biayaSplit = $dtaccess->Fetch($rs); 

              $sql = "select fol_id, fol_nominal, fol_jumlah from klinik.klinik_folio
              where fol_id = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
              $rs = $dtaccess->Execute($sql);
              $folio = $dtaccess->Fetch($rs); 

              $sql = "select id_split ,id_biaya, bea_split_persen,bea_split_nominal from klinik.klinik_biaya_split a  
              left join klinik.klinik_split b on a.id_split = b.split_id
              where a.id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["id_biaya_tarif"]);
              $rs = $dtaccess->Execute($sql);
              $biayasplit = $dtaccess->FetchAll($rs);
      // echo $sql;

      // for ($i = 0; $i < count ($biayasplit); $i++) 
      // {
      //   echo $biayasplit[$i]["id_split"];
      //INSERT KLINIK BIAYA SPLIT
        // $dbTable = "klinik.klinik_folio_split";
        // $dbField[0] = "folsplit_id";   // PK
        // $dbField[1] = "id_fol";
        // $dbField[2] = "id_split";
        // $dbField[3] = "folsplit_nominal";
        // $dbField[4] = "folsplit_nominal_satuan";
        // $dbField[5] = "folsplit_jumlah";
        // $dbField[6] = "id_dep";
        // //$dbField[7] = "id_fol_pelaksana";

        // // $folSplitId = $dtaccess->GetTransID();  

        // $hasilSatuan = $biayaSplit[$i]["bea_split_nominal"];
        // $hasil = ($hasilSatuan)*$dataFolio[$i]["fol_jumlah"];

        // $dbValue[0] = QuoteValue(DPE_CHAR,$folSplitId);
        // $dbValue[1] = QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
        // $dbValue[2] = QuoteValue(DPE_CHAR,$biayaSplit[$i]["id_split"]);
        // $dbValue[3] = QuoteValue(DPE_NUMERIC,$hasil);
        // $dbValue[4] = QuoteValue(DPE_NUMERIC,$biayaSplit[$i]["bea"]*$hasilSatuan);
        // $dbValue[5] = QuoteValue(DPE_NUMERIC,$dataFolio[$i]["fol_jumlah"]);
        // $dbValue[6] = QuoteValue(DPE_NUMERIC,$depId);
        // //$dbValue[7] = QuoteValue(DPE_CHAR,$folPelId);
        // //print_r($dbValue); die();
        // $dbKey[1] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        // $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        // $dtmodel->Update() or die("insert  error"); 

        // unset($dtmodel);
        // unset($dbField);
        // unset($dbValue);
        // unset($dbKey);
      // }

            }
          } elseif($_POST["reg_jenis_pasien"]=="18"){
              // Panggil Persentase Jamkesda
           $sqlJamkesda = "select * from global.global_jamkesda_kota where jamkesda_kota_id = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
           $dataJamkesda = $dtaccess->Fetch($sqlJamkesda);
           $jamkesdaNama=$dataJamkesda["jamkesda_kota_nama"];
           $jamkesdaPesentaseKota=$dataJamkesda["jamkesda_kota_persentase_kota"];
           $jamkesdaPesentaseProv=$dataJamkesda["jamkesda_kota_persentase_prov"];

           $sql = "select fol_nominal, fol_id, fol_waktu from klinik.klinik_folio where 
           id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." order by fol_waktu asc";
           $dataFolio = $dtaccess->FetchAll($sql);
              //print_r($dataFolio); die();
           for($i=0,$n=count($dataFolio);$i<$n;$i++){
            $jaminDinkesProv=(StripCurrency($dataFolio[$i]["fol_nominal"])*StripCurrency($jamkesdaPesentaseProv)/100);
            $jaminDinkesKota=(StripCurrency($dataFolio[$i]["fol_nominal"])*StripCurrency($jamkesdaPesentaseKota)/100);
            $totalJaminan=StripCurrency($jaminDinkesKota)+StripCurrency($jaminDinkesProv);
            $hrsBayar = StripCurrency($dataFolio[$i]["fol_nominal"])-StripCurrency($totalJaminan);

            $sql = "update klinik.klinik_folio set fol_dijamin=".QuoteValue(DPE_NUMERIC,StripCurrency($totalJaminan)).",  
            fol_hrs_bayar=".QuoteValue(DPE_NUMERIC,$hrsBayar).", fol_dijamin1=".QuoteValue(DPE_NUMERIC,StripCurrency($jaminDinkesProv)).",
            fol_dijamin2=".QuoteValue(DPE_NUMERIC,StripCurrency($jaminDinkesKota)).", fol_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_jenis"])." 
            where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_id=".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
                //echo $sql; die();
            $dtaccess->Execute($sql);
          }
        }

        // history
       /*           $dbTable = "klinik_rawat_inap_history";
          $dbField[0] = "rawat_inap_history_id";   // PK
          $dbField[1] = "rawat_inap_history_who_update";
          $dbField[2] = "rawat_inap_history_when_update";
          $dbField[3] = "rawat_inap_history_tanggal";
          $dbField[4] = "id_reg";
          $dbField[5] = "rawat_inap_history_kelas";
          $dbField[6] = "rawat_inap_history_status";          
          $dbField[7] = "rawat_inap_history_rawat_jalan";
          $dbField[8] = "rawat_inap_history_poli_tujuan";
          //$dbField[7] = "rawat_inap_history_kamar";
          //$dbField[8] = "rawat_inap_history_bed";
          //$dbField[9] = "rawat_inap_history_kelas_tujuan";
          //$dbField[10] = "rawat_inap_history_kamar_tujuan";
          //$dbField[11] = "rawat_inap_history_bed_tujuan";
          //$dbField[9] = "rawat_inap_history_cara_keluar";
          
          $rawat_inap_history_id = $dtaccess->GetTransID("klinik_rawat_inap_history","rawat_inap_history_id",DB_SCHEMA_KLINIK);
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$rawat_inap_history_id);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$userData["name"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,date("Y-m-d H:i:s"));
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["reg_kelas"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,T);    // a = awal, p = pulang, t = transfer
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["reg_status"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
          //$dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          //$dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);
          //$dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_kategori_kamar_tujuan"]);
          //$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_kamar_tujuan"]);
          //$dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_bed_tujuan"]);
          //$dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_keadaan_keluar_inap"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

          $dtmodel->Insert() or die("insert  error"); 
            
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);  */      
        // end history     

          if ($_POST["reg_jenis_pasien_lama"] != $_POST["cust_usr_jenis"]) {
             # code...
           $back = "ubah_tindakan.php?&id_pembayaran=".$_POST["id_pembayaran"]."&id_cust_usr=".$_POST["id_cust_usr"]."&cust_usr_jenis=".$_POST['cust_usr_jenis']."&jenis_pasien_lama=".$_POST['reg_jenis_pasien_lama']."&id_reg=".$_POST['id_reg'];
           echo $back;

           // header("location:".$back);
           echo "<script>document.location.href='".$back."';</script>";

         }
         else{
          if($_POST["btnSave"]) echo "<script>document.location.href='".$backPage."';</script>";   
          else echo "<script>document.location.href='".$thisPage."&id=".$enc->Encode($_POST["rawat_id"])."&id_reg=".$_POST["id_reg"]."';</script>";
          exit();    


        }  



      }

    /*// -- cari shift ---
     $sql = "select * from global.global_jam order by jam_nama";
     $rs = $dtaccess->Execute($sql);
     $dataShift = $dtaccess->FetchAll($rs);*/

   // cari shift ee --
     $sql = "select * from global.global_shift order by shift_nama desc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);
     
    // buat ambil tindakan --
    //$sql = "select * from klinik.klinik_biaya where biaya_jenis = 'TA' ";
    //$datatindakan= $dtaccess->FetchAll($sql);

         // cari kategori tindkannya //
     $sql = "select * from  klinik.klinik_kategori_tindakan "; 
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataKategori = $dtaccess->FetchAll($rs_edit);
     
     // --- cari poli ---
     $sql = "select poli_nama,poli_id, id_biaya from global.global_auth_poli where poli_id > '0' order by poli_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);
     
      // cari jenis pasien e
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_departemen order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);
     
     // --- cari kelas ---
     $sql = "select * from klinik.klinik_kelas order by kelas_id";
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs);
     
     // --- Panggil Dokter ---
     $sql = "select * from global.global_auth_user where (id_rol = '5' or id_rol='2') order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);
     
     // cari nama perusahaan --
     $sql = "select * from global.global_perusahaan where id_dep =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $NamaPerusahaan = $dtaccess->FetchAll($rs);
     
     // cari nama kota jamkesda --
     $sql = "select * from global.global_jamkesda_kota where id_dep =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $NamaKota = $dtaccess->FetchAll($rs);
     
       // cari kategori jkn --
     $sql = "select * from global.global_jkn order by jkn_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJKN = $dtaccess->FetchAll($rs);

     $sql = "select * from global.global_kondisi_akhir_pasien order by kondisi_akhir_pasien_id asc";
     $dataKondisiAkhir = $dtaccess->FetchAll($sql);

     ?>  

     <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
     <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>
     <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/kinetic-v3.js"></script>
     <script type="text/javascript" language="javascript" src="ajax.js"></script>
     <script type="text/javascript">

      <? $plx->Run(); ?>

      function Kembali()
      {
        document.location.href='<?php echo $backPage;?>';
      }

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan menghapus data ini???'));
  else return false;
} 

window.onload = function() { TampilCombo(); }
function TampilCombo(id)
{        

         //alert(id);
         if(id=="7"){
          id_perusahaan.disabled = false;
              //elm_combo.checked = true; 

            } else {
              id_perusahaan.disabled = true;
            }
            if(id=="18"){
              id_jamkesda_kota.disabled = false;
              //elm_combo.checked = true; 

            } else {
              id_jamkesda_kota.disabled = true;
            }
            if(id=="5"){
              cust_usr_jkn.disabled = false;
              //elm_combo.checked = true; 

            } else {
              cust_usr_jkn.disabled = true;
            }
          }

          function CheckDataSave(frm)
          {   
            if(!frm.rawat_tanggal.value){
              alert('Tanggal Pemeriksaan harus di isi');
              return false;
            } 

          }

          function ChangeDisplay(id) {
           var disp = Array();

           disp['none'] = 'block';
           disp['block'] = 'none';

           document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
         }

         function CheckDataSave(frm)
         {   
          <?php if(!$_GET["id"]) { ?>

            if(!frm.id_reg.value){
             alert('Maaf, Anda belum memasukkan pasien');
             return false;
           }

         <?php } ?>    
       } 


       $(document).ready(function() {
        $(".topMenuActionFotoxGigi").click( function() {
         if ($("#openCloseIdentifierFotoxGigi").is(":hidden")) {
          $("#sliderFotoxGigi").animate({ 
           marginTop: "-370px"                     
         }, 900 );
          $("#topMenuImageFotoxGigi").html('<img src="<?php echo $ROOT;?>gambar/ondo/open5.png" alt="open Tindakan" />');
          $("#openCloseIdentifierFotoxGigi").show();
        } else {
          $("#sliderFotoxGigi").animate({ 
           marginTop: "0px"
         }, 900 );
          $("#topMenuImageFotoxGigi").html('<img src="<?php echo $ROOT;?>gambar/ondo/open5.png" alt="close Tindakan" />');
          $("#openCloseIdentifierFotoxGigi").hide();
        }
      });  
      });


       $(document).ready(function() {
        $(".topMenuActionGigi").click( function() {
         if ($("#openCloseIdentifierGigi").is(":hidden")) {
          $("#sliderGigi").animate({ 
           marginTop: "-360px"
         }, 900 );
          $("#topMenuImageGigi").html('<img src="<?php echo $ROOT;?>gambar/ondo/open.png" alt="open Tindakan" />');
          $("#openCloseIdentifierGigi").show();
        } else {
          $("#sliderGigi").animate({ 
           marginTop: "0px"
         }, 900 );
          $("#topMenuImageGigi").html('<img src="<?php echo $ROOT;?>gambar/ondo/open.png" alt="close Tindakan" />');
          $("#openCloseIdentifierGigi").hide();
        }
      });  
      });

       $(document).ready(function() {
        $(".topMenuActionFotoGigi").click( function() {
         if ($("#openCloseIdentifierFotoGigi").is(":hidden")) {
          $("#sliderFotoGigi").animate({ 
           marginTop: "-380px"                     
         }, 900 );
          $("#topMenuImageFotoGigi").html('<img src="<?php echo $ROOT;?>gambar/ondo/open2.png" alt="open Tindakan" />');
          $("#openCloseIdentifierFotoGigi").show();
        } else {
          $("#sliderFotoGigi").animate({ 
           marginTop: "0px"
         }, 900 );
          $("#topMenuImageFotoGigi").html('<img src="<?php echo $ROOT;?>gambar/ondo/open2.png" alt="close Tindakan" />');
          $("#openCloseIdentifierFotoGigi").hide();
        }
      });  
      }); 

    </script>
    <script>
      function loadImages(sources, callback){
        var images = {};
        var loadedImages = 0;
        var numImages = 0;
        for (var src in sources) {
          numImages++;
        }
        for (var src in sources) {
          images[src] = new Image();
          images[src].onload = function(){
            if (++loadedImages >= numImages) {
              callback(images);
            }
          };
          images[src].src = sources[src];
        }
      }

      function writeMessage(stage, message){
        var context = stage.getContext();
        stage.clear();
        context.font = "18pt Calibri";
        context.fillStyle = "black";
        context.fillText(message, 10, 25);
      }

      function hilang(stage,shape){
        var context = stage.getContext();
        stage.clear();
        shape.scale(1.5);

      }




    </script>
    <?php// echo $view->InitUpload(); ?>
    <?php //echo $view->InitThickBox(); ?>
    <!-- Buat menu tampilan gambar rekam medik foto gigi -->
    <script src="<?php echo $ROOT;?>lib/script/jquery/coresol/js-coresol.js"></script> 
    <script language="javascript" type="text/javascript">
      $(function() {
        $(".main .jCarouselLite").jCarouselLite({
          btnNext: ".main .next",
          btnPrev: ".main .prev",
          speed: 1000,
          scroll: 1,
          visible: 3
        });           
      });

      $(function() {
        $(".mainset .jCarouselLite").jCarouselLite({
          btnNext: ".mainset .next",
          btnPrev: ".mainset .prev",
          speed: 1000,
          scroll: 1,
          visible: 3
        });           
      });
    </script>
    <script src="<?php echo $ROOT;?>lib/script/jquery/coresol/jquery.jcarousellite.min.js"></script>


    <!-- Buat ambil foto Webcam dan Browse foto gigi pasien ee -->  
    <link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" /> 
    <script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
    <script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        $("a[rel=sepur]").fancybox({
          'transitionIn' : 'elastic',
          'transitionOut' : 'elastic',
          'overlayColor' : '#111'
        });
      });

      $(document).ready(function() {
        $("a[rel=video]").fancybox({
          'width' : '100%',
          'height' : '100%',
          'autoScale' : true,
          'transitionIn' : 'none',
          'transitionOut' : 'none',
          'type' : 'iframe'      
        });
      });     
    </script>
    <script src="<?php echo $ROOT;?>lib/script/jquery/webcam/webcam.js"></script>     
    <script type="text/javascript">
      $(document).ready(function(){
       var camera = $('#camera'),
       photos = $('#photos'),
       screen =  $('#screen');

       var template = '<a href="<?php echo $ROOT;?>gambar/foto_gigi/{src}" rel="cam" '
       +'style="background-image:url(<?php echo $ROOT;?>gambar/thumbails/{src})"></a>';

  /*----------------------------------
    Setting up the web camera
    ----------------------------------*/
    webcam.set_swf_url('<?php echo $ROOT;?>lib/script/jquery/webcam/webcam.swf');
  webcam.set_api_url('upload_gigi.php');  // The upload script
  webcam.set_quality(80);       // JPEG Photo Quality
  webcam.set_shutter_sound(true, '<?php echo $ROOT;?>lib/script/jquery/webcam/shutter.mp3');

  // Generating the embed code and adding it to the page: 
  screen.html(
   webcam.get_html(screen.width(), screen.height())
   );

  /*----------------------------------
    Binding event listeners
    ----------------------------------*/
    var shootEnabled = false;    
    $('#shootButton').click(function(){

      if(!shootEnabled){
       return false;
     }
     webcam.freeze();
     togglePane();
     return false;
   });

    $('#cancelButton').click(function(){
      webcam.reset();
      togglePane();
      return false;
    });

    $('#uploadButton').click(function(){
      webcam.upload();
      webcam.reset();
      togglePane();
      return false;
    });

    camera.find('.settings').click(function(){
      if(!shootEnabled){
       return false;
     }

     webcam.configure('camera');
   });

  // Showing and hiding the camera panel: 
  $('.camTop').click(function(){
    camera.animate({
      bottom:-990
    });
  });

  var showns = false;
  $('.camTops').click(function(){

    if(showns){
     camera.animate({
      bottom:-990
    });
   }
   else {
    camera.animate({
      bottom:-5
    },{easing:'easeOutExpo',duration:'slow'});
  }

  showns = !showns;
});

  /*---------------------- 
    Callbacks
    ----------------------*/
    webcam.set_hook('onLoad',function(){
    // When the flash loads, enable
    // the Shoot and settings buttons:
    shootEnabled = true;
  });

    webcam.set_hook('onComplete', function(msg){

    // This response is returned by upload.php
    // and it holds the name of the image in a
    // JSON object format:
    msg1 = $.parseJSON(msg);
    
    if(msg.error){
      alert(msg1.message);
    }
    else {
       //Adding it to the page;    
       document.getElementById('cust_usr_foto_gigi').value=msg1.filename;
       document.original.src='<?php echo $lokTakeFoto."/";?>'+msg1.filename;
       alert('Upload Foto Gigi Sukses !!!');
       photos.prepend(templateReplace(template,{src:msg1.filename}));
       initFancyBox();
     }
   });

    webcam.set_hook('onError',function(e){
      screen.html(e);
    });


  // This function toggles the two
  // .buttonPane divs into visibility:
  function togglePane(){
    var visible = $('#camera .buttonPane:visible:first');
    var hidden = $('#camera .buttonPane:hidden:first');
    
    visible.fadeOut('fast',function(){
      hidden.show();
    });
  }
  
  // Helper function for replacing "{KEYWORD}" with
  // the respectful values of an object:
  function templateReplace(template,data){
    return template.replace(/{([^}]+)}/g,function(match,group){
      return data[group.toLowerCase()];
    });
  }
});


      function ajaxFileUpload(fileupload,hidval,img)
      {                     
       var lokasi = Array();

       lokasi['xrays_gigi_0'] = '<?php echo $lokasiXray;?>';
       lokasi['xrays_gigi_1'] = '<?php echo $lokasiXray;?>';
       lokasi['xrays_gigi_2'] = '<?php echo $lokasiXray;?>';
       lokasi['xrays_gigi_3'] = '<?php echo $lokasiXray;?>';
       lokasi['xrays_gigi_4'] = '<?php echo $lokasiXray;?>';
       lokasi['xrays_gigi_5'] = '<?php echo $lokasiXray;?>';

       lokasi['foto_gigi_0'] = '<?php echo $lokasi;?>';
       lokasi['foto_gigi_1'] = '<?php echo $lokasi;?>';
       lokasi['foto_gigi_2'] = '<?php echo $lokasi;?>';
       lokasi['foto_gigi_3'] = '<?php echo $lokasi;?>';
       lokasi['foto_gigi_4'] = '<?php echo $lokasi;?>';
       lokasi['foto_gigi_5'] = '<?php echo $lokasi;?>';

       $("#loading")
       .ajaxStart(function(){
         $(this).show();
       })
       .ajaxComplete(function(){
         $(this).hide();
       });
       $.ajaxFileUpload
       (
       {
        url:fileupload,
        secureuri:false,
        fileElementId:'fileToUpload',
        dataType: 'json',
        success: function (data, status)
        {
         if(typeof(data.error) != 'undefined')
         {
          if(data.error != '')
          {
           alert(data.error);
         }else
         {
           alert(data.msg);

           document.getElementById(hidval).value= data.file;
           document.getElementById(img).src=lokasi[img]+'/'+data.file;
         }
       }
     },
     error: function (data, status, e)
     {
       alert(e);
     }
   }
   )

       return false;
     }


     function CekTindakan(frm) {

      if(!frm.id_tindakan_0.value){
        alert('Pilih dahulu Tindakan yang akan dimasukkan');
        frm.id_tindakan_0.focus();
        return false;
      }

      return true;      
    }

    function CekTanggal(frm) {

      if(!frm.rawat_tanggal.value){
        alert('Pilih dahulu Pemeriksaan Tanggal yang akan dimasukkan');
        frm.rawat_tanggal.focus();
        return false;
      }

      return true;      
    }

    function TotalHarga(total) { 
     var tindakan_0 = document.getElementById('totalTind_0').value.toString().replace(/\,/g,"");
     //alert(tindakan_0);
     var tindakan_1 = document.getElementById('totalTind_1').value.toString().replace(/\,/g,"");     
     var tindakan_2 = document.getElementById('totalTind_2').value.toString().replace(/\,/g,"");    
     var tindakan_3 = document.getElementById('totalTind_3').value.toString().replace(/\,/g,"");    
    // var tindakan_4 = document.getElementById('totalTind_4').value.toString().replace(/\,/g,"")     
    // var tindakan_5 = document.getElementById('totalTind_5').value.toString().replace(/\,/g,"");    
     //var tindakan_6 = document.getElementById('totalTind_6').value.toString().replace(/\,/g,"");    
     //var tindakan_7 = document.getElementById('totalTind_7').value.toString().replace(/\,/g,"");     
     //var tindakan_8 = document.getElementById('nom_8').value.toString().replace(/\,/g,"");     
     //var tindakan_9 = document.getElementById('nom_9').value.toString().replace(/\,/g,"");    
     var totReg = document.getElementById('tot_reg').value;

     var total = total.toString().replace(/\,/g,"");
     tindsatuInt=tindakan_0*1;
     tindduaInt=tindakan_1*1;
     tindtigaInt=tindakan_2*1;
     tindempatInt=tindakan_3*1;
//      tindlimaInt=tindakan_4*1;
//      tindenamInt=tindakan_5*1;                                  
//      tindtujuhInt=tindakan_6*1;
//      tinddelapanInt=tindakan_7*1;
     //tindsembilanInt=tindakan_8*1;
     //tindsepuluhInt=tindakan_9*1; 
     totalRegistrasi = totReg*1;
     //totalBiaya=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt+tindtujuhInt+tinddelapanInt+tindsembilanInt+tindsepuluhInt+totalRegistrasi;   
     ///totalBiaya=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt+tindtujuhInt+tinddelapanInt+totalRegistrasi;
     totalBiaya=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+totalRegistrasi;
     
     document.getElementById('txtTotalDibayar').value = formatCurrency(totalBiaya);
     document.getElementById('txtTotalDibayar').focus();

   }

 </script>
 <script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.js"></script>
 <link rel="stylesheet" href="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.css" type="text/css" />

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
                  <h2>Edit Perawatan</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form name="frmEdit" method="POST" autocomplete="off"  action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckDataSave(this)">



                   <div class="col-md-4 col-sm-6 col-xs-12">
                    <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Pemeriksaan Tanggal</label>
                    <input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $_POST['id_pembayaran'] ?>">
                    <input type="hidden" name="reg_jenis_pasien_lama" id="reg_jenis_pasien_lama" value="<?php echo $row_edit["reg_jenis_pasien"] ?>">
                    <div class='input-group date' id='datepicker'>

                      <input name="reg_tanggal" type='text' class="form-control" 
                      value="<?php if ($_POST['reg_tanggal']) { echo $_POST['reg_tanggal']; } else { echo date('d-m-Y'); } ?>"  />
                      <span class="input-group-addon">
                        <span class="fa fa-calendar">
                        </span>
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-6 col-xs-12">
                    <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Pemeriksaan Waktu</label>
                    <input type="text" name="reg_waktu" id="reg_waktu" class="form-control" value="<?php if ($_POST['reg_waktu']) { echo $_POST['reg_waktu']; } else { echo date('H:i:s'); } ?>"  />
                  </div>

                  <table width="100%" border="0" cellpadding="0" cellspacing="0"> 
                   <tr >
                     <td align ="left" colspan="1" ><b>&nbsp;&nbsp;</b></td>
                   </tr>
                   <tr> 
                     <!--- table kiri atas--->
                     <td width="50%">
                       <table class="table table-striped table-bordered dt-responsive nowrap" width="100%" valign="top">
 <!--    <tr>
          <td align="left" class="tablesmallheader" width="20%">&nbsp;Nama Departemen</td>
     <td width="1%"  class="tablecontent" align="center">:</td>
              <td class="tablecontent" width="80%" >
                <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                    <?php $counter = -1;
                      for($i=0,$n=count($dataKlinik);$i<$n;$i++){
                        unset($spacer); 
                        $length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
                      for($j=0;$j<$length;$j++) $spacer .= "..";
                    ?>
                  <option class="inputField" value="<?php echo $dataKlinik[$i]["dep_id"];?>"<?php if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
                    <?php } ?>
                </select>
                </td>
              </tr> -->
              <tr>
                <td width="25%"  class="tablesmallheader">&nbsp;No. RM </td>

                <td  width="70%"  ><label>&nbsp;<?php echo $_POST["cust_usr_kode"]; ?></label></td>      
              </tr>
              <tr>
               <td  width="25%"  class="tablesmallheader">&nbsp;Nama Lengkap </td>

               <td  width="70%"  ><label>&nbsp;<?php echo $_POST["cust_usr_nama"]." / ".$_POST["tahun"]." Tahun"; ?></label></td>   
             </tr>
             <tr>
               <td width= "25%"  class="tablesmallheader">&nbsp;Jenis Kelamin </td>

               <td width= "70%" ><label>&nbsp;<?php echo $jenisKelamin[$_POST["cust_usr_jenis_kelamin"]]; ?></label></td>
             </tr>
             <tr>
               <td  width="25%"  class="tablesmallheader">&nbsp;Alamat </td>

               <td  width="70%"  ><label>&nbsp;<?php echo $_POST["cust_usr_alamat"]; ?></label></td>   
             </tr>
             <tr>
               <td width = "25%" class="tablesmallheader">&nbsp;No HP </td>

               <td width = "70%" ><label>&nbsp;<?php echo $_POST["cust_usr_no_hp"]; ?></label></td>
             </tr>
             <tr>
               <td width="7" class="tablesmallheader">No. Identitas </td>

               <td width="43" class="tablecontent">
                <input type="text" name="cust_usr_no_identitas" id="cust_usr_no_identitas" size="40" value="" class="form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
              </td> 
            </tr>
 <!--    <tr>
         <td width="7" class="tablesmallheader">Status Bayar </td>
         <td width="1%"  class="tablecontent" align="center">:</td>
         <td width="43" class="tablecontent">
         <input type="text" name="reg_bayar" id="reg_bayar" size="10" value="<?php echo $_POST["reg_bayar"]; ?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      </td> 
    </tr> -->

    <tr>                                                                                        
     <td width = "25%" class="tablesmallheader">&nbsp;Jenis Pasien </td>

     <td width = "20%" >
       <select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
        <!--<option value="" >[ Pilih Jenis Pasien ]</option>-->
        <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
          <option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
        <?php } ?>
      </select>

      <select name="cust_usr_jkn" id="cust_usr_jkn" onKeyDown="return tabOnEnter(this, event);">
        <option value="" >[ Pilih Kategori JKN ]</option>
        <?php for($i=0,$n=count($dataJKN);$i<$n;$i++){ ?>
          <option value="<?php echo $dataJKN[$i]["jkn_id"];?>" <?php if($dataJKN[$i]["jkn_id"]==$_POST["cust_usr_jkn"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataJKN[$i]["jkn_nama"];?></option>
        <?php } ?>    
      </select>        

      <select name="id_jamkesda_kota" id="id_jamkesda_kota" onKeyDown="return tabOnEnter(this, event);">
        <option value="" >[ Pilih Nama Kota ]</option>
        <?php for($i=0,$n=count($NamaKota);$i<$n;$i++){ ?>
          <option value="<?php echo $NamaKota[$i]["jamkesda_kota_id"];?>" <?php if($NamaKota[$i]["jamkesda_kota_id"]==$_POST["id_jamkesda_kota"]) echo "selected"; ?>><?php echo ($i+1).". ".$NamaKota[$i]["jamkesda_kota_nama"];?></option>
        <?php } ?>    
      </select>

      <select name="id_perusahaan" id="id_perusahaan" onKeyDown="return tabOnEnter(this, event);">
        <option value="" >[ Pilih Nama Perusahaan ]</option>
        <?php for($i=0,$n=count($NamaPerusahaan);$i<$n;$i++){ ?>
          <option value="<?php echo $NamaPerusahaan[$i]["perusahaan_id"];?>" <?php if($NamaPerusahaan[$i]["perusahaan_id"]==$_POST["id_perusahaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$NamaPerusahaan[$i]["perusahaan_nama"];?></option>
        <?php } ?>    
      </select>    



    </td>

  </tr>
  <tr>                                                                                        
   <td width = "25%" class="tablesmallheader">&nbsp;Poli </td>

   <td width = "70%" ><select name="id_poli" id="id_poli" class="form-control" onKeyDown="return tabOnEnter(this, event);">     
    <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
      <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPoli[$i]["poli_nama"];?></option>
    <?php } ?>
  </select>
</td>
</tr>
<tr>
 <td width="7" class="tablesmallheader">Status Pasien</td>

 <td width="43" class="tablecontent">
  <select name="reg_status" id="reg_status" class="form-control" onKeyDown="return tabOnEnter(this, event);"/>
  <option value="E0" <?php if('E0'==$_POST["reg_status"]) echo "selected"; ?> >Rawat Jalan Awal</option>
  <option value="E1" <?php if('E1'==$_POST["reg_status"]) echo "selected"; ?> >Rawat Jalan</option>          
  <option value="E2" <?php if('E2'==$_POST["reg_status"]) echo "selected"; ?> >Rawat Jalan Pulang</option>
  <option value="M0" <?php if('M0'==$_POST["reg_status"]) echo "selected"; ?> >Pendaftaran Loket</option>
  <option value="M1" <?php if('M1'==$_POST["reg_status"]) echo "selected"; ?> >Pendaftaran Poli Kedua</option>
  <option value="I0" <?php if('I0'==$_POST["reg_status"]) echo "selected"; ?> >Pendaftaran Rawat Inap</option>
  <option value="I1" <?php if('I1'==$_POST["reg_status"]) echo "selected"; ?> >Rawat Inap Pilih Kamar</option>
  <option value="I2" <?php if('I2'==$_POST["reg_status"]) echo "selected"; ?> >Rawat Inap</option>
  <option value="I3" <?php if('I3'==$_POST["reg_status"]) echo "selected"; ?> >Pasien Inap Rencana Pulang</option>
  <option value="I4" <?php if('I4'==$_POST["reg_status"]) echo "selected"; ?> >Pasien Pulang Belum Bayar</option>
  <option value="I5" <?php if('I5'==$_POST["reg_status"]) echo "selected"; ?> >Pasien Pulang Sudah Bayar</option>
  <option value="G0" <?php if('G0'==$_POST["reg_status"]) echo "selected"; ?> >Pendaftaran IGD</option>
  <option value="G1" <?php if('G1'==$_POST["reg_status"]) echo "selected"; ?> >Tindakan IGD</option>
  <option value="G2" <?php if('G2'==$_POST["reg_status"]) echo "selected"; ?> >IGD Pulang</option>
</select>
</td> 
</tr>
<tr>
 <td>Kondisi Akhir </td>
 <td>
   <select name="reg_status_kondisi" class="form-control">
     <option value="">Kosong</option>
     <?php for ($i = 0; $i < count($dataKondisiAkhir); $i++) { ?>
       <option value="<?php echo $dataKondisiAkhir[$i]['kondisi_akhir_pasien_id'] ?>" <?php if ($_POST['reg_status_kondisi'] == $dataKondisiAkhir[$i]['kondisi_akhir_pasien_id']) echo "selected"; ?>> <?php echo $dataKondisiAkhir[$i]['kondisi_akhir_pasien_nama'] ?> </option>}
       option
     <?php } ?>
   </select>
 </td>
</tr>
<tr>                                                                                        
 <td width = "25%" class="tablesmallheader">&nbsp;Shift </td>

 <td width = "70%" ><select name="reg_shift" id="reg_shift" class="form-control" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
  <?php for($i=0,$n=count($dataShift);$i<$n;$i++){ ?>
    <option value="<?php echo $dataShift[$i]["shift_id"];?>" <?php if($dataShift[$i]["shift_id"]==$_POST["reg_shift"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_nama"]." (".$dataShift[$i]["shift_jam_awal"]."-".$dataShift[$i]["shift_jam_akhir"].")";?></option>
  <?php } ?>
</select>
</td>
</tr>
<tr>                                                                                        
 <td width = "25%" class="tablesmallheader">&nbsp;Kelas </td>
 <td width = "70%" ><select name="reg_kelas" id="reg_kelas" onKeyDown="return tabOnEnter(this, event);" class="form-control"> <!--onChange="this.form.submit();" -->
  <?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
    <option value="<?php echo $dataKelas[$i]["kelas_id"];?>" <?php if($dataKelas[$i]["kelas_id"]==$_POST["reg_kelas"]) echo "selected"; ?>><?php echo $dataKelas[$i]["kelas_nama"];?></option>
  <?php } ?>
</select>
</td>

</tr>
<tr>                                                                                        
 <td width = "25%" class="tablesmallheader">&nbsp;NO. SEP </td>

 <td width = "70%"> <input type="text" name="noSep" class="form-control" value="<?php echo $_POST["reg_no_sep"];?>" size="40"> </td>
</tr>
<tr>                                                                                        
 <td width = "25%" class="tablesmallheader">&nbsp;Dokter </td>

 <td width = "70%"> 
  <select name="id_dokter" id="id_dokter" class="form-control" onKeyDown="return tabOnEnter(this, event);">
    <option value="--">[ Pilih Dokter ]</option>      
    <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
      <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($_POST["id_dokter"]==$dataDokter[$i]["usr_id"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
    <?php } ?>
  </select>           
</td>
</tr>


</table> 
</td>

</tr>             
</table>    

</td>
</tr>

</table>

<table width="100%" border="0" cellpadding="1" cellspacing="1">
  <tr align ="center" >
   <td align="left"  class="tableheader" colspan="2"><b>&nbsp;&nbsp;</b></td>
 </tr>
 <tr> 
   <!--- table kiri atas--->
   <td width="100%" colspan="2"></td>
 </tr>
</table> 
<tr>
  <td colspan="2" align="center" class="tableheader">

   <input type="submit" name="<? if($_x_mode == "Edit"){?>btnSave<?}?>" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CekTanggal(document.frmEdit);"/>     
   <input type="button" name="btnDel" id="btnDel" value="Kembali" class="submit" onClick="javascript:return Kembali();" />  
 </td>
</tr> 
</td>
</tr>
</table>  
<?php //} ?>
<script type="text/javascript">
  function findValue(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');

    //alert("The value you selected was: " + sValue);
    document.getElementById('icd_nama').value=values[0];
    document.getElementById('id_icd').value=values[1];
    document.getElementById('icd_nomor').focus();
  }

  function selectItem(li) {
   findValue(li);
 }

 function formatItem(row) {

  var alamat = row[1].split('~');
  
  if(row[0]) {
    document.getElementById('icd_nama').value=alamat[0];
    document.getElementById('id_icd').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";

}

  //-------------------ICD 2
  
  function findValue2(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');

    //alert("The value you selected was: " + sValue);
    document.getElementById('icd_nama2').value=values[0];
    document.getElementById('id_icd2').value=values[1];
    document.getElementById('icd_nomor2').focus();
  }
  
  function selectItem2(li) {
   findValue2(li);
 }

 function formatItem2(row) {

  var alamat = row[1].split('~');
  
  if(row[0]) {
    document.getElementById('icd_nama2').value=alamat[0];
    document.getElementById('id_icd2').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";

}

   //-------------------ICD 3

   function findValue3(li) {
     if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');

    //alert("The value you selected was: " + sValue);
    document.getElementById('icd_nama3').value=values[0];
    document.getElementById('id_icd3').value=values[1];
    document.getElementById('icd_nomor3').focus();
  }
  
  function selectItem3(li) {
   findValue3(li);
 }

 function formatItem3(row) {

  var alamat = row[1].split('~');
  
  if(row[0]) {
    document.getElementById('icd_nama3').value=alamat[0];
    document.getElementById('id_icd3').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";

}

//--------------------END---------------------------------///

function lookupAjax() {
 var oSuggest = $("#CityAjax")[0].autocompleter;

 oSuggest.findValue();
 return false;
}

function lookupLocal() {
 var oSuggest = $("#CityLocal")[0].autocompleter;

 oSuggest.findValue();
 return false;
}


$("#CityAjax").autocomplete(
  "autocomplete.php",
  {
   delay:10,
   minChars:2,
   matchSubset:1,
   matchContains:1,
   cacheLength:10,
   onItemSelect:selectItem,
   onFindValue:findValue,
   formatItem:formatItem,
   autoFill:true
 }
 );

$("#CityAjax2").autocomplete(
  "autocomplete.php",
  {
   delay:10,
   minChars:2,
   matchSubset:1,
   matchContains:1,
   cacheLength:10,
   onItemSelect:selectItem2,
   onFindValue:findValue2,
   formatItem:formatItem2,
   autoFill:true
 }
 );

$("#CityAjax3").autocomplete(
  "autocomplete.php",
  {
   delay:10,
   minChars:2,
   matchSubset:1,
   matchContains:1,
   cacheLength:10,
   onItemSelect:selectItem3,
   onFindValue:findValue3,
   formatItem:formatItem3,
   autoFill:true
 }
 );

</script>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="jadwal_id" value="<?php echo $_POST["jadwal_id"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="rawat_icd_id" value="<?php echo $_POST["rawat_icd_id"];?>"/>
<input type="hidden" name="cust_usr_nama" value="<?php echo $_POST["cust_usr_nama"];?>"/>
<input type="hidden" name="cust_usr_alamat" value="<?php echo $_POST["cust_usr_alamat"];?>"/>
<input type="hidden" name="penjualan_id" value="<?php echo $_POST["penjualan_id"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="penjualan_detail_id[0]" value="<?php echo $_POST["penjualan_detail_id"][0];?>"/>
<input type="hidden" name="penjualan_detail_id[1]" value="<?php echo $_POST["penjualan_detail_id"][1];?>"/>
<input type="hidden" name="penjualan_detail_id[2]" value="<?php echo $_POST["penjualan_detail_id"][2];?>"/>
<input type="hidden" name="penjualan_detail_id[3]" value="<?php echo $_POST["penjualan_detail_id"][3];?>"/>
<input type="hidden" name="penjualan_id" value="<?php echo $dataObatPasien[0]["penjualan_id"];?>"/>
<script type="text/javascript">

  Calendar.setup({
        inputField     :    "reg_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_rawat",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
      });
    </script>
  </form>  
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