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
     
     $delimiter = $_POST["delimiter"];
     $startLine = 0;
	
     
     if($_POST["csvFile"]) $csvFile = $_POST["csvFile"];
     else $csvFile = $ROOT."../temp/";
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
                    
                         if($data[0] || $data[1] || $data[2] || $data[3] || $data[4] || $data[5] || $data[6] || $data[7] || $data[8] || $data[9]|| $data[10] || $data[11] || $data[12] || $data[13] || $data[14]|| $data[15]|| $data[16]|| $data[17]|| $data[18]|| $data[19]|| $data[20]
                        	|| $data[21] || $data[22]|| $data[23]|| $data[24] || $data[25] || $data[26] || $data[27] || $data[28] || $data[29] || $data[30] || $data[31] || $data[32] || $data[33]) {
                
         // KONFIGURASI
      $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_no_depan"] =  $row_edit["dep_konf_no_depan"];       
      $_POST["dep_jml_nol_depan"] = $row_edit["dep_jml_nol_depan"];
      $_POST["dep_panjang_kode_pasien"] = $row_edit["dep_panjang_kode_pasien"];
      
/*
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
  */      
    //nomor RM nya disesuaikan dengan kebutuhan jumlahanya

		$_POST["cust_usr_kode"] = str_pad($data[0],$_POST["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);
		
	//cari data pasien sudah ada atau tidak
	
		$sql = "select * from global.global_customer_user where cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$rs = $dtaccess->Execute($sql);
		$dataPasien = $dtaccess->Fetch($rs);
		
		$arr = str_split($_POST["cust_usr_kode"],"2");
    	$usr_kode_tampilan = implode(".",$arr);
		$_POST["cust_usr_kode_tampilan"] = $usr_kode_tampilan;
        //echo $sql;
       // die();
                        
                                        $dbTable = "global.global_customer_user";
                              
                                        $dbField[0] = "cust_usr_id";   // PK
                                        $dbField[1] = "cust_usr_nama";                       
                                        $dbField[2] = "cust_usr_alamat";
                                        $dbField[3] = "cust_usr_umur";
                                        $dbField[4] = "cust_usr_tempat_lahir";
                                        $dbField[5] = "cust_usr_tanggal_lahir";
                                        $dbField[6] = "cust_usr_jenis_kelamin";
                                        $dbField[7] = "cust_usr_no_hp";
                                        $dbField[8] = "id_dep";
                                        $dbField[9] = "cust_usr_who_update";                
                                        $dbField[10] = "cust_usr_when_update";                 
                                        $dbField[11] = "cust_usr_jenis";
                                        $dbField[12] = "cust_usr_huruf";
                                        $dbField[13] = "cust_usr_no_telp";
                                        $dbField[14] = "cust_usr_pekerjaan";
                                        $dbField[15] = "cust_usr_agama";
                                        $dbField[16] = "id_dokter";
                                        $dbField[17] = "cust_usr_email";
                                        $dbField[18] = "cust_usr_kode_tampilan";  
                                         
                                    if($_POST["dep_konf_no_depan"]=="y") { 
                                      
                                         $dbField[19] = "cust_usr_huruf_urut"; 
                                         $dbField[20] = "cust_usr_kode";  
                                         
                                    } else if($_POST["dep_konf_no_depan"]=="n") { 
                                    
                                         $dbField[19] = "cust_usr_huruf_urut"; 
                                         $dbField[20] = "cust_usr_kode"; 
                                    }
                                             if(!$dataPasien){                         
                                             $pasienId = $dtaccess->GetTransID("global.global_customer_user","cust_usr_id",DB_SCHEMA);
                                             }else{
                                             $pasienId = $dataPasien["cust_usr_id"];	
                                             }
                                             $dbValue[0] = QuoteValue(DPE_CHAR,$pasienId);
                                             $dbValue[1] = QuoteValue(DPE_CHAR,$data[1]);
                                             $dbValue[2] = QuoteValue(DPE_CHAR,$data[5]);
                                             $dbValue[3] = QuoteValue(DPE_CHAR,$data[300]);
                                             $dbValue[4] = QuoteValue(DPE_CHAR,$data[2]);
                                             $dbValue[5] = QuoteValue(DPE_DATE,$data[3]);
                                             $dbValue[6] = QuoteValue(DPE_CHAR,$data[4]);
                                             $dbValue[7] = QuoteValue(DPE_CHAR,$data[8]);
                                             $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
                                             $dbValue[9] = QuoteValue(DPE_CHAR,$userData["name"]);
                                             $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                                             $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataJenis["jenis_id"]);
                                             $dbValue[12] = QuoteValue(DPE_CHAR,strtoupper($hurufKode));
                                             $dbValue[13] = QuoteValue(DPE_CHAR,$data[8]);
                                             $dbValue[14] = QuoteValue(DPE_CHAR,$data[19]);
                                             $dbValue[15] = QuoteValue(DPE_CHAR,$data[14]);
                                             $dbValue[16] = QuoteValue(DPE_NUMERIC,$dataDokter["usr_id"]);
                                             $dbValue[17] = QuoteValue(DPE_CHAR,$data[12]);
                                             $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode_tampilan"]);
                                             
                                        if($_POST["dep_konf_no_depan"]=="y") { 
                                         
                                             $dbValue[19] = QuoteValue(DPE_NUMERIC,$lastKode["cust_usr_huruf_urut"]+1); 
                                             $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);    
                                        } else if($_POST["dep_konf_no_depan"]=="n") {  
                                              
                                             $dbValue[19] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_huruf_urut"]); 
                                             $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
                                        }
                                             
                                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                                        if(!$dataPasien){
                                        $dtmodel->Insert() or die("insert error");
                                        }else{
										$dtmodel->Update() or die("update error");
										}
										
                                        unset($dtmodel); unset($dbValue); unset($dbKey);
                                        
                                          
                         
                         } else {
                              $error[] = $startLine;
                         }
                    
                    
                    $startLine ++;
               }
          }
          
          if($error) $error = implode("<br>Data Excel Baris ke ",$error);
     } 
     
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
        <tr class="tablecontent" align="center">
          <td width="40%" align="right" class="tablecontent">&nbsp;&nbsp;Klinik&nbsp;&nbsp;</td>
          <td width="60%" align="left" class="tablecontent">
			 <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--">- Semua Klinik -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";
				?>
					<option class="inputField" value="<?php echo $dataKlinik[$i]["dep_id"];?>"<?php if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
				<?php if (!$_POST["klinik"]) {?>
			         <?php echo "<font color='red'>Harap Pilih Klinik terlebih Dahulu </font>"; ?>
               <?php } ?>
		  </td>
		 </tr>
		 <tr class="tablecontent" align="center">
          <td width="40%" align="right" class="tablecontent">&nbsp;&nbsp;Pemisah / Delimiter&nbsp;&nbsp;</td>
          <td width="60%" align="left" class="tablecontent">
			 <select name="delimiter" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="">- Pilih Pemisah / Delimiter -</option>
				<option class="inputField" value=";"<?php if ($_POST["delimiter"]==";") echo"selected"?>> Titik Koma (;)</option>
				<option class="inputField" value=","<?php if ($_POST["delimiter"]==",") echo"selected"?>> Koma (,)</option>
				</select>
				<?php if (!$_POST["delimiter"]) {?>
			         <?php echo "<font color='red'>Harap Pilih Pemisah terlebih Dahulu </font>"; ?>
               <?php } ?>
		  </td>
		 </tr>
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
<br />
&nbsp;&nbsp;Untuk Contoh data silahkan klik <a href="Book1.csv">disini</a>


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
