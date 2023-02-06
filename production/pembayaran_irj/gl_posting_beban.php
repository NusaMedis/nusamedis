<?php
     
     
                           if($_POST["dep_posting_beban"]=='y')
                           {
                      //POsting Biaya
                           //POSTING ke GL
                           
                      //cari yang split-nya ada angkanya
                            $sql = "select a.folsplit_nominal from klinik.klinik_folio_split a
                                   left join klinik.klinik_folio b on a.id_fol = b.fol_id
                                   left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
                                   where c.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and
                                   a.folsplit_nominal > '0'";
                            $rs = $dtaccess->Execute($sql);
                            $postbeban = $dtaccess->Fetch($rs);            
                           
                           
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
                                  
                            if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
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
                        }
                        
?>