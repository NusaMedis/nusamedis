<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "report_pasien.php";
     $poliId = $auth->IdPoli();
	 
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
     
 /*    
    if(!$auth->IsAllowed("rm_info_lap_kunjungan_irj",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("rm_info_lap_kunjungan_irj",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } 

*/     
    // $_POST["klinik"]=$depId;

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else  $_POST["klinik"] = $_POST["klinik"];
     
 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
          
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }
     
     //cari shift
	 $sql = "select * from global.global_shift order by shift_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);
	 
     if($_POST["id_dokter"]) $sql_where1 = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     
     //untuk mencari tanggal
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
     if($_POST["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     if($_POST["reg_shift"]){
		$sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
	 }

     if($_POST["id_poli"] && $_POST["id_poli"] <> '--') $sql_where[] = " a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);


	/*if($userId<>'b9ead727d46bc226f23a7c1666c2d9fb' or $userId<>'92df81c2bebf2f93f75d9ad1014fe930'){
		$sql_where[] = " a.reg_who_update =".QuoteValue(DPE_CHAR,$userName);
	 }*/
	 
	 if($_POST["cust_usr_nama"]){
		$sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%'";
	 }
	 
	 if($_POST["cust_usr_kode"]){
		$sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
	 }
	 
	 if($_POST["cust_usr_alamat"]){
		$sql_where[] = " b.cust_usr_alamat = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
	 }
   
   if($_POST["reg_jenis_pasien"]){
		$sql_where[] = " a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
	 }
   
  //  if($_POST["reg_tipe_layanan"]){
		// $sql_where[] = " a.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
	 // }
   
  //  if($_POST["id_perusahaan"]){
		// $sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
	 // }
   
  //  if($_POST["cust_usr_jkn"]){
		// $sql_where[] = " a.reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"]);
	 // }
   
  //  if($_POST["id_jamkesda_kota"]){
		// $sql_where[] = " a.id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
	 // }
	 
	 if($_POST["reg_status_pasien"]){
		$sql_where[] = " a.reg_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);
	 }
	 
	 if($_POST["kondisi_akhir"]){
		$sql_where[] = " a.reg_status_kondisi = ".QuoteValue(DPE_CHAR,$_POST["kondisi_akhir"]);
	 }
     if($_POST["id_usr"]){
    $sql_where[] = " a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_usr"]);
   }

  

	 
     
     if($_POST["btnLanjut"] || $_POST["btnCetak"]){
      $sql = "select b.cust_usr_jam_lahir, b.id_lokasi, b.cust_usr_penanggung_jawab,b.id_pekerjaan,t.pekerjaan_nama, b.id_kecamatan, b.id_kelurahan, b.id_kota, b.id_prop,b.id_pendidikan,pendidikan_nama,a.reg_kode_trans,b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat,  b.cust_usr_tanggal_lahir,b.cust_usr_agama,m.agm_nama as agama,b.cust_usr_pekerjaan, b.cust_usr_jenis_kelamin, f.dep_nama,
      a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien,a.reg_status, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal,
      a.reg_batal,d.usr_name,jenis_nama, a.id_poli,a.id_poli_asal ,c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur, a.id_cust_usr,
      g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update,a.reg_status_kondisi, a.reg_tipe_layanan,d.usr_name, j.tipe_biaya_nama, a.id_pembayaran,
      a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd,l.kondisi_akhir_pasien_nama,o.pembayaran_flag,p.rawatinap_id,p.id_reg,p.rawatinap_asal_instalasi,q.rujukan_nama,y.*,z.*
     ,r.id_rawatinap from klinik.klinik_inacbg u
     left join klinik.klinik_registrasi a on u.id_reg=a.reg_id
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
      left join global.global_auth_poli c on c.poli_id = a.id_poli
      left join global.global_auth_user d on a.id_dokter = d.usr_id
      left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
      left join global.global_departemen f on a.id_dep = f.dep_id
      left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
      left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota
      left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
      left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan
      left join klinik.klinik_perawatan k on k.id_reg=a.reg_id
      left join global.global_kondisi_akhir_pasien l on l.kondisi_akhir_pasien_id=a.reg_status_kondisi
      left join global.global_agama m on m.agm_id=b.cust_usr_agama
      left join global.global_pendidikan n on n.pendidikan_id = b.id_pendidikan
      left join klinik.klinik_pembayaran o on a.id_pembayaran = o.pembayaran_id
      left join klinik.klinik_rawatinap p on a.reg_id = p.id_reg
      left join global.global_rujukan  q on a.reg_rujukan_id = q.rujukan_id
      left join klinik.klinik_rawat_inap_history  r on a.reg_id = r.id_rawatinap
      left join klinik.klinik_rawatinap s on s.id_reg = a.reg_id
      left join global.global_pekerjaan t on t.pekerjaan_id=b.id_pekerjaan
      left join klinik.klinik_keadaan_keluar_inap y on y.keadaan_keluar_inap_id =a.reg_keadaan_keluar_inap
      left join klinik.klinik_cara_keluar_inap z on z.cara_keluar_inap_id = a.reg_cara_keluar_inap 
     ";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " and reg_tipe_rawat='I'  and( a.reg_utama=a.reg_id or a.reg_utama is null  )  and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') ";
     $sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
     // echo $sql;
     //echo count($dataTable[$i]["cust_usr_jenis_kelamin"]=='L');
   //  var_dump($dataTable[0]); die();

     $sql = "select count(reg_id) as laki from klinik.klinik_registrasi a left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
     left join global.global_auth_poli c on c.poli_id = a.id_poli";
     $sql .=" where ".implode(" and ",$sql_where);
    $sql.= " and cust_usr_jenis_kelamin = 'L'  and reg_tipe_rawat='I'  and( a.reg_utama=a.reg_id or a.reg_utama is null  ) and (c.poli_nama='Ranap Nifas' or c.poli_nama='Ranap Neo' or c.poli_nama='Ranap Anak' or c.poli_nama='Ranap RGT')  and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n')";
     $dataLaki = $dtaccess->Fetch($sql);

     $sql = "select count(reg_id) as perempuan from klinik.klinik_registrasi a left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
     left join global.global_auth_poli c on c.poli_id = a.id_poli";
     $sql .=" where ".implode(" and ",$sql_where);
    $sql.= "and cust_usr_jenis_kelamin = 'P' and reg_tipe_rawat='I'  and( a.reg_utama=a.reg_id or a.reg_utama is null  ) and reg_utama is null and (c.poli_nama='Ranap Nifas' or c.poli_nama='Ranap Neo' or c.poli_nama='Ranap Anak' or c.poli_nama='Ranap RGT')  and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n')";
     $dataPerempuan = $dtaccess->Fetch($sql);

     $sql = "select count(reg_id) as lama from klinik.klinik_registrasi a left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
     left join global.global_auth_poli c on c.poli_id = a.id_poli";
     $sql .=" where ".implode(" and ",$sql_where);
    $sql.= "and reg_status_pasien = 'L'  and reg_tipe_rawat='I'  and( a.reg_utama=a.reg_id or a.reg_utama is null  ) and (c.poli_nama='Ranap Nifas' or c.poli_nama='Ranap Neo' or c.poli_nama='Ranap Anak' or c.poli_nama='Ranap RGT')  and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n')";
     $dataLama = $dtaccess->Fetch($sql);

     $sql = "select count(reg_id) as baru from klinik.klinik_registrasi a left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
     left join global.global_auth_poli c on c.poli_id = a.id_poli";
     $sql .=" where ".implode(" and ",$sql_where);
  $sql.= " and reg_status_pasien = 'B' and reg_tipe_rawat='I'  and( a.reg_utama=a.reg_id or a.reg_utama is null  )and (c.poli_nama='Ranap Nifas' or c.poli_nama='Ranap Neo' or c.poli_nama='Ranap Anak' or c.poli_nama='Ranap RGT')  and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') and (c.poli_nama='Ranap Nifas' or c.poli_nama='Ranap Neo' or c.poli_nama='Ranap Anak' or c.poli_nama='Ranap RGT')  and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n')";
     $dataBaru = $dtaccess->Fetch($sql);
    }

     $tableHeader = "&nbsp;Laporan Kunjungan Pasien Rawat Inap";
  
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
     $counterHeader3 = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;

    
	 
	   // $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
    //  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
    //  $counterHeader++;
	           
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;

      $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Penanggung Jawab";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     

   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kunjungan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli Asal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Ruang";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Pembayaran";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

      $tbHeader[0][$counterHeader][TABLE_ISI] = "Kondisi Akhir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keadaan Keluar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Keluar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
    
    /* $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;*/
	 
    
       $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

      $sql  = "select lokasi_nama from global.global_lokasi where lokasi_propinsi = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_prop"]);
      $sql .= " and lokasi_kabupatenkota = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_kota"]);
      $sql .= " and lokasi_kecamatan = '00' and lokasi_kelurahan = '0000'";
      //$sql .= " and (lokasi_nama like '%Kota%' or lokasi_nama like '%Kabupaten%' or lokasi_nama like '%KABUPATEN%' or lokasi_nama like '%KOTA%')";
      $sql .= " order by lokasi_propinsi,lokasi_kecamatan,lokasi_kelurahan asc";
      $dataKabupaten = $dtaccess->Fetch($sql);
//echo $sql;
      $sqlKec  = "select lokasi_nama from global.global_lokasi where lokasi_propinsi = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_prop"]);
      $sqlKec .= " and lokasi_kabupatenkota = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_kota"]);
      $sqlKec .= " and lokasi_kecamatan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_kecamatan"]);
      $sqlKec .= " order by lokasi_kelurahan asc";
      $dataKecamatan = $dtaccess->Fetch($sqlKec);

      $sqlKel  = "select lokasi_nama from global.global_lokasi where lokasi_propinsi = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_prop"]);
      $sqlKel .= " and lokasi_kabupatenkota = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_kota"]);
      $sqlKel .= " and lokasi_kecamatan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_kecamatan"]);
      $sqlKel .= " and lokasi_kelurahan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_kelurahan"]);
      $dataKelurahan = $dtaccess->Fetch($sqlKel);

       $sql = "SELECT * from   global.global_auth_poli WHERE poli_tipe='I' 
               order by poli_nama"; 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs_edit);

    
       $sql  = "select * from klinik.klinik_registrasi where id_cust_usr= ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_cust_usr"])."AND reg_tipe_rawat='J' AND reg_utama ISNULL ORDER BY reg_when_update DESC LIMIT 1";
      // echo $sql;
      $statusPx = $dtaccess->Fetch($sql);

      $sql  = "select * from klinik.klinik_registrasi where reg_status='I9' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_pembayaran"]);
      // echo $sql;
      $asal = $dtaccess->Fetch($sql);


      $sqljml  = "select count(*) as jumlahtf from klinik.klinik_registrasi where reg_status='I9' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_pembayaran"]);
      // echo $sqljml;
            $jmlTf = $dtaccess->Fetch($sqljml);



  

	  //echo $sql;
		//if($_POST["id_poli"] == '--') 
		//{
		 //if ($dataTable[$i]["id_poli"]!=$dataTable[$i-1]["id_poli"])
		 //{
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++; 


             if ($jmlTf["jumlahtf"]>0) {
        $tbContent[$i][$counter][TABLE_ISI] = format_date($asal["reg_tanggal"]);
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
        $counter++;
        }
        else{
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
        }

        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jam_lahir"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
        $counter++;
          
        

      
		  
		// $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
  //         $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
  //         $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;    

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_penanggung_jawab"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;   

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";   
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];       
          $counter++; 

          if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
          $umur = explode("~",$dataTable[$i]["cust_usr_umur"]);
          $tbContent[$i][$counter][TABLE_ISI] = $umur[0]." tahun ".$umur[1]." bulan ".$umur[2]." hari";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;

          if ($statusPx["reg_status_pasien"]=="B") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "Baru";
          }
          else if ($statusPx["reg_status_pasien"]=="L") {
            # code...
               $tbContent[$i][$counter][TABLE_ISI] = "Lama";
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];

         
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          if($dataTable[$i]["rawatinap_asal_instalasi"]=="J"){

            $tbContent[$i][$counter][TABLE_ISI] = "Rawat Jalan";
          }
          elseif($dataTable[$i]["rawatinap_asal_instalasi"]=="G"){

            $tbContent[$i][$counter][TABLE_ISI] = "IGD";
          }


          
          
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  


          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;         
          
        if($dataTable[$i]["reg_jenis_pasien"]=='5'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jkn_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='18'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jamkesda_kota_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='7'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["perusahaan_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }else{
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }

       $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kondisi_akhir_pasien_nama"];
       $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
       $counter++;


          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["keadaan_keluar_inap_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cara_keluar_inap_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  

      if ($jmlTf["jumlahtf"]>0) {
        $tbContent[$i][$counter][TABLE_ISI] = $asal["reg_who_update"];
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
      $counter++;
      }
      else{
        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
        $counter++;
        
      }
   
 
        
        

          
          // $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          // $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          // $counter++;
		  
        //}  
	   /*} else { //jika milih poli
		
		if ($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"])
		 {
         $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;    
          
          //if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
          $umur = explode("~",$dataTable[$i]["cust_usr_umur"]);
          $tbContent[$i][$counter][TABLE_ISI] = $umur[0]." tahun ".$umur[1]." bulan ".$umur[2]." hari";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          if($dataTable[$i]["reg_jenis_pasien"]=='5'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jkn_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='18'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jamkesda_kota_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='7'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["perusahaan_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }else{
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
		  
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
        }  

       }*/	   
		
     }
     
     $colspan = count($tbHeader[0]);
     
       //ambil nama poli
//  $sql = "select poli_nama, poli_id from global.global_auth_poli where 
//  (poli_tipe='J' or poli_tipe='M' or poli_tipe='R' or poli_tipe='L' or poli_tipe='P') and id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])   ; 
//  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
//  $dataPoli = $dtaccess->FetchAll($rs_edit);

  $sql = "select a.poli_nama, a.poli_id from global.global_auth_poli a join global.global_auth_user_poli b on a.poli_id = b.id_poli 
          where a.poli_tipe='I' and b.id_usr =".QuoteValue(DPE_CHAR,$userId); 
  $sql .= " order by poli_nama asc";
  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataPoli = $dtaccess->FetchAll($rs_edit);
  
     // ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs); 
          
    //echo $sql;
          $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_GET["klinik"]."'";
          $rs = $dtaccess->Execute($sql);
          $namaKlinik = $dtaccess->Fetch($rs);
                                                      
      //Nama Sekolah
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
      
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     if($konfigurasi["dep_lowest"]=='n'){
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }else if($_POST["klinik"]){
     //Data Klinik
          $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }     
     
     //ambil jenis pasien
     $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_id asc ";
     $rs = $dtaccess->Execute($sql);
     $dataDokter = $dtaccess->FetchAll($rs);
     
     // cari perusahaan
     $sql = "select * from global.global_perusahaan order by perusahaan_id desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPerusahaan = $dtaccess->FetchAll($rs);
	 
	 // cari kota jamkesda
     $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_id desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKota = $dtaccess->FetchAll($rs);
	 
	 // cari Kategori jkn
     $sql = "select * from global.global_jkn order by jkn_id desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJKN = $dtaccess->FetchAll($rs);
     
       // cari tipe biaya
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tipeBiaya = $dtaccess->FetchAll($rs);
	 
	 // cari kondisi
	 $sql = "select kondisi_akhir_pasien_id,kondisi_akhir_pasien_nama
				from global.global_kondisi_akhir_pasien 
				order by kondisi_akhir_pasien_id asc";
	$rs = $dtaccess->Execute($sql);
	$dataKondisi = $dtaccess->FetchAll($rs);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    		if($_POST["btnExcel"]){
         $_x_mode = "excel";
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }
     $sql = "select * from global.global_lokasi where lokasi_kabupatenkota <>'00' and lokasi_kecamatan='00' and lokasi_kelurahan ='0000' 
             order by lokasi_propinsi, lokasi_kabupatenkota asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKotaku = $dtaccess->FetchAll($rs);

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
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Laporan Registrasi Rawat Inap</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
				  
					<!--fieldset>
                          <div class="control-group">
                            <div class="controls">
                              <div class="col-md-11 xdisplay_inputx form-group has-feedback">
                                <input type="text" name="tgl_coba" class="form-control has-feedback-left" id="single_cal2" aria-describedby="inputSuccess2Status2">
                                <span class="fa fa-calendar-o form-control-feedback left" aria-hidden="true"></span>
                                <span id="inputSuccess2Status2" class="sr-only">(success)</span>
                              </div>
                            </div>
                          </div>
					</fieldset-->
					
			
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

              <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
                        <div class='input-group col-md-12 col-sm-12 col-xs-12'>
                          <select class="select2_single form-control" name="id_usr" id="id_usr" onKeyDown="return tabOnEnter(this, event);">
                            <!--onChange="this.form.submit();" -->
                            <option value="">[ Pilih Pegawai ]</option>
                            <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                              <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_usr"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $dataDokter[$i]["usr_id"]; ?>');"><?php echo $dataDokter[$i]["usr_name"]; ?></option>
                            <?php } ?>
                          </select>
                        </div>    			 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
						<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
						
				    </div>
				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
						<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
						
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Alamat</label>
						<?php echo $view->RenderTextBox("cust_usr_alamat","cust_usr_alamat",30,200,$_POST["cust_usr_alamat"],false,false);?>
				    </div>
				    
				   <!--  <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Shift</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               				<select class="select2_single form-control" name="reg_shift" id="reg_shift" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" 
			   					<option value="">[- Semua Shift -]</option>
          							<?php for($i=0,$n=count($dataShift);$i<$n;$i++){ ?>
          						<option value="<?php echo $dataShift[$i]["shift_id"];?>" <?php if($dataShift[$i]["shift_id"]==$_POST["reg_shift"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_nama"]." (".$dataShift[$i]["shift_jam_awal"]."-".$dataShift[$i]["shift_jam_akhir"].")";?></option>
									<?php } ?>
							</select>
						</div> 
				    </div> -->
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
						<?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               				<select class="select2_single form-control" name="reg_jenis_pasien" id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                				<option value="0" >[ Pilih Cara Bayar ]</option>
                					<?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                				<option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["reg_jenis_pasien"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
      								<?php } ?>
      						</select>
						
				    </div>
				    
				    <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jamkesda Kota</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               					<select class="select2_single form-control" name="id_jamkesda_kota" id="id_jamkesda_kota" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" 
          							<option value="" >[ Pilih Nama Kota ]</option>
          								<?php for($i=0,$n=count($dataKota);$i<$n;$i++){ ?>
          							<option value="<?php echo $dataKota[$i]["jamkesda_kota_id"];?>" <?php if($dataKota[$i]["jamkesda_kota_id"]==$_POST["id_jamkesda_kota"]) echo "selected"; ?>><?php echo $dataKota[$i]["jamkesda_kota_nama"];?></option>
										<?php } ?>
								</select>
						</div> 
				    </div> -->
				<!--     
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kota/Kabupaten</label> -->
					<!-- 	<?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      						<?php } else { ?> -->
              				<!-- <td width="20%" class="tablecontent"> -->
      						<!-- <?php } ?> -->
               				<!-- 	<select class="select2_single form-control" name="id_lokasi_kota" id="id_lokasi_kota" onKeyDown="return tabOnEnter(this, event);">  --><!--onChange="this.form.submit();" -->
          							<!-- <option value="" >[ Pilih Kota / Kabupaten ]</option> -->
									<!-- <?php for($i=0,$n=count($dataKotaku);$i<$n;$i++){ ?> -->
								<!-- 	<option value="<?php echo $dataKotaku[$i]["lokasi_id"];?>" <?php if($dataKotaku[$i]["lokasi_id"]==$_POST["id_lokasi_kota"]) echo "selected"; ?>><?php echo $dataKotaku[$i]["lokasi_nama"];?></option>
								 -->	<!-- <?php } ?>
								</select> -->
						
				    <!-- </div> -->

				    <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      						<?php } else { ?>
              				<td width="20%" class="tablecontent">
      						<?php } ?>
               					<select class="select2_single form-control" name="reg_tipe_rawat" id="reg_tipe_rawat" onKeyDown="return tabOnEnter(this, event);">
				                  <option value="" >[ Semua Tipe Rawat]</option>
				                  <option value="J" <?php if($_POST["reg_tipe_rawat"]=='J') echo "selected"; ?>>Rawat Jalan</option>
				                  <option value="G" <?php if($_POST["reg_tipe_rawat"]=='G') echo "selected"; ?>>Rawat Darurat</option>
				                  <option value="I" <?php if($_POST["reg_tipe_rawat"]=='I') echo "selected"; ?>>Rawat Inap</option>
				        		 </select>
				        		 </td>
						</div> 
				    </div>
				     -->
				   
				   
				    
				    <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Layanan</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              			<td width="20%" class="tablecontent">
      						<?php } else { ?>
              			<td width="20%" class="tablecontent">
      						<?php } ?>
               			<select class="select2_single form-control" name="reg_tipe_layanan" id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" 
          					<option value="" >[ Pilih Tipe Layanan ]</option>
		  					<?php for($i=0,$n=count($tipeBiaya);$i<$n;$i++){ ?>
          					<option value="<?php echo $tipeBiaya[$i]["tipe_biaya_id"];?>" <?php if($tipeBiaya[$i]["tipe_biaya_id"]==$_POST["reg_tipe_layanan"]) echo "selected"; ?>><?php echo $tipeBiaya[$i]["tipe_biaya_nama"];?></option>
							<?php } ?>
						</select>
						</div> 
				    </div> -->
				    
				  
				    
				  
				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kondisi Akhir</label>
						<?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               					<select class="select2_single form-control" name="kondisi_akhir" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          							<option value="" >[ Pilih Kondisi Akhir ]</option>
          							<?php for($i=0,$n=count($dataKondisi);$i<$n;$i++){ ?>
          							<option value="<?php echo $dataKondisi[$i]["kondisi_akhir_pasien_id"];?>" <?php if($dataKondisi[$i]["kondisi_akhir_pasien_id"]==$_POST["kondisi_akhir"]) echo "selected"; ?>><?php echo $dataKondisi[$i]["kondisi_akhir_pasien_nama"];?></option>
									<?php } ?>
								</select>
						
				    </div>

             <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik</label>
            <?php if($userData["rol"]!='2') { ?>              
              <td width="20%" class="tablecontent">
                <?php } else { ?>
              <td width="20%" class="tablecontent">
                <?php } ?>
              <select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                <option value="">[Pilih Klinik]</option>
                <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
                <?php } ?>
              </select>
            
            </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
               			<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
               			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
				    </div>
					<div class="clearfix"></div>
					<? if($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]){?>
					<?}?>
					<? if ($_x_mode == "Edit"){ ?>
					<?php echo $view->RenderHidden("kategori_tindakan_id","kategori_tindakan_id",$biayaId);?>
					<? } ?>
					
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->


              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_content">                
                    <div class="col-md-offset-3 col-md-3 col-sm-12 col-xs-12">
                      <label class="control-label">Jumlah Kunjungan Baru : </label>
                      <?php echo $dataBaru["baru"]; ?>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                      <label class="control-label">Jumlah Pasien Laki-laki : </label>
                      <?php echo $dataLaki["laki"]; ?>
                    </div>
                    <div class="col-md-offset-3 col-md-3 col-sm-12 col-xs-12">
                      <label class="control-label">Jumlah Kunjungan Lama : </label>
                      <?php echo $dataLama["lama"]; ?>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                      <label class="control-label">Jumlah Pasien Perempuan : </label>
                      <?php echo $dataPerempuan["perempuan"]; ?>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12"><br><br> </div>
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

</script>
<?php if(!$_POST["btnExcel"]) { ?>

<br />
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) { 
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
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

<?php if($_x_mode=="cetak"){ ?>	
  window.open('report_pasien_cetak_irna.php?tipe=<?php echo $_POST["reg_tipe_rawat"];?>&kode=<?php echo $_POST["cust_usr_kode"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>&jenis=<?php echo $_POST["reg_jenis_pasien"];?>&cetak=y', '_blank');
<?php } ?>

<?php if($_x_mode=="excel"){ ?>	
  window.open('report_pasien_cetak_irna.php?tipe=<?php echo $_POST["reg_tipe_rawat"];?>&kode=<?php echo $_POST["cust_usr_kode"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>&jenis=<?php echo $_POST["reg_jenis_pasien"];?>&excel=y', '_blank');
<?php } ?>

</script>