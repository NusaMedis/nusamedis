<?php     
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
	   require_once($LIB."tampilan.php");	
      
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $depNama = $auth->GetDepNama(); 
    $userName = $auth->GetUserName();     
	   $auth = new CAuth();
     $err_code = 0;
     $tahunTarif = $auth->GetTahunTarif();
     $depId = $auth->GetDepId();
      
	   $plx = new expAJAX("CheckDataIcd,SetCmbKamar");
     $findPage = "cari_tarif.php?";
     
     if(!$auth->IsAllowed("man_tarif_biaya_akomodasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_biaya_akomodasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     
     	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
    	else $_x_mode = "New";
   
	    //if(!$_POST["id_biaya"])  $_POST["id_biaya"] = $_GET["id_biaya"];
      if($_GET['id_tahun_tarif']) $_POST["id_tahun_tarif"] = & $_GET['id_tahun_tarif'];
      if($_GET['id_kelas']) $_POST["id_kelas"] = & $_GET['id_kelas'];
      if($_GET['id_kamar']) $_POST["id_kamar"] = & $_GET['id_kamar']; 
      
      $editPage = "biaya_akomodasi_edit.php";
      $backPage = "biaya_akomodasi_view.php?id_tahun_tarif=".$_POST["id_tahun_tarif"]."&id_kelas=".$_POST["id_kelas"]."&id_kamar=".$_POST["id_kamar"];
      
      if ($_GET["id"])
         $tableHeader = "EDIT SETUP BIAYA AKOMODASI "; 
      else
         $tableHeader = "TAMBAH SETUP BIAYA AKOMODASI";
	
	function CheckDataIcd($icdNomor,$biayaAkomodasiId=null)
	{
          global $dtaccess;                  
	        $sql = "SELECT * FROM global.global_biaya_akomodasi
              WHERE biaya_akomodasi_id =".$_GET["id"];
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
            
		return $dataAdaIcd["biaya_akomodasi_id"];
     }  
      
      function SetCmbKamar($id_kategori){
          global $dtaccess, $view;
          
          $sql = "select a.* from klinik.klinik_kamar a 
         where a.id_kelas = ".QuoteValue(DPE_CHAR,$id_kategori);
         //return $sql."-".$_POST["id_kamar"];
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataKamar = $dtaccess->FetchAll($rs);
           
          unset($opt_kamar);
          $opt_kamar[0] = $view->RenderOption("","[pilih kamar]",$show);
          $i=1;
          
          for($i=0,$n=count($dataKamar);$i<$n;$i++){
            if($_POST["id_kamar"]==$dataKamar[$i]["kamar_id"]) $show="selected";
            $opt_kamar[$i+1] = $view->RenderOption($dataKamar[$i]["kamar_id"],$dataKamar[$i]["kamar_nama"],$show);
            unset($show);
          }
          $str = $view->RenderComboBox("id_kamar","id_kamar",$opt_kamar,null,null,null);
          
          return $str;
     }
        
	   //View
     if ($_GET["id"]) {         
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaAkomodasiId = $enc->Decode($_GET["id"]);
          }
           
          $sql = "SELECT a.*,b.biaya_nama FROM global.global_biaya_akomodasi a left join klinik.klinik_biaya b on a.id_biaya=b.biaya_id
              WHERE a.biaya_akomodasi_id= ".QuoteValue(DPE_CHAR,$biayaAkomodasiId); 
          
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);   
          
          $_POST["id_kelas"]= $row_edit["id_kelas"];
          $_POST["id_kamar"]= $row_edit["id_kamar"];
          $_POST["id_biaya"]= $row_edit["id_biaya"];
          $_POST["id_dep"]= $row_edit["id_dep"];
          $_POST["biaya_nama"]= $row_edit["biaya_nama"]; 
          $_POST["id_tahun_tarif"] = $row_edit["id_tahun_tarif"];      
           
          $dtaccess->Clear($rs_edit);
          $view->CreatePost($row_edit);
     }
      
	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {     
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     //Tambah atau Edit Ketika klik tombol Simpan
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               
               $biayaAkomodasiId = & $_POST["biaya_akomodasi_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "global.global_biaya_akomodasi";
               
               $dbField[0] = "biaya_akomodasi_id";   // PK
               $dbField[1] = "id_kelas";
               $dbField[2] = "id_kamar";
               $dbField[3] = "id_biaya";
               $dbField[4] = "id_dep";
               $dbField[5] = "id_tahun_tarif";
			
               if(!$biayaAkomodasiId) $biayaAkomodasiId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaAkomodasiId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);

			        // print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:".$backPage);
               exit();        
          }
     }
     
     //delete
      if ($_GET["del"]) 
      {
          $biayaAkomodasiId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_biaya_akomodasi where biaya_akomodasi_id = ".QuoteValue(DPE_CHAR,$biayaAkomodasiId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }

          

      // Combo Box
    
     $sql = "select * from klinik.klinik_kelas order by kelas_id ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs); 
     
     $opt_kategori[0] = $view->RenderOption("","[Pilih Kelas]",$show);
     for($i=0,$n=count($dataKelas);$i<$n;$i++){   
         if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
         $opt_kategori[$i+1] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
         unset($show);
     }   
    
     $sql= "select * from klinik.klinik_tahun_tarif order by tahun_tarif_nama ASC";   
     $rs = $dtaccess->Execute($sql);
     $dataTahunTarif = $dtaccess->FetchAll($rs);    
?>
                                                   
<div id="body">
<div id="scroller">
 
<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tablesmallheader">
        <td width="100%">Tahun Tarif</td>
    </tr>
</table>
                      
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Edit Biaya Akomodasi</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
      <tr >   <!--bagian awal combo nama tarif -->
      <td align="right" class="tablecontent" width="20%">&nbsp;Tahun Tarif&nbsp;&nbsp;</td>
			 <td class="tablecontent" width="80%">
       <select name="id_tahun_tarif" class="inputField">      
       <option value="--" >[ Pilih Tahun Tarif ]</option>
				<?php 
       		for($i=0,$n=count($dataTahunTarif);$i<$n;$i++){
				?>
					<option class="inputField" value="<?php echo $dataTahunTarif[$i]["tahun_tarif_id"];?>"
        <?php if($dataTahunTarif[$i]["tahun_tarif_id"]==$_POST['id_tahun_tarif']) {echo"selected";}?>>
        <?php echo $dataTahunTarif[$i]["tahun_tarif_nama"];?>&nbsp;
          </option>
				<?php } ?>
				</select>
		  </td> 
		 </tr> <!--bagian akhir combo --> 
      <tr>
        <td align="right" class="tablecontent" width="20%">&nbsp;Kelas :&nbsp;&nbsp;</td>
			  <td class="tablecontent" width="80%">
          <?php echo $view->RenderComboBox("id_kelas","id_kelas",$opt_kategori,null,null,"onchange=\"javascript:return CariKamar(document.getElementById('id_kelas').value);\"");?>
        </td>
     </tr>
     <tr >
        <td align="right" class="tablecontent" width="20%">&nbsp;Kamar :&nbsp;&nbsp;</td>
    		<td class="tablecontent" width="80%">
          <div id="div_kamar">
            <?php echo SetCmbKamar($_POST["id_kelas"]);?>
          </div>
        </td>
     </tr>
    <tr>
       <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Nama Tarif :&nbsp;&nbsp;<?if(readbit($err_code,3) || readbit($err_code,4)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
       <td width="70%" class="tblCol">
          <!--               <?php //echo $view->RenderTextBox("pgw_nama","pgw_nama","30","50",$_POST["usr_name"],"inputField", null,false);?>&nbsp;&nbsp;  -->
       <input  type="text" name="biaya_nama" id="biaya_nama" size="30" maxlength="50" readonly value="<?php echo $_POST["biaya_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
       <input type="hidden" name="id_biaya" id="id_biaya" value="<?php echo $_POST["id_biaya"];?>"/>     
       <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Tarif">
       <img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Tarif" alt="Cari Tarif" class="tombol" align="middle"/></a>
       
       </td>
       <!--   <button class="submit" id="btnSearch" onclick="return ajaxFileUpload();">Upload Foto</button>-->
   </tr>
     
    
    
    
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$backPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>
		</td> 
<script>document.frmEdit.icd_nomor.focus();</script>
