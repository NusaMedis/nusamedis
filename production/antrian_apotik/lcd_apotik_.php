<?php
 	require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");                                                                  
     require_once($LIB."expAJAX.php");    
     require_once($LIB."tampilan.php");
 
 $poli = "33"; //POLI APOTIK IRJ

 $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];

$skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
    
     $sql_where[] = "date(a.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(a.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
      if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
 
     $sql = "select a.*,reg_status, b.cust_usr_kode, d.reg_tipe_rawat, d.id_poli_asal, c.jenis_nama, d.id_pembayaran,e.poli_nama, a.is_terima from apotik.apotik_penjualan a
             left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
             left join global.global_jenis_pasien c on a.id_jenis_pasien = c.jenis_id 
             left join klinik.klinik_registrasi d on d.reg_id = a.id_reg
             left join global.global_auth_poli e on e.poli_id=d.id_poli_asal
             where a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
     $sql .= " and ".$sql_where;
     $sql .= "order by penjualan_create desc";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
    // echo $sql;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Custom Theme Style -->
    <link href="<?php echo $ROOT; ?>assets/build/css/custom.min.css" rel="stylesheet">
	<!-- jQuery -->
    <script src="<?php echo $ROOT; ?>assets/vendors/jquery/dist/jquery.min.js"></script>
	
    <!-- Bootstrap -->
    <script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
	
	<!-- Bootstrap -->

    <link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<script>
var refreshId = setInterval(function()
{
$('#responsecontainer').load('lcd_apotik.php');
}, 1000);
	</script>
<body bgcolor="#FFF">
	<div id="responsecontainer">
    <table width="100%">
                        <tr>                       
     						<td><font size="6"><center>DAFTAR ANTRIAN APOTIK</center></font></td>
                        </tr>
                        <tr>                       
     						<td><font size="5"><center>RSIA MUSLIMAT JOMBANG</center></font></td>
                        </tr>
                        <tr>                       
     						<td>&nbsp;</td>
                        </tr>
    </table>
<table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
                      <thead>
                        <tr>                       
     						<th class="column-title" width="1%">No</th>
                           <th class="column-title" width="5%">No RM</th>
                           <th class="column-title" width="20%">Nama Pasien</th>
                           <th class="column-title" width="10%">Poli / Kamar</th>
                           <th class="column-title" width="10%">Tanggal</th>
                           <th class="column-title" width="10%">No Faktur</th>
                           <th class="column-title" width="32%">Keterangan</th>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   
							//cari poli asal atau kamar sebelumnya
						     	if($dataTable[$i]["reg_tipe_rawat"]=='I'){
							     	$sql = "select c.gedung_rawat_nama, d.kamar_nama from klinik.klinik_rawatinap b
							     			left join klinik.klinik_registrasi a on a.reg_id = b.id_reg
							     			left join global.global_gedung_rawat c on b.id_gedung_rawat = c.gedung_rawat_id
							     			left join klinik.klinik_kamar d on b.id_kamar = d.kamar_id
							     			where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_pembayaran"]);
							     	$rs = $dtaccess->Execute($sql);
							     	$dataKamar = $dtaccess->Fetch($rs);
						//echo $sql;
							     	$pasienasal = $dataKamar["gedung_rawat_nama"]." (".$dataKamar["kamar_nama"].")";

						     	}elseif($dataTable[$i]["reg_tipe_rawat"]=='J'){
						     		$sql = "select b.poli_nama from klinik.klinik_registrasi a
							     			left join global.global_auth_poli b on b.poli_id = a.id_poli_asal
							     			where a.reg_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_reg"]);
							     	$rs = $dtaccess->Execute($sql);
							     	$dataKamar = $dtaccess->Fetch($rs);
                                   // echo "status reg".$dataTable[$i]["reg_status"];
							     	//echo $sql;
							     	if($dataKamar["poli_nama"]==''){
							     		$pasienasal = "Penjualan Bebas";
							     	}else{
							     	$pasienasal = $dataKamar["poli_nama"];	
							     	}
						     	}else{
									$pasienasal = "I G D";
								}
								if($dataTable[$i]["is_terima"]=='i'){
									$keterangan = " Obat /Farmasi Diinput Ke System";
								}elseif($dataTable[$i]["is_terima"]=='n'){
									$keterangan = " Obat /Farmasi Sedang Disiapkan";
								}elseif ($dataTable[$i]["is_terima"]=='s') {
									$keterangan = " Obat /Farmasi Siap Diserahkan";
								}elseif ($dataTable[$i]["is_terima"]=='y'){
									$keterangan="Obat telah Diserahkan";
								}

                          	?>
                          
                          <tr class="even pointer">
                            
                        	<td class=" "> <?php echo $i+1?></td>
                        
                          <td><? echo $dataTable[$i]["cust_usr_kode"];?></td>
                          <td><? echo $dataTable[$i]["cust_usr_nama"];?></td>
                          <td><? echo $pasienasal;?></td>
                          <td><? echo $dataTable[$i]["penjualan_create"];?></td>
                          <td><? echo $dataTable[$i]["penjualan_nomor"];?></td>
                          <td><? echo $keterangan;?></td>
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
                </div>
</body>
</html>