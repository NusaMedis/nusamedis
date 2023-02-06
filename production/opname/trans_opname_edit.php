<?php
  require_once("../penghubung.inc.php");
  require_once($ROOT."lib/login.php");
  require_once($ROOT."lib/encrypt.php");
  require_once($ROOT."lib/datamodel.php");
  require_once($ROOT."lib/currency.php");
  require_once($ROOT."lib/dateLib.php");
  require_once($ROOT."lib/expAJAX.php");
  require_once($ROOT."lib/tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new TextEncrypt();     
  $auth = new CAuth();
  $table = new InoTable("table","100%","left");
  $usrId = $auth->GetUserId();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();

  $tgl = date("d-m-Y");
  $wkt = date("H:i:s");


  $opname_id = ($_POST['opname_id']) ? $_POST['opname_id'] : $_GET['id'];
  $periode_tahun_awal = ($_POST['periode_tahun']) ? $_POST['periode_tahun'] : $_GET['tahun'];
  $periode_bulan_awal = ($_POST['periode_bulan']) ? $_POST['periode_bulan'] : $_GET['id_periode'];
  $gudang_awal = ($_POST['gudang']) ? $_POST['gudang'] : $_GET['id_gudang'];
  
  if ($dataOpname) {
    $tgl_opname = $dataOpname['opname_tanggal'];
  }else{
    $tgl_opname = ($_POST['tanggal']) ? $_POST['tanggal'] : $_GET['tanggal'];
  }

  /* SQL GUDANG */
  $sql_gudang = "SELECT * FROM logistik.logistik_gudang WHERE gudang_flag = 'M' AND id_dep = ".QuoteValue(DPE_CHAR, $depId)." ORDER BY gudang_nama ASC";
  $dataGudang = $dtaccess->FetchAll($sql_gudang);
  /* SQL GUDANG */  

  $sql = "SELECT * from logistik.logistik_opname_detail a 
  left join logistik.logistik_item b on a.id_item = b.item_id
  WHERE id_opname = ".QuoteValue(DPE_CHAR, $opname_id)." ORDER BY opname_detail_urut ASC";
  $dataTable = $dtaccess->FetchAll($sql);

  /* SQL OPNAME */
  $sql = "SELECT * FROM logistik.logistik_opname WHERE opname_id = ".QuoteValue(DPE_CHAR, $opname_id);
  $dataOpname = $dtaccess->Fetch($sql);
  /* SQL OPNAME */

  $stat = "";
    if(count($dataTable) > 0){
    if( $tgl >= date_format(date_create($dataTable[0]['penerimaan_periode_tanggal_awal']), 'd-m-Y') && $tgl <= date_format(date_create($dataTable[0]['penerimaan_periode_tanggal_akhir']), 'd-m-Y') ){
      $stat = "allow";
    }
    else{
      $stat = "ignore";
    }
  }
  
  $waktu = ($dataOpname) ? $dataOpname['opname_waktu'] : $wkt;
  $tanggal = ($dataOpname) ? date_db($dataOpname['opname_tanggal']) : $tgl;

  $tableHeader = "Opname Barang";
?>

<!DOCTYPE html>
<html>
  <?php require_once($LAY."header.php") ?>
  <head>
  	<style type="text/css">
  		i.iconButton{
  			cursor: pointer;;
  			font-size: 25px;
  		}
  	</style>
  </head>

  <script type="text/javascript">
  	function number_format (number, decimals, dec_point, thousands_sep) {
	  // Strip all characters but numerical ones.
	  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	  var n = !isFinite(+number) ? 0 : +number,
	      prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	      sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	      dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	      s = '',
	      toFixedFix = function (n, prec) {
	          var k = Math.pow(10, prec);
	          return '' + Math.round(n * k) / k;
	      };
	  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
	  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	  if (s[0].length > 3) {
	      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	  }
	  if ((s[1] || '').length < prec) {
	      s[1] = s[1] || '';
	      s[1] += new Array(prec - s[1].length + 1).join('0');
	  }
	  return s.join(dec);
	}

    function obat(isi) {
      mamas = $('#tanggal').val();
      auto_obat(isi, mamas);
    }

    function tanggal_isi(isi) {
      var gudang = $('#gudang').val();
      mamas = isi;
      auto_obat(gudang, mamas);
    }

    function auto_obat(isi_gudang, tanggal_op) {
      var waktu = $('#waktu').val();
      $('#item_nama').autocomplete({
        serviceUrl: 'get_obat.php?id_gudang='+isi_gudang+'&tanggal='+tanggal_op+'&waktu='+waktu,
        paramName: 'item_nama',
        transformResult: function(response) {
          var data = jQuery.parseJSON(response);
          return {
            suggestions: $.map(data, function(item) {
              return {
                value: item.item_nama,
                data: { 
                  item_id: item.item_id,
                  item_nama: item.item_nama,
                  item_kode: item.item_kode,
                  stok_dep_saldo: item.stok_dep_saldo,
                  stok_tercatat: item.stok_tercatat,
                } 
              };
            })
          };
        },

        onSelect: function (suggestion) {
          $('#item_nama').val(suggestion.data.item_nama);
          $('#item_kode').val(suggestion.data.item_kode);
          $('#item_id').val(suggestion.data.item_id);
          $('#stok_tercatat').val(suggestion.data.stok_dep_saldo);
          $('#stok_tercatatnyaa').val(suggestion.data.stok_tercatat);
        }
      });
    }

    $( document ).ready(function() {
      var mamas = $('#tanggal').val();
      var gudang = $('#gudang').val();
      auto_obat(gudang,mamas);

      tahun($("#periode_tahun").val());

      history.replaceState("Default", "Opname Barang", "");

    });
  </script>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <?php require_once($LAY."topnav.php") ?>
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <!-- DATA OPNAME -->
            <form method="POST" action="">
              <input type="hidden" name="opname_id" id="opname_id" value="<?=$opname_id?>">
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2><?=$tableHeader?></h2>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Periode Tahun</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <select class="form-control" name="periode_tahun" id="periode_tahun" onchange="tahun(this.value)" required="" disabled>
                              <option value="">--- Pilih Periode Tahun ---</option>
                              <?php
                                $periode_tahun = ($periode_tahun_awal) ? $periode_tahun_awal : date('Y');
                                for ($i = 2018; $i <= date('Y')+5; $i++) {
                                ?>
                                  <option value="<?=$i?>" <?= ($periode_tahun == $i) ? "selected" : ''; ?>><?=$i?></option>
                                <?php
                                }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Periode Bulan</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <select class="form-control" name="periode_bulan" id="periode_bulan" onchange="bulan(this.value)" required="" <?php if ($dataOpname) echo 'disabled' ?>>
                              <option value="">--- Pilih Periode Bulan ---</option>
                            </select>
                            <input type="hidden" id="idPeriode">
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Tanggal Opname</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <!-- <div class='input-group date' id='datepicker'> -->
                              <input name="tanggal" id="tanggal" type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" value="<?php if(!$tgl_opname) { 
                                              echo date('d-m-Y');
                                            }else{
                                             echo date("d-m-Y", strtotime($tgl_opname));
                                             }?>" 
                                     <?=($dataOpname) ? 'readonly' : "" ?> onchange="tanggal_isi(this.value)">
                              <!-- <span class="input-group-addon"><span class="fa fa-calendar"></span></span> -->
                            <!-- </div> -->
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Waktu Opname</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <input name="waktu" id="waktu" type='text' class="form-control" data-inputmask="'mask': '99:99:99'" value="<?= ($_POST['waktu']) ? $_POST['waktu'] : $waktu; ?>" required="" <?=($dataOpname) ? 'readonly' : "" ?>>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Gudang</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <select class="form-control" name="gudang" id="gudang" required="" onchange="obat(this.value)" <?php if ($dataOpname) echo 'disabled' ?>>
                              <option value="">--- Pilih Gudang ---</option>
                              <?php foreach ($dataGudang as $gudang): ?>
                                <option value="<?=$gudang['gudang_id']?>" <?= ($gudang_awal == $gudang['gudang_id']) ? "selected" : ''; ?>><?=$gudang['gudang_nama']?></option>
                              <?php endforeach ?>
                            </select>
                            <input type="hidden" id="gudang" value="<?=$gudang_awal?>">
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Keterangan</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <textarea name="opname_keterangan" id="opname_keterangan" class="form-control" style="resize: none;" <?php if ($dataOpname) echo "disabled" ?>><?= $dataOpname['opname_keterangan'] ?></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- DATA OPNAME -->
              <div class="clearfix"></div>
              <!-- TABLE -->
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="x_panel">
                    <div class="x_content">
                      <table class="table table-striped table-bordered" id="dataOpname">
                        <thead>
                          <tr>
                            <th width="4%" style="text-align: center;"> Hapus </th>
                         	
                            <th width="4%" style="text-align: center;"> No </th>
                            <th width="7%" style="text-align: center;"> Kode Barang </th>
                            <th width="35%" style="text-align: center;">Nama Obat</th>
                            <th width="10%" style="text-align: center;">Stok Tercatat</th>
                            <th width="10%" style="text-align: center;">Stok Sebenarnya</th>
                            <th width="10%" style="text-align: center;">Selisih</th>
                            <th width="10%" style="text-align: center;">Keterangan</th>
                          </tr>
                        </thead>
                        <tbody>
       						<?php if ($dataTable): ?>
                            <?php foreach ($dataTable as $key => $value): ?>
                              <tr id="<?=$value['opname_detail_id']?>">
                                <td>

                                  <?php if($value['is_verified'] == 'n') { ?>
                                    <center><i class="glyphicon glyphicon-trash iconButton" id="delete" data-id="<?=$value['opname_detail_id']?>"></i></center>
                                    <input type="hidden" id="opnameDetId" value="<?=$value['opname_detail_id']?>">
                                  <?php }?>
                                
                                </td>
                                <td align="center"><?=$key+1?></td>
                                <td align="center"><?=$value['item_kode']?></td>
                                <td><?=$value['item_nama']?></td>
                                <td align="center"><?=number_format($value['opname_detail_jumlah_sebelumnya'],2,",",".")?></td>
                                <td align="center"><?=number_format($value['opname_detail_jumlah'],2,",",".")?></td>
                                <td align="center"><?=number_format($value['opname_detail_selisih'],2,",",".")?></td>
                                <td><?=$value['opname_detail_keterangan']?></td>
                              </tr>
                            <?php endforeach ?>
                          <?php endif ?>
                          
                        </tbody>
                        <tfoot>
                           <tr>
	                           <td>
	                           </td>
	                           
	                           <td>
	                           </td>

	                           <td>
	                           <input type="text" name="item_kode" id="item_kode" class="form-control" readonly="true">
	                           </td>
	                           <td>
	                              <input type="text" name="item_nama" id="item_nama" value="" placeholder="Nama Obat" class="form-control">
	                              <input type="hidden" name="item_id" id="item_id" value="">
	                              <input type="hidden" name="stok_tercatatnyaa" id="stok_tercatatnyaa" value="" placeholder="Stok Tercatat" class="form-control" readonly="">
	                           </td>
	                            <td>
	                              <input type="text" name="stok_tercatat" id="stok_tercatat" value="" placeholder="Stok Tercatat" class="form-control" readonly="">
	                            </td>
	                            <td><input type="text" name="stok_sebenarnya" id="stok_sebenarnya" value="" placeholder="Stok Sebenarnya" class="form-control" onchange="fun_selisih(this.value)"></td>
	                            <td><input type="text" name="selisih" id="selisih" value="" placeholder="Selisih" class="form-control" readonly=""></td>
	                            <td><input type="text" name="keterangan" id="keterangan" value="" placeholder="Keterangan" class="form-control"></td>
                        	</tr>
                        </tfoot>
                      </table>
                      <a href="trans_opname.php" title="Kembali" class="btn btn-danger">Kembali</a>
                      <button type="button" class="btn btn-warning" onclick="cetak()">Cetak</button>
                      <button type="button" class="btn btn-success pull-right" id="submit">Simpan</button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- TABLE -->
            </form>
          </div>
        </div>
        <?php require_once($LAY."footer.php") ?>
      </div>
    </div>
    <?php require_once($LAY."js.php") ?>
  </body>
</html>

<script type="text/javascript">

	var xhr;
  
  function SendEdit(key){

    var stok_item_id = $(`#id_edit_item_${key}`).val();
    var stok_sebelumnya = $(`#stok_sebelum_edit_item_${key}`).val();
    var stok_asli = $(`#stok_asli_edit_item_${key}`).val();
    var stok_selisih = $(`#stok_selisih_edit_item_${key}`).val();
    var ket = $(`#ket_edit_item_${key}`).val();
    var nama_item = $(`#nama_edit_item_${key}`).val();
    var id_item = $(`#idItem_edit_item_${key}`).val();
    
    $('#item_nama').val(nama_item);
    $('#item_id').val(id_item);
    $('#stok_item_id').val(stok_item_id);
    $('#stok_tercatat').val(parseFloat(stok_sebelumnya));
    $('#stok_tercatatnyaa').val(parseFloat(stok_sebelumnya));
    $('#stok_sebenarnya').val(parseFloat(stok_asli));
    $('#selisih').val(parseFloat(stok_selisih));
    $('#keterangan').val(ket);


  }

  function tahun(isi_tahun) {
    jQuery.getJSON('get_periode.php', {tahun: isi_tahun}, function(data) {
      $("#periode_bulan").html(`<option value="">--- Pilih Periode Bulan ---</option>`);
      $.each(data, function(index, val) {
        $("#periode_bulan").append(
          "<option id = '"+val.penerimaan_periode_id+"' value = '"+val.penerimaan_periode_id+"'>"+val.penerimaan_periode_nama+"</option>");
      });
      $('#<?=$periode_bulan_awal?>').attr('selected',true);
      $("input#idPeriode").val($("select#periode_bulan").val());
    });

    

  }

  function bulan(isi_bulan) {
    jQuery.getJSON('get_periode.php', {id: isi_bulan}, function(data) {
      $("#tanggal_awal").val(data.tgl_awal);
      $("#tanggal_akhir").val(data.tgl_akhir);
      $("input#idPeriode").val($("select#periode_bulan").val());
    });

    
  }

  function fun_selisih(isi) {
  	var selisih = isi-$('#stok_tercatatnyaa').val();
    $('#selisih').val(selisih);
  }

  function cetak() {
    window.open('trans_opname_cetak.php?id=<?=$opname_id?>', '_blank');
    window.location.href = "trans_opname.php";
  }

  $("button#submit").click(function(){
  	var periode_tahun = $("select#periode_tahun").val();
    var id_periode = $("input#idPeriode").val();
  	var id_opname = $("input#opname_id").val();
  	var tgl = $("input#tanggal").val();
  	var waktu = $("input#waktu").val();
  	var gudang = $("input#gudang").val();
  	var keterangan_op = $("textarea#opname_keterangan").val();

  	var id_item = $("input#item_id").val();
  	var nama_item = $("input#item_nama").val();
  	var stok_prev = $("input#stok_tercatatnyaa").val();
  	var stok_real = $("input#stok_sebenarnya").val();
  	var stok_selisih = $("input#selisih").val();
  	var keterangan = $("input#keterangan").val();
  	var req = '';

  	if(stok_selisih != '0' && keterangan.length == 0){
  		req = "dame";
  	}

  	if(typeof xhr != "undefined"){
  		xhr.abort();
  	}

  	if(stok_real.length > 0 && req == '') {

  	xhr = $.post("proses_opname.php", {
  		act : "simpan",
  		id_periode : id_periode,
  		id_opname : id_opname,
  		tgl : tgl,
  		waktu : waktu,
  		gudang : gudang,
  		keterangan_op : keterangan_op,

  		id_item : id_item,
  		nama_item : nama_item,
  		stok_prev : stok_prev,
  		stok_real : stok_real,
  		stok_selisih : stok_selisih,
  		keterangan : keterangan,
  	});

  	xhr.promise().done(function(data){
      var id_opname = $("input#opname_id").val();
  		var data = JSON.parse(data);
  		var insertTo = [];

      if(id_opname == ""){
        history.pushState(data.id_opname, "Opname Barang", "trans_opname_edit.php?tahun="+periode_tahun+"&id_periode="+id_periode+"&id_gudang="+gudang+"&id="+data.id_opname);
      }

  		insertTo.push('<tr id="'+data.opname_detail_id+'">');

  		insertTo.push('<td>');
  		insertTo.push('<center><i class="glyphicon glyphicon-trash iconButton" id="delete" data-id="'+data.opname_detail_id+'"></i></center>');
  		insertTo.push('<input type="hidden" id="opnameDetId" value="'+data.opname_detail_id+'">');
  		insertTo.push('</td>');

  		insertTo.push('<td align="center">'+data.opname_detail_urut+'</td>');
  		insertTo.push('<td align="center">'+data.item_kode+'</td>');
  		insertTo.push('<td>'+data.item_nama+'</td>');
  		insertTo.push('<td align="center">'+number_format(data.opname_detail_jumlah_sebelumnya, 2, ',', '.')+'</td>');
  		insertTo.push('<td align="center">'+number_format(data.opname_detail_jumlah, 2, ',', '.')+'</td>');
  		insertTo.push('<td align="center">'+number_format(data.opname_detail_selisih, 2, ',', '.')+'</td>');
  		insertTo.push('<td>'+data.opname_detail_keterangan+'</td>');

  		insertTo.push('</tr>');

  		insertTo = insertTo.join("");

  		$("table#dataOpname tbody").append(insertTo);
  		$("input#opname_id").val(data.id_opname);

  		$("input#item_kode").val("");
  		$("input#item_id").val("");
	  	$("input#item_nama").val("");
	  	$("input#stok_tercatatnyaa").val("");
	  	$("input#stok_tercatat").val("");
	  	$("input#stok_sebenarnya").val("");
	  	$("input#selisih").val("");
	  	$("input#keterangan").val("");

      $("select#gudang").attr("disabled", true);
      $("select#periode_bulan").attr("disabled", true);
      $("input#tanggal").attr("disabled", true);
      $("input#waktu").attr("disabled", true);
      $("textarea#opname_keterangan").attr("disabled", true);


  	});
  	}
  	else{
  		if(stok_real.length == 0){
  			alert("Stok Belum Diisi");
  			$("input#stok_sebenarnya").focus();
  		}
  		else if(req == 'dame'){
  			alert("Keterangan Kosong");
  			$("input#keterangan").focus();
  		}
  	}

  });

  $("table#dataOpname tbody").on("click", "i#delete", function(){
  	var idRow = $(this).data("id");
    var id_opname = $("input#opname_id").val();

  	if(typeof xhr != "undefined"){
  		xhr.abort();
  	}

  	xhr = $.post("proses_opname.php", {
  		act : "delete",
      id_opname : id_opname,
  		opnameDetId : idRow,
  	});

  	xhr.promise().done(function(data){
  		$("table#dataOpname tbody tr#"+idRow).remove();
  	});
  });

  $(window).on("popstate",function(e){
    var id_opname = $("input#opname_id").val();
    var periode_tahun = $("select#periode_tahun").val();
    var id_periode = $("input#idPeriode").val();
    var gudang = $("input#gudang").val();

    history.replaceState(id_opname, "Opname Barang as", "trans_opname_edit.php?tahun="+periode_tahun+"&id_periode="+id_periode+"&id_gudang="+gudang+"&id="+id_opname);
  });
</script>