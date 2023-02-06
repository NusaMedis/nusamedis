<?php
	   // Library
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php"); 
     
     // Inisialisasi Lib
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId(); 
//     $ArrayPoliTujuan = $_GET['id_pengirim'];     
     $id_dokter=$_GET['id_pengirim'];
	     
     // buat ambil tindakan --
     $sql = "select * from logistik.logistik_pengirim where id_dep =".QuoteValue(DPE_CHAR,$depId)." and id_gudang= ".QuoteValue(DPE_CHAR,$id_dokter)." order by pengirim_nama asc";
//     $sql = "select * from global.global_auth_user where id_dep =".QuoteValue(DPE_CHAR,$depId)." and (id_rol = '2' or id_rol = '5' ) and usr_poli =".QuoteValue(DPE_CHAR,$id_dokter)."  
	//			     order by usr_name asc";
//    echo $sql;
     $rs=$dtaccess->Execute($sql);
		 $dataDokter= $dtaccess->FetchAll($rs);
//	print_r($dataDokter);
  	 // echo currency_format($datatindakan["biaya_total"]);  id="barang" onChange="detail(this.value);"    
       
?>
       <?php if($dataDokter) { ?>
        <select title="id_pengirim" name="id_pengirim" id="id_pengirim">
           <option value="" align="center">-| Pilih Pengirim |-</option>
           <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
           <option value="<?php echo $dataDokter[$i]["pengirim_id"];?>" <?php //if($_POST["id_kategori"][0]==$datatindakan[$i]["kategori_tindakan_id"]) echo "selected"; ?>><?php echo substr($dataDokter[$i]["pengirim_nama"], 0, 50);?></option>
           <?php } ?>
       </select>         
       <?php } else {	echo "Silahkan pilih nama Penerima"; } ?>       
       
