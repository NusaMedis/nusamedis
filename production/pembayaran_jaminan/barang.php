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
        
     //$id_kategori=$_POST['id_kategori'];
     $id_kategori=explode("-", $_POST['id_kategori']);
	     
       // buat ambil tindakan --
/*     	 $sql = "select * from klinik.klinik_biaya where biaya_jenis = 'TA' and 
        id_dep =".QuoteValue(DPE_CHAR,$depId)." and 
        biaya_kategori =".QuoteValue(DPE_CHAR,$id_kategori[0])." and    
        id_jenis_pasien =".QuoteValue(DPE_CHAR,$id_kategori[1])." and    
        id_shift =".QuoteValue(DPE_CHAR,$id_kategori[2])."     
        order by biaya_nama asc";       */

     	 $sql = "select a.*,b.kategori_tindakan_nama from klinik.klinik_biaya a
        left join klinik.klinik_kategori_tindakan b on a.biaya_kategori = b.kategori_tindakan_id
        left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id = b. id_kategori_tindakan_header
        where 
        a.id_shift = '1' and  
        a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and 
        a.biaya_kategori =".QuoteValue(DPE_CHAR,$id_kategori[0])."     
        order by a.biaya_nama asc";
        //echo $sql;

//      a.id_shift =".QuoteValue(DPE_CHAR,$id_kategori[2])." and  

		   $datatindakan= $dtaccess->FetchAll($sql);
		   //echo currency_format($datatindakan["biaya_total"]);       
		   if(!$_POST["txtQty"]) $_POST["txtQty"]=1;
       
?>
               
           <select name="id_biaya" id="id_biaya" >
             <?php for($i=0,$n=count($datatindakan);$i<$n;$i++){ ?>
             <option value="<?php echo $datatindakan[$i]["biaya_id"]."-".$datatindakan[$i]["biaya_total"]."-".$id_kategori[1];?>" <?php //if($_POST["id_kategori"][0]==$datatindakan[$i]["kategori_tindakan_id"]) echo "selected"; ?>><?php echo substr($datatindakan[$i]["biaya_nama"], 0, 35);?> (<?php echo substr($datatindakan[$i]["kategori_tindakan_nama"], 0, 35);?> ) Rp.<?php echo currency_format($datatindakan[$i]["biaya_total"]);?></option>
             <?php } ?>
           </select>      
          &nbsp;Jumlah : &nbsp; 
           <input type="text" size="5" name="txtQty" id="txtQty" value="<?php echo $_POST["txtQty"]; ?>" />   

