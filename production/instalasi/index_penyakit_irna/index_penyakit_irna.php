<?php
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
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "rekap_10_bsr_diagnosa.php";

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;

     //pemanggilan tanggal hari ini 
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
     
     if($_POST["tgl_awal"]) $sql_where[] = "a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     if($_POST["_tipe"]) $sql_where[] = "h.poli_tipe = ".QuoteValue(DPE_CHAR,$_POST["_tipe"]);
    if($_POST["cust_usr_nama"]){
        $sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%'";
     }
     
     if($_POST["cust_usr_kode"]){
        $sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
     }
     
     
    

     $sql = "select a.reg_prosedur_masuk, b.cust_usr_id, b.cust_usr_alergi,b.cust_usr_gol_darah,b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,c.rawat_kasus_keterangan,a.reg_tipe_rawat, 
          a.reg_jenis_pasien, a.reg_status_pasien,a.id_pembayaran, a.reg_keterangan, a.reg_waktu, a.reg_tanggal as tanggal, a.reg_batal, a.reg_id,o.*,p.usr_name as coder,
          a.id_poli, ((current_date - cust_usr_tanggal_lahir)/365) as umur, c.rawat_id,c.rawat_anamnesa, c.rawat_pemeriksaan_fisik, 
          c.rawat_penunjang, c.rawat_diagnosa_utama,c.rawat_diagnosa_kedua,poli_nama,dep_nama, jenis_nama,
          c.rawat_terapi, c.rawat_penting, c.rawat_foto, c.rawat_foto_xray, 
                   d.usr_name as dokter, a.reg_who_update, e.perusahaan_nama, k.jamkesda_kota_nama, l.jkn_nama,h.poli_tipe, n.*,x.*,z.*,m.*,q.*,r.*,s.*
          from klinik.klinik_registrasi a 
          left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
          left join klinik.klinik_perawatan c on c.id_reg = a.reg_id
          
          left join global.global_auth_poli h on a.id_poli = h.poli_id
          left join global.global_departemen i on a.id_dep = i.dep_id
          left join global.global_jenis_pasien j on a.reg_jenis_pasien = j.jenis_id
             left join global.global_auth_user d on d.usr_id = a.id_dokter
             left join global.global_perusahaan e on e.perusahaan_id = a.id_perusahaan
             left join global.global_jamkesda_kota k on k.jamkesda_kota_id = a.id_jamkesda_kota
             left join global.global_jkn l on l.jkn_id = a.reg_tipe_jkn
        left join klinik.klinik_perawatan_icd m on m.id_rawat=c.rawat_id
        left join klinik.klinik_icd n on n.icd_id = m.id_icd
        left join klinik.klinik_inacbg o on a.reg_id = o.id_reg
        left join global.global_auth_user p on p.usr_id = o.inacbg_who_update 
        left join klinik.klinik_perawatan_icd9 q on q.id_rawat = c.rawat_id 
        left join klinik.klinik_icd9 r on r.icd9_id = q.id_icd9
        left join global.global_prosedur_masuk s on  s.prosedur_masuk_id = a.reg_prosedur_masuk
        left join  klinik.klinik_keadaan_keluar_inap z on z.keadaan_keluar_inap_id = a.reg_keadaan_keluar_inap
  
        left join klinik.klinik_perawatan_imunisasi x on x.id_reg=a.reg_id";
      $sql.= " where 1=1 and o.inacbg_id notnull and ".implode(" and ",$sql_where);
      $sql.= " and a.reg_status!='I9' and 
      (h.poli_tipe = 'G' or h.poli_tipe = 'I' or h.poli_tipe = 'J') 
          and b.cust_usr_kode<>'500' and a.reg_batal is null
       ";
      
        $sql.= " order by a.reg_when_update asc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataTable = $dtaccess->FetchAll($rs);
        $today = new DateTime('today');


    $lahir = new DateTime($dataTable['cust_usr_tanggal_lahir']);
        
        // tahun
        // $y = $today->diff($lahir)->y;

        // bulan
        $m = $today->diff($lahir)->m;

        // hari
        $d = $today->diff($lahir)->d;
         //echo $sql;
       // echo $sqljml;
       //var_dump($datawaktuTunggu);
     
     
     // --- construct new table ---- //
   
     $tableHeader = "&nbsp;Kartu Indeks Penyakit Penderita Dirawat di Rumah Sakit";
   
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
    
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Masuk RS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Keluar RS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas Perawatan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode ICD 10 (Diagnosa Utama)";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelompok Umur Penderita (tahun)";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "60%";
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "12";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;





     $tbHeader[1][$counterHeader2][TABLE_ISI] = "0-7";
     $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
     $counterHeader2++;

     $tbHeader[1][$counterHeader2][TABLE_ISI] = "8-28 hari";
    $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
    $counterHeader2++;


    $tbHeader[1][$counterHeader2][TABLE_ISI] = "<1";
    $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
    $counterHeader2++;

    $tbHeader[1][$counterHeader2][TABLE_ISI] = "1-4";
   $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
   $counterHeader2++;



   $tbHeader[1][$counterHeader2][TABLE_ISI] = "5-9";
   $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
   $counterHeader2++;

   $tbHeader[1][$counterHeader2][TABLE_ISI] = "10-14";
  $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
  $counterHeader2++;


  $tbHeader[1][$counterHeader2][TABLE_ISI] = "15-19";
  $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
  $counterHeader2++;

  $tbHeader[1][$counterHeader2][TABLE_ISI] = "20-44";
 $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
 $counterHeader2++;

 $tbHeader[1][$counterHeader2][TABLE_ISI] = "45-54";
 $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
 $counterHeader2++;

 $tbHeader[1][$counterHeader2][TABLE_ISI] = "55-59";
$tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
$counterHeader2++;


$tbHeader[1][$counterHeader2][TABLE_ISI] = "60-69";
$tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
$counterHeader2++;

$tbHeader[1][$counterHeader2][TABLE_ISI] = "70>";
$tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
$counterHeader2++;


  

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa Sekunder";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Komplikasi";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Operasi(Normal,SC,dll)";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Meninggal";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Ket";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
    $counterHeader++;

     //for($i=0,$counter=0,$n=count($dataPasienJml);$i<$n;$i++,$counter=0){
     //$_POST["baris"] = 100;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

        $tanggal = new DateTime($dataTable[$i]["cust_usr_tanggal_lahir"]);

        // tanggal hari ini
        $today = new DateTime('today');

        // tahun
        $y = $today->diff($tanggal)->y;

        // bulan
        $m = $today->diff($tanggal)->m;

        // hari
        $d = $today->diff($tanggal)->d;

         $sql1 = "select * from klinik.klinik_registrasi a
                   left join klinik.klinik_perawatan b on b.id_reg = a.reg_id
                   left join klinik.klinik_perawatan_icd d on d.id_rawat=b.rawat_id 
                   where a.reg_id=".QuoteValue(DPE_CHAR,$dataTable[$i]["reg_id"])."
                   and a.reg_tanggal = ".QuoteValue(DPE_DATE,$dataTable[$i]["tanggal"]);            
         $sql1 .= $sql1_where;          
         $sql1 .= " order by reg_when_update asc";       
         $dataSpan = $dtaccess->FetchAll($sql1); 
         // echo $sql1;
            if($dataTable[$i]["reg_id"]!=$dataTable[$i-1]["reg_id"]) {
          $dataSpan["jml_span"] = count($dataSpan);
          // echo $dataSpan["jml_span"];

            $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
            $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
            $counter++;
            $m++;

              
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
            $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";    
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];      
            $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = date_db($dataTable[$i]["cust"]);
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";    
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];      
            $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
            $counter++;

           

            if($dataTable[$i]["cust_usr_jenis_kelamin"]=='P'){
                $tbContent[$i][$counter][TABLE_ISI] = "Perempuan";
               
            }
            elseif ($dataTable[$i]["cust_usr_jenis_kelamin"]=='L'){
                $tbContent[$i][$counter][TABLE_ISI] = "Laki-laki";
              
            }
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
            $counter++;

         

            if($y==0 && $m==0 && $d<=7){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }

            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";   
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];       
            $counter++;



            if($y==0 && $m==0 && $d<=28 && $d>=8 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
               
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
            $counter++;
           if($y==0 && $m>=0  && $m<12){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }

            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
            $counter++;
            

           if($y>=1 && $y<=4 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];         
            $counter++;
            


            if($y>=5 && $y<=9 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
             
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";   
                $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];         
            $counter++;


            if($y>=10 && $y<=14 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";  
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
            $counter++;
            
            if($y>=15 && $y<=19 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";   
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];       
            $counter++;
            

            if($y>=20 && $y<=44 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];         
            $counter++;

            if($y>=45 && $y<=54 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
               
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];         
            $counter++;
            
            if($y>=55 && $y<=59 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";     
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];     
            $counter++;
            
            if($y>=60 && $y<=69 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
              
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";    
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];      
            $counter++;
            
            if($y>=70 ){
                $tbContent[$i][$counter][TABLE_ISI] = "v";
            
            }
            else{

                $tbContent[$i][$counter][TABLE_ISI] = " ";
                

            }

            $tbContent[$i][$counter][TABLE_ALIGN] = "left";  
             $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
            $counter++;

      

      }

      if ($dataTable[$i]["rawat_icd_status"]=="Primer") {
          # code...
           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor"]."&nbsp;".$dataTable[$i]["icd_nama"]; 
         
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = " ";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++; 


     if ($dataTable[$i]["rawat_icd_status"]=="Sekunder") {
          # code...
           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor"]."&nbsp;".$dataTable[$i]["icd_nama"]; 
         
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = " ";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++; 
        // for($i=0,$counter=0,$n=count($dataIcd);$i<$n;$i++,$counter=0){

        //     if($dataIcd[$i]['rawat_status']=='primer'){

        //         $tbContent[$i][$counter][TABLE_ISI] =$dataIcd[$i]['rawat_status'];
        //         $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
        //         $counter++;
        //     }


        // }
           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd9_nomor"]."&nbsp;".$dataTable[$i]["icd9_nama"]; 

           $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++; 

          if ($dataTable[$i]["rawat_icd_kasus"]=="L") {
          	# code...
          	  $tbContent[$i][$counter][TABLE_ISI] = "Lama"; 

         
          }
          elseif ($dataTable[$i]["rawat_icd_kasus"]=="B") {
          	# code...
          	$tbContent[$i][$counter][TABLE_ISI] = "Baru"; 

          }
            $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++; 

           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["prosedur_masuk_nama"]; 

           $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++; 


          $tbContent[$i][$counter][TABLE_ISI] =$dataTable[$i]["keadaan_keluar_inap_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_VALIGN] = "top"; 
        
          $counter++;
          









       

         
       
     }
     
  //    $colspan = count($tbHeader[0]);
   
   
  //  $counter = 0;
                     
  // $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  // $tbBottom[0][$counter][TABLE_ISI] ="Jumlah";
  // $tbBottom[0][$counter][TABLE_COLSPAN] = 2;
  // $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	// $counter++;

	// //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
  // $tbBottom[0][$counter][TABLE_ALIGN] = "right";


                   
  //    $tbBottom[0][$counter][TABLE_ISI] =$totalLk;
  //    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   
  //    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
  //       $counter++;
   
  //       //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
  //    $tbBottom[0][$counter][TABLE_ALIGN] = "right";

                      
	// $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  //    $tbBottom[0][$counter][TABLE_ISI] =$totalPr;
  //    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
  //       $counter++;
   
      
   
   
   

  
          
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
     
     $sql = "select * from global.global_departemen where dep_id like '%".$depId."%' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);

      $sql = "SELECT * from   global.global_auth_poli where poli_tipe = 'G' or poli_tipe = 'I' or poli_tipe = 'J'
               order by poli_nama"; 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs_edit);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	if($_POST["btnExcel"]){
        
        $_x_mode = "excel";
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

     $tableHeader = "Indeks Penyakit Rawat Jalan dan IGD";
     
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
                    <h2><?php echo $tableHeader; ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >

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

</script>

<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

<!-- <script type="text/javascript">
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
</script> -->

<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>lib/script/scroll_ipad2.js"></script>
<style type="text/css">
#top{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0068c9), to(#007bed));
background: -moz-linear-gradient(top, #0068c9, #007bed); 
}
#footer{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#007bed), to(#0068c9));
background: -moz-linear-gradient(top, #007bed, #0068c9);
}
</style>			
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
				  
          <div class="form-group">
            <label class="control-label col-md-4 col-sm-4 col-xs-12">Poli</label>
            <div class="col-md-5 col-sm-5 col-xs-12">
                 <select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                            <option value="">[Pilih Klinik]</option>
                            <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                            <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
                            <?php } ?>
                </select>
            </div>
    			</div>  

       
				  					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
               			<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success"> 
               			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
                     <!-- <input type="submit" name="btnExcel" id="btnExcel" value="Excel" class="pull-right btn btn-success"> -->
				    </div>
					<div class="clearfix"></div>
										
					<!-- <script type="text/javascript">
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
					</script> -->
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
					         <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                       <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
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

<script>


<?php if($_x_mode=="cetak"){ ?>	
  window.open('index_penyakit_irj_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&tipe=<?php echo $_POST["_tipe"];?>&cetak=y', '_blank');
<?php } ?>

<?php if($_x_mode=="excel"){ ?>	
 window.open('index_penyakit_irj_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&tipe=<?php echo $_POST["_tipe"];?>&excel=y', '_blank');
<?php } ?>


</script>

