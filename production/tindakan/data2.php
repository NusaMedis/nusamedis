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
     

if(isset($_POST["header"]) && !empty($_POST["header"])){
     
     if($_POST['header']) $sql_where_tindakan[] = "id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$_POST['header']);
     $sql_tindakan = "select * from  klinik.klinik_kategori_tindakan where 1=1";
     if($sql_where_tindakan)  $sql_tindakan .= " and ".implode(" and ",$sql_where_tindakan);
     $sql_tindakan .= " order by kategori_urut asc";
    //echo  $sql_where_tindakan;
     $rs_tindakan = $dtaccess->Execute($sql_tindakan);
     $dataKategoriTindakan = $dtaccess->FetchAll($rs_tindakan);
    //Count total number of rows
    $rowCount =  count($dataKategoriTindakan);
    
    //Display cities list
    if($rowCount > 0){
       if($rowCount = 1) echo '<option value="">Pilih Kategori Tindakan</option>';
        for($i=0,$n=count($dataKategoriTindakan);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataKategoriTindakan[$i]["kategori_tindakan_id"].'">'.
                            $dataKategoriTindakan[$i]["kategori_tindakan_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Kategori Tindakan</option>';
    }
}
   


?>