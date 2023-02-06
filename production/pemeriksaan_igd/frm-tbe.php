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

    $sql = "select * from klinik.klinik_anamnesa_pilihan where id_anamnesa=".QuoteValue(DPE_CHAR,$_GET['anamnesa_id'])." order by anamnesa_pilihan_urut asc";
    $rs = $dtaccess->Execute($sql);
    $dataAnamnesaPilihan = $dtaccess->FetchAll($rs);

    function slugify($text){
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '_', $text);
      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);
      // trim
      $text = trim($text, '_');
      // remove duplicate -
      $text = preg_replace('~-+~', '_', $text);
      // lowercase
      $text = strtolower($text);
       
      return $text;
    }
?>
<form method="post">
    <table class="dv-table" style="width:100%;background: transparent;padding:5px;margin-top:5px;">
        <?php foreach ($dataAnamnesaPilihan as $field): ?>
            <? $sql = "select * from klinik.klinik_anamnesa_pilihan_detail where id_anamnesa_pilihan=".QuoteValue(DPE_CHAR,$field['anamnesa_pilihan_id'])." order by anamnesa_pilihan_detail_urut asc";
                $rs = $dtaccess->Execute($sql);
                $dataAnamnesaPilihanDetail = $dtaccess->FetchAll($rs);
            ?>
            <? foreach ($dataAnamnesaPilihanDetail as $editor): ?>
                <tr>
                    <td valign="top"><?= $editor["anamnesa_pilihan_detail_nama"] ?></td>
                    <? if( $editor["anamnesa_pilihan_detail_id"] == 'e6eda8266a3cbd86bef650a8bb18d74e' ): ?>
                        <td><input type="text" name="<?= $field["anamnesa_pilihan_id"] ?>" class="form-control" required="true"></td>
                        <td align="center">&nbsp;Gram</td>
                    <? elseif( $editor["anamnesa_pilihan_detail_id"] == 'feaa8978b945d40471cc42a6d0be9731' ): ?>
                        <td><input type="text" name="<?= $field["anamnesa_pilihan_id"] ?>" class="form-control" required="true"></td>
                        <td align="center">&nbsp;Minggu</td>
                    <? elseif( $editor["anamnesa_pilihan_detail_tipe"] == 't' ): ?>
                        <td colspan="2"><input type="text" name="<?= $field["anamnesa_pilihan_id"] ?>" class="form-control" required="true"></td>
                    <? elseif( $editor["anamnesa_pilihan_detail_tipe"] == 'd' ): ?>
                        <td colspan="2"><input type="date" name="<?= $field["anamnesa_pilihan_id"] ?>" class="form-control" required="true"></td>
                    <? elseif( $editor["anamnesa_pilihan_detail_tipe"] == 'c' ): ?>
                        <td colspan="2">
                            <? 
                                $sql = "select * from klinik.klinik_anamnesa_pilihan_detail_pilihan where id_anamnesa_pilihan_detail=".QuoteValue(DPE_CHAR,$editor['anamnesa_pilihan_detail_id'])." order by anamnesa_pilihan_detail_pilihan_urut asc";
                                $rs = $dtaccess->Execute($sql);
                                $dataAnamnesaPilihanDetailPilihan = $dtaccess->FetchAll($rs); 
                            ?>
                            <select class="form-control" name="<?= $field["anamnesa_pilihan_id"] ?>">
                                <option value="" disabled="">Pilih <?= $editor["anamnesa_pilihan_detail_nama"]?></option>
                                <? foreach($dataAnamnesaPilihanDetailPilihan as $opts): ?>
                                    <option value="<?= $opts["anamnesa_pilihan_detail_pilihan_nama"]?>"><?= $opts["anamnesa_pilihan_detail_pilihan_nama"]?></option>
                                <? endforeach;?>
                            </select>
                        </td>
                    <? endif; ?>
                </tr>
            <?php endforeach; ?>
            <!-- //$dataAnamnesaPilihanDetail -->
        <?php endforeach; ?>
        <!-- //$dataAnamnesaPilihan -->
    </table>
    <div style="padding:5px 0;text-align:right;padding-right:30px">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save1(this)">Simpan</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="cancel1(this)">Cancel</a>
    </div>
</form>
<script type="text/javascript">
    function save1(target){
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        saveItem<?= $_GET['row'] ?>(index);
    }
    function cancel1(target){
        var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
        var index = parseInt(tr.attr('datagrid-row-index'));
        console.log(index)
        cancelItem<?= $_GET['row'] ?>(index);
    }
</script>