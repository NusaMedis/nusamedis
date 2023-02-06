<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();

     // PRIVILLAGE
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 
     if($_GET["new"]) $_POST["new"]=$_GET["new"];
     else $_POST["new"]=$_POST["new"]; 
          
     if ($_POST["btnNew"]){
     $editPage = "item_generik_view.php?new=1"; 
     header('location:'.$editPage);
     }
     else $editPage = "item_generik_view.php";
     if ($_POST["btnNew"]){
     $thisPage = "item_generik_view.php?new=1"; }
     else $thisPage = "item_generik_view.php";
 
 if($_POST["btnBack"]) {
     $backPage="item_generik_view.php";
     header("location:".$backPage);        
     }      
    /* if(!$auth->IsAllowed("apo_setup_barang",PRIV_READ)){
         echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("apo_setup_barang",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     }  */
     
     if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
     
       if ($_GET["kembali"]) $_POST["klinik"]=$_GET["kembali"];
       
    if ($_POST["btnSave"]) {
       $itemId = & $_POST["cbDelete"];
  		for($i=0,$n=count($itemId);$i<$n;$i++) {
  			$sql = "update logistik.logistik_item set item_psikotropika='y'
                where item_id = ".QuoteValue(DPE_CHAR,$itemId[$i]);
                //echo $sql; die();            
  			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
  		}
    }
     
    if ($_POST["btnUpdate"]) {
       $itemId = & $_POST["cbDelete"];
  		for($i=0,$n=count($itemId);$i<$n;$i++) {
  			$sql = "update logistik.logistik_item set item_psikotropika='n'
                where item_id = ".QuoteValue(DPE_CHAR,$itemId[$i]);
                //echo $sql; die();            
  			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
  		}
    }

     // -- paging config ---//
     /*$recordPerPage = 100;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;
     // -- end paging config ---//
     
     if($_GET["klinik"]){
       $_SESSION["x_id_jenis_x"] = $_POST["klinik"];
     }else{
       $_GET["klinik"] = $_SESSION["x_id_jenis_x"];
     }
   
     $tipe["V"] = "Volume Based";
     $tipe["N"] = "Non Valume Based";
     */
     //ambil data outlet dan data gudang
    /* $sql = "select konf_outlet,konf_gudang from logistik.logistik_konfigurasi 
        where konf_id = 0";
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $konfigurasi = $dtaccess->Fetch($rs_edit);   */
     
    /* if($_GET["id_jenis"]){
       $_SESSION["x_id_jenis_x"] = $_POST["id_jenis"];
     }else{
       $_GET["id_jenis"] = $_SESSION["x_id_jenis_x"];
     } */
     
     //if(!$_POST["id_jenis"]) $_POST["id_jenis"] = "2";
     //if($_POST["id_jenis"]) $sql_where [] = " item_tipe_jenis = ".QuoteValue(DPE_CHAR,$_POST["id_jenis"]);
     if($_POST["grup_item_id"]) $sql_where [] = " id_kategori = ".QuoteValue(DPE_CHAR,$_POST["grup_item_id"]);     
     if($_POST["_nama"]) $sql_where[] = "UPPER(a.item_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%"));
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
        

     
     $addPage = "item_edit.php?tambah=".$_POST["klinik"]."&id_kategori=".$_POST["grup_item_id"];
     
     $edit="item_generik_view.php?edit=1";
     


     if($_POST["new"])  {
     $sql_where[] = "(item_psikotropika is null or item_psikotropika ='n')";  
     }  else {
           $sql_where[] = "item_psikotropika = 'y'";    
     }
     
          if($sql_where) $sql_where = implode(" and ",$sql_where);
          
          if($_POST["btnSearch"] || $_POST["new"] || $_POST["btnBack"]){
     $sql = "select a.*, b.jenis_nama, b.jenis_id, c.dep_nama, d.grup_item_nama, e.satuan_nama as satuan_beli, f.satuan_nama as satuan_jual,
     kategori_tindakan_nama
     from logistik.logistik_item a
     left join global.global_jenis_pasien b on b.jenis_id = a.item_tipe_jenis
     left join global.global_departemen c on c.dep_id = a.id_dep
     left join logistik.logistik_grup_item d on d.grup_item_id=a.id_kategori
     left join logistik.logistik_item_satuan e on a.id_satuan_beli = e.satuan_id
     left join logistik.logistik_item_satuan f on a.id_satuan_jual = f.satuan_id
     left join klinik.klinik_kategori_tindakan g on a.id_kategori_tindakan = g.kategori_tindakan_id";
     if($sql_where) $sql .= " where a.item_flag ='M' and a.item_aktif='y' and ".$sql_where;
     //$sql .= " order by id_kategori asc, item_berlaku asc ";
     $sql .= " order by item_nama ";
     //echo $sql;
	   $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
//     echo $sql;
     //echo $sql.'<br>';
	   // --- ngitung jml data e ---              
     /*$sql = "select count(item_id) as total
               from logistik.logistik_item";
      if($sql_where) $sql .= " where ".$sql_where;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum); */
      //echo $sql;
     //*-- config table ---*//
     $tableHeader = "&nbsp;Setup Psiko Tropika";
     
     $isAllowedDel = $auth->IsAllowed("apo_setup_barang",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("apo_setup_barang",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("apo_setup_barang",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "&nbsp;";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Barang";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Satuan Beli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;  
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Satuan Jual";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;  
    /*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Stok";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;  
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Harga Beli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Harga Jual";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;   */
     
       //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0,$j++){
/*          
  if($dataTable[$i]["id_kategori_tindakan"]!=$dataTable[$i-1]["id_kategori_tindakan"]){
      $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["kategori_tindakan_nama"];
      $tbContent[$j][$counter][TABLE_ALIGN] = "left";
      $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
      $tbContent[$j][$counter][TABLE_COLSPAN] = $counterHeader;
      $counter=0;
      $j++;
  }    
  */        
               $tbContent[$j][$counter][TABLE_ISI] = "<input type='checkbox' name='cbDelete[]' value='".$dataTable[$i]["item_id"]."'>";               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
            $counter++;

          $tbContent[$j][$counter][TABLE_ISI] = $j+1; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["grup_item_nama"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["satuan_beli"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++;  
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["satuan_jual"]; 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
       /*  
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["item_stok"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++;  
          
          $tbContent[$j][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_harga_beli"]); 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_harga_jual"]); 
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;     */
          
         
     }     
     
     $colspan = count($tbHeader[0]);
     
       }
    
      //data jenis
			$sql = "select jenis_id , jenis_nama from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc"; 
			$dataJenis = $dtaccess->FetchAll($sql);
			//echo $dataJenis;
			
				     // --- master Tipe  ---
     $sql = "select * from logistik.logistik_grup_item  where item_flag='M' and id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])." order by grup_item_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataTipe = $dtaccess->FetchAll($rs);
    //echo $sql;    
    
		$tipe[] = $view->RenderOption("","[- Pilih Semua Tipe -]",$show);
    for($i=0,$n=count($dataTipe);$i<$n;$i++){
		unset($show);
		if($_POST["grup_item_id"]==$dataTipe[$i]["grup_item_id"]) $show = "selected";
    $tipe[] = $view->RenderOption($dataTipe[$i]["grup_item_id"],$dataTipe[$i]["grup_item_nama"],$show);
	} 
			
	   if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }
    
     //-- bikin combo box untuk jenis item --//
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'  order by jenis_id asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     
     unset($opt_jenis);$i=1;
     $opt_jenis[0] = $view->RenderOption("--","[Pilih Jenis]",$show);
     while($data_jenis = $dtaccess->Fetch($rs)){
     unset($show);
        if($data_jenis["jenis_id"] == $_POST["id_jenis"]) $show="selected";
        $opt_jenis[$i] = $view->RenderOption($data_jenis["jenis_id"],$data_jenis["jenis_nama"],$show);
        $i++;
     }
   /* 
    $sql = "select item_id, item_berlaku from logistik.logistik_item where id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $dataItem = $dtaccess->FetchAll($rs);
    for($i=0,$n=count($dataItem);$i<$n;$i++) {
    
    $hasil = explode("-", $dataItem[$i]["item_berlaku"]);
    $update = "0".$hasil[0]."-".$hasil[1];

        if(strlen($hasil)=="5") {
             $coba = ($hasil[0]."-".$hasil[1]);
             //echo $coba;
          $sql = "update logistik.logistik_item set item_berlaku =".QuoteValue(DPE_CHAR,$coba)." where item_id=".QuoteValue(DPE_CHAR,$dataItem[$i]["item_id"]);
          $dtaccess->Execute($sql);
        } else if(strlen($hasil)=="4") {
             $cobadE = ("05"."-".$hasil[1]);
             //echo $coba;
          $sql = "update logistik.logistik_item set item_berlaku =".QuoteValue(DPE_CHAR,$cobadE)." where item_id=".QuoteValue(DPE_CHAR,$dataItem[$i]["item_id"]);
          $dtaccess->Execute($sql);
        }
    
    }    
    
   
          $sql = "update logistik.logistik_item set item_berlaku =".QuoteValue(DPE_CHAR,"01-2010");
          $dtaccess->Execute($sql);
    */  
    
?>

<!DOCTYPE html>
<html lang="en">
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.11.3.min.js"></script>
<script language="JavaScript">

function CheckDel(frm)
                    {
                           if (confirm("Semua transaksi yang terdapat barang tersebut akan dihapus, Apakah anda yakin ingin menghapus barang?")==1)
                           {
                                document.frmView.submit();
                            } else { 
                         return false;
                        }
                  }
/*  function reklinik(kliniks) {
   document.location.href='item_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }  */

  function rejenis(jenis) {
   document.location.href='item_generik_view.php?klinik='+jenis+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
function CheckData() {
	if(confirm('Anda Yakin Ingin Memberikan Flag Generik Pada Item Ini?')){
		document.frmView.btnSave.value = 'Simpan';
		document.location.href='item_generik_view.php?new=1';
	}
}
  
function editData() {
	if(confirm('Anda Yakin Ingin Mengubah Flag Generik Pada Item Ini?')){
		document.frmView.btnSave.value = 'Simpan';
		document.location.href='item_generik_view.php';
	}
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
                <h3>Apotik</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setup Psiko Tropika</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori Barang</label>
							<select class="form-control" name="grup_item_id" onchange="this.form.submit();">
							<option value="">[- Tipe Item -]</option>
							<?php for($i=0,$n=count($dataTipe);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataTipe[$i]["grup_item_id"];?>" <?php if($_POST["grup_item_id"]==$dataTipe[$i]["grup_item_id"]) echo "selected";?>><?php echo $dataTipe[$i]["grup_item_nama"];?></option>
						   <?php } ?>               
							</select>
				</div>
				 <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Barang</label>
								<?php echo $view->RenderTextBox("_nama","_nama",40,200,$_POST["_nama"],false,false);?>
				</div>
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnSearch" value="Cari" class="submit"/>
						<!--<input type="button" name="btnAdd" value="Tambah" id="button" class="submit" onClick="document.location.href='<?php echo $addPage;?>?parent='">-->
						<? if ($_POST["new"]) { ?>
						<input type="submit" name="btnSave" value="Simpan sbg Psiko Tropika" class="submit" onClick="javascript:CheckData();">
						<input type="submit" name="btnBack" value="Kembali" class="submit" />
						<? } else {?>
						<input type="submit" name="btnUpdate" value="Hapus sbg Psiko Tropika" class="submit" onClick="javascript:editData();">            
						<input type="submit" name="btnNew" value="Tambah" class="submit"/>
						<? }   ?>
						</div>
					<div class="clearfix"></div>
					         <input type="hidden" name="new" id="new" value="<?php echo $_POST["new"];?>"  />
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- row filter -->

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
        <!--page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
