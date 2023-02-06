<?php
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."expAJAX.php");
require_once($LIB."tampilan.php");
require_once($LIB."/currency.php");
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$userData = $auth->GetUserData();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
     //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
$depLowest = $auth->GetDepLowest();


$_x_mode = "New";
$thisPage = "ubah_tindakan.php?&id_pembayaran=".$_POST["id_pembayaran"]."&id_cust_usr=".$_POST["id_cust_usr"]."&cust_usr_jenis=".$_POST['cust_usr_jenis']."&jenis_pasien_lama=".$_POST['reg_jenis_pasien_lama']."&id_reg=".$_POST['id_reg'];
$editPage = "ubah_tindakan.php?";
$findPage = "pasien_find.php?";

if ($_GET["edit"]) {
  # code...
//   $sql = "select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
// $dataRegistrasi = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);

  $sql = "select a.rawatinap_id,c.id_poli,a.rawatinap_tanggal_masuk,c.id_dokter, a.rawatinap_waktu_masuk, c.reg_jenis_pasien, c.reg_id,c.reg_utama,c.reg_status,c.reg_tanggal,c.reg_waktu,c.id_cust_usr,c.id_pembayaran,
  d.cust_usr_kode_tampilan,d.cust_usr_nama,d.cust_usr_tanggal_lahir,d.cust_usr_alamat,e.jenis_nama,f.jkn_nama,m.jenis_kelas_nama,l.sebab_sakit_nama, b.rawat_inap_history_kelas_tujuan as reg_kelas,
  g.kelas_nama,h.kamar_nama,h.id_jenis_kelas,i.bed_kode, k.gedung_rawat_nama, perusahaan_nama, o.rawat_id
  from klinik.klinik_rawat_inap_history b
  left join klinik.klinik_rawatinap a on a.rawatinap_id = b.id_rawatinap
  left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
  left join global.global_customer_user d on c.id_cust_usr = d.cust_usr_id 
  left join global.global_jenis_pasien e on c.reg_jenis_pasien = e.jenis_id
  left join global.global_jkn f on c.reg_tipe_jkn = f.jkn_id
  left join klinik.klinik_kelas g on g.kelas_id = b.rawat_inap_history_kelas_tujuan
  left join klinik.klinik_kamar h on h.kamar_id = b.rawat_inap_history_kamar_tujuan
  left join klinik.klinik_kamar_bed i on i.bed_id = b.rawat_inap_history_bed_tujuan
  left join global.global_auth_poli j on c.id_poli = j.poli_id
  left join global.global_gedung_rawat k on h.id_gedung_rawat = k.gedung_rawat_id
  left join global.global_sebab_sakit l on l.sebab_sakit_id = c.reg_sebab_sakit
  left join klinik.klinik_jenis_kelas m on m.jenis_kelas_id = h.id_jenis_kelas
  left join global.global_perusahaan n on n.perusahaan_id = c.id_perusahaan
  left join klinik.klinik_perawatan o on c.reg_id = o.id_reg
  ";
  $sql .= " where d.cust_usr_kode <> '100' and b.rawat_inap_history_status != 'P' and c.reg_id = '$_GET[id_reg]' LIMIT 1";
  $rs = $dtaccess->Execute($sql);
  $dataRegistrasi = $dtaccess->Fetch($rs); 
  echo $sql.'<br/>';

  $sql = "select a.biaya_tarif_id, a.biaya_total, b.biaya_nama, id_jenis_pasien
  from   klinik.klinik_biaya_tarif a 
  left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
  left join klinik.klinik_kategori_tindakan c on b.biaya_kategori = c.kategori_tindakan_id
  left join klinik.klinik_kategori_tindakan_header d on d.kategori_tindakan_header_id = c. id_kategori_tindakan_header
  left join klinik.klinik_biaya_poli e on d.kategori_tindakan_header_id = e.id_kategori_tindakan_header
  ";


  $sql = "select a.biaya_tarif_id,a.id_biaya, a.biaya_total, b.biaya_nama, id_jenis_pasien
  from   klinik.klinik_biaya_tarif a 
  left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
  left join klinik.klinik_kategori_tindakan c on b.biaya_kategori = c.kategori_tindakan_id
  left join klinik.klinik_kategori_tindakan_header d on d.kategori_tindakan_header_id = c. id_kategori_tindakan_header
  left join klinik.klinik_biaya_poli e on d.kategori_tindakan_header_id = e.id_kategori_tindakan_header
  ";

    //$sql .=" where a.biaya_tarif_tgl_akhir >= ".QuoteValue(DPE_CHAR,date("Y-m-d"));
  $sql .=" and e.id_poli = ".QuoteValue(DPE_CHAR,$dataRegistrasi['id_poli']);

  $sql .=" where a.id_kelas = ".QuoteValue(DPE_CHAR,$dataRegistrasi['reg_kelas']);
  $sql .=" and id_jenis_kelas = ".QuoteValue(DPE_CHAR,$dataRegistrasi['id_jenis_kelas']);
  $sql .=" and id_jenis_pasien = ".QuoteValue(DPE_CHAR,$dataRegistrasi['reg_jenis_pasien']);
  $sql .=" and b.biaya_jenis_sem = 'PK' ";
  $sql .=" and a.id_biaya = ".QuoteValue(DPE_CHAR,$_GET['id_biaya']);
    //$sql .=" and b.biaya_jenis_sem is NULL";
   // $sql .=" and biaya_visite_id is not null";
  $sql .=" order by b.biaya_nama asc";
  echo $sql.'<br/>';

    // die($sql);

  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->Fetch($rs);
  echo $dataTable["id_biaya"];








  $sql = "select id_split, bea_split_nominal from klinik.klinik_biaya_split   
  where id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataTable['biaya_tarif_id']);
  $rs = $dtaccess->Execute($sql);
  echo $sql.'<br/>';

  $biayaSplit = $dtaccess->FetchAll($rs); 
  for($i=0,$n=count($biayaSplit);$i<$n;$i++){
    $sql = "update klinik.klinik_folio set  fol_jenis_pasien=".QuoteValue(DPE_NUMERIC,$dataRegistrasi["reg_jenis_pasien"]).",id_biaya=".QuoteValue(DPE_CHAR,$dataTable["id_biaya"]).",id_biaya_tarif=".QuoteValue(DPE_CHAR,$dataTable["biaya_tarif_id"])." where  fol_id=".QuoteValue(DPE_CHAR,$_GET["id_folio"]);
  $dtaccess->Execute($sql);
    echo $sql.'<br/>';

    $sql = "select fol_nominal,id_biaya_tarif,fol_jumlah,fol_id from klinik.klinik_folio where fol_id=".QuoteValue(DPE_CHAR,$_GET["id_folio"]);
    echo $sql.'<br/>';

    $dataFolio = $dtaccess->Fetch($sql);

    $hasilSatuan = $biayaSplit[$i]["bea_split_nominal"];
    echo $hasilSatuan.'<br/>';
    $hasil = ($hasilSatuan)*$dataFolio["fol_jumlah"];
    echo ($hasilSatuan)."* ".$dataFolio["fol_jumlah"].'<br/>';

    $sql="update klinik.klinik_folio_split set  folsplit_nominal=".QuoteValue(DPE_NUMERIC,$hasil).",folsplit_nominal_satuan=". QuoteValue(DPE_NUMERIC,$biayaSplit[$i]["bea"]*$hasilSatuan)." where id_fol=".QuoteValue(DPE_CHAR,$_GET["id_folio"])." and  id_split=".QuoteValue(DPE_CHAR,$biayaSplit[$i]["id_split"]);
    echo $sql.'<br/>';
  $dtaccess->Execute($sql);
  }

    //INSERT REMUNERASI PASIEN
  $sql = "select * from klinik.klinik_biaya_remunerasi where id_biaya_tarif =" . QuoteValue(DPE_CHAR, $dataFolio["id_biaya_tarif"]);
  $sql .= " and id_split = " . QuoteValue(DPE_CHAR,"1");
  $sql .= " and id_folio_posisi = " . QuoteValue(DPE_CHAR, "10");
  $rs = $dtaccess->Execute($sql);
  $remunDokter = $dtaccess->Fetch($rs);
  echo $sql.'<br/>';

  $sql = "select * from klinik.klinik_biaya_remunerasi where id_biaya_tarif =" . QuoteValue(DPE_CHAR, $dataFolio["id_biaya_tarif"]);
  $sql .= " and id_split = " . QuoteValue(DPE_CHAR,"1");
  $sql .= " and id_folio_posisi = " . QuoteValue(DPE_CHAR, "2");
  $rs = $dtaccess->Execute($sql);
  $remunPerawat = $dtaccess->Fetch($rs);
  echo $sql.'<br/>';
  //cari folio
  $sql = "select folsplit_id from klinik.klinik_folio_split 
  where id_fol = " . QuoteValue(DPE_CHAR, $_GET["id_folio"]) . " and id_split='1'";
  echo $sql.'<br/>';
  $rs = $dtaccess->Execute($sql);
  $folioSplit = $dtaccess->Fetch($rs);


  $sql="update klinik.klinik_folio_pelaksana set  fol_pelaksana_nominal=".QuoteValue(DPE_NUMERIC,$remunDokter["biaya_remunerasi_nominal"]).",id_fol_split=". QuoteValue(DPE_CHAR,$folioSplit["folsplit_id"])." where id_fol=".QuoteValue(DPE_CHAR,$_GET["id_folio"])." and  fol_pelaksana_tipe=10";
  echo $sql.'<br/>';
  $dtaccess->Execute($sql);

  $sql="update klinik.klinik_folio_pelaksana set  fol_pelaksana_nominal=".QuoteValue(DPE_NUMERIC,$remunPerawat["biaya_remunerasi_nominal"]).",id_fol_split=". QuoteValue(DPE_CHAR,$folioSplit["folsplit_id"])." where id_fol=".QuoteValue(DPE_CHAR,$_GET["id_folio"])." and  fol_pelaksana_tipe=2";
  echo $sql.'<br/>';
  $dtaccess->Execute($sql);

  $thisPage = "ubah_tindakan.php?&id_pembayaran=".$dataRegistrasi["id_pembayaran"]."&id_cust_usr=".$dataRegistrasi["id_cust_usr"]."&cust_usr_jenis=".$dataRegistrasi['reg_jenis_pasien']."&jenis_pasien_lama=".$_GET['jenis_pasien_lama']."&id_reg=".$dataRegistrasi['reg_id'];


      header("location:".$thisPage);
        exit();


}

        // if($_GET["id_cust_usr"]) $_POST["cust_usr_id"] = $enc->Decode($_GET["id_cust_usr"]);
$_POST["id_cust_usr"]=$_GET["id_cust_usr"];


$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode from global.global_customer_user a where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_GET["id_cust_usr"]);
$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
// echo $sql;
$_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];



if(!$_GET["edit"]) {

         // if($_GET['id_pembayaran']) {
  $table = new InoTable("table","100%","left");

  $sql = "select * from klinik.klinik_folio
  where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["id_pembayaran"])." and fol_jenis_sem notnull
  order by tindakan_tanggal desc";
          // echo $sql;
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
  $dataTable = $dtaccess->FetchAll($rs);

  $addPage = "perawatan_tambah.php?tambah=1&id=".$enc->Encode($dataTable[0]["cust_usr_id"]);

          //*-- config table ---*//
  $sql="select * from global.global_jenis_pasien where jenis_id =".QuoteValue(DPE_NUMBER,$_GET["cust_usr_jenis"]);
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
  $jenisUbah = $dtaccess->Fetch($rs);

  $sql="select * from global.global_jenis_pasien where jenis_id =".QuoteValue(DPE_NUMBER,$_GET["jenis_pasien_lama"]);
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
  $jenisLama = $dtaccess->Fetch($rs);

  $tableHeader = "&nbsp;Nama : ".$dataPasien["cust_usr_nama"]." ( ".$dataPasien["cust_usr_kode"]." )"." diubah dari ".$jenisLama["jenis_nama"]." ke ".$jenisUbah["jenis_nama"];



}




         // $isAllowedUpdate = $auth->IsAllowed("dok_edit_pemeriksaan",PRIV_UPDATE);

          // --- construct new table ---- //
         // $colspan = ($isAllowedUpdate) ? 2:1;


          //$tbHeader[0][1][TABLE_ISI] = '<a href="'.$addPage.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0"></a>';
          //$tbHeader[0][1][TABLE_WIDTH] = "30%";
          //$tbHeader[0][1][TABLE_CLASS] = "tablecontent-odd";
          //$tbHeader[0][1][TABLE_COLSPAN] = "2";

$tbHeader[0][0][TABLE_ISI] = $tableHeader;
$tbHeader[0][0][TABLE_WIDTH] = "80%";
$tbHeader[0][0][TABLE_COLSPAN] = "12";

$counterHeader = 0;

$tbHeader[1][$counterHeader][TABLE_ISI] = "Tanggal";
$tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;   



     /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Bayar";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;    
*/
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Nama Tindakan";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;    

     /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Anamnesa";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Diagnosa";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "16%"; 
         $counterHeader++;

         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Pemeriksaan Fisik";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Penunjang";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;
        
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Tindakan";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;   
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Terapi(Resep)";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++; */
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Nominal";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;


		 // if($isAllowedUpdate){
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Edit";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
         $counterHeader++;
              //}

           /*    $tbHeader[1][$counterHeader][TABLE_ISI] = "Hapus";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
               $counterHeader++; */


               for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){


                 if($dataTable[$i]["tindakan_tanggal"]) {
                   $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".format_date($dataTable[$i]["tindakan_tanggal"]);
                 } else {
                   $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".format_date($dataTable[$i]["tindakan_tanggal"])." (Belum Ada Data)";
                 }$tbContent[$i][$counter][TABLE_ALIGN] = "center";          
                 $counter++;

      /*         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_bayar"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;


			*/   



               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_nominal"]); 
               $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
               $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          // $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
               $counter++;



		  //if($isAllowedUpdate) {
               if ($dataFolio["cust_usr_jenis"] != $_GET["jenis_pasien_lama"]) {
                 # code...
                $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&edit=1&&id_folio='.$dataTable[$i]["fol_id"].'&id_biaya='.$dataTable[$i]["id_biaya"].'&id_reg='.$_GET["id_reg"].'&jenis_pasien_lama='.$_GET["jenis_pasien_lama"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/transfer.png" alt="Edit" title="Edit" border="0"></a>';               

              }
              else{
                $tbContent[$i][$counter][TABLE_ISI] =' ';

              }

              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;

               //}

           /*         $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'deleted=1&id_reg='.$dataTable[$i]["reg_id"].'&id_rawat='.$dataTable[$i]["rawat_id"].'&id_jadwal='.$dataTable[$i]["jadwal_id"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"  onclick="javascript: return hapus();"></a>';               
                    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                    $counter++; */
                  }

                  $colspan = $colspan;

                  $tbBottom[0][0][TABLE_ISI] .= '&nbsp;';
                  $tbBottom[0][0][TABLE_WIDTH] = "100%";
                  $tbBottom[0][0][TABLE_COLSPAN] = "12";


       //-----konfigurasi-----//
                  $sql = "select * from global.global_departemen";
                  $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
                  $rs = $dtaccess->Execute($sql);
                  $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;


                  ?>

                  <?php// echo $view->InitUpload(); ?>
                  <link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
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

                    function Kembali() {

                      document.location.href='pasien_view.php';
                    }

                  </script>


                  <?php// echo $view->InitThickBox(); ?>
                  <script language="JavaScript">

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan menghapus data ini???'));
  else return false;
}

</script>

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
                  <h2>Tindakan Pasien</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
                    <?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
                      <font color="red"><strong>No. RM Tidak Ditemukan</strong></font>
                    <?php } ?>

                    <script>document.frmFind.cust_usr_kode.focus();</script>

                  </form>

                  <?php if($dataPasien["cust_usr_id"] || $_POST["btnAdd"]) { ?>
                    <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckSimpan(this)">
                      <table width="100%" align="center">
<!--<tr><td align="right">
     <a href="<?php echo $addPage; ?>" style="text-decoration:none"><input type="button" value="Tambah" class="submit" alt="Tambah" title="Tambah" border="0"></a>
   </td></tr>-->
   <tr><td>
    <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
  </td></tr>
  <tr>
    <td>
      <input type="button" name="btnBack" id="btnBack" value="Kembali" class="submit" onClick="javascript: Kembali();" />
    </td>
  </tr>
</table>
</form>
<?php } ?>

</div>

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
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
  <a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
  <a rel="sepur" href="<?php echo $ROOT;?>demo/edit_pemeriksaan.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
  var gb = document.getElementById("gb");
  gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>


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