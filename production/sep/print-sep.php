<?php
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."tampilan.php");     
require_once($LIB."currency.php");
require_once($LIB."dateLib.php");

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
  <title>S E P</title>
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
        <td align="left" colspan="6" class="judul">SURAT ELIGIBILITAS PESERTA</td>

      </tr>
      <tr>
        <td align="left" colspan="6" class="judul"><?=$konfigurasi['dep_nama'] ?></td>
      </tr>
      <tr>
        <td valign="left" width="14%" class="isi">No. SEP</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"><?= $data['no_sep'];?> </td>
        <!--   <td>&nbsp;</td> -->
        <td valign="left" width="14%" class="isi">Peserta</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="20%" class="isi"><?= str_replace("*", "'", $data['jenis_peserta_txt']) ?></td>
      </tr>
      <tr>
        <td valign="left" width="14%" class="isi">Tgl. SEP</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"><?=date_db($data['tgl_sep']) ?></td>
        <!-- <td>&nbsp;</td> -->
         <!--  <td valign="left" width="14%" class="isi">COB</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="20%" class="isi"><?= ($data['cob'] === 1) ? 'Ya' : 'Tidak' ?></td> -->
         <td valign="left" colspan="3"></td>
        </tr>
        <tr>
          <td valign="left" width="14%" class="isi">No. Kartu</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="35%" class="isi"><?= $data['cust_usr_no_jaminan'];?>   (MR <?= substr($data['cust_usr_kode'],2);?> ) </td>
          <!-- <td>&nbsp;</td> -->
          <td valign="left" colspan="3"></td>
          

        </tr>
        <tr>
          <td valign="left" width="14%" class="isi">Nama Peserta</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="35%" class="isi"><?php 
          if ($data['cust_usr_nama_txt'] == '' || $data['cust_usr_nama_txt'] == null) {
            # code...
            echo str_replace("*", "'", $data['cust_usr_nama']) ;
          }
          else{

           echo str_replace("*", "'", $data['cust_usr_nama_txt']);

         }
         ;?> </td>
         <!-- <td>&nbsp;</td> -->
         <td valign="left" width="14%" class="isi">Jns. Rawat</td>
         <td valign="left" width="1%" class="isi"> : </td>
         <td valign="left" width="20%" class="isi">
           <?php if ($data['reg_tipe_rawat']=="J") {
             # code...
            echo " R. Jalan ";
          } 
          elseif ($data['reg_tipe_rawat']=="G") {
                // code...
            echo "R. Darurat";
          }
          else {
            echo "R. Inap"; }?></td>
          </tr>
          <tr>
            <td valign="left" width="14%" class="isi">Tgl. Lahir</td>
            <td valign="left" width="1%" class="isi"> : </td>
            <td valign="left" width="35%" class="isi">

              <?php

              if ($data['cust_usr_tgllahir_txt'] == '' || $data['cust_usr_tgllahir_txt'] == null) {
                # code...
                echo date_db($data['cust_usr_tanggal_lahir']) ;
              }
              else{

               echo date_db($data['cust_usr_tgllahir_txt']);

             }


             ?> - Kelamin : <?=($data['cust_usr_jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');?>




           </td>
           <!-- <td>&nbsp;</td> -->
           <td valign="left" width="14%" class="isi">Jns. Kunjungan</td>
           <td valign="left" width="1%" class="isi"> : </td>
           <td valign="left" width="20%" class="isi">- Konsultasi dokter (pertama)</td>
         </tr>
         <tr>
          <td valign="left" width="14%" class="isi">No. Telepon</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="35%" class="isi"><?=$data['cust_usr_no_hp'];?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="left" width="14%" class="isi"></td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="20%" class="isi">- Prosedur tidak berkelnjutan</td>
        </tr>
        <tr>
          <td valign="left" width="14%" class="isi">Sub/Spesialis</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="20%" class="isi">
            <?php
            if ($data['jns_pelayanan'] == 1) {
            # code...
              echo " - ";
            }
            elseif ($data['jns_pelayanan'] == 2) {
              # code...
              echo $data['poli_nama'];
            }  ?></td>
            <!-- <td valign="left" width="35%" class="isi"><?=$data['poli_nama'];?></td> -->
            <!-- <td>&nbsp;</td> -->
            <td valign="left" width="14%" class="isi">Poli Perujuk</td>
            <td valign="left" width="1%" class="isi"> : </td>
            <td valign="left" width="20%" class="isi">- </td>
          </tr>
          <tr>
            <td valign="left" width="14%" class="isi">Dokter</td>
            <td valign="left" width="1%" class="isi"> : </td>
            <td>
             <?php 
             if ($data['nama_dpjp'] == '' || $data['nama_dpjp'] == null) {
            # code...
              echo $data['usr_name'];
            }
            else{

             echo $data['nama_dpjp'];

           }


           ?>
         </td>
         <!-- <td>&nbsp;</td> -->
         <td valign="left" width="14%" class="isi">Hak Kelas</td>
         <td valign="left" width="1%" class="isi"> : </td>
         <td valign="left" width="20%" class="isi">
          <?php
          if ($data['reg_tipe_rawat']=="J") {
             # code...
            echo " - ";
          } 
          else {echo $data['kls_rawat_txt']; }?>
        </td>

      </tr>
      <tr>
        <td valign="left" width="14%" class="isi">Faskes Perujuk</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"><?=$data['rujukan_ppk_rujukan_txt'];?></td>
        <!-- <td>&nbsp;</td> -->
        <td valign="left" width="14%" class="isi">Kls. Rawat</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="20%" class="isi">
         <?php if ($data['reg_tipe_rawat']=="J") {
             # code...
          echo " - ";
        } 
        else {echo "Kelas ". $data['kls_rawat']; }?></td>
      </tr>
      <tr>
        <td valign="left" width="14%" class="isi">Diagnosa Awal</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"><?= wordwrap($data['diag_awal_txt'],45,"<br>\n",TRUE);?></td>
        <td valign="left" width="14%" class="isi">penjamin</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="35%" class="isi"></td>
        
      </tr>
      <tr>
        <td valign="left" width="14%" class="isi">Catatan</td>
        <td valign="left" width="1%" class="isi"> : </td>
        <td valign="left" width="20%" class="isi"><?= $data['catatan'] ?></td>
        <!-- <td>&nbsp;</td> -->
        <td>&nbsp;</td>
        <td valign="left" class="isi" colspan="3">Pasien/ Keluarga Pasien</td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td valign="left" colspan="7" class="isi"><sub><i>* Saya Menyetujui BPJS Kesehatan menggunakan informasi medis pasien jika diperlukan.</i></sub></td>
      </tr>
      <tr>
        <td valign="left" colspan="4" class="isi"><sub><i>* SEP bukan sebagai bukti penjaminan peserta.</i></sub></td>
        <td valign="left" class="isi" colspan="4">______________________</td>
      </tr>
      <?php if ($data["reg_tipe_rawat"]=="I") {?>

        <td valign="left" colspan="4" class="isi"><sub><i>* Dengan menerbitkan SEP ini. Peserta rawat inap telah mendapatkan informasi dan menempati kelas rawat sesuai hak kelasnya (terkecuali penuh atau naik kelas sesuai aturan yang berlaku)</i></sub></td>



      <?php } ?>
      <tr>
        <td valign="left" colspan="7" class="isi"><sub><i>Cetakan ke - <?php echo $_GET['no']." ".date('d-F-Y H:i:s') ?><?php ?></i></sub></td>
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