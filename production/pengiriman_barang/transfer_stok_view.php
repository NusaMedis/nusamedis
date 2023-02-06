<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");     
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $usrId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     //$theDep = $auth->GetNamaLogistik();
     $poli = $auth->GetPoli();


	 if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $editPage = "transfer_stok_edit.php?";
     $thisPage = "transfer_stok_view.php?";
     $detPage = "transfer_stok_detail_view.php?";
     $orderPage = "trans_beli_po_order.php?";
     $printPage = "transfer_cetak.php?";
     $approvePage = "transfer_stok_approve.php?";
     $skr = date("d-m-Y");
     
         $sql =" select * from apotik.apotik_conf where conf_id = 'asa' ";
    $rs_edit = $dtaccess->Execute($sql);
    $dataOnOff = $dtaccess->Fetch($rs_edit);
    
   // $dataOnOff["conf_logistik"] = 'y';
     
    if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
       } else if($_POST["klinik"]) { 
        $_POST["klinik"] = $_POST["klinik"]; 
         } else { 
          $_POST["klinik"] = $depId; 
          }              

     $addPage = "transfer_stok_edit.php?tambah=1&klinik=".$_POST["klinik"];
     

     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     $_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
     
     $sql_where[] = "a.transfer_tanggal_permintaan >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "a.transfer_tanggal_permintaan <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));

     $sql_where = implode(" and ",$sql_where);


     
     $sql = "select a.*,b.gudang_nama as dep_asal, c.gudang_nama as dep_tujuan
             from logistik_transfer_stok a
             left join logistik_gudang b on a.id_asal = b.gudang_id
             left join logistik_gudang c on a.id_tujuan = c.gudang_id";
     $sql .= " where  transfer_jenis='M' and transfer_tipe is null
              and a.is_approve_kirim ='y' and a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%")." and ".$sql_where;
     $sql .= " order by transfer_nomor asc";  
     echo $sql;   
      

     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Penerimaan Barang";
     
    
     // --- construct new table ---- //
     $counterHeader = 0;
    
     
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Det";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
    
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Approve";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
    
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Permintaan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Keluar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nomer";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah Item";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Asal Stok";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tujuan Stok";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keterangan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
    
     $jumHeader= $counterHeader;     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){          
    
          if($dataTable[$i]["transfer_flag"]=='n') {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detPage.'klinik='.$dataTable[$i]["id_dep"].'&id='.$enc->Encode($dataTable[$i]["transfer_id"]).'"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/cari.png" alt="Detail" title="Detil Transfer" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          else
          {
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
         
          if($dataTable[$i]["is_approve_terima"]=='n') {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$approvePage.'klinik='.$dataTable[$i]["id_dep"].'&id='.$enc->Encode($dataTable[$i]["transfer_id"]).'"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/aktif.png" alt="Approve" title="Approve" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          else
          {
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
       
          if($dataTable[$i]["is_approve_terima"]=='y') {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$printPage.'klinik='.$dataTable[$i]["id_dep"].'&id='.$dataTable[$i]["transfer_id"].'"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak" title="Cetak" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          else
          {
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          } 

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["transfer_tanggal_permintaan"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["transfer_tanggal_keluar"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["transfer_nomor"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
           $sql = "select count(a.transfer_detail_id) as jumlah
                   from logistik_transfer_stok_detail a
                   where a.id_transfer = ".QuoteValue(DPE_CHAR,$dataTable[$i]["transfer_id"]).
                   " and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);    
           //echo $sql;
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataJumlah = $dtaccess->Fetch($rs);
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataJumlah["jumlah"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_asal"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_tujuan"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["transfer_keterangan"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
         
     }
     
     $colspan = count($tbHeader[0]);

     
   if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
            //echo $sql;
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
            //echo $sql;
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id asc";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }
     
?>

<html lang="en">
<script language="JavaScript">
  function rejenis(kliniks) {
   document.location.href='transfer_stok_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }  
</script>
<script language="javascript" type="text/javascript">

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=900,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=900,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
</script>
  <?php require_once($LAY."header.php") ?>

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
                <h3><?php echo $tableHeader;?></h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Filter</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >		    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
              <div class='input-group date' id='datepicker'>
							<input name="tanggal_awal" type='text' class="form-control" 
							value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  name="tanggal_akhir"  type='text' class="form-control" 
							value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>			    
					<div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnLanjut" id="btnUrut" value="Lanjut" class="pull-right  btn btn-primary" class="submit">
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
					   <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
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
