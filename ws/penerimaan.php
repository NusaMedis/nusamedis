<?
     require_once("connect.php");

     if($_GET["TanggalUpdate"]) $_POST["TanggalUpdate"]= $_GET["TanggalUpdate"];
     
     if($_GET["TanggalUpdate"]){
        
     //cari data penerimaan
     $sql1 = pg_query($link, "select * from bios.bios_transaksi_keuangan a
                    left join bios.bios_akun_keuangan b on a.id_akun = b.akun_id
                    left join bios.bios_bank c on a.id_bank = c.bank_id
                    where b.akun_nomor='424121'
                    and transaksi_tanggal =".QuoteValue(DPE_DATE,$_POST["TanggalUpdate"])."
                    order by transaksi_tanggal_update desc, transaksi_waktu_update desc");
     $dataPenerimaan = pg_fetch_assoc($sql1);
     
   if(!$dataPenerimaan){
        $json_data ='
              {"Message":"Maaf data Penerimaan belum ada !"
              }
              '; 

   }else{  

// JSON data
 $json_data ='
              {"Penerimaan":
               [{"Tanggal":"'.$dataPenerimaan["transaksi_tanggal"].'",
              "KodeAkun":"'.$dataPenerimaan["akun_nomor"].'",
              "Saldo":"'.$dataPenerimaan["transaksi_nominal"].'",
              "TanggalUpdate":"'.$dataPenerimaan["transaksi_tanggal_update"].' '.$dataPenerimaan["transaksi_waktu_update"].'"}]
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