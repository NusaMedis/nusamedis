<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
     //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
   	 $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
	 $depId = $auth->GetDepId();
	 $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
     
     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
	 
    //DATA AWAL
    $tglSekarang = date("d-m-Y");
	
	// Data tipe poli
     $sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='G' or poli_tipe_id='J' or poli_tipe_id='I') order by poli_tipe_nama asc";    
     $rs = $dtaccess->Execute($sql);
     $dataTipe = $dtaccess->FetchAll($rs);
	 
	 //data gedung
     $sql = "select * from global.global_gedung_rawat 
             order by gedung_rawat_nama, gedung_lantai_ke asc ";     
     $rs = $dtaccess->Execute($sql);
     $dataGedungRawat = $dtaccess->FetchAll($rs);
     
     //data kelas
     $sql = "select * from klinik.klinik_kelas order by kelas_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKelas = $dtaccess->FetchAll($rs);
     
     for($i=0,$n=count($dataKelas);$i<$n;$i++){
        unset($show);
        if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
        $opt_kategori[$i] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
		$opt_kamar[0] = $view->RenderOption("--","[pilih kamar]",$show);
     
		$opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
		if($_POST["id_kamar"] && $_POST["id_kamar"]!="--"){
		$opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
		}
	 }
    
	 // Data asal poli
     $sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='G' or poli_tipe_id='J') order by poli_tipe_nama asc"; 
     $rs = $dtaccess->Execute($sql);
     $dataAsal = $dtaccess->FetchAll($rs);

     // Data poli
     $sql = "select * from global.global_auth_poli order by poli_nama asc"; 
     $rs = $dtaccess->Execute($sql);
     $polike2 = $dtaccess->FetchAll($rs);
	
	//cari data Sebab Sakit
     $sql = "select * from global.global_sebab_sakit";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataSebabSakit = $dtaccess->FetchAll($rs);
	 
	 // Data prosedur masuk
     $sql = "select * from global.global_prosedur_masuk";    
     $rs = $dtaccess->Execute($sql);
     $dataProsedurMasuk = $dtaccess->FetchAll($rs);
	 
	 // Data jenis pasien yang ditampilkan umum saja//
     $sql = "select * from  global.global_jenis_pasien a";
    // $sql .= " where jenis_id<>".PASIEN_BAYAR_BPJS." and jenis_flag='y'";
    //echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataJPasien = $dtaccess->FetchAll($rs);
	 
	 // Data jenis jkn
      $sql = "select * from  global.global_jkn order by jkn_id desc";
     $rs = $dtaccess->Execute($sql);
     $dataJKN = $dtaccess->FetchAll($rs);

      // Data jenis iks
      $sql = "select * from  global.global_perusahaan order by perusahaan_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataIKS = $dtaccess->FetchAll($rs);
	 
	 //tabel header
     $tableHeader = "Edit Registrasi";
	 $addpasien = '<input type="button" name="btnadd" value="Registrasi Pasien Baru" class="btn btn-primary col-md-3 pull-right" onClick="document.location.href=\''.$pasienadd.'\'"></button>';
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
	
	<script type="text/javascript">
	function getKlinik(param){
		if (param == 'I') {
			$("#div_klinik").css('display','none');
			$("#div_gedung").css('display','block');
			$("#div_kelas").css('display','block');
			$("#div_kamar").css('display','block');
			$("#div_bed").css('display','block');
			$("#div_asal").css('display','block');
			$("#div_reg_tanggal").css('display','block');
		} else { 
			$("#div_klinik").css('display','block');
			$("#div_gedung").css('display','none');
			$("#div_kelas").css('display','none');
			$("#div_kamar").css('display','none');
			$("#div_bed").css('display','none');
			$("#div_asal").css('display','none');
			$("#div_reg_tanggal").css('display','block');
		}
		
		if(param){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'instalasi_id='+param,
				success:function(html){
					$('#klinik').html(html);
					$('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
				}
			}); 
		}else{
			$('#klinik').html('<option value="">Pilih Instalasi Dahulu</option>');
			$('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
		}
	};
	
	function getDokter(param){
		if(param){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'poli_id='+param,
				success:function(html){
					$('#dokter').html(html);
				}
			});
			getPaket(param);            
		}else{
			$('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
			$('#paket').html('<option value="">Pilih Klinik Dahulu</option>');
		}
	};

	function getPoliAsal(param){
		$.ajax({
			type:'GET',
			url:'poli_asal.php',
			data:'id_cust_usr='+param,
			success:function(html){
				$('#klinik_asal').html(html);
			}
		});           
	};
	
	function getPaket(param){
		if(param){
		$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'id_poli='+param,
				success:function(html){
					$('#paket').html(html);
				}
			});             
		}
	};
	
	function getKamarbyKelas(param){
		var kelas_id = param;
		var gedung_id = $('#id_gedung_rawat').val();
		if(kelas_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'kelas_id='+kelas_id+'&gedung_id='+gedung_id,
				success:function(html){
					$('#id_kamar').html(html);
					$('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>'); 
				}
			}); 
		}else{
			$('#id_kamar').html('<option value="">Pilih Gedung dan Kelas Dahulu</option>'); 
			$('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
		}
	};
	
	function getBed(param){
		var kamar_id = param;
		if(kamar_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'kamar_id='+kamar_id,
				success:function(html){
					$('#id_bed').html(html);
				}
			}); 
		}else{
			$('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
		}
		
	};
	
	function getCaraKunjungan(param){
		var prosedur_id = param;
		if(prosedur_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'prosedur_id='+prosedur_id,
				success:function(html){
					$('#reg_rujukan_id').html(html); 
					$('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
				}
			}); 
		}else{
			$('#reg_rujukan_id').html('<option value="">Pilih Prosedur Dahulu</option>');
			$('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
		}
	};
	
	function getPoli(param){
		var poli_id = param;
		if(poli_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'poli_id='+poli_id,
				success:function(html){
					$('#poli_id').html(html); 
					// $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
				}
			}); 
		}else{
			// $('#reg_rujukan_id').html('<option value="">Pilih Prosedur Dahulu</option>');
			// $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
		}
	};
	
	function getCaraKunjunganDet(param){
		var rujukan_id = param;
		if(rujukan_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'rujukan_id='+rujukan_id,
				success:function(html){
					$('#reg_rujukan_det').html(html); 
			   }
			}); 
		}else{
			$('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
		}
	};
	
	function cekCaraBayar(param){
		var jenis_pasien = param;
		if(jenis_pasien=='5'){ //pasien jkn
            $('#bpjs').css('display','block');
            $("#div_jkn").css('display','block');
            $("#div_iks").css('display','none');
            $("#tipe_jkn").removeAttr("disabled") ; 
            $("#tipe_iks").attr("disabled","disabled");
        } else if(jenis_pasien=='7'){ //cara bayar iks
            $('#bpjs').css('display','none');
            $("#div_jkn").css('display','none');
            $("#div_iks").css('display','block');
            $("#tipe_jkn").attr("disabled","disabled");
            $("#tipe_iks").removeAttr("disabled") ; 
        }else{
            $('#bpjs').css('display','none');   
            $("#div_jkn").css('display','none');
            $("#div_iks").css('display','none');
            $("#tipe_jkn").attr("disabled","disabled");
            $("#tipe_iks").attr("disabled","disabled");
        }
	};
	
	function getDokterIrna(param){
		$.ajax({
			type:'POST',
			url:'RS_Data.php',
			data:'irna=irna',
			success:function(html){
				$('#dokter').html(html);
			}
		}); 
	};
	</script>

    
   <script type="text/javascript">
	//filter field data 
		$(function(){

			//auto complete
	        $('#diagnosa').autocomplete({
	          serviceUrl: 'get_icd.php',
	          paramName: 'q',
	          transformResult: function(response) {
	            var data = jQuery.parseJSON(response);
	            return {
	                suggestions: $.map(data, function(item) {
	                    return { value: item.icd_nomor+" - "+item.icd_nama, data: item.icd_nomor };
	                })
	            };
	          },
	          onSelect: function (suggestion) {
	            $('#reg_diagnosa_awal').val(suggestion.data);
	          }
	        });

			var dg = $('#dg').datagrid();
			
			dg.datagrid('enableFilter', [
			//disable filter
			{field:'reg_waktu',type:'label'},
			{field:'cust_usr_kode_tampilan',type:'text'},
			{field:'cust_usr_nama',type:'text'},
			{field:'cust_usr_alamat',type:'label'},
			{field:'cust_usr_tanggal_lahir',type:'label'},
			//enable filter
			{
				field:'reg_status', //filter status
				type:'combobox',
				options:{
					data: [{
						label: 'Semua',
						value: ''
					},{
						label: 'Belum Dilayani',
						value: 'Belum Dilayani'
					},{
						label: 'Sampai di Poli',
						value: 'Sampai di Poli'
					},{
						label: 'Sudah Dilayani',
						value: 'Sudah Dilayani'
					}],
					valueField:'value',
					textField:'label',
					panelHeight: 'auto',
					onChange:function(value){
						if (value == ''){
							dg.datagrid('removeFilterRule', 'reg_status');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'reg_status',
								op: 'equal',
								value: value
							});
						}
						dg.datagrid('doFilter');
					}
				}
			},{
				field:'reg_tipe_jkn', //filter status
				type:'combobox',
				options:{
					data: [{
						label: 'Semua',
						value: ''
					},{
						label: 'PBI',
						value: 'PBI'
					},{
						label: 'NON PBI',
						value: 'NON PBI'
					}],
					valueField:'value',
					textField:'label',
					panelHeight: 'auto',
					onChange:function(value){
						if (value == ''){
							dg.datagrid('removeFilterRule', 'reg_tipe_jkn');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'reg_tipe_jkn',
								op: 'equal',
								value: value
							});
						}
						dg.datagrid('doFilter');
					}
				}
			},{
				field:'poli_nama', //filter poli
				type:'combobox',
				options:{
					url: 'get_klinik.php',
					valueField:'poli_nama',
					textField:'poli_nama',
					panelHeight: 'auto',
					onChange:function(value){
						if (value == ''){
							dg.datagrid('removeFilterRule', 'poli_nama');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'poli_nama',
								op: 'equal',
								value: value
							});
						}
						dg.datagrid('doFilter');
					}
				}
			}]);
		});
	</script>
	
  <!-- /////////////////// -->
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
                <h3><?php echo $tableHeader; ?></h3>
              </div>
            </div>
            <?php //echo "$addpasien"; ?>
            <div class="clearfix"></div>
			
			<!-- insert irj Data Pasien -->
			<form id="form_irj" action="proses_update.php" method="post">
				<input id="cust_usr_id" type="hidden" name="cust_usr_id">
				<input id="regId" type="hidden" name="regId">
				<input id="reg_status_pasien" value="L" type="hidden" name="reg_status_pasien">
            <div class="row">             
			<!-- KOLOM KIRI -->
              <div class="col-md-6 col-sm-6 col-xs-12">
				<!-- == Hasil dari combobox => set ke element berdasar id == -->
				<div class="x_panel">
				  <div class="x_title">
                    <h2>Data Registrasi</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<table class="col-md-12 col-sm-12 col-md-12">
					  <tr>
						<th width="150px">No. RM</th>
						<td width="15px">:  </td>
						<td><input id="norm" class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_kode" readonly></td>
					  </tr>
					  <tr>
						<th>Nama Pasien</th>
						<td>:  </td>
						<td><input id="nmps"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_nama" readonly></td>
					  </tr>
					  <tr>
						<th>Alamat</th>
						<td>:  </td>
						<td><input id="alps"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_alamat" readonly></td>	
					  </tr>
					  <tr>
						<th>Poli Klinik</th>
						<td>:  </td>
						<td><input id="poli"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_alamat" readonly></td>	
					  </tr>
					</table>
                  </div>
                </div>
              </div>
			  <!-- KOLOM KANAN -->
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_content" >
                  
                    <div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tipe Rawat</label>
                        <select id="instalasi" class="select2_single form-control" name="instalasi" onChange="getKlinik($(this).val());">
                           <!--  <option value="">- Pilih instalasi -</option> -->
                          <?php for ($i=0; $i < count($dataTipe); $i++) { ?>
                            <option value="<?php echo $dataTipe[$i]['poli_tipe_id'] ?>" 
                                <?php if ($reg['reg_tipe_rawat'] == $dataTipe[$i]['poli_tipe_id']) { echo "selected"; } elseif ($dataTipe[$i]['poli_tipe_id'] == 'J') { echo "selected"; } ?>
                            ><?php echo $dataTipe[$i]['poli_tipe_nama'] ?></option>
                          <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_klinik_asal" class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Poli Klinik </label>
                        <select id="klinik_asal" class="select2_single form-control" name="klinik_asal">
                            <option value="">- Pilih -</option>
                            <?php for($i=0,$n=count($polike2);$i<$n;$i++) {?>
                          		<option value="<?php echo $polike2[$i]["poli_id"];?>"><?php echo $polike2[$i]["poli_nama"];?></option>
                            <?php } ?>
                        </select>
                    </div>  

                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Bayar</label>
                        <select id="reg_jenis_pasien" class="select2_single form-control" name="reg_jenis_pasien"  onChange="cekCaraBayar($(this).val())">
                        <!--<option value="">- Pilih Cara Bayar -</option>-->
                            <?php 
                            for($i=0,$n=count($dataJPasien);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataJPasien[$i]["jenis_id"];?>"
                            <?php if ($dataJPasien[$i]["jenis_id"]=='2') echo "selected" ?>>
                                <?php echo $dataJPasien[$i]["jenis_nama"];?>
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_jkn" class="col-md-6 col-sm-6 col-xs-12" style="display:none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Tipe JKN</label>
                        <select id="tipe_jkn" class="select2_single form-control" name="tipe_jkn" disabled>
                        <!--<option value="">- Pilih Cara Bayar -</option>-->
                            <?php 
                            for($i=0,$n=count($dataJKN);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataJKN[$i]["jkn_id"];?>">
                                <?php echo $dataJKN[$i]["jkn_nama"];?>
                            </option>
                        <?php } ?>
                        </select>
                    </div>

                    <div id="div_iks" class="col-md-6 col-sm-6 col-xs-12" style="display: none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Perusahaan</label>
                        <select id="tipe_iks" class="select2_single form-control" name="perusahaan" disabled="">
                        <?php 
                            for($i=0,$n=count($dataIKS);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataIKS[$i]["perusahaan_id"];?>">
                                <?php echo $dataIKS[$i]["perusahaan_nama"];?>
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                                          
                    <div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Sebab Sakit</label>
                        <select id="reg_sebab_sakit" class="select2_single form-control" name="reg_sebab_sakit" >
                       <!--  <option value="">- Pilih Sebab Sakit -</option> -->
                            <?php for($i=0,$n=count($dataSebabSakit);$i<$n;$i++){ ?>
                            <option value="<?php echo $dataSebabSakit[$i]["sebab_sakit_id"];?>">
                                <?php echo $dataSebabSakit[$i]["sebab_sakit_nama"];?> 
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    <!--
                    <div id="div_reg_tanggal" class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tanggal Registrasi</label>
                        <input type="text" id="reg_tanggal" name="reg_tanggal" class="form-control" value="<?php echo date('d-m-Y'); ?>" data-inputmask="'mask': '99-99-9999'">
                    </div> -->
                    
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Prosedur Masuk</label>
                        <select id="reg_prosedur_masuk" class="select2_single form-control" name="reg_prosedur_masuk" required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')" onChange="getCaraKunjungan($(this).val())">
                        <option value="">- Pilih Prosedur Masuk -</option>
                            <?php 
                            for($i=0,$n=count($dataProsedurMasuk);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataProsedurMasuk[$i]["prosedur_masuk_id"];?>">
                                <?php echo $dataProsedurMasuk[$i]["prosedur_masuk_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Kunjungan</label>
                        <select id="reg_rujukan_id" class="select2_single form-control" name="reg_rujukan_id"  onChange="getCaraKunjunganDet($(this).val())">
                        <option value="">- Pilih Cara Kunjungan -</option>
                            <?php 
                            for($i=0,$n=count($dataCaraKunjungan);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataCaraKunjungan[$i]["rujukan_id"];?>">
                                <?php echo $dataCaraKunjungan[$i]["rujukan_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div>

                    <div id="rujukan_det" class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Detail Kunjungan</label>
                        <select id="reg_rujukan_det" class="select2_single form-control" name="reg_rujukan_det" >
                            <option value="">- Pilih Detail Kunjungan -</option>
                        </select>
                    </div>
                   
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Diagnosa Awal</label>
                        <input name ="diagnosa" type="text" class="form-control" id="diagnosa" placeholder="" value="">
                        <input name ="reg_diagnosa_awal" type="hidden" class="form-control" id="reg_diagnosa_awal" value="">
                    </div>
                    <!-- <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Klinik</label>
                        <select id="klink" class="select2_single form-control" name="klinik" required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')" onChange="getPoli($(this).val())">
                        <option value="">- Pilih Klinik -</option>
                            <?php 
                            for($i=0,$n=count($dataPoli);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataPoli[$i]["poli_id"];?>">
                                <?php echo $dataPoli[$i]["poli_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div> -->
                  </div>
                </div>
			  <input class="btn btn-success" name="btn_value" id="btn_value" value="Tambah" type="hidden">
			  <input name="btn" id="btn" class="btn btn-primary col-md-3 pull-right" type="submit" value="Simpan">
			  <input id="btnReset" class="btn btn-danger pull-right" style="display:none;" value="Batal" onclick="window.location.reload()">
              </div>
            </div>
			</form>

             <!-- Data View Pasien -->
            <div class="row">             
              <div class="col-md-12 col-sm-12 col-xs-12">
              
					<table id="dg" title="Data Kunjungan Pasien <?php echo $tglSekarang;?>" style="width:100%;height:400px"
							url="get_irj_mundur.php"
							toolbar="#toolbar" pagination="false" striped="true"
							rownumbers="true" fitColumns="true" singleSelect="true">
						<thead>
							<tr>
								<!-- TABEL DATA => field samakan field tabel database -->
								<th field="reg_id" hidden >Reg ID</th>
								<th field="reg_tanggal" width="50">Tanggal</th>
								<th field="reg_waktu" width="50">Waktu</th>
								<th field="reg_kode_trans" width="50">No. Registrasi</th>
								<th field="cust_usr_kode_tampilan" width="50">No. RM</th>
								<th field="cust_usr_nama" width="50">Nama Pasien</th>
								<th field="cust_usr_alamat" width="100">Alamat</th>
								<th field="cust_usr_tanggal_lahir" width="50">Tanggal Lahir</th>
								<th data-options="field:'jenis_nama',width:50,
									formatter:function(value,row){
										if(row.jkn_nama != null){ a = row.jenis_nama+' '+row.jkn_nama }
										else if(row.perusahaan_nama != null){ a = row.jenis_nama+' '+row.perusahaan_nama }
										else { a = row.jenis_nama };
										return a;
									}
								">Cara Bayar</th>
								<th field="poli_nama" width="100">Poli</th>
								<th data-options="field:'reg_status',
									formatter:function(value,row){
									var E0 = 'Belum Dilayani';
									var E1 = 'Sampai di Poli';
									var E2 = 'Sudah Dilayani';
									if (row.reg_status == 'E0') { return E0; }
									if (row.reg_status == 'E1') { return E1; }
									if (row.reg_status == 'E2') { return E2; }
								},
								">Status</th>
	<!-- 							<th data-options="field:'poli_asal',width:50,
									formatter:function(value,row){
										if(row.poli_asal != row.poli_nama){ a = row.poli_asal }
										else { a = 'Poli Pertama' };
										return a;
									}
								">Poli Asal</th> -->
								<th field="dokter" width="100">Dokter</th>
							</tr>
						</thead>
					</table>
					<div id="toolbar">
						<div id = "tb" style = "padding: 5px; height: auto">
                        
							<div style = "margin-bottom: 5px">
								Rentang tanggal: <input id="tgl_awal" class = "easyui-datebox" data-options="formatter:myformatter,parser:myparser" style = "width: 120px">
								Ke: <input id="tgl_akhir" class = "easyui-datebox" data-options="formatter:myformatter,parser:myparser" style = "width: 120px">
								<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="cari()"> Cari </a>
							</div>    
							<div>
								<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Batal Registrasi</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg').datagrid('reload');">Refresh</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak()">Cetak Reg</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakb()">Cetak Barcode</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakbb()">Cetak Barcode Besar</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakkartu()">Cetak Kartu</a>            
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakringkasan()">Cetak Ringkasan</a>
							</div>
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
<!-- jQuery -->
<?php require_once($LAY."js.php") ?>

	<script type="text/javascript">
        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(d,m-1,y);
            } else {
                return new Date();
            }
        }
		
		function cari(){			
			$('#dg').edatagrid('load',{
		        tgl_awal: $('#tgl_awal').val(),
		        tgl_akhir: $('#tgl_akhir').val()
		    });
		}
    </script>
	<script type="text/javascript">
	//fungsi edit => Ambil data dari tabel berdasar PK => lempar data berdasar elemen id
		function editUser(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				$.get('get_irj_mundur.php',{reg_id:row.reg_id},function(r){
					$('#norm').val(r[0].cust_usr_kode_tampilan);
					$('#nmps').val(r[0].cust_usr_nama);
					$('#alps').val(r[0].cust_usr_alamat);
					$('#poli').val(r[0].poli_nama);					
					$('#instalasi').val(r[0].reg_tipe_rawat);
					$('#regId').val(r[0].reg_id);
					$('#cust_usr_id').val(r[0].id_cust_usr);
					$('#klinik_asal').val(r[0].id_poli);
					$('#reg_diagnosa_awal').val(r[0].reg_diagnosa_awal);
					$('#diagnosa').val(r[0].reg_diagnosa_awal);
					var reg_tanggal = formatDate(r[0].reg_tanggal);
					$('#reg_tanggal').val(reg_tanggal);
					//getPoliAsal(r[0].id_cust_usr);
					getKlinik(r[0].reg_tipe_rawat);
					getPoli(r[0].poli_id);
					getCaraKunjungan(r[0].reg_prosedur_masuk);
					getCaraKunjunganDet(r[0].reg_rujukan_id);
					cekCaraBayar(r[0].reg_jenis_pasien);
					setTimeout(function() {  
						$('#reg_sebab_sakit').val(r[0].reg_sebab_sakit);
						$('#reg_prosedur_masuk').val(r[0].reg_prosedur_masuk);
						$('#reg_rujukan_id').val(r[0].reg_rujukan_id);
						$('#poli_id').val(r[0].poli_id);
						$('#reg_rujukan_det').val(r[0].reg_rujukan_det);
						$('#reg_jenis_pasien').val(r[0].reg_jenis_pasien);
						$('#tipe_jkn').val(r[0].reg_tipe_jkn);
						$('#tipe_iks').val(r[0].id_perusahaan);
					}, 2000);
					document.getElementById('btn').value = "Simpan";	//jika edit tombol ganti value
					document.getElementById('btn_value').value = "Simpan";	//jika edit tombol ganti value
					document.getElementById('btnReset').style.display = 'block';	//jika edit tombol reset muncul
				},'json');
			}  
			
		}

		function formatDate(date) {
		    var d = new Date(date),
		        month = '' + (d.getMonth() + 1),
		        day = '' + d.getDate(),
		        year = d.getFullYear();

		    if (month.length < 2) month = '0' + month;
		    if (day.length < 2) day = '0' + day;
		    return [day, month, year].join('-');
		}

		//fungsi hapus => Ambil data dari tabel berdasar PK => query hapus data di file del_irj.php
		function destroyUser(){
			var row = $('#dg').datagrid('getSelected');
			if (row){

				$.messager.prompt('Anda yakin?', 'Alasan pembatalan registrasi:', function(r){
					if (r){
						//alert(r);
						$.post('del_irj_mundur.php',{reg_id:row.reg_id,alasan:r},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Pembatalan registrasi sukses"
								});
								$('#dg').datagrid('reload');	// reload the user data
							} else {
								$.messager.show({	// show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						},'json');
					}
				});
			}
		}
		//fungsi cetak registrasi
		function cetak(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_registrasi.php?reg_id='+row.reg_id;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}
		//fungsi cetak barcode
		function cetakb(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_barcode.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}
		
		//fungsi cetak barcode besar
		function cetakbb(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_barcode_besar.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
				//	printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//fungsi cetak kartu
		function cetakkartu(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_kartu.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					printWindow.print();
					printWindow.close();
				}, true);
			}
		}

    		//fungsi cetak ringkasan
		function cetakringkasan(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_ringkasan.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					//printWindow.print();
				//	printWindow.close();
				}, true);
			}
		}

	</script>
	
  </body>
</html>           