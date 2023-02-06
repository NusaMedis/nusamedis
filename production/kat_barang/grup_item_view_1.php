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
     $usrId = $auth->GetUserId();
     $editPage = "grup_item_edit.php";
     $thisPage = "grup_item_view.php?";
     $formatPage = "grup_item_format.php?";
     $subPage = "grup_item_sub_view.php";
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
    /* if(!$auth->IsAllowed("apo_setup_kat_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("apo_setup_kat_barang",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
     //if(!$_POST["klinik"]) $_POST["klinik"] = $depId; 
       if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
     // if (!$_GET["klinik"]) { $_POST["klinik"] = $depId; }     
     //else if(!$_POST["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; }
      
      //if (!$_POST["klinik"]) $_POST["klinik"] = $_GET["klinik"];
      
        if ($_GET["kembali"]) $_POST["klinik"]=$_GET["kembali"];
        $addPage = "grup_item_edit.php?tambah=".$_POST["klinik"];
     
     // -- paging config ---//
     $recordPerPage = 500;
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
     
      //ambil data outlet dan data gudang
     /*$sql = "select konf_outlet,konf_gudang from logistik.logistik_konfigurasi where konf_id = 0";
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $konfigurasi = $dtaccess->Fetch($rs_edit); */
     
   //if($_GET["klinik"] !="--") $sql_where= "id_dep_klinik like ".QuoteValue(DPE_CHAR,"%".$_GET["klinik"]."%"); 
   if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "id_dep like ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
   if($_POST["_nama"]) $sql_where[] = "UPPER(grup_item_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%")); 
   if($sql_where) $sql_where = implode(" and ",$sql_where);
     
     $sql = "select a.*, b.dep_nama from logistik.logistik_grup_item a
             left join global.global_departemen b on b.dep_id = a.id_dep";
     if($sql_where) $sql .= " where item_flag = 'M' and ".$sql_where;
     $sql .= " order by grup_item_nama asc ";
     $rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
     $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;  
       
     // --- ngitung jml data e ---              
     $sql = "select count(grup_item_id) as total from  logistik.logistik_grup_item";
     if($sql_where) $sql .= " where ".$sql_where;
     //echo $sql;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Kategori Barang";
     
     /*$isAllowedDel = $auth->IsAllowed("apo_setup_kat_barang",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("apo_setup_kat_barang",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("apo_setup_kat_barang",PRIV_CREATE); */
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "&nbsp;";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++;   
 
     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++; */
  
     //if($isAllowedUpdate){
          //$tbHeader[0][$counterHeader][TABLE_ISI] = "Sub Kat.";
          //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          //$counterHeader++;
     
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     //} 
      
     //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     //} 
     
           //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;   
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
          $tbContent[$i][$counter][TABLE_ISI] = ($startPage+$i+1);               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
        
          $lokasi = $ROOT."gambar/item";
      if($dataTable[$i]["grup_pic"]) $fotoName=$lokasi."/".$dataTable[$i]["grup_pic"];
      else $fotoName = $lokasi."/default_barang.jpg";
          
          $tbContent[$i][$counter][TABLE_ISI] ='<img hspace="2" width="100" height="75" src="'.$fotoName.'" border="0">';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
                
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["grup_item_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;   */
           
          //if($isAllowedUpdate) {
               //$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$subPage.'?id='.$dataTable[$i]["grup_item_id"].'"><img hspace="2" src="'.$ROOT.'gambar/icon/add.png" width="30px" height="30px" alt="Edit" title="Edit" border="0"></a>';               
               //$tbContent[$i][$counter][TABLE_ALIGN] = "center";
               //$counter++; 
          
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["grup_item_id"]).'&klinik='.$dataTable[$i]["id_dep"].'"><img hspace="2" src="'.$ROOT.'gambar/icon/edit.png" width="30px" height="30px" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          //}
          //if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["grup_item_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          //} 
          
     } 
         $lokasi = $ROOT."gambar/item";
         $fotoName=$lokasi."/".$dataTable[$i]["grup_pic"];

     $colspan = count($tbHeader[0]);
   
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
    $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     
?>
<?php ////echo $view->RenderBody("ipad_depans.css",true,"KATEGORI BARANG"); ?>
<?php //echo $view->RenderBody("module.css",true,false,"KATEGORI BARANG"); ?>
<script language="JavaScript">
  function rejenis(kliniks) {
   document.location.href='grup_item_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
</script> 
<body>
<!--<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6">SETUP KATEGORI BARANG</font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div> -->
<br />
<div id="body">
<div id="scroller">

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"];?>"> 
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <!--<tr class="tablecontent">
          <td width="20%" align="right">&nbsp;&nbsp;Nama Klinik&nbsp;&nbsp;</td>
          <td width="80%" align="left">
			 <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onchange="rejenis(this.value);">
				<option class="inputField" value="--" >- Semua Klinik -</option>     -->
				<?php// $counter = -1;
				//	for($i=0,$n=count($dataKlinik);$i<$n;$i++){
			//		unset($spacer); 
			//		$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
			//		for($j=0;$j<$length;$j++) $spacer .= ".."; 
				?>
				<!--	<option class="inputField" value="<?php //echo $dataKlinik[$i]["dep_id"];?>"<?php //if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php //echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php// }?>
				</select>
		  </td>
		 </tr> -->
		 <tr>
				<td align="right" class="tablecontent" width="30%">&nbsp;&nbsp;Nama Kategori Barang&nbsp;&nbsp;</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_nama","_nama",50,200,$_POST["_nama"],false,false);?>
					<input type="submit" name="btnSearch" value="Cari" class="submit"/>
				</td>
			</tr>
					<tr>
				<td colspan="2" align="center">					
            <input type="button" name="btnAdd" value="Tambah" id="button" class="submit" onClick="document.location.href='<?php echo $addPage;?>'">
				</td>
			</tr>
		 </table> 
<br />		 
<?php if ($_POST["klinik"]) { ?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
<tr>
<td>
<?php  echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td>
</tr>
</table>
<?php } ?>		 
		 
</form>
     </div>
		 </div>

  		<!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
</table>-->
<?php// if ($_POST["klinik"]) { ?> 
 <!--   <table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td align="right" width="33"><?php //echo $view->RenderPaging($numRows["total"], $recordPerPage, $currPage ); ?></td>
</tr>
</table>
<form name="frmView" method="POST" action="<?php //echo $editPage; ?>">
     <?php //echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>  -->
<?php //} ?>
<?php //echo $view->RenderBodyEnd(); ?>