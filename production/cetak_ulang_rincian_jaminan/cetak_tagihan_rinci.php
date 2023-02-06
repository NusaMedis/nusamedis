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
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
  
  if($_GET["id_reg"] ) {
    $sql = "select a.id_pembayaran,a.id_poli,a.id_cust_usr, a.reg_jenis_pasien , a.reg_when_update, a.reg_kode_trans,a.reg_tanggal, a.reg_tanggal_pulang, gedung_rawat_nama,
            b.cust_usr_nama,b.cust_usr_kode_tampilan, cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin, ((current_date - b.cust_usr_tanggal_lahir)/365) as umur, 
            b.cust_usr_alamat,a.reg_no_sep,b.cust_usr_no_jaminan,c.usr_name, d.poli_nama, e.jenis_nama, f.fol_keterangan, 
            g.pembayaran_create, g.pembayaran_total, g.pembayaran_dijamin, g.pembayaran_subsidi, g.pembayaran_hrs_bayar,
            h.pembayaran_det_kwitansi, h.pembayaran_det_hrs_bayar, h.pembayaran_det_total,cust_usr_tanggal_lahir, reg_umur, reg_umur_bulan, reg_umur_hari,
            l.jkn_nama
            from klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran g on g.pembayaran_id=a.id_pembayaran
            left join klinik.klinik_pembayaran_det h on h.id_reg=a.reg_id
            left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join klinik.klinik_folio f on f.id_reg = a.reg_id 
            left join global.global_jkn l on l.jkn_id=a.reg_tipe_jkn
			left join klinik.klinik_rawat_inap_history o on a.reg_utama = o.id_reg
            left join global.global_gedung_rawat p on o.rawat_inap_history_gedung_tujuan = p.gedung_rawat_id
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
           
    $dataPasien= $dtaccess->Fetch($sql);
    $view->CreatePost($dataPasien);
    $keterangan=explode("-",$dataPasien["fol_keterangan"]);
    $terima = $keterangan[0];
    $periode = $keterangan[1];

    //AMBIL DATA TRANSAKSI
    $sql = "select a.fol_nama, a.fol_nominal, a.fol_jumlah, a.fol_hrs_bayar,  a.fol_jenis, a.fol_catatan, a.tindakan_tanggal, a.tindakan_waktu,
            f.usr_name
    from klinik.klinik_folio a
    left join global.global_auth_user f on a.fol_pelaksana = f.usr_id
    where a.fol_lunas='y' 
    and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." 
    and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
    $sql .= " order by a.tindakan_tanggal,a.tindakan_waktu asc";
    $dataFolio = $dtaccess->FetchAll($sql);

    for($i=0,$n=count($dataFolio);$i<$n;$i++){
     $totalBiaya = $totalBiaya+$dataFolio[$i]["fol_nominal"];
    }

  }
     
	 if($_GET["id_reg"] ) { 
		$sql = "select p.gedung_rawat_nama
            from klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran g on g.pembayaran_id=a.id_pembayaran
            left join klinik.klinik_pembayaran_det h on h.id_reg=a.reg_id
            left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join klinik.klinik_folio f on f.id_reg = a.reg_id 
            left join global.global_jkn l on l.jkn_id=a.reg_tipe_jkn
			left join klinik.klinik_rawat_inap_history o on a.reg_utama = o.id_reg
            left join global.global_gedung_rawat p on o.rawat_inap_history_gedung_tujuan = p.gedung_rawat_id
            where p.gedung_rawat_nama IS NOT NULL and a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId)."group by p.gedung_rawat_nama";
			
		$dataRuangan= $dtaccess->Fetch($sql);
		$view->CreatePost($dataRuangan);	
	 }
     
        // KONFIGURASI
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $lokasi = $ROOT."/gambar/img_cfg";  
     
     if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
     if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
     
  if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }

?>

<!DOCTYPE html>
<html>
<head>
  <title>Cetak Tagihan Rincian Rawat Inap Sementara</title>
  <style type="text/css">
    body {
      font-family: Verdana, Arial, Helvetica, sans-serif;
      font-size: 12px;
      margin: 0px;
      padding: 0px 0px 0px 0px;
    }

    #table1 {
      width: 100%;
      margin-top: 40px; 
    }

    #table2 {
      width: 100%;
      border-collapse: collapse;
    }

    #table2 td {
      padding: 5px;
    }

    #table2 td.bord-top {
      border-top: 1px dashed;
      border-bottom: 1px dashed;
    }

    #tableSub {
      width: 100%;
      border-collapse: collapse;
      margin-left: 5px; 
    }

  </style>
</head>
<body onload="window.print(); ">
  <center><span style="font-size: 14px;">RINGKASAN BIAYA TINDAKAN RAWAT INAP</span></center>
  </br>
  <center><span style="font-size: 14px;">RSPI PROF.DR.SULIANTI SAROSO</span></center>
  <table id="table1"> 
    <tr>
      <td align="left" width="7%">No. Registrasi</td>  
      <td width="1%">:</td>  
      <td align="left" width="15%"><?php echo $dataPasien["reg_kode_trans"];?></td>  
      <td align="center" width="5%">&nbsp;</td>
      <td align="left" width="7%">Tgl Lahir / Umur</td>  
      <td width="1%">:</td>  
      <td align="left" width="20%"><?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]).' / '.$dataPasien["reg_umur"].' thn '
      .$dataPasien["reg_umur_bulan"].' bln '.$dataPasien["reg_umur_hari"] ;?> hr</td>  
    </tr>
    <tr>
      <td>Tgl Registrasi</td>  
      <td>:</td>  
      <td><?php echo format_date($dataPasien["reg_tanggal"]);?></td>  
      <td>&nbsp;</td>
      <td>Cara Bayar</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["jenis_nama"].' '.$dataPasien["jkn_nama"];?></td>  
    </tr><tr>
      <td>Tgl Pulang</td>  
      <td>:</td>  
      <td><?php echo format_date($dataPasien["reg_tanggal_pulang"]);?></td>  
      <td>&nbsp;</td>
      <td>Ruangan</td>
      <td>:</td>  
      <td><?php echo (!empty( $dataPasien["poli_nama"] )) ? $dataPasien["poli_nama"] : $dataRuangan["gedung_rawat_nama"];?></td>  
    </tr>
    <tr>
      <td>No. RM</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_kode_tampilan"];?></td>  
      <td>&nbsp;</td>
      <td>No Jaminan</td>
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_no_jaminan"];?></td> 
    </tr>
    <tr>
      <td>Nama Pasien</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_nama"];?></td>  
      <td>&nbsp;</td>
      <td>No SEP</td>
      <td>:</td>
      <td><?php echo $dataPasien["reg_no_sep"];?></td>  
    </tr>
    <tr>
      <td>Alamat</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_alamat"];?></td>  
      
    </tr>
  </table>
  <br><br>
  <table id="table2">
    <tr>
      <td width="1%" align="right" class="bord-top">No</td>
      <td width="10%" align="center" class="bord-top">Tanggal</td>
      <td width="25%" align="center" class="bord-top">DESKRIPSI</td>
      <td width="4%" align="center" class="bord-top">JML</td>
      <td width="5%" align="right" class="bord-top">TAGIHAN</td>
    </tr>
    <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { 
      $totalPembayaran += $dataFolio[$i]["fol_nominal"]; 
      $folnama = $dataFolio[$i]["fol_nama"];
      if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI' || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG' || $dataFolio[$i]["fol_jenis"]=='I') {
        $folnama = $dataFolio[$i]["fol_nama"]."(".$dataFolio[$i]["fol_catatan"].")";
        $sql = "select a.item_nama as item, a.* ,satuan_nama, c.item_nama as barang
              from apotik.apotik_penjualan_detail a
              left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
              left join logistik.logistik_item c on a.id_item = c.item_id
              left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
              where b.penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
        $rs = $dtaccess->Execute($sql); 
        $dataFarmasidetail  = $dtaccess->FetchAll($rs);
      }
      if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA' || $dataFolio[$i]["fol_jenis"]=='RG' || $dataFolio[$i]["fol_jenis"]=='RI' ) {
        $folnama = $dataFolio[$i]["fol_nama"]."(".$dataFolio[$i]["fol_catatan"].")";     
        $sql = "select a.item_nama as item, a.* ,satuan_nama, c.item_nama as barang
                from logistik.logistik_retur_penjualan_detail a
                left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                left join logistik.logistik_item c on a.id_item = c.item_id
                left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
        $rs = $dtaccess->Execute($sql);
        $dataReturdetail  = $dtaccess->FetchAll($rs);   
      }  
    ?>
    <?php if($dataFolio[$i]["kategori_kassa_id"]!=$dataFolio[$i-1]["kategori_kassa_id"]) { ?>
    <tr>
      <td align="left" class="bord-top bord-bot"><span style="letter-spacing: 1.3px; font-weight: bold;"><?php echo $dataFolio[$i]["kategori_kassa_nama"];?></span></td>
      <td align="center" class="bord-top "></td>
      <td align="right" class="bord-top"></td>
      <td align="right" class="bord-top "></td>
      <td align="right" class="bord-top"></td>
    </tr>
    <?php } ?>
    <tr>
      <td align="right"><?php echo $i+1; ?></td>
      <td align="left"><?php echo format_date($dataFolio[$i]['tindakan_tanggal']).' '.$dataFolio[$i]['tindakan_waktu']; ?></td>
      <td align="left"><?php echo $folnama;?></td>
      <td align="center"><?php echo $dataFolio[$i]['fol_jumlah']; ?></td>
      <td align="right"><?php echo number_format($dataFolio[$i]['fol_nominal']); ?></td>
    </tr>
    <?php if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI' || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG' || $dataFolio[$i]["fol_jenis"]=='I') { ?>
    <tr>
      <td align="right"></td>
      <td align="left"></td>
      <td align="left">
        <table id="tableSub">
          <tr>
            <td align="left" class="bord-top" width="40%">Nama Item</td>
            <td align="center" class="bord-top" width="20%">Jumlah / Satuan</td>
            <td align="right" class="bord-top" width="20%">Harga Jual</td>
            <td align="right" class="bord-top" width="20%">Sub Total</td>
          </tr>
          <?php for($x=0; $x<count($dataFarmasidetail); $x++) { ?>
          <tr>
            <?php if($dataFarmasidetail[$x]["item"]=='' || $dataFarmasidetail[$x]["item"]==null){ ?>
              <td align="left"><?php echo $dataFarmasidetail[$x]["barang"];?></td>
            <?php } else { ?>
              <td align="left"><?php echo $dataFarmasidetail[$x]["item"];?></td>
            <?php } ?>
            <td align="left"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>
            <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
            <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>
          </tr>
          <?php } # end loop detail penjualan ?>
        </table>
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
    <?php  } # end data penjualan  ?>
   <?php if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA' || $dataFolio[$i]["fol_jenis"]=='RG' || $dataFolio[$i]["fol_jenis"]=='RI' ) { ?>
    <tr>
      <td align="right"></td>
      <td align="left"></td>
      <td align="left">
        <table id="tableSub">
          <tr>
            <td align="left" class="bord-top" width="40%">Nama Item</td>
            <td align="center" class="bord-top" width="20%">Jumlah / Satuan</td>
            <td align="right" class="bord-top" width="20%">Harga Jual</td>
            <td align="right" class="bord-top" width="20%">Sub Total</td>
          </tr>
          <?php for($x=0; $x<count($dataReturdetail); $x++) { ?>
          <tr>
            <?php if($dataReturdetail[$x]["item"]=='' || $dataReturdetail[$x]["item"]==null){ ?>
              <td align="left"><?php echo $dataReturdetail[$x]["barang"];?></td>
            <?php } else { ?>
              <td align="left"><?php echo $dataReturdetail[$x]["item"];?></td>
            <?php } ?>
            <td align="left"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>
            <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
            <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>
          </tr>
          <?php } # end loop detail retur ?>
        </table>
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
    <?php  } # end data retur  ?>
  <?php } # end loop folio ?>
    <tr>
      <td align="right" class="bord-top" colspan="4"><span style="letter-spacing: 1.3px; font-weight: bold;">Total Tagihan</span></td>
      <td align="right" class="bord-top"><?php echo number_format($totalPembayaran); ?></td>
    </tr>
  </table>
  <span style="text-align: right; float: right; margin-top: 30px;">Dicetak di <? echo $konfigurasi["dep_kota"].", Tanggal ". date("d-m-Y H:i:s");?><br>Printed by <? echo $userName;?></span>
</body>
</html>