<?php     require_once("../penghubung.inc.php");     require_once($ROOT."lib/login.php");     require_once($ROOT."lib/encrypt.php");     require_once($ROOT."lib/datamodel.php");     require_once($ROOT."lib/currency.php");     require_once($ROOT."lib/dateLib.php");     require_once($ROOT."lib/tampilan.php");             $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);     $dtaccess = new DataAccess();     $enc = new TextEncrypt();          $auth = new CAuth();     $table = new InoTable("table","70%","left");     $usrId = $auth->GetUserId();	   $depId = $auth->GetDepId();                if($_POST["id"])       {       $transferId = & $_POST["id"];       }     else if($_GET["id"])       {       $transferId = $enc->Decode($_GET["id"]);      }          if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];         $skr = date("Y-m-d");     $editPage = "transfer_stok_detail_edit.php?id=".$enc->Encode($transferId);    $thisPage = "transfer_stok_detail_view.php?id=".$enc->Encode($transferId)."&klinik=".$_POST["klinik"];    $backPage = "transfer_stok_view.php";    $transferPage = "transfer_stok_view.php";              $sql = "select a.*,b.gudang_nama as dep_asal,c.gudang_nama as dep_tujuan             from logistik.logistik_transfer_stok a             left join logistik.logistik_gudang b on a.id_asal = b.gudang_id             left join logistik.logistik_gudang c on a.id_tujuan = c.gudang_id             where a.transfer_id = ".QuoteValue(DPE_CHAR,$transferId)." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);     $rs = $dtaccess->Execute($sql);     $dataTransfer = $dtaccess->Fetch($rs);        $_POST["id_pengirim"] = $dataTransfer["id_pengirim"];      if(!$_POST["transfer_tanggal_keluar"]) $_POST["transfer_tanggal_keluar"]=date("d-m-Y");     //$_POST["transfer_tanggal_keluar"] = $dataTransfer["transfer_tanggal_keluar"];          //jangan dihapus -- untuk lihat tanggal keluar nya nanti di item find     $_SESSION["tgl"] = $dataTransfer["transfer_tanggal_keluar"];          if($_POST["btnLanjut"]){     //echo "tanggal : ".$_POST["transfer_tanggal_keluar"];     $sql = "update logistik.logistik_transfer_stok set transfer_tanggal_keluar=".QuoteValue(DPE_DATE,date_db($_POST["transfer_tanggal_keluar"]))."            where transfer_id=".QuoteValue(DPE_CHAR,$transferId);     //echo $sql; die();     $dtaccess->Execute($sql);          $sql = "select a.*, b.item_nama, b.item_tree_kode, c.*,d.sup_nama from logistik.logistik_transfer_stok_detail a             left join logistik_item b on a.id_item = b.item_id             left join logistik.logistik_grup_item c on c.grup_item_id=b.id_kategori             left join global.global_supplier d on b.id_sup = d.sup_id             where id_transfer = ".QuoteValue(DPE_CHAR,$transferId)." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])."              order by b.item_nama asc";     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);     $dataTable = $dtaccess->FetchAll($rs);          $sql = "select count(a.transfer_detail_id) as jumlah             from logistik_transfer_stok_detail a             where a.id_transfer = ".QuoteValue(DPE_CHAR,$transferId).             " and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);         //echo $sql;     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);     $dataJumlah = $dtaccess->Fetch($rs);     //echo "jumlah stok".$dataJumlah["jumlah"];          //*-- config table ---*///*     $tableHeader = "&nbsp;INPUT PERMINTAAN BARANG";          $isAllowedDel = $auth->IsAllowed("transfer_stok",PRIV_DELETE);     $isAllowedUpdate = $auth->IsAllowed("transfer_stok",PRIV_UPDATE);     $isAllowedCreate = $auth->IsAllowed("transfer_stok",PRIV_CREATE);          // --- construct new table ---- //     $counterHeader = 0;     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";          $counterHeader++;         //if($isAllowedDel){          //$tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";          //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";          //$counterHeader++;     //}          //if($isAllowedUpdate){     if($dataTransfer["is_approve_kirim"] <>'y') {          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";          $counterHeader++;         }     //}              $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Barang";     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";          $counterHeader++;          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori";     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";          $counterHeader++;          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Barang";     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";          $counterHeader++;          //$tbHeader[0][$counterHeader][TABLE_ISI] = "Expire Date";     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";          //$counterHeader++;     $tbHeader[0][$counterHeader][TABLE_ISI] = "Supplier";     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";          $counterHeader++;          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah Permintaan";     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";          $counterHeader++;          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah Disetujui";     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";          $counterHeader++;              for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){               $tbContent[$i][$counter][TABLE_ISI] = $i+1;                              $tbContent[$i][$counter][TABLE_ALIGN] = "center";               $counter++;                    //if($isAllowedDel) {               //$tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["transfer_detail_id"].'">';                              //$tbContent[$i][$counter][TABLE_ALIGN] = "center";               //$counter++;          //}                    //if($isAllowedUpdate) {               if($dataTransfer["is_approve_kirim"] <>'y') {               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&klinik='.$dataTable[$i]["id_dep"].'&transfer_detail_id='.$enc->Encode($dataTable[$i]["transfer_detail_id"]).'&transfer_tanggal_keluar='.$_POST["transfer_tanggal_keluar"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/edit.png" alt="Edit" title="Edit" border="0"></a>';                              $tbContent[$i][$counter][TABLE_ALIGN] = "center";               $counter++;              }          //}                   $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];           $tbContent[$i][$counter][TABLE_ALIGN] = "left";          $counter++;                    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["sskel_nama"];           $tbContent[$i][$counter][TABLE_ALIGN] = "left";          $counter++;                    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_tree_kode"];           $tbContent[$i][$counter][TABLE_ALIGN] = "left";          $counter++;                    //$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);           //$tbContent[$i][$counter][TABLE_ALIGN] = "left";          //$counter++;          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["sup_nama"];           $tbContent[$i][$counter][TABLE_ALIGN] = "left";          $counter++;                    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["transfer_detail_jumlah_permintaan"]);           $tbContent[$i][$counter][TABLE_ALIGN] = "right";          $counter++;                  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["transfer_detail_jumlah"]);           $tbContent[$i][$counter][TABLE_ALIGN] = "right";          $counter++;              }          $colspan = count($tbHeader[0]);     //if($isAllowedDel) {     //     $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input onclick="" type="submit" name="btnDelete" value="Hapus" class="submit">&nbsp;';     //}       // if($dataJumlah["jumlah"]<40) {    //      $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="submit" onClick="document.location.href=\''.$editPage.'&klinik='.$dataTransfer["id_dep"].'&tgl='.$dataTransfer["transfer_tanggal_keluar"].'\'">&nbsp;';    // }              $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input onclick="return CetakYes();"  type="button" name="btnCetak" value="Cetak" class="submit">&nbsp;';    $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnKembali" value="Kembali" class="submit" onClick="document.location.href=\''.$backPage.'?klinik='.$_POST["klinik"].'\'">&nbsp;';              $tbBottom[0][0][TABLE_WIDTH] = "100%";     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;  */   }        if($_POST["btnSimpan"]){   //    $transferId = & $_POST["transferid"];     //   echo "Masuk".$transferId;                $sql = "update logistik.logistik_transfer_stok set               id_pengirim = ".QuoteValue(DPE_CHAR,$_POST["pengirimku"]).",              transfer_tanggal_keluar = ".QuoteValue(DPE_DATE,date_db($_POST["transfer_tanggal_keluar"]))." where              transfer_id = ".QuoteValue(DPE_CHAR,$transferId);       $rs = $dtaccess->Execute($sql);           // echo $sql; die();      for($i=0,$n=count($_POST["transfer_detail_id"]);$i<$n;$i++){            $dbTable = "logistik.logistik_transfer_stok_detail";            $dbField[0]  = "transfer_detail_id";   // PK      $dbField[1]  = "id_item";      $dbField[2]  = "transfer_detail_jumlah";            //$fakturId = $dtaccess->GetTransID();      $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["transfer_detail_id"][$i]);      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_item"][$i]);      $dbValue[2] = QuoteValue(DPE_NUMERIC,Stripcurrency($_POST["transfer_detail_jumlah".$i]));           // print_r($dbValue);       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);      // die();      $dtmodel->Update() or die("update  error");             unset($dbField);      unset($dbValue);      }            $cetak="yes";   }       $sql = "select * from logistik.logistik_pengirim where id_dep =".QuoteValue(DPE_CHAR,$depId)." and id_gudang= ".QuoteValue(DPE_CHAR,$dataTransfer["id_asal"])." order by pengirim_nama asc";     $rs = $dtaccess->Execute($sql);     $dataPengirim = $dtaccess->FetchAll($rs);     // echo $sql;    ?><script language="javascript" type="text/javascript">function CheckDataSave(frm){        if(!frm.transfer_nomor.value){		alert('No. Transfer Harus Diisi');		frm.po_nomor.focus();          return false;	}   	if(confirm('Input Permintaan Barang dilakukan dan tidak bisa dirubah, Apakah anda yakin ?')) {  BukaWindow('transfer_cetak.php?id=<?php echo $transferId;?>','Pemakaian Logistik');  //document.frmView.submit();  	  }else{  document.location.href='<?php echo $transferPage;?>';  }}var _wnd_new;function BukaWindow(url,judul){    if(!_wnd_new) {			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=900,height=600,left=100,top=100');	} else {		if (_wnd_new.closed) {			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=900,height=600,left=100,top=100');		} else {			_wnd_new.focus();		}	}     return false;}function CetakYes(){  BukaWindow('transfer_cetak.php?id=<?php echo $transferId;?>','Cetak Pengiriman');  document.location.href='<?php echo $transferPage;?>'; 		  }<?php if($cetak=='yes'){ ?>  BukaWindow('transfer_cetak.php?id=<?php echo $transferId;?>','Cetak Pengiriman');  document.location.href='<?php echo $transferPage;?>'; 		  <?php }  ?></script><div id="header"><table border="0" width="100%" valign="top"><tr><td width="10%" align="left" valign="top"><a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a></td><td width="90%" valign="top" align="right"><a href="#" target="_blank"><font size="6"><?php echo $tableHeader;?></font></a>&nbsp;&nbsp;</td></tr></table></div><div id="body"><div id="scroller"><form name="frmView" method="POST" action="<?php echo $thisPage; ?>"><table width="100%" border="0" cellpadding="0" cellspacing="0">     <tr>          <td width="15%">Nomer</td>          <td width="1%">:</td>          <td class="tablecontent-odd"><?php echo $dataTransfer["transfer_nomor"];?></td>     </tr>          <tr>          <td width="15%">Keterangan</td>          <td width="1%">:</td>          <td class="tablecontent-odd"><?php echo $dataTransfer["transfer_keterangan"];?></td>     </tr>     <tr>          <td>Asal Stok</td>          <td width="1%">:</td>          <td class="tablecontent-odd"><?php echo $dataTransfer["dep_asal"];?></td>     </tr>     <!--<tr>          <td>Pengirim Stok</td>          <td width="1%">:</td>          <td class="tablecontent-odd">            <select name="pengirimku" id="pengirimku" onKeyDown="return tabOnEnter(this, event);">							      <?php for($i=0,$n=count($dataPengirim);$i<$n;$i++){ ?>              <option value="<?php echo $dataPengirim[$i]["pengirim_id"];?>" <?php if($dataPengirim[$i]["pengirim_id"]==$_POST["id_pengirim"]) echo "selected"; ?>><?php echo $dataPengirim[$i]["pengirim_nama"];?></option>				      <?php } ?>			      </select>			    </td>     </tr>-->      <tr>          <td>Tujuan Stok</td>          <td>:</td>          <td class="tablecontent-odd"><?php echo $dataTransfer["dep_tujuan"];?></td>     </tr>     <!--<tr>          <td>Penerima Stok</td>          <td width="1%">:</td>          <td class="tablecontent-odd"><?php echo $dataTransfer["transfer_penerima"];?></td>     </tr>-->     <tr>          <td>Tanggal Permintaan</td>          <td>:</td>          <td class="tablecontent-odd"><?php          $dataTransfer["transfer_tanggal_permintaan"] = explode(" ",$dataTransfer["transfer_tanggal_permintaan"]);           echo format_date($dataTransfer["transfer_tanggal_permintaan"][0])." ".$dataTransfer["transfer_tanggal_permintaan"][1];           ?></td>     </tr>     <tr>          <td>Tanggal Pengiriman</td>          <td>:</td>          <?php if($dataTransfer["is_approve_kirim"]=='y') { ?>           <td class="tablecontent-odd">              <?php echo $view->RenderTextBox("transfer_tanggal_keluar","transfer_tanggal_keluar","15","30",$_POST["transfer_tanggal_keluar"],"inputField", "disabled",false);?>           </td>          <? }else{ ?>          <td class="tablecontent-odd">              <?php echo $view->RenderTextBox("transfer_tanggal_keluar","transfer_tanggal_keluar","15","30",$_POST["transfer_tanggal_keluar"],"inputField", null,false);?>              <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_keluar_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />          </td>          <? } ?>     </tr>     <tr>          <td colspan="3">            <input type="submit" name="btnLanjut" value="Lanjut" class="submit">            </td>     </tr></table><br /><br><? if($_POST["btnLanjut"]) { ?><table width="100%" border="1" cellpadding="0" cellspacing="0">      <tr>            <td class="tablecontent-odd"><strong>No</strong></td>            <td class="tablecontent-odd">Nama Barang</td>            <td class="tablecontent-odd">Kategori</td>            <td class="tablecontent-odd">Stok</td>            <!--<td class="tablecontent-odd">Supplier</td>-->            <td class="tablecontent-odd">Jumlah Permintaan</td>            <td class="tablecontent-odd">Jumlah Disetujui</td>      </tr>   <?php for($i=0,$n=count($dataTable);$i<$n;$i++) {                  $sql = "select stok_dep_saldo from logistik.logistik_stok_dep where id_item=".QuoteValue(DPE_CHAR,$dataTable[$i]["id_item"])."                and id_gudang=".QuoteValue(DPE_CHAR,$dataTransfer["id_asal"]);         $rs = $dtaccess->Execute($sql);         $stokGudang = $dtaccess->Fetch($rs);                  if(!$_POST["transfer_detail_jumlah$i"]){         $_POST["transfer_detail_jumlah$i"]= $dataTable[$i]["transfer_detail_jumlah_permintaan"];         }else{ $_POST["transfer_detail_jumlah$i"] = $dataTable[$i]["transfer_detail_jumlah"]; }   ?>               <tr>            <td width="1%"><? echo $i+1; ?></td>            <td width="25%"><? echo $dataTable[$i]["item_nama"]; ?>               <input type="hidden" name="id_item[<?php echo $i;?>]" id="id_item<?php echo $i;?>" value="<?php echo $dataTable[$i]["id_item"] ;?>" /></td>               <input type="hidden" name="transfer_detail_id[<?php echo $i;?>]" id="transfer_detail_id<?php echo $i;?>" value="<?php echo $dataTable[$i]["transfer_detail_id"] ;?>" /></td>            <td width="15%"><? echo $dataTable[$i]["grup_item_nama"]; ?></td>            <!--<td width="10%"><? echo $dataTable[$i]["item_tree_kode"]; ?></td>-->            <!--<td width="10%"><? echo $dataTable[$i]["sup_nama"]; ?></td>-->            <td width="10%" align="right"><? echo currency_format($stokGudang["stok_dep_saldo"]); ?>              <input type="hidden" name="stok_gudang<?php echo $i;?>" id="stok_gudang<?php echo $i;?>" value="<? echo currency_format($stokGudang["stok_dep_saldo"]); ?>" />            </td>            <td width="5%" align="right"><? echo currency_format($dataTable[$i]["transfer_detail_jumlah_permintaan"]); ?></td>            <td width="5%" align="right">            <?php echo $view->RenderTextBox("transfer_detail_jumlah$i","transfer_detail_jumlah$i","6","6",currency_format($_POST["transfer_detail_jumlah$i"]),"curedit", null,true);?>            </td>      </tr>   <? } ?>      <tr>            <td colspan ='7'>                <input type="submit" name="btnSimpan" value="Simpan" class="submit">            <!--    <input onclick="return CetakYes();"  type="button" name="btnCetak" value="Cetak" class="submit">-->                <input type="button" name="btnKembali" value="Kembali" class="submit" onClick="document.location.href='transfer_stok_view.php'">            </td>      </tr></table><?php echo $view->RenderHidden("id_pengirim","id_pengirim",$_POST["id_pengirim"]);?><?php echo $view->RenderHidden("transferid","transferid",$transferId);?><? } ?><?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?><?php echo $view->RenderHidden("klinik","klinik",$depId);?></form><script type="text/javascript">    Calendar.setup({        inputField     :    "transfer_tanggal_keluar",      // id of the input field        ifFormat       :    "<?=$formatCal;?>",       // format of the input field        showsTime      :    false,            // will display a time selector        button         :    "img_keluar_tanggal",   // trigger for the calendar (button ID)        singleClick    :    true,           // double-click mode        step           :    1                // show all years in drop-down boxes (instead of every other year as default)    }); </script></div></div>