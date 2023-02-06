<?php 
 
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."bit.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."currency.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."expAJAX.php");
  require_once($LIB."tampilan.php"); 

  //INISIALISASI LIBRARY
  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $depNama = $auth->GetDepNama(); 
  $userName = $auth->GetUserName();
  $enc = new textEncrypt();     
  $depId = $auth->GetDepId();
  $lokasi = $ROOT."gambar/foto_pasien";



  # data pasien bpjs
  if($_GET["cust_usr_kode"])  $sql_where[] = "cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_kode"])."%");
  if($_GET["no_sep"])  $sql_where[] = "no_sep like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["no_sep"])."%");
  if($_GET["cust_usr_nama"])  $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_nama"])."%");
  if(!empty($_GET["tgl_awal"]))  $sql_where[] = "tgl_sep >=".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
  if(!empty($_GET["tgl_akhir"]))  $sql_where[] = "tgl_sep <=".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
  if( empty($_GET["tgl_awal"]) && empty($_GET["tgl_akhir"]) ) $sql_where[] = "tgl_sep =".QuoteValue(DPE_DATE,date('Y-m-d'));
  $sql_where[] = "cust_usr_nama is not null";
  $sql_where[] = "cust_usr_kode <> '500'";
  if ($sql_where[0])  $sql_where = implode(" and ",$sql_where);

  $sql = "select d.cust_usr_nama_txt, reg_id,cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan, cust_usr_nama, cust_usr_tanggal_lahir , poli_nama, reg_tanggal, reg_waktu, tgl_sep, no_sep, jkn_nama, jenis_peserta_txt
      from klinik.klinik_registrasi a
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
      left join global.global_auth_poli c on a.id_poli = c.poli_id
      left join klinik.klinik_sep d on a.reg_id = d.sep_reg_id
      left join global.global_jkn e on d.tipe_jkn = e.jkn_id";
  $sql .= " WHERE reg_jenis_pasien = 5 and (d.no_sep != '' or d.no_sep IS NOT NULL) ";  
  $sql .= " and ".$sql_where;
  $sql .= " order by reg_tanggal desc, reg_waktu desc, tgl_sep desc limit 200";
  // echo $sql;

  $rs = $dtaccess->Execute($sql);
  $rows = $dtaccess->FetchAll($rs);
  header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data SEP.xls");
?>

    <div class="container body">
      <div class="main_container">
      

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Data SEP</h3>
              </div>
            </div>
            <div class="clearfix"></div>

            <div class="row">
 
              <!-- /.col -->

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Pasien Jaminan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">          
                    <table border="1">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th width="150px">Tanggal Reg.</td>
                          <th>Tanggal SEP.</td>
                          <th width="100px">No MR</td>
                          <th width="200px">Nama</td>
                          <th>Poli Klinik</td>
                          <th>Jenis Peserta</td>
                          <th width="100px">No SEP</td>
                          
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $no=1;
                        foreach ($rows as $key => $value) { ?>
                        <tr id="<?= $value['no_sep'];?>">
                          <td><?php echo $no; ?></td>
                          <td><?php echo format_date($value['reg_tanggal']).' '.$value['reg_waktu'];?></td>
                          <td><?php echo format_date($value['tgl_sep']);?></td>
                          <td><?php echo $value['cust_usr_kode'];?></td>
                          <td><?php 
                          if (is_null($value['cust_usr_nama_txt'])) {
                             # code...
                            echo  str_replace("*", "'", $value['cust_usr_nama']) ;
                           }
                           else if ($value['cust_usr_nama_txt']=" ") {
                             # code...
                             echo  str_replace("*", "'", $value['cust_usr_nama']);
                           }
                           else{ echo  str_replace("*", "'", $value['cust_usr_nama_txt']);
                         }?></td>
                          <td><?php echo $value['poli_nama'];?></td>
                          <td><?php echo $value['jenis_peserta_txt'];?></td>
                          <td><?php echo $value['no_sep'];?></td>
                        
                        </tr>
                        <?php
                        $no++; } ?>
                      </tbody>
                    </table>
          
          
                  </div>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            &nbsp;
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

