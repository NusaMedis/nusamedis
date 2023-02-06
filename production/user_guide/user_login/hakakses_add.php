<?php
     //LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
  
     //INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();              
     $err_code = 0;
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();
     //PRIVILLAGE  
   
/*   if(!$auth->IsAllowed("man_user_user_login",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_user_login",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

     
      $thisPage = "hakakses_edit.php";
      $findPage = "cari_pegawai.php?";
      $lokasi = $ROOT."/gambar/foto_pegawai";
      

  
     //GET DATA
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
     else $_x_mode = "New";
   
     if($_POST["usr_id"])  $usrId = & $_POST["usr_id"];   
     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     
     //EDIT DATA
     /*
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $usrId = $enc->Decode($_GET["id"]);
          }
          
          $sql = "select a.*,b.* from global.global_auth_user a
                  left join hris.hris_pegawai b on b.id_usr = a.usr_id where usr_id = ".QuoteValue(DPE_CHAR,$usrId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["usr_loginname"] = $row_edit["usr_loginname"];
          $_POST["usr_name"] = $row_edit["usr_name"];
          $_POST["id_rol"] = $row_edit["id_rol"];
          $_POST["usr_status"] = $row_edit["usr_status"];
          $_POST["usr_when_create"] = $row_edit["usr_when_create"];
          $_POST["usr_app_def"] = $row_edit["usr_app_def"];
          $_POST["usr_poli"] = $row_edit["usr_poli"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $_POST["usr_foto"] = $row_edit["usr_foto"]; 
          $_POST["usr_alamat"] = $row_edit["pgw_alamat_surabaya"]; 
          $_POST["usr_tempat_lahir"] = $row_edit["pgw_tempat_lahir"]; 
          $_POST["usr_tgl_lahir"] = format_date($row_edit["pgw_tanggal_lahir"]); 
          $_POST["usr_no_hp"] = $row_edit["pgw_telp_hp"];
          $_POST["usr_pendidikan"] = $row_edit["pgw_pendidikan"];
          $_POST["id_struk"] = $row_edit["id_struk"];
          $_POST["pgw_nip"] = $row_edit["pgw_nip"];
          $_POST["pgw_id"] = $row_edit["pgw_id"];
         $_POST["usr_alamat"];
          if($_POST["usr_foto"]) $fotoName = $lokasi."/".$row_edit["usr_foto"];
          else $fotoName = $lokasi."/default.jpg";
          $back = "hakakses_view_staff.php?kembali=".$_POST["klinik"];
          
          
          $sql = "select * from global.global_auth_user_app
                    where id_usr = ".QuoteValue(DPE_CHAR,$usrId)."
                    order by id_app";
          $rs = $dtaccess->Execute($sql);
          $dataUsrApp = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($dataUsrApp);$i<$n;$i++){
               $_POST["id_app"][$dataUsrApp[$i]["id_app"]] = $dataUsrApp[$i]["id_app"];               
          }
          
           // -- CARI DATA APLIKASI -- //
          $sql = "select * from global.global_app order by app_nama asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataApp = $dtaccess->FetchAll($rs); 
          for($i=0,$n=count($dataApp);$i<$n;$i++)
          {
               $sql = "select * from global.global_auth_user_app
                    where id_usr = ".QuoteValue(DPE_CHAR,$usrId)." and id_app = ".$dataApp[$i]["app_id"]."
                    order by id_app";
                    $rs = $dtaccess->Execute($sql);
               $dataUsrAppSatuan = $dtaccess->Fetch($rs);
          
               if ($dataUsrAppSatuan)
                  $_POST["id_app"][$i] = $dataUsrAppSatuan["id_app"];
               else 
                  $_POST["id_app"][$i] = "N";  
               unset($dataUsrAppSatuan);            
          } 

          //----end---//
          
          $sql = "select * from global.global_auth_user_poli
                    where id_usr = ".QuoteValue(DPE_CHAR,$usrId)."
                    order by id_poli";
          $rs = $dtaccess->Execute($sql);
          //echo $sql;
          $dataUsrPoli = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($dataUsrPoli);$i<$n;$i++){
               $_POST["id_poli"][$dataUsrPoli[$i]["id_poli"]] = $dataUsrPoli[$i]["id_poli"];               
          }
          
           // -- CARI DATA APLIKASI -- //
          $sql = "select * from global.global_auth_poli order by poli_nama asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataPoli = $dtaccess->FetchAll($rs); 
          for($i=0,$n=count($dataPoli);$i<$n;$i++)
          {
               $sql = "select * from global.global_auth_user_poli
                    where id_usr = ".QuoteValue(DPE_CHAR,$usrId)." and id_poli = '".$dataPoli[$i]["poli_id"]."'
                    order by id_poli";
                    $rs = $dtaccess->Execute($sql);
               $dataUsrPoliSatuan = $dtaccess->Fetch($rs);
          
               if ($dataUsrPoliSatuan)
                  $_POST["id_poli"][$i] = $dataUsrPoliSatuan["id_poli"];
               else 
                  $_POST["id_poli"][$i] = "N";  
               unset($dataUsrPoliSatuan);            
          } 
          //----end---//
          
      }         */


     
     
     if (!$_POST["usr_foto"])
     {
        $_POST["usr_foto"] = "default.jpg";
        $fotoName = $lokasi."/".$_POST["usr_foto"];
     }
     
     // AKSES DATA
     if($_x_mode=="New") $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;
  
     //PRIVILLAGE 
     /*if(!$auth->IsAllowed("setup_hak_akses",$privMode)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
     } else if($auth->IsAllowed("setup_hak_akses",$privMode)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }  */
  
     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
      
     
     // menyimpan data 
     if ($_POST["btnSave"]) {
                   
          if($_POST["id_rol"]=="2" && $_POST["usr_no_urut"] ==""){
          $sql = "select max(usr_no_urut) as usr_no_max from global.global_auth_user";
          $rs = $dtaccess->Execute($sql);
          $dataUrut = $dtaccess->Fetch($rs);
          $_POST["usr_no_urut"] = $dataUrut["usr_no_max"]+1;
          }else if($_POST["id_rol"]=="2" && $_POST["usr_no_urut"] !=null){
          $_POST["usr_no_urut"] = $_POST["usr_no_urut"];
          }else{
          $_POST["usr_no_urut"] = null;
          } 
          // INSERT ATO UPDATE DATA KE TABEL
          
               $dbTable = "global.global_auth_user";
               
               $dbField[0] = "usr_id";   // PK
               $dbField[1] = "usr_loginname";
               $dbField[2] = "usr_name";
               $dbField[3] = "id_rol";
               $dbField[4] = "usr_status";
               $dbField[5] = "usr_when_create";
               $dbField[6] = "usr_app_def";
               $dbField[7] = "usr_poli";
               $dbField[8] = "id_dep";       
               $dbField[9] = "usr_foto";
               $dbField[10] = "id_pgw";  
               if($_POST["is_password"]) {
               $dbField[11] = "usr_password";
               }
               if($_POST["id_rol"]=="2" && $_POST["is_password"]){
               $dbField[12] = "usr_no_urut";
               }else if($_POST["id_rol"]=="2"){
               $dbField[11] = "usr_no_urut";
               }      
               if(!$_POST["usr_when_create"]) $_POST["usr_when_create"] = date("Y-m-d H:i:s");
               
               //if(!$usrId) $usrId = $dtaccess->GetNewID("global.global_auth_user","usr_id",DB_SCHEMA);
               if(!$usrId) $usrId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$usrId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["usr_loginname"]);
               if ($_POST["pgw_nama"] == '' || $_POST["pgw_nama"] == null){
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
               } else {
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pgw_nama"]); 
               }              
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_rol"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["usr_status"]);
               $dbValue[5] = QuoteValue(DPE_DATE,$_POST["usr_when_create"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,10);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["usr_poli"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["usr_foto"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["pgw_id"]); 
               if($_POST["is_password"]) {$dbValue[11] = QuoteValue(DPE_CHAR,md5($_POST["usr_password"]));  }
               if($_POST["id_rol"]=="2" && $_POST["is_password"]){
               $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["usr_no_urut"]);
               }else if($_POST["id_rol"]=="2"){
               $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["usr_no_urut"]);
               }
              //print_r($dbValue);
              //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
               $dtmodel->Insert() or die("insert  error");	
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

               /* NGGA USAH UPDATE DATA PEGAWAI KALAU MAU UPDATE DI DATA PEGAWAI SAJA
               $sql = "update hris.hris_pegawai set pgw_foto = '".$_POST["usr_foto"]."', pgw_alamat_surabaya=".QuoteValue(DPE_CHAR,$_POST["usr_alamat"]).",
                        pgw_tempat_lahir=".QuoteValue(DPE_CHAR,$_POST["usr_tempat_lahir"]).", pgw_tanggal_lahir=".QuoteValue(DPE_DATE,date_db($_POST["usr_tgl_lahir"])).",
                        pgw_telp_hp=".QuoteValue(DPE_CHAR,$_POST["usr_no_hp"])." where pgw_id = '".$_POST["pgw_id"]."'";
               $dtaccess->Execute($sql); //}

               $sql = "update hris.hris_pegawai set id_struk= '".$_POST["id_struk"]."' where pgw_id = '".$_POST["pgw_id"]."'";
               $dtaccess->Execute($sql); */
               //echo $sql; die();
               
                
               // --- buat nyimpen data poli per user ---                  
               $sql = "delete from global.global_auth_user_poli 
                         where id_usr = ".QuoteValue(DPE_CHAR,$usrId);
               $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);                    
               
               $dbTable = "global.global_auth_user_poli";
               
               $dbField[0] = "usr_poli_id";   // PK
               $dbField[1] = "id_usr";
               $dbField[2] = "id_poli";
               $dbField[3] = "id_dep";
            
               foreach($_POST["id_poli"] as $key=>$value){
                    $usrPoliId = $dtaccess->GetTransID();
                    $dbValue[0] = QuoteValue(DPE_CHAR,$usrPoliId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$usrId);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$value);
                    $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
                    
                    //print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        
                    $dtmodel->Insert() or die("insert  error");	                         
                    unset($usrPoliId);
                    unset($dtmodel);                         
                    unset($dbValue);
                    unset($dbKey);
               }
               
               // --- buat nyimpen applicationnya ---                  
               $sql = "delete from global.global_auth_user_app 
                         where id_usr = ".QuoteValue(DPE_CHAR,$usrId);
               $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);                    
               
               $dbTable = "global.global_auth_user_app";
               
               $dbField[0] = "usr_app_id";   // PK
               $dbField[1] = "id_usr";
               $dbField[2] = "id_app";
               $dbField[3] = "id_dep";
            
               foreach($_POST["id_app"] as $key=>$value){
                    $usrAppId = $dtaccess->GetNewID("global.global_auth_user_app","usr_app_id",DB_SCHEMA_GLOBAL);
                    $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrAppId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$usrId);
                    $dbValue[2] = QuoteValue(DPE_NUMERIC,$value);
                    $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
                    
                    //print_r($dbValue);
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        
                    $dtmodel->Insert() or die("insert  error");	                         
                    
                    unset($dtmodel);                         
                    unset($dbValue);
                    unset($dbKey);
                    
               }
               
               // --- buat nyimpen nama dokter di hris pegawai---                  
              
               
               unset($dbField);




          
			   $back = "hakakses_view.php?id=".$usrId;
               header("location:".$back);
               exit();
			   }

			   
               //print_r($dbValue);
               //die();
               
  		if ($_GET["tambah"]) 
      {
        $_POST["klinik"]=$_GET["tambah"];
          $back = "hakakses_view_staff.php?kembali=".$_POST["klinik"];
     
       }
     //BUAT HAPUS USER DATA
     if ($_GET["del"]) {
          $usrId = $enc->Decode($_GET["id"]);
          //print_r($_POST["btnDelete"]);
          //die();

               $sql = "delete from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$usrId);
               $dtaccess->Execute($sql);
               
               //$sql = "delete from hris.hris_pegawai where pgw_id = ".QuoteValue(DPE_CHAR,$usrId);
               //$dtaccess->Execute($sql);
    
                
     }
     // -- CARI DATA JABATAN ATO ROLE NYA -- //
     $sql = "select * from global.global_auth_role order by rol_name asc";
     $rs = $dtaccess->Execute($sql);
     $dataJabatan = $dtaccess->FetchAll($rs);     

     // -- CARI DATA APLIKASI -- //
     $sql = "select * from global.global_app order by app_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataApp = $dtaccess->FetchAll($rs); 

     
     // --- cari Klinik ---
     $sql = "select dep_id,dep_nama from global.global_departemen order by dep_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKlinik = $dtaccess->FetchAll($rs);   
    
      $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_POST["klinik"]."'";
      $rs = $dtaccess->Execute($sql);
      $namaKlinik = $dtaccess->Fetch($rs);
      
      //Nama Klinik
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"]; 
      
     // --- cari Poli per departemen ---
     $sql = "select poli_id,poli_nama from global.global_auth_poli order by poli_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli= $dtaccess->FetchAll($rs);  

       //Data Satuan Kerja

      $sql = "select * from hris.hris_struktural where struk_is_lowest = 'y' order by struk_tree";
      $rs = $dtaccess->Execute($sql);
      $dataSatker = $dtaccess->FetchAll($rs);
	
      $kembali = "hakakses_view.php?satker=".$_GET["satker"]."&kembali=".$_POST["klinik"]."";
      $_POST["usr_status"] = 'y'; //biar otomatis ke centang

?>





<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/ew.js"></script>


<script language="javascript" type="text/javascript">

function CheckDataSave(frm) 
{  
    if(!frmEdit.pgw_nama.value){
		alert('Pegawai Harus Dipilih');
		frmEdit.pgw_nama.focus();
        return false;
	}
    
    if(!frmEdit.usr_loginname.value){
		alert('Nama Login Harus Diisi');
		frmEdit.usr_loginname.focus();
        return false;
	}
    
    if(!frmEdit.usr_password.value){
		alert('Password Harus Diisi');
		frmEdit.usr_password.focus();
        return false;
	}
      
     document.frmEdit.submit();  
}
</script>  

</head>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        
		<?php require_once($LAY."sidebar.php"); ?>

        <!-- top navigation -->
		<?php require_once($LAY."topnav.php"); ?>
		<!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

			<form id="frmEdit" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-7 col-sm-7 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>User Setup</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Pengguna</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                          <input type="text" name="pgw_nama" id="pgw_nama" size="20" maxlength="20" readonly="readonly" data-validate-length-range="2" required="required" class="form-control col-md-7 col-xs-12">
                        <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Pasien">
                        		<img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Pegawai" alt="Cari Pegawai" class="tombol" align="middle"/></a>
                        <?php echo $view->RenderHidden("pgw_id","pgw_id",$_POST["pgw_id"]);?>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">NIP<span class="required"></span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderTextBox("pgw_nip","pgw_nip","50","50",$_POST["pgw_nip"],"inputField", "readonly" ,false);?>&nbsp;&nbsp;</td>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderTextBox("usr_alamat","usr_alamat","40","40",$_POST["usr_alamat"],"inputField", "readonly",false);?>&nbsp;&nbsp;</td>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No HP</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderTextBox("usr_no_hp","usr_no_hp","20","20",$_POST["usr_no_hp"],"inputField", "readonly",false);?>&nbsp;&nbsp;</td>
                        </div>
                      </div>
                     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Satuan kerja</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderTextBox("struk_nama","struk_nama","20","20",$_POST["struk_nama"],"inputField", "readonly",false);?>&nbsp;&nbsp;</td>
                        </div>
                      </div> 
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Jabatan</label>
                        <div id="div_rol" class="col-md-8 col-sm-8 col-xs-12">
                          <select class="select2_single form-control" id="id_rol"  name="id_rol" required>
                                    <option class="inputField" value="">Pilih Jabatan</option>
                               <?php for($i=0,$n=count($dataJabatan);$i<$n;$i++){ ?>
                                    <option class="inputField" value="<?php echo $dataJabatan[$i]["rol_id"];?>"><?php echo $dataJabatan[$i]["rol_name"];?></option>
                               <?php } ?>
                          </select>
                              <input type="hidden" name="usr_no_urut" value="<?php echo $_POST['usr_no_urut'];?>">
                        </div>
                      </div>
 
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" required="required" for="first-name">Nama Login<span class="required"></span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderTextBox("usr_loginname","usr_loginname","30","50","","inputField", null,false);?>&nbsp;&nbsp;</td>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" required="required" for="last-name">Password<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderPassword("usr_password","usr_password","30","50","",($_x_mode=="Edit")?"passDisable":"inputField",($_x_mode=="Edit")?"disabled":"",false);			
                              if($_x_mode == "Edit"){ 
                                   echo $view->RenderCheckBox("is_password","is_password","y","inputField",false,"onClick='GantiPassword(this.form,this)'");
                                   echo $view->RenderLabel("lbl_password","is_password","Ganti Password"); 
                              } elseif($_x_mode == "New"){
                                   echo $view->RenderHidden("is_password","is_password","y");
                              } ?>
                        </div>
                      </div> 
                      
                     
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Status<span class="required"></span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <?php echo $view->RenderCheckBox("usr_status","usr_status","y","inputField",($_POST["usr_status"]=="y")?"checked":"");						
                                   echo $view->RenderLabel("usr_status","usr_status","Aktif")?>
                        </div>
                      </div>
                  </div>
                </div>
				<div class="col-md-2 col-sm-2 col-xs-12">
					<div class="x_panel">
                  <div class="x_title">
                    <h2>Aplikasi</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-1 col-sm-3 col-xs-12" for="first-name"><span class="required"></span>
                        </label>
                        <td>
                              <table class="tabel">
							  <input type="checkbox" onClick="EW_selectKey(this,'id_app[<?php echo $dataApp[$i]["app_id"];?>]')">
                              <?php for($i=0,$n=count($dataApp);$i<$n;$i++){?>                             
                              <tr>
                                   <td width="50%">
                                        <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="id_app[<?php echo $dataApp[$i]["app_id"];?>]" id="id_app[]" value="<?php echo $dataApp[$i]["app_id"];?>" <?php if ($_POST["id_app"][$i]==$dataApp[$i]["app_id"]) echo "checked"; ?>/>
                                        <label for="id_app[<?php echo $dataApp[$i]["app_id"];?>]"><?php echo $dataApp[$i]["app_nama"];?></label>                                       
                                   </td>
                              </tr>
                              <?php }?>
                              
                              </table>
						</div>
                      </div>
                    <div class="clearfix"></div> 
                      <div class="ln_solid"></div>
                  </div>
				</div>
					<div class="col-md-3 col-sm-3 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Poli</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"><span class="required"></span>
                        </label>
                         <td  valign="top">
                              <table border= "0" width=80%">
							  <input type="checkbox" onClick="EW_selectKey(this,'id_poli[<?php echo $dataPoli[$j]["poli_id"];?>]')">
                              <?php for($j=0,$k=count($dataPoli);$j<$k;$j++){?>
                              <tr>
                                   <td width="50%">
                                        <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="id_poli[<?php echo $dataPoli[$j]["poli_id"];?>]" id="id_poli[]" value="<?php echo $dataPoli[$j]["poli_id"];?>" <?php if ($_POST["id_poli"][$j]==$dataPoli[$j]["poli_id"]) echo "checked"; ?> />
                                        <label for="id_poli[<?php echo $dataPoli[$j]["poli_id"];?>]"><?php echo $dataPoli[$j]["poli_nama"];?></label>                                       
                                   </td>
                                    
                              </tr>
                              <?php }?>
                              </table>
						</div>
                      </div>
                    <div class="clearfix"></div> 
                      <div class="ln_solid"></div>
                                
                    
                  </div>
                </div>
                    <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
                          <button id="btnSave" name="btnSave"; type="submit" value="Tambah" onClick="javascript:return CheckDataSave(document.frmEdit);" class="btn btn-primary">Simpan</button>
                          <button class="btn btn-danger" type="button" onClick="window.history.back()">Kembali</button>				                      
                        </div>


				</form>

              </div>
            </div>
          </div>
        </div>

        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>