<?php 
 	 require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");


      if($_GET['id_kategori_tindakan_header_instalasi']!=''){

      	 $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_GET['id_kategori_tindakan_header_instalasi'])." order by kategori_tindakan_header_urut asc";

    	     $rs_header = $dtaccess->Execute($sql_header);
    	     $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);

    		while($row<=$dataKategoriTindakanHeader){
          echo '<option class="inputField" value="'.$row['kategori_tindakan_header_id'].'" >'.$row['kategori_tindakan_header_nama'].'</option>';

    		}
      }

       if($_GET['id_kategori_tindakan_header']!=''){

           $sql_where_tindakan   = "select * from  klinik.klinik_kategori_tindakan where id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$idKategoriTindakanHeader)." order by kategori_urut asc";

           $rs_tindakan          = $dtaccess->Execute($sql_where_tindakan);
           $dataKategoriTindakan = $dtaccess->FetchAll($rs_tindakan);
           
        while($row<=$dataKategoriTindakan){
          echo '<option class="inputField" value="'.$row['kategori_tindakan_id'].'" >'.$row['kategori_tindakan_nama'].'</option>';

        }
       }


 ?>