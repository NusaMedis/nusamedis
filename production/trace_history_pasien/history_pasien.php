<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "edit_input_rm.php";
     $poliId = $auth->IdPoli();
     
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }
      if($_GET["tgl_awal"]) $_POST["tgl_awal"]=$_GET["tgl_awal"];
      if($_GET["tgl_akhir"]) $_POST["tgl_akhir"]=$_GET["tgl_akhir"];
    
 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
    
     //untuk mencari tanggal	 
  	 if(!empty($_POST["cust_usr_nama"])){
  		$sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%'";
  	 }
  	 
  	 if(!empty($_POST["cust_usr_kode"])){
  		$sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
  	 }
  
  	 if(!empty($_POST["cust_usr_tanggallahir"])){
  		$sql_where[] = " b.cust_usr_tanggal_lahir = ".QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggallahir"]));
  	 }
       if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
       
     if($_POST["btnLanjut"])   
     {     
      $sql = "select b.cust_usr_kode, d.rawat_id, a.hak_kelas_inap, i.tipe_biaya_nama, c.*, h.jenis_nama, e.jkn_nama,c.inacbg_no_sep,j.poli_nama,k.usr_name as coder
			         from klinik.klinik_inacbg c 
               join global.global_customer_user b on c.id_cust_usr = b.cust_usr_id
               left join klinik.klinik_registrasi a on a.reg_id=c.id_reg
               left join global.global_jkn e on e.jkn_id = b.cust_usr_jkn
			         left join global.global_perusahaan f on f.perusahaan_id = a.id_perusahaan
			         left join global.global_jamkesda_kota g on g.jamkesda_kota_id = a.id_jamkesda_kota
			         left join global.global_jenis_pasien h on h.jenis_id = a.reg_jenis_pasien
				     left join global.global_tipe_biaya i on i.tipe_biaya_id = a.reg_tipe_layanan
					 left join klinik.klinik_perawatan d on d.id_reg = a.reg_id
           left join global.global_auth_poli j on j.poli_id = a.id_poli
           left join global.global_auth_user k on k.usr_id = c.inacbg_who_update";
     $sql.= " where  1=1 ";
     $sql.= " and a.reg_icd <>'n' and c.inacbg_appv='n' ";
     if($sql_where) $sql .= " and ".$sql_where;
     $sql.= " order by b.cust_usr_kode asc, c.inacbg_tanggal_masuk asc, b.cust_usr_nama asc";
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
   } 

     $tableHeader = "&nbsp;History Pasien";
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_bridging.xls');
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

?>



<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
		<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
		<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->
		<!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Rekam Medik</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?php echo $tableHeader;?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">    
			
                        	     			 
				
						
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
						<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
						</div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. Medrec</label>
						<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
				</div>		   
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tanggal Lahir(DD-MM-YYYY)</label>
            <div class='input-group date' id='datepicker2'>
              <input  id="cust_usr_tanggallahir" name="cust_usr_tanggallahir"  type='text' class="form-control" value="<?php echo $_POST["cust_usr_tanggallahir"]; ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>
				
          				</div>	    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
					 <input type="submit" name="btnLanjut" value="Lanjut" class="submit">
              </div>							    
					<div class="clearfix"></div>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table  class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>                     
                               <th class="column-title">No</th>
                                <th class="column-title">Tanggal Masuk</th>
                                <th class="column-title">Tanggal Keluar</th>                       
                               <th class="column-title">No RM</th>                     
                               <th class="column-title">Nama</th>
                               <th class="column-title">Cara Bayar</th>                       
                               <th class="column-title">Diagnosa Utama</th>
                               <th class="column-title">Dokter</th> 
                               <th class="column-title">Coder</th> 
                               <th class="column-title">Klinik</th>  
                               <th class="column-title">Instalasi</th>  
                               
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
                          
                          <tr class="even pointer">
                            <td class=" "><?php echo $i+1?></td>
                            <td class=" "><?php echo date_db($dataTable[$i]["inacbg_tanggal_masuk"])?></td>
                            <td class=" "><?php echo date_db($dataTable[$i]["inacbg_tanggal_keluar"])?></td>
                            <td class=" "><?php echo $dataTable[$i]["cust_usr_kode"]?></td>
                            <td class=" "><?php echo $dataTable[$i]["inacbg_pasien_nama"]?></td>
                             <? if($dataTable[$i]["jenis_id"]=='5'){ ?>
                            <td class=" "><?php echo $dataTable[$i]["jenis_nama"]."-".$dataTable[$i]["jkn_nama"]?></td>
                            <? } else{ ?>
                            <td class=" "><?php echo $dataTable[$i]["jenis_nama"]?></td>
                            <? } ?>
                            <?
                                $sql = "select rawat_icd_kode from klinik.klinik_perawatan_icd
                                      where id_inacbg = ".QuoteValue(DPE_CHAR,$dataTable[$i]["inacbg_id"])."
                                      and rawat_icd_urut = ".QuoteValue(DPE_CHAR,1);
                                $rs = $dtaccess->Execute($sql);
                                $dataInaIcd = $dtaccess->Fetch($rs);?>
                            <td class=" "><?php echo $dataInaIcd["rawat_icd_kode"]?></td>
                            <td class=" "><?php echo $dataTable[$i]["inacbg_dokter"]?></td>
                            <td class=" "><?php echo $dataTable[$i]["coder"]?></td>
                            <td class=" "><?php echo $dataTable[$i]["poli_nama"]?></td>
                            <? if($dataTable[$i]["inacbg_jenis_pasien"]=='1'){
                              $layanan = "Rawat Inap";
                              }elseif($dataTable[$i]["inacbg_jenis_pasien"]=='2'){
                              $layanan = "Rawat Jalan";
                              } ?>
                            <td class=" "><?php echo $layanan; ?></td>
                        
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>

                    <?php if($_POST["btnExcel"]) {?>
                         <table width="100%" border="0" cellpadding="0" cellspacing="0">
                              <tr class="tableheader">
                                   <?php if($_POST["instalasi"]=='1'){?>
                                   <td align="center" colspan="35">
                                   <?php } elseif($_POST["instalasi"]=='2' || $_POST["instalasi"]==''){?>
                                   <td align="center" colspan="25">
                                   <?php }?>
                                   <strong>INACBGs APPROVAL<br />
                                   <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
                                   <br /><br />
                                   </strong>
                                   </td>          
                              </tr>
                             <tr class="tableheader">
                              <td align="left" colspan="3">
                              <?php if($_POST["tgl_awal"]==$_POST["tgl_akhir"]) { echo "Tanggal : ".$_POST["tgl_awal"]; } elseif($_POST["tgl_awal"]!=$_POST["tgl_akhir"]) { echo "Periode : ".$_POST["tgl_awal"]." - ".$_POST["tgl_akhir"]; }  ?>              
                              <br /><br />
                              </td>
                              <td>&nbsp;</td>
                              <td align="left" colspan="3">
                              <?php if($_POST["instalasi"]=="1") { echo "Instalasi : Rawat Inap"; } elseif($_POST["instalasi"]=="2") { echo "Instalasi : Rawat Jalan"; } else { echo "Instalasi : --";}  ?>              
                              <br /><br />
                              </td>
                              </tr>
                         </table>
                    <?php }?>
                    
                    </body>					 
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>

