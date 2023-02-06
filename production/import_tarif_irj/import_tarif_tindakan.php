<?php
    require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."bit.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");   

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
     $tahunTarif = $auth->GetTahunTarif();
     
     /*
     if(!$auth->IsAllowed("man_import_tarif_irj",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_import_tarif_irj",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }  */

     if(!$_POST["klinik"]) $_POST["klinik"]=$depId;

     $delimiter = ",";
     $startLine = 0;


     if($_POST["csvFile"]) $csvFile = $_POST["csvFile"];
     else $csvFile = $ROOT."temp/";
     // echo $csvFile;
     // die();
    $plx = new expAJAX();
    

     $sql = "select * from klinik.klinik_split  order by split_urut";
     $rs_edit = $dtaccess->Execute($sql);
     $dataSplit = $dtaccess->FetchAll($rs_edit);
     
     

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
          }
          else
          {

             while ($data = fgetcsv($myFile, 500000, $delimiter))
             {

              if($data[0] || $data[1] || $data[2] || $data[3] || $data[4]  || $data[5]  ||
                 $data[6] || $data[7] || $data[8] || $data[9] || $data[10] || $data[11] ||
                 $data[12] || $data[13] || $data[14] || $data[15] || $data[16] || $data[17] ||
                 $data[18] || $data[19] || $data[20] || $data[21] || $data[22] || $data[23] ||
                 $data[24] || $data[25])
              {

                    $n=count($dataSplit);
                   
                    $sql = "select kategori_tindakan_id from klinik.klinik_kategori_tindakan where
                    kategori_tindakan_kode = ".QuoteValue(DPE_CHAR,$data[2]);
                    $rs_edit = $dtaccess->Execute($sql);
                    $dataKatTindakan = $dtaccess->Fetch($rs_edit);

                    $sql = "select poli_id from global.global_auth_poli where upper(poli_nama) like ".QuoteValue(DPE_CHAR,'%'.strtoupper($data[12]).'%');
                    $dataPoli = $dtaccess->Fetch($sql);
                    
                    
                        $dbTable = "klinik.klinik_biaya"; // buat tindakan

                      $dbField[0] = "biaya_id";   // PK
                      $dbField[1] = "biaya_nama";
                      $dbField[2] = "biaya_jenis_sem";
                      $dbField[3] = "biaya_jenis";
                      $dbField[4] = "biaya_kategori";
                      $dbField[5] = "id_dep";
                        $dbField[6] = "biaya_kode";
                  $dbField[7] = "id_poli";
                         

                         $biayaId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHAR,$biayaId);
                         $dbValue[1] = QuoteValue(DPE_CHAR,strtoupper($data[1]));
                         $dbValue[2] = QuoteValue(DPE_CHAR,$data[4]);
                         $dbValue[3] = QuoteValue(DPE_CHAR,$data[5]);
                         $dbValue[4] = QuoteValue(DPE_CHAR,$data[2]);
                         $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
                         $dbValue[6] = QuoteValue(DPE_CHAR,strtoupper($data[0]));
                         $dbValue[7] = QuoteValue(DPE_CHAR,strtoupper($dataPoli['poli_id']));
                                                 
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                        $sql = "select biaya_id from klinik.klinik_biaya where biaya_nama = ".QuoteValue(DPE_CHAR,$data[1]);
                        $dataBiayaId = $dtaccess->Fetch($sql);
                        
                        if ($dataBiayaId['biaya_id']=='') {
                          $dtmodel->Insert() or die("insert error");
                        }

                        unset($dtmodel); unset($dbValue); unset($dbKey);
                        
                        $sql = "select kelas_id from klinik.klinik_kelas where
                        kelas_tingkat = ".QuoteValue(DPE_NUMERIC,$data[3]);
                        $rs_edit = $dtaccess->Execute($sql);
                        $dataKelas = $dtaccess->Fetch($rs_edit);

                        if ($dataBiayaId != '') {
                          $biayaId = $dataBiayaId['biaya_id'];
                        }

                        $dbTable = "klinik.klinik_biaya_tarif";

                      $dbField[0] = "biaya_tarif_id";   // PK
                      $dbField[1] = "id_biaya";
                      $dbField[2] = "biaya_total";
                      $dbField[3] = "id_kelas";
                        $dbField[4] = "is_cito";
                      $dbField[5] = "biaya_tarif_tgl_awal";
                      $dbField[6] = "biaya_tarif_tgl_akhir";
                        $dbField[7] = "id_jenis_pasien";
                        
                         

                         $biayaTarifId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHAR,$biayaTarifId);   
                         $dbValue[1] = QuoteValue(DPE_CHAR,$biayaId);
                         $dbValue[2] = QuoteValue(DPE_NUMERIC,$data[11]);
                         $dbValue[3] = QuoteValue(DPE_CHAR,$dataKelas["kelas_id"]);
                         $dbValue[4] = QuoteValue(DPE_CHAR,$data[6]);
                         $dbValue[5] = QuoteValue(DPE_DATE,$data[7]);
                         $dbValue[6] = QuoteValue(DPE_DATE,$data[8]);
                         $dbValue[7] = QuoteValue(DPE_CHAR,$data[13]);
                         
                                                 
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
// print_r($dbValue);die();
                        $dtmodel->Insert() or die("insert error");

                        unset($dtmodel); unset($dbValue); unset($dbKey);


                        for($i=0,$n=count($dataSplit);$i<$n;$i++){ 

                      
                        $dbTable = "klinik.klinik_biaya_split";

                        $dbField[0] = "bea_split_id";   // PK
                        $dbField[1] = "id_biaya";
                        $dbField[2] = "id_split";
                        $dbField[3] = "bea_split_nominal";
                        $dbField[4] = "id_dep";
                        $dbField[5] = "bea_split_persen";
                        $dbField[6] = "id_tahun_tarif";

                         $beaSplitId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHAR,$beaSplitId);
                         $dbValue[1] = QuoteValue(DPE_CHAR,$biayaId);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$i]["split_id"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$data[11]);
                         $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
                         $dbValue[5] = QuoteValue(DPE_NUMERIC,0);
                         $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);

                 //       print_r($dbValue); //die();
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                        $dtmodel->Insert() or die("insert error");

                        unset($beaSplitId); unset($dtmodel); unset($dbValue); unset($dbKey);

                        }
                        

                        }  // end check benar ngga is file excelnya
                        else
                         {
                              $error[] = $startLine;
                         }


                    $startLine ++;
               }

          }

        if($error) $error = implode("<br>Data Excel Baris ke ",$error);
     }


     
?>

<br /><br /><br /><br />

<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">

<?php $plx->Run();?>

function cariSplit(id){ 
  document.getElementById('div_split').innerHTML = GetSplit(id,'type=r');
}

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
     <legend><strong>Import Tarif IRJ</strong></legend>
     <table width="80%" border="0" cellpadding="1" cellspacing="1">
        <tr>
             <td width="25%" align="right" class="tablecontent"><strong>CSV File<?php if($err){?> <font color="red">(*)</font><?php } ?>&nbsp;</strong></td>
             <td width="75%" class="tblCol">
                  <input type="file" name="csv_file" size=25 class="submit">
                  <span id="div_uh"></span>
                  <input type="submit" name="btnNext" value="Submit" class="submit" OnClick="document.frmEdit.btnNext.value = 'Please Wait'">
             </td>
        </tr>
     </table>
</fieldset>
<br />
<font size="3" color="red">
&nbsp;&nbsp;Kolom Split Data Tindakan :
<br /><br />
</font>
<font size="2" color="red">
&nbsp;&nbsp;Kolom 1    : Kode Tindakan<br />
&nbsp;&nbsp;Kolom 2    : Nama Tindakan<br />
&nbsp;&nbsp;Kolom 3    : Kategori Tindakan<br />
&nbsp;&nbsp;Kolom 4    : Tingkat Kelas<br />
&nbsp;&nbsp;Kolom 5    : Jenis Tindakan<br />
&nbsp;&nbsp;Kolom 6    : Variable INACBG<br />
&nbsp;&nbsp;Kolom 7    : CITO/ELEKTIF ->C/E<br />
&nbsp;&nbsp;Kolom 8    : Tgl Awal Berlaku(yyyy-mm-dd)<br />
&nbsp;&nbsp;Kolom 9    : Tgl Akhir Berlaku(yyyy-mm-dd)<br />
<?php for($i=0,$n=count($dataSplit);$i<$n;$i++){ ?>
&nbsp;&nbsp;Kolom <?php echo $i+10; ?>    : <?php echo $dataSplit[$i]["split_nama"]?>  <br />
<?php } ?>  
<?php $n =count($dataSplit); ?>
&nbsp;&nbsp;Kolom <?php echo $n+10; ?>   : Total <br />
&nbsp;&nbsp;Kolom 13   : Nama Poli <br />
&nbsp;&nbsp;Kolom 14   : Jenis Pasien <br />


<br />
&nbsp;&nbsp;Untuk Contoh data (dengan 3 split) silahkan klik <a href="irj.csv">disini</a>
<br />
&nbsp;&nbsp;Pemisah antar kolom adalah <strong>;</strong>
</font>
<input type="hidden" name="dep_posting_poli" id="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"];?>" />
</form>

<?php if($_POST["btnNext"] && !$err) {?>
    <font style="font-size:14px">Import Data Selesai</font>
<?php }?>

<?php if($err){?><label><font color="red" style="font-size:12px; font-weight:bold;">&nbsp;Pilih Files csv terlebih dahulu</font></label><?php } ?>

<?php if($error) {?>
     <br /><br />
     <font color="red">
          Ada Beberapa Data yang tidak Valid<br />
          <?php echo "Data Excel Baris ke ".$error;?>
     </font>
<?php }?>
<!--------Buat Helpicon----------->
</div>
 