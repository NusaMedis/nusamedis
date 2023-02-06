<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."currency.php");
  require_once($LIB."expAJAX.php"); 
  require_once($LIB."bit.php");
  require_once($LIB."tree.php");
  require_once($LIB."tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $tgl = date("d-m-Y");
  $userData = $auth->GetUserData();
  $userId = $auth->GetUserId(); 
  $userName = $auth->GetUserName();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $poliId = $auth->IdPoli();

  $sql = 'SELECT b.jenis_nama FROM klinik.klinik_registrasi a LEFT JOIN global.global_jenis_pasien b ON a.reg_jenis_pasien = b.jenis_id WHERE a.reg_id = '.QuoteValue(DPE_CHAR, $_GET['id_reg']);
  $dataLama = $dtaccess->Fetch($sql);

  if ($_POST['btnSave']) {
    /* UPDATE REGISTRASI */
    $sql = "UPDATE klinik.klinik_registrasi SET reg_jenis_pasien = ".QuoteValue(DPE_CHAR, $_POST['reg_jenis_pasien'])." WHERE reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
    $dtaccess->Execute($sql);
    /* UPDATE REGISTRASI */

    if ($_POST['poli_tipe'] == 'I') {
      /* UPDATE INAP */
      $sql = "UPDATE klinik.klinik_registrasi SET reg_tanggal = ".QuoteValue(DPE_CHAR, date_db($_POST['reg_tanggal'])).", reg_waktu = ".QuoteValue(DPE_CHAR, $_POST['waktu1'].':'.$_POST['waktu2'].':'.$_POST['waktu3'])." WHERE reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
      $dtaccess->Execute($sql);
      /* UPDATE INAP */
    }

    // $sql = "SELECT jenis_nama FROM global.global_jenis_pasien WHERE jenis_id = ".QuoteValue(DPE_CHAR, $_POST['reg_jenis_pasien']);
    // $dataJenisBaru = $dtaccess->Fetch($sql);

    // $perubahan = "Merubah Jenis Pasien ".$dataLama['jenis_nama']." Menjadi Jenis Pasien ".$dataJenisBaru['jenis_nama'];

    // $dbTable = "klinik.klinik_status_pasien_history";
               
    // $dbField[0] = "status_pasien_history_id";   // PK
    // $dbField[1] = "status_pasien_history_keterangan";
    // $dbField[2] = "status_pasien_history_perubahan";
    // $dbField[3] = "id_reg";
    // $dbField[4] = "when_create";
 
    // $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransId());
    // $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["keterangan_status"]); 
    // $dbValue[2] = QuoteValue(DPE_CHAR,$perubahan); 
    // $dbValue[3] = QuoteValue(DPE_CHAR,$_GET['id_reg']); 
    // $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s')); 

    // $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    // $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

    // $dtmodel->Insert() or die("insert  error");

    header('Location: ../penata_jasa_irj/ar_main.php');
  }

  /* DATA PASIEN */
  $sql_pasien = "SELECT a.reg_id, b.cust_usr_id, c.id_instalasi, a.reg_tanggal, a.reg_waktu, b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_umur, b.cust_usr_jenis_kelamin, b.cust_usr_alamat, b.cust_usr_no_hp, a.reg_jenis_pasien, a.id_poli, a.reg_tipe_rawat, c.poli_tipe, a.reg_status, a.reg_kelas, a.reg_no_sep, d.inacbg_kode, a.id_dokter FROM klinik.klinik_registrasi a LEFT JOIN global.global_customer_user b ON a.id_cust_usr = b.cust_usr_id LEFT JOIN global.global_auth_poli c ON a.id_poli = c.poli_id LEFT JOIN klinik.klinik_inacbg d ON a.reg_id = d.id_reg WHERE reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
  $dataPasien = $dtaccess->Fetch($sql_pasien);

  $waktu = explode(':', $dataPasien['reg_waktu']);
  $umur = explode('~', $dataPasien['cust_usr_umur']);
  /* DATA PASIEN */

  /* JENIS PASIEN */
  $sql_jenis_pasien = "SELECT jenis_id, jenis_nama FROM global.global_jenis_pasien ORDER BY jenis_id ASC";
  $dataJenisPasien = $dtaccess->FetchAll($sql_jenis_pasien);
  /* JENIS PASIEN */

  /* POLI */
  $sql_poli = "SELECT poli_id, poli_nama FROM global.global_auth_poli WHERE id_instalasi = ".QuoteValue(DPE_CHAR, $dataPasien['id_instalasi'])." ORDER BY poli_nama";
  $dataPoli = $dtaccess->FetchAll($sql_poli);
  /* POLI */

  /*KELAS*/
  $sql_kelas = "SELECT kelas_id, kelas_nama FROM klinik.klinik_kelas ORDER BY kelas_nama ASC";
  $dataKelas = $dtaccess->FetchAll($sql_kelas);
  /*KELAS*/

  /*DOKTER*/
  $sql_dokter = "SELECT usr_id, usr_name FROM global.global_auth_user ORDER BY usr_name ASC";
  $dataDokter = $dtaccess->FetchAll($sql_dokter);
  /*DOKTER*/
?>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <?php require_once($LAY."topnav.php") ?>
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Edit Perawatan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmEdit" id="frmEdit" method="POST"  action="">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Pemeriksaan Tanggal</label>
                        <div class='input-group date' id='datepicker'>
                          <input name="reg_tanggal" type='text' class="form-control" value="<?=date_db($dataPasien['reg_tanggal'])?>" <?php if ($dataPasien['poli_tipe'] != 'I') echo 'disabled'?>>
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>
                      </div>
	                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Pemeriksaan Waktu</label>
                				<select name="waktu1" class="inputField" <?php if ($dataPasien['poli_tipe'] != 'I') echo 'disabled'?>>
                					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
                            <?php if ($i < '10'): ?>
                  						<option class="form-control" value="<?php echo "0".$i;?>" <?php if($i==$waktu[0]) echo "selected"; ?>><?php echo $i;?></option>
                            <?php else: ?>
                              <option class="form-control" value="<?php echo $i;?>" <?php if($i==$waktu[0]) echo "selected"; ?>><?php echo $i;?></option>
                            <?php endif ?>
                					<?php } ?>
                				</select>:
              					<select name="waktu2" class="inputField" <?php if ($dataPasien['poli_tipe'] != 'I') echo 'disabled'?>>
                					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
                            <?php if ($i < '10'): ?>
                  						<option class="form-control" value="<?php echo "0".$i;?>" <?php if($i==$waktu[1]) echo "selected"; ?>><?php echo $i;?></option>
                            <?php else: ?>
                              <option class="form-control" value="<?php echo $i;?>" <?php if($i==$waktu[1]) echo "selected"; ?>><?php echo $i;?></option>
                            <?php endif ?>
                					<?php } ?>
                				</select>
              					<select name="waktu3" class="inputField" <?php if ($dataPasien['poli_tipe'] != 'I') echo 'disabled'?>>
                					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
                            <?php if ($i < '10'): ?>
                  						<option class="form-control" value="<?php echo "0".$i;?>" <?php if($i==$waktu[2]) echo "selected"; ?>><?php echo $i;?></option>
                            <?php else: ?>
                              <option class="form-control" value="<?php echo $i;?>" <?php if($i==$waktu[2]) echo "selected"; ?>><?php echo $i;?></option>
                            <?php endif ?>
                					<?php } ?>
                				</select>
                      </div>
                      <table width="100%" border="0" cellpadding="0" cellspacing="0"> 
                        <tr >
                          <td align ="left" colspan="1" ><b>&nbsp;&nbsp;</b></td>
                        </tr>
                        <tr> 
                          <td width="50%">
                            <table class="table table-striped table-bordered dt-responsive nowrap" width="100%" valign="top">
                              <tr>
                                <td width="25%"  class="tablesmallheader">&nbsp;No. RM </td>
                                <td  width="70%"  ><label>&nbsp;<?php echo $dataPasien["cust_usr_kode"]; ?></label></td>      
                              </tr>
                              <tr>
                                <td  width="25%"  class="tablesmallheader">&nbsp;Nama Lengkap </td>
                                <td  width="70%"  ><label>&nbsp;<?php echo $dataPasien["cust_usr_nama"]." / ".$umur[0]." Tahun"; ?></label></td>   
                              </tr>
                              <tr>
                                <td width= "25%"  class="tablesmallheader">&nbsp;Jenis Kelamin </td>
                                <td width= "70%" ><label>&nbsp;<?=($dataPasien['cust_usr_jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan';?></label></td>
                              </tr>
                              <tr>
                                <td  width="25%"  class="tablesmallheader">&nbsp;Alamat </td>
                                <td  width="70%"  ><label>&nbsp;<?php echo $dataPasien["cust_usr_alamat"]; ?></label></td>   
                              </tr>
                              <tr>
                                <td width = "25%" class="tablesmallheader">&nbsp;No HP </td>
                                <td width = "70%" ><label>&nbsp;<?php echo $dataPasien["cust_usr_no_hp"]; ?></label></td>
                              </tr>
                              <tr>                                                                                        
                                <td width = "25%" class="tablesmallheader">&nbsp;Jenis Pasien </td>
                                <td width = "20%" >
                                  <select name="reg_jenis_pasien" id="reg_jenis_pasien" class="form-control">
                                    <option value="">--- Pilih Jenis Pasien ---</option>
                                    <?php for($i=0;$i<count($dataJenisPasien);$i++){ ?>
                                      <option value="<?php echo $dataJenisPasien[$i]["jenis_id"];?>" <?php if($dataJenisPasien[$i]["jenis_id"]==$dataPasien["reg_jenis_pasien"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataJenisPasien[$i]["jenis_nama"];?></option>
				                            <?php } ?>
			                            </select>         
                                </td>
                              </tr>
                              <tr>
                                <td width = "25%" class="tablesmallheader">&nbsp;Keterangan</td>
                                <td width = "70%" ><textarea name="keterangan_status" style="width: 100%"></textarea></td>
                              </tr>
                            </table> 
                          </td>
                        </tr>
                      </table>
                      <table width="100%" border="0" cellpadding="1" cellspacing="1">
                        <tr align ="center" >
                          <td align="left"  class="tableheader" colspan="2"><b>&nbsp;&nbsp;</b></td>
                        </tr>
                        <tr> 
                          <td width="100%" colspan="2"></td>
                        </tr>
                        <tr>
                          <td colspan="2" align="center" class="tableheader">
                            <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CekTanggal(document.frmEdit);"/>     
                            <input type="button" name="btnDel" id="btnDel" value="Kembali" class="submit" onClick="kembali()" />  
                          </td>
                        </tr> 
                      </table> 
                      <input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
                      <input type="hidden" name="poli_tipe" value="<?php echo $dataPasien["poli_tipe"];?>"/>
                      <input type="hidden" name="jadwal_id" value="<?php echo $_POST["jadwal_id"];?>"/>
                      <input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
                      <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
                      <input type="hidden" name="rawat_icd_id" value="<?php echo $_POST["rawat_icd_id"];?>"/>
                      <input type="hidden" name="cust_usr_nama" value="<?php echo $_POST["cust_usr_nama"];?>"/>
                      <input type="hidden" name="cust_usr_alamat" value="<?php echo $_POST["cust_usr_alamat"];?>"/>
                      <input type="hidden" name="penjualan_id" value="<?php echo $_POST["penjualan_id"];?>"/>
                      <input type="hidden" name="penjualan_detail_id[0]" value="<?php echo $_POST["penjualan_detail_id"][0];?>"/>
                      <input type="hidden" name="penjualan_detail_id[1]" value="<?php echo $_POST["penjualan_detail_id"][1];?>"/>
                      <input type="hidden" name="penjualan_detail_id[2]" value="<?php echo $_POST["penjualan_detail_id"][2];?>"/>
                      <input type="hidden" name="penjualan_detail_id[3]" value="<?php echo $_POST["penjualan_detail_id"][3];?>"/>
                      <input type="hidden" name="penjualan_id" value="<?php echo $dataObatPasien[0]["penjualan_id"];?>"/>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php require_once($LAY."js.php") ?>
      </div>
    </div>
  </body>
</html>
<script type="text/javascript">
  function kembali() {
    window.parent.document.location.href='../penata_jasa_irj/ar_main.php';
  }
</script>