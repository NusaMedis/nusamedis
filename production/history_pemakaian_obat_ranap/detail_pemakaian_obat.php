<?php
// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $backPage = "History_view_inap.php";
     $thisPage = "detail_penggunaan_obat.php";
     $poliId = $auth->IdPoli();

     if($_GET['reg_id']) $regid = $_GET['reg_id'];

     $sql = "select b.cust_usr_nama, b.cust_usr_kode, a.reg_kode_trans from klinik.klinik_registrasi a 
          LEFT JOIN global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
          where reg_id = '$regid'";
     $rs = $dtaccess->Execute($sql);
     $dataUsr = $dtaccess->FetchAll($rs);

     $sql = "select id_pembayaran from klinik.klinik_registrasi where reg_id = '$regid'";
     $IdPembayaran = $dtaccess->Fetch($sql);


     $sql = "SELECT * from apotik.apotik_penjualan 
     where penjualan_total <> 0 and  id_fol 
     in(SELECT fol_id from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$IdPembayaran['id_pembayaran'])." )  order by penjualan_create asc";
     $rs = $dtaccess->Execute($sql);
     // echo $sql;
     $dataPenjualan = $dtaccess->FetchAll($rs);

     $sql = "select * from global.global_departemen where dep_id = '$depId'";
     $rs = $dtaccess->Execute($sql);
     $dataDep = $dtaccess->FetchAll($rs);
     $png = $dataDep[0]['dep_logo'];
     $width = $dataDep[0]['dep_width'];
     $height = $dataDep[0]['dep_height'];
     $lokasi = "$ROOT/gambar/img_cfg";
     $gambar = "$lokasi/$png";

     $x = "";
?>
<!DOCTYPE html>
<html>
<head>
     <style type="text/css">

          div.inti{
               width: 30cm;
               margin: auto;
          }

          div.second{
               display: block;
          }

          div.right{
               float: right;
               display: inline-block;
               padding-top: 45px;
          }

          h3, h5, h2{
               margin: 0;
          }

          div.left{
               display: inline-block;
          }

          .red{
               color: red;
               border-color: black;
          }

          .total{
               text-align: right;
          }

          table {
               border-collapse: collapse;
               border: 1px solid black;
               width: 100%;
          }

          table.usr{
               width: auto;dataSUM
          }

          table.usr, table.usr td{
               border: none;
               padding-bottom: 2px;
               padding-top: 2px;
          }

          th {
               height: 1.4cm;
               border-left: 1px solid black;
               border-right: 1px solid black;
          }

          th, .tt td{
               border-top: 3px solid black !important;
               border-bottom: 3px solid black !important;
          }

          td {
               height: 0.5cm;
               border: 1px solid black;
               padding-left: 10px;
          }

          td.bot{
               border-bottom: none
          }

          td.top{
               border-top: none;
          }

          td.nobor{
               border-right: none;
          }

          td.noba{
               border-left: none;
          }

     </style>
</head>
<body onload="window.print()">
	
     <div class="inti">
          <table class="usr">
               <tr>
                    <td>
                         <img src="<?php echo $gambar; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>">
                    </td>
                    <td>
                         <h2><?php echo $dataDep[0]['dep_nama']; ?></h5>
                         <h5><?php echo $dataDep[0]['dep_kop_surat_1']; ?></h5>
                         <h5><?php echo $dataDep[0]['dep_kop_surat_2']; ?></h5>
                    </td>
               </tr>
          </table>
     
     <div class="second">
          <div class="left">
               <table class="usr">
                    <tr>
                         <td>Nama Pasien</td>
                         <td>: <?php echo $dataUsr[0]['cust_usr_nama'] ?></td>
                    </tr>
                     <tr>
                         <td>No. MedRec</td>
                         <td>: <?php echo $dataUsr[0]['cust_usr_kode'] ?></td>
                    </tr>
                     <tr>
                         <td>No. Registrasi</td>
                         <td>: <?php echo $dataUsr[0]['reg_kode_trans'] ?></td>
                    </tr>
               </table>
          </div>
          <div class="right">
               <h3>Pemakaian Obat Pasien Rawat Inap</h3>
          </div>
     </div>
     <table>
          <thead>
               <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Obat</th>
                    <th>Nama Obat/Alkes</th>
                    <th>Tuslag</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
               </tr>
          </thead>
          <tbody>
               <?php 
               for($i = 0, $n = count($dataPenjualan); $i<$n; $i++){
               ?>
               <tr>
                    <td colspan="2" class="nobor"><b><?php echo $dataPenjualan[$i]['penjualan_nomor']; ?></b></td>
                    <td colspan="6" class="noba"><b>Nomor Resep: <?php echo $dataPenjualan[$i]['penjualan_urut'];?></b></td>
               </tr>
               
                    <?php
                    $penjualanId = $dataPenjualan[$i]['penjualan_id'];
                    $sqla = "SELECT c.penjualan_grandtotal,a.id_penjualan, b.item_kode, b.item_nama, a.penjualan_detail_harga_jual, a.penjualan_detail_jumlah, a.penjualan_detail_total, b.item_racikan, b.item_id
                    	from apotik.apotik_penjualan_detail a 
                        LEFT JOIN logistik.logistik_item b on a.id_item = b.item_id 
                        left join apotik.apotik_penjualan c on c.penjualan_id = a.id_penjualan   
                         where a.id_penjualan = '$penjualanId'";
                    $rv = $dtaccess->Execute($sqla);
                    $dataObat = $dtaccess->FetchAll($rv);

                    for($u = 0, $r = count($dataObat); $u<$r; $u++){
                         if($u != 0){
                              $x = "top";
                         }

                    ?>
               <tr>
                    <td class="bot <?php echo $x; ?>"><?php echo ($u+1);?></td>
                    <td class="bot <?php echo $x; ?>"><?php echo FormatTimeStamp($dataPenjualan[$i]['penjualan_create']); ?></td>
                    <td class="bot <?php echo $x; ?>"><?php echo $dataObat[$u]['item_kode']; ?></td>
                    <td class="bot <?php echo $x; ?>">
                    	<?php if($dataObat[$u]['item_racikan'] == 'y  ') { 
                    		$sql = "SELECT item_nama, detail_racikan_jumlah from apotik.apotik_detail_racikan where id_nama_racikan = '".$dataObat[$u]['item_id']."'";
                        	$dataRacikan = $dtaccess->FetchAll($sql); ?>

                    		<b><?=$dataObat[$u]['item_nama']?></b><br>

                    		<?php for($y=0; $y < count($dataRacikan); $y++) { ?>
		                        &nbsp; &nbsp; &nbsp;<?=$dataRacikan[$y]["item_nama"]?>(<?=$dataRacikan[$y]["detail_racikan_jumlah"]?>)<br>
		                    <?php } ?>
                    	<?php } else { ?>
                    		<?=$dataObat[$u]['item_nama']?>
                    	<?php } ?>
                    	
                    		
                    	</td>
                    <?php 
                    if($u == 0){ ?>
                          <td class="bot <?php echo $x; ?> total"><?php echo number_format($dataPenjualan[$i]['penjualan_tuslag'], 0, ',', '.'); ?></td>
                    <?php
                    }
                    else{ ?>
                         <td class="bot <?php echo $x; ?>"></td>
                    <?php
                    }
                    
                    ?>
                    <td class="bot <?php echo $x; ?>"><?php echo $dataObat[$u]['penjualan_detail_jumlah'];?></td>
                    <td class="bot <?php echo $x; ?> total"><?php echo number_format(($dataObat[$u]['penjualan_detail_harga_jual']*1.1), 0, ',', '.');?></td>
                    <td class="bot <?php echo $x; ?> total"><?php echo number_format($dataObat[$u]['penjualan_detail_total'], 0, ',', '.');?></td>
               </tr>
               <?php
               
               } 
               if($dataPenjualan[$i]['penjualan_biaya_racikan'] != 0 && $dataPenjualan[$i]['penjualan_biaya_racikan'] != null){?>
              
              <tr>
                <td colspan="5">Biaya Racikan : </td>
                <td colspan="5" class="nom"><?php echo currency_format($dataPenjualan[$i]['penjualan_biaya_racikan']);?></td>
              </tr>
              <?php } 
               $totalPen = $dataPenjualan[$i]['penjualan_total'] + $dataPenjualan[$i]['penjualan_biaya_racikan'];
              ?>
               <tr>
                    <td colspan="4">Sub total</td>
                    <td class="red total"></td>
                    <td colspan="2"></td>
                    <td colspan="8" class="red total"><?php echo number_format($totalPen, 0, ',', '.'); ?></td>
               </tr>
               <?php 
               $jumlah += $totalPen;
          } 
               $sql = "select SUM(penjualan_total) as tot, sum(penjualan_tuslag) as tuslag,sum(penjualan_biaya_racikan) as racikan from apotik.apotik_penjualan where penjualan_terbayar = 'n' and id_pembayaran = ".QuoteValue(DPE_CHAR,$IdPembayaran['id_pembayaran']);
               $rs = $dtaccess->Execute($sql);
               $dataSUM = $dtaccess->Fetch($rs);
               ?>
               <tr class="tt">
                    <td colspan="4" class="nobor">T O T A L</td>
                    <td class="nobor noba red total"></td>
                    <td colspan="2" class="nobor noba"></td>
                    <td class="noba red total"><?php echo number_format($jumlah, 0, ',', '.'); ?></td>
               </tr>
          </tbody>
     </table>
     </div>
</body>
</html>