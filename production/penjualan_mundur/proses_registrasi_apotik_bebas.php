<?php
//INSERT KLINIK REGISTRASI            
                $dbTable = "klinik.klinik_registrasi";
           
                $dbField[0] = "reg_id";   // PK
                $dbField[1] = "reg_tanggal";
                $dbField[2] = "reg_waktu";
                $dbField[3] = "id_cust_usr";
                $dbField[4] = "reg_status";
                $dbField[5] = "reg_who_update";
                $dbField[6] = "reg_when_update";
                $dbField[7] = "id_dep";
               // $dbField[8] = "id_pembayaran";
                $dbField[8] = "reg_jenis_pasien";
                $dbField[9] = "id_poli";
                $dbField[10] = "reg_tipe_rawat";
                $dbField[11] = "reg_tracer";
                $dbField[12] = "reg_tracer_riwayat";
                $dbField[13] = "reg_tracer_barcode";
                $dbField[14] = "reg_tracer_barcode_besar";
                $dbField[15] = "reg_tracer_registrasi";
                $dbField[16] = "reg_keterangan";
                $dbField[17] = "id_poli_asal";

                $status = 'A0';  // Status Apotik --
                $regId = $dtaccess->GetTransID();      
                $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
                $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
                $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
                $dbValue[3] = QuoteValue(DPE_CHAR,'100');//DIPATEN 100 untuk Penjualan Obat dari Luar
                $dbValue[4] = QuoteValue(DPE_CHAR,$status);
                $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
                $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                //$dbValue[8] = QuoteValue(DPE_CHAR,$byrId);
                $dbValue[8] = QuoteValue(DPE_CHAR,'2');
                $dbValue[9] = QuoteValue(DPE_CHAR,$poli);
                $dbValue[10] = QuoteValue(DPE_CHAR,'J');
                $dbValue[11] = QuoteValue(DPE_CHAR,'y');
                $dbValue[12] = QuoteValue(DPE_CHAR,'y');
                $dbValue[13] = QuoteValue(DPE_CHAR,'y');
                $dbValue[14] = QuoteValue(DPE_CHAR,'y');
                $dbValue[15] = QuoteValue(DPE_CHAR,'y');
			    $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]." (".$_POST["penjualan_alamat"].")");
                $dbValue[17] = QuoteValue(DPE_CHAR,$poli);

                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                
                $dtmodel->Insert() or die("insert error"); 

                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);

         
          // Insert Biaya Pembayaran //
              $dbTable = "klinik.klinik_pembayaran";
              $dbField[0] = "pembayaran_id";   // PK
              $dbField[1] = "pembayaran_create";
              $dbField[2] = "pembayaran_who_create";
              $dbField[3] = "pembayaran_tanggal";
              $dbField[4] = "id_reg";
              $dbField[5] = "id_cust_usr";
              $dbField[6] = "pembayaran_total";
              $dbField[7] = "id_dep";
              $dbField[8] = "pembayaran_flag";
              $dbField[9] = "pembayaran_yg_dibayar";
              
               $byrId = $dtaccess->GetTransID();

               $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrId);
               $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
               $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
               $dbValue[4] = QuoteValue(DPE_CHAR,$regId);
               $dbValue[5] = QuoteValue(DPE_CHAR,'100');
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$beaNominale);
               $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[8] = QuoteValue(DPE_CHAR,'n');
               $dbValue[9] = QuoteValue(DPE_NUMERIC,'0.00');
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               
               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);

           $sql = "update klinik.klinik_registrasi set id_pembayaran = ".
           QuoteValue(DPE_CHAR,$byrId)." where reg_id = ".
           QuoteValue(DPE_CHAR,$regId);
           $rs = $dtaccess->Execute($sql);

                 // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
          $dbField[8] = "id_waktu_tunggu_status";
            
          $waktuTungguId = $dtaccess->GetTransID(); 
             
          $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'100');
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$status);
          $dbValue[6] = QuoteValue(DPE_CHAR,"Pembelian / Input Data Farmasi");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$status);
          
                
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");    

         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);   

          
 ?>