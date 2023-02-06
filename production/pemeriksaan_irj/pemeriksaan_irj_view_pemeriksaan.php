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

    
      

      $sql = "select a.id_cust_usr,a.reg_no_antrian, a.reg_tanggal,a.reg_id,a.reg_kode_trans,a.reg_waktu,a.reg_status,a.reg_tipe_jkn,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama,perusahaan_nama, a.reg_status_pasien, q.usr_name
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
        $sql = "select jenis_id, jenis_nama
              from global.global_jenis_pasien
              where jenis_flag = 'y' order by jenis_id ASC";    
        $rs = $dtaccess->Execute($sql);
        $dataJenisBayar = $dtaccess->FetchAll($rs);

       $sql = "select b.poli_nama, b.poli_id 
              from global.global_auth_user_poli a 
              left join global.global_auth_poli b on a.id_poli = b.poli_id
              where a.id_usr = ".QuoteValue(DPE_CHAR,$userId)." and (b.poli_tipe<>'I' and b.poli_tipe<>'A' and b.poli_tipe<>'L') order by poli_nama ASC";    
      $rs = $dtaccess->Execute($sql);
      $dataPoli = $dtaccess->FetchAll($rs);


        
        
      if($_GET["btnLanjut"] ) {   

        if($_GET["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_GET["cust_usr_kode"]."%");
        if($_GET["cust_usr_nama"])  $sql_where[] = "UPPER(b.cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_nama"])."%");
        if($_GET["reg_kode_trans"])  $sql_where[] = "a.reg_kode_trans like".QuoteValue(DPE_CHAR,"%".$_GET["reg_kode_trans"]."%");
        if($_GET["dokter"])  $sql_where[] = "q.usr_name like".QuoteValue(DPE_CHAR,"%".$_GET["dokter"]."%");

        if($_GET["reg_status_pasien"] != '--')  $sql_where[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,$_GET["reg_status_pasien"]);
        if($_GET["jenis_nama"] != '--')  $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["jenis_nama"]);
        if($_GET["poli_nama"] != '--')  $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["poli_nama"]);
        if($_GET["reg_status"] != '--')  $sql_where[] = "a.reg_status = ".QuoteValue(DPE_CHAR,$_GET["reg_status"]);
  
        //  if(!empty($_GET["tgl_awal"]))  $sql_where[] = " DATE(a.reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
        //  if(!empty($_GET["tgl_akhir"]))$sql_where[] = " DATE(a.reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
        $sql_where[] = " a.reg_tanggal = '".$tglSekarang."'";
        // $sql_where[] = " a.id_dep =".QuoteValue(DPE_CHAR,$depId);
        
       if ($sql_where[0]) 
       $sql_where = "and ".implode(" and ",$sql_where);
         $sql = "select a.id_cust_usr,a.reg_no_antrian, a.reg_tanggal,a.reg_id,a.reg_kode_trans,a.reg_waktu,a.reg_status,a.reg_tipe_jkn,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama,perusahaan_nama, a.reg_status_pasien, q.usr_name
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
  $sql .= " where id_pembayaran notnull  and c.poli_tipe = 'J' and a.reg_status <>' '  and d.id_usr = '$userId' and a.id_dep = '$depId'  and  reg_status <> 'E3' ".$sql_where."";
  $sql .= $sql_where;
  $sql .= " order by a.reg_no_antrian asc";

  // echo $sql;   
  $dataTable = $dtaccess->FetchAll($sql);   
         // echo $sql;
    } 

         
         
    $row = -1;
    
          $tableHeader = "&nbsp;Rawat Jalan | Pemeriksaan Pasien";
          $counterHeader = 0;
          
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

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Sampai di Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Layani";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Reg";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak SPB";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Tagihan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Barcode";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Registrasi";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
            
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Lahir";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Baru / Lama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          
          

        //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;   
                                                                                          
      for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
        $reg_id = $dataTable[$i]["reg_id"];
        $reg_status = $dataTable[$i]["reg_status"];
        $id_pembayaran = $dataTable[$i]["id_pembayaran"];
        $id_cust_usr = $dataTable[$i]["id_cust_usr"];

        $poli = "'".$reg_id."','".$reg_status."'";
        $cetak = "'".$reg_id."'";
        $cetakspb = "'".$reg_id."','".$id_pembayaran."'";
        $cetak_tagihan = "'".$reg_id."','".$id_pembayaran."'";
        $cetakb = "'".$reg_id."','".$id_cust_usr."'";

        $HistorycodingPage = "pemeriksaan_irj_view_pemeriksaan20230114.php?id_reg_pasien=".$dataTable[$i]["reg_id"]."&registrasi_status=".$dataTable[$i]["reg_status"]."";

          
        $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;

        if ($dataTable[$i]["reg_status"] == "E0") {
            // $tbContent[$i][$counter][TABLE_ISI] = '<a class="btn" onclick="sampai('.$poli.')"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/aktif.png" alt="Sampai di poli" title="Sampai di poli" border="0"/></a>';
            $tbContent[$i][$counter][TABLE_ISI] = '<a class="btn btn-success" onclick="sampai('.$poli.')">Sampai di poli</a>';
        } else {
            $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
        }
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;

        if ($dataTable[$i]["reg_status"] == "E0") {
            $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
        } else {
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$HistorycodingPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cari.png" alt="Layani" title="Layani" border="0"/></a>';
        }
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;
   
        $tbContent[$i][$counter][TABLE_ISI] = '<a class="btn" onclick="cetak('.$cetak.')"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak Reg" title="Cetak Reg" border="0"/></a>';
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;
   
        $tbContent[$i][$counter][TABLE_ISI] = '<a class="btn" onclick="cetakspb('.$cetakspb.')"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak SPB" title="Cetak SPB" border="0"/></a>';
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;
   
        $tbContent[$i][$counter][TABLE_ISI] = '<a class="btn" onclick="cetak_tagihan('.$cetak_tagihan.')"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak Tagihan" title="Cetak Tagihan" border="0"/></a>';
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;
   
        $tbContent[$i][$counter][TABLE_ISI] = '<a class="btn" onclick="cetakb('.$cetakb.')"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak Barcode" title="Cetak Barcode" border="0"/></a>';
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;
          
        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["reg_waktu"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;

        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_kode"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;

        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["reg_kode_trans"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;

        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;

        $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;             
        
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;             
        
        
        $tbContent[$i][$counter][TABLE_ISI] = ($dataTable[$i]["reg_status_pasien"] == 'L')? 'Lama': 'Baru';
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;             
        
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
        $counter++;             
        
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
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
                <h3>
                  Rawat Jalan | Pemeriksaan Pasien
                </h3>
              </div>
            </div>
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Pasien Terdaftar <?=$tglSekarang?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  <form name="frmFind" method="GET" action="<?php echo $_SERVER["PHP_SELF"]?>">
            <!-- <div class="col-md-4 col-sm-6 col-xs-12">
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
            </div> -->

            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
              <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_GET["cust_usr_nama"],false,false);?>
            </div>
            
            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
              <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_GET["cust_usr_kode"],false,false);?>
            </div>       
            
            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">No. Registrasi </label>
              <?php echo $view->RenderTextBox("reg_kode_trans","reg_kode_trans",30,200,$_GET["reg_kode_trans"],false,false);?>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Baru / Lama</label>
              <select id="reg_status_pasien" class="select2_single form-control" name="reg_status_pasien">
                <option value="--"> [ Baru / Lama ] </option>
                <option value="B" <?php if('B'==$_GET["reg_status_pasien"]) echo "selected"; ?>> Baru </option>
                <option value="L" <?php if('L'==$_GET["reg_status_pasien"]) echo "selected"; ?>> Lama </option>
              </select>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
              <select id="jenis_nama" class="select2_single form-control" name="jenis_nama">
                <option value="--">[ Pilih Cara Bayar ]</option>
                    <?php for($i=0,$n=count($dataJenisBayar);$i<$n;$i++){ ?>
                      <option value="<?php echo $dataJenisBayar[$i]["jenis_id"];?>"
                          <?php if($dataJenisBayar[$i]["jenis_id"]==$_GET["jenis_nama"]) echo "selected"; ?>><?=$dataJenisBayar[$i]["jenis_nama"];?>   
                      </option>
                  <?php } ?>
              </select>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Poli</label>
              <select id="poli_nama" class="select2_single form-control" name="poli_nama">
                <option value="--">[ Pilih Poli ]</option>
                    <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                      <option value="<?php echo $dataPoli[$i]["poli_id"];?>"
                          <?php if($dataPoli[$i]["poli_id"]==$_GET["poli_nama"]) echo "selected"; ?>><?=$dataPoli[$i]["poli_nama"];?>   
                      </option>
                  <?php } ?>
              </select>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Dokter </label>
              <?php echo $view->RenderTextBox("dokter","dokter",30,200,$_GET["dokter"],false,false);?>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Status</label>
              <select id="reg_status" class="select2_single form-control" name="reg_status">
                <option value="--"> Semua </option>
                <option value="E0" <?php if('E0'== $_GET["reg_status"]) echo "selected"; ?>> Belum Dilayani </option>
                <option value="E1" <?php if('E1'== $_GET["reg_status"]) echo "selected"; ?>> Sampai di Poli </option>
                <option value="E2" <?php if('E2'== $_GET["reg_status"]) echo "selected"; ?>> Sudah Dilayani </option>
              </select>
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
                      <div class="table-responsive">
                      <!-- <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive" cellspacing="0" width="100%"> -->
                      <table id="" class="table table-striped table-bordered " cellspacing="0" width="100%">
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
                    </div> 
                    
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

<script>
    function sampai(id, status) {
			if (status == 'E0') {
				$.get('get_irj.php', {
					reg_id: id
				}, function(result) {
					//insert awal (PK FK) ke folio						
					var dataString = 'isNewRecord=false' +
					'&id_dep=' + result[0].id_dep +
					'&id_reg=' + result[0].reg_id +
					'&cust_usr_id=' + result[0].id_cust_usr +
					'&id_pembayaran=' + result[0].id_pembayaran +
					'&id_poli=' + result[0].id_poli +
					'&id_dokter=' + result[0].usr_id +
					'&id_reg_jenis_pasien=' + result[0].reg_jenis_pasien;
					//alert (dataString);return false;
					$.ajax({
						type: "POST",
						url: "proses_sampai.php",
						data: dataString,
						success: function() {
              location.reload(); 
						}
					});
				}, 'json');
			} else if (status != 'E0') {
				alert('Pasien Sudah Sampai di Poli');
			}
		}

    function cetak(id) {
      if (id) {
        var url = '../edit_registrasi/cetak_registrasi.php?reg_id=' + id;
        var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() {
          }, true);
      }
    }

    //fungsi cetak spb
		function cetakspb(id,pembayaran) {
			if (id) {
				var url = 'cetak_spb.php?id_reg=' + id + '&pembayaran_id=' + pembayaran;
				var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
				printWindow.addEventListener('load', function() {
				}, true);
			}
		}

    //fungsi cetak tagihan
		function cetak_tagihan(id,pembayaran) {
			if (id) {
				var url = 'cetak_tagihan.php?id_reg=' + id + '&pembayaran_id=' + pembayaran;
				var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
				printWindow.addEventListener('load', function() {
				}, true);
			}
		}

    function cetakb(id,usr) {
			if (id) {
				var url = '../pemeriksaan_irna/cetak_barcode.php?id_reg=' + id + '&id=' + usr;
				var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
				printWindow.addEventListener('load', function() {
				}, true);
			}
		}
</script>
  </body>
</html>