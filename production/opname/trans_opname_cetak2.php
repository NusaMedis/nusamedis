                                                                                                                                                     <?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/tampilan.php");
     require_once($ROOT."lib/encrypt.php");
     
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
	   
	   if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
         
     // konfigurasi gudang apotik
	   //$sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     //$rs = $dtaccess->Execute($sql);
     //$gudang = $dtaccess->Fetch($rs);
     //$_POST["id_dep"] = $gudang["conf_gudang_obat"];
	   $_POST["id_opname"] = $_GET["id"];
	   $_POST["id_periode"] = $_GET["id_periode"];
     $_POST["id_kategori"] = $_GET["id_kategori"];
     
	   if($_GET["id_gudang"]) { $_POST["id_gudang"] = $_GET["id_gudang"]; }
     if($_GET["tanggal"]) $_POST["opname_tanggal"] = $_GET["tanggal"];

     //if($_POST["id_opname"] && $_POST["id_opname"]!="--") $sql_where[] = "a.id_opname = ".QuoteValue(DPE_CHAR,$_POST["id_opname"]);
     if($_POST["id_gudang"] && $_POST["id_gudang"]!="--") $sql_where[] = "b.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     if($_POST["id_kategori"] && $_POST["id_kategori"]!="--" && $_POST["id_kategori"]!="nn") $sql_where[] = "a.id_kategori = ".QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
     elseif($_POST["id_kategori"]=="nn") $sql_where[] = "(a.id_kategori = '' or a.id_kategori is null or a.id_kategori = '--') ";
     //if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $sql_where = implode(" and ",$sql_where);

     $sql  = "select a.item_id, a.item_nama ,a.id_kategori,kategori_tindakan_nama, a.id_kategori_tindakan, a.item_tipe_jenis, 
          a.id_dep, b.stok_dep_saldo, c.gudang_nama, d.dep_nama as departemen ,e.grup_item_nama
          from logistik.logistik_item a                      
          left join logistik.logistik_stok_dep b on b.id_item = a.item_id
          left join logistik.logistik_gudang c on c.gudang_id = b.id_gudang
          left join global.global_departemen d on d.dep_id = a.id_dep
          left join logistik.logistik_grup_item e on e.grup_item_id=a.id_kategori
          left join klinik.klinik_kategori_tindakan f on a.id_kategori_tindakan = f.kategori_tindakan_id";
          //, g.batch_id, g.id_item, g.batch_create, g.batch_no, g.batch_tgl_jatuh_tempo, h.stok_batch_dep_saldo
          //join logistik.logistik_item_batch g on g.id_item = a.item_id
          //join logistik.logistik_stok_batch_dep h on h.id_batch = g.batch_id";
          
     if($sql_where) $sql .= " where a.item_flag ='M' and ".$sql_where." order by grup_item_nama asc, item_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataItem = $dtaccess->FetchAll($rs);   
    // echo $sql;
    /*$sql =" select a.stok_dep_saldo , b.gudang_nama from logistik.logistik_stok_dep a
    left join logistik.logistik_gudang b on b.gudang_id = a.id_gudang";
    $sql .= " where a.id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"]);
    if($_POST["id_gudang"] && $_POST["id_gudang"]!="--"){
    $sql .= " and a.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
    }
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
	  $dataStok = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*/
  /*$table = new InoTable("table1","100%","left",null,0,2,1,null);     
  $PageHeader = "LAPORAN STOK OPNAME";

	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++; 

	$tbHeader[0][$counter][TABLE_ISI] = "Nama Item";
	//$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;

  if($_POST["id_gudang"]=="--"){
	$tbHeader[0][$counter][TABLE_ISI] = "Gudang";
	//$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
  $counter++;  
  }
  
	$tbHeader[0][$counter][TABLE_ISI] = "Keterangan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Saldo Awal";
	//$tbHeader[0][$counter][TABLE_WIDTH] = "13%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Masuk";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Keluar";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Saldo Akhir";
	//$tbHeader[0][$counter][TABLE_WIDTH] = "13%";
  $counter++;
  
	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	
    //hitung untuk opname jika item bertambah maka masuk , jika item berkurang maka keluar
	  if($dataTable[$i]["stok_item_flag"]==O){
      if($dataTable[$i]["stok_item_jumlah"] > 0){
       $opnameKet[$i] = true;
      }else{
       $opnameKet[$i] = false;
      }
    }
    
    //mencari keternangan dimana item itu di transfer
    if($dataTable[$i]["stok_item_flag"]=='T'){
      if($dataTable[$i]["id_dep_asal"]){
      $dimana[$i] = " dari ".$dataTable[$i]["nama_asal"];
      }else{
      $dimana[$i] = " ke ".$dataTable[$i]["nama_tujuan"];
      }
    
    }	  
		$tbContent[$m][$counter][TABLE_ISI] = $i+1;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tgl = explode(" ", $dataTable[$i]["tanggal"]);
	  $tbContent[$m][$counter][TABLE_ISI] = format_date($tgl[0])." ".$tgl[1];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

	  $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		/*$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["departemen"];
    $tbContent[$m][$counter][TABLE_ALIGN] = "left";          
    $counter++;*/
		
    /*if($_POST["id_gudang"]=="--"){
    $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["gudang"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
    }

    if($dataTable[$i]["stok_item_flag"]==A)  $kau = "Saldo Awal " ;
    //if($dataTable[$i]["stok_item_flag"]==B)  $kau = "Pembelian di " ;
    if($dataTable[$i]["stok_item_flag"]==T)  $kau = "Transfer" ;
    if($dataTable[$i]["stok_item_flag"]==O)  $kau = "Stok Opname" ;
    if($dataTable[$i]["stok_item_flag"]==P)  $kau = "Pemakaian" ;
    if($dataTable[$i]["stok_item_flag"]==K)  $kau = "Return Barang" ;

		
		$tbContent[$m][$counter][TABLE_ISI] = $kau.$dimana[$i];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format(($dataTable[$i]["stok_item_saldo"]-$dataTable[$i]["stok_item_jumlah"]));
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
        
    if($dataTable[$i]["stok_item_flag"]=='A' || $dataTable[$i]["id_dep_asal"] || $opnameKet[$i]){
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["stok_item_jumlah"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    }else{
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    }
     
    if(($dataTable[$i]["stok_item_flag"]=='O' || $dataTable[$i]["id_dep_tujuan"]) && !$opnameKet[$i]){ 
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".abs(currency_format($dataTable[$i]["stok_item_jumlah"]));
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		}else{
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    }
     
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["stok_item_saldo"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;

    unset($kau,$sini,$sana) ;
  }*/
  
  
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
	 if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
	
	  $sql = "select penerimaan_periode_nama from logistik.logistik_penerimaan_periode
            where penerimaan_periode_id = ".QuoteValue(DPE_CHAR,$_POST["id_periode"]);
    $rs = $dtaccess->Execute($sql);
    $dataPeriode = $dtaccess->Fetch($rs);
    //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
        
    $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    
    //-- bikin combo box untuk Tujuan --//
   	$sql = "select gudang_nama from logistik.logistik_gudang where gudang_id = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." order by gudang_id asc"; 
		$dataGudang = $dtaccess->Fetch($sql);
     
  $lokasi = $ROOT."/gambar/img_cfg";
  if($konfigurasi["dep_logo"]) $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
   else $fotoName = $lokasi."/default.jpg";
     	
?>


<script language="javascript" type="text/javascript">
 window.print();
</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>
<table border="0" cellpadding="2" rowspan="3" cellspacing="0" align="center">
    <tr>
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?php echo $fotoName ;?>" height="60"></td>
      <td style="text-align:center;font-size:16px;font-family:times new roman;font-weight:bold;" class="tablecontent">

      <?php echo $konfigurasi["dep_nama"]?><BR>
      <?php echo $konfigurasi["dep_kop_surat_1"]?><BR>
      </td>
       </tr> 
       <tr>
       <td style="text-align:center;font-size:14px;font-family:times new roman;" class="tablecontent">
     
      <?php echo $konfigurasi["dep_kop_surat_2"]?></td>
    </tr>
  </table>
<br>
 <table border="0" cellpadding="3" cellspacing="0" style="align:left" width="100%">     
   <tr>
      <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $dataPeriode["penerimaan_periode_nama"];?></td>


    </tr>
   <tr>
      <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tanggal : <?php echo $_POST["opname_tanggal"];?></td>


    </tr>    
    <tr>
          <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Gudang : <strong><?php echo $dataGudang["gudang_nama"];?></strong></td>
        </tr>
        <tr>
        <td style="text-align:center;font-size:15px;font-family:sans-serif;font-weight:bold;" class="tablecontent">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $PageHeader; ?></td>
        </tr>
  </table>

<br>

  
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<?php //echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
<table width="100%" border="1" cellpadding="1" cellspacing="1">
              <tr>  
               <td align="center" class="subheader" width="2%">No</td>                                         
               <td align="center" class="subheader" width="10%">Kategori</td>
               <td align="center" class="subheader" width="15%">Nama Item</td>
               <td align="center" class="subheader" width="5%">Gudang</td>
               <!--<td align="center" class="subheader" width="8%">No Batch</td>
               <td align="center" class="subheader" width="8%">Expire Date</td>
               <td align="center" class="subheader" width="8%">Batch Create</td>
               <td align="center" class="subheader" width="5%">Stok Batch</td>-->
               <td align="center" class="subheader" width="5%">Stok Tercatat</td>               
               <td align="center" class="subheader" width="5%">Stok Sebenarnya</td>
				       <td align="center" class="subheader" width="8%">Keterangan</td>
               <!--<td align="center" class="subheader" width="7%">Tambah Batch</td>-->                                      
          </tr>
          <?php for($i=0,$j=0,$counter=0,$n=count($dataItem);$i<$n;$i++,$counter=0,$j++){ 

         if(!$_POST["id_opname"]){
               //cari saldo batch sebelum opname
     $sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch where
             id_batch = ".QuoteValue(DPE_CHAR,$dataItem[$i]["batch_id"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             date(stok_item_batch_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_batch_create desc";
      $rs = $dtaccess->Execute($sql);
      $saldoBatchPraOpname[$i] = $dtaccess->Fetch($rs);

               //cari saldo batch sebelum opname
     $sql = "select stok_item_saldo from logistik.logistik_stok_item where
             id_item = ".QuoteValue(DPE_CHAR,$dataItem[$i]["item_id"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             date(stok_item_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_create desc";
      $rs = $dtaccess->Execute($sql);
      $saldoPraOpname[$i] = $dtaccess->Fetch($rs); 
      
      }else{
      $sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch where
             id_batch = ".QuoteValue(DPE_CHAR,$dataItem[$i]["batch_id"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             id_opname = ".QuoteValue(DPE_CHAR,$_POST["id_opname"]);
      $rs = $dtaccess->Execute($sql);
      $saldoBatchPraOpname[$i] = $dtaccess->Fetch($rs);

     $sql = "select stok_item_saldo from logistik.logistik_stok_item where      
             id_item = ".QuoteValue(DPE_CHAR,$dataItem[$i]["item_id"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             id_opname = ".QuoteValue(DPE_CHAR,$_POST["id_opname"]);
      $rs = $dtaccess->Execute($sql);      
      $saldoPraOpname[$i] = $dtaccess->Fetch($rs);
      }         
      
      $itemku = $dataItem[$i]["item_id"];
          ?>
          <tr  class="<?php if($i%2==0) echo 'tablecontent-odd'; else echo 'tablecontent'; ?>">  
            <?php /*if($dataItem[$i]["id_item"]!=$dataItem[$i-1]["id_item"]) { 
                     $dataSpan["jml_span"] = $hitung[$dataItem[$i]["id_item"]] += 1; 
                     $m++;*/ ?>
                     
                      <td align="center" style="padding-left:5px;">
                    <?php echo ($i+1)?>                  
               </td>

               <td align="center" style="padding-left:5px;">
                    <?php echo $view->RenderLabel("grup_item_nama","grup_item_nama",$dataItem[$i]["grup_item_nama"], null,false);?>                  
               </td>
               <td align="left" style="padding-left:5px;">     
                    <?php echo $view->RenderLabel("item1","item1",$dataItem[$i]["item_nama"], null,false);?>
                    <?php echo $view->RenderHidden("id_item[$i]","id_item[$i]",$dataItem[$i]["item_id"]);?>                                
                    <!--<input type="text" name="id_item[<?php echo $i;?>]" id="id_item_<?php echo $i;?>" value="<?php echo $dataItem[$i]["item_id"];?>" />-->
               </td>              
               <td align="center" style="padding-left:5px;">
                    <?php echo $view->RenderLabel("gudang_nama","gudang_nama",$dataItem[$i]["gudang_nama"], null,false);?>                  
               </td>
               <?php //} ?>
                                          
               <!--<td align="center">
                    <?php echo $view->RenderTextBox("batch_no[$i]","batch_no[$i]","8","8",$dataItem[$i]["batch_no"],"", null,false);?>
                    <input type="hidden" name="batch_id[<?php echo $i;?>]" id="batch_id_<?php echo $i;?>" value="<?php echo $dataItem[$i]["batch_id"];?>" />
                    <?php echo $view->RenderHidden("id_item_batch$i","id_item_batch$i",$dataItem[$i]["item_id"]);?>
                    <?php echo $view->RenderHidden("id_item_batch_$i","id_item_batch_$i",$dataItem[$i-1]["item_id"]);?>
                    <?php //echo $view->RenderTextBox("id_item_batch[$i]","id_item_batch[$i]","8","30",$dataItem[$i]["item_id"]);?>
               </td>
               <td align="center">
                    <?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo$i","batch_tgl_jatuh_tempo$i","10","10",format_date($dataItem[$i]["batch_tgl_jatuh_tempo"]),"","readonly", null,false);?>
               </td>
               <td align="center">
                    <?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo$i","batch_tgl_jatuh_tempo$i","16","30",formatTimestamp($dataItem[$i]["batch_create"]),"","readonly", null,false);?>
               </td>

               <td align="center">
                    <?php echo $view->RenderTextBox("stokHandBatch$i","stokHandBatch$i","5","30",number_format($saldoBatchPraOpname[$i]["stok_item_batch_saldo"],4),"curedit", "",true,"onchange=\"javascript:return CariStokAKhir(document.getElementById('stokHandBatch$i').value,document.getElementById('batch_id_$i').value,document.getElementById('id_item_batch$i').value,document.getElementById('id_gudang').value);\"")?>
               </td>-->
               
               <?php //if($dataItem[$i]["id_item"]!=$dataItem[$i-1]["id_item"]) { $dataSpan["jml_span"]; ?>
               <td align="left" style="padding-left:5px;">                    
                    <?php echo $view->RenderLabel("stokHand$i","stokHand$i",currency_format($saldoPraOpname[$i]["stok_item_saldo"]),null,false);?>
                    <?php echo $view->RenderHidden("urutan","urutan",$i);?>                   
               </td>               
               <td align="center" style="padding-left:5px;">
                    
                  <!--<div id="div_stok<?php echo $_POST["id_item_batch$i"];?>"><?php //echo GetStokAkhir($_POST["stokHandBatch$i"],$_POST["batch_id$i"],$_POST["id_item_batch$i"],$_POST["id_gudang"]);?></div>-->
                  <?php echo $view->RenderLabel("stokReal_$itemku","stokReal_$itemku",currency_format($saldoPraOpname[$i]["stok_item_saldo"]),null,false); ?>               
               </td>              
               <td align="center" style="padding-left:5px;">
                    <?php echo $view->RenderLabel("stokKet$i","stokKet$i",$dataItem[$i]["stok_item_keterangan"],null,false);?>                    
               </td>              

               <!--<td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                <?php echo '<a href="tambah_batch.php?id_item='.$dataItem[$i]["item_id"].'&klinik='.$dataItem[$i]["id_dep"].'&id_jenis='.$dataItem[$i]["item_tipe_jenis"].'&id_gudang='.$_POST["id_gudang"].'"><img src="'.$ROOT.'gambar/add.png" border="0" alt="Pilih" title="Pilih" width="18" height="18" class="img-button")"/></a>'; ?>
               </td>-->
               <?php //} ?>
          </tr>
          <?php } ?>
          </table>
</td>
</tr>
</table> 

