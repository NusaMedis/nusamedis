<?php     // LIBRARY     require_once("../penghubung.inc.php");     require_once($LIB."login.php");     require_once($LIB."encrypt.php");     require_once($LIB."datamodel.php");     require_once($LIB."tampilan.php");         require_once($LIB."currency.php");        // INISIALISASY LIBRARY     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);     $dtaccess = new DataAccess();  	   $auth = new CAuth();	   $depNama = $auth->GetDepNama();     $usrId = $auth->GetUserId();     $depId = $auth->GetDepId();     $userName = $auth->GetUserName();	   $theDep = $auth->GetNamaLogistik();  //Ambil Gudang yang aktif	   	   $viewPage = "konfigurasi_edit.php";     	   // PRIVILLAGE    /*     if(!$auth->IsAllowed("apo_setup_konfigurasi",PRIV_READ) && !$auth->IsAllowed("man_pengaturan_konf_apotik",PRIV_READ)){          die("access_denied");          exit(1);     } elseif($auth->IsAllowed("apo_setup_konfigurasi",PRIV_READ)===1 || $auth->IsAllowed("man_pengaturan_konf_apotik",PRIV_READ)===1){          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";          exit(1);     } */    if ($_POST["btnSave"] ) {               $depId = & $_POST["dep_id"];        $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);	  $rs = $dtaccess->Execute($sql); 	  $gudang = $dtaccess->Fetch($rs);         		 $dbTable = "apotik.apotik_conf";                               $dbField[0] = "conf_id";   // PK		         	  $dbField[1] = "id_dep";                $dbField[2] = "conf_biaya_resep";                $dbField[3] = "conf_masa_berlaku";                 $dbField[4] = "conf_biaya_racikan";                $dbField[5] = "conf_harga_jual_hpp";                $dbField[6] = "conf_lock_transaksi";                $dbField[7] = "conf_apotik_central";                //$dbField[7] = "id_gudang";                $dbField[8] = "conf_biaya_tuslag";                $dbField[9] = "conf_biaya_tuslag_persen";                $dbField[10] = "conf_biaya_racikan_manual";                                if(!$gudang) $_POST["conf_id"]= $dtaccess->GetTransID();                else if($gudang) $_POST["conf_id"] = $gudang["conf_id"]; 		         	 			                       $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["conf_id"]);			          $dbValue[1] = QuoteValue(DPE_CHAR,$depId); 			          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_resep"]));                $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["masa_berlaku"]); 			          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_racikan"]));                $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["conf_harga_jual_hpp"]);                $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["conf_lock_transaksi"]);                $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["conf_apotik_central"]);                //$dbValue[7] = QuoteValue(DPE_CHAR,$theDep);                $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_tuslag"]));                $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["conf_biaya_tuslag_persen"]);                $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["conf_biaya_racikan_manual"]);            		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value            		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);            		if($gudang){            		$dtmodel->Update() or die("update  error");	                }else if(!$gudang){            		$dtmodel->Insert() or die("insert  error");                   }             		unset($dtmodel);            		unset($dbField);            		unset($dbValue);            		unset($dbKey);                               $sql = " select * from logistik.logistik_konfigurasi where konf_id = '1' and id_dep = ".QuoteValue(DPE_CHAR,$depId);               $rs = $dtaccess->Execute($sql);               $sqlgudangpusat = $dtaccess->Fetch($rs);                 		 $dbTable = "logistik.logistik_konfigurasi";                               $dbField[0] = "konf_id";   // PK		         	  $dbField[1] = "id_dep";                $dbField[2] = "konf_gudang";                $dbField[3] = "konf_nama";                 $dbField[4] = "konf_prosentase_hpp";                $dbValue[0] = QuoteValue(DPE_CHAR,'1');			          $dbValue[1] = QuoteValue(DPE_CHAR,$depId); 			          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["conf_apotik_central"]);                $dbValue[3] = QuoteValue(DPE_CHAR,'GUDANG PUSAT'); 			          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_racikan"]));                //$dbValue[7] = QuoteValue(DPE_CHAR,$theDep);                            		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value            		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);            		$dtmodel->Update() or die("update  error");	            		$simpan=1;            		unset($dtmodel);            		unset($dbField);            		unset($dbValue);            		unset($dbKey);  }          	$lokasi = $ROOT."/gambar/img_cfg";        	        	$sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);        	$rs_edit = $dtaccess->Execute($sql);        	$row_edit = $dtaccess->Fetch($rs_edit);        	$dtaccess->Clear($rs_edit);        	//echo $sql;        	$_POST["conf_id"] = $row_edit["conf_id"];          $_POST["biaya_resep"] = $row_edit["conf_biaya_resep"];          $_POST["id_dep"] = $row_edit["id_dep"];          $_POST["masa_berlaku"] = $row_edit["conf_masa_berlaku"];                  $_POST["biaya_racikan"] = $row_edit["conf_biaya_racikan"];          $_POST["conf_harga_jual_hpp"] = $row_edit["conf_harga_jual_hpp"];          $_POST["conf_lock_transaksi"] = $row_edit["conf_lock_transaksi"];          $_POST["conf_apotik_central"] = $row_edit["conf_apotik_central"];          $_POST["biaya_tuslag"] = $row_edit["conf_biaya_tuslag"];        	$view->CreatePost($row_edit);                    if(!$_POST["gudang_obat"]) $_POST["gudang_obat"] = 'A';                    $sql = "select * from logistik.logistik_gudang order by gudang_nama asc";          $rs = $dtaccess->Execute($sql);          $dataGudang = $dtaccess->FetchAll($rs);          ?><?php// echo $view->RenderBody("module.css",true,false,"KONF. APOTIK"); ?><script type="text/javascript">function CheckDataSave(frm){    	  if(!frm.gudang_obat.value){		alert('Gudang obat harus diisi');          return false;	} 	          return false;	}  	   </script><title>KONFIGURASI </title><!DOCTYPE html><html lang="en">  <?php require_once($LAY."header.php") ?>  <body class="nav-md">    <div class="container body">      <div class="main_container">        <?php require_once($LAY."sidebar.php") ?>        <!-- top navigation -->          <?php require_once($LAY."topnav.php") ?>        <!-- /top navigation -->        <!-- page content -->        <div class="right_col" role="main">          <div class="">      <div class="clearfix"></div>      <!-- row filter -->      <div class="row">              <div class="col-md-12 col-sm-12 col-xs-12">                <div class="x_panel">                  <div class="x_title">                    <h2>Konfigurasi Apotik</h2>                    <div class="clearfix"></div>                  </div>                  <div class="x_content"><form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>"><table width="100%" border="0" class="table table-bordered" >           <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Biaya Resep&nbsp;</td>              <td class="tablecontent" width="70%" align="left"><?php echo $view->RenderTextBox("biaya_resep","biaya_resep","20","100",currency_format($_POST["biaya_resep"]),"inputField", null,true);?>&nbsp;</td>    </tr>     <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Biaya Racikan&nbsp;</td>              <td class="tablecontent" width="70%" align="left"><?php echo $view->RenderTextBox("biaya_racikan","biaya_racikan","20","100",currency_format($_POST["biaya_racikan"]),"inputField", null,true);?>&nbsp;        <input type="checkbox" name="conf_biaya_racikan_manual" value="y" <?php if ($_POST["conf_biaya_racikan_manual"] == "y") {echo "checked";} ?> >Manual      </td>    </tr>    <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Biaya Tuslag&nbsp;</td>              <td class="tablecontent" width="70%" align="left"><?php echo $view->RenderTextBox("biaya_tuslag","biaya_tuslag","20","100",currency_format($_POST["biaya_tuslag"]),"inputField", null,true);?>&nbsp;      <input type="checkbox" name="conf_biaya_tuslag_persen" value="y" <?php if ($_POST["conf_biaya_tuslag_persen"] == "y") {echo "checked";} ?> >Persen (%)    </td>    </tr>       <!--  <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Tempo Masa Berlaku&nbsp;</td>              <td class="tablecontent" width="70%" align="left">         <select name="masa_berlaku" id="masa_berlaku" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">            <option value="0">Bulan Ini</option>						<option value="-30" <?php if($_POST["masa_berlaku"]=="-30") echo "selected";?>>Sebelum 1 Bulan</option>            <option value="-60" <?php if($_POST["masa_berlaku"]=="-60") echo "selected";?>>Sebelum 2 Bulan</option>            <option value="-90" <?php if($_POST["masa_berlaku"]=="-90") echo "selected";?>>Sebelum 3 Bulan</option>            <option value="-120" <?php if($_POST["masa_berlaku"]=="-120") echo "selected";?>>Sebelum 4 Bulan</option>            <option value="-150" <?php if($_POST["masa_berlaku"]=="-150") echo "selected";?>>Sebelum 5 Bulan</option>            <option value="-180" <?php if($_POST["masa_berlaku"]=="-180") echo "selected";?>>Sebelum 6 Bulan</option>            <option value="-210" <?php if($_POST["masa_berlaku"]=="-210") echo "selected";?>>Sebelum 7 Bulan</option>            <option value="-240" <?php if($_POST["masa_berlaku"]=="-240") echo "selected";?>>Sebelum 8 Bulan</option>            <option value="-270" <?php if($_POST["masa_berlaku"]=="-270") echo "selected";?>>Sebelum 9 Bulan</option>            <option value="-300" <?php if($_POST["masa_berlaku"]=="-300") echo "selected";?>>Sebelum 10 Bulan</option>            <option value="-330" <?php if($_POST["masa_berlaku"]=="-330") echo "selected";?>>Sebelum 11 Bulan</option>             <option value="-360" <?php if($_POST["masa_berlaku"]=="-360") echo "selected";?>>Sebelum 12 Bulan</option>                       </select>       </td>    </tr>    <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Harga Jual dengan HPP&nbsp;</td>              <td class="tablecontent" width="70%" align="left">         <select name="conf_harga_jual_hpp" id="conf_harga_jual_hpp" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">						<option value="n" <?php if($_POST["conf_harga_jual_hpp"]=="n") echo "selected";?>>Tidak</option>						<option value="y" <?php if($_POST["conf_harga_jual_hpp"]=="y") echo "selected";?>>Ya</option>			</select>       </td>    </tr>			    <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Lock Transaksi untuk Opname&nbsp;</td>              <td class="tablecontent" width="70%" align="left">         <select name="conf_lock_transaksi" id="conf_lock_transaksi" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">						<option value="n" <?php if($_POST["conf_lock_transaksi"]=="n") echo "selected";?>>Tidak</option>						<option value="y" <?php if($_POST["conf_lock_transaksi"]=="y") echo "selected";?>>Ya</option>			</select>       </td>    </tr>			    <tr>      <td class="tablecontent" width="20%" align="right">&nbsp;&nbsp;Gudang Apotik Central&nbsp;</td>              <td class="tablecontent" width="70%" align="left">         <select name="conf_apotik_central" id="conf_apotik_central" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">						<option value="">Pilih Gudang</option>            <?php for($i=0,$n=count($dataGudang);$i<$n;$i++){ ?>						<option value="<?php echo $dataGudang[$i]["gudang_id"]?>" <?php if($_POST["conf_apotik_central"]==$dataGudang[$i]["gudang_id"]) echo "selected";?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>            <?php } ?>			</select>       </td>    </tr> -->      </table>           <table width="100%" border="0" >          <tr>               <td colspan="2" align="center">                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false);?><!--,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>  -->                   </td>          </tr>     </table><?php if($simpan) { ?><font color="red"><strong>Konfigurasi telah disimpan</strong></font><?php } ?><?php echo $view->RenderHidden("conf_id","conf_id",$_POST["conf_id"]);?><?php echo $view->RenderHidden("dep_id","dep_id",$depId);?>   <?php echo $view->RenderHidden("id_gudang","id_gudang",$theDep);?><?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?> </form></div>		 </div>  		<!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">			<tr>      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>      </tr>      </table>--><?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?><?php// echo $view->RenderBodyEnd(); ?>            </div>          </div>        </div>        <!-- /page content -->        <!-- footer content -->          <?php require_once($LAY."footer.php") ?>        <!-- /footer content -->      </div>    </div><?php require_once($LAY."js.php") ?>  </body></html>