<?php
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
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();

     $sql = "select id_rol from global.global_auth_user where usr_id = '$userId'";
     $jabatan = $dtaccess->Fetch($sql);

     $sqlDR = "select usr_id, id_rol from global.global_auth_user where usr_name = ".QuoteValue(DPE_CHAR,$userName);
     $dataDokter = $dtaccess->Fetch($sqlDR);//bf
     //var_dump($dataDokter); die();
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
     $statusInacbgStatus["p"] = "Sudah Diinput Perawat";
     $statusInacbgStatus["d"] = "Sudah Diinput Dokter";
     // $statusInacbgStatus["n"] = "Sudah Askep";
     // $statusInacbgStatus["y"] = "Sudah As Med";
     // $statusInacbgStatus["b"] = "Sudah Diinput";



     // KONFIGURASI
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_GET["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];


      $table = new InoTable("table","100%","left");
       $skr = date("d-m-Y");

       if(!$_GET["tgl_awal"]) $_GET["tgl_awal"] = $skr;
       if(!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] = $skr;
        //if($_GET["tgl_awal"]) $_GET["tgl_awal"] =  $_GET["tgl_awal"];
        //if($_GET["tgl_akhir"]) $_GET["tgl_akhir"] =  $_GET["tgl_akhir"];


     if($_GET["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_GET["cust_usr_kode"]."%");
     if($_GET["usr_id"])  $sql_where[] = " c.id_dokter =".QuoteValue(DPE_CHAR,$_GET["usr_id"]);
     if($_GET["cust_usr_nama"])  $sql_where[] = "UPPER(b.cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_nama"])."%");
     if($_GET["reg_no_sep"])  $sql_where[] = "c.reg_no_sep like".QuoteValue(DPE_CHAR,"%".$_GET["reg_no_sep"]."%");

      if($_GET["reg_tipe_rawat"])  $sql_where[] = "c.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$_GET["reg_tipe_rawat"]);
      if($_GET["klinik"])  $sql_where[] = "c.id_poli = ".QuoteValue(DPE_CHAR,$_GET["klinik"]);

     if(!empty($_GET["tgl_awal"]))  $sql_where[] = "DATE(i.inacbg_tanggal_masuk) >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if(!empty($_GET["tgl_akhir"]))$sql_where[] = "DATE(i.inacbg_tanggal_masuk) <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     $sql_where[] = " a.id_dep =".QuoteValue(DPE_CHAR,$depId);

     if ($sql_where[0])
     $sql_where = implode(" and ",$sql_where);


     if($_GET["btnLanjut"] || $_GET["tgl_awal"])
     {
        $sql = "select i.*,a.*, c.reg_tipe_rawat,c.id_dep,c.id_dokter,c.reg_id,c.id_cust_usr,c.id_poli, b.cust_usr_nama, b.cust_usr_kode,e.usr_name, j.usr_name as coder, e.usr_id,
            d.poli_nama,f.jenis_nama,c.reg_tanggal_pulang,c.reg_waktu_pulang,c.reg_waktu,c.reg_no_sep,h.jkn_nama,c.reg_tanggal
            from klinik.klinik_inacbg i
            left join klinik.klinik_registrasi c on c.reg_id = i.id_reg
            join global.global_customer_user b on c.id_cust_usr = b.cust_usr_id
            join klinik.klinik_pembayaran a on i.id_pembayaran = a.pembayaran_id
            left join global.global_auth_poli d on d.poli_id = c.id_poli
            left join global.global_auth_user e on e.usr_id = c.id_dokter
            left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
            left join global.global_jkn h on c.reg_tipe_jkn = h.jkn_id
            left join global.global_auth_user j on j.usr_id = i.inacbg_who_update
            where (b.cust_usr_kode <>'500' and b.cust_usr_kode <>'100')
            and poli_id = 'c96a0c5914b37954352542aae75e4709'
            and ".$sql_where;
            if($dataDokter["id_rol"] == '2') {
              $sql .= " and c.id_dokter =".QuoteValue(DPE_CHAR,$dataDokter[0]);//bf
            } 
          $sql .= "   order by reg_tanggal DESC, reg_waktu DESC";
          if(empty($_GET["tgl_akhir"]) && empty($_GET["tgl_awal"])) $sql .= " limit 200";
          $dataTable = $dtaccess->FetchAll($sql);
         //  echo $sql;
          // die();
        //  var_dump($dataTable); die();
    }
    //var_dump($dataTable); die();
    $row = -1;

          $tableHeader = "&nbsp;E-Medical Record";
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

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Dx Utama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          /*
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
*/
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;


        //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;

      for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {

          $sqlBayar = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$dataTable[$i]["reg_id"])."
                        and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $dataBayar = $dtaccess->Fetch($sqlBayar);

// SEMENTARA DIHILANGKAN
//      if(!$dataBayar || $dataBayar["pembayaran_flag"]=='n')
//      {
         // $codingPage = "input_rm_edit.php?id_inacbg=".$dataTable[$i]["inacbg_id"]."&id_reg=".$dataTable[$i]["reg_id"]."&tgl_awal=".$_GET["tgl_awal"]."&tgl_akhir=".$_GET["tgl_akhir"];
          // if ($jabatan["id_rol"] == '2') {
          //    $codingPage = "asuhan_medis.php?id_inacbg=".$dataTable[$i]["inacbg_id"]."&id_reg=".$dataTable[$i]["reg_id"]."&tgl_awal=".$_GET["tgl_awal"]."&tgl_akhir=".$_GET["tgl_akhir"]."&cust_usr_nama=".$_GET["cust_usr_nama"]."&cust_usr_kode=".$_GET["cust_usr_kode"]."&_reg_no_sep=".$_GET["reg_no_sep"]."&_reg_tipe_rawat=".$_GET["reg_tipe_rawat"]."&btnLanjut=Lanjut";
          // } else {
             $codingPage = "../pemeriksaan_irj/asuhan_medis_lanjutan_page.php?id_inacbg=".$dataTable[$i]["inacbg_id"]."&id_reg=".$dataTable[$i]["reg_id"]."&tgl_awal=".$_GET["tgl_awal"]."&tgl_akhir=".$_GET["tgl_akhir"]."&cust_usr_nama=".$_GET["cust_usr_nama"]."&cust_usr_kode=".$_GET["cust_usr_kode"]."&id_cust_usr=".$dataTable["id_cust_usr"]."&_reg_no_sep=".$_GET["reg_no_sep"]."&_reg_tipe_rawat=".$_GET["reg_tipe_rawat"]."&btnLanjut=Lanjut";
          // }         
          $hapusPage = "askep.php?hapus=1&id_inacbg=".$dataTable[$i]["inacbg_id"]."&tgl_awal=".$_GET["tgl_awal"]."&tgl_akhir=".$_GET["tgl_akhir"];
          $editPage = "askep.php?edit=1&id_reg=".$dataTable[$i]["reg_id"]."&id_inacbg=".$dataTable[$i]["inacbg_id"]."&tanggal_awal=".$_GET["tgl_awal"]."&tanggal_akhir=".$_GET["tgl_akhir"]."&cust_usr_nama=".$_GET["cust_usr_nama"]."&cust_usr_kode=".$_GET["cust_usr_kode"]."&_reg_no_sep=".$_GET["reg_no_sep"]."&_reg_tipe_rawat=".$_GET["reg_tipe_rawat"]."&btnLanjut=Lanjut";
          $cetakklaimlink = "http://".$wsinacbgserver."/ws_inacbg/cetak_klaim.php";

          //diagnosa utama
          $sql = "select rawat_icd_kode from klinik.klinik_perawatan_icd where id_inacbg = ".QuoteValue(DPE_CHAR,$dataTable[$i]["inacbg_id"])." and rawat_icd_urut = ".QuoteValue(DPE_CHAR,1);
          $rs = $dtaccess->Execute($sql);
          $dataInaIcd = $dtaccess->Fetch($rs);


          if ($dataTable[$i]["inacbg_check"]=='d' && $jabatan["id_rol"] == '6')
            $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          else if ($dataTable[$i]["inacbg_check"]=='k' && $jabatan["id_rol"] == '2') 
            $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          else
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$codingPage.'" target="_blank"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cari.png" alt="Coding" title="Coding" border="0"/></a>';
             
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          /*
          if ($dataTable[$i]["inacbg_check"]<>'k')
              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"/></a>';
          else
              $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          if ($dataTable[$i]["inacbg_check"]=='k' || $dataTable[$i]["inacbg_check"]=='n')
              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$hapusPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"/></a>';
          else
              $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak Klaim" title="Cetak Klaim" border="0" style="cursor: pointer;" onclick="javascript:CetakKlaim(\''.$dataTable[$i]["inacbg_no_sep"].'\',\''.$_GET["usernik"].'\',\''.$dataTable[$i]["id_reg"].'\');"/>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          */



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

          $tbContent[$i][$counter][TABLE_ISI] = QuoteValue(DPE_TIME,$dataTable[$i]["reg_waktu"]);
          //format_date();
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

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataInaIcd["rawat_icd_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          /*$tbContent[$i][$counter][TABLE_ISI] = $tipeRawatLabel[$dataTable[$i]["reg_tipe_rawat"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
		  */

          $tbContent[$i][$counter][TABLE_ISI] = $statusInacbgStatus[$dataTable[$i]["inacbg_check"]];
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

    $sqlCDR = "select * from global.global_auth_user where id_rol = '2' order by usr_name ASC ";
    $rscdr = $dtaccess->Execute($sqlCDR);
    $dataComboDokter = $dtaccess->FetchAll($rscdr); 

    $sql = "select * from global.global_auth_poli where poli_tipe='J' order by poli_nama ASC";
    $rs = $dtaccess->Execute($sql);
    $dataPoli = $dtaccess->FetchAll($rs);
   
   // var_dump($dataComboDokter);
    /*if($_GET['poli']) $sql_where_header[] = "id_dokter = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
    $sql = "select * from global.global_auth_poli where poli_tipe='J' order by poli_nama ASC";
    $rs = $dtaccess->Execute($sql);
    $dataPoli = $dtaccess->FetchAll($rs);*/
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
                <h3>Rawat Jalan</h3>
              </div>
            </div>
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>E-Medical Record</h2>
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

            <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">Pilih Klinik</label>
                <select name="klinik" id="klinik" class="form-control">
                    <option value="">[--Pilih Klinik--]</option>
                    <?php for ($y=0; $y < count($dataPoli); $y++) { ?>
                    <option value="<?php echo $dataPoli[$y]["poli_id"] ;?>" <?php if($_GET["klinik"]==$dataPoli[$y]["poli_id"]) echo "selected" ;?>><?php echo $dataPoli[$y]["poli_nama"] ;?></option>
                  <?php } ?>
                </select>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">Pilih Dokter</label>
                <select name="usr_id" id="usr_id" class="form-control">
                    <option value="">[--Pilih Dokter--]</option>
                    <?php for ($y=0; $y < count($dataComboDokter); $y++) { ?>
                    <option value="<?php echo $dataComboDokter[$y]["usr_id"] ;?>" <?php if($_GET["usr_id"]==$dataComboDokter[$y]["usr_id"]) echo "selected" ;?>><?php echo $dataComboDokter[$y]["usr_name"] ;?></option>
                  <?php } ?>
                </select>
            </div>

            <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">Pilih Dokter</label>
                <select name="dokter" id="dokter" class="form-control">
                    <option value="">[--Pilih Dokter--]</option>
                    <?php for ($y=0; $y < count($dataDokter); $y++) { ?>
                    <option value="<?php echo $dataDokter[$y]["poli_id"] ;?>" <?php if($_GET["doktek"]==$dataDokter[$y]["poli_id"]) echo "selected" ;?>><?php echo $dataDokter[$y]["poli_nama"] ;?></option>
                  <?php } ?>
                </select>
            </div> -->
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
            <td class="tablecontent-odd" colspan="5"><input type="submit" name="btnLanjut" value="Lanjut" class="add btn btn-primary"></td>
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
