<?
     require_once("connect.php");

     if($_GET["TanggalUpdate"]) $_POST["TanggalUpdate"]= $_GET["TanggalUpdate"];
     
     if($_GET["TanggalUpdate"]){
     
     //cari data akun pengeluaran
     $sql = pg_query($link, "select * from bios.bios_akun_keuangan where akun_nomor='3'");
     $akunId = pg_fetch_assoc($sql);
     
     //cari data pengeluaran
     $sql1 = pg_query($link, "select * from bios.bios_transaksi_keuangan a
                    left join bios.bios_akun_keuangan b on a.id_akun = b.akun_id
                    left join bios.bios_bank c on a.id_bank = c.bank_id
                    where b.akun_nomor='3'
                    and transaksi_tanggal =".QuoteValue(DPE_DATE,$_POST["TanggalUpdate"])."
                    order by transaksi_tanggal_update desc, transaksi_waktu_update desc");
     $dataSaldo = pg_fetch_assoc($sql1);
     
   if(!$dataSaldo){
    $json_data ='
              {"Message":"Maaf data Saldo belum ada !"
              }
              '; 
   }else{  


// JSON data
 $json_data ='
              {"SaldoBLU":
               [{"Tanggal":"'.$dataSaldo["transaksi_tanggal"].'",
              "KodeJenisRekening":"'.$dataSaldo["akun_nomor"].'",
              "NamaBank":"'.$dataSaldo["bank_nama"].' '.$dataSaldo["bank_cabang"].'",
              "Saldo":"'.$dataSaldo["transaksi_nominal"].'",
              "TanggalUpdate":"'.$dataSaldo["transaksi_tanggal_update"].' '.$dataSaldo["transaksi_waktu_update"].'"}]
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