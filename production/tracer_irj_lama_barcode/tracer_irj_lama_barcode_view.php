<?php 
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");     
     require_once($LIB."datamodel.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."dateLib.php"); 
     require_once($LIB."bit.php");     
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");     

     $dtaccess = new DataAccess();     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $enc = new textEncrypt();
     $tablePerawatan= new InoTable("table1","100%","center");
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depLowest = $auth->GetDepLowest();
     $auth = new CAuth();  
     $poliId = $auth->IdPoli();
     $depId = $auth->GetDepId();

	/* if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } */
    
	
	/* if(!$auth->IsAllowed("fo_input_rm",PRIV_READ)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else if($auth->IsAllowed("fo_input_rm",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     } */

      
     $statusAntri[0] = "Antri";
     $statusAntri[1] = "Masuk";
     $idPoli=$_GET["antrian"];
     
     
     $skr = date("d-m-Y");
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     
     $plx = new expAJAX("GetPerawatan,CekTracer"); 
     
 
      
     $editPage = "entry_icd_edit.php";
     
     if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
                                
     /*$sql = "select * from  global.global_customer_user";   
     //if($_POST["klinik"] && $_POST["klinik"]!="--") $sql .=" where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $sql .= " order by cust_usr_nama asc";        
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
                         echo "test";   */
     // DATA POLI
     $sql = "select * from global.global_auth_poli order by poli_id ASC";
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
     //
     function CekTracer() 
	 {
      global  $dtaccess; 
      $sql = "select reg_id, id_cust_usr, reg_tracer_barcode from klinik.klinik_registrasi";
	  $sql .=" where (reg_tracer_barcode is null or reg_tracer_barcode='n') and ( reg_utama is null or reg_utama = '' ) ";
//	  $sql .=" and reg_status_pasien='L' ";
       
         $rs = $dtaccess->Execute($sql);
         $dataTracer = $dtaccess->Fetch($rs);
		if ($dataTracer > 0 ) return $dataTracer['reg_id']."-".$dataTracer['id_cust_usr'];
	 }
                 
     function GetPerawatan() {

      global  $dtaccess, $view, $tablePerawatan, $statusAntri, $enc, 
      $auth, $combo, $poli, $depId, $tgl, $skr, $regId;
      //return $skr;    
          // -- paging config ---//
       $recordPerPage = 50;
       if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
       else $currPage = 1;
       $startPage = ($currPage-1)*$recordPerPage;
       $endPage = $startPage + $recordPerPage;
       // -- end paging config ---//      
        
        $table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");  
        $in_tglawl = $skr; 
        $in_tglakh = $skr;
        
        $sql_where[] = "DATE(reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($in_tglawl));
        $sql_where[] = "DATE(reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($in_tglakh));
          
          $sql_where[] = "1=1"; 
	        $sql_where = implode(" and ",$sql_where);
               
          $sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_tempat_lahir,b.cust_usr_tanggal_lahir,
                  b.cust_usr_umur,b.cust_usr_alamat,a.reg_id,a.reg_status,a.reg_status_pasien,
                  e.usr_name, a.reg_waktu, d.poli_nama, b.cust_usr_id, a.id_poli, a.reg_tanggal, a.id_pembayaran, 
                  f.jenis_nama
                  from klinik.klinik_registrasi a 
                  join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
                  left join global.global_auth_poli d on d.poli_id = a.id_poli
                  left join global.global_auth_user e on a.id_dokter = e.usr_id
                  left join global.global_jenis_pasien f on f.jenis_id=a.reg_jenis_pasien
                  where b.cust_usr_kode<>'500' and 
                  (a.reg_status='M0' or a.reg_status='E0' or 
                  a.reg_status='E1' or a.reg_status='E2' or a.reg_status='F0') 
                  and a.id_dep like '".$depId."' and (reg_tracer_barcode is null or reg_tracer_barcode='n') 
                  and (reg_utama='' or reg_utama is null) and ".$sql_where."
                  order by reg_when_update asc";
         //return $sql; 
         $rs = $dtaccess->Query($sql);
         $dataTable = $dtaccess->FetchAll($rs);
              
          $isAllowedDel = $auth->IsAllowed("fo_registrasi",PRIV_DELETE);
     
     $table = "<table border='1' width='100%'>";     
     $table .= "<tr class='subheader'>";
     $table .= "<td width='35%' colspan='1' align='left'>&nbsp;</td>";
     $table .= "<td width='35%' colspan='10' align='right'>&nbsp;</td>";
     $table .= "</tr>";
     $table .= "</table>";

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "TTL";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
           
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
                   
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Print";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;

          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_tempat_lahir"]."/".format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;

               $umur=explode("~",$dataTable[$i]["cust_usr_umur"]);
               $tbContent[$i][$counter][TABLE_ISI] = $umur[0]." th";
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               if ($dataTable[$i]["reg_status_pasien"]=='B')
                 $statusPasien="Baru";
               else  
                 $statusPasien="Lama";
               $tbContent[$i][$counter][TABLE_ISI] = $statusPasien;
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++; 
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
                              
               $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="32" height="32" src="'.$ROOT.'../../../../gambar/icon/cetak.png" style="cursor: pointer; border:0px solid white" alt="Cetak Tracer" title="Cetak Tracer" onClick="ProsesCetakBarcode(\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["cust_usr_id"].'\');"/>';              
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++; 				
				
          }

          return $table."".$tablePerawatan->RenderView($tbHeader,$tbContent,$tbBottom);
		  $regId = $dataTable[0]["reg_id"];	
      
     }

    
?>
<head>
<TITLE>TRACER BARCODE KECIL</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
<?php// echo $view->RenderBody("ipad_depans.css",true,"TRACER IRJ PASIEN LAMA"); ?>

<script type="text/javascript">
<? $plx->Run(); ?>
var _wnd_new;

var mTimer;

function timer(){     
     clearInterval(mTimer);     
     GetPerawatan('target=perawatan_div');       
     mTimer = setTimeout("timer()", 5000);
	//alert(CekTracer('type=r'));
	 if(CekTracer('type=r')){
		var mystr =  CekTracer('type=r');
		var myarr =  mystr.split("-");
		myvar = myarr[0] + "," + myarr[1];
		//alert(myvar);
		ProsesCetakBarcode(myarr[0],myarr[1]);
		//alert(CekTracer('type=r'));
	 }
}

timer();

function BukaWindow(url,judul)  
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=2480,height=3508,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}


function ProsesCetakBarcode(id_reg,id) {
 //alert(id_reg+id);
  BukaWindow('cetak_barcode.php?id_reg='+id_reg+'&id='+id,'Cetak Barcode');
//  BukaWindow('../registrasi/cetakbarcode.php?id=<?php echo $IdCust;?>&id_reg=<?php echo $IdReg;?>&reg=<?php echo $kodeTrans;?>&noantri=<?php echo $noantri;?>','No Antrian');
	document.location.href='<?php echo $thisPage;?>';
}

function submitenter(myfield,e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   Search();
   return false;
   }
else
   return true;
}


</script>

<script language="javascript" type="text/javascript">
function hapus() {
  if(confirm('apakah anda yakin untuk mengahpus data sender ini ?'));
  else return false;
}
</script>
<?php// echo $view->RenderBodyEnd(); ?>
</head>

<style type="text/css">
#top{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0068c9), to(#007bed));
background: -moz-linear-gradient(top, #0068c9, #007bed); 
}
#footer{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#007bed), to(#0068c9));
background: -moz-linear-gradient(top, #007bed, #0068c9);
}
</style>

<body onLoad="timer();">
	<table border="1" cellpadding="0" cellspacing="0" align="left" width="100%">
		<tr valign="top">
			<td width="100%">
				<form name="frmViewUmum" method="POST" action="proses_ugd_edit.php">
   				<div id="perawatan_div" style="height:1000;overflow:auto">
					<?php echo GetPerawatan(); ?>
				</div>
				</form>
			</td>
		</tr>
	</table>

</body>
</html>
