<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");    
     require_once($LIB."currency.php");
	 
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
	   $depId = $auth->GetDepId();
     $thisPage = "lap_tindakan.php";
    
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $lokasi = $ROOT."/gambar/img_cfg";
     $depLowest = $auth->GetDepLowest();
     $depNama = $auth->GetDepNama(); 
     
     //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     if(!$_POST["klinik"]) $_POST["klinik"]=$depId;
	   else $_POST["klinik"] = $_POST["klinik"];   
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }


     $printPage = "../input_hasil_lab_irj/input_hasil_lab_cetak2.php?id_reg=";

	   $sql = "select * from  klinik.klinik_split where id_tahun_tarif = ".QuoteValue(DPE_CHAR,$tahunTarif)." order by split_urut asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataSplit = $dtaccess->FetchAll($rs);
    // echo $sql;
     
 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
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
	 
	 if(!$_POST["cust_usr_jenis"])  $_POST["cust_usr_jenis"]="0";
     
     $perusahaan = $_POST["ush_id"];
     $kasir = $_POST["pembayaran_create"];
      
     //$sql_where[] = "reg_tanggal is not null and a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     if($_POST["tgl_awal"]) $sql_where[] = "d.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "d.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
    if($_POST["js_biaya"]) $sql_where[] = "i.pembayaran_jenis = ".QuoteValue(DPE_CHAR,$_POST["js_biaya"]);
     //if($_POST["jbayar"]) $sql_where[] = "m.id_jbayar = ".QuoteValue(DPE_CHAR,$_POST["jbayar"]);
     if($_POST["cust_usr_kode"]) $sql_where[] = "c.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
     if($_POST["pembayaran_create"]&& $_POST["pembayaran_create"]!="--") $sql_where[] = "i.pembayaran_who_create like ".QuoteValue(DPE_CHAR,"%".$_POST["pembayaran_create"]."%");
     
     //$sql_where[] = " (pembayaran_flag='y' or pembayaran_flag='k') ";
     
     if($_POST["id_dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     
     if($_POST["reg_shift"]){
		$sql_where[] = " d.reg_shift = ".QuoteValue(DPE_DATE,$_POST["reg_shift"]);
	 }
    
	 if($_POST["reg_tipe_layanan"]){
		$sql_where[] = "d.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
	 } 
     
     if($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"]!="0"){
		 $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	   }
	   
	     if($_POST["ush_id"]){                              
		 $sql_where[] = "d.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["ush_id"]);
	   }
     
      if($_POST["cito"]){                              
     $sql_where[] = "n.is_cito = ".QuoteValue(DPE_CHAR,$_POST["cito"]);
     }
	     
     
     if($_POST["fol_nama"]){
      $sql_where[] = "upper(a.fol_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["fol_nama"])."%");

     } 
  
     
     if($_POST["reg_status_pasien"]){
      $sql_where[] = "reg_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["cito"]);
     } 
    if($_POST["id_usr"]){
    $sql_where[] = " a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_usr"]);
   }
	 
	 /*if(!$userId == 'b9ead727d46bc226f23a7c1666c2d9fb'){
		$sql_where[] = " i.pembayaran_who_create = '".$userName."'";
	 }*/
	 
     //$sql_where = implode(" and ",$sql_where);

     $sql = "select a.*,  c.cust_usr_nama, c.cust_usr_kode, b.biaya_nama, d.reg_tanggal,             
             f.jenis_nama, g.usr_name as dokter, i.*, d.reg_jenis_pasien,d.reg_id, e.dep_nama,
             j.poli_nama, k.tipe_biaya_nama, l.shift_nama, m.usr_name as ptg_entri , n.is_cito  
             ,h.pemeriksaan_id,h.pemeriksaan_pasien_nama, pemeriksaan_hasil ,o.poli_nama as poli_asal      
             from  klinik.klinik_folio a  
              left join klinik.klinik_registrasi d on a.id_reg = d.reg_id
              left join klinik.klinik_pembayaran i on i.pembayaran_id = a.id_pembayaran 
             left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
             left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
               left join global.global_departemen e on e.dep_id = a.id_dep 
             left join global.global_jenis_pasien f on f.jenis_id = d.reg_jenis_pasien
             left join global.global_auth_user g on a.id_dokter = g.usr_id
             left join global.global_auth_poli j on j.poli_id = d.id_poli
             left join global.global_tipe_biaya k on k.tipe_biaya_id = d.reg_tipe_layanan
                   left join global.global_shift l on l.shift_id = d.reg_shift
             left join global.global_auth_user m on a.who_when_update = m.usr_id
             left join klinik.klinik_biaya_tarif n on a.id_biaya_tarif = n.biaya_tarif_id
             left join laboratorium.lab_pemeriksaan h on a.id_reg = h.id_reg
              left join global.global_auth_poli o  on o.poli_id = d.id_poli_asal
              where  j.poli_tipe='R' and  (d.reg_batal is null or reg_batal='n') and d.reg_tanggal >='06-07-2020'
              and d.reg_tanggal <='08-06-2020' ";
   // $sql.= " where ".implode(" and ",$sql_where);
   //   $sql.= " and j.poli_tipe='R' and  (d.reg_batal is null or reg_batal='n') ";
     $sql.= "order by a.id_pembayaran asc,a.id_reg asc, d.reg_tanggal asc,d.reg_waktu asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs); 
	   //echo $sql;


      for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["id_pembayaran"]==$dataTable[$i-1]["id_pembayaran"] ){
          $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
          }      
      }                                                                                      
     // -- end ---

     $counter=0;
     $counterHeader=0;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
    $counterHeader++;
    
    /*$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
    $counterHeader++;*/
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Lab";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "No Rekam Medis";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%"; 
    $counterHeader++;
         
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli Asal";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
    $counterHeader++;
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter Pengirim";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
    $counterHeader++;
    
    /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
    $counterHeader++;*/

    /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
    $counterHeader++;*/
  
  /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Shift";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
    $counterHeader++;*/
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;

    /*$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
    $counterHeader++;
    */
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
    $counterHeader++;

    // $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
    // $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
    // $counterHeader++;
    
  /*   $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Biaya";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Tindakan";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
     */
  /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Dijamin";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
  $tbHeader[0][$counterHeader][TABLE_ISI] = "Subsidi";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
  $tbHeader[0][$counterHeader][TABLE_ISI] = "Harus Bayar";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Diskon";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Kurang Bayar";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Pembayaran";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Dijamin";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Keterangan";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;*/
   /*
    for($i=0,$n=count($dataSplit);$i<$n;$i++){
       
       $tbHeader[0][$counterHeader][TABLE_ISI] = $dataSplit[$i]["split_nama"];
       $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";  
       $counterHeader++;		
       //$counter=0;
     //$n = $i;
    }     
     */ 
    // $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
    // $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    // $counterHeader++;
    
    // $tbHeader[0][$counterHeader][TABLE_ISI] = "Pelaksana";
    // $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    // $counterHeader++;
    
    /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Ptg. Entri";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;*/

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;
    
    // $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Petugas";
    // $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    // $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Hasil Lab";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
    $counterHeader++;


    for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
         
         
    /*$sql = "select sum(a.pembayaran_total) as total from klinik.klinik_pembayaran a
           left join klinik.klinik_registrasi d on d.reg_id = a.id_reg";
    $sql .= " where a.pembayaran_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_pembayaran"])." 
    and date(a.pembayaran_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." 
    and date(a.pembayaran_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]))."
             and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);      
   $dataDet = $dtaccess->Fetch($sql); */
   
   $sql="select usr_name from klinik.klinik_folio_pelaksana b 
         left join global.global_auth_user g on b.id_usr=g.usr_id 
         left join klinik.klinik_folio a on a.fol_id=b.id_fol 
         where b.id_fol=".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"])." order by fol_pelaksana_tipe asc";
    $pelaksana=$dtaccess->FetchAll($sql);
    //echo $sql;
                      
         if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"] ){
          $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_pembayaran"]]+1;
             
             $tbContent[$i][$counter][TABLE_ISI] = $m+1;
             $tbContent[$i][$counter][TABLE_ALIGN] = "right";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
             $m++;

             /*$daytime = explode(".", $dataTable[$i]["pembayaran_create"]);
             $time = explode(" ", $daytime[0]);*/
             $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;

              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nomor_kwitansi"];
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
             
             /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_det_kwitansi"];
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;*/
     
             $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
     
             if( $dataTable[$i]["cust_usr_kode"]=='100'){
               
            
  
             $tbContent[$i][$counter][TABLE_ISI] =  $dataTable[$i]["pemeriksaan_pasien_nama"];
             } else {
             $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
             }
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;

            
            if( $dataTable[$i]["cust_usr_kode"]=='100'){         
             $tbContent[$i][$counter][TABLE_ISI] =  "Laboratorium";
             } else {
             $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_asal"];
             }

             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++; 
           
             
             /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;*/

             /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;*/
         
       /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["shift_nama"];
             $tbContent[$i][$counter][TABLE_ALIGN] = "left";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;*/
        }
     //echo $sql;
         //  if ($dataTable[$i]['is_cito'] == 'C') { $cito = " ( CITO )"; } else { $cito = ""; }
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"].$cito;
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";
         $counter++;

                  
/*          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_nominal_satuan"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
*/
         $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_jumlah"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_nominal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
         
        /*  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_nominal"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
         $totalBiaya += $dataTable[$i]["fol_nominal"]; 
     
     $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where
            id_pembayaran =".QuoteValue(DPE_CHAR,$dataTable[$i]["id_pembayaran"]); 
    //echo $sql;
    $dataDetFolJum = $dtaccess->Fetch($sql);
    $totalFolioDetail=$dataDetFolJum["total"];

         if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
       $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalFolioDetail);
       $tbContent[$i][$counter][TABLE_ALIGN] = "right";
       $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
       $counter++;
       $totalFolio += $totalFolioDetail;
         } */
     
     /*$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_dijamin"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
     
     $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_subsidi"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
     
     $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_hrs_bayar"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
       
         if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
             $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_diskon"]);
             $tbContent[$i][$counter][TABLE_ALIGN] = "right";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
   
             $totalDiskon += $dataTable[$i]["pembayaran_diskon"];
          
             $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_total"]-$dataTable[$i]["pembayaran_yg_dibayar"]);
             $tbContent[$i][$counter][TABLE_ALIGN] = "right";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
             $totalKurangBayar += $dataTable[$i]["pembayaran_total"]-$dataTable[$i]["pembayaran_yg_dibayar"];
          
             $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_det_total"]);
             $tbContent[$i][$counter][TABLE_ALIGN] = "right";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
             $totaled += $dataTable[$i]["pembayaran_det_total"];
             
             $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_dijamin"]);
             $tbContent[$i][$counter][TABLE_ALIGN] = "right";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
             $totalDijamin += $dataTable[$i]["pembayaran_dijamin"];
             
             if (($dataTable[$i]["pembayaran_total"]-$dataTable[$i]["pembayaran_yg_dibayar"]>0) && $dataTable[$i]["pembayaran_diskon"]>0)
                 $tbContent[$i][$counter][TABLE_ISI] = "Kurang Bayar & Diskon";
             else if ($dataTable[$i]["pembayaran_diskon"]>0) 
                 $tbContent[$i][$counter][TABLE_ISI] = "Diskon";
             else if ($dataTable[$i]["pembayaran_total"]-$dataTable[$i]["pembayaran_yg_dibayar"]>0) 
                 $tbContent[$i][$counter][TABLE_ISI] = "Kurang Bayar";   
             else     
                $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
                
             $tbContent[$i][$counter][TABLE_ALIGN] = "center";
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
             $counter++;
         } */
    /*    
     for($j=0,$k=count($dataSplit);$j<$k;$j++){
       $Split = $dataFolSplit[$dataTable[$i]["fol_id"]][$dataSplit[$j]["split_id"]];
       $tbContent[$i][$counter][TABLE_ISI] = currency_format($Split);
       $tbContent[$i][$counter][TABLE_ALIGN] = "right";          
       $counter++;
       $totSplit[$j] += $Split;            
     }
       */
    //  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
    //  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //  $counter++; 
     
    //  $tbContent[$i][$counter][TABLE_ISI] = $pelaksana[0]["usr_name"];
    //  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //  $counter++;  
     
    // /* $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ptg_entri"];
    //  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //  $counter++; */

    //  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
    //  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //  $counter++;   
   
  //  if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
  //    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_who_create"];
  //    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  //    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
  //    $counter++;
  //    }

     if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
     $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Rincian" title="Rincian" border="0" onClick="CetakHasilLab(\''.$dataTable[$i]["reg_id"]."-".$dataTable[$i]["pembayaran_id"].'-'.$dataTable[$i]["reg_tipe_rawat"].'\');"/>';  
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
     $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
     $counter++;
     }

       $total+=$dataTable[$i]["fol_nominal"];

   }  
    
  $counter = 0;
         
  $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  $tbBottom[0][$counter][TABLE_ISI] = "Total Biaya";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 6;
  $tbBottom[0][$counter][TABLE_ALIGN] = "center";
  $counter++;


  $tbBottom[0][$counter][TABLE_WIDTH] = "30%"; 
  $tbBottom[0][$counter][TABLE_COLSPAN] = 7;
  $tbBottom[0][$counter][TABLE_ISI] =  currency_format($total);
  $tbBottom[0][$counter][TABLE_ALIGN] = "right";
  $counter++;

         
//   $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
//   $tbBottom[0][$counter][TABLE_ISI] = "Total Biaya";
// $tbBottom[0][$counter][TABLE_COLSPAN] = 6;
// $tbBottom[0][$counter][TABLE_ALIGN] = "center";
// $counter++;


// $tbBottom[0][$counter][TABLE_WIDTH] = "30%"; 
// $tbBottom[0][$counter][TABLE_COLSPAN] = 8;
// $tbBottom[0][$counter][TABLE_ISI] =  currency_format($total);
// $tbBottom[0][$counter][TABLE_ALIGN] = "right";
// $counter++;




 
  /*  
    $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalFolio);
    $tbBottom[0][$counter][TABLE_ALIGN] = "right";
    $counter++;*/
 
    /*$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_COLSPAN] = 3;
    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
    $counter++;
 
    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalDiskon);
   $tbBottom[0][$counter][TABLE_ALIGN] = "right";
   $counter++;
   
     $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalKurangBayar);
   $tbBottom[0][$counter][TABLE_ALIGN] = "right";
   $counter++;
   
   $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totaled);
   $tbBottom[0][$counter][TABLE_ALIGN] = "right";
   $counter++;
   
   $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalDijamin);
   $tbBottom[0][$counter][TABLE_ALIGN] = "right";
   $counter++;
 
   $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   $tbBottom[0][$counter][TABLE_COLSPAN] = 1;
   $tbBottom[0][$counter][TABLE_ALIGN] = "center";
   $counter++; 
 
   for($j=0,$k=count($dataSplit);$j<$k;$j++){
        $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
        $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totSplit[$j]);
        $tbBottom[0][$counter][TABLE_ALIGN] = "right";
        $counter++;
    }
     */
 
     $tableHeader ="Laporan Jasa Dokter";
 if($_POST["btnExcel"]){
          $_x_mode = "excel" ;  

    }  
 
  if($_POST["btnCetak"]){
     $_x_mode = "cetak" ;      
  }
     

     // cari jenis pasien e
    $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $jenisPasien = $dtaccess->FetchAll($rs);
    
    
    // cek nama perusahaan --
    $sql = "select * from global.global_jenis_pasien where jenis_id = '7'";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $corporate = $dtaccess->Fetch($rs);
    
     // cari nama perusahaan --
    $sql = "select * from global.global_perusahaan where id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $NamaPerusahaan = $dtaccess->FetchAll($rs);
    
    
     //ambil nama dokter e
    $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])." order by usr_id asc ";
    $rs = $dtaccess->Execute($sql);
    $dataDokter = $dtaccess->FetchAll($rs);
         
    $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    
     if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
     if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
     
     if($_POST["dep_logo"]) $fotoName = $lokasi."/".$row_edit["dep_logo"];
     else $fotoName = $lokasi."/default.jpg";    
    
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
         $sql = "select * from global.global_departemen where dep_id = '".$depId."' order by dep_id";
         $rs = $dtaccess->Execute($sql);
         $dataKlinik = $dtaccess->FetchAll($rs);
    }

     // Data Poli //
    $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama";
    $dataPoli = $dtaccess->FetchAll($sql);       
  
  // cari tipe layanan
    $sql = "select * from global.global_tipe_biaya where tipe_biaya_aktif='y' order by tipe_biaya_nama desc";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $tipeBiaya = $dtaccess->FetchAll($rs);
    
     // cari nama kasir --
    $sql = "select * from global.global_auth_user_app a left join global.global_auth_user b on a.id_usr = b.usr_id where id_app = 5";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataKasir = $dtaccess->FetchAll($rs);
  
  //cari shift by id
     $sql = "select * from global.global_shift where shift_id = '".$_POST["reg_shift"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShiftId = $dtaccess->Fetch($rs);

     $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
     $dataJenisBayar2= $dtaccess->FetchAll($sql);
    
?>





<!DOCTYPE html>
<html lang="en">
<script type="text/javascript">
  function CetakHasilLab(id)
 {
     var all_id = id.split('-');
     window.open('../input_hasil_lab_irj/input_hasil_lab_cetak2.php?id_reg='+all_id[0],'Rincian Tagihan');
}

function ProsesCetakIRNA(id) {
//alert(id);

     var all_id = id.split('-');
     var link  = 'edit_data_pasiene.php?usr_id='+all_id[0]+'&id_reg='+all_id[1]+'&kode='+all_id[2];
     
  window.open('cetak_tagihan_rinci.php?id_reg='+all_id[0]+'&pembayaran_id='+all_id[1]+'&pembayaran_det_id='+all_id[2],'Cetak Kwitansi Rincian Rinci'); 
  //document.location.href='<?php echo $thisPage;?>';
}
</script>
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
                    <h2><?php echo $tableHeader; ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					 
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
            <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
             
            </div>
            
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
            <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
            
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Tindakan</label>
						
							<input class="form-control col-md-7 col-xs-12" type="text"  id="fol_nama" name="fol_nama" size="100" maxlength="255" value="<?php echo $_POST["fol_nama"];?>"/>
						
				    </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
                        <?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               					<select class="select2_single form-control" name="reg_status_pasien" id="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          							<option value="" >[ Pilih Jenis Pasien ]</option>
		  							<option value="B" <?php if('B'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Baru</option>
          							<option value="L" <?php if('L'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Lama</option>
								</select>
            
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
						
							<select class="select2_single form-control" name="id_usr">
								 <option value="">[ Pilih Dokter ]</option>
                                    <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                                      <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_usr"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $dataDokter[$i]["usr_id"]; ?>');"><?php echo $dataDokter[$i]["usr_name"]; ?></option>
                                    <?php } ?>
							</select>
						
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
               			<!--<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">-->
               			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
               			<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
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
					  <!-- <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">-->
                      <!--
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
                      -->
                      <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
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















<?php if(!$_POST["btnExcel"]) { ?>

<br /><br /><br /><br />

<?php } ?>
<script language="JavaScript">   
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
     }
}

  window.onload = function() { TampilCombo(); TampilKasir();}
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

	function TampilKasir(id)
    {        
         
         //alert(id);
         if(id=="b9ead727d46bc226f23a7c1666c2d9fb"){
              usr_id.disabled = false;
              //elm_combo.checked = true; 
                       
         } else {
              usr_id.disabled = true;
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
  window.open('lap_jasdok_cetak.php?klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&dokter=<?php echo $_POST["id_usr"];?>&cetak=y', '_blank');
<?php } ?>
<?php if($_x_mode=="excel"){ ?> 
  window.open('lap_jasdok_cetak.php?klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&dokter=<?php echo $_POST["id_usr"];?>&excel=y', '_blank');
<?php } ?>

</script>

<?php if(!$_POST["btnExcel"]) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<?php } ?>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '50%',
'height' : '100%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=950,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=950,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function ProsesEditing(id) {

	   var all_id = id.split('-');
     var link  = 'input_report_setoran_loket.php?bahan_edit='+all_id[0]+'&klinik='+all_id[1];
	   BukaWindow(link);
	   //document.location.href='<?php echo $thisPage;?>';
}
</script>



<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php if($_POST["btnExcel"]) {?>
<?php } ?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php if($konfigurasi["dep_konf_dento"]=='y') { ;?>
<!--------Buat Helpicon----------->
<script type="text/javascript">
function showHideGB(){
var gb = document.getElementById("gb");
var w = gb.offsetWidth;
gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
gb.opened = !gb.opened;
}
function moveGB(x0, xf){
var gb = document.getElementById("gb");
var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>

<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div>
<?php } ?>
</div>

<?php } ?>

 

