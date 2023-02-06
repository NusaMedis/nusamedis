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
  define('_MPDF_PATH','mpdf/');
include(_MPDF_PATH . "mpdf.php");
  $mpdf=new mPDF('utf-8', 'A4');
  $mpdf->AddPage('L');

  
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
  <body>
    <table align="center" width="100%" border='0'>
      <tbody> 
        <tr>
          <td rowspan="2"><img src="../gambar/bpjs.png" alt="BPJS" style="width: 150px; height: 30px;"></td>
          <td align="center" colspan="6" class="judul">SURAT ELIGIBILITAS PESERTA</td>
          
        </tr>
        <tr>
          <td align="center" colspan="6" class="judul"><?=$konfigurasi['dep_nama'] ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">No. SEP</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?= $data['no_sep'];?> </td>
        <!--   <td>&nbsp;</td> -->
          <td valign="center" width="14%" class="isi">Peserta</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?= $data['jenis_peserta_txt']?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Tgl. SEP</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?=date_db($data['reg_tanggal']) ?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" width="14%" class="isi">COB</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?= ($data['cob'] === 1) ? 'Ya' : 'Tidak' ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">No. Kartu</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?= $data['cust_usr_no_jaminan'];?>   (MR <?= substr($data['cust_usr_kode'],2);?> ) </td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" width="14%" class="isi">Jns. Rawat</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php echo ($data['jns_pelayanan'] === 1) ? 'R. Inap' : 'R. Jalan'; ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Nama Peserta</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="50%" class="isi"><?=$data['cust_usr_nama'];?> - Kelamin : <?=($data['cust_usr_jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" width="14%" class="isi">Kls. Rawat</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php
          if ($data['reg_tipe_rawat']=="J") {
             # code...
            echo " - ";
           } 
           else {echo $data['kls_rawat_txt']; }?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Tgl. Lahir</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?=date_db($data['cust_usr_tanggal_lahir']);?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" width="14%" class="isi">Penjamin</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?= $data['laka_penjamin'] ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">No. Telepon</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?=$data['cust_usr_no_hp'];?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Sub/Spesialis</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?=$data['poli_nama'];?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">DPJP Yg Melayani</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?=$data['usr_name'];?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Faskes Perujuk</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?=$data['rujukan_ppk_rujukan_txt'];?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Diagnosa Awal</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?= wordwrap($data['diag_awal_txt'],45,"<br>\n",TRUE);?></td>
          <td>&nbsp;</td>
          <td valign="center" class="isi" colspan="3">Pasien/ Keluarga Pasien</td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Catatan</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?= $data['catatan'] ?></td>
          <!-- <td>&nbsp;</td> -->
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td valign="center" colspan="7" class="isi"><sub><i>* Saya Menyetujui BPJS Kesehatan menggunakan informasi medis pasien jika diperlukan.</i></sub></td>
        </tr>
        <tr>
          <td valign="center" colspan="4" class="isi"><sub><i>* SEP bukan sebagai bukti penjaminan peserta.</i></sub></td>
          <td valign="center" class="isi" colspan="4">______________________</td>
        </tr>
        <?php if ($data["reg_tipe_rawat"]=="I") {?>

            <td valign="center" colspan="4" class="isi"><sub><i>* Dengan menerbitkan SEP ini. Peserta rawat inap telah mendapatkan informasi dan menempati kelas rawat sesuai hak kelasnya (terkecuali penuh atau naik kelas sesuai aturan yang berlaku)</i></sub></td>


          
          <?php } ?>
         <tr>
          <td valign="center" colspan="7" class="isi"><sub><i>Cetakan ke - <?php echo $_GET['no']." ".date('d-F-Y H:i:s') ?><?php ?></i></sub></td>
        </tr>
      </tbody>
    </table>
  </body>
</html>

<?php
// $filename = $data['no_sep'].".pdf";
// $content = ob_get_clean();
//  require __DIR__.'/html2pdf/vendor/autoload.php';
//  try
//  {
//   //$html2pdf = new HTML2PDF('P','A4','en');
//   $html2pdf = new Html2Pdf('L', array(210,140), 'en');
//   $html2pdf = new Html2Pdf('P','A4','fr', true, 'UTF-8', array(15, 15, 15, 15), false); 
//   $html2pdf->writeHTML($content);
//   $html2pdf->output();
//  }
//  catch(HTML2PDF_exception $e) { echo $e; }
// header('Content-Type: application/pdf');
?>
<?php
$html = ob_get_contents();
ob_end_clean();

$mpdf->WriteHTML(utf8_encode($html));
$mpdf->Output("".$data['no_sep']."-".date('d-F-Y H:i:s').".pdf" ,'D');
$db1->close();
?>