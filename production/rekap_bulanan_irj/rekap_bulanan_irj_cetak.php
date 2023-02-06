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
	   $userId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $thisPage = "report_pasien.php";
     $skr = date("d-m-Y");
     //$_POST["klinik"]=$depId;

     if (!$_GET["klinik"]) $_POST["klinik"]=$depId;
     else $_POST["klinik"]= $_GET["klinik"];

     //pemanggilan tanggal hari ini jika gk ada get tgl awal n akhir 
     if(!$_GET["tgl_awal"]) $_GET["tgl_awal"]  = $skr;
     if(!$_GET["tgl_akhir"]) $_GET["tgl_akhir"]  = $skr;

     //untuk mencari tanggal
    // if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_GET["klinik"]."%");
     if($_GET["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     
     $jmlHari = HitungHari(date_db($_GET["tgl_awal"]),date_db($_GET["tgl_akhir"]));

     if($_GET["id_poli"]){
     $sql = "select * from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
     } else {
     $sql = "select * from global.global_auth_poli where poli_tipe='J' order by poli_id asc";
     }
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
     
     if($_GET["id_jenis"]){
     $sql = "select * from global.global_jenis_pasien where jenis_id=".QuoteValue(DPE_NUMERIC,$_GET["id_jenis"]);
     } else {
     $sql = "select * from global.global_jenis_pasien where jenis_flag='y' order by jenis_id asc";
     }
     $rs = $dtaccess->Execute($sql);
     $dataJenisPasien = $dtaccess->FetchAll($rs);
     
     $sql = "select count(reg_id) as total, a.reg_tanggal, a.reg_jenis_pasien, a.id_poli from klinik.klinik_registrasi a
            where reg_status_pasien = 'B' group by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli
            order by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli";
     $rs = $dtaccess->Execute($sql); 
  	 while($row = $dtaccess->Fetch($rs)) {
  		$dataBaru[$row["reg_tanggal"]][$row["reg_jenis_pasien"]][$row["id_poli"]] = $row["total"];		  
       }
       
     $sql = "select count(reg_id) as total, a.reg_tanggal, a.reg_jenis_pasien, a.id_poli from klinik.klinik_registrasi a
            where reg_status_pasien = 'L' group by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli
            order by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli";
     $rs = $dtaccess->Execute($sql); 
  	 while($row2 = $dtaccess->Fetch($rs)) {
  		$dataLama[$row2["reg_tanggal"]][$row2["reg_jenis_pasien"]][$row2["id_poli"]] = $row2["total"];		  
       }
       
     $tableHeader = "Rekap Bulanan IRJ";
  
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
     $counterHeader3 = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "3";
     $counterHeader++;
     
     for($a=0,$b=count($dataPoli);$a<$b;$a++){

        $tbHeader[0][$counterHeader][TABLE_ISI] = $dataPoli[$a]["poli_nama"];  
        $tbHeader[0][$counterHeader][TABLE_COLSPAN] = count($dataJenisPasien)*2;     
        $counterHeader++;  
        
      for($p=0,$q=count($dataJenisPasien);$p<$q;$p++){
    		$tbHeader[1][$counterHeader2][TABLE_ISI] = $dataJenisPasien[$p]["jenis_nama"];
        $tbHeader[1][$counterHeader2][TABLE_COLSPAN] = "2";          
        $counterHeader2++;		        
        
        $tbHeader[2][$counterHeader3][TABLE_ISI] = "Baru";
        $counterHeader3++;
        
        $tbHeader[2][$counterHeader3][TABLE_ISI] = "Lama";
        $counterHeader3++;
        
      }    
     }
     
     $tgl = date_db($_GET["tgl_awal"]);                 
     for($i=0,$counter=0,$n=$jmlHari;$i<$n;$i++,$counter=0){
       
          $tglTable = explode("-",$tgl);
          $tbContent[$i][$counter][TABLE_ISI] = $tglTable[2];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";            
          $counter++;                                         
       
       for($j=0,$m=count($dataPoli);$j<$m;$j++){   
        for($k=0,$o=count($dataJenisPasien);$k<$o;$k++){
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataBaru[$tgl][$dataJenisPasien[$k]["jenis_id"]][$dataPoli[$j]["poli_id"]]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";          
          $counter++;
          //print_r($dataBaru);
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataLama[$tgl][$dataJenisPasien[$k]["jenis_id"]][$dataPoli[$j]["poli_id"]]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";          
          $counter++;
          
        }
       }
       $tgl = DateAdd($tgl,1);
       //print_r($tgl);   
     }
     
     $colspan = count($tbHeader[0]);

       //ambil nama poli
  $sql = "select * from global.global_auth_poli where id_dep = ".QuoteValue(DPE_CHAR,$_GET["klinik"])   ; 
  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $poli = $dtaccess->FetchAll($rs_edit);
  
     // ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs); 
     
     //Data Klinik
          $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
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
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      //$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];
      $lokasi = $ROOT."/gambar/img_cfg";   
      
        if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }    
  
     // cari nama perusahaan --
     $sql = "select * from klinik.klinik_kat_icd
            where kat_icd_id = '".$_GET["id_kat_icd"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $NamaKatIcd = $dtaccess->Fetch($rs); 
     
     $sql = "select * from global.global_rl_4_kat a
             left join global.global_rl_4_jenis b on a.id_jenis_rl_4 = b.rl_4_jenis_id
             where rl_4_kat_id='".$_GET["id_kat_rl_4"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $NamaKatRl4 = $dtaccess->Fetch($rs);

?>
<script language="JavaScript">

window.print();

</script>

<<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table border="0" cellpadding="2" rowspan="3" cellspacing="0" align="center">
    <tr>
      <td style="text-align:center;font-size:16px;font-family:times new roman;font-weight:bold;" class="tablecontent">
       Rekap Bulanan IRJ<br />
       <?php// echo $depNama." - ".$konfigurasi["dep_kop_surat_1"]."&nbsp;".$konfigurasi["dep_kop_surat_2"];?>
      </td>
    </tr>
<TR>
    <TD align="center"><b>Tanggal : <?php echo ($_GET["tgl_awal"])."</b>&nbsp;s/d&nbsp;<b>".($_GET["tgl_akhir"])."</b>";?></TD>
</TR>
  </table>
 <br><br> 
 <b>Nama Rumah Sakit : <?php echo $depNama;?></b> 
<table width="100%" border="0" cellpadding="0" cellspacing="0">   

<tr>
<td COLSPAN="2">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td>
</tr>
</table>  
