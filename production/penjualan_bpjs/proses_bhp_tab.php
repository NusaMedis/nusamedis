<?php
//LIBRARY
    require_once("../penghubung.inc.php");
    require_once($LIB."login.php");
    require_once($LIB."encrypt.php");
    require_once($LIB."datamodel.php");
    require_once($LIB."dateLib.php");
    require_once($LIB."currency.php");
    require_once($LIB."expAJAX.php");
    require_once($LIB."tampilan.php");

//INISIALISAI AWAL LIBRARY
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
    $skr = date("Y-m-d");
    $time = date("H:i:s");
    $usrId = $auth->GetUserId();
    $table = new InoTable("table","100%","left");
    $depId = $auth->GetDepId();
    $userName = $auth->GetUserName();





/* regist apotik */

    $sql="select id_cust_usr,id_poli from klinik.klinik_registrasi where reg_id='$_POST[id_reg]'";
    $dataCust = $dtaccess->Fetch($sql);


    $sql="select * from klinik.klinik_registrasi where reg_status ='A7' and id_cust_usr=".QuoteValue(DPE_CHAR,$dataCust['id_cust_usr']);
    $dataBhpTab = $dtaccess->Fetch($sql);
   



     /* jika data bhp tidak ada maka register */
    if(!$dataBhpTab['reg_id']){
        $_POST['id_reg_lama'] = $_POST['id_reg'];
         require_once('tab_bhp/proses_registrasi_apotik.php');
        $_POST["id_reg"] = $regId;

    }else{

      $_POST['id_reg'] = $dataBhpTab['reg_id'];
    }


    $sql = "select a.*,f.*, c.reg_jenis_pasien , c.reg_status , c.reg_tanggal, c.reg_id, c.id_poli, d.rawat_terapi, c.id_dokter,g.jenis_nama, h.jkn_nama, i.perusahaan_nama,
            c.id_pembayaran, k.id_gudang from global.global_customer_user a
            left join klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id
            left join klinik.klinik_perawatan d on d.id_reg = c.reg_utama
            left join global.global_auth_user f on f.usr_id = c.id_dokter
            left join global.global_jenis_pasien g on c.reg_jenis_pasien = g.jenis_id
            left join global.global_jkn h on c.reg_tipe_jkn = h.jkn_id
            left join global.global_perusahaan i on c.id_perusahaan = i.perusahaan_id
            left join global.global_auth_poli k on k.poli_id = c.id_poli
            where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." order by c.reg_tanggal desc,c.reg_waktu desc";
    $dataPasien = $dtaccess->Fetch($sql);

  /* CARI ID Gudang */
    $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$dataCust['id_poli']);
    $rs = $dtaccess->Execute($sql);
    $gudang = $dtaccess->Fetch($rs);
    $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
    
  /* nomer nota penjualan */
    $sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =".QuoteValue(DPE_CHAR,$depId)." and penjualan_flag = 'D'";
    $lastKode = $dtaccess->Fetch($sql);
    $tgl = explode("-",$skr);
    $_POST["penjualan_no"] = "APRJ".str_pad($lastKode["urut"]+1,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
    $_POST["hidUrut"] = $lastKode["urut"]+1;

    $sql="select * from apotik.apotik_penjualan where id_reg='$_POST[id_reg]'";
    $ApJual=$dtaccess->Fetch($sql);
  /* insert Apotik penjualan */
  if(!$ApJual['penjualan_id']){
          $dbTable = "apotik.apotik_penjualan";
          $dbField[0]  = "penjualan_id";   // PK
          $dbField[1]  = "penjualan_nomor";
          $dbField[2]  = "penjualan_urut";
          $dbField[3]  = "id_cust_usr";
          $dbField[4]  = "cust_usr_nama";
          $dbField[5]  = "id_jenis_pasien";
          $dbField[6]  = "penjualan_flag";
          $dbField[7]  = "penjualan_create";
          $dbField[8]  = "who_update";
          $dbField[9]  = "id_gudang";
          $dbField[10]  = "id_dokter";
          $dbField[11]  = "dokter_nama";
          $dbField[12]  = "id_dep";
          $dbField[13]  = "id_reg";
          
          $penjualanId = $dtaccess->GetTransID();

          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
          $dbValue[2] = QuoteValue(DPE_NUMERIC,1);
          $dbValue[3] = QuoteValue(DPE_CHAR,$dataPasien["cust_usr_id"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$dataPasien["cust_usr_nama"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,$dataPasien["reg_jenis_pasien"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,'D');
          $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[8] = QuoteValue(DPE_CHAR,$usrId);
          $dbValue[9] = QuoteValue(DPE_CHAR,$theDep);
          $dbValue[10] = QuoteValue(DPE_CHAR,$dataPasien["id_dokter"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$dataPasien["usr_name"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          $dtmodel->Insert() or die("insert  error");
          echo "insert apotik penjualan sukses <br>";

          unset($dbField);
          unset($dbValue);
  }else{
    $penjualanId = $ApJual['penjualan_id'];
  }
  
  /* Insert apotek Detail */

      $sql = "select item_nama from logistik.logistik_item where item_id='$_POST[item_id]'";
      $obat = $dtaccess->Fetch($sql);

      $sql = "select * from apotik.apotik_penjualan_detail where id_item ='$_POST[item_id]' and id_penjualan=".QuoteValue(DPE_CHAR,$ApJual['penjualan_id']);
      $ApJualDet = $dtaccess->Fetch($sql);

      
      $dateSekarang = date('Y-m-d H:i:s');
      $dbTable = "apotik.apotik_penjualan_detail";
      $dbField[0]  = "penjualan_detail_id";   // PK
      $dbField[1]  = "id_penjualan";
      $dbField[2]  = "id_item";
      $dbField[3]  = "penjualan_detail_harga_jual";
      $dbField[4]  = "penjualan_detail_jumlah";
      $dbField[5]  = "penjualan_detail_total";
      $dbField[6]  = "penjualan_detail_flag";
      $dbField[7]  = "penjualan_detail_create";
      $dbField[8]  = "id_petunjuk";
      $dbField[9]  = "id_dep";
      $dbField[10]  = "penjualan_detail_sisa";
      $dbField[11]  = "id_batch";
      $dbField[12]  = "penjualan_detail_tuslag";
      $dbField[13]  = "penjualan_detail_dosis_obat";
      $dbField[14]  = "id_aturan_minum";
      $dbField[15]  = "id_aturan_pakai";
      $dbField[16]  = "item_nama";
      $dbField[17]  = "id_jam_aturan_pakai";
       
        if(!$ApJualDet['id_penjualan']){
          $penjualanDetailId = $dtaccess->GetTransID();
        }else{
          $penjualanDetailId = $ApJualDet['id_penjualan'];
        }
          $total=$_POST["biaya"] * $_POST["fol_jumlah"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya"]));
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["fol_jumlah"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($total));
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["fol_Jumlah"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["penjualan_detail_dosis_obat"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_aturan_minum"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_aturan_pakai"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$obat["item_nama"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["id_jam_aturan_pakai"]);

        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        if(!$ApJualDet['id_penjualan']){
          $dtmodel->Insert() or die("insert  error");
          echo "insert apotik penjualan sukses detail <br>";
        }else{
          $dtmodel->Update() or die("insert  error");
          echo "Update apotik penjualan sukses detail <br>";
        }
       

        unset($dbTable);
        unset($dbField);
        unset($dbValue);
        unset($dbKey); 

 /* Cari jml Stok di gudang */
      

      // $sql="select * from logistik.logistik_stok_item where id_item='$_POST[item_id]' and id_penjualan=".QuoteValue(DPE_CHAR,$ApJual['penjualan_id']);
      // $LogStok = $dtaccess->Fetch($sql);

      // $sql = "select stok_dep_saldo from logistik.logistik_stok_dep 
      // where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
      // $stok= $dtaccess->Fetch($sql); 

      // if(!$LogStok['stok_item_id']){
      //   $sisa_stok = $stok['stok_dep_saldo'] - $_POST["fol_jumlah"] ;
      // }else{
      //   $sisa_stok = $stok['stok_dep_saldo'] - $_POST["fol_jumlah"] +$LogStok['stok_item_jumlah'] ;
      // }
      
    
      $sql = "select item_harga_beli from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$_POST["item_id"]);
      $rs = $dtaccess->Execute($sql);
      $dataHargabeli = $dtaccess->Fetch($rs);

  /* insert data logistik */
    
  
    $dbTable = "logistik.logistik_stok_item";
    $dbField[0]  = "stok_item_id";   // PK
    $dbField[1]  = "stok_item_jumlah";
    $dbField[2]  = "id_item";    
    $dbField[3]  = "id_gudang";
    $dbField[4]  = "stok_item_flag";
    $dbField[5]  = "stok_item_create";         
    $dbField[6]  = "stok_item_saldo";
    $dbField[7]  = "id_dep";
    $dbField[8]  = "stok_item_keterangan";
    $dbField[9]  = "id_penjualan";
    $dbField[10]  = "stok_item_hpp";
    $dbField[11]  = "stok_item_hna";
    $dbField[12]  = "stok_item_hna_ppn_minus_diskon";
    
    $date = date("Y-m-d H:i:s");
    if(!$LogStok['stok_item_id']){
      $stokid = $dtaccess->GetTransID();
    }else{
      $stokid = $LogStok['stok_item_id'];
    }
    
    
    $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
    $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["fol_jumlah"]);  
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
    $dbValue[4] = QuoteValue(DPE_CHAR,'P');
    $dbValue[5] = QuoteValue(DPE_DATE,$date);
    $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($sisa_stok)); 
    $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
    $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
    $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya"]));
    $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
    $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
    
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
    if(!$LogStok['stok_item_id']){
      $dtmodel->Insert() or die("insert  error"); 
    echo "insert logistik item sukses <br>";
    }else{
      $dtmodel->Update() or die("Update  error"); 
      echo "Update logistik item sukses <br>";
    }

    unset($dbTable);
    unset($dbField);
    unset($dbValue);
    unset($dbKey); 
  

/* update stok dep */
    // $sql = "update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($sisa_stok)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))."
    // where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
    // $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    // echo "update stok dep sukses <br>";

/* Insert / Update  Folio Tindakan */    

    // $sql="select * from klinik.klinik_folio where id_reg='$_POST[id_reg]'";
    // $FolBhp = $dtaccess->Fetch($sql);
    // if(!$FolBhp['fol_id']){
    //   $FolBhp['fol_nominal'] = 0;
    // }

    // $totalobat =  $FolBhp['fol_nominal'] + $total;
    
    
    // $dbTable = "klinik.klinik_folio";
    // $dbField[0] = "fol_id";   // PK
    // if(!$FolBhp['fol_id']){
    // $dbField[1] = "id_reg";
    // $dbField[2] = "fol_nama";
    // $dbField[3] = "fol_dibayar_when";
    // $dbField[4] = "fol_jenis";
    // $dbField[5] = "id_cust_usr";
    // $dbField[6] = "fol_waktu";
    // $dbField[7] = "fol_lunas";
    // $dbField[8] = "id_biaya";                   
    // $dbField[9] = "id_poli";
    // $dbField[10] = "fol_jenis_pasien";
    // $dbField[11] = "id_dep";
    // $dbField[12] = "id_pembayaran"; 
    // $dbField[13] = "fol_nominal";
    // $dbField[14] = "fol_dibayar";
    // $dbField[15] = "fol_total_harga";
    // $dbField[16] = "fol_jumlah";
    // $dbField[17] = "fol_catatan";
    // $dbField[18] = "fol_nominal_satuan";
    // $dbField[19] = "fol_hrs_bayar"; 
    // }else{
    // $dbField[1] = "fol_nominal";
    // $dbField[2] = "fol_dibayar";
    // $dbField[3] = "fol_total_harga";
    // $dbField[4] = "fol_jumlah";
    // $dbField[5] = "fol_catatan";
    // $dbField[6] = "fol_nominal_satuan";
    // $dbField[7] = "fol_hrs_bayar";
    // }          
    

    // if(!$FolBhp['fol_id']){
    //   $folId =  $dtaccess->GetTransID();
    // }else{
    //   $folId =  $FolBhp['fol_id'] ;
    // }
       
    
    //   $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
    //   if(!$FolBhp['fol_id']){
    //   $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
    //   $dbValue[2] = QuoteValue(DPE_CHAR,'Penjualan Obat');
    //   $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
    //   $dbValue[4] = QuoteValue(DPE_CHAR,'13');
    //   $dbValue[5] = QuoteValue(DPE_CHARKEY,$regLama["cust_usr_id"]);
    //   $dbValue[6] = QuoteValue(DPE_DATE,$date);
    //   $dbValue[7] = QuoteValue(DPE_CHARKEY,'n');
    //   $dbValue[8] = QuoteValue(DPE_CHAR,'9999999');
    //   $dbValue[9] = QuoteValue(DPE_CHARKEY,$_POST["id_poli"]);
    //   $dbValue[10] = QuoteValue(DPE_NUMERIC,$regLama["reg_jenis_pasien"]);
    //   $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
    //   $dbValue[12] = QuoteValue(DPE_CHAR,$regLama["id_pembayaran"]);
    //   $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[16] = QuoteValue(DPE_NUMERIC,1);
    //   $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
    //   $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));   
    //   }else{
    //   $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[4] = QuoteValue(DPE_NUMERIC,1);
    //   $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
    //   $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));
    //   $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($totalobat));  

    //   }

    //   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    //   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

    //   if(!$FolBhp['fol_id']){
    //     $dtmodel->Insert() or die("insert  error");
    //     echo "insert folio sukses $folId </br> ";
    //   }else{
    //     $dtmodel->Update() or die("Update  error");
    //     echo "Update folio sukses $folId </br> ";
    //   }
      
    //   unset($dbField);
    //   unset($dtmodel);
    //   unset($dbValue);
    //   unset($dbKey);   

/* update pemabayaran */
    $sqlpemb = "select id_pembayaran from klinik.klinik_registrasi
              where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $idPemb = $dtaccess->Fetch($sqlpemb);

    $sql  ="update klinik.klinik_registrasi set reg_obat='y'
            where id_pembayaran=".QuoteValue(DPE_CHARKEY,$regLama["id_pembayaran"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql); 

    /* UPDATE PEMBAYARAN TOTAL */
    $sql = "SELECT sum(fol_nominal) AS total FROM klinik.klinik_folio WHERE id_pembayaran = ".QuoteValue(DPE_CHAR, $idPemb["id_pembayaran"]);
    $dataTotal = $dtaccess->Fetch($sql);

    $sql = "UPDATE klinik.klinik_pembayaran SET pembayaran_total = ".QuoteValue(DPE_NUMERIC, $dataTotal['total'])." WHERE pembayaran_id = ".QuoteValue(DPE_CHAR, $idPemb["id_pembayaran"]);
    $dtaccess->Execute($sql);

    if(!$FolBhp['fol_id']){
  /* update fol id di apotik penjualan */
     $sql = "update apotik.apotik_penjualan set id_fol=".QuoteValue(DPE_CHAR,$folId)." 
     where penjualan_id=".QuoteValue(DPE_CHAR,$penjualanId);
     $rs = $dtaccess->Execute($sql);
    }