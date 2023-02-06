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
	   $auth = new CAuth();
     $err_code = 0;
     $depId = $auth->GetDepId();
     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
          
     $viewPage = "kat_tindakan_header_view.php";
     $editPage = "kat_tindakan_header_edit.php";
     $findPage = "akun_prk.php?"; 
     $findPage2 = "akun_prk2.php?";  
     $plx = new expAJAX("GetHeader");

    /*   if(!$auth->IsAllowed("man_tarif_kat_tindakan_header",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_tarif_kat_tindakan_header",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */
	
     if($_GET["id_kategori_tindakan_header_instalasi"]) $_POST["id_kategori_tindakan_header_instalasi"]=$_GET["id_kategori_tindakan_header_instalasi"];
     
     function GetHeader()
	    {
        global $dtaccess,$view,$depId;
     	  $sql = "select a.* from klinik.klinik_kategori_tindakan_header_instalasi a
        where  a.id_dep =".QuoteValue(DPE_CHAR,$depId)."     
        order by a.klinik_kategori_tindakan_header_instalasi_nama asc";
        //return $katTahunTarifId;   
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataKategoriHeader = $dtaccess->FetchAll($rs_edit);
		   	unset($optionKatTindakanHeader);
			  $optionKatTindakanHeader[0] = $view->RenderOption("","[Pilih Kategori Tindakan Header Instalasi]",$show);
		  	$i = 1;
			
        for($i=0,$n=count($dataKategoriHeader);$i<$n;$i++){   
         if($_POST["id_kategori_tindakan_header_instalasi"]==$dataKategoriHeader[$i]["klinik_kategori_tindakan_header_instalasi_id"]) $show = "selected";
         $optionKatTindakanHeader[$i+1] = $view->RenderOption($dataKategoriHeader[$i]["klinik_kategori_tindakan_header_instalasi_id"],$dataKategoriHeader[$i]["klinik_kategori_tindakan_header_instalasi_nama"],$show);
         unset($show);
     }
			$str = $view->RenderComboBox("id_kategori_tindakan_header_instalasi","id_kategori_tindakan_header_instalasi",$optionKatTindakanHeader,null,null,null);
                       
	 return $str;
  }
     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kat_header_id"])  $splitId = & $_POST["kat_header_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $splitId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*, b.no_prk as no_prk_pendapatan,b.nama_prk as prk_pendapatan, c.no_prk as no_prk_biaya,c.nama_prk as prk_biaya
                  from klinik.klinik_kategori_tindakan_header a
                  left join gl.gl_perkiraan b on a.id_prk_pendapatan = b.id_prk
                  left join gl.gl_perkiraan c on a.id_prk_biaya = c.id_prk
                  where kategori_tindakan_header_id = ".QuoteValue(DPE_CHAR,$splitId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["kategori_tindakan_header_nama"] = $row_edit["kategori_tindakan_header_nama"];
          $_POST["id_dep"] = $row_edit["id_dep"];
          $_POST["kategori_tindakan_header_urut"] = $row_edit["kategori_tindakan_header_urut"];
          $_POST["id_kategori_tindakan_header_instalasi"] = $row_edit["id_kategori_tindakan_header_instalasi"];
          $_POST["id_prk_pendapatan"] = $row_edit["id_prk_pendapatan"];
          $_POST["id_prk_biaya"] = $row_edit["id_prk_biaya"];          
          $_POST["nama_prk_pendapatan"] = $row_edit["prk_pendapatan"];
          $_POST["nama_prk_biaya"] = $row_edit["prk_biaya"];          
          $_POST["no_prk_pendapatan"] = $row_edit["no_prk_pendapatan"];
          $_POST["no_prk_biaya"] = $row_edit["no_prk_biaya"];
          
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $splitId = & $_POST["kat_header_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_kategori_tindakan_header";
               
               $dbField[0] = "kategori_tindakan_header_id";   // PK
               $dbField[1] = "kategori_tindakan_header_nama"; 
               $dbField[2] = "id_kategori_tindakan_header_instalasi";
               $dbField[3] = "id_dep";
               $dbField[4] = "id_prk_pendapatan";
               $dbField[5] = "id_prk_biaya";
               $dbField[6] = "kategori_tindakan_header_urut";
               
                $sql = "select max(kategori_tindakan_header_urut) as total 
                        from klinik.klinik_kategori_tindakan_header where id_dep =".QuoteValue(DPE_CHAR,$depId);
                $rs = $dtaccess->Execute($sql);
                $Maxs = $dtaccess->Fetch($rs);
                $MaksUrut = ($Maxs["total"]+1);
			
               if (!$_POST["kategori_tindakan_header_urut"]) $_POST["kategori_tindakan_header_urut"]= $MaksUrut;
      
               if(!$splitId) $splitId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$splitId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kategori_tindakan_header_nama"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan_header_instalasi"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_prk_pendapatan"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_prk_biaya"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["kategori_tindakan_header_urut"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
                  
               if($_POST["btnSave"] || $_POST["btnUpdate"])  {
          
                 $sql = "select * from klinik.klinik_kategori_tindakan_header c 
                        left join klinik.klinik_kategori_tindakan_header_instalasi d on d.klinik_kategori_tindakan_header_instalasi_id=c.id_kategori_tindakan_header_instalasi 
                        where 1=1  order by klinik_kategori_tindakan_header_instalasi_urut,kategori_tindakan_header_urut,kategori_tindakan_header_nama";
                 $rs = $dtaccess->Execute($sql);
                 $dataHeader= $dtaccess->FetchAll($rs); 

                 for($i=0,$n=count($dataHeader);$i<$n;$i++) {       
          
                   $sql = "update klinik.klinik_kategori_tindakan_header set kategori_tindakan_header_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                          where kategori_tindakan_header_id=".QuoteValue(DPE_CHAR,$dataHeader[$i]["kategori_tindakan_header_id"]);
                   //echo $sql; die();
                   $dtaccess->Execute($sql);
          
                 } //end loopinh   
               }
               
                  header("location:".$viewPage."?klinik=".$depId."&dep_lowest=".$_POST["dep_lowest"]."&id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]);
                  exit();
          }
     }
     
     if ($_GET["del"]) {
          $kategoriHeaderId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_kategori_tindakan_header where kategori_tindakan_header_id = ".QuoteValue(DPE_CHAR,$kategoriHeaderId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
           //DIURUTKAN KEMBALI SETELAH DIHAPUS
           $sql = "select * from klinik.klinik_kategori_tindakan_header c 
                  left join klinik.klinik_kategori_tindakan_header_instalasi d on d.klinik_kategori_tindakan_header_instalasi_id=c.id_kategori_tindakan_header_instalasi 
                  where 1=1  order by klinik_kategori_tindakan_header_instalasi_urut,kategori_tindakan_header_urut,kategori_tindakan_header_nama";
           $rs = $dtaccess->Execute($sql);
           $dataHeader= $dtaccess->FetchAll($rs); 

           for($i=0,$n=count($dataHeader);$i<$n;$i++) {       
    
             $sql = "update klinik.klinik_kategori_tindakan_header set kategori_tindakan_header_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                    where kategori_tindakan_header_id=".QuoteValue(DPE_CHAR,$dataHeader[$i]["kategori_tindakan_header_id"]);
             //echo $sql; die();
             $dtaccess->Execute($sql);
                                                                                              
           } //end loopinh   
    
          header("location:kat_tindakan_header_view.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]);
          exit(); 
          }
 
     /*if ($_POST["btnDelete"]) {
          $splitId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($splitId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_split
                         where split_id = ".QuoteValue(DPE_CHAR,$splitId[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          }
          
          header("location:".$viewPage);
          exit();    
     } */
     
     
     $sql = "select * from klinik.klinik_kategori_tindakan_header_instalasi order by klinik_kategori_tindakan_header_instalasi_urut";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
	 
	  //combo box Kategori Tindakan Header
     $sql = "select * from klinik.klinik_kategori_tindakan_header_instalasi where id_dep =".QuoteValue(DPE_CHAR,$depId)." 
     order by klinik_kategori_tindakan_header_instalasi_nama";
     //echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);
     
?>

<script language="JavaScript">
<? $plx->Run(); ?>
  function cariHeader(id)
{  
	document.getElementById('div_tahun_tarif').innerHTML = GetHeader('type=r');
}
</script>
<!DOCTYPE html>
<html lang="en">

  <?php require_once($LAY."header.php"); ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        
		<?php require_once($LAY."sidebar.php"); ?>

        <!-- top navigation -->
		<?php require_once($LAY."topnav.php"); ?>
<!--<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp; Setup Split Tindakan</td>
    </tr>
</table>-->
		<div class="right_col" role="main">

            <div class="page-title">
              <div class="title_left">
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">
			  <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Tindakan Header</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  
					<form id="frmEdit" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Header Instalasi</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <span id="div_tahun_tarif"><?php echo GetHeader();?></span>
                        </div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Kategori Header</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <?php echo $view->RenderTextBox("kategori_tindakan_header_nama","kategori_tindakan_header_nama","50","100",$_POST["kategori_tindakan_header_nama"],"inputField", null,false);?>
                        </div>
                      </div>
             
            <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kategori Header Urut</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <?php echo $view->RenderTextBox("kategori_tindakan_header_urut","kategori_tindakan_header_urut","50","100",$_POST["kategori_tindakan_header_urut"],"inputField", null,false);?>
                        </div>
                      </div>          
            <!--
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Perkiraan Pendapatan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                        	<span class="col-md-5 col-sm-5 col-xs-12">
                        	<?php echo $view->RenderTextBox("nama_prk_pendapatan","nama_prk_pendapatan","40","100",$_POST["nama_prk_pendapatan"],"inputField",false,false);?>
                        	</span>
                        	<span class="col-md-5 col-sm-5 col-xs-12">                                        
               				<?php echo $view->RenderTextBox("no_prk_pendapatan","no_prk_pendapatan","20","100",$_POST["no_prk_pendapatan"],"inputField",false,false);?>
               				</span>
               				<span class="col-md-2 col-sm-2 col-xs-12">
               				<input type="hidden" readonly name="id_prk_pendapatan" id="id_prk_pendapatan" value="<?php echo $_POST["id_prk_pendapatan"];?>" /> 
               				</span>                                      
               				<a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Perkiraan">                 
               				<img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a>

                      </div>
					  </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Perkiraan Biaya</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                        	<span class="col-md-5 col-sm-5 col-xs-12">
                        	<?php echo $view->RenderTextBox("nama_prk_biaya","nama_prk_biaya","40","100",$_POST["nama_prk_biaya"],"inputField",false,false);?>
                        	</span>                                        
                        	<span class="col-md-5 col-sm-5 col-xs-12">
               				<?php echo $view->RenderTextBox("no_prk_biaya","no_prk_biaya","20","100",$_POST["no_prk_biaya"],"inputField",false,false);?>
               				</span>
               				<input type="hidden" readonly name="id_prk_biaya" id="id_prk_biaya" value="<?php echo $_POST["id_prk_biaya"];?>" />                                                   
               				<a href="<?php echo $findPage2;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Perkiraan">                           
               				<img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a>
						  <input type="hidden" readonly name="id_prk_kredit" id="id_prk_kredit" value="<?php echo $_POST["id_prk_kredit"];?>" />      
                          <input type="hidden" readonly name="id_prk_debet" id="id_prk_debet" value="<?php echo $_POST["id_prk_debet"];?>" />                                                   
                        </div>
                      </div>    -->

                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    	<?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$viewPage."?Kembali=".$depId."&id_kategori_tindakan_header_instalasi=".$_GET["id_kategori_tindakan_header_instalasi"]."';\"");?>  
                        </div>
                      </div>

						<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
						<?php echo $view->RenderHidden("kat_header_id","kat_header_id",$splitId);?>
						<? } ?>
						<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
                    </form>
                  </div>
                </div>
              </div>
              </div>
        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->              						
              </div>
              </div>
      </div>
 <?php require_once($LAY."js.php") ?>
  </body>
  </html>              