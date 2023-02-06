<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
        
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
     

     $editPage = "trans_opname_edit.php?";
     $thisPage = "trans_opname.php?";
     $detPage = "trans_opname_proses.php?";
     $printPage = "trans_opname_cetak.php?";
     $skr = date("d-m-Y");
    
     if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
       } else if($_POST["klinik"]) { 
        $_POST["klinik"] = $_POST["klinik"]; 
         } else { 
          $_POST["klinik"] = $depId; 
          }          
     
     if($_GET["id_gudang"]) $_POST["id_gudang"]=$_GET["id_gudang"];
     if($_GET["id_periode"]) $_POST["id_periode"]=$_GET["id_periode"];
     if($_GET["tahun"]) $_POST["tahun"]=$_GET["tahun"];
     
     if(!$_POST["tahun"]) $_POST["tahun"]=date('Y');
     
     $plx = new expAJAX("GetPeriode");
     
     function GetPeriode($thn){
        global $dtaccess,$view,$depId,$ROOT; 
         $sql = "select * from logistik.logistik_penerimaan_periode where extract(year from penerimaan_periode_tanggal_awal)=".QuoteValue(DPE_CHAR,$thn)." 
                order by penerimaan_periode_tanggal_awal asc";
         $rs = $dtaccess->Execute($sql); 
      	 $dataPeriode = $dtaccess->FetchAll($rs);
    			unset($periode);
    			$periode[0] = $view->RenderOption("","[Pilih Periode]",$show);
    			$i = 1;
    			
         for($i=0,$n=count($dataPeriode);$i<$n;$i++){   
             if($_POST["id_periode"]==$dataPeriode[$i]["penerimaan_periode_id"]) $show = "selected";
             $periode[$i+1] = $view->RenderOption($dataPeriode[$i]["penerimaan_periode_id"],$dataPeriode[$i]["penerimaan_periode_nama"],$show);
             unset($show);
         }
    			$str = $view->RenderComboBox("id_periode","id_periode",$periode,null,null,null);
    	 return $str;
     }

//     if(!$_POST["id_gudang"]) $_POST["id_gudang"]=$theDep;     
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;       
     
     $addPage = "trans_opname_edit.php?tambah=1&klinik=".$_POST["klinik"]."&id_periode=".$_POST["id_periode"]."&id_gudang=".$_POST["id_gudang"]."&tahun=".$_POST["tahun"];
     
     if($_POST["id_periode"]){ $sql_where[] = "a.id_periode = ".QuoteValue(DPE_CHAR,$_POST["id_periode"]);
     } else {
     if($_POST["tanggal_awal"]) $sql_where[] = "a.opname_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     if($_POST["tanggal_akhir"]) $sql_where[] = "a.opname_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     }
     if($_POST["id_gudang"] && $_POST["id_gudang"]<>'--') $sql_where[] = "a.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);

     //$sql_where[] = "1=1";
     
     if($sql_where) $sql_where = implode(" and ",$sql_where);


     
     $sql = "select a.*,b.gudang_nama,c.penerimaan_periode_nama
             from logistik.logistik_opname a
             left join logistik.logistik_gudang b on a.id_gudang = b.gudang_id
             left join logistik.logistik_penerimaan_periode c on a.id_periode = c.penerimaan_periode_id";
     $sql .= " where a.opname_flag='M' and a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
     if($sql_where) $sql .= " and ".$sql_where;
     //if ($_POST["id_periode"] && $_POST["id_periode"]<>'--') $sql .= " and a.id_periode = ".QuoteValue(DPE_CHAR,$_POST["id_periode"]);
     //if ($_POST["id_periode"] && $_POST["id_gudang"]<>'--') $sql .= " and a.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $sql .= " order by penerimaan_periode_tanggal_awal,id_gudang asc";     
  //   echo $sql;


     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Opname";
     
     $isAllowedDel = $auth->IsAllowed("transfer_stok",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("transfer_stok",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("transfer_stok",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
    
     
     //if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     //}
          
          /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;*/

     //if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Print";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     //}
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Periode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Gudang";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
          
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){          
          //cari opname terakhir gudang tersebut
          $sql = "select opname_id from logistik.logistik_opname where id_gudang = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_gudang"])."
                  order by opname_tanggal desc";
          $rs = $dtaccess->Execute($sql);
          $opnameTerakhir = $dtaccess->Fetch($rs);  
              
              if($dataTable[$i]["opname_id"]==$opnameTerakhir["opname_id"]){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'klinik='.$dataTable[$i]["id_dep"].'&id='.$dataTable[$i]["opname_id"].'&id_gudang='.$dataTable[$i]["id_gudang"].'&id_periode='.$dataTable[$i]["id_periode"].'&tahun='.$_POST["tahun"].'"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               }else{
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               }
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               /*$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detPage.'klinik='.$dataTable[$i]["id_dep"].'&id='.$dataTable[$i]["pemakaian_id"].'&id_gudang='.$dataTable[$i]["id_gudang"].'&id_periode='.$dataTable[$i]["id_periode"].'&tahun='.$_POST["tahun"].'"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/cari.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;*/

             $tbContent[$i][$counter][TABLE_ISI] = '<img onclick="BukaWindow(\''.$printPage.'klinik='.$dataTable[$i]["id_dep"].'&id='.$enc->Encode($dataTable[$i]["opname_id"]).'&id_gudang='.$dataTable[$i]["id_gudang"].'&id_periode='.$dataTable[$i]["id_periode"].'&tahun='.$_POST["tahun"].'\',\' Cetak \')" hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak" title="Cetak" style="cursor:pointer;" border="0">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penerimaan_periode_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
                              
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["gudang_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
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



		// bikin combo box untuk gudang //
   	$sql = "select * from logistik.logistik_gudang where id_dep =".QuoteValue(DPE_CHAR,$depId)."
            and  gudang_flag = 'M' order by gudang_nama asc"; 
 
    $rs = $dtaccess->Execute($sql);            
		$dataGudang = $dtaccess->FetchAll($rs);
    //echo $sql; 
    
    $year = date('Y')+5;
    //echo $year;
    $a=0;
    $tahun[0] = $view->RenderOption("","[Pilih Tahun]",$show);
      for($i=2010;$i<=$year;$i++){
             if($_POST["tahun"]==$i) $show = "selected";
             $tahun[$a+1] = $view->RenderOption($i,$i,$show);
             $a++;   
             unset($show);            
        }
    //print_r($tahun);

		// bikin combo box untuk periode //
   	/*$sql = "select * from logistik.logistik_penerimaan_periode order by penerimaan_periode_tanggal_awal asc";
    $rs = $dtaccess->Execute($sql); 
		$dataPeriode = $dtaccess->FetchAll($rs);*/
         
?>

<?php echo $view->RenderBody("ipad_depans.css",true,"STOK OPNAME"); ?>
<script language="JavaScript">
<?php $plx->Run(); ?>

function CariPeriode(id){ 
	document.getElementById('div_periode').innerHTML = GetPeriode(id,'type=r');
}

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

function CheckDataSave(frm)
{  
  if(!document.getElementById('id_gudang').value || document.getElementById('id_gudang').value=='--'){
    alert('Gudang harus dipilih!');
    document.getElementById('id_gudang').focus();
    return false;
  }	
	
	return true;
          
}
</script>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6">STOK OPNAME</font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">
 <br />
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"];?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr class="tablecontent">
          <td width="20%" align="right">&nbsp;&nbsp;Nama GLFK&nbsp;&nbsp;</td>
          <td width="80%" align="left">
			 <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onchange="rejenis(this.value);">
				<option class="inputField" value="" >- Semua  -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= ".."; 
				?>
					<option class="inputField" value="<?php echo $dataKlinik[$i]["dep_id"];?>"<?php if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php }?>
				</select>
		  </td>
		 </tr>
     <tr>
          <td align="right" width="10%" class="tablecontent">&nbsp;&nbsp;Tanggal Opname&nbsp;</td>
          <td  width="80%" class="tablecontent">
      			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
      			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
                     - 
      			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
      			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
          </td>
     </tr>
     <tr>
          <td align="right" width="15%" class="tablecontent">&nbsp;Tahun </td>
          <td class="tablecontent" colspan="4">
            <?php echo $view->RenderComboBox("tahun","tahun",$tahun,null,null,"onchange=\"javascript:return CariPeriode(document.getElementById('tahun').value);\"");?>
      </tr>
      <tr>
          <td align="right" width="15%" class="tablecontent">&nbsp;Periode </td>
          <td class="tablecontent" colspan="4">
            <div id="div_periode"><?php echo GetPeriode($_POST["tahun"]);?></div>
            <!--<select name="id_periode" id="id_periode" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
              <option value="--">[- Pilih Periode -]</option>
                <?php for($i=0,$n=count($dataPeriode);$i<$n;$i++) { ?>
    						 <option value="<?php echo $dataPeriode[$i]["penerimaan_periode_id"];?>" <?php if($_POST["id_periode"]==$dataPeriode[$i]["penerimaan_periode_id"]) echo "selected";?>><?php echo $dataPeriode[$i]["penerimaan_periode_nama"];?></option>
    						<?php } ?>               
            </select>-->
      </tr>
      <tr>
      <td width='10%' align="right" class="tablecontent">&nbsp;&nbsp;Gudang&nbsp;</td>
      <td align="left" class="tablecontent">
        <select name="id_gudang" id="id_gudang" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">
          <option value="--">[- Pilih Gudang -]</option>
            <?php for($i=0,$n=count($dataGudang);$i<$n;$i++) { ?>
						 <option value="<?php echo $dataGudang[$i]["gudang_id"];?>" <?php if($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) echo "selected";?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>
						<?php } ?>               
        </select>
      </td>
      </tr>

		 <tr>
				<td colspan="2" align="center">					
            <input type="submit" name="btnLanjut" value="Lanjut" class="submit" onClick="javascript:return CheckDataSave(this.form);">
            <input type="button" name="btnAdd" value="Tambah Baru" id="button" class="submit" onClick="document.location.href='<?php echo $addPage;?>'">
				</td>
			</tr>
		 </table> 
    </form>

<script type="text/javascript">
   Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    }); 
</script>

    <br />
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
  <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>
</div>
</div>

<?php echo $view->RenderBodyEnd(); ?>