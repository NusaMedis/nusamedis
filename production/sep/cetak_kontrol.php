<?php
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."tampilan.php");     
require_once($LIB."currency.php");
require_once($LIB."dateLib.php");
  $custom_script[] = "cetak_kontrol.js";
$dtaccess = new DataAccess();
$enc = new textEncrypt();                                 
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  // $depId = $auth->GetDepId();
$depNama = $auth->GetDepNama();
$depId = '9999999';

  #config
$sql = "SELECT * FROM global.global_departemen WHERE dep_id = '$depId'";
$konfigurasi = $dtaccess->Fetch($sql);

$sql_pasien = "SELECT a.*, b.*, c.poli_nama, d.*, e.usr_name
FROM klinik.klinik_registrasi a 
LEFT JOIN global.global_customer_user b ON a.id_cust_usr = b.cust_usr_id 
LEFT JOIN global.global_auth_poli c ON a.id_poli = c.poli_id 
LEFT JOIN klinik.klinik_sep d ON a.reg_id = d.sep_reg_id
LEFT JOIN global.global_auth_user e ON a.id_dokter = e.usr_id 
WHERE reg_id = ".QuoteValue(DPE_CHAR, $_GET['reg_id']);
$data =  $dtaccess->Fetch($sql_pasien);
  // echo "<pre>";
  // print_r($data);
  // echo "</pre>";
  // die;


?>

<html>
<head>
  <title>Cetak Surat Kontrol</title>
</head>

<style type = "text/css">
@page {
 size: 21cm 13cm;  /* width height */
 margin: 0.5cm
}
</style>
<style type="text/css">
.judul{
  font-family: arial;
  font-size: 16px;
}
.isi{
  font-family: arial;
  font-size: 14px;
}
</style>
<body onload="window.print(); //window.close();">
  <table width="100%" border='0'>
    <tbody> 
      <tr>
        <td rowspan="2"><img src="../gambar/bpjs.png" alt="BPJS" style="width: 150px; height: 30px;"></td>
        <td align="left" colspan="6" class="judul">SURAT RENCANA KONTROL<br/>
        RSIA MUSLIMAT JOMBANG</td>
        <td align="right" colspan="6" class="judul"><p id="nosep"></p></td>

      </tr>
      <tr>
        <td align="left" colspan="6" class="judul">Mohon Pemeriksaan dan Penanganan Lebih Lanjut :</td>
      </tr>
      <tr>
        <td valign="left" width="14%" class="isi">No. Kartu</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"><p id="noKartu"></p> </td>
        <!--   <td>&nbsp;</td> -->

      </tr>
      <tr>
       <td valign="left" width="14%" class="isi">Peserta</td>
       <td valign="left" width="1%" class="isi"> : </td>
       <td valign="left" width="20%" class="isi"><p id="nama"></p></td>
       

        </tr>
        <tr>
         <td valign="left" width="14%" class="isi">Tgl. Lahir</td>
         <td valign="left" width="1%" class="isi"> : </td>
         <td valign="left" width="35%" class="isi"><p id="tgllahir"></p></td>
       </tr>
       <tr>
        <td valign="left" width="14%" class="isi">Diagnosa</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"><p id="diagnosa"></p></td>
        <!-- <td>&nbsp;</td> -->
       

    </tr>
    <tr>
      <td valign="left" width="14%" class="isi">Rencana Kontrol</td>
      <td valign="left" width="1%" class="isi"> : </td>
      <td valign="left" width="35%" class="isi"><p id="rencana"></p></td>
     <!-- <td>&nbsp;</td> -->
     
    </tr>

   Demikian atas bantuannya,diucapkan banyak terima kasih.

 <tr>
  <td valign="left" width="14%" class="isi"></td>
  <td valign="left" width="1%" class="isi">  </td>
  <td valign="left" width="35%" class="isi"></td>
  <!-- <td>&nbsp;</td> -->
  <td valign="left" colspan="3"></td>
</tr>
<tr>
  <td valign="left" width="14%" class="isi"></td>
  <td valign="left" width="1%" class="isi"> : </td>
  <td valign="left" width="35%" class="isi"></td>
  <td>&nbsp;</td>
  <td valign="left" class="isi" colspan="3">Mengetahui DPJP</td>
</tr>

<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
</tr>
<tr>
  <td valign="left" colspan="7" class="isi"><sub><i></i></sub></td>
</tr>
<tr>
  <td valign="left" colspan="4" class="isi"><sub><i></i></sub></td>
  <td valign="left" class="isi" colspan="4">______________________</td>
</tr>

<tr>
  <td valign="left" colspan="7" class="isi"><sub id="entri"><i></i></sub><sub><i>Tgl Cetak <?php date('d-F-Y H:i:s') ?></i></sub></td>
</tr>
</tbody>
</table>
</body>
</html>

<?php
// $filename = $data['no_sep'].".pdf";
// $content = ob_get_clean();
//  require_once(dirname(__FILE__).'/html2pdf/html2pdf.class.php');
//  try
//  {
//   //$html2pdf = new HTML2PDF('P','A4','en');
//   $html2pdf = new HTML2PDF('L', array(210,140), 'en');
//   $html2pdf->setDefaultFont('Arial');
//   $html2pdf->writeHTML($content);
//   $html2pdf->Output($filename);
//  }
//  catch(HTML2PDF_exception $e) { echo $e; }
// header('Content-Type: application/pdf');
?>