<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."/bit.php");
  require_once($LIB."/login.php");
  require_once($LIB."/encrypt.php");
  require_once($LIB."/datamodel.php");
  require_once($LIB."/dateLib.php");
  require_once($LIB."/currency.php");
  require_once($LIB."/tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $err_code = 0;
  $userData = $auth->GetUserData();
  $userId = $auth->GetUserId();
  $depNama = $auth->GetDepNama();

  $userName = $auth->GetUserName();
  $tgl = date("d-m-Y");

  $_x_mode = "New";
  $thisPage = "kasir_view.php";

  if($_GET["id_reg"] ) {
    $sql = "select id_pembayaran, kamar_nama, bed_kode, kelas_nama 
      from klinik.klinik_registrasi a 
      left join klinik.klinik_rawatinap b on a.reg_id = b.id_reg
      left join klinik.klinik_kamar c on b.id_kamar = c.kamar_id
      left join klinik.klinik_kamar_bed d on b.id_bed = d.bed_id
      left join klinik.klinik_kelas e on c.id_kelas = e.kelas_id
      where reg_id = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
    $dataPembayaran = $dtaccess->Fetch($sql);
    // echo $sql;


    $sql = "select a.id_pembayaran,a.id_poli,a.id_cust_usr, a.reg_tanggal, a.reg_tanggal_pulang, g.pembayaran_diskon, a.reg_jenis_pasien , a.reg_when_update, a.reg_kode_trans,a.reg_tanggal, 
      b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin, ((current_date - b.cust_usr_tanggal_lahir)/365) as umur, 
      b.cust_usr_alamat,a.reg_no_sep,b.cust_usr_no_identitas,c.usr_name, d.poli_nama, e.jenis_nama, 
      g.pembayaran_create, g.pembayaran_total, g.pembayaran_dijamin, g.pembayaran_subsidi, g.pembayaran_hrs_bayar,
      h.pembayaran_det_kwitansi, h.pembayaran_det_hrs_bayar, h.pembayaran_det_total, h.pembayaran_det_service_cash,
      l.jkn_nama
      from klinik.klinik_registrasi a 
      left join klinik.klinik_pembayaran g on g.pembayaran_id=a.id_pembayaran
      left join klinik.klinik_pembayaran_det h on h.id_reg=a.reg_id
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
      left join global.global_auth_user c on c.usr_id = a.id_dokter 
      left join global.global_auth_poli d on a.id_poli = d.poli_id
      left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
      left join global.global_jkn l on l.jkn_id=a.reg_tipe_jkn
      where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET['id_reg'])." and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,"9999999");
      // echo $sql;
    $dataPasien= $dtaccess->Fetch($sql);
    $view->CreatePost($dataPasien);
    $keterangan=explode("-",$dataPasien["fol_keterangan"]);
    $terima = $keterangan[0];
    $periode = $keterangan[1];

    $sql = "select reg_tanggal , reg_tanggal_pulang from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
    $TanggalRegistrasi = $dtaccess->Fetch($sql);

    $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET['pembayaran_id'])." and id_biaya <> '9999999'";
    $TindakanJasa = $dtaccess->Fetch($sql);

    $JasaRS = $TindakanJasa['total'] * 0.1;

    $sql = "select sum(fol_nominal) as total, min(fol_waktu) as tindakan_pertama from klinik.klinik_folio where  id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET['pembayaran_id']);
    $TindakanJasa = $dtaccess->Fetch($sql);

    $sql = "select b.biaya_jenis_sem, c.jenis_tindakan_nama
      from klinik.klinik_folio a 
      left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
      left join klinik.klinik_jenis_tindakan c on b.biaya_jenis_sem = c.jenis_tindakan_kode
      where (fol_lunas='n' or fol_lunas='y')  
      and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
    $sql .= " GROUP BY b.biaya_jenis_sem, c.jenis_tindakan_nama, c.jenis_tindakan_urut order by c.jenis_tindakan_urut";
    // echo $sql;
    $datatipe = $dtaccess->FetchAll($sql);

    $sql = "select deposit_nominal from klinik.klinik_deposit where id_cust_usr = " . QuoteValue(DPE_CHAR, $dataPasien["id_cust_usr"]);
    $datadeposit = $dtaccess->Fetch($sql);
    if($datadeposit['deposit_nominal'] || $datadeposit['deposit_nominal'] != 0) $deposit = $datadeposit['deposit_nominal'];
    else $deposit = 0;
  }

  // KONFIGURASI
  $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,"9999999");
  $rs = $dtaccess->Execute($sql);
  $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";  

  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;

  if($konfigurasi["dep_logo"]!="n") {
    $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
    $fotoName = $lokasi."/default.jpg"; 
  } else { 
    $fotoName = $lokasi."/default.jpg"; 
  }

  $tgl_pertama = date_format(date_create($TindakanJasa["tindakan_pertama"]), 'Y-m-d');
  $tgl1 = new DateTime($dataPasien['reg_tanggal']);
  $tgl2 = new DateTime($TanggalRegistrasi['reg_tanggal_pulang']);
  $tanggal = $tgl2->diff($tgl1)->days + 1;
?>

<!DOCTYPE html>
<html>
  <head>
    <style type="text/css">
      table {
        width: 20cm;
      }

      table, td {
        border: 1px solid black;
        border-collapse: collapse;
      }

      thead td.center{
        text-align: center;
        font-size:14px;
      }

      td{
        padding: 3px;
      }

      td.titl{
        font-weight: bold;
        font-size:14px;
      }

      td.subs{
        padding-left: 3cm;
      }

      td.nom{
        text-align: right;
        font-size:12px;
      }

      td.jum{
        text-align: center;
        font-size:12px;
      }

      td.nor{
        border-bottom: none;
        border-top: none;
        font-size:12px;
      }

      td.rig{
        border-right: none;
        font-size:12px;
      }

      td.cen{
        border-right: none;
        border-left: none;
        font-size:12px;
      }

      td.lef{
        border-left: none;
        font-size:12px;
      }

      img{
        width: 2cm;
      }

      h3, h2{
        margin: 0;
      }
    </style>
  </head>
  <body>
    <table style="width: 17cm;">
      <thead>
        <tr>
          <td rowspan="2" class="center">
            <img src="<?php echo $fotoName;?>">
            <H4><?php echo $konfigurasi['dep_nama']; ?></H4>
            <?php echo $konfigurasi['dep_kop_surat_1']." Telp. ".$konfigurasi['dep_kop_surat_2']; ?>
          </td>
          <td colspan="3" class="center">
            <b>NOTA RINCIAN</b>
            <b>BIAYA PERAWATAN</b>
          </td>
        <!--   <td class="center">
            <H2><?php echo "&nbsp;".$dataPasien["pembayaran_det_kwitansi"];?></H2>
            <h3>TANGGAL<?php echo " $tgl";?></h3>
            <h5>Lama Tinggal <?= $tanggal ?> Hari</h5>
          </td> -->
        </tr>
        <tr>
          <td class="rig" >
            Nomor Register / Medrec<br>
            Nama Pasien<br>
            Alamat<br>
            Bagian kamar<br>
          </td>
          <td class="cen">
            : <br>
            : <br>
            : <br>
            : <br>
          </td>
          <td class="lef">
            <b><?php echo " ".$dataPasien["cust_usr_kode"];?></b><br>
            <b><?php echo " ".str_replace("*", "'", $dataPasien["cust_usr_nama"]) ;?></b><br>
            <?php echo " ".$dataPasien["cust_usr_alamat"];?><br>
             <?=(strpos(strtolower($dataPembayaran["kamar_nama"]), "isolasi") !== false) ? " ".$dataPembayaran["kamar_nama"] : " ".$dataPembayaran["kelas_nama"]." - ".$dataPembayaran["kamar_nama"]?><br>
          </td>
        </tr>
        <tr>
          <td > TANGGAL MRS <br> <?php echo "&nbsp;".FormatTimestamp($dataPasien['reg_tanggal']);?></td>
          <td colspan="2">TANGGAL KRS   <?php echo FormatTimestamp($TanggalRegistrasi['reg_tanggal_pulang']);?></td>
          <td> <?= $tanggal ?> Hari</td>
        </tr>
      </thead>
    </table>
    <table style="width: 17cm">
      <?php
        for($i = 0, $n = count($datatipe); $i < $n; $i++){ 
          if($datatipe[$i]['biaya_jenis_sem'] == "OA"){
            ?>
            <tr>
              <td colspan="7" class="titl">PENJUALAN OBAT </td>
            </tr>
            <?php
          } else {
            ?>
            <tr>
              <td colspan="7" class="titl"><?php echo $datatipe[$i]['jenis_tindakan_nama'];?></td>
            </tr>
            <?php
          }
        ?>
        <?php
          $sql = "select fol_id, a.fol_waktu,a.fol_nama, b.biaya_jenis_sem, b.biaya_kategori, biaya_id, c.jenis_tindakan_nama ,a.fol_nominal, a.fol_nominal_satuan, a.fol_jumlah, a.fol_hrs_bayar, a.fol_jenis, a.fol_catatan, a.tindakan_tanggal, a.tindakan_waktu, a.id_biaya_tarif
            from klinik.klinik_folio a 
            left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
            left join klinik.klinik_jenis_tindakan c on b.biaya_jenis_sem = c.jenis_tindakan_kode
            where (fol_lunas='n' or fol_lunas='y')
            and b.biaya_jenis_sem = ".QuoteValue(DPE_CHAR,$datatipe[$i]['biaya_jenis_sem'])." 
            and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
          $sql .= " order by a.tindakan_tanggal,a.fol_nama asc";
          // echo $sql."<br/>";
          $dataFolio = $dtaccess->FetchAll($sql);

                  for($a = 0, $s = count($dataFolio); $a < $s; $a++){
            $sql = "select * from klinik.klinik_folio_pelaksana a left join klinik.klinik_folio_posisi b on b.fol_posisi_id = a.fol_pelaksana_tipe left join global.global_auth_user c on c.usr_id = a.id_usr where id_fol = ".QuoteValue(DPE_CHAR,$dataFolio[$a]['fol_id'])." and fol_pelaksana_tipe = '10'";
            $dataPelaksana = $dtaccess->Fetch($sql);
            // if ($dataFolio[$a]['biaya_id'] == 'a2769e4db5090d45af53863088dc1efb'  || $dataFolio[$a]['biaya_kategori'] == '1563a52a81b432934cf26675eedb090b'  || $dataFolio[$a]['usr_id'] == 'fe8c6d977f2f99f7ed64f15ba3764c28' || $dataFolio[$a]['biaya_id'] == 'c65be6b5075dad17f89b8abed9bbde0a')  {
            if ($dataFolio[$a]['biaya_id'] == 'a2769e4db5090d45af53863088dc1efb' || $dataFolio[$a]['biaya_kategori'] == '1563a52a81b432934cf26675eedb090b') {
              $dataPelaksana['usr_name'] = '&nbsp';
            }else{
              $dataPelaksana['usr_name'] = $dataPelaksana['usr_name'];
            }
            if($dataFolio[$a]['biaya_jenis_sem'] == 'OA'){
              $sql = "SELECT b.item_nama, b.penjualan_detail_harga_jual, b.penjualan_detail_jumlah, b.penjualan_detail_total, b.penjualan_detail_tuslag, a.penjualan_biaya_racikan, b.id_item, c.item_racikan
                from apotik.apotik_penjualan a
                LEFT JOIN apotik.apotik_penjualan_detail b on a.penjualan_id = b.id_penjualan
                left join logistik.logistik_item c on b.id_item = c.item_id
                where a.penjualan_nomor = ".QuoteValue(DPE_CHAR, $dataFolio[$a]['fol_catatan']);
              $rs = $dtaccess->Execute($sql);
              $dataobat = $dtaccess->FetchAll($rs);
              ?>
              <tr>
                <td colspan="7"><?php echo $dataFolio[$a]['fol_catatan'];?></td>
              </tr>
              <?php
                for($o = 0, $p = count($dataobat); $o < $p; $o++){?>
                  <tr>
                    <td class="nor"></td>
                    <td class="nor"> 
                      <?php if($dataobat[$o]['item_racikan'] == 'y  '){ 
                        $sql = "SELECT item_nama, detail_racikan_jumlah from apotik.apotik_detail_racikan where id_nama_racikan = '".$dataobat[$o]['id_item']."'";
                          $dataRacikan = $dtaccess->FetchAll($sql);
                        ?>
                        <b><?=$dataobat[$o]['item_nama']?></b><br>

                        <?php for($x=0; $x < count($dataRacikan); $x++) { ?>
                          &nbsp;&nbsp;<?=$dataRacikan[$x]["item_nama"]?> (<?=$dataRacikan[$x]["detail_racikan_jumlah"]?>)<br>
                        <?php } ?>

                      <?php }
                      else { ?>
                        <?=$dataobat[$o]['item_nama']?>
                      <?php } ?>
                      
                      
                    </td>
                    <td class="nor"></td>
                    <td class="jum nor"><?php echo $dataobat[$o]['penjualan_detail_jumlah'] ;?></td>
                    <td class="nom nor"><?php echo currency_format(intval(($dataobat[$o]['penjualan_detail_harga_jual']*1.1)*1.05)) ;?></td>
                    <td class="nom nor"><?php echo currency_format($dataobat[$o]['penjualan_detail_total']) ;?></td>
                  </tr>
              <?php
                }
              ?>
              <?php
              if($dataobat[0]['penjualan_biaya_racikan'] != 0 && $dataobat[0]['penjualan_biaya_racikan'] != null){
              ?>
              <tr>
                <td colspan="5">Biaya Racikan : </td>
                <td colspan="5" class="nom"><?php echo currency_format($dataobat[0]['penjualan_biaya_racikan']);?></td>
              </tr>
              <?php } ?>
              <tr>
                <td colspan="5" class="subs">Sub total : <?php echo $dataFolio[$a]['fol_catatan'];?></td>
                <td colspan="5" class="nom"><?php echo currency_format($dataFolio[$a]['fol_hrs_bayar']);?></td>
              </tr>
              <?php
            }  else {
              $tot_jumlah = 0;
              $tot_bayar = 0;
              if ($dataFolio[$a]['tindakan_tanggal'] == $dataFolio[$a+1]['tindakan_tanggal'] && $dataFolio[$a]['id_biaya_tarif'] == $dataFolio[$a+1]['id_biaya_tarif']) {
                if ($dataFolio[$a]['fol_id'] == $dataFolio[$a+1]['fol_id']) {
                  $tot_jumlah = 1;
                  $tot_bayar = 1 * $dataFolio[$a]['fol_hrs_bayar'];
                }else{
                  $tot_jumlah = $tot_jumlah + $dataFolio[$a]['fol_jumlah'];
                  $tot_bayar = $tot_bayar + $dataFolio[$a]['fol_hrs_bayar'];
                }
                // $tot_bayar = $dataFolio[$a]['fol_hrs_bayar'];

                $fol_jumlah += $dataFolio[$a]['fol_jumlah'];
                $foljum = $fol_jumlah + $dataFolio[$a+1]['fol_jumlah'];
                $hrs_bayar += $dataFolio[$a]['fol_hrs_bayar'];
                $hrs = $hrs_bayar + $dataFolio[$a+1]['fol_hrs_bayar'];
              } else {
                ?>
                <tr>
                  <td class="nor" width="13%"><?php echo $dataFolio[$a]['tindakan_tanggal'] ;?></td>
                  <td class="nor"><?php echo $dataFolio[$a]['fol_nama'] ;?></td>
                  <!-- <td class="nor"><?=($dataPelaksana['usr_id'] == 'fe8c6d977f2f99f7ed64f15ba3764c28') ? "" : $dataPelaksana['usr_name']?></td> -->
                   <td class="nor"><?php echo $dataPelaksana['usr_name']?></td>
                  <?php if ($dataFolio[$a]['tindakan_tanggal'] == $dataFolio[$a-1]['tindakan_tanggal'] && $dataFolio[$a]['id_biaya_tarif'] == $dataFolio[$a-1]['id_biaya_tarif']) {
                   ?>
                    <td class="jum nor"><?php echo $foljum ;?></td>
                  <?php 
                } else {
                  
                   ?>
                    <td class="jum nor"><?php echo $dataFolio[$a]['fol_jumlah'] ;?></td>
                  <?php } ?>
                  <td class="nom nor"><?php echo currency_format($dataFolio[$a]['fol_nominal_satuan']) ;?></td>
                  <?php if ($dataFolio[$a]['tindakan_tanggal'] == $dataFolio[$a-1]['tindakan_tanggal'] && $dataFolio[$a]['id_biaya_tarif'] == $dataFolio[$a-1]['id_biaya_tarif']) { ?>
                    <td class="nom nor"><?php echo currency_format($hrs) ;?></td>
                  <?php } else { ?>
                    <td class="nom nor"><?php echo currency_format($dataFolio[$a]['fol_nominal']) ;?></td>
                  <?php } ?>
                </tr>
                <?php
                $tot_jumlah = 0;
                $tot_nominal = 0;
                $tot_bayar = 0;
                $fol_jumlah = 0;
                $foljum = 0;
                $hrs_bayar = 0;
                $hrs = 0;
              }
            }
          }
          ?>
          <tr>
            <?php if($datatipe[$i]['biaya_jenis_sem'] == "OA"){ ?>
              <td colspan="5" class="titl">TOTAL PENJUALAN OBAT</td>
            <?php } else if($datatipe[$i]['biaya_jenis_sem'] != null || $datatipe[$i]['jenis_tindakan_nama'] != null) { ?>
              <td colspan="5" class="titl">Total <?php echo $datatipe[$i]['jenis_tindakan_nama'];?></td>
            <?php
              }
            
            $sql = "SELECT sum(a.fol_nominal) as tot from klinik.klinik_folio a left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
              where b.biaya_jenis_sem = ".QuoteValue(DPE_CHAR,$datatipe[$i]['biaya_jenis_sem'])." 
              and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." ";
              // echo $sql;
            $jumlah = $dtaccess->Fetch($sql);

            if($datatipe[$i]['biaya_jenis_sem'] == "RI") {
              // $retur = $jumlah['tot'];
            }

            if($jumlah['tot'] != 0){
              ?>
              <td class="nom titl"><?php echo currency_format($jumlah['tot']);?></td>
            <?php } ?>
          </tr>
        <?php
      }
    ?>
    </table>
    <table style="width: 17cm;">
      <tr>
        <td rowspan="3" width="35%"> Catatan :</td>
        <td> BIAYA PERAWATAN + DOKTER + OBAT</td>
        <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($TindakanJasa['total']) ;?></td>
      </tr>
      <tr>
        <td>JASA RS</td>
        <td class="rig"> Rp. </td><td class="nom lef">
          <?php 
            $jsrs = $dataPasien['pembayaran_total'] * 0.1;
            echo currency_format($JasaRS) ;
          ?>
        </td>
      </tr>
      <tr>
        <td>SUB TOTAL</td>
        <td class="rig"> Rp. </td><td class="nom lef">
          <?php
            $subtot = $TindakanJasa['total'] + $JasaRS;
            echo currency_format($subtot) ;
          ?>
        </td>
      </tr>
      <tr>
        <td rowspan="7"> Paraf :
          <br>
          <br>
          <br>
          <br>
          <br>
          <br>
          <br>
          <br>
          <center><?php echo $userName;?></center>
        </td>
        <td>UANG MUKA/TLAH DIBAYAR</td>
        <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($deposit);?></td>
      </tr>
      <tr>
        <td>BPJS</td>
        <td class="rig"> Rp. </td><td class="nom lef">
          <?php if ($dataPasien['reg_jenis_pasien'] == '5') {
            echo currency_format($dataPasien['pembayaran_dijamin']);
          } ?>
        </td>
      </tr>
      <tr>
        <td>DISKON</td>
        <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($dataPasien['pembayaran_diskon']) ;?></td>
      </tr>
      <tr>
        <td>RETUR</td>
        <td class="rig"> Rp. </td><td class="nom lef"><?php //echo currency_format($retur);?></td>
      </tr>
      <tr>
        <td>TUNAI</td>
        <td class="rig"> Rp. </td><td class="nom lef"></td>
      </tr>
      <tr>
        <td>KONTRAKTOR/UMUM/KRYWN</td>
        <td class="rig"> Rp. </td>
        <td class="nom lef">
          <?php if ($dataPasien['reg_jenis_pasien'] == '7') {
            // echo currency_format($dataPasien['pembayaran_dijamin']);
          } ?>
        </td>
      </tr>
      <tr>
        <td>KURANG/LEBIH/JUMLAH</td>
        <td class="rig"> Rp. </td><td class="nom lef"><?php
         // echo currency_format($subtot  - $deposit - $dataPasien['pembayaran_dijamin'] - $dataPasien['pembayaran_diskon']);
        echo currency_format($subtot)
         ?>
           
         </td>
      </tr>
    </table>
  </body>
</html>