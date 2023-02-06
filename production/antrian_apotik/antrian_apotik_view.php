<?php
 //LIBRARY 
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");                                                                  
     require_once($LIB."expAJAX.php");    
     require_once($LIB."tampilan.php");
 
     //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();             
     $auth = new CAuth();
     $skr = date("Y-m-d");
     $time = date("H:i:s");
     $usrId = $auth->GetUserId();	
     $table = new InoTable("table","100%","left");    
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
    // $poli = $auth->GetPoli();
     //DIPATEN SEMENTARA

     $thisPage = "antrian_apotik_view.php?";
     $poli = "33"; //POLI APOTIK IRJ
     
	   $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
     $_POST["id_poli"] = $poli;
     // PRIVILLAGE
   if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
     
     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
    
     $sql_where[] = "date(a.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(a.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
     if($_POST["cust_usr_nama"]) $sql_where[] = " upper(a.cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%");
     if($_POST["cust_usr_kode"]) $sql_where[] = " b.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
     if($_POST["penjualan_nomor"]) $sql_where[] = "upper(a.penjualan_nomor) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["penjualan_nomor"])."%");

     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
 
     $sql = "select a.*,reg_status, b.cust_usr_kode, d.reg_tipe_rawat, d.id_poli_asal, c.jenis_nama, d.id_pembayaran,e.poli_nama from apotik.apotik_penjualan a
             left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
             left join global.global_jenis_pasien c on a.id_jenis_pasien = c.jenis_id 
             left join klinik.klinik_registrasi d on d.reg_id = a.id_reg
             left join global.global_auth_poli e on e.poli_id=d.id_poli_asal
             where a.id_dep =".QuoteValue(DPE_CHAR,$depId)." 
             and a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
     $sql .= " and ".$sql_where;
     $sql .= "order by penjualan_create desc";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
 
  $tableHeader = "Proses Obat Pasien";
  
$_POST["id_cust_usr"]= $_GET["idcust"];

     if($_GET["mulai"]){
     	
     	$sql = "update apotik.apotik_penjualan set is_terima ='m' where penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
     	$rs = $dtaccess->Execute($sql);

     	$sql = "update klinik.klinik_registrasi set reg_status = 'A1' where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     	$rs = $dtaccess->Execute($sql);

     	            // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
          $dbField[8] = "id_waktu_tunggu_status";
            
          $waktuTungguId = $dtaccess->GetTransID(); 
             
          $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,'A1');
          $dbValue[6] = QuoteValue(DPE_CHAR,"Obat / Farmasi Disiapkan ");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,'A1');
          
                
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");    

         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey); 
     }
 	

     if($_GET["selesai"]){
     	
     	$sql = "update apotik.apotik_penjualan set is_terima ='s' where penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
     	$rs = $dtaccess->Execute($sql);

     	$sql = "update klinik.klinik_registrasi set reg_status = 'A2' where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     	$rs = $dtaccess->Execute($sql);

$sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $sql .= " order by klinik_waktu_tunggu_when_create desc ";
     $rs = $dtaccess->Fetch($sql);
     $durasi = durasi($rs["prev"],date("Y-m-d H:i:s"));
     $durasiDetik = durasiDetik($rs["prev"],date("Y-m-d H:i:s"));

   
     	            // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
          $dbField[8] = "klinik_waktu_tunggu_durasi";
          $dbField[9] = "klinik_waktu_tunggu_durasi_detik";
          $dbField[10] = "id_waktu_tunggu_status";

          $waktuTungguId = $dtaccess->GetTransID(); 
             
          $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,'A2');
          $dbValue[6] = QuoteValue(DPE_CHAR,"Obat / Farmasi Telah Siap Diserahkan");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$durasi);
          $dbValue[9] = QuoteValue(DPE_NUMERIC,$durasiDetik);
          $dbValue[10] = QuoteValue(DPE_CHAR,"A2");
                
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");    

         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey); 
     }

     if($_GET["serahkan"]){
     	
     	$sql = "update apotik.apotik_penjualan set is_terima ='y' where penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
     	$rs = $dtaccess->Execute($sql);

     	$sql = "update klinik.klinik_registrasi set reg_status = 'A3' where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     	$rs = $dtaccess->Execute($sql);

     	            // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
          $dbField[8] = "id_waktu_tunggu_status";
            
          $waktuTungguId = $dtaccess->GetTransID(); 
             
          $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,'A3');
          $dbValue[6] = QuoteValue(DPE_CHAR,"Obat / Farmasi Telah Diserahkan");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,'A3');
          
                
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");    

         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey); 
     }
?>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script language="JavaScript">

</script>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Apotik</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Proses Obat Pasien</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form  name="frmFind" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
						<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input  id="tanggal_awal" name="tanggal_awal" type='text' class="form-control" value="<?php echo $_POST["tanggal_awal"] ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  id="tanggal_akhir" name="tanggal_akhir"  type='text' class="form-control" value="<?php echo $_POST["tanggal_akhir"] ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
				     <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama</label>
                        <input  id="cust_usr_nama" name="cust_usr_nama" type='text' class="form-control" value="<?php echo $_POST["cust_usr_nama"] ?>"  />
					</div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No RM</label>
                        <input  id="cust_usr_kode" name="cust_usr_kode" type='text' class="form-control" value="<?php echo $_POST["cust_usr_kode"] ?>"  />
					</div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No Faktur</label>
                        <input  id="penjualan_nomor" name="penjualan_nomor" type='text' class="form-control" value="<?php echo $_POST["penjualan_nomor"] ?>"  />
					</div>
								    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
            			<input type="submit" name="btnLanjut" id="btnLanjut" value="Lanjut" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">&nbsp;
					</div>
				    
					<div class="clearfix"></div>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table id="datatable-responsive" class="table table-striped table-bordered nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>                       
                           <th class="column-title" width="1%">Mulai Disiapkan</th>
                           <th class="column-title" width="1%">Siap Diserahkan</th>
                           <th class="column-title" width="1%">Serahkan</th>
                           <th class="column-title" width="5%">No RM</th>
                           <th class="column-title" width="20%">Nama Pasien</th>
                           <th class="column-title" width="10%">Poli / Kamar</th>
                           <th class="column-title" width="10%">Tanggal</th>
                           <th class="column-title" width="10%">No Faktur</th>
                           <th class="column-title" width="32%">Item / Barang</th>
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
							     	//echo $sql;
							     	if($dataKamar["poli_nama"]==''){
							     		$pasienasal = "Penjualan Bebas";
							     	}else{
							     	$pasienasal = $dataKamar["poli_nama"];	
							     	}
						     	}else{
									$pasienasal = "I G D";
						     	}
						     
						     //data item 
						     	$sql = "select a.id_item, a.item_nama, a.penjualan_detail_jumlah, b.item_racikan from apotik.apotik_penjualan_detail a 
						     			left join logistik.logistik_item b on a.id_item = b.item_id 
						     			where id_penjualan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["penjualan_id"])."
						     			order by a.item_nama asc ";
						     	$rs = $dtaccess->Execute($sql);
						     	$dataItemJual = $dtaccess->FetchAll($rs);


                          	?>
                          
                          <tr class="even pointer">
                            <? if($dataTable[$i]["reg_status"]<>'A1' && $dataTable[$i]["reg_status"]<>'A2' && $dataTable[$i]["reg_status"]<>'A3'){ ?>
                            <td class=" "><a href="<? echo $thisPage;?>mulai=1&id=<?echo $dataTable[$i]["penjualan_id"]?>&id_reg=<? echo $dataTable[$i]["id_reg"]?>&idcust=<?echo $dataTable[$i]["id_cust_usr"]?>"><img hspace="2" src="<? echo $ROOT?>gambar/finder.png" align="top" alt="Di Siapkan" title="Di Siapkan" border="0" ></a></td>
                        <? }else{ ?>
                        <td class=" "></td>
                          <? }?>
                          <? if($dataTable[$i]["reg_status"]=='A1'){ ?>
                            <td class=" "><a href="<? echo $thisPage?>selesai=1&id=<? echo $dataTable[$i]["penjualan_id"]?>&id_reg=<? echo$dataTable[$i]["id_reg"]?>&idcust=<?echo $dataTable[$i]["id_cust_usr"]?>"><img hspace="2" src="<? echo $ROOT?>gambar/finder.png" align="top" alt="Siap Serahkan" title="Siap Serahkan" border="0" ></a></td>
                        <? }else{ ?>
                        <td class=" "></td>
                          <? }?>
                          <? if($dataTable[$i]["reg_status"]=='A2'){ ?>
                            <td class=" "><a href="<? echo$thisPage?>serahkan=1&id=<? echo $dataTable[$i]["penjualan_id"]?>&id_reg=<? echo $dataTable[$i]["id_reg"]?>&idcust=<?echo $dataTable[$i]["id_cust_usr"]?>"><img hspace="2" src="<? echo $ROOT?>gambar/finder.png" align="top" alt="Siap Serahkan" title="Siap Serahkan" border="0" ></a></td>
                        <? }else{ ?>
                        <td class=" "></td>
                          <? }?>
                          <td><? echo $dataTable[$i]["cust_usr_kode"];?></td>
                          <td><? echo $dataTable[$i]["cust_usr_nama"];?></td>
                          <td><? echo $pasienasal;?></td>
                          <td><? echo $dataTable[$i]["penjualan_create"];?></td>
                          <td><? echo $dataTable[$i]["penjualan_nomor"];?></td>
                          <td>
                          	<table witdh="100">
                          		<? for($a=0,$b=count($dataItemJual);$a<$b;$a++) {   
                          			?>
                          		<tr>
                          			<td><?php echo $dataItemJual[$a]["item_nama"]?>
                          				<? if($dataItemJual[$a]["item_racikan"]=='y'){ 
                          					$sql = "select item_nama, detail_racikan_jumlah from apotik.apotik_detail_racikan where id_nama_racikan = ".QuoteValue(DPE_CHAR,$dataItemJual[$a]["id_item"]);
                          					$rs = $dtaccess->Execute($sql);
                          					$dataDetailRacikan = $dtaccess->FetchAll($rs); ?>
										<table witdh="100">
                          					<? for($x=0,$y=count($dataDetailRacikan);$x<$x;$x++) { 
                          					?>
                          					<tr><td><?php echo $dataDetailRacikan[$x]["item_nama"]?></td><td><?php echo $dataDetailRacikan[$x]["detail_racikan_jumlah"]?></td></tr>
                          				<? } ?>
                          			</table>
                          			<? } ?>
                          			</td>
                          			<td align ="right"><?php echo $dataItemJual[$a]["penjualan_detail_jumlah"]?></td>
                          		</tr>
                          	<? } ?>
                          	</table>
                          </td>
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
					<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>