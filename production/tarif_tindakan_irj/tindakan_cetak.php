<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $PageJenisBiaya = "page_jenis_biaya.php";    

     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     
     /*if(!$auth->IsAllowed("man_tarif_tarif_tindakan_semua_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_tindakan_semua_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	  $backPage = "tindakan_view.php"; 
     
    	$isAllowedCreate=1;
    	$isAllowedUpdate=1;
    	$isAllowedDel=1;
      $tipeRawat["TA"] = "IRJ";
      $tipeRawat["TG"] = "IGD";
      $tipeRawat["TI"] = "IRNA";
     
     if($_POST["id_kategori_tindakan_header_instalasi"]) { 
       $idKategoriTindakanHeaderInstalasi = $_POST["id_kategori_tindakan_header_instalasi"];
       $_POST["id_kategori_tindakan_header_instalasi"] = $_POST["id_kategori_tindakan_header_instalasi"];
       }
     
     if($_GET["id_kategori_tindakan_header_instalasi"]) { 
       $idKategoriTindakanHeaderInstalasi = $_GET["id_kategori_tindakan_header_instalasi"];
       $_POST["id_kategori_tindakan_header_instalasi"] = $_GET["id_kategori_tindakan_header_instalasi"];
     }

     if($_POST["id_kategori_tindakan_header"]) { 
       $idKategoriTindakanHeader = $_POST["id_kategori_tindakan_header"];
       $_POST["id_kategori_tindakan_header"] = $_POST["id_kategori_tindakan_header"];
       }
     
     if($_GET["id_kategori_tindakan_header"]) { 
       $idKategoriTindakanHeader = $_GET["id_kategori_tindakan_header"];
       $_POST["id_kategori_tindakan_header"] = $_GET["id_kategori_tindakan_header"];
     }

     if($_POST["biaya_kategori"]) { 
       $idKategori = $_POST["biaya_kategori"];
       $_POST["biaya_kategori"] = $_POST["biaya_kategori"];
       }
     
     if($_GET["biaya_kategori"]) { 
       $idKategori = $_GET["biaya_kategori"];
       $_POST["biaya_kategori"] = $_GET["biaya_kategori"];
     }
     
     if($_POST["biaya_jenis"]) { 
       $biayaJenis = $_POST["biaya_jenis"];
       $_POST["biaya_jenis"] = $_POST["biaya_jenis"];
       }
     
     if($_GET["biaya_jenis"]) { 
       $biayaJenis = $_GET["biaya_jenis"];
       $_POST["biaya_jenis"] = $_GET["biaya_jenis"];
     }

     if($_GET['cetak']){
     	$_POST["btnCetak"] = $_GET['cetak'];
     } 

     if($_GET['excel']) {
     	$_POST["btnExcel"] = $_GET['excel'];
     }                                                          
     
     $excel = $_POST["btnExcel"];
     $cetak = $_POST["btnCetak"];
     
     $addPage = "tindakan_add.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"]."&tambah=1";
     $editPage = "tindakan_edit.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
     $detailPage = "tindakan_detail_view.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
     $thisPage = "tindakan_view.php";

     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$addPage.'\'"></button>';
		
		$sql_where[] = "1=1"; 
	    //if($in_nama) $sql_where[] = "UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
	    //$sql_where[] = " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
	    //if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
      if($idKategoriTindakanHeader && $idKategoriTindakanHeader!="--") $sql_where[] = "b.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$idKategoriTindakanHeader);
      if($idKategori && $idKategori!="--" ) $sql_where[] = "a.biaya_kategori = ".QuoteValue(DPE_CHAR,$idKategori);
      if($biayaJenis && $biayaJenis!="--") $sql_where[] = "a.biaya_jenis = ".QuoteValue(DPE_CHAR,$biayaJenis);    
      $sql_where = implode(" and ",$sql_where);

// QUERY PERKIRAAN NANTI DULU
//              f.no_prk as no_prk_beban, 
//              e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, 
//            left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
//              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
    
   	  $sql = "select h.*,a.biaya_nama, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, c.dep_nama, 
              d.kegiatan_kategori_nama,i.kelas_nama, g.kategori_tindakan_header_nama, j.jenis_nama
              from klinik.klinik_biaya a
              left join klinik.klinik_biaya_tarif h on a.biaya_id = h.id_biaya
              left join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join global.global_departemen c on c.dep_id = a.id_dep
              left join klinik.klinik_kegiatan_kategori_tindakan d on d.kegiatan_kategori_id = a.id_kegiatan_kategori 
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              left join klinik.klinik_kelas i on h.id_kelas = i.kelas_id
              left join global.global_jenis_pasien j on h.id_jenis_pasien  = j.jenis_id
              where ".$sql_where;
      $sql .= " order by g.kategori_tindakan_header_urut,b.kategori_urut,a.biaya_urut,i.kelas_nama";
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataTable = $dtaccess->FetchAll($rs);
      // echo $sql;
  	
		
	 	$counterHeader = 0;
     $tableHeader = "Manajemen - Tarif Tindakan";
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;  
	 
	   //$tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     //$counterHeader++;  
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     $counterHeader++;  
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Awal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Akhir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jasa Pelayanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jasa Sarana";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Pelaksana 1";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter Instruksi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
	 
	 
	  $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter Operator";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
	 
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
    // $counterHeader++; 
	 	 
//  	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Pendapatan";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++; 
	 
//	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Beban";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++; 
	  
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tambah";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++;
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++; 
	   
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
     //$counterHeader++; 
          
     //TOTAL HEADER TABLE
     $jumHeader= $counterHeader;
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
            $sql = 	"select * from klinik.klinik_biaya_split where id_split = '1' and id_biaya_tarif = ".QuoteValue(DPE_CHAR, $dataTable[$i]['biaya_tarif_id']);
            $Split1 = $dtaccess->Fetch($sql);
            // echo $sql;

            $sql =  "select * from klinik.klinik_biaya_split where id_split = '2' and id_biaya_tarif = ".QuoteValue(DPE_CHAR, $dataTable[$i]['biaya_tarif_id']);
            $Split2 = $dtaccess->Fetch($sql);


            // echo $Split1["bea_split_nominal"];
            // echo "Remun".$Remun1['biaya_remunerasi_nominal'];
	
	          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
	          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	          $counter++;
			  
//			      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kategori_tindakan_nama"];
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//	          $counter++;
  			 $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_nama"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
            
            //$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$tipeRawat[$dataTable[$i]["biaya_tarif_jenis"]];
	          //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          //$counter++;
            
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kelas_nama"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;

             $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++;
            
            $tbContent[$i][$counter][TABLE_ISI] = number_format($dataTable[$i]["biaya_total"], 0, ',', '.');
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
			  
            $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["biaya_tarif_tgl_awal"]);
	          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
	          $counter++;
            
            $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["biaya_tarif_tgl_akhir"]);
	          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
	          $counter++;
            
            // echo $sql;
            if ($Split1['bea_split_id']) { 
              $tbContent[$i][$counter][TABLE_ISI] = str_replace(',', '.', currency_format($Split1['bea_split_nominal']));
            }else{
              $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
            }
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++;

            if ($Split2['bea_split_id']) { 
              $tbContent[$i][$counter][TABLE_ISI] = str_replace(',', '.', currency_format($Split2['bea_split_nominal']));
            }else{ 
              $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
            }
            $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            $counter++;

            
            $sqlRemun1 = "select biaya_remunerasi_nominal from klinik.klinik_biaya_remunerasi where id_split = '1' and id_folio_posisi='10' and  id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id']);
            $Remun1 = $dtaccess->Fetch($sqlRemun1);

            $sqlRemun2 = "select biaya_remunerasi_nominal from klinik.klinik_biaya_remunerasi where id_split = '1' and id_folio_posisi='2' and id_biaya_tarif =".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id']);
            $Remun2 = $dtaccess->Fetch($sqlRemun2);


            $sqlRemun3 = "select biaya_remunerasi_nominal from klinik.klinik_biaya_remunerasi where id_split = '1' and id_folio_posisi='7' and id_biaya_tarif =".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id']);
            $Remun3 = $dtaccess->Fetch($sqlRemun3);

            $tbContent[$i][$counter][TABLE_ISI] =   str_replace(',', '.', currency_format($Remun2['biaya_remunerasi_nominal']));
            
            $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            $counter++;


            $tbContent[$i][$counter][TABLE_ISI] =   str_replace(',', '.', currency_format($Remun3['biaya_remunerasi_nominal']));
            
            $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            $counter++;


            $tbContent[$i][$counter][TABLE_ISI] = str_replace(',', '.', currency_format($Remun1['biaya_remunerasi_nominal']));
          
            $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            $counter++;

            // if ($Remun['biaya_remunerasi_id']) { 
            //   $tbContent[$i][$counter][TABLE_ISI] = 'y';
            // }else{ 
            //   $tbContent[$i][$counter][TABLE_ISI] = "n";
            // }
            // $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            // $counter++;            

                    
     }
		
		
		
		
     if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=tarif_all.xls');
     }
     
     if($_POST["btnCetak"]){
      $_x_mode = "cetak" ;      
     }

 // Data Kategori Tindakan Header Instalasi//
     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_instalasi[] = "a.klinik_kategori_tindakan_header_instalasi_id = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
     $sql_instalasi = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a where 1=1";
     if ($sql_where_instalasi) $sql_instalasi .= " and ".implode(" and ",$sql_where_instalasi);
     $sql_instalasi .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs_instalasi = $dtaccess->Execute($sql_instalasi);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->Fetch($rs_instalasi);

      // Data Kategori Tindakan Header //
     if($_POST['id_kategori_tindakan_header']) $sql_where_header[] = "a.kategori_tindakan_header_id = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->Fetch($rs_header);

     // Data Kategori Tindakan Header //
     
     if($_POST['biaya_kategori']) $sql_where_tindakan[] = "kategori_tindakan_id = ".QuoteValue(DPE_CHAR,$_POST['biaya_kategori']);
     $sql_tindakan = "select * from  klinik.klinik_kategori_tindakan where 1=1";
     if ($sql_where_tindakan) $sql_tindakan .= " and ".implode(" and ",$sql_where_tindakan);
     $sql_tindakan .= " order by kategori_urut asc";
     $rs_tindakan = $dtaccess->Execute($sql_tindakan);
     $dataKategoriTindakan = $dtaccess->Fetch($rs_tindakan);

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
   
   if(isset($_POST["btnCetak"])){
?>
<script language="JavaScript">

window.print();

</script>
<?php
}
?>
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
      <td width="30%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Kategori Tindakan Header Instalasi : <?php echo $dataKategoriTindakanHeaderInstalasi["klinik_kategori_tindakan_header_instalasi_nama"];?></td>
     <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">TARIF TINDAKAN</td>
    </tr>
    <tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Kategori Tindakan Header : <?php echo $dataKategoriTindakanHeader["kategori_tindakan_header_nama"];?></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Kategori Tindakan : <?php echo $dataKategoriTindakan["kategori_tindakan_nama"];?></td>
    </tr>

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
