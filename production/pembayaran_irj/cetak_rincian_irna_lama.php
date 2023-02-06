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
     $tgl = date("d M Y");

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

        $sql = "select a.id_pembayaran,a.id_poli,a.id_cust_usr, g.pembayaran_diskon, a.reg_jenis_pasien , a.reg_when_update, a.reg_kode_trans,a.reg_tanggal, 
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
            where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dataPasien= $dtaccess->Fetch($sql);
    $view->CreatePost($dataPasien);
    $keterangan=explode("-",$dataPasien["fol_keterangan"]);
    $terima = $keterangan[0];
    $periode = $keterangan[1];

    //AMBIL DATA TRANSAKSI


    $sql = "select b.biaya_jenis_sem, c.jenis_tindakan_nama

            from klinik.klinik_folio a 
            left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
            left join klinik.klinik_jenis_tindakan c on b.biaya_jenis_sem = c.jenis_tindakan_kode
        where a.fol_lunas='n' 
        and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." 
        and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
    $sql .= " GROUP BY b.biaya_jenis_sem, c.jenis_tindakan_nama, c.jenis_tindakan_urut
        order by c.jenis_tindakan_urut";
    $datatipe = $dtaccess->FetchAll($sql);


        $sql = "select deposit_nominal from klinik.klinik_deposit where id_cust_usr = " . QuoteValue(DPE_CHAR, $dataPasien["id_cust_usr"]);
        $datadeposit = $dtaccess->Fetch($sql);
        if($datadeposit['deposit_nominal'] || $datadeposit['deposit_nominal'] != 0) $deposit = $datadeposit['deposit_nominal'];
        else $deposit = 0;



    

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
    <style type="text/css">
        table {
            width: 30cm;
        }

        table, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        thead td.center{
            text-align: center;
        }

        td{
            padding: 3px;
        }

        td.titl{
            font-weight: bold;
        }

        td.subs{
            padding-left: 5cm;
        }

        td.nom{
            text-align: right;
        }

        td.jum{
            text-align: center;
        }

        td.nor{
            border-bottom: none;
            border-top: none;
        }

        td.rig{
            border-right: none;
        }

        td.lef{
            border-left: none;
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
    <table>
        <thead>
            <tr>
                <td rowspan="2" class="center">
                    <img src="<?php echo $fotoName;?>">
                    <H3><?php echo $konfigurasi['dep_nama']; ?></H3>
                    <?php echo $konfigurasi['dep_kop_surat_1']." Telp. ".$konfigurasi['dep_kop_surat_2']; ?>
                </td>
                <td class="center">
                    <h2>NOTA RINCIAN</h2>
                    <h3>BIAYA PERAWATAN</h3>
                </td>
                <td class="center">
                    <H2><?php echo "&nbsp;".$dataPasien["pembayaran_det_kwitansi"];?></H2>
                    <h3>TANGGAL<?php echo " $tgl";?></h3>
                </td>
            </tr>
            <tr>
                <td class="rig">
                    Nomor Register / Medrec<br>
                    Nama Pasien<br>
                    Alamat<br>
                    Bagian kamar<br>
                </td>
                <td class="lef">
                    : <b><?php echo " ".$dataPasien["cust_usr_kode"];?></b><br>
                    : <b><?php echo " ".$dataPasien["cust_usr_nama"];?></b><br>
                    : <?php echo " ".$dataPasien["cust_usr_alamat"];?><br>
                    : <?php echo " ".$dataPembayaran["kelas_nama"]." - ".$dataPembayaran["kamar_nama"];?><br>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <?php echo "&nbsp;".FormatTimestamp($dataPasien["reg_tanggal"]);?>

                </td>
            </tr>
        </thead>
    </table>
    <table>
        <?php
                for($i = 0, $n = count($datatipe); $i < $n; $i++){
                    if($datatipe[$i]['biaya_jenis_sem'] == "OA"){
                        ?>
                         <tr>

                            <td colspan="7" class="titl">PENJUALAN OBAT </td>
                        </tr>
                        <?php
                    }
                    else{
                        ?>
                        <tr>
                            <td colspan="7" class="titl"><?php echo $datatipe[$i]['jenis_tindakan_nama'];?></td>
                        </tr>
                        <?php
                    }
                    ?>
                   

                    <?php
                    $sql = "select a.fol_waktu,a.fol_nama, b.biaya_jenis_sem, c.jenis_tindakan_nama ,a.fol_nominal, a.fol_jumlah, a.fol_hrs_bayar, a.fol_jenis, a.fol_catatan, a.tindakan_tanggal, a.tindakan_waktu, f.usr_name

                        from klinik.klinik_folio a 
                        left join global.global_auth_user f on a.fol_pelaksana = f.usr_id 
                        left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
                        left join klinik.klinik_jenis_tindakan c on b.biaya_jenis_sem = c.jenis_tindakan_kode
                        where a.fol_lunas='n' 
                        and a.id_dep=".QuoteValue(DPE_CHAR,$depId)."
                        and b.biaya_jenis_sem = ".QuoteValue(DPE_CHAR,$datatipe[$i]['biaya_jenis_sem'])." 
                        and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
                    $sql .= " order by a.tindakan_tanggal,a.tindakan_waktu asc";
                    $dataFolio = $dtaccess->FetchAll($sql);

                    for($a = 0, $s = count($dataFolio); $a < $s; $a++){

                        if($dataFolio[$a]['biaya_jenis_sem'] == 'OA'){
                            $sql = "select b.item_nama, b.penjualan_detail_harga_jual, b.penjualan_detail_jumlah, b.penjualan_detail_total 
                                    from apotik.apotik_penjualan a
                                    LEFT JOIN apotik.apotik_penjualan_detail b on a.penjualan_id = b.id_penjualan
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
                                    <td class="nor"><?php echo $dataobat[$o]['item_nama'] ;?></td>
                                    <td class="nor"></td>
                                    <td class="jum nor"><?php echo $dataobat[$o]['penjualan_detail_jumlah'] ;?></td>
                                    <td class="nom nor"><?php echo currency_format($dataobat[$o]['penjualan_detail_harga_jual']) ;?></td>
                                    <td class="nom nor"><?php echo currency_format($dataobat[$o]['penjualan_detail_total']) ;?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            <tr>
                                <td colspan="5" class="subs">Sub total : <?php echo $dataFolio[$a]['fol_catatan'];?></td>
                                <td colspan="5" class="nom"><?php echo currency_format($dataFolio[$a]['fol_hrs_bayar']);?></td>
                            </tr>
                            <?php
                        }

                        else if($dataFolio[$a]['biaya_jenis_sem'] == 'RI'){

                            $sql = "select c.item_nama, b.retur_penjualan_detail_jumlah, c.item_harga_jual, b.retur_penjualan_detail_total 

                                from logistik.logistik_retur_penjualan a
                                left join logistik.logistik_retur_penjualan_detail b on a.retur_penjualan_id = b.id_penjualan_retur
                                left join logistik.logistik_item c on b.id_item = c.item_id

                                where a.retur_penjualan_nomor =".QuoteValue(DPE_CHAR, $dataFolio[$a]['fol_catatan']);
                            $rs = $dtaccess->Execute($sql);
                            $dataretur = $dtaccess->FetchAll($rs);
                            ?>
                            <tr>
                                <td colspan="7"><?php echo $dataFolio[$a]['fol_catatan'];?></td>
                            </tr>
                            <?php

                            for($r = 0, $e = count($dataretur); $r < $e; $r++){
                                ?>
                                <tr>
                                    <td class="nor"></td>
                                    <td class="nor"><?php echo $dataretur[$r]['item_nama'] ;?></td>
                                    <td class="nor"></td>
                                    <td class="jum nor"><?php echo $dataretur[$r]['retur_penjualan_detail_jumlah'] ;?></td>
                                    <td class="nom nor"><?php echo currency_format($dataretur[$r]['item_harga_jual']) ;?></td>
                                    <td class="nom nor"><?php echo currency_format($dataretur[$r]['retur_penjualan_detail_total']) ;?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <td colspan="5" class="subs">Sub Total : <?php echo $dataFolio[$a]['fol_catatan'];?></td>
                                <td colspan="5" class="nom"><?php echo currency_format($dataFolio[$a]['fol_hrs_bayar']);?></td>
                            </tr>
                            <?php
                        }

                        else{
                        ?>

                    <tr>
                        <td class="nor"><?php echo $dataFolio[$a]['tindakan_tanggal'] ;?></td>
                        <td class="nor"><?php echo $dataFolio[$a]['fol_nama'] ;?></td>
                        <td class="nor"><?php echo $dataFolio[$a]['usr_name'] ;?></td>
                        <td class="jum nor"><?php echo $dataFolio[$a]['fol_jumlah'] ;?></td>
                        <td class="nom nor"><?php echo currency_format($dataFolio[$a]['fol_nominal']) ;?></td>
                        <td class="nom nor"><?php echo currency_format($dataFolio[$a]['fol_hrs_bayar']) ;?></td>
                    </tr>
            <?php
                }
                }?>
                <tr>
                <?php
                    if($datatipe[$i]['biaya_jenis_sem'] == "OA"){
                        ?>
                        
                            <td colspan="5" class="titl">TOTAL PENJUALAN OBAT</td>
                        
                        <?php

                    }
                    else if($datatipe[$i]['biaya_jenis_sem'] != null || $datatipe[$i]['jenis_tindakan_nama'] != null){
                        ?>
                         
                            <td colspan="5" class="titl">Total <?php echo $datatipe[$i]['jenis_tindakan_nama'];?></td>
                        
                        <?php
                    }

                    $sql = "SELECT sum(a.fol_hrs_bayar) as tot from klinik.klinik_folio a
                            left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
                            where
                            a.id_dep=".QuoteValue(DPE_CHAR,$depId)."
                            and b.biaya_jenis_sem = ".QuoteValue(DPE_CHAR,$datatipe[$i]['biaya_jenis_sem'])." 
                            and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
                    $jumlah = $dtaccess->Fetch($sql);
                    if($datatipe[$i]['biaya_jenis_sem'] == "RI"){
                        $retur = $jumlah['tot'];
                    }

                    if($jumlah['tot'] != 0){
                ?>
                <td class="nom titl"><?php echo currency_format($jumlah['tot']);?></td>
                <?php
                }
                ?>
                </tr>
                <?php
                }
            ?>
    </table>
    <table>
        <tr>
            <td rowspan="3" width="35%"> Catatan :</td>
            <td> BIAYA PERAWATAN + DOKTER + OBAT</td>
            <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($dataPasien['pembayaran_total']) ;?></td>
        </tr>
        <tr>
            <td>JASA RS</td>
            <td class="rig"> Rp. </td><td class="nom lef"><?php 
            $jsrs = $dataPasien['pembayaran_total'] * 0.1;
            echo currency_format($jsrs) ;
            ?></td>
        </tr>
        <tr>
            <td>SUB TOTAL</td>
            <td class="rig"> Rp. </td><td class="nom lef"><?php
            $subtot = $dataPasien['pembayaran_total'] + $jsrs;
             echo currency_format($subtot) ;
             ?></td>
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
            <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($dataPasien['pembayaran_dijamin']) ;?></td>
        </tr>
        <tr>
            <td>DISKON</td>
            <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($dataPasien['pembayaran_diskon']) ;?></td>
        </tr>
        <tr>
            <td>RETUR</td>
            <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($retur);?></td>
        </tr>
        <tr>
            <td>TUNAI</td>
            <td class="rig"> Rp. </td><td class="nom lef"></td>
        </tr>
        <tr>
            <td>KONTRAKTOR/UMUM/KRYWN</td>
            <td class="rig"> Rp. </td><td class="nom lef"></td>
        </tr>
        <tr>
            <td>KURANG/LEBIH/JUMLAH</td>
            <td class="rig"> Rp. </td><td class="nom lef"><?php echo currency_format($subtot + $retur - $deposit - $dataPasien['pembayaran_dijamin'] - $dataPasien['pembayaran_diskon']);?></td>
        </tr>
    </table>
</body>
</html>