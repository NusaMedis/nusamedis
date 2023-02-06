<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
	 
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $depNama = $auth->GetDepNama();
     $plx = new expAJAX("KurangBayar");
 	   
     if(!$auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE) && !$auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE)===1 || $auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_pemeriksaan_view.php";
     $editPage = "kasir_pemeriksaan_proses.php";
     $cicilanPage = "kasir_pemeriksaan_proses_cicilan.php";
     $bayarCicilan = "kasir_pemeriksaan_proses_byr_cicilan.php";
                                                                                       
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
     $_POST["dep_kasir_reg_bayar"] = $konfigurasi["dep_kasir_reg_bayar"];
     $_POST["dep_posting_split"] = $konfigurasi["dep_posting_split"];
     $_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
     $_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];
    
      $table = new InoTable("table","100%","left");
       $skr = date("d-m-Y");
       
       function KurangBayar($id){
          global $dtaccess, $depId, $view, $auth, $table;
          
          $sql = "select * from klinik.klinik_pembayaran where id_cust_usr=".QuoteValue(DPE_CHAR,$id)." 
                  and (pembayaran_flag='k' or pembayaran_flag='p' or pembayaran_total<>pembayaran_yg_dibayar)";
          //return $sql;
          $kurang = $dtaccess->Fetch($sql);
                   
          if($kurang["pembayaran_id"]) {
            return format_date($kurang["pembayaran_tanggal"]);
          }
          else
          {
            return 0;
          }
       }
       
        if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
        if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
        if($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
        if($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];

     if($_POST["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
     if($_POST["cust_usr_nama"])  {
      $sql_where[] = "(UPPER(b.cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%")." 
                      or UPPER(h.fol_keterangan) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%").")";
     }
     if($_POST["reg_jenis_pasien"])  $sql_where[] = "c.reg_jenis_pasien =".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);

     $sql_where[] = "DATE(reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "DATE(reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     
     if($_POST["btnLanjut"] || $_POST["btnExcel"])   
     {   
         //Kalau registrasi bisa langsung bayar maka :
         if($_POST["dep_kasir_reg_bayar"] =='y') $sqlBayarReg="(c.reg_status='M0' or c.reg_status='M1'or c.reg_status='E0' or c.reg_status='A0' or c.reg_status='E1') and ";
         if($_POST["dep_kasir_reg_bayar"] =='n') $sqlBayarReg="(c.reg_status='E0' or c.reg_status='A0' or c.reg_status='E1') and ";

         $sql = "select * from global.global_auth_poli where poli_tipe='P'";
         $rs = $dtaccess->Execute($sql);
         $poliOP = $dtaccess->Fetch($rs);

         if($_POST["dep_kasir_tindakan"] =='n' || !$_POST["dep_kasir_tindakan"] ){
           
                      
                   $sql = "select a.*, c.id_dokter,c.reg_id,c.id_poli,c.reg_tanggal,c.reg_waktu, b.cust_usr_nama, b.cust_usr_kode,e.usr_name, 
                      d.poli_nama,f.jenis_nama,g.tipe_biaya_nama,h.fol_keterangan,h.fol_total_harga,h.fol_lunas,c.reg_keterangan,
    				          c.reg_status, c.reg_jenis_pasien, c.reg_tipe_jkn 
                      from klinik.klinik_pembayaran a 
                      join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                      join klinik.klinik_registrasi c on c.reg_id = a.id_reg
                      left join global.global_auth_poli d on d.poli_id = c.id_poli
                      left join global.global_auth_user e on e.usr_id = c.id_dokter
                      left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
                      left join global.global_tipe_biaya g on c.reg_tipe_layanan = g.tipe_biaya_id
                      left join klinik.klinik_folio h on h.id_pembayaran=a.pembayaran_id
                      where ".$sqlBayarReg." a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and a.pembayaran_flag<>'p'
                      and c.reg_batal is null and (reg_utama='' or reg_utama is null)";
                  if($poliOP) $sql .= " and c.id_poli<>".QuoteValue(DPE_CHAR,$poliOP["poli_id"]);
                  $sql .= " and ".$sql_where." order by a.pembayaran_create asc";
                 //echo $sql;
                      //echo $sql;
    //                    a.pembayaran_flag = 'n' and
    //                   c.reg_status = 'E0' and 
    // SEMENTARA DIHILANGKAN
              $dataTable = $dtaccess->FetchAll($sql);
        } else {
              $sql = "select a.*, c.reg_id,c.id_poli,c.reg_tanggal,c.reg_waktu, b.cust_usr_nama, b.cust_usr_kode,e.usr_name, 
                      d.poli_nama,f.jenis_nama,g.tipe_biaya_nama,h.fol_keterangan, h.fol_total_harga, h.fol_lunas, c.reg_keterangan,
    				          c.reg_status, c.reg_jenis_pasien from klinik.klinik_pembayaran a 
                      join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                      join klinik.klinik_registrasi c on c.reg_id = a.id_reg
                      left join global.global_auth_poli d on d.poli_id = c.id_poli
                      left join global.global_auth_user e on e.usr_id = c.id_dokter
                      left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
                      left join global.global_tipe_biaya g on c.reg_tipe_layanan = g.tipe_biaya_id
                      left join klinik.klinik_folio h on h.id_pembayaran=a.pembayaran_id
                      where ".$sqlBayarReg." a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and a.pembayaran_flag<>'p' 
                      and c.reg_batal is null and (reg_utama='' or reg_utama is null)";
                  if($poliOP) $sql .= " and c.id_poli<>".QuoteValue(DPE_CHAR,$poliOP["poli_id"]);
                  $sql .= " and ".$sql_where." order by a.pembayaran_create asc";
                 
              $dataTable = $dtaccess->FetchAll($sql);
        }
    }
    //echo $sql;
    
    if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=kasir.xls');
     }  
    
    $row = -1;
		for($i=0,$n=count($dataTable);$i<$n;$i++) {
			 
			if($dataTable[$i]["pembayaran_id"]!=$dataTable[$i-1]["pembayaran_id"]) 
      {
				$row++;
				$data[$row] = $dataTable[$i]["reg_id"];	
				$reg[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_id"];
				$biaya[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["id_biaya"]; 
				$nama[$dataTable[$i]["reg_id"]] = $dataTable[$i]["cust_usr_nama"];
        $cust[$dataTable[$i]["reg_id"]] = $dataTable[$i]["id_cust_usr"];
        $kode[$dataTable[$i]["reg_id"]] = $dataTable[$i]["cust_usr_kode"];
				$waktu[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_tanggal"];
				$time[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_waktu"];
				$idfol[$dataTable[$i]["reg_id"]] = $dataTable[$i]["fol_id"];
				$poli[$dataTable[$i]["reg_id"]] = $dataTable[$i]["poli_nama"];
				$departemen[$dataTable[$i]["reg_id"]] = $dataTable[$i]["dep_nama"];
        $dokter[$dataTable[$i]["reg_id"]] = $dataTable[$i]["usr_name"];
        $cicilan[$dataTable[$i]["reg_id"]] = $dataTable[$i]["pembayaran_jenis"];
				$bayar[$dataTable[$i]["reg_id"]] = $dataTable[$i]["jenis_nama"];
        $layanan[$dataTable[$i]["reg_id"]] = $dataTable[$i]["tipe_biaya_nama"];
        $ket[$dataTable[$i]["reg_id"]] = $dataTable[$i]["fol_keterangan"];
        $regket[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_keterangan"];
		    $byr[$dataTable[$i]["reg_id"]] = $dataTable[$i]["pembayaran_id"];
		    $id[$dataTable[$i]["reg_id"]] = $dataTable[$i]["id_dokter"];
        $tot[$dataTable[$i]["reg_id"]] = $dataTable[$i]["fol_total_harga"];
        $lunas[$dataTable[$i]["reg_id"]] = $dataTable[$i]["pembayaran_flag"];
        $status[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_status"];
        $jenis[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_jenis_pasien"];
        $jkn[$dataTable[$i]["reg_id"]] = $dataTable[$i]["reg_tipe_jkn"];
        $pembayaranYgDibayar[$dataTable[$i]["reg_id"]] = $dataTable[$i]["pembayaran_yg_dibayar"];
        $pembayaranTotal[$dataTable[$i]["reg_id"]] = $dataTable[$i]["pembayaran_total"];
        $pembayaranDijamin[$dataTable[$i]["reg_id"]] = $dataTable[$i]["pembayaran_dijamin"];
			}
	}
      
      if($_GET["piutang"]){
        $_POST["pembayaran_id"] = $_GET["pembayaran_id"];
        $_POST["id_jbayar"] = "01";

        $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])."
                and fol_lunas='n'";
        $rs = $dtaccess->Execute($sql);
        $total = $dtaccess->Fetch($rs);
        
        $sql = "select * from klinik.klinik_pembayaran where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPembayaranLama = $dtaccess->Fetch($sql);
        
        $pembayaranHrsBayar = $dataPembayaranLama["pembayaran_hrs_bayar"] + $total["total"];
        
        $sql="update klinik.klinik_pembayaran set pembayaran_flag='p', pembayaran_hrs_bayar=".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaranHrsBayar))." 
              where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $dtaccess->Execute($sql);
      
        $sql = "select * from klinik.klinik_pembayaran where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPembayaranPas = $dtaccess->Fetch($sql);
        
        $selisih = $dataPembayaranPas["pembayaran_total"] - $dataPembayaranPas["pembayaran_yg_dibayar"];
                
        $sql = "select max(pembayaran_det_ke) as total from klinik.klinik_pembayaran_det 
                where id_pembayaran =".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $rs = $dtaccess->Execute($sql);
        $Maxs = $dtaccess->Fetch($rs);
        $MaksUrut = ($Maxs["total"]+1);
              
        $dbTable = "klinik.klinik_pembayaran_det";
        $dbField[0] = "pembayaran_det_id"; // PK
        $dbField[1] = "id_pembayaran";
        $dbField[2] = "pembayaran_det_create";
        $dbField[3] = "pembayaran_det_tgl";
        $dbField[4] = "pembayaran_det_ke";
        $dbField[5] = "pembayaran_det_total";
        $dbField[6] = "id_dep";
        $dbField[7] = "pembayaran_det_service_cash";
        $dbField[8] = "id_dokter";
        $dbField[9] = "who_when_update";
        $dbField[10] = "id_jbayar";
        $dbField[11] = "pembayaran_det_flag";
        $dbField[12] = "pembayaran_det_tipe_piutang";
        $dbField[13] = "pembayaran_det_hrs_bayar";
              
         $byrHonorId = $dtaccess->GetTransID();
         $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
         $dbValue[1] = QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
         $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
         $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
         $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
         if($dataPembayaranPas["pembayaran_total"]<>$dataPembayaranPas["pembayaran_yg_dibayar"]){
         $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($selisih));
         } else {
         $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($total["total"]));
         }
         $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
         $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
         $dbValue[8] = QuoteValue(DPE_CHAR,$_GET["id_dokter"]);
         $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
         $dbValue[10] = QuoteValue(DPE_CHAR,'01');
         $dbValue[11] = QuoteValue(DPE_CHAR,'P');
         $dbValue[12] = QuoteValue(DPE_CHAR,'P');
         $dbValue[13] = QuoteValue(DPE_NUMERIC,$total["total"]);
     
     //print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
         
         $dtmodel->Insert() or die("insert  error");
         
         unset($dbField);
         unset($dtmodel);
         unset($dbValue);
         unset($dbKey);
        
        $sql="select * from klinik.klinik_folio a
            join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
            where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId)."
            and fol_lunas='n'";
		 $rs = $dtaccess->Execute($sql);
     $dataFolioPas = $dtaccess->FetchAll($rs);
     
     $sql="select * from klinik.klinik_registrasi a
     left join global.global_customer_user b on a.id_cust_usr= b.cust_usr_id
     where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataPas = $dtaccess->Fetch($rs);
     
      $dbTable = "gl.gl_buffer_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "ref_tra_urut";
      $dbField[8]  = "id_pembayaran_det";
      $dbField[9]  = "flag_jurnal";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'PENDPOST-%' 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
      
      if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
           $keterangan ="Jurnal Piutang Pendapatan a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
      }else{
           $keterangan ="Jurnal Piutang Pendapatan a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'PENDPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'PE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue);
      

      // update pembayaran detail
      $sqlPembdet = "update klinik.klinik_pembayaran_det set is_posting = 'y' where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$byrHonorId);
      $updatePembdet = $dtaccess->Execute($sqlPembdet);
      
      //--GL POSTING UANG MUKA        
    require_once('gl_uang_muka.php');
    //-- akhir posting UM    


    $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_piutang_perorangan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);              
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($total["total"]));

//print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
                    
      for($m=0,$n=count($dataFolioPas);$m<$n;$m++){

// Pendapatan IRJ
     require('gl_pendapatan_irj.php');

      }

//POsting Biaya
     //POSTING ke GL
     
//cari yang split-nya ada angkanya
      $sql = "select a.folsplit_nominal from klinik.klinik_folio_split a
             left join klinik.klinik_folio b on a.id_fol = b.fol_id
             left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
             where c.id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and
             a.folsplit_nominal > '0'";
      $rs = $dtaccess->Execute($sql);
      $postbeban = $dtaccess->Fetch($rs);            
//       echo $sql; die();
     
     if ($postbeban["folsplit_nominal"]) {
           
      $dbTable = "gl.gl_buffer_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "ref_tra_urut";
      $dbField[8]  = "id_pembayaran_det";
      $dbField[9]  = "flag_jurnal";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
            
      if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Beban Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Beban Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } else {
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Beban a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Beban a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'BEBANPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'BE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 




     //masukkan biaya bebannya
     for($m=0,$n=count($dataFolioPas);$m<$n;$m++){
// Pendapatan IRJ
       require('gl_posting_split.php');
        //--GL POSTING PASIEN UMUM       
       require('gl_posting_beban_umum.php');
             
     }      
      }
        
        $sql="update klinik.klinik_registrasi set reg_bayar='n' where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $dtaccess->Execute($sql);
        
        
        $sql = "update klinik.klinik_folio set id_pembayaran_det=".QuoteValue(DPE_CHAR,$byrHonorId).", fol_lunas='y' 
                where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and fol_lunas='n'";
        $dtaccess->Execute($sql);
        
        $kembali = "kasir_pemeriksaan_view.php?tgl_awal=".QuoteValue(DPE_DATE,$_POST["tgl_awal"])."&tgl_akhir=".QuoteValue(DPE_DATE,$_POST["tgl_akhir"]);
        header("location:".$kembali);
        exit();
      }
  
  
	        $tableHeader = "&nbsp;Antrian Kasir IRJ";
          $counterHeader = 0;

          if(!$_POST["btnExcel"]){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Kurang Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;*/
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Piutang";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
        //if (($userId=='b9ead727d46bc226f23a7c1666c2d9fb') or ($userId=='efa1d87843bd111c3325a649807df7e6')or ($userId=='efa1d87843bd111c3325a649807df7e6'))
		  //{
		      $tbHeader[0][$counterHeader][TABLE_ISI] = "Lihat Sementara";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
         /* $tbHeader[0][$counterHeader][TABLE_ISI] = "Buat Cicilan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++; */
       //}
          }
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
          $counterHeader++; 
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tagihan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          
//           $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
//           $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
//           $counterHeader++;
          
          
      for($i=0,$nomor=1,$n=count($data),$counter=0;$i<$n;$i++,$counter=0) {
          
          $sqlBayar = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$reg[$data[$i]])." 
                        and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataBayar = $dtaccess->Fetch($sqlBayar);
             
          $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$byr[$data[$i]])."
                  and fol_lunas='n'";
          $rs = $dtaccess->Execute($sql);
          $total = $dtaccess->Fetch($rs);   
          
          if($jenis[$data[$i]]=="2"){
           if($_POST["dep_konf_bulat_ribuan"]=="y"){
              $totalint = substr(currency_format($total["total"]),-3);   
              $selisih = 1000-$totalint; 
              if($selisih<>1000){ $_POST["bulat"] = $selisih;} else $_POST["bulat"]=0;
              $totalHarga = $total["total"] + $_POST["bulat"];
           } else{  
              if($_POST["dep_konf_bulat_ratusan"]=="y") { 
                $totalint = substr(currency_format($total["total"]),-2);
                $selisih = 100-$totalint;  
                if($selisih<>100){ $_POST["bulat"] = $selisih;} else $_POST["bulat"]=0; 
                $totalHarga = $total["total"] + $_POST["bulat"];
              } else {
                $totalHarga = $total["total"];
              } 
           }
         }
               
// SEMENTARA DIHILANGKAN			
//      if(!$dataBayar || $dataBayar["pembayaran_flag"]=='n')
//      {	
			    $editPage = "kasir_pemeriksaan_proses.php?id_dokter=".$id[$data[$i]]."&id_reg=".$reg[$data[$i]]."&pembayaran_id=".$byr[$data[$i]];
			    $viewPage = "kasir_lihat_proses.php?id_dokter=".$id[$data[$i]]."&id_reg=".$reg[$data[$i]]."&pembayaran_id=".$byr[$data[$i]];
			    $kurangBayarPage = "kasir_pemeriksaan_kurang_bayar.php?id_dokter=".$id[$data[$i]]."&id_reg=".$reg[$data[$i]]."&pembayaran_id=".$byr[$data[$i]];
          $cicilanPage = "kasir_pemeriksaan_proses_cicilan.php?id_reg=".$reg[$data[$i]]."&pembayaran_id=".$byr[$data[$i]];
				  $bayarCicilan = "kasir_pemeriksaan_proses_byr_cicilan.php?id_reg=".$reg[$data[$i]]."&pembayaran_id=".$byr[$data[$i]];
          $piutangPage = "kasir_pemeriksaan_view.php?piutang=1&id_dokter=".$id[$data[$i]]."&id_reg=".$reg[$data[$i]]."&pembayaran_id=".$byr[$data[$i]];
        
        if($fol[$data[$i]][$i]==STATUS_PEMERIKSAAN)
				$editPage .= "&biaya=".$biaya[$data[$i]][$i];
        
        if(!$_POST["btnExcel"]){  
            if($pembayaranYgDibayar[$data[$i]]<$pembayaranTotal[$data[$i]] && $jenis[$data[$i]]=="2") {
      			$tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
            } else {
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/cash.png" alt="Bayar" title="Bayar" border="0" onclick="javascript: return CekData('.QuoteValue(DPE_CHAR,$cust[$data[$i]]).');" /></a>';               
      			}
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
    			  /*if($pembayaranYgDibayar[$data[$i]]<$pembayaranTotal[$data[$i]] && $jenis[$data[$i]]=="2") {
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$kurangBayarPage.'"><img hspace="2" width="38" height="50" src="'.$ROOT.'gambar/retur.png" alt="Kurang Bayar" title="Kurang Bayar" border="0"/></a>';                
            } else {
      			$tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
      			}
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;*/
          
          if(($lunas[$data[$i]]=='n' || $lunas[$data[$i]]=='') && $jenis[$data[$i]]=='2') {
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$piutangPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/edit.png" alt="Piutang" title="Piutang" border="0" onclick="javascript: return CaraPiutang();" /></a>';               
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
        //if (($userId=='b9ead727d46bc226f23a7c1666c2d9fb') or ($userId=='efa1d87843bd111c3325a649807df7e6')or ($userId=='efa1d87843bd111c3325a649807df7e6'))
		  //{
		      $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$viewPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/search.png" alt="Lihat" title="Lihat" border="0"/></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    //	}	
        }
        	 
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$kode[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			if($kode[$data[$i]]=="100" || $kode[$data[$i]]=="500"){
          if($ket[$data[$i]]=='' || $ket[$data[$i]]==null){          
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$regket[$data[$i]];
          }else{
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$ket[$data[$i]];          
          }
          }else{
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$nama[$data[$i]];
          }
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    //			$tgl = explode(" ", $time[$data[$i]]);
    			$tbContent[$i][$counter][TABLE_ISI] = format_date($waktu[$data[$i]]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
   // 			$waktu = explode(" ", $time[$data[$i]]);
    			$tbContent[$i][$counter][TABLE_ISI] = $time[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = $poli[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $layanan[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $bayar[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          if($jenis[$data[$i]]=='2'){
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalHarga);
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($total["total"]);
          }
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          if($jenis[$data[$i]]=="5" && $jkn[$data[$i]]=='2'){
          $tbContent[$i][$counter][TABLE_ISI] = "JKN Non PBI";
          } elseif($jenis[$data[$i]]=="5" && $jkn[$data[$i]]=='1'){
          $tbContent[$i][$counter][TABLE_ISI] = "JKN PBI";
          } elseif($lunas[$data[$i]]=='n' || $lunas[$data[$i]]==''){
          $tbContent[$i][$counter][TABLE_ISI] = "Belum Lunas";
          } elseif($lunas[$data[$i]]=='k'){
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas (Kurang Bayar)";
          } elseif($lunas[$data[$i]]=='p'){
          $tbContent[$i][$counter][TABLE_ISI] = "Piutang";
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
          }
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          
//  		 		} //END JIKA SUDAH BAYAR SEMENTYARA DIHILANGKAN

          unset($sqlBayar);
          unset($dataBayar);
      } 
      
          //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
    
     // cari jenis pasien e
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs);
	
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("module.css",true,true,"ANTRIAN KASIR IRJ"); ?>
<?php } ?>
<?php echo $view->InitUpload(); ?>
<?php if(!$_POST["btnExcel"]) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<?php } ?>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

<script language="JavaScript">
function CaraPiutang() {
  if(confirm('apakah anda yakin akan mem- PIUTANG -kan transaksi ini???'));
  else return false;
}
</script>

<script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '60%',
'height' : '110%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
});

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
//     $next = "kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];

<?php if($cetak=="y"){ ?>
//    if(confirm('Cetak Invoice?')) 
       BukaWindow('tutup_kasir_cetak.php?tgl_awal=<?php echo $_POST["tanggal_awal"];?>&tgl_akhir=<?php echo $_POST["tanggal_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&js_biaya=<?php echo $_POST["js_biaya"];?>&jbayar=<?php echo $_POST["jbayar"]?>', '_blank');
	 document.location.href='<?php echo $thisPage;?>';
<?php } ?> 

</script>

<script type="text/javascript" language="JavaScript">
<? $plx->Run(); ?>

var mTimer;
function CekData(id){
var hasil;
  //alert(id);
  hasil=KurangBayar(id,'type=r');
  if (hasil != 0)
  {
     if(id <> '500' || id <> '100'){
     alert('Pasien ada tunggakan tanggal '+ hasil);
     return false;
     }
  } else
  {
    return true;
  }
  //SetPerawatan(id,loket,'type=r');
}


</script>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<?php if(!$_POST["btnExcel"]) { ?>
<div id="body">
<br />
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border="1" cellpadding=2 cellspacing=1 width="100%" class="smallheader" id="tblSearching">
    <tr >
          <td width="10%" class="tablecontent">&nbsp;Periode Tanggal&nbsp;:</td>
          <td width="30%" class="tablecontent">
               <input type="text" id="tanggal_awal" name="tanggal_awal" size="15" maxlength="10" value="<?php echo $_POST["tanggal_awal"];?>"/>
               <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;(dd-mm-yyy)&nbsp;
               -
               <input type="text" id="tanggal_akhir" name="tanggal_akhir" size="15" maxlength="10" value="<?php echo $_POST["tanggal_akhir"];?>"/>
               <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;(dd-mm-yyy)&nbsp;    
       </td>
    </tr>
    	<tr>
				<td width="10%" class="tablecontent" width="30%">Nama Pasien</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
				</td>
			</tr>
			<tr>
				<td width="10%" class="tablecontent" width="30%">No. RM</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
				</td>
			</tr>
	    	<script>document.frmFind.cust_usr_kode.focus();</script>
      <tr>
				<td width="10%" class="tablecontent" width="30%">Cara Bayar</td>
				<td class="tablecontent">
					<select name="reg_jenis_pasien" id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          <option value="" >[ Pilih Cara Bayar ]</option>
          <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
          <option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["reg_jenis_pasien"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
				  <?php } ?>
			    </select>
				</td>
			</tr>
    <tr>
          <td class="tablecontent" >&nbsp;</td>
          <td class="tablecontent-odd" colspan="5"><input type="submit" name="btnLanjut" value="Lanjut" class="submit">
          <input type="submit" name="btnExcel" value="Export Excel" class="submit"></td>
		</td><!--<td class="tablecontent" align="right"><input type="submit" name="btnTutup" value="Tutup" class="submit"></td>-->
     </tr>
</table>
<input type="hidden" name="dep_konf_bulat_ratusan" value="<?php echo $_POST["dep_konf_bulat_ratusan"]; ?>"/>
<input type="hidden" name="dep_konf_bulat_ribuan" value="<?php echo $_POST["dep_konf_bulat_ribuan"]; ?>"/>
</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>

<?php if($_POST["btnExcel"]) {?>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="10">
               <strong>ANTRIAN KASIR IRJ<br/>
               <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
               <br/>
               </strong>
               </td>          
          </tr>
         <tr class="tableheader">
          <td align="left" colspan="10">
          <?php //echo $poliNama; ?><br/>
          <?php if($_POST["tanggal_awal"]==$_POST["tanggal_akhir"]) { echo "Tanggal : ".$_POST["tanggal_awal"]; } elseif($_POST["tanggal_awal"]!=$_POST["tanggal_akhir"]) { echo "Periode : ".$_POST["tanggal_awal"]." s/d ".$_POST["tanggal_akhir"]; }  ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php //echo "Nama Pasien : ".$_POST["cust_usr_nama"]; ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php if($_POST["reg_jenis_pasien"]){ echo "Jenis Pasien : ".$_POST["reg_jenis_pasien"];} ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php //echo "Nama Poli : ".$dataPoli[$i]["poli_nama"]; ?>
                        
               <br/>
          </td>
          </tr>
     </table>
<?php }?>
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
<?php if(!$_POST["btnExcel"]) {?>
<br /><br />
</div>
</div>

<?php } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
<input type="hidden" name="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"]; ?>"/>
</form>
<br /><br /><br /><br />
</div>

  		<!--<table  width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>-->
      <?php if(!$_POST["btnExcel"]) { ?>
<?php if($konfigurasi["dep_konf_dento"]=='y') { ;?>
<!--------Buat Helpicon----------->
<script type="text/javascript">
function showHideGB(){
var gb = document.getElementById("gb");
var w = gb.offsetWidth;
gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
gb.opened = !gb.opened;
}
function moveGB(x0, xf){
var gb = document.getElementById("gb");
var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
<a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
<a rel="sepur" href="<?php echo $ROOT;?>demo/pembayaran_kasir.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>

