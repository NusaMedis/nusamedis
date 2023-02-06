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
if(isset($_POST["instalasi_id"]) && !empty($_POST["instalasi_id"])){
     
     if($_POST["instalasi_id"] == 'J') {
        $kondisi = "  poli_tipe <> 'I' and poli_tipe <> 'G' and poli_tipe <> 'A' and poli_tipe <> 'R' and poli_tipe <> 'L' ";
     } elseif($_POST["instalasi_id"] == 'G') {
        $kondisi = " poli_tipe = ".QuoteValue(DPE_CHAR,$_POST["instalasi_id"]); 
     }else {
        $kondisi = " 1=1"; 
     }
     
     $sql = "select poli_id, poli_nama from global.global_auth_poli where ".$kondisi;
     $sql .= " order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKlinik = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataKlinik);
    
    //Display cities list
    if($rowCount > 0){
       if($rowCount = 1) echo '<option value="">Pilih klinik</option>';
        for($i=0,$n=count($dataKlinik);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataKlinik[$i]["poli_id"].'">'.
                            $dataKlinik[$i]["poli_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Klinik</option>';
    }
}
# get dokter
/*if(isset($_POST["poli_id"]) && !empty($_POST["poli_id"])){
//urusan dokter
    $sql = "select e.usr_id, e.usr_name 
            from klinik.klinik_jadwal_dokter a
            join global.global_auth_poli b on b.poli_id = a.id_poli
            left join global.global_auth_sub_instalasi c on c.sub_instalasi_id = a.id_sub_instalasi
            left join global.global_auth_instalasi d on a.id_instalasi = d.instalasi_id
            left join global.global_auth_user e on a.id_dokter = e.usr_id
            left join global.global_auth_role f on e.id_rol = f.rol_id ";   
    $sql .= " where f.rol_jabatan = 'D'";   
    $sql .= " and usr_status = 'y'";    
    $sql .= " and a.id_poli =".QuoteValue(DPE_CHAR,$_POST['poli_id']);  
    $sql .= " and a.jadwal_dokter_hari =".QuoteValue(DPE_CHAR,date('N'));  
    $sql .= " and a.jadwal_dokter_jam_mulai <=".QuoteValue(DPE_DATE,date("h:i:s"));  
    $sql .= " and a.jadwal_dokter_jam_selesai >=".QuoteValue(DPE_DATE,date("h:i:s"));  
    $sql .= " order by usr_name asc";
    //die($sql);   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataDokter = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataDokter);
   // print_r($dataDokter);
  // die($sql);

    if($rowCount > 0){
        echo '<option value="">Pilih dokter</option>';
        for($i=0,$n=count($dataDokter);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataDokter[$i]["usr_id"].'">'.
                            $dataDokter[$i]["usr_name"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Dokter</option>';
    }
   
   }*/
if(isset($_POST["id_poli"]) && !empty($_POST["id_poli"])){    
//urusan paket
  $sql = "select * from global.global_paket where id_poli =".QuoteValue(DPE_CHAR,$_POST['id_poli']);
  $sql .= " order by paket_nama asc";
  $rs = $dtaccess->Execute($sql);
  $dataPaketPoli = $dtaccess->FetchAll($rs);
  
  $rowPaket =  count($dataPaketPoli);
    
    //Display cities list
    if($rowPaket > 0){
        echo '<option value="">Pilih Paket</option>';
        for($i=0,$n=count($dataPaketPoli);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataPaketPoli[$i]["paket_id"].'">'.
                            $dataPaketPoli[$i]["paket_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Paket</option>';
    }    
} 


# get kamar
if(isset($_POST["kelas_id"]) && !empty($_POST["gedung_id"])){

    $sql = "select a.kamar_id, a.kamar_nama from klinik.klinik_kamar a ";
    $sql .= " where a.id_gedung_rawat =".QuoteValue(DPE_CHAR,$_POST['gedung_id']);  
    $sql .= " and a.id_kelas =".QuoteValue(DPE_CHAR,$_POST['kelas_id']);    
    $sql .= " order by kamar_nama asc"; 
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataKamar= $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataKamar);
    
    //Display cities list
    if($rowCount > 0){
        echo '<option value="">Pilih Kamar</option>';
        for($i=0,$n=count($dataKamar);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataKamar[$i]["kamar_id"].'">'.
                            $dataKamar[$i]["kamar_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Kamar</option>';
    }
} 

# get bed
if(isset($_POST["kamar_id"]) && !empty($_POST["kamar_id"])){

    $sql = "select bed_id, bed_kode from klinik.klinik_kamar_bed where bed_reserved='n'";
    $sql .= " and id_kamar =".QuoteValue(DPE_CHAR,$_POST['kamar_id']);  
    $sql .= " order by bed_nama asc";   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataBed= $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataBed);
    
    //Display cities list
    if($rowCount > 0){
        echo '<option value="">Pilih Bed</option>';
        for($i=0,$n=count($dataBed);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataBed[$i]["bed_id"].'">'.
                            $dataBed[$i]["bed_kode"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Bed</option>';
    }
} 

# get dokter irna
if(isset($_POST["irna"]) && !empty($_POST["irna"])){
    $sql = "select a.usr_name,a.usr_id,c.rol_jabatan
            from global.global_auth_user a 
            left join global.global_auth_role c on a.id_rol = c.rol_id";
    $sql .= " where c.rol_jabatan = 'D'";   
    $sql .= " and usr_status = 'y'";
    $sql .= " order by usr_name asc";   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataDokter = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataDokter);
    
    //Display cities list
    if($rowCount > 0){
        echo '<option value="">Pilih dokter</option>';
        for($i=0,$n=count($dataDokter);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataDokter[$i]["usr_id"].'">'.
                            $dataDokter[$i]["usr_name"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Dokter</option>';
    }
} 


# ambil data cara kunjungan
if(isset($_POST["prosedur_id"])){
    $sql = "select rujukan_id, rujukan_nama
            from global.global_rujukan ";
    $sql .= " where rujukan_flag = '$_POST[prosedur_id]'";   
    $sql .= " order by rujukan_nama asc";   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $hasil = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($hasil);

    if($rowCount > 0){
        echo '<option value="">Pilih Prosedur Masuk</option>';
        for($i=0,$n=count($hasil);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$hasil[$i]["rujukan_id"].'">'.
                            $hasil[$i]["rujukan_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Data</option>';
    }
} 

# ambil data cara kunjungan detail
if(isset($_POST["rujukan_id"])){
    $sql = "select *
            from global.global_rujukan_det ";
    $sql .= " where id_rujukan = '$_POST[rujukan_id]'";   
    $sql .= " order by rujukan_det_nama asc";   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $hasil = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($hasil);

    if($rowCount > 0){
        echo '<option value="">Pilih Cara Kunjungan</option>';
        for($i=0,$n=count($hasil);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$hasil[$i]["rujukan_det_id"].'">'.
                            $hasil[$i]["rujukan_det_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Data</option>';
    }
} 
     

/* 
# get sub instalasi
if(isset($_POST["instalasi_id"]) && !empty($_POST["instalasi_id"])){
    // data sub instalasi
     $sql = "select sub_instalasi_id, sub_instalasi_nama from global.global_auth_sub_instalasi";
     $sql .= " where id_instalasi = '$_POST[instalasi_id]'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataSubInstalasi = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataSubInstalasi);
    
    //Display states list 
    if($rowCount > 0){
        echo '<option value="">Pilih Sub Instalasi</option>';
        for($i=0,$n=count($dataSubInstalasi);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataSubInstalasi[$i]["sub_instalasi_id"].'">'.
                            $dataSubInstalasi[$i]["sub_instalasi_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Sub Instalasi</option>';
    }
} 
# get klinik
if(isset($_POST["sub_instalasi_id"]) && !empty($_POST["sub_instalasi_id"])){
    $sql = "select poli_id, poli_nama from global.global_auth_poli where id_sub_instalasi = '$_POST[sub_instalasi_id]'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKlinik = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataKlinik);
    
    //Display cities list
    if($rowCount > 0){
        echo '<option value="">Pilih klinik</option>';
        for($i=0,$n=count($dataKlinik);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataKlinik[$i]["poli_id"].'">'.
                            $dataKlinik[$i]["poli_nama"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Klinik</option>';
    }
}*/
# get dokter
if(isset($_POST["poli_id"]) && !empty($_POST["poli_id"])){
//urusan dokter
    $sql = "select a.usr_name,a.usr_id,b.id_poli,c.rol_jabatan
            from global.global_auth_user a 
            left join global.global_auth_user_poli b on a.usr_id = b.id_usr
            left join global.global_auth_role c on a.id_rol = c.rol_id
            left join klinik.klinik_jadwal_dokter d on a.usr_id = d.id_dokter";
    $sql .= " where c.rol_jabatan = 'D'  and b.id_poli=d.id_poli";   
    $sql .= " and usr_status = 'y'";    
    $sql .= " and b.id_poli =".QuoteValue(DPE_CHAR,$_POST['poli_id']);
    $sql .= " and d.jadwal_dokter_jam_mulai <".QuoteValue(DPE_DATE,date("H:i:s"));
    $sql .= " and d.jadwal_dokter_jam_selesai >".QuoteValue(DPE_DATE,date("H:i:s"));
    $sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_CHAR,date('N'));
    //$sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_NUMERIC,GetDayNameNew(date_db($tglSekarang))); 
    $sql .= " order by usr_name asc";   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataDokter = $dtaccess->FetchAll($rs);
    //Count total number of rows
    $rowCount =  count($dataDokter);
    echo $sql;
    //Display cities list
    if($rowCount > 0){
        echo '<option value="">Pilih dokter</option>';
        for($i=0,$n=count($dataDokter);$i<$n;$i++){ 
            echo '<option class="form_control" value="'.$dataDokter[$i]["usr_id"].'">'.
                            $dataDokter[$i]["usr_name"].'</option>';
        }
    }else{
        echo '<option value="">Tidak Ada Dokter</option>';
    }
   
   }
   


?>