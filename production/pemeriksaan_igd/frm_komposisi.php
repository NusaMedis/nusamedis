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
?>
<form method="post">
    <table class="dv-table" style="width:100%;background: transparent;padding:5px;margin-top:5px;">
        <tr>
            <td valign="top">Nama Obat</td>
            <td>
                <?
                $sql = "select item_id, item_nama, b.satuan_nama from logistik.logistik_stok_dep c 
                          left join logistik.logistik_item a on c.id_item = a.item_id 
                          left join logistik.logistik_item_satuan b on a.id_satuan_jual = b.satuan_id
                           WHERE c.id_gudang = '2' and c.stok_dep_saldo > 0 and item_racikan = 'n'
                           order by item_nama asc
                          ";
                $rs = $dtaccess->Execute($sql);
                $dataObat = $dtaccess->FetchAll($rs);

                ?>
                <select class="form-control select2" name="komposisi" style="width: 100%;">
                    <option value="">Pilih Nama Obat</option>
                    <? foreach ($dataObat as $terapi_racikan) : ?>
                        <option value="<?= $terapi_racikan["item_id"] ?>"><?= $terapi_racikan["item_nama"] ?></option>
                    <? endforeach; ?>
                </select>
            </td>
            <input type="hidden" name="id_rawat" value="">
            <input type="hidden" name="id_rawat_terapi_racikan" value="">

        </tr>
        <tr>
            <td valign="top">Satuan</td>
            <td colspan="2"><input type="text" name="dosis" class="form-control" required="true"></td>
        </tr>
    </table>
    <div style="padding:5px 0;text-align:right;padding-right:30px">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save1Komposisi(this)">Simpan</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="cancel1Komposisi(this)">Cancel</a>
    </div>
</form>
<script type="text/javascript">
    init_select2();

    function save1Komposisi(target) {
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        saveKomposisi(index);
    }

    function cancel1Komposisi(target) {
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        cancelKomposisi(index);
    }
</script>