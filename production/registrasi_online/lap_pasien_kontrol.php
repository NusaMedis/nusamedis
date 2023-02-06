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

    if(!$_GET["tanggal_awal"]) $_GET["tanggal_awal"] = $skr;
        if(!$_GET["tanggal_akhir"]) $_GET["tanggal_akhir"] = $skr;
        if($_GET["tanggal_awal"]) $_GET["tanggal_awal"] =  $_GET["tanggal_awal"];
        if($_GET["tanggal_akhir"]) $_GET["tanggal_akhir"] =  $_GET["tanggal_akhir"];
        
       if($_GET["cust_usr_kode"])  $sql_where[] = "c.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_GET["cust_usr_kode"]."%");
     if($_GET["cust_usr_nama"])  $sql_where[] = "upper(c.cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".$_GET["cust_usr_nama"]."%");
     if($_GET["reg_buffer_no_antrian"])  $sql_where[] = " a.reg_buffer_no_antrian like ".QuoteValue(DPE_CHAR,"%".$_GET["reg_buffer_no_antrian"]."%");
     if($_GET["reg_buffer_flag"])  $sql_where[] = "a.reg_buffer_flag = ".QuoteValue(DPE_CHAR,$_GET["reg_buffer_flag"]);
     if($_GET["is_daftar"]){
      if ($_GET["is_daftar"]=="y" or $_GET["is_daftar"]=="n" ) {
        # code...
         $sql_where[] = "a.is_daftar = ".QuoteValue(DPE_CHAR,$_GET["is_daftar"]);
      }
      else{
        $sql_where[] = "a.reg_buffer_batal = 'y' ";
      }

     } 
     if (!$_GET["is_daftar"]) {
       # code...
      $sql_where[]="(reg_buffer_batal ='n' or reg_buffer_batal is null )";

     }
     if($_GET["poli_id"])  $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["poli_id"]);
     if($_GET["dokter"])  $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["dokter"]);
     
     $sql_where[] = "DATE(a.reg_buffer_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     $sql_where[] = "DATE(a.reg_buffer_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

     if ($sql_where[0]) 
     $sql_where = implode(" and ",$sql_where);
      
          $sql = "select a.*, e.usr_name, d.poli_nama,c.cust_usr_nama as nama,c.cust_usr_alamat,c.cust_usr_kode as rm, cust_usr_no_hp, f.jenis_nama, g.tipe_biaya_nama,c.cust_usr_id,x.*
          from klinik.klinik_registrasi_buffer a 
          left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
          left join global.global_auth_poli d on d.poli_id = a.id_poli
          left join global.global_auth_user e on e.usr_id = a.id_dokter
          left join global.global_jenis_pasien f on a.reg_buffer_jenis_pasien = f.jenis_id
          left join global.global_tipe_biaya g on a.reg_buffer_tipe_layanan = g.tipe_biaya_id
          left join global.global_login_cust x on a.cust_id_login = x.login_cust_id
          where   reg_buffer_flag <>' ' and ( reg_buffer_flag = ' ' or reg_buffer_flag = 'W' ) and (reg_buffer_utama='' or reg_buffer_utama is null) and
          ".$sql_where."
                  order by a.reg_buffer_no_antrian asc";
          // echo $sql;
          $dataTable = $dtaccess->FetchAll($sql);
//                  a.pembayaran_flag = 'n' and 
//sementara dihilangkan

          $tableHeader = "&nbsp;Daftar Pasien Kontrol";
          $counterHeader = 0;
          
        
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Register";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;


      
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Medrec";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
      
     
     
      

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "23%";
          $counterHeader++;

           $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "23%";
          $counterHeader++;


          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nomor Handphone";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          if ($_GET["is_daftar"]=="k"){
             $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Pembatalan";
            
          }
          else{
             $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Registrasi";

          }
          
         
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Kunjungan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Kunjungan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++; 
                              
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "22%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Registrasi";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;


          
          
      for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
          
          $editPage = "reg_pas_lama.php?idbuffer=".$dataTable[$i]["reg_buffer_id"]."&cust_usr_id=".$dataTable[$i]["id_cust_usr"]."&id_poli=".$dataTable[$i]["id_poli"]."&jenis_pasien=".$dataTable[$i]["reg_buffer_jenis_pasien"]."&tipe=".$dataTable[$i]["reg_buffer_tipe_rawat"]."&dokter=".$dataTable[$i]["id_dokter"]."&no_antrian=".$dataTable[$i]["reg_buffer_no_antrian"]."&id_reg=".$dataTable[$i]["id_reg"]."&nama=".$dataTable[$i]["nama"]."&tanggal=".$dataTable[$i]["reg_buffer_tanggal"]."&buffer_flag=".$dataTable[$i]["reg_buffer_flag"]."&id_perusahaan=".$dataTable[$i]["perusahaan_id"];
          $bpjs = "../sep/create.php?instalasi='I'&idbuffer=".$dataTable[$i]["reg_buffer_id"]."&cust_usr_id=".$dataTable[$i]["id_cust_usr"]."&id_poli=".$dataTable[$i]["id_poli"]."&jenis_pasien=".$dataTable[$i]["reg_buffer_jenis_pasien"]."&tipe=".$dataTable[$i]["reg_buffer_tipe_rawat"]."&dokter=".$dataTable[$i]["id_dokter"]."&no_antrian=".$dataTable[$i]["reg_buffer_no_antrian"]."&reg_id=".$dataTable[$i]["id_reg"]."&nama=".$dataTable[$i]["nama"]."&tanggal=".$dataTable[$i]["reg_buffer_no_antrian"];
          $cetak_barcode="cetak_reg.php?id_reg=".$regId."&id=".$custUsrId;
          $delPage = "registrasi_online.php?del=1&idbuffer=".$dataTable[$i]["reg_buffer_id"]."&id_reg=".$dataTable[$i]["id_reg"];

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_buffer_id"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;



          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rm"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
        
      
        

       
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


            if ($dataTable[$i]["reg_buffer_flag"]=="W") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] =  $dataTable[$i]["cust_usr_no_hp"];
          }
          elseif ($dataTable[$i]["reg_buffer_flag"]=="A") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["login_cust_phone_number"];
          }
       
          else{
             $tbContent[$i][$counter][TABLE_ISI] = " ";
          }
         
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


          // $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["login_cust_phone_number"];
          // $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          // $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";

          if (($dataTable[$i]["reg_buffer_batal"]=='y')) {
           $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["reg_buffer_when_update_batal"]);
          }
          else{
            $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["reg_buffer_when_update"]);
          }
          
          
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


          
          $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["reg_buffer_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_buffer_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;  
                                    
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
          
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          if ($dataTable[$i]["reg_buffer_flag"]=="W") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "Website";
          }
          elseif ($dataTable[$i]["reg_buffer_flag"]=="A") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "Android";
          }
          elseif ($dataTable[$i]["reg_buffer_flag"]=="S") {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "SMS";
          }
          else{
             $tbContent[$i][$counter][TABLE_ISI] = " ";
          }
         
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          if ($dataTable[$i]["is_daftar"]=='y') {
            # code...
             $tbContent[$i][$counter][TABLE_ISI] = "Sudah Verif";
          }
          elseif ($dataTable[$i]["reg_buffer_batal"]=='y') {
            # code...
            $tbContent[$i][$counter][TABLE_ISI] = "Batal Registrsi";
          }
          else{
            $tbContent[$i][$counter][TABLE_ISI] = "Belum Verif";
          }

          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;


          
//                  } //END JIKA SUDAH BAYAR   SEMENTARA DIHILANGKAN

          unset($sqlBayar);
          unset($dataBayar);
      } 
      
       

         
       
     
     
//      $colspan = count($tbHeader[0]);
   
   
//    $counter = 0;
                     
// 	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
//   $tbBottom[0][$counter][TABLE_COLSPAN] = 2;
//   $tbBottom[0][$counter][TABLE_ALIGN] = "center";
// 	$counter++;

// 	//$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
//   $tbBottom[0][$counter][TABLE_ALIGN] = "right";


                   
//      $tbBottom[0][$counter][TABLE_ISI] =$totalLk;
//      $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   
//      $tbBottom[0][$counter][TABLE_ALIGN] = "center";
//         $counter++;
   
//         //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
//      $tbBottom[0][$counter][TABLE_ALIGN] = "right";

                      
// 	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
//      $tbBottom[0][$counter][TABLE_ISI] =$totalPr;
//      $tbBottom[0][$counter][TABLE_ALIGN] = "center";
//         $counter++;
   
      
          
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
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	

     // $tableHeader = "Rekap 10 Besar Diagnosa";
     
  

  if ($_GET['excel']=="y"){
                      header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment; filename=laporan_pasien_kontrol.xls');?>
          
                    <br>
          
                    <table border="0" colspan="7" cellpadding="2" cellspacing="0" style="align:center" width="100%">     
                             <tr>
                                  <td width="70%" style="text-align:left;font-size:20px;font-family:sans-serif;font-weight:bold;" class="tablecontent"><?php echo $tableHeader;?></td>   
                             </tr>
                              
                    </table>
           
           <br>
          <br>  
          
          <?
                  }
          
          ?>
          
          
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
          <td>
          <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
          </td>
          </tr>
          </table> 
          