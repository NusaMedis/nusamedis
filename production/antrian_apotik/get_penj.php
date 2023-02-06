<?php
 	require_once("../penghubung.inc.php");
    require_once($LIB."datamodel.php");
    require_once($LIB."dateLib.php");
    require_once($LIB."currency.php");                                                                  
    require_once($LIB."expAJAX.php");    
    require_once($LIB."tampilan.php");

    $now = date("Y-m-d H:i:s");
    $bef = date("Y-m-d H:i:s", strtotime("-48 hours"));
    $sql = "SELECT penjualan_nomor, poli_nama, e.cust_usr_nama from apotik.apotik_penjualan a 
        left join klinik.klinik_registrasi b on a.id_reg = b.reg_id 
        left join klinik.klinik_registrasi c on b.reg_utama = c.reg_id
        left join global.global_auth_poli d on c.id_poli = d.poli_id
        left join global.global_customer_user e on a.id_cust_usr = e.cust_usr_id
    		where penjualan_create >= '$bef' and penjualan_create <= '$now' 
        and id_resep is not null 
        and penjualan_grandtotal is null
    		order by penjualan_create desc";
   	$dataBelum = $dtaccess->FetchAll($sql);

   	$sql = "SELECT penjualan_nomor, poli_nama, e.cust_usr_nama from apotik.apotik_penjualan a 
            left join klinik.klinik_registrasi b on a.id_reg = b.reg_id 
            left join klinik.klinik_registrasi c on b.reg_utama = c.reg_id
            left join global.global_auth_poli d on c.id_poli = d.poli_id
            left join global.global_customer_user e on a.id_cust_usr = e.cust_usr_id
            where penjualan_create >= '$bef' and penjualan_create <= '$now' 
            and id_resep is not null 
            and penjualan_grandtotal is not null
            order by penjualan_create desc";
   	$dataSudah = $dtaccess->FetchAll($sql);

   	$dataSend['dataBelum'] = $dataBelum;
   	$dataSend['dataSudah'] = $dataSudah;

   	echo json_encode($dataSend);

?>
