<?
     require_once("connect.php");

     if($_GET["TanggalUpdate"]) $_POST["TanggalUpdate"]= $_GET["TanggalUpdate"];
     
     if($_GET["TanggalUpdate"]){
         
     //cari data pengeluaran
     $sql1 = pg_query($link, "select * from bios.bios_transaksi_keuangan a
                    left join bios.bios_akun_keuangan b on a.id_akun = b.akun_id
                    left join bios.bios_bank c on a.id_bank = c.bank_id
                    where b.akun_nomor='5251190'
                    and transaksi_tanggal =".QuoteValue(DPE_DATE,$_POST["TanggalUpdate"])."
                    order by transaksi_tanggal_update desc, transaksi_waktu_update desc");
     $dataPengeluaran = pg_fetch_assoc($sql1);
     
   if(!$dataPengeluaran){
        $json_data ='
              {"Message":"Maaf data Pengeluaran belum ada !"
              }
              '; 

   }else{  

// JSON data
 $json_data ='
              {"Pengeluaran":
              [{"Tanggal":"'.$dataPengeluaran["transaksi_tanggal"].'",
              "KodeAkun":"'.$dataPengeluaran["akun_nomor"].'",
              "Saldo":"'.$dataPengeluaran["transaksi_nominal"].'",
              "TanggalUpdate":"'.$dataPengeluaran["transaksi_tanggal_update"].' '.$dataPengeluaran["transaksi_waktu_update"].'"}]
              }
              '; 
 
 }
  }else{
  $json_data ='
              {"Message":"Maaf Parameter belum sesuai !"
              }
              '; 

}
 echo $json_data; 
?>