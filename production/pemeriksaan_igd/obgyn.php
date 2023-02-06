<?
$sql = "select * from klinik.klinik_anamnesa_pilihan where id_anamnesa=" . QuoteValue(DPE_CHAR, '9dafa78dca4a01f50d21fbc884a5eecb') . "
        order by anamnesa_pilihan_urut asc, anamnesa_pilihan_id asc";
$rs = $dtaccess->Execute($sql);
$dataAnamnesaDetail = $dtaccess->FetchAll($rs);
?>

<script type="text/javascript" src="obgyn.js"></script>
<script>
  function saveItem(index) {
    var row = $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('getRows')[index];
    var url = row.isNewRecord ? 'saveEasyui.php' : 'updateEasyui.php?func=update&id=' + row.anamnesa_tb_id;
    $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('getRowDetail', index).find('form').form('submit', {
      url: url,
      onSubmit: function(param) {
        param.rawat_id = row.rawat_id;
        param.anamnesa_id = row.anamnesa_id;
        param.poli_id = row.poli_id;
        param.reg_id = row.reg_id;
        return $(this).form('validate');
      },
      success: function(data) {
        data = eval('(' + data + ')');
        data.isNewRecord = false;
        $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('collapseRow', index);
        $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('updateRow', {
          index: index,
          row: data
        });
      }
    });
  }

  function saveItemGinek(index) {
    var row = $('#riwayat_kehamilan_ginek').datagrid('getRows')[index];
    var url = row.isNewRecord ? 'saveEasyui.php' : 'updateEasyui.php?func=update&id=' + row.anamnesa_tb_id;
    $('#riwayat_kehamilan_ginek').datagrid('getRowDetail', index).find('form').form('submit', {
      url: url,
      onSubmit: function(param) {
        param.rawat_id = row.rawat_id;
        param.anamnesa_id = row.anamnesa_id;
        param.poli_id = row.poli_id;
        param.reg_id = row.reg_id;
        return $(this).form('validate');
      },
      success: function(data) {
        data = eval('(' + data + ')');
        data.isNewRecord = false;
        $('#riwayat_kehamilan_ginek').datagrid('collapseRow', index);
        $('#riwayat_kehamilan_ginek').datagrid('updateRow', {
          index: index,
          row: data
        });
      }
    });
  }

  function cancelItem(index) {
    var row = $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('getRows')[index];
    if (row.isNewRecord) {
      $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('deleteRow', index);
    } else {
      $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('collapseRow', index);
    }
  }

  function cancelItemGinek(index) {
    var row = $('#riwayat_kehamilan_ginek').datagrid('getRows')[index];
    if (row.isNewRecord) {
      $('#riwayat_kehamilan_ginek').datagrid('deleteRow', index);
    } else {
      $('#riwayat_kehamilan_ginek').datagrid('collapseRow', index);
    }
  }

  function destroyItem() {
    var row = $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('getSelected');
    if (row) {
      $.messager.confirm('Konfirmasi', 'Anda Yakin?', function(r) {
        if (r) {
          var index = $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('getRowIndex', row);
          $.get('updateEasyui.php?func=destroy&id=' + row.anamnesa_tb_id, {}, function() {
            $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('deleteRow', index);
          });
        }
      });
    }
  }

  function destroyItem() {
    var row = $('#riwayat_kehamilan_ginek').datagrid('getSelected');
    if (row) {
      $.messager.confirm('Konfirmasi', 'Anda Yakin?', function(r) {
        if (r) {
          var index = $('#riwayat_kehamilan_ginek').datagrid('getRowIndex', row);
          $.get('updateEasyui.php?func=destroy&id=' + row.anamnesa_tb_id, {}, function() {
            $('#riwayat_kehamilan_ginek').datagrid('deleteRow', index);
          });
        }
      });
    }
  }

  function newItem() {
    var reg_id = $('#regId').val();
    var rawat_id = $('#rawat_id').val();
    var poli_id = $('#id_poli').val();
    $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('appendRow', {
      isNewRecord: true,
      rawat_id: rawat_id,
      poli_id: poli_id,
      reg_id: reg_id,
      anamnesa_id: '9dafa78dca4a01f50d21fbc884a5eecb'
    });
    var index = $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('getRows').length - 1;
    $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('expandRow', index);
    $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('selectRow', index);
    $('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('fixDetailRowHeight', index);
  }

  function newItemGinek() {
    var reg_id = $('#regId').val();
    var rawat_id = $('#rawat_id').val();
    var poli_id = $('#id_poli').val();
    $('#riwayat_kehamilan_ginek').datagrid('appendRow', {
      isNewRecord: true,
      rawat_id: rawat_id,
      poli_id: poli_id,
      reg_id: reg_id,
      anamnesa_id: 'riwayat_kehamilan_ginek'
    });
    var index = $('#riwayat_kehamilan_ginek').datagrid('getRows').length - 1;
    $('#riwayat_kehamilan_ginek').datagrid('expandRow', index);
    $('#riwayat_kehamilan_ginek').datagrid('selectRow', index);
    $('#riwayat_kehamilan_ginek').datagrid('fixDetailRowHeight', index);
  }
</script>

<div title="Asuhan Medis Awal" style="padding:5px">
  <div class="form-horizontal form-label-left">
    <div class="x_title">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <label class="col-md-11 col-sm-11 col-xs-11">
          <h2>Asuhan Medis Awal</h2>
        </label>
        <div class="col-md-1 col-sm-1 col-xs-1">
          <h2><?php echo $tglSekarang; ?></h2>
        </div>
      </div>
      <hr>
    </div>
    <form id="form_obgyn" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
      <input id="asd" type="hidden" name="asd" value="">
      <div class="col-md-12">
        <div class="col-md-6">
          <h2><b>I. SUBJECTIVE</b></h2>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kasus Obsetri</h4>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <div class="col-md-12">
                  <label style="float: right;">Keluhan Utama</label>
                </div>
              </div>
              <div class="col-md-6">
                <textarea name="keluhanUtama" id="keluhanUtama" style="min-width: 230px; min-height: 200px"></textarea>
              </div>
              <!-- <div class="col-md-2">
                <input type="text" name="berapa_lama" id="berapa_lama" class="form-control" style="width: 75%;">
              </div>
              <div class="col-md-1">
                <label>Bulan</label>
              </div> -->
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-offset-2 col-md-5">
                <select name="select_keluhan_utama" id="select_keluhan_utama" class="form-control">
                  <option value=""></option>
                  <option value="Mengekuarkan Cairan">Mengeluarkan Cairan</option>
                  <option value="Pendarahan">Pendarahan</option>
                </select>
              </div>
              <div class="col-md-5">
                <div class="col-md-7">
                  <label>Berapa Lama</label>
                </div>
                <div class="col-md-5">
                  <input type="text" name="keluhan_utama_bulan" id="keluhan_utama_bulan" class="form-control" style="width: 80%;">
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-offset-5 col-md-2">
                <div class="col-md-offset-1 col-md-11">
                  <label>&nbsp;Pendarahan</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="col-md-5">
                  <input type="checkbox" value="true" name="pendarahan_sedikit" id="pendarahan_sedikit"> Sedikit
                </div>
                <div class="col-md-5">
                  <input type="checkbox" value="true" name="pendarahan_banyak" id="pendarahan_banyak"> Banyak
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-offset-2 col-md-4">
                <div class="col-md-5">
                  <input type="checkbox" value="true" name="mual" id="mual"> Mual
                </div>
                <div class="col-md-5">
                  <input type="checkbox" value="true" name="muntah" id="muntah"> Muntah
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-offset-2 col-md-4">
                <div class="col-md-5">
                  <input type="checkbox" value="true" name="pusing" id="pusing"> Pusing
                </div>
                <div class="col-md-5">
                  <input type="checkbox" value="true" name="perut_sakit" id="perut_sakit"> Perut Sakit
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">HPHT</label>
                  </div>
                </div>
                <div class='input-group date col-md-8' id='datepicker5'>
                  <input type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" name="hpht" id="hpht2">
                  <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">HPL</label>
                  </div>
                </div>
                <div class='input-group date col-md-8' id='datepicker6'>
                  <input type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" name="hpl" id="hpl2">
                  <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
              </div>
            </div>
          </div>
          <br>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Riwayat Kehamilan, Persalinan dan Nifas yang Lalu</h4>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">G</label>
                  </div>
                </div>
                <div class='input-group date col-md-8'>
                  <input type='text' class="form-control" name="g_obstet" id="g_obstet">
                </div>
              </div>
              <div class="col-md-4">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">P</label>
                  </div>
                </div>
                <div class='input-group date col-md-8'>
                  <input type='text' class="form-control" name="p_obstet" id="p_obstet">
                </div>
              </div>
              <div class="col-md-4">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">A</label>
                  </div>
                </div>
                <div class='input-group date col-md-8'>
                  <input type='text' class="form-control" name="a_obstet" id="a_obstet">
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <table id="9dafa78dca4a01f50d21fbc884a5eecb" style="width:100%;" toolbar="#toolbar_tbe" idField="anamnesa_tb_id" rownumbers="true" fitColumns="true" singleSelect="true">
                <thead>
                  <tr>
                    <? foreach ($dataAnamnesaDetail as $fields) : ?>
                      <th field="<?= $fields['anamnesa_pilihan_id'] ?>" width="50"><?= $fields['anamnesa_pilihan_nama'] ?></th>
                    <? endforeach; ?>
                    <!-- // $dataAnamnesaDetail -->
                  </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
              <div id="toolbar_tbe">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newItem()">Baru</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('reload')">Refresh</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyItem()">Hapus</a>
              </div>
            </div>
          </div>
          <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="layani()">Refresh</a>
          <h2><b>II. OBJECTIVE</b></h2>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Keadaan Umum Pasien</label>
              </div>
              <div class="col-md-8">
                <select name="keadaan_umum_pasien" id="keadaan_umum_pasien" class="form-control">
                  <option value=""></option>
                  <option value="Baik">Baik</option>
                  <option value="Sedang">Sedang</option>
                  <option value="Kurang">Kurang</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Kesadaran</label>
              </div>
              <div class="col-md-8">
                <select name="kesadaran" id="kesadaran" class="form-control">
                  <option value=""></option>
                  <option value="Composmentis">Composmentis</option>
                  <option value="Somnolen">Somnolen</option>
                  <option value="Apatis">Apatis</option>
                  <option value="Coma">Coma</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Tekanan Darah Sistole</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="tekanan_darah_sistole" id="tekanan_darah_sistole" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>mm/Hg</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Tekanan Darah Diastole</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="tekanan_darah_diastole" id="tekanan_darah_diastole" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>mm/Hg</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Nadi</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="nadi" id="nadi" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>x/Menit</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Pernafasan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="pernafasan" id="pernafasan" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>x/Menit</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Suhu Badan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="suhu_badan" id="suhu_badan" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>°C</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Berat Badan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="berat_badan" id="berat_badan" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Kg</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Tinggi Badan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="tinggi_badan" id="tinggi_badan" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Cm</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Leher (TVJ)</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="leher_tvj" id="leher_tvj" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Cm</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Mata</label>
              </div>
              <div class="col-md-8">
                <select name="mata" id="mata" class="form-control">
                  <option value=""></option>
                  <option value="Normal">Normal</option>
                  <option value="Conjunctiva merah">Conjunctiva Merah</option>
                  <option value="Conjunctiva pucat">Conjunctiva Pucat</option>
                  <option value="Sklera ikteric">Sklera ikteric</option>
                  <option value="Pandangan kabur">Pandangan Kabur</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Leher</label>
              </div>
              <div class="col-md-8">
                <select name="leher" id="leher" class="form-control">
                  <option value=""></option>
                  <option value="Pembesaran kel. Tiroid">Pembersaran kel. tiroid</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Payudara</label>
              </div>
              <div class="col-md-8">
                <select name="payudara" id="payudara" class="form-control">
                  <option value=""></option>
                  <option value="Pengeluaran ASI">Pengeluaran ASI</option>
                  <option value="Putting datar/tenggelam">Putting datar/tenggelam</option>
                  <option value="Putting susu menonjol">Putting susu menonjol</option>
                  <option value="Lain-lain">Lain-lain</option>
                </select>
              </div>
            </div>
          </div>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Letak Anak</h4>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <input type="checkbox" value="true" name="kepala" id="kepala"> Kepala
              </div>
              <div class="col-md-2">
                <input type="checkbox" value="true" name="sungsang" id="sungsang"> Sungsang
              </div>
              <div class="col-md-2">
                <input type="checkbox" value="true" name="oblique" id="oblique"> Oblique
              </div>
              <div class="col-md-2">
                <input type="checkbox" value="true" name="lintang" id="lintang"> Lintang
              </div>
              <div class="col-md-4">
                <div class="col-md-5">
                  <label style="float: right;">Tinggi FU</label>
                </div>
                <div class="col-md-5">
                  <input type="text" name="tinggi_fu" id="tinggi_fu" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>cm</label>
                </div>
              </div>
            </div>
          </div>
          <label style="font-size: 18px; font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif"><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PEMERIKSAAN USG</i></label>
          <div class="form-group">
            <div class="col-md-12">
              <label style="font-size: 18px; font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif"><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HAMIL MUDA (TRIMESTER I)</i></label>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <table width="100%">
                <tr>
                  <td width="4%"></td>
                  <td>
                    <label><i style="color: black;">GS</i></label>
                  </td>
                  <td width="10%">
                    <select name="gs0" id="gs0" class="form-control">
                      <option value=""></option>
                      <option id="gs01" value="+"><i>+</i></option>
                      <option id="gs02" value="-"><i>-</i></option>
                    </select>
                  </td>
                  <td width="1%">&nbsp;</td>
                  <td width="10%">
                    <select name="gs1" id="gs1" class="form-control">
                      <option value=""></option>
                      <option id="gs11" value="1"><i>Tunggal</i></option>
                      <option id="gs12" value="2"><i>Kembar</i></option>
                    </select>
                  </td>
                  <td width="2%">
                    <label><i style="color: black;"> - </i></label>
                  </td>
                  <td>
                    <input type="text" name="gs2" id="gs2" style="width: 50px;" placeholder="" class="form-control">
                  </td>
                  <td>
                    <label><i style="color: black;">mm</i></label>
                  </td>
                  <td width="2%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">Fetal Pool</i></label>
                  </td>
                  <td width="10%">
                    <select name="fetal_pool" id="fetal_pool" class="form-control">
                      <option value=""></option>
                      <option id="fetal_pool1" value="+"><i>+</i></option>
                      <option id="fetal_pool2" value="-"><i>-</i></option>
                    </select>
                  </td>
                  <td width="2%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">Fetus</i></label>
                  </td>
                  <td width="10%">
                    <select name="fetus" id="fetus" class="form-control">
                      <option value=""></option>
                      <option id="fetus1" value="+"><i>+</i></option>
                      <option id="fetus2" value="-"><i>-</i></option>
                    </select>
                  </td>
                  <td width="20%">&nbsp;</td>
                </tr>
              </table>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <table width="100%">
                <tr>
                  <td width="4%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">CRL</i></label>
                  </td>
                  <td>
                    <input type="text" name="crl" id="crl" style="width: 50px;" class="form-control">
                  </td>
                  <td>
                    <label><i style="color: black;">mm</i></label>
                  </td>
                  <td width="3%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">DJJ</i></label>
                  </td>
                  <td>
                    <select name="djj" id="djj" class="form-control" style="width: 50px;">
                      <option value=""></option>
                      <option id="djj1" value="+">Hidup</option>
                      <option id="djj2" value="-">Mati</option>
                    </select>
                  </td>
                  <td>
                    <label><i style="color: black;">GA</i></label>
                  </td>
                  <td>
                    <input type="text" name="usia_kehamilan_minggu" style="width: 50px;" id="usia_kehamilan_minggu" class="form-control">
                  </td>
                  <td>
                    <label><i style="color: black;">Minggu</i></label>
                  </td>
                  <td>
                    <input type="text" name="usia_kehamilan_hari" style="width: 50px;" id="usia_kehamilan_hari" class="form-control">
                  </td>
                  <td>
                    <label><i style="color: black;">Hari</i></label>
                  </td>
                  <td width="3%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">HPL</i></label>
                  </td>
                  <td>
                    <div class='input-group date col-md-8' id='datepicker7'>
                      <input type='text' class="form-control" style="width: 100px;" data-inputmask="'alias': 'dd-mm-yyyy'" name="hpl_muda" id="hpl_muda">
                      <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <label style="font-size: 18px; font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif"><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HAMIL TRIMESTER II-III</i></label>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label>&nbsp; <i>Janin</i></label>
              </div>
              <div class="col-md-6">
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="janin_tunggal" id="janin_tunggala"> <i>Tunggal</i>
                </div>
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="janin_kembar" id="janin_kembara"> <i>Kembar</i>
                </div>
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="janin_hidup" id="janin_hidupa"> <i>Hidup</i>
                </div>
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="janin_iufd" id="janin_iufda"> <i>IUFD</i>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label>&nbsp; <i>Letak Janin</i></label>
              </div>
              <div class="col-md-9">
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="letak_janin_kepala" id="letak_janin_kepalaa"> <i>Kepala</i>
                </div>
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="letak_janin_sungsang" id="letak_janin_sungsanga"> <i>Sungsang</i>
                </div>
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="letak_janin_melintang" id="letak_janin_melintanga"> <i>Melintang</i>
                </div>
                <div class="col-md-3">
                  <input type="checkbox" value="true" name="letak_janin_oblique" id="letak_janin_obliquea"> <i>Oblique</i>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <table width="100%">
                <tr>
                  <td width="3%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">Ketuban</i></label>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="ketubah_cukup" id="ketubah_cukupa"> <i style="color: black;">Cukup</i>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="ketuban_kurang" id="ketuban_kuranga"> <i style="color: black;">Kurang</i>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="ketuban_banyak" id="ketuban_banyaka"> <i style="color: black;">Banyak</i>
                  </td>
                  <td>
                    <label> <i style="color: black;">AFI</i></label>
                  </td>
                  <td>
                    <input type="text" name="afi" id="afi" style="width: 50px;" class="form-control">
                  </td>
                  <td width="15%">&nbsp;</td>
                </tr>
              </table>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <table width="100%">
                <tr>
                  <td width="2%">&nbsp;</td>
                  <td>
                    <label style="color: black;">&nbsp; <i>Plasenta</i></label>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="insersi_corpus" id="insersi_corpusa"> <i style="color: black;">Corpus</i>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="insersi_sbr" id="insersi_sbra"> <i style="color: black;">SBR</i>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="insersi_anterior" id="insersi_anteriora"> <i style="color: black;">Ant</i>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="insersi_posterior" id="insersi_posteriora"> <i style="color: black;">Post</i>
                  </td>
                  <td>
                    <input type="checkbox" value="true" name="insersi_fudus" id="insersi_fudusa"> <i style="color: black;">Fundus</i>
                  </td>
                  <td>
                    <label>&nbsp; <i style="color: black;">Grade</i></label>
                  </td>
                  <td>
                    <select name="grade" id="grade" class="form-control">
                      <option value=""></option>
                      <option id="grade1" value="I">I</option>
                      <option id="grade2" value="II">II</option>
                      <option id="grade3" value="III">III</option>
                      <option id="grade4" value="IV">IV</option>
                      <option id="grade5" value="V">V</option>
                    </select>
                  </td>
                  <td width="15%">&nbsp;</td>
                </tr>
              </table>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-3">
                <div class="col-md-3">
                  <label><i>BPD</i></label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="bpd" id="bpd" class="form-control">
                </div>
                <div class="col-md-2">
                  <label><i>cm</i></label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="col-md-2">
                  <label><i>FL</i></label>
                </div>
                <div class="col-md-7">
                  <input type="text" name="fl" id="fl" class="form-control">
                </div>
                <div class="col-md-2">
                  <label><i>mm</i></label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="col-md-3">
                  <label><i>AC</i></label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="ac" id="ac" class="form-control">
                </div>
                <div class="col-md-2">
                  <label><i>mm</i></label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="col-md-3">
                  <label><i>EFW</i> </label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="efw" id="efw" class="form-control">
                </div>
                <div class="col-md-2">
                  <label><i>gram</i></label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <table width="100%">
                <tr>
                  <td width="5%">&nbsp;</td>
                  <td width="3%">
                    <label><i style="color: black;">Usia Kehamilan</i></label>
                  </td>
                  <td width="5%">
                    <input type="text" name="usia_kehamilan_minggu1" id="usia_kehamilan_minggu1" style="width: 50px;" class="form-control">
                  </td>
                  <td width="3%">
                    <label><i style="color: black;">Minggu</i></label>
                  </td>
                  <td width="2%">&nbsp;</td>
                  <td width="5%">
                    <input type="text" name="usia_kehamilan_hari1" id="usia_kehamilan_hari1" style="width: 50px;" class="form-control">
                  </td>
                  <td width="3%">
                    <label><i style="color: black;">Hari</i></label>
                  </td>
                  <td width="2%">&nbsp;</td>
                  <td>
                    <label><i style="color: black;">HPL</i></label>
                  </td>
                  <td width="10%">
                    <div class='input-group date col-md-8' id='datepicker8'>
                      <input type='text' class="form-control" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" name="hpltp" id="hpltp">
                      <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                  </td>
                  <td width="10%">Jenis Kelamin</td>
                  <td width="15%">
                    <select class="form-control" name="jenis_kelamin" id="jenis_kelaminl">
                      <option></option>
                      <option value="Laki" id="jenis_kelaminLaki">Laki-laki</option>
                      <option value="Perempuan" id="jenis_kelaminPerempuan">Perempuan</option>
                    </select>
                  </td>
                  <td width="40%">&nbsp;</td>
                </tr>
              </table>
            </div>
          </div>

          <div class="col-md-12">
            <table width="100%">
              <tr>
                <td>Pemeriksaan Penunjang (Laboratorium, Radiologi)</td>
              </tr>
              <tr>
                <td width="45%">
                  <textarea class="form-control" id="pemeriksaanPenunjang" name="pemeriksaanPenunjang"></textarea>
                </td>
              </tr>
            </table>
          </div>

          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Lokalis</h4>
          <div class="col-md-8">
            <textarea class="form-control" name="status_lokalis" id="status_lokalis"></textarea>
          </div>
          <br>
          <div class="form-group">
          </div>
          <h2><b>III. ANALISA</b></h2>
          <div class="col-md-12 col-sm-8 col-xs-12">
            <table width="100%">
              <tr>
                <td style="font-size: 20px;" width="2%">G</td>

                <td width="1%">&nbsp;</td>
                <td width="5%">
                  <select name="g_analisa" id="g_analisa" class="form-control" readonly>
                    <option value=""></option>
                    <option id="g_analisa1" value="1">1</option>
                    <option id="g_analisa2" value="2">2</option>
                    <option id="g_analisa3" value="3">3</option>
                    <option id="g_analisa4" value="4">4</option>
                    <option id="g_analisa5" value="5">5</option>
                    <option id="g_analisa6" value="6">6</option>
                    <option id="g_analisa7" value="7">7</option>
                    <option id="g_analisa8" value="8">8</option>
                    <option id="g_analisa9" value="9">9</option>
                    <option id="g_analisa10" value="10">10</option>
                    <option id="g_analisa11" value="11">11</option>
                    <option id="g_analisa12" value="12">12</option>
                    <option id="g_analisa13" value="13">13</option>
                  </select>
                </td>
                <td width="1%">&nbsp;</td>
                <td style="font-size: 20px;" width="2%">P</td>
                <td width="5%">
                  <select name="p_analisa" id="p_analisa" class="form-control" readonly>
                    <option value=""></option>
                    <option id="p_analisa0" value="0">0</option>
                    <option id="p_analisa1" value="1">1</option>
                    <option id="p_analisa2" value="2">2</option>
                    <option id="p_analisa3" value="3">3</option>
                    <option id="p_analisa4" value="4">4</option>
                    <option id="p_analisa5" value="5">5</option>
                  </select>
                </td>
                <td width="1%">&nbsp;</td>
                <td style="font-size: 20px;" width="2%">A</td>
                <td width="5%">
                  <select name="a_analisa" id="a_analisa" class="form-control" readonly>
                    <option value=""></option>
                    <option id="a_analisa0" value="0">0</option>
                    <option id="a_analisa1" value="1">1</option>
                    <option id="a_analisa2" value="2">2</option>
                    <option id="a_analisa3" value="3">3</option>
                    <option id="a_analisa4" value="4">4</option>
                    <option id="a_analisa5" value="5">5</option>
                  </select>
                </td>
                <td width="5%">&nbsp;</td>
                <td width="15%"><input type="text" name="ket_diagnosa_satu" id="ket_diagnosa_satu" style="width: 150px;" class="form-control"></td>
                <!-- <td style="font-size: 20px;" width="3%">Mg</td> -->
                <td width="1%">&nbsp;</td>
                <td style="font-size: 20px;" width="1%">/</td>
                <td width="10%">
                  <select name="ket_diagnosa_dua" id="ket_diagnosa_dua" class="form-control">
                    <option value=""></option>
                    <option id="ket_diagnosa_duaT" value="T">Tunggal</option>
                    <option id="ket_diagnosa_duaG" value="G">Gemelli</option>
                  </select>
                </td>
                <td style="font-size: 20px;" width="2%">/</td>
                <td width="8%">
                  <select name="ket_diagnosa_tiga" id="ket_diagnosa_tiga" class="form-control">
                    <option value=""></option>
                    <option id="ket_diagnosa_tigaHidup" value="Hidup">Hidup</option>
                    <option id="ket_diagnosa_tigaIUFD" value="IUFD">IUFD</option>
                  </select>
                </td>
                <td width="2%">&nbsp;</td>
                <td width="8%">
                  <select name="ket_diagnosa_lima" id="ket_diagnosa_lima" class="form-control">
                    <option value=""></option>
                    <option id="ket_diagnosa_limaKepala" value="Kepala">Kepala</option>
                    <option id="ket_diagnosa_limaSungsang" value="Sungsang">Sungsang</option>
                    <option id="ket_diagnosa_limaMelintang" value="Melintang">Melintang</option>
                    <option id="ket_diagnosa_limaOblique" value="Oblique">Oblique</option>
                  </select>
                </td>
              </tr>
            </table>
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">&nbsp;</div>
          <div class="col-md-8 col-sm-12 col-xs-12">
            <textarea class="form-control" name="ket_diagnosa_empat" id="ket_diagnosa_empat"></textarea>
          </div>
          <br>
          <div class="form-group">
          </div>
          <h2><b>IV.DIAGNOSA</b></h2>
          <div class="col-md-8 col-sm-8 col-xs-12">
            <textarea class="form-control" name="diagnose_skr" id="diagnose_skr"></textarea>
          </div>
          <div class="form-group">
          </div>
          <h2><b>V. PLANNING</b></h2>
           <h4><b>(Pemeriksaan Penunjang, )</b></h4>
          <div class="col-md-8 col-sm-8 col-xs-12">
            <textarea class="form-control" name="planning_penatalaksanaan" id="planning_penatalaksanaan"></textarea>
          </div>

           <div class="form-group">
          </div>
          
          <div class="col-md-8 col-sm-8 col-xs-12" style="display: flex">
           <div class="col-md-6">
              <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Materi Edukasi :</h4>
             <input type="checkbox" name="diagnosa" id="diagnosa" value="true"> Diagnosa <br>
             <input type="checkbox" name="penjelasan_penyakit" id="penjelasan_penyakit" value="true"> Penjelasan penyakit (penyebab, tanda, gejala) <br>
             <input type="checkbox" name="pemeriksaan_penunjang" id="pemeriksaan_penunjang" value="true"> Pemeriksaan Penunjang <br>
             <input type="checkbox" name="terapi_edukasi" id="terapi_edukasi" value="true"> Terapi <br>
             

             <input type="checkbox" name="prognosa" id="prognosa" value="true"> Prognosa <br>
             <input type="checkbox" name="tindakan_medis" id="tindakan_medis" value="true"> Tindakan Medis <br>
             <input type="checkbox" name="terapi_alter" id="terapi_alter" value="true"> Terapi alternative <br>
             <input type="checkbox" name="konsul" id="konsul" value="true"> Konsul ke : <br>
             <input type="text" name="konsul_det" id="konsul_det" class="form-control" style="display: none" disabled>
             <input type="checkbox" name="edukasi_pulang" id="edukasi_pulang" value="true"> Edukasi sebelum pulang <br>
             <input type="checkbox" name="edukasi_lain" id="edukasi_lain" value="true"> Lain lain : <br>
             <input type="text" name="lain_det" id="lain_det" class="form-control" style="display: none" disabled>
           </div>
           <div class="col-md-6">
              <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edukasi :</h4>
             <input type="checkbox" name="memahamiMateri" id="memahamiMateri" value="true"> Memahami Materi <br>
             <input type="checkbox" name="bisaMengulang" id="bisaMengulang" value="true"> Bisa Mengulang Materi <br>
             <input type="checkbox" name="membatasiMateri" id="membatasiMateri" value="true"> Membatasi Materi <br>
             <input type="checkbox" name="pengulanganMateri" id="pengulanganMateri" value="true"> Butuh Pengulangan Materi <br>
             
             <input type="checkbox" name="butuhLeaflet" id="butuhLeaflet" value="true"> Butuh Leaflet <br>

             
             <input type="checkbox" name="lain_lainEdukasi" id="lain_lainEdukasi" value="true"> Lain lain : <br>
             <input type="text" name="lainEd_det" id="lainEd_det" class="form-control" style="display: none" disabled>
           </div>
          </div>

         

          <div class="form-group"></div>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lap Tindakan</h4>
          <div class="col-md-8 col-sm-8 col-xs-12">
            <textarea class="form-control" name="lap_tindakan" id="lap_tindakan"></textarea>
          </div>
          <input type="hidden" name="nama_pasien" id="nama_pasien_ob">
          <input type="hidden" name="nomor_rm" id="nomor_rm_ob">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="layani()">Refresh</a>
          <div class="item form-group">
            <div class="col-md-2 col-sm-2 col-xs-12">
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <div class="col-md-2 col-sm-2 col-xs-12">
              <button type="button" id="cetak-resume" class="btn btn-success">Cetak Asmed</button>
            </div>
            
            <div class="col-md-2 col-sm-2 col-xs-12" style="margin-top: 10px">
              <button type="button" id="cetak-usg" class="btn btn-success">Cetak USG</button>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <h2><b>I. SUBJECTIVE</b></h2>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kasus Ginekologi</h4>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label>Keluhan Utama</label>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="keluhan_utama" id="keluhan_utama" style="min-width: 230px; min-height: 200px"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-3">
                <label class="col-md-offset-2">Haid Teratur</label>
              </div>
              <div class="col-md-2">
                <input type="checkbox" name="haid_teratur_ya" id="haid_teratur_yaaa" value="true">Ya
              </div>
              <div class="col-md-4">
                <div class="col-md-4">
                  <label>Lamanya</label>
                </div>
                <div class="col-md-6">
                  <input type="text" class="form-control" name="lamanya_haid_teratur">
                </div>
                <div class="col-md-2 col-sm-12 col-xs-12">
                  <label>Hari</label>
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <div class="col-md-3">
                <label class="col-md-offset-2">&nbsp;</label>
              </div>
              <div class="col-md-2">
                <input type="checkbox" name="haid_teratur_tidak" id="haid_teratur_tidak" value="true">Tidak
              </div>
              <div class="col-md-4">
                <div class="col-md-4">
                  <label>Nyeri</label>
                </div>
                <div class="col-md-6">
                  <select class="form-control" name="haid_nyeri" id="haid_nyeri">
                    <option value=""></option>
                    <option value="y">Ya</option>
                    <option value="n">Tidak</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label>Gangguan Haid</label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-8">
                
                <table width="100%">
                  <tr>
                    <td width="3%">&nbsp;</td>
                    <td width="7%" style="color:black;">Aminore</td>
                    <td width="3%"><input type="checkbox" name="amenore_ya" id="amenore_ya" value="y"></td>
                    <td width="5%"><input type="text" name="amenore_hari" id="amenore_hari" value="" placeholder="" class="form-control"></td>
                    <td width="2%">&nbsp;</td>
                    <td width="2%" style="color:black;">Hari</td>
                    <td width="2%">&nbsp;</td>
                    <td width="5%" hidden><input type="text" name="amenore_bulan" id="amenore_bulan" value="" placeholder="" class="form-control"></td>
                    <td width="2%">&nbsp;</td>
                    <td width="5%" style="color:black;">Menopause</td>
                    <td width="2%">&nbsp;</td>
                    <td width="5%"><input type="checkbox" name="menopause" id="menopause" value="y"></td>
                    <td width="20%">&nbsp;</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-5">
                <div class="col-md-offset-1 col-md-3">
                  <label>Haid Lama</label>
                </div>
                <div class="col-md-5">
                  <input type="text" name="haid_lama_hari" id="haid_lama_hari" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Hari</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-7">
                <div class="col-md-offset-1 col-md-6">
                  <label>Haid Lama dan Banyak</label>
                </div>
                <div class="col-md-3">
                  <input type="text" name="haid_lama_banyak_hari" id="haid_lama_banyak_hari" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Hari</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-5">
                <div class="col-md-offset-1 col-md-4">
                  <label>Haid : 1 Bulan</label>
                </div>
                <div class="col-md-5">
                  <input type="text" name="haid_lama_bulan" id="haid_lama_bulan" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Kali</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-offset-3 col-md-5">
                <div class="col-md-5">
                  <label>Terus menerus berapa lama</label>
                </div>
                <div class="col-md-5">
                  <input type="text" name="terus_menerus_lama" id="terus_menerus_lama" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Hari</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label>Pendarahan</label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-3">
                <div class="col-md-offset-1 col-md-5">
                  <input type="checkbox" name="sedikit" id="sedikit" value="true"> Sedikit
                </div>
                <div class="col-md-6">
                  <input type="checkbox" name="banyak" id="banyak" value="true"> Banyak
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Terus menerus berapa lama</label>
                </div>
                <div class="col-md-4">
                  <input type="text" name="terus_menerus_hari" id="terus_menerus_hari" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-2">
                  <label>Hari</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label>Flour Albus</label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-10">
                <div class="col-md-2">
                  <div class="col-md-offset-2">
                    <input type="checkbox" name="gatal" id="gatal" value="true"> Gatal
                  </div>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="tidak_gatal" id="tidak_gatal" value="true"> Tidak Gatal
                </div>
                <div class="col-md-8">
                  <div class="col-md-2">
                    <input type="checkbox" name="bau" id="bau" value="true"> Bau
                  </div>
                  <div class="col-md-3">
                    <input type="checkbox" name="tidak_bau" id="tidak_bau" value="true"> Tidak Bau
                  </div>
                  <!-- <div class="col-md-7">
                    <div class="col-md-5">
                      <label>Warna</label>
                    </div>
                    <div class="col-md-7">
                      <input type="text" name="warna" id="warna" class="form-control">
                    </div>
                  </div> -->
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-offset-1 col-md-1">
                <label>Warna</label>
              </div>
              <div class="col-md-3">
                <input type="text" name="warna" id="warna" class="form-control">
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-12">
                <div class="col-md-2">
                  <div class="col-md-5">
                    <input type="checkbox" name="lainnya" id="lainnya" value="true"> Lainnya
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="col-md-offset-1 col-md-11">
                    <label>Sudah Berapa Lama</label>
                  </div>
                </div>
                <div class="col-md-4">
                  <input type="text" name="sudah_berapa_lama" id="sudah_berapa_lama" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-2">
                  <label>Hari</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <div class="col-md-offset-2">
                  <input type="checkbox" name="perut_sakitt" id="perut_sakitt" value="true"> Perut Sakit
                </div>
              </div>
              <div class="col-md-10">
                <div class="col-md-2">
                  <input type="checkbox" name="tumor" id="tumor" value="true"> Tumor
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="myom_uteri" id="myom_uteri" value="true"> Myom Uteri
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="kista_ovari" id="kista_ovari" value="true"> Kista Ovari
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="ca_cx" id="ca_cx" value="true"> Ca CX
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="lainnyaa" id="lainnyaa" value="true"> Lainnya
                </div>
              </div>
            </div>
          </div>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Riwayat Kehamilan, Persalinan dan Nifas yang Lalu</h4>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">G</label>
                  </div>
                </div>
                <div class='input-group date col-md-8'>
                  <input type='text' class="form-control" name="g_ginek" id="g_ginek">
                </div>
              </div>
              <div class="col-md-4">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">P</label>
                  </div>
                </div>
                <div class='input-group date col-md-8'>
                  <input type='text' class="form-control" name="p_ginek" id="p_ginek">
                </div>
              </div>
              <div class="col-md-4">
                <div class="col-md-4">
                  <div class="col-md-12">
                    <label style="float: right;">A</label>
                  </div>
                </div>
                <div class='input-group date col-md-8'>
                  <input type='text' class="form-control" name="a_ginek" id="a_ginek">
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <table id="riwayat_kehamilan_ginek" style="width:100%;" toolbar="#toolbar_tbee" idField="anamnesa_tb_id" rownumbers="true" fitColumns="true" singleSelect="true">
                <thead>
                  <tr>
                    <? foreach ($dataAnamnesaDetail as $fields) : ?>
                      <th field="<?= $fields['anamnesa_pilihan_id'] ?>" width="50"><?= $fields['anamnesa_pilihan_nama'] ?></th>
                    <? endforeach; ?>
                    <!-- // $dataAnamnesaDetail -->
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
              <div id="toolbar_tbee">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newItemGinek()">Baru</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#riwayat_kehamilan_ginek').datagrid('reload')">Refresh</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyItemGinek()">Hapus</a>
              </div>
            </div>
          </div>
          <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="layani()">Refresh</a>
          <h2><b>II. OBJECTIVE</b></h2>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Keadaan Umum Pasien</label>
              </div>
              <div class="col-md-8">
                <select name="keadaan_umum_pasien_ginek" id="keadaan_umum_pasien_ginek" class="form-control">
                  <option value=""></option>
                  <option value="Baik">Baik</option>
                  <option value="Sedang">Sedang</option>
                  <option value="Kurang">Kurang</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Kesadaran</label>
              </div>
              <div class="col-md-8">
                <select name="kesadaran_ginek" id="kesadaran_ginek" class="form-control">
                  <option value=""></option>
                  <option value="Composmentis">Composmentis</option>
                  <option value="Somnolen">Somnolen</option>
                  <option value="Apatis">Apatis</option>
                  <option value="Coma">Coma</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Tekanan Darah Sistole</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="tekanan_darah_sistole_ginek" id="tekanan_darah_sistole_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>mm/Hg</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Tekanan Darah Diastole</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="tekanan_darah_diastole_ginek" id="tekanan_darah_diastole_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>mm/Hg</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Nadi</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="nadi_ginek" id="nadi_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>x/Menit</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Pernafasan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="pernafasan_ginek" id="pernafasan_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>x/Menit</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Suhu Badan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="suhu_badan_ginek" id="suhu_badan_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>°C</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Berat Badan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="berat_badan_ginek" id="berat_badan_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Kg</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Tinggi Badan</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="tinggi_badan_ginek" id="tinggi_badan_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Cm</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-4">
                  <label style="float: left;">Leher (TVJ)</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="leher_tvj_ginek" id="leher_tvj_ginek" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Cm</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Mata</label>
              </div>
              <div class="col-md-8">
                <select name="mata_ginek" id="mata_ginek" class="form-control">
                  <option value=""></option>
                  <option value="Normal">Normal</option>
                  <option value="Conjunctiva merah">Conjunctiva Merah</option>
                  <option value="Conjunctiva pucat">Conjunctiva Pucat</option>
                  <option value="Sklera ikteric">Sklera ikteric</option>
                  <option value="Pandangan kabur">Pandangan Kabur</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Leher</label>
              </div>
              <div class="col-md-8">
                <select name="leher_ginek" id="leher_ginek" class="form-control">
                  <option value=""></option>
                  <option value="Pembesaran kel. Tiroid">Pembersaran kel. tiroid</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-2">
                <label style="float: left;">Payudara</label>
              </div>
              <div class="col-md-8">
                <select name="payudara_ginek" id="payudara_ginek" class="form-control">
                  <option value=""></option>
                  <option value="Pengeluaran ASI">Pengeluaran ASI</option>
                  <option value="Putting datar/tenggelam">Putting datar/tenggelam</option>
                  <option value="Putting susu menonjol">Putting susu menonjol</option>
                  <option value="Lain-lain">Lain-lain</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pemeriksaan Penunjang</label>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="pemeriksaanPenunjang_g" id="pemeriksaanPenunjang_g"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;USG Ginekologi</h4>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="usg_ginekologi" id="usg_ginekologi"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Lokalis</h4>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="status_lokalis_ginekologi" id="status_lokalis_ginekologi"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pemeriksaan Inspekulo/VT</label>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="pemerisaan_dalam_vt" id="pemerisaan_dalam_vt"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <h2><b>III. ANALISA</b></h2>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="analisa_diagnosaa" id="analisa_diagnosaa_ginek"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <h2><b>IV. DIAGNOSA</b></h2>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="diagnose_gynek" id="diagnose_gynek"></textarea>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <h2><b>IV. PLANNING</b></h2>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="planning_penatalaksanaan_ginek" id="planning_penatalaksanaan_ginek"></textarea>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Materi Edukasi :</label>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
               <input type="checkbox" name="diagnosa" id="diagnosa_g" value="true"> Diagnosa <br>
               <input type="checkbox" name="penjelasan_penyakit" id="penjelasan_penyakit_g" value="true"> Penjelasan penyakit (penyebab, tanda, gejala) <br>
               <input type="checkbox" name="pemeriksaan_penunjang" id="pemeriksaan_penunjang_g" value="true"> Pemeriksaan Penunjang <br>
               <input type="checkbox" name="terapi_edukasi" id="terapi_edukasi_g" value="true"> Terapi <br>
               
               
               <input type="checkbox" name="prognosa" id="prognosa_g" value="true"> Prognosa <br>
               <input type="checkbox" name="tindakan_medis" id="tindakan_medis_g" value="true"> Tindakan Medis <br>
               <input type="checkbox" name="terapi_alter" id="terapi_alter_g" value="true"> Terapi alternative <br>
               <input type="checkbox" name="konsul" id="konsul_g" value="true"> Konsul ke : <br>
               <input type="text" name="konsul_det" id="konsul_det_g" class="form-control" style="display: none" disabled>
               <input type="checkbox" name="edukasi_pulang" id="edukasi_pulang_g" value="true"> Edukasi sebelum pulang <br>
               <input type="checkbox" name="edukasi_lain" id="edukasi_lain_g" value="true"> Lain lain : <br>
               <input type="text" name="lain_det" id="lain_det_g" class="form-control" style="display: none" disabled>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edukasi :</label>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
               <input type="checkbox" name="memahamiMateri" id="memahamiMateri_g" value="true"> Memahami Materi <br>
               <input type="checkbox" name="butuhLeaflet" id="butuhLeaflet_g" value="true"> Butuh Leaflet <br>
               <input type="checkbox" name="membatasiMateri" id="membatasiMateri_g" value="true"> Membatasi Materi <br>
               <input type="checkbox" name="pengulanganMateri" id="pengulanganMateri_g" value="true"> Butuh Pengulangan Materi <br>
               <input type="checkbox" name="bisaMengulang" id="bisaMengulang_g" value="true"> Bisa Mengulang Materi <br>

               
               <input type="checkbox" name="lain_lainEdukasi" id="lain_lainEdukasi_g" value="true"> Lain lain : <br>
               <input type="text" name="lainEd_det" id="lainEd_det_g" class="form-control" style="display: none" disabled>
              </div>
            </div>
          </div>

           

          <div class="form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tindakan</label>
              </div>
              <div class="col-md-6 col-sm-8 col-xs-12">
                <textarea class="form-control" name="tindakan" id="tindakan"></textarea>
              </div>
            </div>
          </div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
        
          <div class="clearfix">&nbsp;</div>
          <div class="clearfix">&nbsp;</div>
          <div class="form-group">
            <div class="col-md-12">
              <button type="button" id="cetakBridge" class="btn btn-success pull-left">Cetak INACBG</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>

  $("input#konsul").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("input#konsul_det").css("display", "block");
        $('input#konsul_det').attr('disabled', false);
    }
    else{
        $("input#konsul_det").css("display", "none");
        $('input#konsul_det').attr('disabled', false);
    }

  });

  $("input#edukasi_lain").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("input#lain_det").css("display", "block");
        $('input#lain_det').attr('disabled', false);
    }
    else{
        $("input#lain_det").css("display", "none");
        $('input#lain_det').attr('disabled', false);
    }
    
  });

  $("input#lain_lainEdukasi").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("input#lainEd_det").css("display", "block");
        $('input#lainEd_det').attr('disabled', false);
    }
    else{
        $("input#lainEd_det").css("display", "none");
        $('input#lainEd_det').attr('disabled', false);
    }
    
  });

  $("input#konsul_g").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("input#konsul_det_g").css("display", "block");
        $('input#konsul_det_g').attr('disabled', false);
    }
    else{
        $("input#konsul_det_g").css("display", "none");
        $('input#konsul_det_g').attr('disabled', false);
    }

  });

  $("input#edukasi_lain_g").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("input#lain_det_g").css("display", "block");
        $('input#lain_det_g').attr('disabled', false);
    }
    else{
        $("input#lain_det_g").css("display", "none");
        $('input#lain_det_g').attr('disabled', false);
    }
    
  });

  $("input#lain_lainEdukasi_g").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("input#lainEd_det_g").css("display", "block");
        $('input#lainEd_det_g').attr('disabled', false);
    }
    else{
        $("input#lainEd_det_g").css("display", "none");
        $('input#lainEd_det_g').attr('disabled', false);
    }
    
  });

  $('#cetak-usg').click(function() {
    var data = $('#form_obgyn').serialize();
    // BukaWindow('cetak_usg.php?' + data, 'cetak usg');
    window.open('cetak_usg.php?' + data, '_blank');
  });
  $('#cetak-resume').click(function() {
    var data = $('#form_obgyn').serialize();
    // BukaWindow('cetak_usg.php?' + data, 'cetak usg');
    window.open('cetak_resume_poli.php?' + data, '_blank');
  });
  $('#cetak-resume-med').click(function() {
     var id_rawat = $('#form_obgyn').find("#asd").val();
    BukaWindow('cetak_bpjs.php?id=' + id_rawat, "Resume");
  });
  $('#cetakBridge').click(function() {
     var id_rawat = $('#form_obgyn').find("#asd").val();
    BukaWindow('cetak_bridge.php?id=' + id_rawat, "Resume");
  });
</script>