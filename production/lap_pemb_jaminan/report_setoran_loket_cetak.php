  <?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $userId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $tahunTarif = $auth->GetTahunTarif();
     $thisPage = "report_setoran_loket.php";
     $printPage = "report_setoran_loket_cetak.php?";
    
     //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
      if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; } 
        else if(!$_POST["klinik"]) { $_POST["klinik"]=$depId; }
   
     

	   $sql = "select * from  klinik.klinik_split where id_tahun_tarif=".QuoteValue(DPE_CHAR,$tahunTarif)." order by split_urut asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataSplit = $dtaccess->FetchAll($rs);
    // echo $sql;
 
 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_id"] = $konfigurasi["dep_id"];
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
          
       $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_GET['tgl_awal']){
     $_GET['tgl_awal']  = $skr;
     }
     if(!$_GET['tgl_akhir']){
     $_GET['tgl_akhir']  = $skr;
     }
     
	//cari shift
	 $sql = "select * from global.global_shift order by shift_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);
	 
         if(!$_GET["cust_usr_jenis"])  $_GET["cust_usr_jenis"]="0";
    
    //$sql_where[] = "reg_tanggal is not null and a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 
     if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_GET["klinik"]);
     if($_GET["tgl_awal"]) $sql_where[] = "date(d.reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "date(d.reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
	 if($_POST["js_biaya"]) $sql_where[] = "i.pembayaran_jenis = ".QuoteValue(DPE_CHAR,$_POST["js_biaya"]);
     if($_POST["jbayar"]) $sql_where[] = "i.id_jbayar = ".QuoteValue(DPE_CHAR,$_POST["jbayar"]);
     if($_GET["kode"]) $sql_where[] = "c.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_GET["kode"]."%");
    // $sql_where[] = " (pembayaran_flag='y' or pembayaran_flag='k') ";
     if($_GET["dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["dokter"]);

	if($_GET["shift"]){
		$sql_where[] = " d.reg_shift = ".QuoteValue(DPE_CHAR,$_GET["shift"]);
	 }
     
	 if($_GET["reg_tipe_layanan"]){
		$sql_where[] = "d.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_GET["layanan"]);
	 }
	 
     if($_GET["cust_usr_jenis"]){
		 $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_jenis"]);
	   }
	   
	     if($_GET["ush_id"]){
		 $sql_where[] = "d.id_perusahaan = ".QuoteValue(DPE_CHAR,$_GET["ush_id"]);
	   }
	 
	 //if($_GET["kasir"]<>"--"){
	//	 $sql_where[] = "i.pembayaran_who_create = ".QuoteValue(DPE_CHAR,$_GET["kasir"]);
	//   }
     
 if($_GET["layanan"]<>"--"){
		if($_GET["layanan"]=="A") {$sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500')";}
   elseif($_GET["layanan"]=="I")
   {$sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='I'";}
   elseif($_GET["layanan"]=="G")
   {$sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='G'";}
   else{$sql_where[] = "(a.id_cust_usr ='100' or a.id_cust_usr ='500')";}
	   }      



     $sql_where = implode(" and ",$sql_where);
     $sql = "select a.*, c.cust_usr_nama, c.cust_usr_kode, b.biaya_nama,              
             f.jenis_nama, g.usr_name as dokter, i.*, d.reg_jenis_pasien, d.reg_tanggal, d.reg_waktu,e.dep_nama,
             j.poli_nama, k.tipe_biaya_nama, l.shift_nama          
             from  klinik.klinik_folio a  
              left join klinik.klinik_registrasi d on a.id_reg = d.reg_id
              left join klinik.klinik_pembayaran i on i.pembayaran_id = d.id_pembayaran 
             left join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
             left join  klinik.klinik_biaya b on b.biaya_id = a.id_biaya
        	   left join global.global_departemen e on e.dep_id = a.id_dep 
             left join global.global_jenis_pasien f on f.jenis_id = d.reg_jenis_pasien
             left join global.global_auth_user g on a.id_dokter = g.usr_id
             left join global.global_auth_poli j on j.poli_id = d.id_poli
             left join global.global_tipe_biaya k on k.tipe_biaya_id = d.reg_tipe_layanan
			       left join global.global_shift l on l.shift_id = d.reg_shift";
     $sql .= " where 1=1  and ".$sql_where; 
     $sql .= " order by d.reg_tanggal,d.reg_waktu,a.fol_waktu";
	   //echo $sql;
     $dataTable = $dtaccess->FetchAll($sql);

      for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["id_pembayaran"]==$dataTable[$i-1]["id_pembayaran"] ){
          $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
          }      
      }                                                                                      
     // -- end ---
     /*$m=0;

     $sql = "select b.* from  klinik.klinik_folio_split b
             inner join  klinik.klinik_folio a on b.id_fol = a.fol_id
             left join klinik.klinik_pembayaran i on i.pembayaran_id = a.id_pembayaran
             left join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
			       join  klinik.klinik_registrasi d on d.reg_id = a.id_reg and d.id_cust_usr = a.id_cust_usr 
             left join klinik.klinik_split e on e.split_id = b.id_split";
     $sql .= " where d.reg_status like 'G%' and ".$sql_where; 
     $sql .= " order by a.id_pembayaran,i.pembayaran_create,a.fol_waktu, e.split_urut";
    // echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
  	 while($row = $dtaccess->Fetch($rs)) {
  		$dataFolSplit[$row["id_fol"]][$row["id_split"]] = $row["folsplit_nominal"];
		   }
       */
   	 $counter=0;
   	 $counterHeader=0;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
	   $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
	   $counterHeader++;
     
     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;*/
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%"; 
	   $counterHeader++;
     	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
     
     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;*/

     /*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Shift";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;   */
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
	   $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
	   $counterHeader++;
	   
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
	   $counterHeader++;
	   
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
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
	   $counterHeader++;
	   
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Ptg Entri";
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

              //$daytime = explode(".", $dataTable[$i]["pembayaran_create"]);
              //$time = explode(" ", $daytime[0]);
              $tbContent[$i][$counter][TABLE_ISI] = date_db($dataTable[$i]["reg_tanggal"]);
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
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
    	
              if($dataTable[$i]["cust_usr_kode"]=='500' || $dataTable[$i]["cust_usr_kode"]=='100'){
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_keterangan"];
              } else {
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
              }
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;
              
              /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;*/

              /*
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;
          
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["shift_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++; */
	       }
			//echo $sql;
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

	                 
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_nominal_satuan"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_jumlah"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
		  
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
          }
		  
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
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
      $counter++; 
      
      /*
      $tbContent[$i][$counter][TABLE_ISI] = $pelaksana[1]["usr_name"];
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
      $counter++; */ 

      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
      $counter++;   
	  
	  if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
	  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_who_create"];
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
      $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
      $counter++;
      }
    }  
     
	 $counter = 0;
          
        $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
     $tbBottom[0][$counter][TABLE_COLSPAN] = 10;
     $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	   $counter++;

	   $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalFolio);
	   $tbBottom[0][$counter][TABLE_ALIGN] = "right";
	   $counter++;
	
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
    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_COLSPAN] = 4;
    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	  $counter++;     
     
     $tableHeader = "Report Pembayaran";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran.xls');
     }
     
       if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
     
     //ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_id=".QuoteValue(DPE_NUMERIC,$_GET["cust_usr_jenis"]);
     $rs = $dtaccess->Execute($sql);
     $jenisPasien = $dtaccess->Fetch($rs);
     
          //Data Klinik
          $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
          
          //echo $sql;
          $sql = "select dep_nama from global.global_departemen where dep_id = '".$_GET["klinik"]."'";
          $rs = $dtaccess->Execute($sql);
          $namaKlinik = $dtaccess->Fetch($rs);
          $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
          
        // cari tipe layanan
     $sql = "select * from global.global_tipe_biaya where tipe_biaya_id = '".$_GET["layanan"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tipeBiaya = $dtaccess->Fetch($rs);

	 //cari shift by id
			$sql = "select * from global.global_shift where shift_id = '".$_GET["shift"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataShiftId = $dtaccess->Fetch($rs);
			
			//cari nama petugas by id
			$sql = "select * from global.global_auth_user where usr_id = '".$_GET["kasir"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataKasirId = $dtaccess->Fetch($rs);

  $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";   
  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
 
  if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }
  
  
?>




<script language="javascript" type="text/javascript">

window.print();

</script>

<!-- Print KwitansiCustom Theme Style -->
    <link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
		<span class="judul3">
		<?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
    <span class="judul4">       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>  
  </tr>
</table>
<br>
 <table border="0" colspan="2" cellpadding="2" cellspacing="0" style="align:left" width="100%">     
    <tr>
     <?php if($_GET["tgl_awal"]==$_GET["tgl_akhir"]) { ?> 
      <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tanggal</td>
       <td width="1%">:</td>
       <td width="19%"><?php echo ($_GET["tgl_awal"]);?></td>
      <?php }else{ ?>
      <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode Tgl masuk</td>
      <td width="1%">:</td>
      <td width="19%"><?php echo ($_GET["tgl_awal"]);?> s/d <?php echo ($_GET["tgl_akhir"]);?></td>      
      <?php } ?>
      <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PENDAPATAN</td>   
    </tr>
    <!--
    <tr>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift</td>
       <td width="1%">:</td>
       <td colspan="2"><?php if($_GET["shift"]=="--"){ echo "--";} else { echo $dataShiftId["shift_nama"];} ?> </td>
    </tr>
	<tr>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Nama Petugas</td>
       <td width="1%">:</td> 
         <td colspan="2"><?php if($_GET["kasir"]=="--"){ echo "--";} else { echo $_GET["kasir"];} ?> </td>
    </tr>
	<tr>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Layanan</td>
       <td width="1%">:</td>       
        <td colspan="2"><?php if($_GET["reg_tipe_layanan"]=="--"){ echo "--";} else { echo $tipeBiaya["tipe_biaya_nama"];} ?> </td>
    </tr> -->
    <tr>
    <?php if($_GET["cust_usr_jenis"]) { ?>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Pasien</td>
       <td width="1%">:</td>       
       <td colspan="2"class="tablecontent"><?php echo $jenisPasien["jenis_nama"];?></td>
       <?php } ?>
    </tr>
    <!--
    <tr>
       <?php if($_GET["layanan"]=='A') { ?>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Pendapatan</td>
       <td width="1%">:</td>       
       <td colspan="2">Rawat Jalan </td>
        <?php } elseif($_GET["layanan"]=='I') { ?>
       <td width="10%"style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Pendapatan</td>
       <td width="1%">:</td>       
       <td colspan="2">Rawat INAP </td>
       <?php } elseif($_GET["layanan"]=='G') { ?> 
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Pendapatan</td>
       <td width="1%">:</td>       
       <td colspan="2">I G D </td>
       <?php } elseif($_GET["layanan"]=='O'){ ?>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Pendapatan</td>
       <td width="1%">:</td>       
       <td colspan="2">Non Fungsional</td>
       <?php } else{ ?>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Pendapatan</td>
       <td width="1%">:</td>       
       <td colspan="2">--</td>
       <?php } ?>              

    </tr> -->
    
  </table>
 <br>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td>
</tr>
</table> 
