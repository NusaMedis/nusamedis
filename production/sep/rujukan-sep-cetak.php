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

  $sql_pasien = "SELECT a.*, b.*, c.poli_nama, d.*, e.usr_name,f.cust_usr_nama_txt
    FROM klinik.klinik_registrasi a 
    LEFT JOIN global.global_customer_user b ON a.id_cust_usr = b.cust_usr_id 
    LEFT JOIN global.global_auth_poli c ON a.id_poli = c.poli_id 
    LEFT JOIN klinik.klinik_sep_rujukan d ON d.sep_rujukan_reg_id = a.reg_id
    LEFT JOIN global.global_auth_user e ON a.id_dokter = e.usr_id 
    LEFT JOIN klinik.klinik_sep f ON a.reg_id = f.sep_reg_id
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
          <td align="left" colspan="2" class="judul">SURAT RUJUKAN</td>
           <td align="left" colspan="2" class="judul">No. <?= $data['no_rujukan'];?></td>
          
        </tr>
        <tr>
          <td align="left" colspan="2" class="judul"><?=$konfigurasi['dep_nama'] ?></td>
          <td align="left" colspan="2" class="judul">Tgl. <?=date_db($data['tgl_rujukan']) ?></td>
        </tr>
        <tr>
         <!--  <td valign="left" width="14%" class="isi">No. SEP</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="35%" class="isi"><?= $data['no_sep'];?> </td> -->
        <!--   <td>&nbsp;</td> -->
          <td valign="left" width="14%" class="isi">Kepada Yth </td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="20%" class="isi"><?= $data['poli_rujukan_txt']."<br/>". str_replace("*", "'", $data['ppk_dirujuk_txt']) ?></td>
        </tr>

        <tr>
        <!--   <td valign="left" width="14%" class="isi">Tgl. SEP</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="35%" class="isi"><?=date_db($data['tgl_sep']) ?></td> -->
          <!-- <td>&nbsp;</td> -->
          <td colspan="3"></td>
          <td>==Rujukan Penuh==</td>
         
        </tr>
        <tr>
          <td colspan="3">Mohon Pemeriksaan dan Penanganan Lebih Lanjut :</td>
         <!--  <td valign="left" width="14%" class="isi">Jns. Rawat</td>
          <td valign="left" width="1%" class="isi"> : </td> -->
          <td valign="left" width="20%" class="isi"><?php
          if ($data['jns_pelayanan'] == 1) {
            # code...
            echo "R. Inap";
           }
           elseif ($data['jns_pelayanan'] == 2) {
              # code...
            echo "R. Jalan";
            }  ?></td>

        </tr>
        
        <tr>
          <td valign="left" width="14%" class="isi">No. Kartu</td>
          <td valign="left" width="1%" class="isi"> : </td>
          <td valign="left" width="35%" class="isi"><?= $data['cust_usr_no_jaminan'];?>   (MR <?= substr($data['cust_usr_kode'],2);?> ) </td>
          <!-- <td>&nbsp;</td> -->
        </tr>
      
        <tr>
        
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
          ;?> - Kelamin : <?=($data['cust_usr_jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');?></td>
          <!-- <td>&nbsp;</td> -->
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


            ?>
            
          </td>
          <!-- <td>&nbsp;</td> -->
         <tr>
            <td valign="left" width="14%" class="isi">Diagnosa</td>
            <td valign="left" width="1%" class="isi"> : </td>
            <td valign="left" width="20%" class="isi"><?= $data['diag_rujukan_txt']?></td>
            
          <td valign="left" class="isi" colspan="2">Mengetahui</td>
         </tr>
         <tr>
            <td valign="left" width="14%" class="isi">Keterangan</td>
            <td valign="left" width="1%" class="isi"> : </td>
            <td valign="left" width="20%" class="isi"><?= $data['catatan'] ?></td>
         </tr>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td valign="left" colspan="7" class="isi"><sub><i>Demikian atas bantuannya diucapkan terima kasih banyak.</i></sub></td>
        </tr>
         <tr>
          <?php
          $berlaku=date('Y-m-d', strtotime('+3 month', strtotime( date('Y-m-d')))); ?>
          <td valign="left" colspan="7" class="isi"><sub><i>* Rujukan Berlaku Sampai <?=date_db($berlaku) ?>.</i></sub></td>
        </tr>
        <tr>
          <td valign="left" colspan="7" class="isi"><sub><i>* Tgl Rencana Berkunjung <?=date_db($data['tgl_di_rujukan']) ?>.</i></sub></td>
        </tr>
        <tr >
          <td valign="left" colspan="3" class="isi"></td>
          <td valign="left" class="isi" colspan="2">______________________</td>
        </tr>

       
         <tr>
          <td valign="left" colspan="7" class="isi"><sub><i>Tanggal cetak - <?php echo date('d-F-Y H:i:s') ?><?php ?></i></sub></td>
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