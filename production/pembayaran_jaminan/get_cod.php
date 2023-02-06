<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	 $tglSekarang = date("Y-m-d");

            $data = [];

               $sql = 'SELECT rawat_icd_id, icd_nomor, icd_nama, icd_id, rawat_icd_kasus,rawat_icd_urut,rawat_icd_status,id_inacbg from klinik.klinik_perawatan_icd a';
               $sql .= ' LEFT JOIN klinik.klinik_icd b on b.icd_id = a.id_icd ';
               $sql .= ' WHERE id_rawat = '.QuoteValue(DPE_CHAR, $_POST['id_rawat']);
               $sql .= ' ORDER BY rawat_icd_status asc';
               //echo $sql;
               $q = $dtaccess->fetchAll($sql);
               
               for($i=0; $i < count($q); $i++){
                if ($q[$i]['rawat_icd_kasus']=='B') {
                  $b = 'Baru';
                }elseif ($q[$i]['rawat_icd_kasus']=='L') {
                  $b = 'Lama';
                }else{
                  $b = '';
                }
                $row = array(
                  'rawat_icd_id' => $q[$i]['rawat_icd_id'],
                  'icd_nomor' => $q[$i]['icd_nomor'],
                  'icd_nama' => $q[$i]['icd_nama'],
                  'icd_id' => $q[$i]['icd_id'],
                  //'rawat_icd_kasus' => $q[$i]['rawat_icd_kasus'],
                  'rawat_icd_kasus' => $b,
                  'rawat_icd_urut' => $q[$i]['rawat_icd_urut'],
                  'rawat_icd_status' => $q[$i]['rawat_icd_status'],
                  'id_inacbg' => $q[$i]['id_inacbg'],
                );
                $data[]=$row;
               }
               echo json_encode($data);
	//}

?>