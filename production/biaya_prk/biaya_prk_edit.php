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
     require_once($LIB."tree.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $skr = date("d-m-Y");
     $userData = $auth->GetUserData();
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $plx = new expAJAX("GetKategori");
     $tahunTarif = $auth->GetTahunTarif();
          	   
	   $findPrkPage = "prk_find.php?";
	   $findPrk2Page = "prk_find2.php?";
 	   $findTindPage = "kategori_tindakan_find.php?";
	   
	  /*if(!$auth->IsAllowed("akunt_master_setup_biaya_prk",PRIV_READ)){
         echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("akunt_master_setup_biaya_prk",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     } */
     
     if($_GET["klinik"]) { 
          $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
          $_POST["klinik"] = $_POST["klinik"]; 
      }else if($_GET["tambah"]) { 
          $_POST["klinik"] = $_GET["tambah"]; 
      }else if(!$_POST["klinik"]) { 
          $_POST["klinik"] = $depId; 
      }
      $klinik = $_POST["klinik"]; 
   
	

     
     //if($_GET["id_tahun_tarif"]) $_POST["id_tahun_tarif"]=$_GET["id_tahun_tarif"];       
     if($_GET["id_poli"] && $_GET["id_poli"]!=='') $_POST["id_poli"]=$_GET["id_poli"];
     if(!$_GET["id_poli"] || $_GET["id_poli"]=='') $_POST["id_poli"]=$_POST["id_poli"];
     	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["biaya_prk_id"])  $itemId = & $_POST["biaya_prk_id"];

    function GetKategori($katHeaderId,$katTindakanId=null)
	{
     global $dtaccess,$view,$depId;
     
	     

     	 $sql = "select a.* from klinik.klinik_kategori_tindakan a
        left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id = a. id_kategori_tindakan_header
        where a.id_kategori_tindakan_header =".QuoteValue(DPE_CHAR,$katHeaderId)."     
        order by a.kategori_tindakan_nama asc";
     //return $sql;   
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataKatTindakan = $dtaccess->FetchAll($rs_edit);
			unset($kategori);
			$kategori[0] = $view->RenderOption("--","[Pilih Kategori Tindakan]",$show);
//			$i = 1;
			
     for($i=0,$n=count($dataKatTindakan);$i<$n;$i++){   
         if($katTindakanId==$dataKatTindakan[$i]["kategori_tindakan_id"]) $show = "selected";
         $labelBiaya=substr($dataKatTindakan[$i]["kategori_tindakan_nama"], 0, 35);
         $kategori[$i+1] = $view->RenderOption($dataKatTindakan[$i]["kategori_tindakan_id"],$labelBiaya,$show);
         unset($show);
     }
			$str = $view->RenderComboBox("id_kategori_tindakan","id_kategori_tindakan",$kategori,null,null,null);
                       
	 return $str;
  } 
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $itemId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*, d.nama_prk as prk_pendapatan, c.nama_prk as prk_debet from klinik.klinik_biaya_prk a
                    left join gl.gl_perkiraan d on d.id_prk = a.id_prk
                    left join global.global_auth_poli b on b.poli_id=a.id_poli
                    left join gl.gl_perkiraan c on c.id_prk = a.id_prk_debet
                    where biaya_prk_id = ".QuoteValue(DPE_CHAR,$itemId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
 
          $_POST["id_poli"] = $row_edit["id_poli"];
          $_POST["id_prk"] = $row_edit["id_prk"];
          $_POST["prk_pendapatan"] = $row_edit["prk_pendapatan"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $_POST["id_biaya"] = $row_edit["id_biaya"];
          $_POST["id_prk_debet"] = $row_edit["id_prk_debet"];
          $_POST["prk_debet"] = $row_edit["prk_debet"];
          $_POST["id_kategori_tindakan"] = $row_edit["id_kategori_tindakan"];
          
          $kembali = "biaya_prk_view.php?kembali=".$_POST["klinik"]."&id_poli=".$_POST["id_poli"];
          
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
      if ($_GET["tambah"]) {
        $_POST["klinik"] = $_GET["tambah"]; 
        $kembali = "biaya_prk_view.php?kembali=".$_POST["klinik"]."&id_poli=".$_POST["id_poli"];
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $biayaPrkId = & $_POST["biaya_prk_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya_prk";
               
               $dbField[0] = "biaya_prk_id";   // PK
			         $dbField[1] = "id_prk";
			         $dbField[2] = "id_dep";
               $dbField[3] = "id_poli";
               $dbField[4] = "id_prk_debet";
               $dbField[5] = "id_kategori_tindakan";
                              
               if(!$biayaPrkId) $biayaPrkId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaPrkId);
			         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_prk"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_prk_debet"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan"]);
               //print_r($dbValue); die();

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
                  unset($biayaPrkId);
               
               
               $sql = " update klinik.klinik_biaya set id_prk = ".QuoteValue(DPE_CHAR,$_POST["id_prk"]).",
                        id_prk_beban =".QuoteValue(DPE_CHAR,$_POST["id_prk_debet"])."
                        where biaya_kategori = ".QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan"])."
                        and id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
               $rs  = $dtaccess->Execute($sql);
          //     echo $sql; die();
               $kembali = "biaya_prk_view.php?kembali=".$_POST["klinik"]."&id_poli=".$_POST["id_poli"];
               
               header("location:".$kembali);
               exit();
          }
 
     }
     
     //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$klinik."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
 
       // cari jenis layanan e
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_id desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tipeBiayaLayanan = $dtaccess->FetchAll($rs);
     
     $sql = "select * from klinik.klinik_tahun_tarif order by tahun_tarif_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataTahun = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_auth_poli where poli_tipe='J' or poli_tipe='G' or poli_tipe='L' or poli_tipe='R' or poli_tipe='M' 
            order by poli_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);

     $sql = "select * from klinik.klinik_kategori_tindakan where id_dep= '".$klinik."'";
     $rs  = $dtaccess->Execute($sql);
     $dataKatTindakan = $dtaccess->FetchAll($rs);
 
  if ($_GET["del"]) {
           $itemId = $enc->Decode($_GET["id"]);
 //   echo "masuk" $itemId;
  //  die();
     
           $sql = "delete from klinik.klinik_biaya_prk where id_dep = ".QuoteValue(DPE_CHAR,$depId)." and biaya_prk_id = ".QuoteValue(DPE_CHAR,$itemId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

           $kembali = "biaya_prk_view.php?kembali=".$_POST["klinik"]."&id_poli=".$_POST["id_poli"];
               
           header("location:".$kembali);
           exit(); 
     }
       
      //default dari setting mana yang default untuk klinik kategori tindakan header per poli
      $sql = "select id_kategori_tindakan_header from klinik.klinik_biaya_poli
             where biaya_poli_default='y' and id_poli =".QuoteValue(DPE_CHAR,$_POST["id_poli"]).
             " and id_tahun_tarif=".QuoteValue(DPE_CHAR,$tahunTarif); 
		  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataKategoriDefault = $dtaccess->Fetch($rs_edit);
      $_POST["id_kat_header_default"] = $dataKategoriDefault["id_kategori_tindakan_header"]; 
      
      //combo kategori tindakan header
      $sql = "select a.* from klinik.klinik_kategori_tindakan_header a 
             left join klinik.klinik_biaya_poli c on a.kategori_tindakan_header_id=c.id_kategori_tindakan_header
             where c.id_poli =".QuoteValue(DPE_CHAR,$_POST["id_poli"])." order by a.kategori_tindakan_header_nama"; 
		  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);  
      $dataKategori = $dtaccess->FetchAll($rs_edit);          
      $katTindakanHeader[0] = $view->RenderOption("--","[Pilih Kategori Tindakan Header]",$show);
      for($i=0,$n=count($dataKategori);$i<$n;$i++)
      {
        unset($show);
        if($_POST["id_kat_header_default"]==$dataKategori[$i]["kategori_tindakan_header_id"]) $show = "selected";
        $katTindakanHeader[$i+1] = $view->RenderOption($dataKategori[$i]["kategori_tindakan_header_id"],$dataKategori[$i]["kategori_tindakan_header_nama"],$show);               
      }
    
?> 
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
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Master Perkiraan Per Kategori Tindakan Per Poli</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
     <table width="100%" cellpadding="2" cellspacing="2">
          <tr>
             <td align="right" class="tablecontent">&nbsp;Poli/Klinik&nbsp;&nbsp;</td>
                  <td colspan="3" class="tablecontent-odd">
        			<select class="form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);"> 
                  <option value="" >[ Pilih Poli ]</option>
                  <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                  <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPoli[$i]["poli_nama"];?></option>
        				<?php } ?>
        			</select>
             </td>
          </tr>
          <tr>
             <td align="right" class="tablecontent">&nbsp;Kategori Tindakan&nbsp;&nbsp;</td>
                  <td colspan="3" class="tablecontent-odd">
             <? if ($_POST["id_biaya"]) $biayaId=$_POST["id_biaya"]; else $biayaId=0; // kalau post id biaya tidak ada isinya kasih nilai 0 biar javascript ngga error ?>
             <?php echo $view->RenderComboBox("id_kategori","id_kategori",$katTindakanHeader,null,null,"onchange=\"javascript:return cariBiaya(document.getElementById('id_kategori').value,".$_POST["reg_tipe_layanan"].",".$_POST["reg_shift"].",".$biayaId.");\"");?>    
             <span id="div_biaya"><?php echo GetKategori($_POST["id_kat_header_default"]);?></span>
             </td>
          </tr>
          
          <tr>
               <td align="right" class="tablecontent">Perkiraan Pendapatan</td>
               <td colspan="3" class="tablecontent-odd">                                                    
               <?php echo $view->RenderTextBox("prk_pendapatan","prk_pendapatan","40","100",$_POST["prk_pendapatan"],"inputField",false,false);?>                                        
               <input class="form-control" type="text" readonly name="id_prk" id="id_prk" value="<?php echo $_POST["id_prk"];?>" />                                                   
               <a href="<?php echo $findPrkPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Perkiraan">                           
               <img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a>
              </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Perkiraan Debet</td>
               <td colspan="3" class="tablecontent-odd">                                                    
               <?php echo $view->RenderTextBox("prk_debet","prk_debet","40","100",$_POST["prk_debet"],"inputField",false,false);?>                                        
               <input class="form-control" type="text" readonly name="id_prk_debet" id="id_prk_debet" value="<?php echo $_POST["id_prk_debet"];?>" />                                                   
               <a href="<?php echo $findPrk2Page;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Perkiraan">                           
               <img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a>
              </td>
          </tr>
          </table>          
           <table width="80%" border="0" cellpadding="1" cellspacing="1">     
          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$kembali."';\"");?>                    
               </td>
          </tr>
     </table>
   </form>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<input type="hidden" name="id_poli" id="id_poli" value="<?php echo $_POST["id_poli"];?>" />
<script>document.frmEdit.item_kode.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("biaya_prk_id","biaya_prk_id",$itemId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>


     </div>
     </div>
    </div>
  </div>
 <?php require_once($LAY."footer.php") ?>
<?php require_once($LAY."js.php") ?>