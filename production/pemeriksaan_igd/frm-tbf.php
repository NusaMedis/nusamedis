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

    $sql = "select * from klinik.klinik_perawatan_terapi where rawat_item_id=".QuoteValue(DPE_CHAR,$_GET['rawat_item_id'])." order by rawat_item_urut asc";
    $rs = $dtaccess->Execute($sql);
    $dataPerawatanTerapi = $dtaccess->FetchAll($rs);

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
                <tr>
                    <td valign="top">Nama Obat</td>
                        <td><input type="text" name="<?= $field["anamnesa_pilihan_id"] ?>" class="form-control" required="true"></td>
                        </td>
                </tr>
                <tr>
                    <td valign="top">Dosis</td>
                     <td>
                            <? 
                                $sql = "select * from apotik.apotik_obat_petunjuk";
                                $rs = $dtaccess->Execute($sql);
                                $dataDosis = $dtaccess->FetchAll($rs); 
                            ?>
                            <select class="form-control" name="terapi_dosis">
                                <option value="" disabled="">Pilih Dosis</option>
                                <? foreach($dataDosis as $opts): ?>
                                    <option value="<?= $opts["petunjuk_id"]?>"><?= $opts["petunjuk_nama"]?></option>
                                <? endforeach;?>
                            </select>
                        </td>
                   
                </tr>

    </table>
    <div style="padding:5px 0;text-align:left;padding-left:30px">
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