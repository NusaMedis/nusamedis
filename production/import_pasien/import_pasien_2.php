<?php
require_once("../penghubung.inc.php");
require_once($LIB."/login.php");
require_once($LIB."/bit.php");
require_once($LIB."/encrypt.php");
require_once($LIB."/datamodel.php");
require_once($LIB."/dateLib.php");
require_once($LIB."/expAJAX.php");
require_once($LIB."/currency.php");
require_once($LIB."/tree.php");
require_once($LIB."/tampilan.php"); 
  
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	   $depId = $auth->GetDepId();
     $userData = $auth->GetUserData();
	   $usrId = $auth->GetUserId();
     $err_code = 0;
     $depLowest = $auth->GetDepLowest();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $_POST["klinik"] =  $depId;

/*     
     if(!$auth->IsAllowed("man_import_pasien_excel",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_import_pasien_excel",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } 
*/     
       $tree = new CTree("global.global_customer_user","cust_usr_id", TREE_LENGTH);
     
     $delimiter = ",";
     $startLine = 0;
	
     
     if($_POST["csvFile"]) $csvFile = $_POST["csvFile"];
     else $csvFile = $ROOT."temp/";
	   // echo $csvFile;
	   // die();
	  $plx = new expAJAX();  
	  
	                 // --- buat nge check --- 
              $sql = "select cust_usr_id, cust_usr_nama ,cust_usr_alamat , cust_usr_kode
              from global.global_customer_user order by cust_usr_nama ";                 
              $rs = $dtaccess->Execute($sql);

          while($row = $dtaccess->Fetch($rs)) {
          $barang[$row["cust_usr_nama"]] = $row["cust_usr_id"];
     }

     if(isset($_POST["btnNext"])){          
          
          if($_FILES["csv_file"]["tmp_name"]){
               $err = false;
          } else {
               $err=true;
          }
         
          if(!$err){
               if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
                    $csvFile .= $_FILES["csv_file"]["name"];
                    copy($_FILES["csv_file"]["tmp_name"], $csvFile);
               }
          }
          
          if ((!$myFile = @fopen(stripslashes($csvFile), "r")) || $err==true) {
               $err = true;
          } else {
          
               // --- buat nge check data e uda ada ga ---
               $sql = "select cust_usr_id, cust_usr_nama ,cust_usr_alamat , cust_usr_kode
              from global.global_customer_user";                         
               $rs = $dtaccess->Execute($sql);
               
                  //reset($barang);        
               while ($data = fgetcsv($myFile, 500000, $delimiter)) {
                    //echo $data[0]."&nbsp;".$data[1]."&nbsp;".$data[2]."&nbsp;".$data[3]."&nbsp;".$data[4]."<br />";
                    
                         if($data[0] || $data[1] || $data[2] || $data[3] || $data[4] || $data[5] || $data[6] || $data[7] || $data[8] || $data[9]|| $data[10] || $data[11] || $data[12] || $data[13] || $data[14]) {
                
         // KONFIGURASI
      $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_no_depan"] =  $row_edit["dep_konf_no_depan"];       
      $_POST["dep_jml_nol_depan"] = $row_edit["dep_jml_nol_depan"];
      $_POST["dep_panjang_kode_pasien"] = $row_edit["dep_panjang_kode_pasien"];
                                        // jika konfigurasinya pake huruf --
        if($_POST["dep_konf_no_depan"]=="y") {
        $namaPasien = $data[1]; 
        $hurufKode = $namaPasien[0];  
        
        $sql = "select cust_usr_kode as kode,cust_usr_huruf_urut from global.global_customer_user where cust_usr_huruf=".QuoteValue(DPE_CHAR,strtoupper($hurufKode))." and id_dep=".QuoteValue(DPE_CHAR,$_POST["klinik"])." order by cust_usr_kode desc";
        $lastKode = $dtaccess->Fetch($sql);
        
        $kodeLast = substr($lastKode["kode"], 1, $_POST["dep_panjang_kode_pasien"]);
        $kode = str_pad($kodeLast+1,$_POST["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);
        $bln = date("m");
        $thn = substr(date("Y"), 2, 4);
        $_POST["cust_usr_kode"] = strtoupper($hurufKode)."".$kode;
        $_POST["cust_usr_huruf_urut"]= $lastKode["cust_usr_huruf_urut"]+1;

        // jika konfigurasinya tanpa huruf --
        } else if($_POST["dep_konf_no_depan"]=="n") {
    
        $sqle = "select cust_usr_kode as kode from global.global_customer_user where cust_usr_huruf='' and id_dep=".QuoteValue(DPE_CHAR,$_POST["klinik"])." order by cust_usr_kode desc";
        $lastKodeNonHuruf = $dtaccess->Fetch($sqle);
        $_POST["cust_usr_kode"] = str_pad($lastKodeNonHuruf["kode"]+1,$_POST["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);

        }
        
        $sql = "select usr_id from global.global_auth_user 
                where upper(usr_name) like '%".strtoupper($data[11])."%' 
                and id_dep=".QuoteValue(DPE_CHAR,$_POST["klinik"]);
        $dataDokter = $dtaccess->Fetch($sql);
        
        $sql = "select jenis_id from global.global_jenis_pasien 
                where upper(jenis_nama) like '%".strtoupper($data[13])."%'";
        $dataJenis = $dtaccess->Fetch($sql);
        
        $arr = str_split($_POST["cust_usr_kode"],"2");
    	$usr_kode_tampilan = implode(".",$arr);
		$_POST["cust_usr_kode_tampilan"] = $usr_kode_tampilan;
        
        //echo $sql;
       // die();
                        
                                        $dbTable = "global.global_customer_user";
                              
                                        $dbField[0] = "cust_usr_nama";   // PK                      
                                        $dbField[1] = "cust_usr_kode";  
                                                                      
                                             $pasienId = $dtaccess->GetTransID("global.global_customer_user","cust_usr_id",DB_SCHEMA);
                                             $dbValue[0] = QuoteValue(DPE_CHAR,$data[0]);
                                             $dbValue[1] = QuoteValue(DPE_CHAR,$data[1]);
                                             
                                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                                        
                                        $dtmodel->update() or die("insert error");
                                                                
                                        unset($dtmodel); unset($dbValue); unset($dbKey);
                                        
                                          
                         
                         } else {
                              $error[] = $startLine;
                         }
                    
                    
                    $startLine ++;
               }
          }
          
          if($error) $error = implode("<br>Data Excel Baris ke ",$error);
     } 
     
    
?>
<?php //echo $view->RenderBody("module.css",true,false,"IMPORT PASIEN EXCEL"); ?>
<br /><br /><br />
<?php// echo $view->InitUpload(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">

<?php $plx->Run();?>

$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '60%',
'height' : '110%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 
</script>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>

<div id="bodyku">
<br />
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">

<fieldset>
     <legend><strong>Import Pasien Excel</strong></legend>
     <table width="80%" border="0" cellpadding="1" cellspacing="1">
         <!--
		 <tr class="tablecontent" align="center">
          <td width="40%" align="right" class="tablecontent">&nbsp;&nbsp;Pemisah / Delimiter&nbsp;&nbsp;</td>
          <td width="60%" align="left" class="tablecontent">
			 <select name="delimiter" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				<option class="inputField" value="">- Pilih Pemisah / Delimiter -</option>
				<option class="inputField" value=";"<?php if ($_POST["delimiter"]==";") echo"selected"?>> Titik Koma (;)</option>
				<option class="inputField" value=","<?php if ($_POST["delimiter"]==",") echo"selected"?>> Koma (,)</option>
				</select>
				<?php if (!$_POST["delimiter"]) {?>
			         <?php echo "<font color='red'>Harap Pilih Pemisah terlebih Dahulu </font>"; ?>
               <?php } ?>
		  </td>
		 </tr> -->
          <tr>
               <td width="25%" align="right" class="tablecontent"><strong>CSV File<?php if($err){?> <font color="red">(*)</font><?php } ?>&nbsp;</strong></td>
               <td width="75%" class="tblCol">
                    <input type="file" name="csv_file" size=25 class="submit">
                    <span id="div_uh"></span>
                    <input type="submit" name="btnNext" value="Proses" class="submit" OnClick="document.frmEdit.btnNext.value = 'Please Wait'">
               </td>
          </tr>
     </table>
</fieldset>
<br />
<font size="3" color="red">
&nbsp;&nbsp;Data yang harus diisi :
<br /><br />
</font>
<font size="2" color="red">
&nbsp;&nbsp;Kolom 1 : Nomor <br />
&nbsp;&nbsp;Kolom 2 : Nama  <br />
&nbsp;&nbsp;Kolom 3 : Alamat <br />
&nbsp;&nbsp;Kolom 4 : Umur <br />
&nbsp;&nbsp;Kolom 5 : Tempat Lahir <br /> 
&nbsp;&nbsp;Kolom 6 : Tanggal Lahir (dd-mm-yyyy)<br />
&nbsp;&nbsp;Kolom 7 : Jenis Kelamin <br /> 
&nbsp;&nbsp;Kolom 8 : Nomor Handphone <br />
&nbsp;&nbsp;Kolom 9 : Nomor Telp <br />
&nbsp;&nbsp;Kolom 10 : Pekerjaan <br />
&nbsp;&nbsp;Kolom 11 : Agama <br />
&nbsp;&nbsp;Kolom 12 : Nama Dokter <br />
&nbsp;&nbsp;Kolom 13 : Alamat Email <br />
&nbsp;&nbsp;Kolom 14 : Jenis Pasien <br />
&nbsp;&nbsp;Kolom 15 : Penanggung Jawab <br />
<br />
&nbsp;&nbsp;Delimiter menggunakan (,) koma, Untuk Contoh data silahkan klik <a href="Book1.csv">disini</a>


</font>
</form>

<?php if($_POST["btnNext"] && !$err) {?>
    <font style="font-size:14px">Proses Import Sudah Selesai</font>
<?php }?>

<?php if($err){?><label><font color="red" style="font-size:12px; font-weight:bold;">&nbsp;Pilih File yang akan di Import Terlebih Dahulu</font></label><?php } ?>

<?php if($error) {?>
     <br /><br />
     <font color="red">
          Ada Beberapa Data yang tidak Valid<br />
          <?php echo "Data Excel Baris ke ".$error;?>
     </font>
<?php }?>
<!--------Buat Helpicon----------->
</div>
