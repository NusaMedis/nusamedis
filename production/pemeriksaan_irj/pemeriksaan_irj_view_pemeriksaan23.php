<?php
error_reporting();
ini_set('display_errors', 1);

     // Library
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."expAJAX.php"); 
     require_once($LIB."tampilan.php");
   
      
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $tglSekarang = date("Y-m-d");
     $userId = $auth->GetUserId();
     /*if(!$auth->IsAllowed("kas_pembayaran_pemeriksaan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kas_pembayaran_pemeriksaan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }  */

     $_x_mode = "New";
     $wsinacbgserver = $_SERVER['HTTP_HOST']."/annisa_real/production";


     $tipeRawatLabel["J"] = "Rawat Jalan";
     $tipeRawatLabel["G"] = "Rawat Darurat";
     $tipeRawatLabel["I"] = "Rawat Inap";
     
     
     $statusInacbgStatus["k"] = "Belum Diinput";
     $statusInacbgStatus["n"] = "Sudah Askep";
     $statusInacbgStatus["y"] = "Sudah As Med";
     $statusInacbgStatus["b"] = "Sudah Diinput";
     
     
     
     // KONFIGURASI
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_GET["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];

    
      

      $sql = "select a.reg_no_antrian, a.reg_tanggal,a.reg_id,a.reg_kode_trans,a.reg_waktu,a.reg_status,a.reg_tipe_jkn,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama,perusahaan_nama, a.reg_status_pasien, q.usr_name
    from
    klinik.klinik_registrasi a left join
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
    global.global_auth_poli c on a.id_poli = c.poli_id left join
    global.global_auth_user_poli d on a.id_poli = d.id_poli
    left join global.global_jkn e on a.reg_tipe_jkn = e.jkn_id
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
    left join global.global_auth_user q on q.usr_id = a.id_dokter
    ";
  $sql .= " where id_pembayaran notnull  and c.poli_tipe = 'J' and a.reg_status <>' '  and d.id_usr = '$userId' and a.id_dep = '$depId' and a.reg_tanggal ='$tglSekarang' and  reg_status <> 'E3'";
  $sql .= " order by a.reg_no_antrian asc";

  // echo $sql;   
  $dataTable = $dtaccess->FetchAll($sql);


  $table = new InoTable("table","100%","left");
       $skr = date("d-m-Y");
       
       if(!$_GET["tgl_awal"]) $_GET["tgl_awal"] = $skr;
       if(!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] = $skr;
        //if($_GET["tgl_awal"]) $_GET["tgl_awal"] =  $_GET["tgl_awal"];
        //if($_GET["tgl_akhir"]) $_GET["tgl_akhir"] =  $_GET["tgl_akhir"];


     if($_GET["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_GET["cust_usr_kode"]."%");
     if($_GET["cust_usr_nama"])  $sql_where[] = "UPPER(b.cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_nama"])."%");
     if($_GET["reg_no_sep"])  $sql_where[] = "c.reg_no_sep like".QuoteValue(DPE_CHAR,"%".$_GET["reg_no_sep"]."%");
     
      if($_GET["reg_tipe_rawat"])  $sql_where[] = "c.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$_GET["reg_tipe_rawat"]);

     if(!empty($_GET["tgl_awal"]))  $sql_where[] = " DATE(a.reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if(!empty($_GET["tgl_akhir"]))$sql_where[] = " DATE(a.reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     // $sql_where[] = " a.id_dep =".QuoteValue(DPE_CHAR,$depId);
     
     if ($sql_where[0]) 
     $sql_where = implode(" and ",$sql_where);


     
     
     if($_GET["btnLanjut"] || $_GET["tgl_awal"])   
     {   
         $sql = "select a.reg_no_antrian, a.reg_tanggal,a.reg_id,a.reg_kode_trans,a.reg_waktu,a.reg_status,a.reg_tipe_jkn,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama,perusahaan_nama, a.reg_status_pasien, q.usr_name
    from
    klinik.klinik_registrasi a left join
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
    global.global_auth_poli c on a.id_poli = c.poli_id left join
    global.global_auth_user_poli d on a.id_poli = d.id_poli
    left join global.global_jkn e on a.reg_tipe_jkn = e.jkn_id
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
    left join global.global_auth_user q on q.usr_id = a.id_dokter
    ";
  $sql .= " where id_pembayaran notnull  and c.poli_tipe = 'J' and a.reg_status <>' '  and d.id_usr = '$userId' and a.id_dep = '$depId'  and  reg_status <> 'E3' and ".$sql_where."";
  // $sql .= $sql_where;
  $sql .= " order by a.reg_no_antrian asc";

  // echo $sql;   
  $dataTable = $dtaccess->FetchAll($sql);   
         // echo $sql;
    } 

         
         
    $row = -1;
    
          $tableHeader = "&nbsp;Ass Keperawatan IGD";
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Proses";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
          /*
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Askep";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Asmed";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
          $counterHeader++; */
          
         /* $tbHeader[0][$counterHeader][TABLE_ISI] = "Buat Cicilan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++; */
 
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Registrasi";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Lahir";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          
          

        //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;   
                                                                                          
      for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {


          $HistorycodingPage = "pemeriksaan_irj_view_pemeriksaan20230114.php?id_reg_pasien=".$dataTable[$i]["reg_id"]."&registrasi_status=".$dataTable[$i]["reg_status"]."";


          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$HistorycodingPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cari.png" alt="Coding" title="Coding" border="0"/></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

           
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;                                                                
                     switch ($dataTable[$i]['reg_status']) {
                      case 'E0':
                        # code...
                        $tipe_emr = "Belum Dilayani";
                        break;
                      case 'E1':
                        # code...
                        $tipe_emr = "Sampai di Poli";
                        break;
                      case 'E2':
                        # code...
                        $tipe_emr = "Sudah Dilayani";
                        break;
                       default:
                        # code...
                        break;
                    }
          $tbContent[$i][$counter][TABLE_ISI] = $tipe_emr;
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

//          } //END JIKA SUDAH BAYAR SEMENTYARA DIHILANGKAN

          unset($sqlBayar);
          unset($dataBayar);
      } 
      
          //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
  
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
    <script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
    <script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <script type="text/javascript">
      $(function () {
        $('#cust_usr_kode').focus();
      })
    </script>
   <script language="JavaScript"> 
          
        var _wnd_new;
        function BukaWindow(url,judul)
        {
            if(!_wnd_new) {
                       _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=1000,left=100,top=10');
             } else {
                  if (_wnd_new.closed) {
                       _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=1000,left=100,top=10');
                  } else {
                       _wnd_new.focus();
                  }
             }
             return false;
        }

        function CetakKlaim(sep,id,id_reg){
        BukaWindow('<? echo $cetakklaimlink;?>?sep='+sep+'&usernik='+id+'&id_reg='+id_reg+'','Cetak Klaim');
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
                <h3>Rawat Darurat</h3>
              </div>
            </div>
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Assesmen Keperawatan IGD</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  <form name="frmFind" method="GET" action="<?php echo $_SERVER["PHP_SELF"]?>">
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Pulang(DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
              <input  id="tgl_awal" name="tgl_awal" type='text' class="form-control" value="<?php echo $_GET["tgl_awal"]; ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
      
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal Pulang(DD-MM-YYYY)</label>
            <div class='input-group date' id='datepicker2'>
              <input  id="tgl_akhir" name="tgl_akhir"  type='text' class="form-control" value="<?php echo $_GET["tgl_akhir"] ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>             
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
            <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_GET["cust_usr_nama"],false,false);?>
            </div>
            
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
            <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_GET["cust_usr_kode"],false,false);?>
            </div>       
            
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. SEP </label>
                        <?php echo $view->RenderTextBox("reg_no_sep","reg_no_sep",30,200,$_GET["reg_no_sep"],false,false);?>
                  </div>
            <input type="hidden" name="_jenis" id="_jenis" size="40" value="<?php echo $_GET["_jenis"];?>" onKeyPress="return submitenter(this,event)"/>
           
           <!--       
          <div class="col-md-4 col-sm-6 col-xs-12">
          <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat </label>
          		<select class="select2_single form-control" name="reg_tipe_rawat" id="reg_tipe_rawat" onKeyDown="return tabOnEnter(this, event);">
				<option value="">.::Semua Tipe Rawat ::.</option>
				<option value="G" <?php if($_GET["reg_tipe_rawat"]=='G') echo "selected"; ?>>Rawat Darurat</option>
				<option value="J" <?php if($_GET["reg_tipe_rawat"]=='J') echo "selected"; ?>>Rawat Jalan</option>
				<option value="I" <?php if($_GET["reg_tipe_rawat"]=='I') echo "selected"; ?>>Rawat Inap</option>
				
							</select>
                  </div>  -->
                    
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <td class="tablecontent-odd" colspan="5"><input type="submit" name="btnLanjut" value="Lanjut" class="submit"></td>
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
                      <script>document.frmSearch._kode.focus();</script> 
                      <body onLoad="mTimer();">
                      <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title" width="<?php echo $tbHeader[0][$k][TABLE_WIDTH];?>"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
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
                    
                    
    <input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
                
                    
                    
                    
                    
                    </body>          
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