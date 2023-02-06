<form action="post">
  <div id="modal_" class="modal fade modal_komposisi" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
          </button>
          <h4 class="modal-title" id="myModalLabel">Racikan <?php echo 'a'.$_POST['rawat_id']; ?></h4>
        </div>
        <div class="modal-body">
          <div id="toolbar_komposisi_">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newKomposisi()">Baru</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#komposisi_').datagrid('reload')">Refresh</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroykomposisi()">Hapus</a>
          </div>
          <table class="" id="komposisi_" style="width:100%;" data-options="toolbar:'#toolbar_komposisi_'">
            <thead >
              <tr>
                  <th width="50" field="komposisi">Komposisi</th>
                  <th width="50" field="jumlah">Jumlah</th>
              </tr>
            </thead>
          </table> 
        </div>
         <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>

          </div>
    </div>
  </div>
</form>
<script type="text/javascript">
    $(function () {
      var tb_komposisi = $('#komposisi_');              
      tb_komposisi.datagrid();
      tb_komposisi.datagrid({
        view: detailview,
        singleSelect:true, 
        fitColumns:true, 
        fit:false, 
        rownumbers:true,  
        striped:true,
        detailFormatter:function(index,row){
          return '<div class="ddv"></div>';
        },
        onExpandRow: function(index,row){
          var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
          ddv.panel({
              border:false,
              cache:true,
              href:'frm_komposisi.php?index='+index+'&row=',
              onLoad:function(){
                  tb_komposisi.datagrid('fixRowHeight',index);
                  tb_komposisi.datagrid('selectRow',index);
                  tb_komposisi.datagrid('getRowDetail',index).find('form').form('load',row);
                  $('[name="dosis"]').inputmask();

              },
          });
          tb_komposisi.datagrid('fixRowHeight',index)
        }
      });
    });

    $(function () {
      var tbe = $('#tb_terapi_racikan');              
      tbe.datagrid();
      tbe.datagrid({
        url: 'get_terapi_racikan.php?id_rawat=53cce7e11a6d598e714d7c3816fe2a43',
        view: detailview,
        singleSelect:true, 
        fitColumns:true, 
        fit:false, 
        rownumbers:true,  
        striped:true,
        detailFormatter:function(index,row){
          return '<div class="ddv"></div>';
        },
        onClickRow: function function_name(index, row) {
          openModal();
        },
        onExpandRow: function(index,row){
          var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
          ddv.panel({
              border:false,
              cache:true,
              href:'frm-terapi_racikan.php?index='+index+'&id_rawat=53cce7e11a6d598e714d7c3816fe2a43',
              onLoad:function(){
                  tbe.datagrid('fixRowHeight',index);
                  tbe.datagrid('selectRow',index);
                  tbe.datagrid('getRowDetail',index).find('form').form('load',row);
              },
          });
          tbe.datagrid('fixRowHeight',index)
        }
      });
    });

    function openModal() {
    var row = $('#tb_terapi_racikan').datagrid('getSelected');
    $('#rawat_terapi_racikan_id').val('ae8a34b749f7b2aeeae898bb0497319b');        
    $('#id_rawat').val('53cce7e11a6d598e714d7c3816fe2a43');        
    $('.modal_komposisi').modal('show');
    var tb_komposisi = $('#komposisi_');
    tb_komposisi.datagrid({
      url: 'get_komposisi.php?rawat_terapi_racikan_id=ae8a34b749f7b2aeeae898bb0497319b',
    })
  }

    function newKomposisi(){
      var rawat_terapi_racikan_id = $('#tb_terapi_racikan').val();  
      var dt = {
        isNewRecord:true,
        rawat_terapi_racikan_id: rawat_terapi_racikan_id,
        rawat_id: '<?= $_POST['rawat_id'] ?>', 
      }
      $('#komposisi_').datagrid('appendRow',dt);
        var index = $('#komposisi_').datagrid('getRows').length - 1;
        $('#komposisi_').datagrid('expandRow', index);
        $('#komposisi_').datagrid('selectRow', index);
        $('#komposisi_').datagrid('fixDetailRowHeight',index);
    }

    function saveKomposisi(index){
        var row = $('#komposisi_').datagrid('getRows')[index];
        var url = row.isNewRecord ? 'simpan-komposisi.php' : 'update-komposisi.php?func=update&id='+row.rawat_item_id;
        $('#komposisi_').datagrid('getRowDetail',index).find('form').form('submit',{
            url: url,
            onSubmit: function(param){
                param.rawat_id = row.rawat_id;
                return $(this).form('validate');
            },
            success: function(data){
                data = eval('('+data+')');
                data.isNewRecord = false;
                $('#komposisi_').datagrid('collapseRow',index);
                $('#komposisi_').datagrid('updateRow',{
                    index: index,
                    row: data
                });
            }
        });
      }

    function cancelKomposisi(index){
        var row = $('#komposisi_').datagrid('getRows')[index];
        if (row.isNewRecord){
            $('#komposisi_').datagrid('deleteRow',index);
        } else {
            $('#komposisi_').datagrid('collapseRow',index);
        }
    }

      function destroykomposisi(){
          var row = $('#komposisi_').datagrid('getSelected');
          if (row){
              $.messager.confirm('Konfirmasi','Anda Yakin?',function(r){
                  if (r){
                      var index = $('#komposisi_').datagrid('getRowIndex',row);
                      $.get('update-komposisi.php?func=destroy&id='+row.rawat_item_id,{},function(){
                          $('#komposisi_').datagrid('deleteRow',index);
                      });
                  }
              });
          }
      }
</script>