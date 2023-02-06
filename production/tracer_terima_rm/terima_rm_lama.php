<?php
     //LIBRARY 
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."currency.php");
require_once($LIB."expAJAX.php");    
require_once($LIB."tampilan.php");

     //INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();             
$auth = new CAuth();
$skr = date("Y-m-d");                                                          
$time = date("H:i:s");
$userId = $auth->GetUserId();  
$table = new InoTable("table","100%","left");    
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
    // $poli = $auth->GetPoli();
     $tempatTracer = "0"; //Pada Rekam Medik
     //echo $theDep;
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $lokasi = $ROOT."/gambar/img_cfg";   
     if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
     if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
     
     if($konfigurasi["dep_logo"]!="n") {
      $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
    } elseif($konfigurasi["dep_logo"]=="n") { 
      $fotoName = $lokasi."/default.jpg"; 
    } else { $fotoName = $lokasi."/default.jpg"; }
    $plx = new expAJAX("CheckData,CheckDataReg,CheckDataPoli");
    
     //AUTHENTIFIKASI
     /*if(!$auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)){
          echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
        } */

     //VARIABLE AWAL

        if (!$_GET["transaksi"]) 
        {
         $transaksiId=$_POST["transaksi_paket_id"];
       } else {
        $transaksiId=$_GET["transaksi"];
      }
      
      if (!$_POST['id_poli']) $_POST['id_poli'] = $_GET['poli'];
      $poli ='--';
      $statusPasien["B"]="Baru";
      $statusPasien["L"]="Lama";

      if($_x_mode=="New") 
       $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") 
       $privMode = PRIV_UPDATE;
     else 
       $privMode = PRIV_DELETE;    

     if($_POST["x_mode"]) 
      $_x_mode = & $_POST["x_mode"];
    else 
      $_x_mode = "New";

      function CheckData($custUsrKode,$custuserId=null)//cek kalau ada yang sama
      {
        global $dtaccess;

        $sql = "SELECT cust_usr_id FROM global.global_customer_user a 
        WHERE a.cust_usr_kode = ".QuoteValue(DPE_CHAR,strtoupper($custUsrKode));

        if ($custuserId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_CHAR,$custuserId);
              //return $sql;
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataAdaClient = $dtaccess->Fetch($rs);

        return $dataAdaClient["cust_usr_id"];
      }

      function CheckDataReg($custUsrKode,$custuserId=null)//cek kalau ada yang sama
      {
        global $dtaccess,$skr;

        $sql = "select cust_usr_id FROM global.global_customer_user where 
        cust_usr_kode=".QuoteValue(DPE_CHAR,$custUsrKode);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataPasien = $dtaccess->Fetch($rs);

        $sql = "SELECT id_cust_usr FROM klinik.klinik_registrasi
        WHERE reg_tanggal = ".QuoteValue(DPE_DATE,$skr)." and  
        id_cust_usr = ".QuoteValue(DPE_CHAR,$dataPasien["cust_usr_id"]);
              //return $sql;
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataAdaClient = $dtaccess->Fetch($rs);

        return $dataAdaClient["id_cust_usr"];
      }

      function CheckDataPoli($custUsrKode,$custuserId=null)//cek kalau ada yang sama
      {
        global $dtaccess,$skr,$poli,$tempatTracer;

        $sql = "SELECT tracer_no_rm FROM klinik.klinik_tracer 
        WHERE tracer_tanggal = ".QuoteValue(DPE_DATE,$skr)." and  
        tracer_tempat = ".QuoteValue(DPE_CHAR,$tempatTracer)." and
        tracer_poli_tujuan = ".QuoteValue(DPE_CHAR,$poli)." and
        tracer_no_rm = ".QuoteValue(DPE_CHAR,$custUsrKode);

              //return $sql;
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataAdaClient = $dtaccess->Fetch($rs);

        return $dataAdaClient["tracer_no_rm"];
      }
     //JIKA MELAKUKAN TRACER
      if ($_GET["cust_usr_kode"]) {

        $waktuSekarang = date('Y-m-d H:i:s');
        $tanggalSekarang = date('Y-m-d');
        $jamSekarang = date('H:i:s');

        $sql = "select * from klinik.klinik_tracer 
        where tracer_no_rm = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_kode"])." and 
        tracer_tempat='0' and tracer_tanggal = ".QuoteValue(DPE_DATE,$_GET["tanggal"]);
                  //tracer_tempat='0' and tracer_terima_rm_waktu is null"; 
        $dataTracer = $dtaccess->Fetch($sql);
        $sql = "select * from global.global_customer_user 
        where cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
        $dataPasien = $dtaccess->Fetch($sql);

        $sql = "select * from klinik.klinik_registrasi 
        where id_cust_usr = ".QuoteValue(DPE_CHAR,$dataPasien["cust_usr_id"]); 
        $dataReg = $dtaccess->Fetch($sql);

        $dbTable = "klinik.klinik_tracer";
          $dbField[0]  = "tracer_id";   // PK
          $dbField[1]  = "tracer_terima_rm_waktu";
          $dbField[2]  = "tracer_terima_rm_who_update";
          $dbField[3]  = "tracer_terima_rm_when_update";
          $dbField[4]  = "tracer_tempat";
          $dbField[5]  = "is_kembali";
          
          $tracerId = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$dataTracer["tracer_id"]);         
          $dbValue[1] = QuoteValue(DPE_CHAR,$waktuSekarang);          
          $dbValue[2] = QuoteValue(DPE_CHAR,$userId);
          $dbValue[3] = QuoteValue(DPE_CHAR,$waktuSekarang);
          $dbValue[4] = QuoteValue(DPE_CHAR,$tempatTracer);
          $dbValue[5] = QuoteValue(DPE_CHAR,'y');
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Update() or die("update  error"); 

          unset($dbField);
          unset($dbValue);           
          unset($_POST["cust_usr_kode"]);

          $sql = "update global.global_customer_user set cust_akhir_rm=".QuoteValue(DPE_CHAR,$tempatTracer)." 
          where cust_usr_id=".QuoteValue(DPE_CHAR,$dataPasien["cust_usr_id"]);
          $dtaccess->Execute($sql);
          

}    //AKHIR TRANSAKSI TRCER


if ($_POST['reg_tanggal_awal']) {
  # code...
 $sql = "select a.*,b.id_poli,b.reg_kode_trans,b.reg_status_kondisi,d.poli_nama
 from klinik.klinik_tracer a left join 
 klinik.klinik_registrasi b on a.id_reg = b.reg_id left join
 global.global_auth_poli d on b.id_poli = d.poli_id
 where b.id_poli = ".QuoteValue(DPE_CHAR,$_POST['id_poli'])." and a.tracer_tanggal >= ".QuoteValue(DPE_DATE,$_POST['reg_tanggal_awal'])." and a.tracer_tanggal <= ".QuoteValue(DPE_DATE,$_POST['reg_tanggal_akhir'])."
 and tracer_tempat ='0'  and a.tracer_terima_rm_waktu is not null
 order by a.tracer_when_update desc";     
 // echo $sql;     
 $rs_edit = $dtaccess->Execute($sql);
 $dataTable = $dtaccess->FetchAll($rs_edit);
 $tableHeader = "&nbsp;Terima Berkas RM";


}
else{

 $sql = "select a.*,b.reg_kode_trans,d.poli_nama
 from klinik.klinik_tracer a left join 
 klinik.klinik_registrasi b on a.id_reg = b.reg_id left join
 global.global_auth_poli d on b.id_poli = d.poli_id
 where a.tracer_tanggal = ".QuoteValue(DPE_DATE,$skr)."
 and tracer_tempat ='0'  and a.tracer_terima_rm_waktu is not null
 order by a.tracer_when_update desc";
   // echo $sql;       
 $rs_edit = $dtaccess->Execute($sql);
 $dataTable = $dtaccess->FetchAll($rs_edit);
 $tableHeader = "&nbsp;Terima Berkas RM";
}

$sql = "select b.poli_nama, b.poli_id 
from global.global_auth_user_poli a 
left join global.global_auth_poli b on a.id_poli = b.poli_id
where a.id_usr = ".QuoteValue(DPE_CHAR,$userId)." and (b.poli_tipe<>'I' and b.poli_tipe<>'A' and b.poli_tipe<>'L') order by poli_nama ASC";    
$rs = $dtaccess->Execute($sql);
$dataPoli = $dtaccess->FetchAll($rs);

     // --- Buat Tabel Tracer---- //
$counterHeader = 0;

    //  $sql = "select a.*,b.reg_kode_trans,d.poli_nama
    //          from klinik.klinik_tracer a left join 
    //          klinik.klinik_registrasi b on a.id_reg = b.reg_id left join
    //          global.global_auth_poli d on a.tracer_poli_asal_kirim_rm = d.poli_id
    //          where a.tracer_tanggal = ".QuoteValue(DPE_DATE,$skr)."
       // and tracer_tempat = '0' 
    //          and a.tracer_terima_rm_waktu is not null
    //          order by a.tracer_when_update desc";       
    //  $rs_edit = $dtaccess->Execute($sql);
    //  $dataTable = $dtaccess->FetchAll($rs_edit);
    //  $tableHeader = "&nbsp;Terima Berkas RM";

?>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>     
<script type="text/javascript" src="ajax.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.js"></script>
<link rel="stylesheet" href="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.css" type="text/css" />

<script language="Javascript">
  <? $plx->Run(); ?>



  function transaksi_tracer(inField, e) 
  {
    var charCode,test;
//Get key code (support for all browsers)
if(e && e.which){
  charCode = e.which;
}else if(window.event){
  e = window.event;
  charCode = e.keyCode;
}

if(charCode == 13) 
{
     //alert(CheckDataDobel(frmEdit.cust_usr_kode.value,'type=r'));
     if(!document.getElementById('cust_usr_kode').value)
     {
      alert('RM Pasien Belum Dimasukkan');
      return false; 
      document.getElementById('cust_usr_kode').focus();
    }
    else if(!CheckData(frmEdit.cust_usr_kode.value,'type=r'))
    {
      alert('Kode RM Pasien Salah');
      return false; 
      document.frmEdit.cust_usr_kode.value='';
    }  
    else if(!CheckDataReg(frmEdit.cust_usr_kode.value,'type=r'))
    {
      alert('Pasien tidak diregistrasi hari ini');
      return false; 
      document.frmEdit.cust_usr_kode.value='';
    }  
    else
    {
      document.frmEdit.submit();
    }
  }
  
}  

</script> 
<TITLE>TERIMA STATUS DARI POLI</TITLE>
<body>
  <div id="header">
    <table border="0" width="100%" valign="top">
      <tr>
        <td width="50%" align="left" valign="top">
          <!-- <a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a> -->
          <img src="<?php echo $fotoName ;?>" height="75">
        </td>
        <td width="50%" valign="top" align="right">
          <a href="#" target="_blank"><font size="6">TERIMA BERKAS RM</font></a>&nbsp;&nbsp;
        </td>
      </tr>
    </table>
  </div>
  <div id="body">
    <div id="scroller">
      <table width="100%" border="0" cellpadding="1" cellspacing="1"> 
        <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">  
         <tr><td>Klinik
         </td>
       </tr>
       <tr><td>
        <select id="id_poli" class="select2_single form-control" name="id_poli" >
          <option value="">- Pilih Klinik -</option>
          <?php 
          for($i=0,$n=count($dataPoli);$i<$n;$i++){
            ?>
            <option value="<?php echo $dataPoli[$i]["poli_id"];?>"
              <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?>   
            </option>
          <?php } ?>
        </select>

      </td>
    </tr>

    <tr>
      <td>
        Tanggal Awal <input type="date" name="reg_tanggal_awal" placeholder="Tanggal Awal" value="<? echo $_POST["reg_tanggal_awal"]; ?>">


        Tanggal Akhir <input type="date" name="reg_tanggal_akhir" placeholder="Tanggal Akhir" value="<? echo $_POST["reg_tanggal_akhir"]; ?>">

        <button type="submit">Cari</button></td>
      </tr> 

      <tr>                                         
       <table width="100%" border="1" cellpadding="1" cellspacing="1">                                        
        <tr class="subheader">
          <td align="left" width="10%" >No. Registrasi &nbsp;</td>
          <td align="left" width="5%" >Waktu &nbsp;</td>
          <td align="left" width="5%" >No. RM &nbsp;</td>                                               
          <td align="left" width="20%" >Nama Pasien&nbsp;</td> 
          <td align="left" width="5%" > Status Pasien</td>
          <td align="left" width="5%" > Poli Asal</td>
          <td align="left" width="5%" > Terima Status</td>
        </tr>

        <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>  
          <?php $findCariPage = 'ganti_jumlah_find.php?id='.$dataTable[$i]["penjualan_detail_id"];?>
          <tr class="tablecontent-odd">
            <td align="left" width="10%" ><?php echo $dataTable[$i]["reg_kode_trans"];?></td>
            <td align="left" width="10%" ><?php echo formatTimestamp($dataTable[$i]["tracer_terima_rm_waktu"]);?></td>
            <td align="left" width="5%" ><?php echo $dataTable[$i]["tracer_no_rm"];?></td>
            <td align="left" width="20%" ><?php echo $dataTable[$i]["tracer_nama"];?></td>                                               
            <td align="left" width="5%" ><?php echo $statusPasien[$dataTable[$i]["tracer_status_pasien"]];?></td> 
            <?php if($dataTable[$i]["tracer_poli_asal_kirim_rm"]=='--'){ ?>
              <td align="left" width="5%" > <?php echo "Rekam Medik"; ?></td>                                              
            <?php }else{ ?>
              <td align="left" width="5%" ><?php echo $dataTable[$i]["poli_nama"];?></td>
            <?php } ?>
            <?php if ($dataTable[$i]['is_kembali'] == 'y') { ?> 
              <td>&nbsp;</td>
            <?php } else {
              if ($dataTable[$i]["reg_status_kondisi"]!='2') {
                         # code...
                ?>
                <td><a href="terima_rm.php?cust_usr_kode=<?php echo $dataTable[$i]['tracer_no_rm'] ?>&poli=<?php echo $poli; ?>&tanggal=<?php echo $dataTable[$i]['tracer_tanggal']; ?>"><img src="../gambar/ok.png" > </a> </td>
              <?php }
            } ?>
            <!-- <input type="submit" name="cust_usr_kode" value="<?php echo $dataTable[$i]['cust_usr_kode'] ?>"><img src="../gambar/ok.png" > -->

          </tr>

        <?php } ?>


      </tr> 

    </table>                                                                    

    <script type="text/javascript">    
document.frmEdit.cust_usr_kode.focus();     //focus di cust_usr_kode
</script>  

</tr>            
<tr>                       
  <td colspan="4" class="tablecontent">&nbsp;</td>                  
</tr>              

<tr>                   
  <td colspan="4">                       
  </td>              
</tr>      

<input type="hidden" name="reg_tanggal" value="<? echo $_POST["reg_tanggal"]; ?>">
</form>  
</table> 
</div>


</div>
</div>