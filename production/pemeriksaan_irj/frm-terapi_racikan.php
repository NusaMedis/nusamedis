<?php 
    require_once("../penghubung.inc.php");
    require_once($LIB."login.php");
    require_once($LIB."encrypt.php");
    require_once($LIB."datamodel.php");
    require_once($LIB."tampilan.php");
    //INISIALISASI LIBRARY
    $enc = new textEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();
    $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
    $table = new InoTable("table1","100%","center");

    //$depNama = $auth->GetDepNama(); 
    $userName = $auth->GetUserName();

 ?>

<form action="post">
					<table id="tb_terapi_racikan" title="Perawatan Terapi Racikan" style="width:100%;" toolbar="#toolbar_terapi_racikan" idField="rawat_terapi_racikan_id" rownumbers="true" fitColumns="true" singleSelect="true">
					<thead>
					  <tr>
						<td valign="top">Jenis Racikan</td>
						<td colspan="2">
							<?php 
								$sql = "select * from apotik.apotik_jenis_racikan";
								$dataJenisRacikan = $dtaccess->FetchAll($sql);
							?>
                            <select class="form-control select2" name="jenis_racikan" style="width: 100%;">
                                <option value="">Pilih Jenis Racikan</option>
                                <? foreach($dataJenisRacikan as $JenisRacikan): ?>
                                    <option value="<?= $JenisRacikan["jenis_racikan_id"]?>"><?=$JenisRacikan["jenis_racikan_nama"]?></option>
                                <? endforeach;?>
                            </select>
						</td>
					</tr>
					<tr>
						<td valign="top" >Jumlah</td>
						<td colspan="2"><input type="text" name="rawat_terapi_racikan_jumlah" class="form-control"></td>
					</tr>
					<tr>
						<td valign="top">Satuan Jadi</td>
						<td colspan="2">
							<?php 
								$sql = "select * from logistik.logistik_item_satuan where satuan_tipe = 'J'";
								$dataSatuanJadi = $dtaccess->FetchAll($sql);
							?>
                            <select class="form-control select2" name="satuan_jadi" style="width: 100%;">
                                <option value="">Pilih Satuan Jadi</option>
                                <? foreach($dataSatuanJadi as $SatuanJadi): ?>
                                    <option value="<?= $SatuanJadi["satuan_id"]?>"><?=$SatuanJadi["satuan_nama"]?></option>
                                <? endforeach;?>
                            </select>
						</td>
					</tr>
					<tr>
						<td valign="top">Dosis</td>
						<td colspan="2">
							<?php 
								$sql = "select * from apotik.apotik_obat_petunjuk";
								$dataDosis = $dtaccess->FetchAll($sql);
							?>
                            <select class="form-control select2" name="dosis" style="width: 100%;">
                                <option value="">Pilih Dosis</option>
                                <? foreach($dataDosis as $Dosis): ?>
                                    <option value="<?= $Dosis["petunjuk_id"]?>"><?=$Dosis["petunjuk_nama"]?></option>
                                <? endforeach;?>
                            </select>
						</td>
					</tr>
					<tr>
						<td valign="top">Aturan Minum</td>
						<td colspan="2">
							<?php 
								$sql = "select * from apotik.apotik_aturan_minum";
								$dataAturanMinum = $dtaccess->FetchAll($sql);
							?>
                            <select class="form-control select2" name="aturan_minum" style="width: 100%;">
                                <option value="">Pilih Aturan Minum</option>
                                <? foreach($dataAturanMinum as $AturanMimum): ?>
                                    <option value="<?= $AturanMimum["aturan_minum_id"]?>"><?=$AturanMimum["aturan_minum_nama"]?></option>
                                <? endforeach;?>
                            </select>
						</td>
					</tr>
					<tr>
						<td valign="top">Aturan Pakai</td>
						<td colspan="2">
							<?php 
								$sql = "select * from apotik.apotik_aturan_pakai";
								$dataAturanPakai = $dtaccess->FetchAll($sql);
							?>
                            <select class="form-control select2" name="aturan_pakai" style="width: 100%;">
                                <option value="">Pilih Aturan Pakai</option>
                                <? foreach($dataAturanPakai as $AturanPakai): ?>
                                    <option value="<?= $AturanPakai["aturan_pakai_id"]?>"><?=$AturanPakai["aturan_pakai_nama"]?></option>
                                <? endforeach;?>
                            </select>
						</td>
					</tr>
					<tr>
						<td valign="top">Jam Aturan Pakai</td>
						<td colspan="2">
							<?php 
								$sql = "select * from apotik.apotik_jam_aturan_pakai";
								$dataJamAturanPakai = $dtaccess->FetchAll($sql);
							?>
                            <select class="form-control select2" name="jam_aturan_pakai" style="width: 100%;">
                                <option value="">Pilih Jam Aturan Pakai</option>
                                <? foreach($dataJamAturanPakai as $JamAturanPakai): ?>
                                    <option value="<?= $JamAturanPakai["jam_aturan_pakai_id"]?>"><?=$JamAturanPakai["jam_aturan_pakai_nama"]?></option>
                                <? endforeach;?>
                            </select>
						</td>
					</tr>
					</thead>
					<tbody>
					  
					</tbody>
					</table> 

    <div style="padding:5px 0;text-align:right;padding-right:30px">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save1Racikan(this)">Simpan</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="cancel1Racikan(this)">Cancel</a>
    </div>
<!-- 					<div id="toolbar_terapi_racikan">
						<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_terapi_racikan()">Baru</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('destroyRow')">Hapus</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('saveRow')">Simpan</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('cancelRow')">Cancel</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('reload')">Refresh</a>
					</div> -->
</form>
<script type="text/javascript">
    init_select2();
    function save1Racikan(target){
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        saveRacikan(index);
    }
    function cancel1Racikan(target){
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        cancelRacikan(index);
    }
</script>