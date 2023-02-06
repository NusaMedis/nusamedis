<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  // include_once($LIB."dateLib.php");
  require_once("sys/helper.php");
  // require_once($LIB."tampilan.php");
  require_once "sys/api.php";

 
  //INISIALISAI AWAL LIBRARY
  // $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $userId = $auth->GetUserId();
  $userLogin = $auth->GetUserData();    
  $bpjs = new Bpjs();

  $c = $bpjs->conf();
  // print_r($c);   
  
  #config
  $sql = "SELECT * FROM global.global_departemen WHERE dep_id = '9999999'";
  $conf = $dtaccess->Fetch($sql);

  #data pasien
  $sql = "SELECT reg_id,cust_usr_id, cust_usr_kode,cust_usr_no_hp, cust_usr_nama, cust_usr_tanggal_lahir, reg_tipe_rawat, cust_usr_jenis_kelamin, reg_kode_trans, poli_nama, d.*
      from klinik.klinik_registrasi a
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
      left join global.global_auth_poli c on a.id_poli = c.poli_id
      join klinik.klinik_sep d on a.reg_id = d.sep_reg_id";
  $sql .= " WHERE reg_id =".QuoteValue(DPE_CHAR, $_GET['reg_id']);
  $rs = $dtaccess->Execute($sql);
  $row = $dtaccess->Fetch($rs);
?>

<html>
  <head>
    <title>S E P</title>
  </head>
  <style>
    .judul{
      font-family: arial;
      font-size: 11px;
    }
    .isi{
      font-family: arial;
      font-size: 10px;
    }
  </style>
  <body onload="window.print(); window.close();">
    <table style="height: 6cm; width: 22cm;">
      <tbody> 
        <tr>
          <td rowspan="2"><img src="../gambar/bpjs.png" alt="BPJS" style="width: 150px; height: 30px;"></td>
          <td align="center" colspan="4" class="judul">SURAT ELIGIBILITAS PESERTA</td>
          <td rowspan="2" style="width: 4cm;">&nbsp;</td>
        </tr>
        <tr>
          <td align="center" colspan="4" class="judul"><?php echo $conf['dep_nama'] ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">No. Kode RS</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo $c['rs_code'];?></td>
          <td valign="center" width="14%" class="isi">Kelas RS</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><b><?php echo $conf['dep_tipe_rs'];?></b></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">No. SEP</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo  $row['no_sep'];?></td>
          <td valign="center" width="14%" class="isi">No. RM</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><b><?php echo $row['cust_usr_kode'];?><b></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Tgl. SEP</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo nice_date($row['tgl_sep'], 'd-m-Y') ?></td>
          <td valign="center" width="14%" class="isi">No. Reg</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php echo $row['reg_kode_trans'];?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">No. Kartu</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo  $row['no_kartu'];?></td>
          <td valign="center" width="14%" class="isi">Peserta</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php echo $row['jenis_peserta_txt']; ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Nama Peserta</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo $row['cust_usr_nama'];?></td>
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Tgl. Lahir</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo nice_date($row['cust_usr_tanggal_lahir'],'d-m-Y');?></td>
          <td valign="center" width="14%" class="isi">COB</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php echo ($row['cob'] == '1') ? 'Ya' : 'Tidak';?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Jns. Kelamin</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo ($row['cust_usr_jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');?></td>
          <td valign="center" width="14%" class="isi">Jns. Rawat</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php if($row['jns_pelayanan'] == '1') { echo "Rawat Inap"; } else { echo "Rawat Jalan"; } ?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Poli Tujuan</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo $row['poli_tujuan_txt'];?></td>
          <td valign="center" width="14%" class="isi">Kls. Rawat</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php echo $row['kls_rawat_txt'];?></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Asal Faskes</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo $row['rujukan_ppk_rujukan_txt'];?></td>
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Diagnosa Awal</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="35%" class="isi"><?php echo $row['diag_awal_txt'];?></td>
          <td align="center" class="isi" colspan="3">Pasien/ Keluarga Pasien</td>
        </tr>
        <tr>
          <td valign="center" width="14%" class="isi">Catatan</td>
          <td valign="center" width="1%" class="isi"> : </td>
          <td valign="center" width="20%" class="isi"><?php echo $row['catatan'];?></td>
          <td valign="center" colspan="3"></td>
        </tr>
        <tr>
          <td valign="center" colspan="6" class="isi"><sub><i>* Saya Menyetujui BPJS Kesehatan menggunakan informasi medis pasien jika diperlukan.</i></sub></td>
        </tr>
        <tr>
          <td valign="center" colspan="3" class="isi"><sub><i>* SEP bukan sebagai bukti penjaminan peserta.</i></sub></td>
          <td align="center" class="isi" colspan="3">______________________</td>
        </tr>
      </tbody>
    </table>
  </body>
</html>