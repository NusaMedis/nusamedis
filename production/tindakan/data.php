<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."login.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
     $tglSekarang = date("Y-m-d");
     $jamSkrg =  date("H:i:s");
     $auth = new CAuth();
     $userId = $auth->GetUserId();
     $tglKunjungan = date("d-m-Y");
     
     
# get klinik
if(isset($_POST["instalasi"]) && !empty($_POST["instalasi"])){
     
     if($_POST['instalasi']) $sql_where_header[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['instalasi']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     // echo $sql_header;
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);
    //Count total number of rows
    $rowCount =  count($dataKategoriTindakanHeader);
    
    //Display cities list
    if($rowCount > 0){
       if($rowCount = 1) echo '<option value="">Pilih Kategori Tindakan Header</option>';
        for($i=0,$n=count($dataKategoriTindakanHeader);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"].'">'.$dataKategoriTindakanHeader[$i]["kategori_tindakan_header_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Kategori Tindakan Header</option>';
    }
}
   


?>