<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
//INISIALISASI LIBRARY
$enc = new textEncrypt();
$dtaccess = new DataAccess();
$auth = new CAuth();
$view = new CView($_SERVER["PHP_SELF"], $_SERVER['QUERY_STRING']);
$table = new InoTable("table1", "100%", "center");

//$depNama = $auth->GetDepNama(); 
$userName = $auth->GetUserName();
$depId = $auth->GetDepId();

$sql = "select dep_konf_reg_kelas_irj, dep_konf_tarif_jenis_pasien, dep_konf_header_klinik from  global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$dataKelasTarif = $dtaccess->Fetch($rs);

$sql = "select a.biaya_tarif_id, a.biaya_total, b.biaya_nama, b.biaya_id, a.is_cito from klinik.klinik_biaya_tarif a left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id left join klinik.klinik_kategori_tindakan c on b.biaya_kategori = c.kategori_tindakan_id  left join klinik.klinik_kategori_tindakan_header d on d.kategori_tindakan_header_id = c. id_kategori_tindakan_header left join klinik.klinik_biaya_poli e on d.kategori_tindakan_header_id = e.id_kategori_tindakan_header where " . QuoteValue(DPE_CHAR, date("Y-m-d")) . " >= a.biaya_tarif_tgl_awal  and " . QuoteValue(DPE_CHAR, date("Y-m-d")) . "<= a.biaya_tarif_tgl_akhir and a.id_kelas = " . QuoteValue(DPE_CHAR, $dataKelasTarif["dep_konf_reg_kelas_irj"]) . " and e.id_poli = " . QuoteValue(DPE_CHAR, $_GET['id_poli']);
if ($dataKelasTarif["dep_konf_tarif_jenis_pasien"] == 'y') $sql .= " and a.id_jenis_pasien = " . QuoteValue(DPE_CHAR, $_GET['jenis_pasien']);
$sql .= " order by b.biaya_nama asc";
$dataTindakan = $dtaccess->FetchAll($sql);
?>
<form method="post">
    <table class="dv-table" style="width:100%;background: transparent;padding:5px;margin-top:5px;">
        <tr>
            <td valign="top">Pemeriksaan</td>
            <td>
                <select class="form-control select2" id="erda" name="tindakan" style="width: 100%;">
                    <option value="">Pilih Pemeriksaan</option>
                    <?php foreach ($dataTindakan as $value) : ?>
                        <option value="<?= $value['biaya_id'] ?>"><?= $value['biaya_nama'] ?></option>
                    <?php endforeach ?>
                </select>
            </td>

        </tr>
    </table>
    <div style="padding:5px 0;text-align:right;padding-right:30px">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save2Komposisi(this)">Simpan</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="cancel2Komposisi(this)">Cancel</a>
    </div>
</form>
<script type="text/javascript">
    init_select2();

    function save2Komposisi(target) {
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        saveRujukan(index);
    }

    function cancel2Komposisi(target) {
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        cancelRujukan(index);
    }
</script>