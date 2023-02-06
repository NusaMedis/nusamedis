<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php"); 
    // require_once($ROOT."lib/tampilan.php");
     require_once($ROOT."lib/conf/database.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
          
    // $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
	   $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     
     $host="localhost";
     $user=$enc->Decode(DB_USER);
     $password=$enc->Decode(DB_PASSWORD);
     $port="5432";
     $dbname = DB_NAME;
    
    //untuk penanda di log
    echo "Insert Akomodasi per ".date('Y-m-d H:i:s');
      
     $link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
      
      //cari konfigurasi departemen
     $sqlcaridep = pg_query($link, "select * from global.global_departemen ");
     $arrdep = pg_fetch_assoc($sqlcaridep);

      // nyari data pasien dulu -- trus di for --
     $sqlcaripasieninap = pg_query($link, "select reg_id,id_dokter,id_pembayaran,reg_kelas,b.id_kamar,a.id_cust_usr,reg_jenis_pasien,a.id_poli,rawatinap_id, 
     b.rawatinap_tanggal_masuk,reg_tanggal, reg_waktu from klinik.klinik_rawatinap b
      left join klinik.klinik_registrasi a on a.reg_id = b.id_reg 
      where reg_status ='I2'
     ");
     $arrpasieninap = pg_fetch_all($sqlcaripasieninap); 
     // print_r($arrpasieninap);
     for($i=0,$n=count($arrpasieninap);$i<$n;$i++){
   //  echo "Masuk";
 
		 //selisih tanggal
		    $mulai = $arrpasieninap[$i]["rawatinap_tanggal_masuk"];
		    $start_date = $mulai;
			$end_date = date('Y-m-d');
			
           // echo $end_date." ";
			//$interval = $end_date->diff($start_date);
            $interval = DateDiff($start_date,$end_date);
			//$d = $interval->d;
           // echo "total hari ".$interval." ";
	for($b=0;$b<=$interval;$b++){
    $tgl2 = date('Y-m-d', strtotime('+'.$b.' days', strtotime($mulai)));	
   
  // echo $tgl2." id ".$arrpasieninap[$i]["id_cust_usr"]."<br>";
   //cari kamar yang aktif di tanggal itu
        //cari biaya akomodasinya
    $sqlku = "select b.id_jenis_kelas, a.rawat_inap_history_kelas_tujuan from klinik.klinik_rawat_inap_history a
     left join klinik.klinik_kamar b on a.rawat_inap_history_kamar_tujuan = b.kamar_id
     where a.id_reg ='".$arrpasieninap[$i]["reg_id"]."' and rawat_inap_history_tanggal <= '".$tgl2."'
     order by rawat_inap_history_tanggal desc";
     $sqlcarikamarlalu = pg_query($link, $sqlku);
     $arrkamarlalu = pg_fetch_assoc($sqlcarikamarlalu);
    // echo $sqlku;
        //cari biaya akomodasinya
     $sqlcariakomodasi = pg_query($link, "select b.biaya_nama, b.biaya_id, c.* from global.global_biaya_akomodasi a
     left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
     left join klinik.klinik_biaya_tarif c on b.biaya_id = c.id_biaya
     where c.id_kelas ='".$arrkamarlalu["rawat_inap_history_kelas_tujuan"]."'
     and c.id_jenis_kelas ='".$arrkamarlalu["id_jenis_kelas"]."'
     and c.biaya_tarif_tgl_awal <= '".date("Y-m-d")."'
     and c.biaya_tarif_tgl_akhir >= '".date("Y-m-d")."'");
     $arrbiayadetail = pg_fetch_assoc($sqlcariakomodasi);
     //  echo($sqlku); 
     // print_r($sqlcarikamarlalu);
     //cari detail biaya
      if($arrbiayadetail){
     
          	   $sqlJamkesda = pg_query($link, "	select a.id_jamkesda_kota, b.jamkesda_kota_nama, b.jamkesda_kota_persentase_kota, 
                              b.jamkesda_kota_persentase_prov from klinik.klinik_registrasi a 
          						        left join global.global_jamkesda_kota b on a.id_jamkesda_kota=b.jamkesda_kota_id 
          						        where reg_id = '".$arrpasieninap[$i]["reg_id"]."'");
      					$arrJamkesda = pg_fetch_assoc($sqlJamkesda);
      				
      					$jamkesdaPesentaseKota=$arrJamkesda["jamkesda_kota_persentase_kota"];
      					$jamkesdaPesentaseProv=$arrJamkesda["jamkesda_kota_persentase_prov"];
                
                $jaminDinkesProv=(StripCurrency($arrbiayadetail["biaya_total"])*StripCurrency($jamkesdaPesentaseProv)/100);
					      $jaminDinkesKota=(StripCurrency($arrbiayadetail["biaya_total"])*StripCurrency($jamkesdaPesentaseKota)/100);
                $totaljaminanjamkesda =  $jaminDinkesKota + $jaminDinkesProv;
                $bayarjamkesda = $arrbiayadetail["biaya_total"] - $totaljaminanjamkesda;
    
   
    
    //cari folio berdasarkan id_biaya_tarif untuk hari yang sama jika ada lewat jika belum insert
    $sqlakomodasisama = pg_query($link, "select fol_id from klinik.klinik_folio 
			     where id_biaya_tarif ='".$arrbiayadetail["biaya_tarif_id"]."'
			     and id_pembayaran ='".$arrpasieninap[$i]["id_pembayaran"]."'
			     and tindakan_tanggal = '".$tgl2."'");
     $arrakomodasisama = pg_fetch_assoc($sqlakomodasisama);
    //print_r($sqlakomodasisama);
     if(!$arrakomodasisama){
     	
     //insert folio per pasien irna berdasarkan reg_id yg aktif
     			  $folId = $dtaccess->GetTransID();
      if($arrpasieninap[$i]["reg_jenis_pasien"]=="5" || $arrpasieninap[$i]["reg_jenis_pasien"]=="7" || $arrpasieninap[$i]["reg_jenis_pasien"]=="26" ){
     $sqlinserfolio = pg_query($link, "
            insert into klinik.klinik_folio(
            fol_id, id_reg, fol_nama, fol_nominal, fol_jenis, id_cust_usr, 
            fol_waktu, fol_lunas,id_biaya,id_poli,fol_jenis_pasien, id_dep, who_when_update,fol_total_harga,
            fol_jumlah, fol_nominal_satuan,id_pembayaran,fol_hrs_bayar,fol_dijamin,id_dokter,tindakan_tanggal,tindakan_waktu,id_biaya_tarif,fol_jenis_sem
            )
    VALUES ('".$folId."', '".$arrpasieninap[$i]["reg_id"]."', '".$arrbiayadetail["biaya_nama"]."', '".$arrbiayadetail["biaya_total"]."', '".$arrbiayadetail["biaya_jenis"]."', '".$arrpasieninap[$i]["id_cust_usr"]."',
            '".date("Y-m-d H:i:s")."', 'n','".$arrbiayadetail["biaya_id"]."','".$arrpasieninap[$i]["id_kamar"]."','".$arrpasieninap[$i]["reg_jenis_pasien"]."','".$arrdep["dep_id"]."','system','".$arrbiayadetail["biaya_total"]."',
            '1','".$arrbiayadetail["biaya_total"]."','".$arrpasieninap[$i]["id_pembayaran"]."','0','".$arrbiayadetail["biaya_total"]."','".$arrpasieninap[$i]["id_dokter"]."',
            '".$tgl2."','".date("H:i:s")."','".$arrbiayadetail["biaya_tarif_id"]."','BA');
            ");
       
       }elseif($arrpasieninap[$i]["reg_jenis_pasien"]=="18"){
     $sqlinserfolio = pg_query($link, "
            insert into klinik.klinik_folio(
            fol_id, id_reg, fol_nama, fol_nominal, fol_jenis, id_cust_usr, 
            fol_waktu, fol_lunas,id_biaya,id_poli,fol_jenis_pasien, id_dep, who_when_update,fol_total_harga,
            fol_jumlah, fol_nominal_satuan,id_pembayaran,fol_hrs_bayar,fol_dijamin,fol_dijamin1,fol_dijamin2,id_dokter,tindakan_tanggal,tindakan_waktu,id_biaya_tarif,,fol_jenis_sem
            )
    VALUES ('".$folId."', '".$arrpasieninap[$i]["reg_id"]."', '".$arrbiayadetail["biaya_nama"]."', '".$arrbiayadetail["biaya_total"]."', '".$arrbiayadetail["biaya_jenis"]."', '".$arrpasieninap[$i]["id_cust_usr"]."',
            '".date("Y-m-d H:i:s")."', 'n','".$arrbiayadetail["biaya_id"]."','".$arrpasieninap[$i]["id_kamar"]."','".$arrpasieninap[$i]["reg_jenis_pasien"]."','".$arrdep["dep_id"]."','system','".$arrbiayadetail["biaya_total"]."',
            '1','".$arrbiayadetail["biaya_total"]."','".$arrpasieninap[$i]["id_pembayaran"]."','".$bayarjamkesda."','".$totaljaminanjamkesda."','".$jaminDinkesProv."','".$jaminDinkesKota."','".$arrpasieninap[$i]["id_dokter"]."',
            '".$tgl2."','".date("H:i:s")."','".$arrbiayadetail["biaya_tarif_id"]."','BA');
            ");
       
       } else{
      $sqlinserfolio = pg_query($link, "
            insert into klinik.klinik_folio(
            fol_id, id_reg, fol_nama, fol_nominal, fol_jenis, id_cust_usr, 
            fol_waktu, fol_lunas,id_biaya,id_poli,fol_jenis_pasien, id_dep, who_when_update,fol_total_harga,
            fol_jumlah, fol_nominal_satuan,id_pembayaran,fol_hrs_bayar,fol_dijamin,id_dokter,tindakan_tanggal,tindakan_waktu,id_biaya_tarif,fol_jenis_sem
            )
    VALUES ('".$folId."', '".$arrpasieninap[$i]["reg_id"]."', '".$arrbiayadetail["biaya_nama"]."', '".$arrbiayadetail["biaya_total"]."', '".$arrbiayadetail["biaya_jenis"]."', '".$arrpasieninap[$i]["id_cust_usr"]."',
            '".date("Y-m-d H:i:s")."', 'n','".$arrbiayadetail["biaya_id"]."','".$arrpasieninap[$i]["id_kamar"]."','".$arrpasieninap[$i]["reg_jenis_pasien"]."','".$arrdep["dep_id"]."','system','".$arrbiayadetail["biaya_total"]."',
            '1','".$arrbiayadetail["biaya_total"]."','".$arrpasieninap[$i]["id_pembayaran"]."','".$arrbiayadetail["biaya_total"]."','0','".$arrpasieninap[$i]["id_dokter"]."',
            '".$tgl2."','".date("H:i:s")."','".$arrbiayadetail["biaya_tarif_id"]."','BA');
            ");
      
       }
     
     //print_r($sqlinserfolio);
    /*  $folpelId1 = $dtaccess->GetTransID();
      $sqlinserfoliopelaksana1 = pg_query($link, "
            insert into klinik.klinik_folio_pelaksana(
            fol_pelaksana_id, id_fol, id_usr, fol_pelaksana_tipe
            )
    VALUES ('".$folpelId1."', '".$folId."', '".$arrpasieninap[$i]["id_dokter"]."', '1');
            ");
     
      $folpelId2 = $dtaccess->GetTransID();
      $sqlinserfoliopelaksana2 = pg_query($link, "
            insert into klinik.klinik_folio_pelaksana(
            fol_pelaksana_id, id_fol, id_usr, fol_pelaksana_tipe
            )
    VALUES ('".$folpelId2."', '".$folId."', '".$arrpasieninap[$i]["id_dokter"]."', '2' );
            ");       
*/
     $sqlcariperawatan = pg_query($link, "select * from klinik.klinik_perawatan 
     where id_reg ='".$arrpasieninap[$i]["reg_id"]."'");
     $arrrawat = pg_fetch_assoc($sqlcariperawatan);

     if(!$arrrawat){
      $folrawat = $dtaccess->GetTransID();
      $sqlinserperawatan = pg_query($link, "
            insert into klinik.klinik_perawatan(
            rawat_id, id_reg, id_cust_usr, rawat_waktu_kontrol, rawat_tanggal,
            rawat_flag,rawat_flag_komen,id_poli,id_dep,
            rawat_who_update,rawat_waktu
            )
    VALUES ('".$folrawat."', '".$arrpasieninap[$i]["reg_id"]."', '".$arrpasieninap[$i]["id_cust_usr"]."', '".date("H:i:s")."', '".date("Y-m-d")."',
            'M','RAWAT JALAN',  '".$arrpasieninap[$i]["id_poli"]."','".$arrdep["dep_id"]."',
            'system','".$arrpasieninap[$i]["reg_tanggal"]." ".$arrpasieninap[$i]["reg_waktu"]."');
            ");       
     
     }else{
       $folrawat = $arrrawat["rawat_id"];
     }

      $folrawattind = $dtaccess->GetTransID();     
      $sqlinsertrawattind = pg_query($link, "
            insert into klinik.klinik_perawatan_tindakan(
            rawat_tindakan_id, id_rawat, id_tindakan, rawat_tindakan_total, id_dep,
            rawat_tindakan_jumlah
            )
    VALUES ('".$folrawattind."', '".$folrawat."', '".$arrbiayadetail["biaya_id"]."', '".$arrbiayadetail["biaya_total"]."', '".$arrdep["dep_id"]."',
            '1');
            ");       
/*
      $folrawattindpel1 = $dtaccess->GetTransID();     
      $sqlinsertrawattindpel = pg_query($link, "
            insert into klinik.klinik_perawatan_tindakan_pelaksana(
            rawat_tindakan_pelaksana_id, id_rawat_tindakan, id_usr, 
            rawat_tindakan_pelaksana_tipe
            )
    VALUES ('".$folrawattindpel1."', '".$folrawattind."', '".$arrpasieninap[$i]["id_dokter"]."', 
            '1');
            ");       

            
      $folrawattindpel2 = $dtaccess->GetTransID();     
      $sqlinsertrawattindpel2 = pg_query($link, "
            insert into klinik.klinik_perawatan_tindakan_pelaksana(
            rawat_tindakan_pelaksana_id, id_rawat_tindakan, id_usr, 
            rawat_tindakan_pelaksana_tipe
            )
    VALUES ('".$folrawattindpel2."', '".$folrawattind."', '".$arrpasieninap[$i]["id_dokter"]."', 
            '2');
            ");       
*/
     }
     }
     }
     }
 
?>