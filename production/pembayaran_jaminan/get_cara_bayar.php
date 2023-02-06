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


if(isset($_POST["id_jenis1"]) && !empty($_POST["id_jenis1"])){
     $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
       $dataJenisBayar2= $dtaccess->FetchAll($sql);  

    $rowCount =  count($dataJenisBayar2);
    echo $sql;
    //Display cities list
    if($rowCount > 0){
        echo '<option value="">Pilih Jenis Bayar</option>';
        for($i=0,$n=count($dataJenisBayar2);$i<$n;$i++){ 
          unset($spacer); 
            $length = (strlen($dataJenisBayar2[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
            for($j=0;$j<$length;$j++) $spacer .= "..";  
            echo '<option class="form_control" value="'.$dataJenisBayar2[$i]["jbayar_id"].'">'.
                            $spacer." ".$dataJenisBayar2[$i]["jbayar_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Pilihan</option>';
    }
   
   }
?>