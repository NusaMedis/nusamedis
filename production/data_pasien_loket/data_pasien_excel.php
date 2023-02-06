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

/** SQL DATA PASIEN*/
$sql = "select a.cust_usr_id,a.cust_usr_kode,a.cust_usr_nama,a.cust_usr_alamat,a.cust_usr_tanggal_lahir from global.global_customer_user a";
$sql .= " where 1=1";

$sql .= " order by a.cust_usr_kode desc";
$rs = $dtaccess->Execute($sql,DB_SCHEMA);
$dataTable = $dtaccess->FetchAll($rs);

     
     
     // --- construct new table ---- //
   
     $tableHeader = "&nbsp;Data Pasien";
  
     // --- construct new table ---- //
  
    
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "left";   

     $counterHeader++;

    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     ;
     $counterHeader++;

          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     ;
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     ;
     $counterHeader++;

     

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     ;
     $counterHeader++;

      $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
     ;
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "60%";
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "5";
     $counterHeader++;


   $tbHeader[0][$counterHeader][TABLE_ISI] = "Penanggung Jawab";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "60%";
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "5";
     $counterHeader++;

  



     //for($i=0,$counter=0,$n=count($dataPasienJml);$i<$n;$i++,$counter=0){
     //$_GET["baris"] = 100;
     $no = 1;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

         $tanggal = new DateTime($dataTable[$i]["cust_usr_tanggal_lahir"]);

        // tanggal hari ini
        $today = new DateTime($dataTable[$i]["reg_tanggal"]);

        // tahun
        $y = $today->diff($tanggal)->y;

        // bulan
        $m = $today->diff($tanggal)->m;

        // hari
        $d = $today->diff($tanggal)->d;


    
         
            $tbContent[$i][$counter][TABLE_ISI] = $no;             
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan;
            $counter++;
            $no++;

    

              
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
                    
            $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";    
                
            $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
                    
            $counter++;




            if($dataTable[$i]["cust_usr_jenis_kelamin"]=='P'){
                $tbContent[$i][$counter][TABLE_ISI] = "Perempuan";
               
            }
            elseif ($dataTable[$i]["cust_usr_jenis_kelamin"]=='L'){
                $tbContent[$i][$counter][TABLE_ISI] = "Laki-laki";
              
            }
            $tbContent[$i][$counter][ROWSPAN] = $dataSpan;
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
            $counter++;

            
            $tbContent[$i][$counter][TABLE_ISI] = $y." tahun ".$m." bulan ".$d." hari ";
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";    
                
            $counter++;


            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_tanggal_lahir"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
                    
            $counter++;

            
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_penanggung_jawab"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
                    
            $counter++;



            //  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
            // $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            //         
            // $counter++;
        








       

         
       
     }
     
//      $colspan = count($tbHeader[0]);
   
   
//    $counter = 0;
                     
//  $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
//   $tbBottom[0][$counter][TABLE_COLSPAN] = 2;
//   $tbBottom[0][$counter][TABLE_ALIGN] = "center";
//  $counter++;

//  //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
//   $tbBottom[0][$counter][TABLE_ALIGN] = "right";


                   
//      $tbBottom[0][$counter][TABLE_ISI] =$totalLk;
//      $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   
//      $tbBottom[0][$counter][TABLE_ALIGN] = "center";
//         $counter++;
   
//         //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
//      $tbBottom[0][$counter][TABLE_ALIGN] = "right";

                      
//  $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
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
                    header('Content-Disposition: attachment; filename=DataPasien.xls');?>
          
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
          