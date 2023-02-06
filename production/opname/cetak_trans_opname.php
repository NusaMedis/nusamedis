<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/tampilan.php");

     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $depLowest = $auth->GetDepLowest();
	   $theDep = $auth->GetNamaLogistik();  //Ambil Gudang yang aktif
     
     // -- cek konfigurasi stok -- //
	   //$sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     //$rs = $dtaccess->Execute($sql);
     //$gudang = $dtaccess->Fetch($rs);
     //$_POST["id_dep"] = $gudang["conf_gudang_obat"];
     
     if($_GET["klinik"]) { 
              $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
              $_POST["klinik"] = $_POST["klinik"]; }
      else { 
              $_POST["klinik"] = $depId; 
      }
      
      
     if(!$_POST["id_jenis"])  {
       $_POST["id_jenis"] = "2"; 
     } else if($_GET["id_jenis"]) {
       $_POST["id_jenis"] = $_GET["id_jenis"];     
     } 


//     if($_GET["tanggal"]) $_POST["tgl_awal"] = $_GET["tanggal"]; 
     if(!$_POST["tgl_awal"])  {
       $_POST["tgl_awal"] = $skr; 
     } else if($_GET["tanggal"]) {
       $_POST["tgl_awal"] = $_GET["tanggal"];     
     } 
     
     $sql_where[] = "b.id_gudang = ".QuoteValue(DPE_CHAR,$_GET["id_gudang"]);
     $sql_where[] = "h.id_gudang = ".QuoteValue(DPE_CHAR,$_GET["id_gudang"]);

    if($_GET["nama"] && $_GET["nama"]!="") $sql_where[] = "upper(a.item_nama) like ".QuoteValue(DPE_CHAR,strtoupper($_GET["nama"]."%"));
     if($_POST["id_jenis"] && $_POST["id_jenis"]!="--"){
     $sql_where[] = "a.item_tipe_jenis = ".QuoteValue(DPE_CHAR,$_POST["id_jenis"]);
      } 
    if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
    
    
    
    if($_GET["id_kategori"] && $_GET["id_kategori"]!="--" && $_GET["id_kategori"]!="nn") $sql_where[] = "a.id_kategori = ".QuoteValue(DPE_CHAR,$_GET["id_kategori"]);
        elseif($_GET["id_kategori"]=="nn") $sql_where[] = "(a.id_kategori = '' or a.id_kategori is null or a.id_kategori = '--') ";

    if($sql_where) $sql_where = implode(" and ",$sql_where);
  
  //$sql1 = "update logistik.logistik_item_batch set batch_status ='y' where batch_flag ='O'";
   	//$rs_batch = $dtaccess->Execute($sql1);
    
//    echo $sql1; 

  $sql  = "select g.batch_id, g.id_item, a.item_id, a.item_nama ,a.id_kategori,kategori_tindakan_nama, a.id_kategori_tindakan, a.item_tipe_jenis, a.id_dep, b.stok_dep_saldo ,
          c.gudang_nama, d.dep_nama as departemen ,e.grup_item_nama, g.batch_create, g.batch_no, g.batch_tgl_jatuh_tempo, h.stok_batch_dep_saldo
          from logistik.logistik_item a                      
          left join logistik.logistik_stok_dep b on b.id_item = a.item_id
          left join logistik.logistik_gudang c on c.gudang_id = b.id_gudang
          left join global.global_departemen d on d.dep_id = a.id_dep
          left join logistik.logistik_grup_item e on e.grup_item_id=a.id_kategori
          left join klinik.klinik_kategori_tindakan f on a.id_kategori_tindakan = f.kategori_tindakan_id
          join logistik.logistik_item_batch g on g.id_item = a.item_id
          join logistik.logistik_stok_batch_dep h on h.id_batch = g.batch_id";
//  if($sql_where) $sql .= " where batch_status = 'y' and ".$sql_where." order by id_kategori asc, item_tipe_jenis , item_nama asc";
  if($sql_where) $sql .= " where ".$sql_where." order by id_kategori asc, item_nama, batch_create asc";
//	echo $sql;
  $rs_edit = $dtaccess->Execute($sql);
  $dataItem = $dtaccess->FetchAll($rs_edit);
	    
	    for($i=0,$n=count($dataItem);$i<$n;$i++) {
   
      if($dataItem[$i]["id_item"]==$dataItem[$i-1]["id_item"]){
          $hitung[$dataItem[$i]["id_item"]] += 1;
       }
   }
	    
	    $tglAwal=format_date($_POST["tanggal_awal"]);
	    $tglAkhir=$_POST["tanggal_akhir"];
	
	 //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);

     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     $sql = "select * from logistik.logistik_gudang where gudang_id ='".$_GET["id_gudang"]."'";
     $rs = $dtaccess->Execute($sql);
     $dataGudang = $dtaccess->Fetch($rs);
     
  $sql = "select grup_item_id,grup_item_nama from logistik.logistik_grup_item where grup_item_id ='".$_GET["id_kategori"]."'"; 
	     $rs = $dtaccess->Execute($sql);
  	$dataKatgudang = $dtaccess->Fetch($sql);
     
  $lokasi = $ROOT."/gambar/img_cfg";
  if($konfigurasi["dep_logo"]) $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  else $fotoName = $lokasi."/default.jpg";
     		
?>

<?php echo $view->RenderBody("inventori_prn.css",true); ?>

<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}

#splitBorder tr td table{
border-collapse:collapse;
}

#splitBorder tr td table tr td {
border:1px solid black;
}

body {
     font-family:      Verdana, Arial, Helvetica, sans-serif;
     font-size:        12px;
     margin: 5px;
     margin-top:		  0px;
     margin-left:	  0px;
}

.menubody{
     background-image:    url(gambar/background_01.gif);
     background-position: left;
}
.menutop {
     font-family: Arial;
     font-size: 11px;
     color:               #FFFFFF;
     background-color:    #000e98;
     background-image:     url(gambar/bg_topmenu.png);
     background-repeat:	repeat-x;
     font-weight: bold;
     text-transform: uppercase;
     text-align: center;
     height: 25px;
     background-position: left top;
     cursor:pointer;
}

.menubottom {
     background-image:    	 url(gambar/submenu_bg.png);
     background-repeat:   	no-repeat;
}

.menuleft {
     font-family:      		Arial, Helvetica, sans-serif;
     font-size:        		12px;
     color:					#333333;
     background-image:    	 url(gambar/submenu_btn.png);
     background-repeat:   	repeat-y;
     font-weight: 			bolder;
}

.menuleft_bawah {
     font-family:      		Arial, Helvetica, sans-serif;
     font-size:        		8px;
     color:					#333333;
     background-image:    	 url(gambar/submenu_btn_bawah.png);	
     font-weight: 			bold;	
}

.img-button {
     cursor:     pointer;
     border:     0px;
}

.menuleft a:link, a:visited, a:active {
     font-family:      Arial, Helvetica, sans-serif;
     font-size:        12px;
     text-decoration:  none;
     color:            #333333;
}

.menuleft a:hover {
     font-family:      Arial, Helvetica, sans-serif;
     font-size:        12px;
     text-decoration:  none;
     color:            #6600CC;
}

table {
     font-family:    Verdana, Arial, Helvetica, sans-serif;
     font-size:      12px;
	padding:0px;
	border-color:#000000;
	border-collapse : collapse;
	border-style:solid;
	}

#tablesearch{
	display:none;
}

.passDisable{
     color: #0F2F13;
     border: 1px solid #f1b706;
     background-color: #ffff99;
}

.tabaktif {
     font-family: Verdana, Arial, Helvetica, sans-serif;
     font-size: 10px;
     color:               #E60000;
     background-color:    #ffe232;
     background-image:     url(gambar/tbl_subheadertab.png);
     background-repeat:	repeat-x;
     font-weight: bolder;
     height: 18;
     text-transform: capitalize;
}

.tabpasif {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color:               #000000;
	background-color:    #ffe232;
	background-image:     url(gambar/tbl_subheader2.png);
	background-repeat:	repeat-x;
	font-weight: bolder;
	height: 18;
	text-transform: capitalize;
}

.caption {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-style: normal;
}

a:link, a:visited, a:active {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
    text-decoration:  none;
    color:            #1F457E;

}

a:hover {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
    text-decoration:  underline;
    color:            #8897AE;
}

.titlecaption {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-style: oblique;
	font-weight: bolder;

}

.tableheader {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	color:               #333333;
	font-weight: bold;
	text-transform: uppercase;
}

.tablesmallheader {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;	
	font-weight: bold;
	height: 18px;
	background-position: left top;
}

.tablecontent {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;	
	height: 18px;
}

.tablecontent-odd {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	height: 18px;
}

.tablecontent-kosong {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: bold;
	color: #FC0508;
	height: 18px;
}

.tablecontent-medium {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 16px;
	font-weight: lighter;
	background-color:    #fff5b3;
	height: 18px;
}

.tablecontent-gede {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 23px;
	font-weight: lighter;
	background-color:    #fff5b3;
	height: 18px;
}

.tablecontent-odd {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;
	height: 18px;
}

.tablecontent-odd-kosong {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #FC0508;
	font-weight: lighter;
	height: 18px;
}

.tablecontent-odd-medium {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 16px;
	font-weight: lighter;
	height: 18px;
}

.tablecontent-odd-gede {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 23px;
	font-weight: lighter;
	height: 18px;
}

.tablecontent-telat {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #FC0508;
	font-weight: lighter;
	background-color:    #fff5b3;
	height: 18px;
}

.tablecontent-odd-telat {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #FC0508;
	font-weight: lighter;
	height: 18px;
}

.inputField
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #0F2F13;
	border: 1px solid #1A5321;
	background-color: #EBF4A8;
}


.content {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	background-color:    #E7E6FF;
	height: 18px;
}

.content-odd {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	height: 18px;
}

.subheader {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color:               #000000;
	background-color:    #FFFFFF;
	font-weight: bolder;
	height: 18;
	text-transform: capitalize;
}

.subheader-print {
    font-family:        Verdana, Arial, Helvetica, sans-serif;
    font-size:          10px;
    color:              #000000;
    font-weight:        bolder;
    height:             18;
}

.staycontent {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;
}

.button, submit, reset {
    display:none;
    visibility:hidden;
}

select, option {
	font-family:	Verdana, Arial, Helvetica, sans-serif;
	font-size:		10px;
	text-indent:	2px;
	margin: 2px;
	left: 0px;
	clip:  rect(auto auto auto auto);
	border-top: 0px;
	border-right: 0px;
	border-bottom: 0px;
	border-left: 0px;
}

input, textarea {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	border: 1px solid #f1b706;
	text-indent:	2px;
	margin: 2px;
	left: 0px;
	width: auto;
	vertical-align: middle;
}

.subtitlecaption {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-style: normal;
	font-weight: 500;
}

.inputcontent {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;
	background : #E6EDFB url(../none);
	border: none;
	text-align: right;
}

.hlink {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}

.navActive {
	color:  #cc0000;
}

fieldset {
	border: thin solid #2F2F2F;
}

.whiteborder {
	border: none;
	margin: 0px 0px;
	padding: 0px 0px;
	border-collapse : collapse;
}

.adaborder {
	border-left: none;
	border-top: none;
	border-bottom: none;
	border-right: solid #999999 1px;
	margin: 0px 0px;
	padding: 0px 0px;
	border-collapse : separate;
}

.divcontent {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;
	background-color:    #E7E6FF;
	border-bottom: solid #999999 1px;
	border-right: solid #999999 1px;
}

.curedit {
	text-align: right;
}
 
#div_cetak{ display: block; }

#tblSearching{ display: none; }

#printMessage {
    display: none;
}

#noborder.tablecontent {
border-style: none;
}

#noborder.tablecontent-odd {
border-style: none;
}
.noborder {
border-style: none;
}
 
    body {
	   font-family:      Arial, Verdana, Helvetica, sans-serif;
	   margin: 0px;
	    font-size: 10px;
    }
    
    .tableisi {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: none #000000 0px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    
    .tableisi td {
	    border: solid #000000 1px; 
	    padding:4px;
    }
    
    .tablenota {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: solid #000000 1px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    .tablenota .judul  {
	    border: solid #000000 1px; 
	    padding:4px;
    }
    
    .tablenota .isi {
	    border-right: solid black 1px;
	    padding:4px;
    }
    
    .ttd {
	    height:50px;
    }
    
    .judul {
	    font-size:      14px;
	    font-weight: bolder;
	    border-collapse:collapse;
    }
    
    
    .judul {
	    font-size:      14px;
	    font-weight: bolder;
	    border-collapse:collapse;
    }
    
    
    .judul1 {
	    font-size: 14px;
	    font-weight: bolder;
    }
    .judul2 {
	    font-size: 14px;
	    font-weight: bolder;
    }
    .judul3 {
	    font-size: 18px;
	    font-weight: normal;
    }
    
    .judul4 {
	    font-size: 12px;
	    font-weight: bold;
	    background-color : #CCCCCC;
	    text-align : center;
    }
    .judul5 {
	    font-size: 16px;
	    font-weight: bold;
	    background-color : #d6d6d6;
	    text-align : center;
	    color : #000000;
    } 
    .judul6 {
	    font-size: 12px;
	    font-weight: bold;
	    text-align : center;
	    color : #000000;
    }  
</style>

<table border="0" cellpadding="2" rowspan="3" cellspacing="0" align="center">
    <tr>
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?php echo $fotoName ;?>" height="60"></td>
      <td style="text-align:center;font-size:16px;font-family:times new roman;font-weight:bold;" class="tablecontent">

      <?php echo $konfigurasi["dep_nama"]?><BR>
      <?php echo $konfigurasi["dep_kop_surat_1"]?><BR>
      </td>
       </tr> 
       <tr>
       <td style="text-align:center;font-size:14px;font-family:times new roman;" class="tablecontent">
     
      <?php echo $konfigurasi["dep_kop_surat_2"]?></td>
    </tr>
  </table>
<br>
 <table border="0" cellpadding="3" cellspacing="0" style="align:left" width="100%"> 
 <tr>
        <td style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Nama Gudang : <?php echo $dataGudang["gudang_nama"] ?> </td>
        </tr> 
        <tr>
        <td style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Kategori : <?php echo $dataKatgudang["grup_item_nama"] ?> </td>
        </tr>    
      <tr>
        <td style="text-align:center;font-size:15px;font-family:sans-serif;font-weight:bold;" class="tablecontent">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Laporan Stok Opname</td>
        </tr>
  </table>

  <br>
<br>
      <table width="100%" border="1"> 			  
          <tr> 
               <td align="center" class="subheader" width="2%">No</td>                                          
               <td align="center" class="subheader" width="10%">Kategori</td>
               <td align="center" class="subheader" width="15%">Nama Item</td>
             <!--  <td align="center" class="subheader" width="5%">Gudang</td>  -->
               <td align="center" class="subheader" width="8%">No Batch</td>
               <td align="center" class="subheader" width="8%">Expire Date</td>

               <td align="center" class="subheader" width="5%">Stok</td>
                                    
          </tr>
          <?php for($i=0,$j=0,$counter=0,$n=count($dataItem);$i<$n;$i++,$counter=0,$j++){ 
          ?>
          <tr  class="<?php if($i%2==0) echo 'tablecontent-odd'; else echo 'tablecontent'; ?>">  
            <?php if($dataItem[$i]["id_item"]!=$dataItem[$i-1]["id_item"]) { 
                     $dataSpan["jml_span"] = $hitung[$dataItem[$i]["id_item"]] += 1; 
                     $m++; ?>
                <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $i+1?>                  
               </td>
               <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $view->RenderLabel("grup_item_nama","grup_item_nama",$dataItem[$i]["grup_item_nama"], null,false);?>                  
               </td>
               <td align="left" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">     
                    <?php echo $view->RenderLabel("item1","item1",$dataItem[$i]["item_nama"], null,false);?>
                    <?php echo $view->RenderHidden("id_item[$i]","id_item[$i]",$dataItem[$i]["item_id"]);?>                                
                    <!--<input type="text" name="id_item[<?php echo $i;?>]" id="id_item_<?php echo $i;?>" value="<?php echo $dataItem[$i]["item_id"];?>" />-->
               </td>              
             <!--  <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $view->RenderLabel("gudang_nama","gudang_nama",$dataItem[$i]["gudang_nama"], null,false);?>                  
               </td>  -->
               <?php } ?>
                                          
               <td align="center">
                    <?php echo $dataItem[$i]["batch_no"];?>
                   
                    <?php //echo $view->RenderTextBox("id_item_batch[$i]","id_item_batch[$i]","8","30",$dataItem[$i]["item_id"]);?>
               </td>
               <td align="center">
                    <?php echo format_date($dataItem[$i]["batch_tgl_jatuh_tempo"]);?>
               </td>


               <td align="center">


                  &nbsp;  

               </td>
               
               <?php if($dataItem[$i]["id_item"]!=$dataItem[$i-1]["id_item"]) { $dataSpan["jml_span"]; ?>
                     
              
               <?php } ?>
          </tr>
          <?php } ?>
        <tr>
               
          </tr>
		</table>


<?php echo $view->RenderBodyEnd(); ?>

 
