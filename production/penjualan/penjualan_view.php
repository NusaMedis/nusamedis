<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();    
//	   $poli = $auth->GetPoli();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();

     //DIPATEN SEMENTARA
     if ($_POST['apotik'])$poli = $_POST['apotik']; //POLI APOTIK IRJ
          
	   $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif 
     
    $sql =" select * from apotik.apotik_conf where conf_id = 'asa' ";
    $rs_edit = $dtaccess->Execute($sql);
    $dataOnOff = $dtaccess->Fetch($rs_edit);
     
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
     $skrR = date("Y-m-d");
     $kemarin = date('Y-m-d', strtotime("-1 day", strtotime(date("Y-m-d"))));
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
    

     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
 
     $sql = "select a.*, b.cust_usr_kode, b.cust_usr_tanggal_lahir, c.jenis_nama, d.id_pembayaran, d.reg_utama, e.poli_nama, e.poli_tipe, a.id_resep 
             from apotik.apotik_penjualan a
             left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
             left join global.global_jenis_pasien c on a.id_jenis_pasien = c.jenis_id 
             left join klinik.klinik_registrasi d on d.reg_id = a.id_reg
             left join global.global_auth_poli e on e.poli_id=d.id_poli_asal
             where (penjualan_id in(select id_penjualan from apotik.apotik_penjualan_detail) or id_resep is not null) and a.is_terima!='y' and cust_usr_id <> '100' and penjualan_flag<>'R' and d.reg_jenis_pasien = '2' 
             and a.id_gudang like ".QuoteValue(DPE_CHAR,'%'.$theDep.'%');
     $sql .= " and ((date(a.penjualan_create) >= '$skrR' and date(a.penjualan_create) <= '$skrR') 
               or 
               (date(a.penjualan_create) >= '$kemarin' and date(a.penjualan_create) <= '$skrR' and id_resep is not null and penjualan_grandtotal is null))";
     $sql .= "order by penjualan_create desc";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;
     //die();
     $isAllowedDel = $auth->IsAllowed("pros_penjualan_dlm",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("pros_penjualan_dlm",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("pros_penjualan_dlm",PRIV_CREATE);
     
     //echo $sql;
     //die();
     
     // --- construct new table ---- //
     // VIEW PENJUALAN OBAT
     $tableHeader = "Penjualan Obat Pasien";
     $counterHeader = 0;
     /*if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }*/

    // if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Item";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
    // }

     /*
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Order";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }  */

     

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak E-Resep";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Resep Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";     
     $counterHeader++;
     
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Siap";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";     
     $counterHeader++;
	 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Serahkan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal CPPT";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Nota";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik/Kamar/Triase Asal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
          //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

      $sql = "select reg_tipe_rawat from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]['reg_utama']);
      $TipeRawat = $dtaccess->Fetch($sql);

      $sql = "SELECT rawat_terapi from klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]['id_resep']);
      $catatanDokter = $dtaccess->Fetch($sql);

      $sql = "SELECT rawat_cppt_data, rawat_cppt_when_update from klinik.klinik_perawatan_cppt where rawat_cppt_id = ".QuoteValue(DPE_CHAR, $dataTable[$i]["id_resep"]);
      $CPPT = $dtaccess->Fetch($sql);

      $tanggal_cppt = ($CPPT) ? date_format(date_create($CPPT['rawat_cppt_when_update']), 'd-m-Y') : '';

      $dataCPPT = ($CPPT) ? unserialize($CPPT['rawat_cppt_data']) : [] ;

      $dataCPPT['terapi'] = ($dataCPPT['terapi']) ? $dataCPPT['terapi'] : $dataCPPT['terapiApotik'] ;

      $dataCPPT['terapi'] = ($dataTable[$i]['tipe_resep'] == 'I') ? $dataCPPT['terapiInfus'] : $dataCPPT['terapi'] ;

      $catatanDokter['rawat_terapi'] = ($catatanDokter['rawat_terapi']) ? $catatanDokter['rawat_terapi'] : $dataCPPT['terapi'] ;

      $sql = "select * from gl.gl_buffer_transaksi where is_posting = 'y' and id_pembayaran_det = ".QuoteValue(DPE_CHAR,$dataTable[$i]['penjualan_id']);
      $Jurnal = $dtaccess->Fetch($sql);

      $sellPage = "penjualan.php?";              
      if($dataTable[$i]["penjualan_terbayar"]=='n' && $Jurnal['is_posting'] == '' && ($TipeRawat['reg_tipe_rawat'] == 'G' || $TipeRawat['reg_tipe_rawat'] == 'J')){
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$sellPage.'kode='.$enc->Encode($dataTable[$i]["cust_usr_kode"]).'&transaksi='.$enc->Encode($dataTable[$i]["penjualan_id"]).'&idreg='.$enc->Encode($dataTable[$i]["id_reg"]).'&id_pembayaran='.$dataTable[$i]["id_pembayaran"].'"><img hspace="2" src="'.$ROOT.'gambar/finder.png" align="top" alt="Edit" title="Edit" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
       } elseif ($TipeRawat['reg_tipe_rawat'] == 'I' && $Jurnal['id_tra'] == '') {
         $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$sellPage.'kode='.$enc->Encode($dataTable[$i]["cust_usr_kode"]).'&transaksi='.$enc->Encode($dataTable[$i]["penjualan_id"]).'&idreg='.$enc->Encode($dataTable[$i]["id_reg"]).'&id_pembayaran='.$dataTable[$i]["id_pembayaran"].'"><img hspace="2" src="'.$ROOT.'gambar/finder.png" align="top" alt="Edit" title="Edit" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
       }
       else {
       
         $tbContent[$i][$counter][TABLE_ISI] = '';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
       }

       

        

        if(!empty($dataTable[$i]["id_fol"]) && $dataTable[$i]["penjualan_grandtotal"]>0){ 
        $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetak(\''.$dataTable[$i]["penjualan_id"].'\');"/>';
        }else{
         $tbContent[$i][$counter][TABLE_ISI] = ''; 
        }
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;

        if(!empty($dataTable[$i]["id_fol"])){ 
        $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="CetakResep(\''.$dataTable[$i]["penjualan_id"].'\');"/>';
        }else{
         $tbContent[$i][$counter][TABLE_ISI] = ''; 
        }
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;

        if($catatanDokter["rawat_terapi"] != '' || $dataTable[$i]['tipe_resep'] == 'I'){
        $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetakCatatanDokter(\''.$dataTable[$i]["id_resep"].'\', \''.$dataTable[$i]["penjualan_id"].'\');"/>';
        }else{
         $tbContent[$i][$counter][TABLE_ISI] = ''; 
        }
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
		  
		  if($dataTable[$i]["is_terima"]=='s'){
           $tbContent[$i][$counter][TABLE_ISI] = '';
		   }else{
			 $tbContent[$i][$counter][TABLE_ISI] = '<a href="siap_obat.php?kode='.$enc->Encode($dataTable[$i]["cust_usr_kode"]).'&id='.$enc->Encode($dataTable[$i]["penjualan_id"]).'&idreg='.$enc->Encode($dataTable[$i]["id_reg"]).'&id_pembayaran='.$dataTable[$i]["id_pembayaran"].'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/jempol.png" style="cursor:pointer" alt="Obat Siap" title="Obat Siap" border="0"/>';
           }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
		  
          if($dataTable[$i]["penjualan_terbayar"]=='y'){
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="serah_obat.php?kode='.$enc->Encode($dataTable[$i]["cust_usr_kode"]).'&id='.$enc->Encode($dataTable[$i]["penjualan_id"]).'&idreg='.$enc->Encode($dataTable[$i]["id_reg"]).'&id_pembayaran='.$dataTable[$i]["id_pembayaran"].'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/give.png" style="cursor:pointer" alt="Serahkan" title="Serahkan" border="0"/>';
          }else{
             $tbContent[$i][$counter][TABLE_ISI] = '';
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

          $date = explode(" ", $dataTable[$i]["penjualan_create"]);
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($date[0])."&nbsp;".$date[1]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $tanggal_cppt; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".date_db($dataTable[$i]["cust_usr_tanggal_lahir"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;          
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["poli_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;         

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["jenis_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_grandtotal"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;    

          if($dataTable[$i]["poli_tipe"] == 'I') $color = 'blue';
          if($dataTable[$i]["poli_tipe"] == 'J') $color = 'red';
          if($dataTable[$i]["poli_tipe"] == 'G') $color = 'purple';
          
          $styleRow[$i] = ($dataTable[$i]["penjualan_grandtotal"] == 0 && ($catatanDokter["rawat_terapi"] != '' || $dataTable[$i]['tipe_resep'] == 'I')) ? "color: $color; font-weight: bold" : "" ;

     }
     $colspan = count($tbHeader[0]);
 
     /*if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'tambah=1\'">&nbsp;';
     }*/
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;

     $sql = "select a.* from global.global_auth_poli a left join global.global_auth_user_poli b on b.id_poli = a.poli_id where poli_tipe = 'A' and id_usr = ".QuoteValue(DPE_CHAR,$userId);
     $dataApotik = $dtaccess->FetchAll($sql);
?>


<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script language="JavaScript">

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
function ProsesCetak(id) {
 
  BukaWindow('penjualan_cetak.php?id='+id+'','Nota');
	//document.location.href='<?php echo $thisPage;?>';
}

function CetakResep(id) {
 
  BukaWindow('etiket_cetak.php?id='+id+'','Nota');
  //document.location.href='<?php echo $thisPage;?>';
}

function ProsesCetakCatatanDokter(id, penj_id) {
 
  BukaWindow('catatan_cetak.php?id='+id+'&penj='+penj_id,'Nota');
  //document.location.href='<?php echo $thisPage;?>';
}

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
                    <h2>Penjualan Obat Pasien Umum</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form  name="frmFind" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
						<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input  id="tanggal_awal" name="tanggal_awal" type='text' class="form-control" value="<?php echo $_POST["tanggal_awal"] ?>"  readonly />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  id="tanggal_akhir" name="tanggal_akhir"  type='text' class="form-control" value="<?php echo $_POST["tanggal_akhir"] ?>"  readonly />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Apotik</label>
              <select class="form-control" name="apotik">
               <!--  <option value="">Semua Apotik</option> -->
               <?php for ($i=0; $i < count($dataApotik); $i++) { ?>
                <option value="<?php echo $dataApotik[$i]['poli_id'] ?>" <?php if ($dataApotik[$i]['poli_id'] == $_POST['apotik']) { echo 'selected'; } ?>><?php echo $dataApotik[$i]['poli_nama']; ?></option>
               <?php } ?>
              </select>
            </div>				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="button" name="btnTambah" value="Tambah Penjualan Obat Pasien" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href='penjualan.php'" />
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
					   <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                            <? } ?>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
                          
                          <tr class="even pointer">
                            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                            <td class=" " style="<?=$styleRow[$i]?>" align="<?php echo $tbContent[$i][$k][TABLE_ALIGN];?>"><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                            <? } ?>
                            
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
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




















