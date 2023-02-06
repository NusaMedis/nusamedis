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

<style>

body {
     font-family:      Verdana, Arial, Helvetica, sans-serif;
     font-size:        10px;
     margin: 5px;
     margin-top:		  0px;
     margin-left:	  0px;
}

table {
     font-family:    Verdana, Arial, Helvetica, sans-serif;
     font-size:      12px;
	padding:0px;
	border-color:#000000;
	border-collapse : collapse;
	border-style:solid;
	}
</style>
</head>

<body onload="window.print(); window.close();">
  <table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
    	<td class="tablecontent" align ="center" width="99%"><font size="2">SURAT ELIGIBILITAS PESERTA</td>
    </tr>
    <tr>
    	<td class="tablecontent" align ="center"><font size="2"><?php echo $conf['dep_nama'] ?></font></td> 
    </tr>                                                    
  </table>
	<br>  
  <table align="left" width="50%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
    <tr>
      	<td align="left" width="18%">No. Kode RS</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="34%" ><?php echo $c['rs_code'];?></td>
    </tr>
	<tr>
	    <td align="left" width="18%">No. SEP</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="34%" ><?php echo  $row['no_sep'];?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Tgl. SEP</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo nice_date($row['tgl_sep'], 'd-m-Y') ?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">No. Kartu </td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo  $row['no_kartu'];?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Nama Peserta</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo $row['cust_usr_nama'];?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Tgl. Lahir </td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo nice_date($row['cust_usr_tanggal_lahir'],'d-m-Y');?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Jns. Kelamin </td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo ($row['cust_usr_jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Poli Tujuan </td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo $row['poli_tujuan_txt'];?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Asal Faskes</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo $row['rujukan_ppk_rujukan_txt'];?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Diagnosa Awal</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%"><?php echo $row['diag_awal_txt'];?></td>
    </tr>
    <tr>
	    <td align="left" width="18%">Catatan</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left"width="32%"><?php echo $row['catatan'];?></td>
    </tr>
  </table>
  <table align="right" width="50%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
	    <td align="left" colspan='2' width="18%">Kelas RS</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%" colspan='2'><b><?php echo $conf['dep_tipe_rs'];?><b></td>
    </tr>
	<tr>
	    <td align="left" colspan='2' width="18%">No. RM</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%" colspan='2'><b><?php echo $row['cust_usr_kode'];?><b></td>
    </tr>
    <tr>
	    <td align="left" colspan='2' width="18%">No. Reg</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" width="32%" colspan='2'><?php echo $row['reg_kode_trans'];?></td> 
    </tr>
    <tr>
	    <td align="left" colspan='2' width="18%">Peserta</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" colspan='2' width="32%"><?php echo $row['jenis_peserta_txt']; ?></td>
    </tr>
    <tr>
    	<td align="left" >&nbsp;</td>  
    </tr>
    <tr>
    	<td align="left" colspan='2' width="18%">COB</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" colspan='2' width="32%"><?php echo ($row['cob'] == '1') ? 'Ya' : 'Tidak';?></td>
    </tr>
    <tr>
	    <td align="left" colspan='2' width="18%">Jns. Rawat</td>  
	    <td align="center" width="1%">:</td>
	    <td align="left" colspan='2' width="32%">
		<?php if($row['jns_pelayanan'] == '1')  {
				echo "Rawat Inap";
			  } else {
				echo "Rawat Jalan";
			  }	 
		?>
	</td>
    </tr>
    <tr>
      <td align="left" colspan='2'width="18%">Kls. Rawat</td>  
    <td align="center" width="1%">:</td>
    <td align="left" colspan='2' width="32%"><?php echo $row['kls_rawat_txt'];?></td>
    </tr>
    <tr>
      <td align="left"
      width="18%">&nbsp;</td>  
    </tr>
    <tr>
          <td align="center" width='50%' colspan ='4'><!--Petugas BPJS Kesehatan--></td>
          <td width='50%' align="center">Pasien/ Keluarga Pasien</td>
    </tr>
    <tr>
      <td align="left"colspan='4'>&nbsp;</td>  
    </tr>
</table>
<table width="100%" border='0'>    <tr>
      <td align="left"><sub><i>* Saya Menyetujui BPJS Kesehatan menggunakan informasi medis </i></sub></td>  
    <td></td>
    </tr>
<tr>
      <td align="left"><sub><i>&nbsp;&nbsp;&nbsp;Pasien jika diperlukan.</i></sub></td>  
    <td></td>
    </tr>
    <tr>
      <td align="left"><sub><i>* SEP bukan sebagai bukti penjaminan peserta.</i></sub></td>  
    <td align="center" width="25%">___________________</td>
    </tr>
  </table> 
  <table align="left" width="50%" border='0'>     <tr>
      <td align="left" colspan='2'>Cetakan Ke 1</td>    <td align="center" width="3%">&nbsp;</td>  
</tr></table>
<?php if($row['reg_tipe_rawat'] == 'J'): ?>
<br>
<table border='0' width="100%">
<tr>
<td align="center" colspan='4'><br>RESUME MEDIS</td>
 </tr>
 <tr>
<td align="center" colspan='4'>RAWAT JALAN - IGD</td>
 </tr>
 <tr height="30px">
<td align="left" >Diagnosa Utama</td><td align="left">: ....................................................................</td><td align="left"> Kode ICD-10</td><td align="left"> : &nbsp;</td>
 </tr>
 <tr height="30px">
<td align="left" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr>
 <tr height="30px">
<td align="left" >Diagnosa Sekunder</td><td align="left">: ....................................................................</td><td align="left"> Kode ICD-10</td><td align="left"> : &nbsp;</td>
 </tr>
<tr height="30px">
<td align="left" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="left" colspan="2">&nbsp;</td>
 </tr>
 <tr height="30px">
<td align="left" >Tindakan</td><td align="left">: ....................................................................</td><td align="left"> Kode ICD-9</td><td align="left"> : &nbsp;</td>
 </tr>
<tr height="30px">
<td align="center" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr>
<tr height="30px">
<td align="center" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr> 
 <tr height="30px">
<td align="left" >Konsul</td><td align="left">: ....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr>
<tr height="30px">
<td align="center" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr> 
 <tr>
<td align="center" colspan="3">&nbsp;</td><td align="left"> <?php echo $conf['dep_kota'];?>, </td>
 </tr>
 <tr>
<td align="center" colspan="3">&nbsp;</td><td align="left"> Dokter Pemeriksa<br><br><br><br>(...............................)</td>
 </tr>
</table>
<?php endif; ?>
</div>
</body>
</html>