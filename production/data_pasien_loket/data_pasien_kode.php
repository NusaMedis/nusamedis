<?php
      // No. RM Otomatis
      DEFINE(TIPE_NO_RM_SEQUENTIAL,'1');
      DEFINE(TIPE_NO_RM_ALFABETIC,'2');
      DEFINE(TIPE_NO_RM_CUSTOM1,'3');
      DEFINE(TIPE_NO_RM_CUSTOM2,'4');
      
       $sql = "select dep_panjang_kode_pasien,dep_tipe_no_rm,dep_jml_nol_depan from 
       global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
      
       $panjangKodePasien = $dtaccess->Fetch($sql);  
       
       $_POST["dep_tipe_no_rm"] = $panjangKodePasien["dep_tipe_no_rm"];
       $_POST["dep_jml_nol_depan"]  = $panjangKodePasien["dep_jml_nol_depan"];
			//die($panjangKodePasien["dep_jml_nol_depan"]);
       if($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_SEQUENTIAL)
       {                   
         $sqle = "select max(CAST (cust_usr_kode as integer)) as kode from 
         global.global_customer_user where cust_usr_kode<>'100' and cust_usr_kode<>'500' and cust_usr_kode<>'501'";
         $lastKodeNonHuruf = $dtaccess->Fetch($sqle);
         //diconvert dulu ke Integer
         $lastKodeNonHuruf["kode"] = intval($lastKodeNonHuruf["kode"]);     
//echo $sqle;		 
       }
       elseif($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_ALFABETIC)
       {
        $hurufNama = substr($_POST["cust_usr_nama"],0,1); 
        $sql = "select cust_usr_huruf_urut as kode from global.global_customer_user 
                 where cust_usr_huruf = ".QuoteValue(DPE_CHAR,strtoupper($hurufNama))."
                 order by kode desc";
        $rs = $dtaccess->Execute($sql);
        $lastKodeNonHuruf = $dtaccess->Fetch($rs); 
               
       }
       else //TIPE_NO_RM_CUSTOM1 & TIPE_NO_RM_CUSTOM2
       {
         $tahunrm = substr(date('Y'),-2);
                   
         $sql = "select cust_usr_rak_rm as rak from global.global_customer_user 
                 where cust_usr_tahun_rm = ".QuoteValue(DPE_CHAR,$tahunrm)."
                 order by rak desc";
         $rs = $dtaccess->Execute($sql);
         $rakrm = $dtaccess->Fetch($rs);
    
         $_POST["cust_usr_tahun_rm"] = $tahunrm;
                           
         if(!$rakrm){ 
         $_POST["cust_usr_rak_rm"] = '1';
         $_POST["cust_usr_urut_rm"] = '1';         
    
         }else{
                   
         $sql = "select cust_usr_urut_rm as rm_urut from global.global_customer_user 
                 where cust_usr_tahun_rm = ".QuoteValue(DPE_CHAR,$tahunrm)."
                 and cust_usr_rak_rm = ".QuoteValue(DPE_CHAR,$rakrm["rak"])."
                 order by rm_urut desc";
         $rs = $dtaccess->Execute($sql);
         $rmurut = $dtaccess->Fetch($rs);
         
           if($rmurut["rm_urut"]=='99')
           {
             $_POST["cust_usr_urut_rm"] = '1';
             $_POST["cust_usr_rak_rm"] = $rakrm["rak"]+1;
            }else{           
            $_POST["cust_usr_urut_rm"] = $rmurut["rm_urut"]+1;
            $_POST["cust_usr_rak_rm"] = $rakrm["rak"];
           }         
         }
       }
       
       //INSERT KODE PASIEN KE DATABASE
       if($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_SEQUENTIAL)
       {
         if($_POST["dep_jml_nol_depan"]<>0){
              $_POST["kode_pasien"] = str_pad($lastKodeNonHuruf["kode"]+1,$panjangKodePasien["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);
         } else {
            $_POST["kode_pasien"] = $lastKodeNonHuruf["kode"]+1;
         }
       }
       elseif($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_ALFABETIC)
       {
                  
        $_POST["kode_pasien"] = $hurufNama."".str_pad($lastKodeNonHuruf["kode"]+1,$panjangKodePasien["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);
       
       }
       elseif($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM1)
       {
        
        $_POST["kode_pasien"] = str_pad($_POST["cust_usr_rak_rm"],2,"0",STR_PAD_LEFT)."".str_pad($_POST["cust_usr_urut_rm"],2,"0",STR_PAD_LEFT)."".$tahunrm;
       
       }
       else //TIPE_NO_RM_CUSTOM2
       {
                       
        $_POST["kode_pasien"] = str_pad($_POST["cust_usr_urut_rm"],2,"0",STR_PAD_LEFT)."".str_pad($_POST["cust_usr_rak_rm"],2,"0",STR_PAD_LEFT)."".$tahunrm;         
    
       }
       
       
       
       //Masukkan comma diantara 2 digit dipaten
       $arr = str_split($_POST["kode_pasien"],"2");
       $_POST["cust_usr_kode_tampilan"] = implode(".",$arr);
       
       //NILAI CUST_USR _KODE
      // $_POST["cust_usr_kode"] = $_POST["kode_pasien"];

        //INSERT ID dan KODE   (KALAU KODE PASIEN NYA di depan
        /*
        $dbTable = "global.global_customer_user";
    
         $dbField[0] = "cust_usr_id";
         $dbField[1] = "cust_usr_kode";
         $dbField[2] = "cust_usr_kosong";
         $dbField[3] = "cust_usr_when_update";
         $dbField[4] = "is_lock";
         if($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM1 || $_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM2){
         $dbField[5] = "cust_usr_urut_rm";           
         $dbField[6] = "cust_usr_rak_rm";           
         $dbField[7] = "cust_usr_tahun_rm";                                 
         }
         
         $_POST["cust_usr_id"] = $dtaccess->GetTransID("global.global_customer_user","cust_usr_id");
         
         $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kode_pasien"]);
         $dbValue[2] = QuoteValue(DPE_CHAR,'y');
         $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
         $dbValue[4] = QuoteValue(DPE_CHAR,'y');          
         if($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM1 || $_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM2){
         $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_urut_rm"]);           
         $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_rak_rm"]);         
         $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_tahun_rm"]);                                
         }
         //print_r($dbValue);
         //die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
           $dtmodel->Insert() or die("insert  error");
         
         unset($dbField);
         unset($dtmodel);
         unset($dbValue);
         unset($dbKey);
         // END INSERT PASIEN UNTUK KODE PASIEN
        */
?>