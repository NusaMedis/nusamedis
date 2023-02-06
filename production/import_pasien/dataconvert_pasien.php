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

         // KONFIGURASI
      $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $rs_edit = $dtaccess->Execute($sql);
      $row_edit = $dtaccess->Fetch($rs_edit);
      $dtaccess->Clear($rs_edit);

      $_POST["dep_konf_no_depan"] =  $row_edit["dep_konf_no_depan"];       
      $_POST["dep_jml_nol_depan"] = $row_edit["dep_jml_nol_depan"];
      $_POST["dep_panjang_kode_pasien"] = $row_edit["dep_panjang_kode_pasien"];
      
    //nomor RM nya disesuaikan dengan kebutuhan jumlahanya
    if($_POST["btnNext"]){
  // --- cari data pasien terakhir ---
      $sql = "select max(cast (cust_usr_kode as integer)) as kode
             from global.global_customer_user where cust_usr_kode <> '100' and cust_usr_kode <> '500' and cust_usr_kode <> '501'";                         
      $rs = $dtaccess->Execute($sql);
      $dataPasien = $dtaccess->Fetch($rs);

      //perbaiki data demografi  hapus rm yang bukan angka
      $sql = "delete from public.data_demografi_full where \"NORM\" !~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$'";
      $rs = $dtaccess->Execute($sql);

      //hapus data demografi yang norm nya lebih dari 400000
      $sql = "delete from public.data_demografi_full where \"NORM\" > '400000'";
      $rs = $dtaccess->Execute($sql);

//echo $sql; die();
      //cari data pasien data demografi
    $sql = "select * from public.data_demografi_full where cast(\"NORM\" as integer) > ".QuoteValue(DPE_CHAR,$dataPasien["kode"])." and cast(\"NORM\" as integer) < '400000' order by \"NORM\" asc";
    $rs = $dtaccess->Execute($sql);
    $dataTable = $dtaccess->FetchAll($rs);

//echo $sql; //die();
    for ($i=0,$n=count($dataTable);$i<$n;$i++){
     // checking jumlah digit rm nya
     $arrRM = strlen($dataTable[$i]["NORM"]);

    // echo "panjang rm ".$arrRM;
     if($arrRM=$_POST["dep_panjang_kode_pasien"]){
      $_POST["cust_usr_kode"]=$dataTable[$i]["NORM"];
     }else{
      $_POST["cust_usr_kode"] = str_pad($dataTable[$i]["NORM"],"0",STR_PAD_LEFT);
     }

    //tampilan koe rm pake titik
    $arr = str_split($_POST["cust_usr_kode"],"2");
    $usr_kode_tampilan = implode(".",$arr);
    $_POST["cust_usr_kode_tampilan"] = $usr_kode_tampilan;
        //echo "NORM ".$dataTable[$i]["NORM"];
        //die();
    //cari data yang ada jika ada lewat jika tidak insert
    $sql = "select cust_usr_id from global_customer_user where cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
    $rs = $dtaccess->Execute($sql);
    $dataPasienAsli = $dtaccess->Fetch($rs);

    if(!$dataPasienAsli){
                        
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
                                                                     
                                             $pasienId = $dtaccess->GetTransID("global.global_customer_user","cust_usr_id",DB_SCHEMA);
                                             
                                             $dbValue[0] = QuoteValue(DPE_CHAR,$pasienId);
                                             $dbValue[1] = QuoteValue(DPE_CHAR,htmlentities($dataTable[$i]["NAMA_PASIEN"]));
                                             $dbValue[2] = QuoteValue(DPE_CHAR,htmlentities($dataTable[$i]["ALAMAT"]));
                                             $dbValue[3] = QuoteValue(DPE_CHAR,null);
                                             $dbValue[4] = QuoteValue(DPE_CHAR,$dataTable[$i]["TEMPAT_LAHIR"]);
                                             $dbValue[5] = QuoteValue(DPE_DATE,$dataTable[$i]["TGL_LAHIR"]);
                                             $dbValue[6] = QuoteValue(DPE_CHAR,$dataTable[$i]["JNS_KELAMIN"]);
                                             $dbValue[7] = QuoteValue(DPE_CHAR,$dataTable[$i]["NO_TELP"]);
                                             $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
                                             $dbValue[9] = QuoteValue(DPE_CHAR,$userData["name"]);
                                             $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                                             $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataJenis["jenis_id"]);
                                             $dbValue[12] = QuoteValue(DPE_CHAR,strtoupper($hurufKode));
                                             $dbValue[13] = QuoteValue(DPE_CHAR,$dataTable[$i]["NO_TELP"]);
                                             $dbValue[14] = QuoteValue(DPE_CHAR,$dataTable[$i]["PEKERJAAN"]);
                                             $dbValue[15] = QuoteValue(DPE_CHAR,$dataTable[$i]["AGAMA"]);
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

                                        $dtmodel->Insert() or die("insert error");
                    
                                        unset($dtmodel); unset($dbValue); unset($dbKey);
  }
  }
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

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>

<div id="bodyku">
<br />
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">

<fieldset>
     <legend><strong>Convert Data Pasien</strong></legend>
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
          <tr>
               <td align="center" class="tablecontent" colspan="2">
                    <input type="submit" name="btnNext" value="Proses" class="submit" OnClick="document.frmEdit.btnNext.value = 'Please Wait'">
               </td>
          </tr>
     </table>
</fieldset>

</form>

<?php if($_POST["btnNext"]) {?>
    <font style="font-size:14px">Proses Import Sudah Selesai</font>
<?php }?>

<!--------Buat Helpicon----------->
</div>
