<?php
    require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tree.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php"); 
    
    //INISIALISAI AWAL LIBRARY
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $err_code = 0;                   
     $auth = new CAuth();
     $depNama = $auth->GetDepNama(); 
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();	
     $table = new InoTable("table","100%","left");   
     $depId = $auth->GetDepId();
     $userData = $auth->GetUserData(); 
     $depLowest = $auth->GetDepLowest();
     $userName = $auth->GetUserName();
     
     
     if($_GET["loket"]) { $_POST["loket"] = $_GET["loket"]; 
      }else if($_POST["loket"]) { $_POST["loket"] = $_POST["loket"]; }
      else { $_POST["loket"] = $depId; }
     
     $findPage="departemen_find2.php?";
     $viewPage = "posting_gl.php";
  	
    if($_x_mode=="New") $privMode = PRIV_CREATE;
  	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
  	elseif($_x_mode=="Delete") $privMode = PRIV_DELETE;  	
    else $privMode = PRIV_READ ;

    if(!$auth->IsAllowed("akunt_proses_postinggl",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("akunt_proses_postinggl",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
     if($_POST["tgl_awal"]) $sql_where[] = "tanggal_tra >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "tanggal_tra <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     if($_POST["loket"] && $_POST["loket"]!="--") $sql_where[] .= " a.dept_id = ".QuoteValue(DPE_CHAR,$_POST["loket"]);
     
     $sql = "select a.id_tra,a.ref_tra,a.tanggal_tra,a.ket_tra,a.namauser,a.real_time
              from  gl.gl_buffer_transaksi a where a.is_posting='n'";
     $sql .=" and ".implode(" and ",$sql_where);
  //   echo $sql;
     $rs_edit = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs_edit);

          
	 
	 
if($_POST["btpost"]){
  $cb = & $_POST["cbPost"];
      for($i=0,$n=count($cb);$i<$n;$i++){
      
     
      $sql = "select * from  gl.gl_buffer_transaksi
              where id_tra = ".QuoteValue(DPE_CHAR,$cb[$i]);
      $rs_edit = $dtaccess->Execute($sql);
      $dataTrans = $dtaccess->FetchAll($rs_edit);
      
     for($k=0,$batas=count($dataTrans);$k<$batas;$k++){     
               
      $dbTable = " gl.gl_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";

      
      $dbValue[0] = QuoteValue(DPE_CHAR,$dataTrans[$k]["id_tra"]);
      $dbValue[1] = QuoteValue(DPE_CHAR,$dataTrans[$k]["ref_tra"]);
      $dbValue[2] = QuoteValue(DPE_DATE,$dataTrans[$k]["tanggal_tra"]);//$_POST["tanggal_tra"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$dataTrans[$k]["ket_tra"]);
      $dbValue[4] = QuoteValue(DPE_CHAR,$dataTrans[$k]["namauser"]);
      $dbValue[5] = QuoteValue(DPE_DATE,$dataTrans[$k]["real_time"]);
      $dbValue[6] = QuoteValue(DPE_CHAR,$dataTrans[$k]["dept_id"]);

      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);
      $dtmodel->Insert() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue); 
      
      }
      
      $sql = "select * from  gl.gl_buffer_transaksidetil where tra_id = ".QuoteValue(DPE_CHAR,$cb[$i]);
      $rs = $dtaccess->Execute($sql);
      $dataTransaksiDetil = $dtaccess->FetchAll($rs);
      
      for($j=0,$m=count($dataTransaksiDetil);$j<$m;$j++){
      
      $dbTable = " gl.gl_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "jumlah_trad";
          $dbField[5]  = "dept_id";


          $dbValue[0] = QuoteValue(DPE_CHAR,$dataTransaksiDetil[$j]["id_trad"]);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataTransaksiDetil[$j]["tra_id"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataTransaksiDetil[$j]["prk_id"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$dataTransaksiDetil[$j]["ket_trad"]);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTransaksiDetil[$j]["jumlah_trad"]));
          $dbValue[5] = QuoteValue(DPE_CHAR,$dataTransaksiDetil[$j]["dept_id"]);
    
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbField);
          unset($dbValue); 
          unset($_POST["btnSave"]);
          unset($_POST["job_nama"]);
          unset($_POST["prk_nama"]);
          unset($_POST["jumlah_trad1"]);
          unset($_POST["jumlah_trad2"]);
          
      
          }
      
      //Telah Terposting    
      $sql = "update  gl.gl_buffer_transaksi set is_posting = 'y' where id_tra = ".QuoteValue(DPE_CHAR,$cb[$i]);
    	$dtaccess->Execute($sql);

    }          
    // kembali ke tampilan view ---
    header("location:".$viewPage);
    exit();    
}
   
   if($_POST["loket"]){
       //Data loket
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataloket = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["loket"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataloket = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataloket = $dtaccess->FetchAll($rs);
     }


?>
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>library/script/elements.js"></script>
<script language="Javascript">

</script>
<br /><br /><br /><br />>
<body>
<div id="body">
<div id="scroller">

<table cellpadding="1" cellspacing="1" border="0" width="100%">
    <tr><td width="50%">
  <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" >

<br>
    <fieldset>
    <table border="0" cellpadding="2" cellspacing="1" width="100%">
    <tr>
        <td colspan="2" class="subheader"><center>FILTER</center></td>
    </tr>
    <tr class="tablecontent" align="center">
          <td width="30%" align="right" class="tablecontent">&nbsp;&nbsp;loket&nbsp;&nbsp;</td>
          <td width="60%" align="left" class="tablecontent">
			 <select name="loket" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--">- Semua loket -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataloket);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataloket[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";
				?>
					<option class="inputField" value="<?php echo $dataloket[$i]["dep_id"];?>"<?php if ($_POST["loket"]==$dataloket[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataloket[$i]["dep_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
		 </tr>
    <tr>
        <td class="tablecontent" align="right" nowrap>Periode&nbsp;&nbsp;</td>
        <td nowrap>
	<input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
              <img src="<?php echo $ROOT;?>gambar/cal.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;<sup>(dd-mm-yyyy)</sup>&nbsp;-&nbsp;
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $ROOT;?>gambar/cal.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />            
               &nbsp;<sup>(dd-mm-yyyy)</sub>
	</td>
    </tr>
    <!--<tr>
        <td class="tablecontent" align="right" nowrap>Transaksi&nbsp;per&nbsp;Halaman&nbsp;&nbsp;</td>
        <td><input name="numrows" type="text" id="numrows" size="4" maxlength="3" value="<?php echo($_POST["numrows"]);?>" /></td>
    </tr>
    <tr>
        <td class="tablecontent" align="right" nowrap>Departemen&nbsp;&nbsp;</td>
        <td nowrap><?php echo $view->RenderTextBox("kode_dept","kode_dept","20","400",$_POST["kode_dept"],"inputField",false,false);?>
       <input type="hidden" name="id_dept" id="id_dept" value="<?php echo $_POST["id_dept"];?>" />
        <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Departemen"> 
        <img src="<?php echo($ROOT);?>images/bd_insrow.png" border="0" align="middle" width="14" height="14" OnClick="javascript: OpenDepartment('frmsubmit.kode_dept', document.frmsubmit.kode_dept.value,'<?php echo($id_dept);?>','yes');" title="Pilih Departemen" alt="Pilih Departemen" class="img-button"/>
        </td>
    </tr>-->

    <tr> 
        <td colspan="2" align="right"><input type="submit" id="btnShow" name="btnShow" class="submit" value="Lihat Jurnal" /></td>
    </tr>
    </table>
    </fieldset>
    </form>
    </td><td>&nbsp;</td>
</tr>
</table>
</form>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
 
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script> 

<?php// if($_POST["btnShow"]) { ?>
  <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" >
<table width="100%" border="1" cellpadding="1" cellspacing="1"> 

    <tr >
        <td width="1%" class="tablecontent"><center>&nbsp;&nbsp;Date&nbsp;&nbsp;</center></td>
        <td width="1%" class="tablecontent"><center>&nbsp;&nbsp;Ref.&nbsp;&nbsp;</center></td>
        <td class="tablecontent">&nbsp;&nbsp;Keterangan&nbsp;-&nbsp;[&nbsp;Akun&nbsp;]&nbsp;&nbsp;</td>
        <td width="20%" class="tablecontent"><center>&nbsp;&nbsp;Jumlah&nbsp;&nbsp;</center></td>
    </tr>

<?php  for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){ ?>    
    <tr >
        <td class="tablecontent-odd"><center>&nbsp;&nbsp;<?php echo(format_date($dataTable[$i]["tanggal_tra"]));?>&nbsp;&nbsp;</center></td>
        <td class="tablecontent-odd"><center>&nbsp;&nbsp;<?php echo($dataTable[$i]["ref_tra"]);?>&nbsp;&nbsp;</center></td>
        <td colspan = "3" class="tablecontent-odd"><strong>&nbsp;&nbsp;<?php echo($dataTable[$i]["ket_tra"]); ?>&nbsp;&nbsp;</strong></td>
    </tr>
    <?php  
    $sql = "select a.id_tra,a.ref_tra,a.tanggal_tra,a.ket_tra,a.namauser,a.real_time,
            b.ket_trad,b.jumlah_trad,c.nama_prk,c.no_prk from  gl.gl_buffer_transaksidetil b
             left join  gl.gl_buffer_transaksi a on a.id_tra = b.tra_id
             left join  gl.gl_perkiraan c on b.prk_id = c.id_prk
             where a.is_posting='n' and b.tra_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_tra"]); 
     $rs_edit = $dtaccess->Execute($sql);
     $dataDetil = $dtaccess->FetchAll($rs_edit);
    
    for($j=0,$count=0,$m=count($dataDetil);$j<$m;$j++,$count=0){ 
    $nm_prk[$j] = "[".$dataDetil[$j]["no_prk"]."]&nbsp;".$dataDetil[$j]["nama_prk"];?>
    <tr >
        <td colspan="2" class="tablecontent-odd">&nbsp;</td>
        <td class="tablecontent-odd">&nbsp;&nbsp;<?php echo($nm_prk[$j]);?>&nbsp;&nbsp;</td>
        <td class="tablecontent-odd">&nbsp;<?php echo currency_format($dataDetil[$j]["jumlah_trad"]); ?></td>
    </tr>
<?php //$total[$j] += $dataDetil[$j]["jumlah_trad"]; ?>    
<?php } ?>   
<?php $sql = "select sum(jumlah_trad) as jumlah from  gl.gl_transaksidetil
              where jumlah_trad > '0' and tra_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_tra"]);
      $rs_edit = $dtaccess->Execute($sql);
     $dataTot = $dtaccess->Fetch($rs_edit); ?> 
    
    <tr >
        <td colspan="2" class="tablecontent">&nbsp;</td>
        <td align="right" class="tablecontent"><strong>&nbsp;&nbsp;Total&nbsp;Transaksi&nbsp;:&nbsp;<?php echo(currency_format($dataTot["jumlah"]));?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
        <td colspan="2" class="tablecontent">&nbsp;</td>
    </tr>
     <tr class="content" class="tablecontent">
            <td colspan="2" class="tablecontent" nowrap>&nbsp;</td>
            <td colspan="3" class="tablecontent" nowrap>&nbsp;&nbsp;<input type="checkbox" name="cbPost[]" id="cbPost[<?php echo $dataTable[$i]["id_tra"];?>]" value="<?php echo $dataTable[$i]["id_tra"];?>" />
            <label for="cbPost[<?php echo $dataTable[$i]["id_tra"];?>]">Posting&nbsp;ke&nbsp;GL</label>&nbsp;-&nbsp;
            <a href="../daftar_transaksi/cashier_transaction.php?posting=1&id=<?php echo $enc->Encode($dataTable[$i]["id_tra"]);?>"><img src="<?php echo($ROOT);?>gambar/edit.png" border="0" width="16" height="16" align="middle" alt="Edit Transaction" title="Edit Transaction" /></a> - 
            <a href="../daftar_transaksi/cashier_transaction_list.php?posting=1&del=1&amp;id=<?php echo $enc->Encode($dataTable[$i]["id_tra"]);?>"><img src="<?php echo($ROOT);?>gambar/hapus.png" border="0" width="16" height="16" align="middle" alt="Delete Transaction" title="Delete Transaction" /></a>&nbsp;&nbsp;
            </td>
        </tr>
   
<?php } ?> 
 <tr class="tablesmallheader">
        <td colspan="5" nowrap>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" owrap>&nbsp;</td>
        <td colspan="3" nowrap>&nbsp;&nbsp;<img src="<?php echo($ROOT);?>gambar/arrow_kiriatas.gif" border="0" align="middle" />&nbsp;&nbsp;
        <a style="cursor: pointer;color: #1F457E;" OnClick="javascript: EW_selectKey(document.frmEdit,'cbPost[]',true);" >Check&nbsp;All</a>&nbsp;/&nbsp;
        <a style="cursor: pointer;color: #1F457E;" OnClick="javascript: EW_selectKey(document.frmEdit,'cbPost[]',false);">UnCheck&nbsp;All</a>&nbsp;</td>
    </tr>  
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr>
        <td width="50%" nowrap>&nbsp;&nbsp;<input type="submit" name="btpost" id="btpost" value="Post to GL" class="submit" />&nbsp;&nbsp;</td>
        <td align="right" width="50%" nowrap>&nbsp;&nbsp;<!--<img src="<?php echo($ROOT);?>images/printer.png" border="0" width="16" height="16" align="middle" class="img-button" alt="Print" title="Print" OnClick="javascript: print_doc();" />-->&nbsp;&nbsp;&nbsp;&nbsp;</td>
    </tr>
</table>
</form>
<?php //} ?>
<?php if($_POST["btnShow"] && !$dataTable){?>
<br><font color="red"><b>Maaf Data Tidak Tersedia</b></font>
<?php } ?>

</div>
</div>
