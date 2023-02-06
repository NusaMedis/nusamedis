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

        if (!$_POST["klinik"]) $_POST["klinik"]=$depId;

     //pemanggilan tanggal hari ini 
     if(!$_GET["tgl_awal"]) $_GET["tgl_awal"] = date("d-m-Y");
     if(!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] = date("d-m-Y");
     
     if($_GET["tgl_awal"]) $sql_where[] = "a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     if($_GET["id_poli"]) $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
     
     if($_GET["tgl_awal"]) $sql_where2[] = "b.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where2[] = "b.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     if($_GET["id_poli"]) $sql_where2[] = "b.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
//     $sql_where[] = "1=1";
     
     $jmlHari = HitungHari(date_db($_GET["tgl_awal"]),date_db($_GET["tgl_akhir"]));

     $sql_where = implode(" and ",$sql_where);
     
     $sql = "select a.*,b.cust_usr_nama,b.cust_usr_kode from  klinik.klinik_registrasi a left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id";
     $sql .= " where a.reg_tipe_rawat='J' and reg_status not like 'A%' and ".$sql_where; 
     $sql .= "order by a.reg_when_update";
     $rs = $dtaccess->Execute($sql);
     $dataRegistrasi = $dtaccess->FetchAll($rs);

     $sql = "select * from  klinik.klinik_waktu_tunggu_status";
     $sql .= " where waktu_tunggu_tipe_rawat='J' and waktu_tunggu_status_flag='y' order by waktu_tunggu_status_urut"; 
     $rs = $dtaccess->Execute($sql);
     $dataStatus = $dtaccess->FetchAll($rs);

     $sql_where2 = implode(" and ",$sql_where2);

     $sql = "select a.id_reg, a.klinik_waktu_tunggu_when_create, klinik_waktu_tunggu_durasi, a.klinik_waktu_tunggu_durasi_detik,a.klinik_waktu_tunggu_status from 
             klinik.klinik_waktu_tunggu a
             left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
             left join klinik.klinik_waktu_tunggu_status c on c.waktu_tunggu_status_id = a.id_waktu_tunggu_status";
     $sql .= " where b.reg_tipe_rawat='J' and reg_status not like 'A%' and c.waktu_tunggu_tipe_rawat='J' and ".$sql_where2;         
     $sql .= "order by b.reg_when_update,c.waktu_tunggu_status_urut";
    // echo $sql;
     $rs = $dtaccess->Execute($sql); 
      while($row = $dtaccess->Fetch($rs)) {
          $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_create"] = $row["klinik_waktu_tunggu_when_create"];           
          $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi_detik"] = $row["klinik_waktu_tunggu_durasi_detik"];         
          $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi"] = $row["klinik_waktu_tunggu_durasi"];           

       }
       //var_dump($datawaktuTunggu);
     
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
     $counterHeader3 = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
     
 
     for($a=0,$b=count($dataStatus);$a<$b;$a++){

        $tbHeader[0][$counterHeader][TABLE_ISI] = $dataStatus[$a]["waktu_tunggu_status_nama"];
        if ($dataStatus[$a]["waktu_tunggu_status_urut"]==1) $jumSpan=1;  
        if ($dataStatus[$a]["waktu_tunggu_status_urut"]>1) $jumSpan=3;
        $tbHeader[0][$counterHeader][TABLE_COLSPAN] = $jumSpan;  
        $counterHeader++;  
 
         $tbHeader[1][$counterHeader2][TABLE_ISI] = "Waktu";
         $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
         $counterHeader2++;
     
         if ($dataStatus[$a]["waktu_tunggu_status_urut"]>1)
         { 
         $tbHeader[1][$counterHeader2][TABLE_ISI] = "Durasi";
         $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
         $counterHeader2++;
         }

         if ($dataStatus[$a]["waktu_tunggu_status_urut"]>1)
         { 
         $tbHeader[1][$counterHeader2][TABLE_ISI] = "Durasi Detik";
         $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
         $counterHeader2++;
         }
     }
     
 
     $tgl = date_db($_POST["tgl_awal"]);
 
     for($i=0,$counter=0,$n=count($dataRegistrasi);$i<$n;$i++,$counter=0){
       
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1).".";
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";            
          $counter++;                                         

          $tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";            
          $counter++;                                         
       
          $tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";            
          $counter++;                                         

       for($j=0,$m=count($dataStatus);$j<$m;$j++){   
          $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataStatus[$j]["waktu_tunggu_status_kode"]]["klinik_waktu_tunggu_when_create"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;

          if ($dataStatus[$j]["waktu_tunggu_status_urut"]>1)
          { 
          $tbContent[$i][$counter][TABLE_ISI] = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataStatus[$j]["waktu_tunggu_status_kode"]]["klinik_waktu_tunggu_durasi"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          }

          if ($dataStatus[$j]["waktu_tunggu_status_urut"]>1)
          { 
          $tbContent[$i][$counter][TABLE_ISI] = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataStatus[$j]["waktu_tunggu_status_kode"]]["klinik_waktu_tunggu_durasi_detik"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          }
       }
       //$tgl = DateAdd($tgl,1);
       //print_r($tgl);   
     }
     
     $colspan = count($tbHeader[0]);

            $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
       $rs = $dtaccess->Execute($sql);
       $konfigurasi = $dtaccess->Fetch($rs);
       
        if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
        if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
        //$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];
        $lokasi = $ROOT."/gambar/img_cfg";   
        
        if($konfigurasi["dep_logo"]!="n") {
        $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
        } elseif($konfigurasi["dep_logo"]=="n") { 
        $fotoName = $lokasi."/default.jpg"; 
        } else { $fotoName = $lokasi."/default.jpg"; }    
        
        //ambil nama poli
      $sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where poli_id = ".QuoteValue(DPE_CHAR,$_GET["id_poli"])   ; 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->Fetch($rs_edit);
  
?>
<script language="JavaScript">

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
      <td width="30%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $_GET["tgl_awal"];?> - <?php echo $_GET["tgl_akhir"];?></td>
      <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">
     LAPORAN WAKTU TUNGGU PASIEN <? if($_GET["tipe"]=='L'){ echo "LABORATORIUM";}elseif($_GET["tipe"]=='G'){echo "IGD";}elseif($_GET["tipe"]=='I'){echo "IRNA";}?> </td>
    </tr>
    <? if ($_GET["shift"]!="--") { ?>
    <!--<tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift : <?php echo $dataShift["shift_nama"];?></td>
    </tr>
    <? } ?> -->
    <? if ($_GET["id_poli"]!="--") { ?>
    <tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Klinik : <?php echo $dataPoli["poli_nama"];?></td>
    </tr>
    <? } ?>
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