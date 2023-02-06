<?php
  $sql = "SELECT * from klinik.klinik_alasan_kontrol";
  $dtAlasan = $dtaccess->FetchAll($sql);
?>
<script type="text/javascript" src="kontrol.js"></script>
<script>
 

</script>


  <div class="form-horizontal form-label-left">
    <div class="x_title">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <label class="col-md-11 col-sm-11 col-xs-11">
          <h2>Form Kontrol</h2>
        </label>
        <div class="col-md-1 col-sm-1 col-xs-1">
          <h2><?php echo $tglSekarang; ?></h2>
        </div>
      </div>
      <hr>
    </div>
    <form id="form_kontrol" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
      <input id="asd" type="hidden" name="asd" value="">
      <div class="col-md-12">
        <div class="col-md-12">
          <h2><b>1. Masih memerlukan perawatan/kontrol kembali di RS</b></h2>
          <div class="form-group">
                <div class="col-md-6">
                  <label>Alasan Kontrol : </label>
                </div>
          </div>
          <div class="form-group">
              <div class="col-md-6">
                <select class="form-control" id="alasan" name="alasan">
                  <?php for($i = 0; $i < count($dtAlasan); $i++) { ?>
                    <option value="<?=$dtAlasan[$i]['alasan_kontrol_id']?>"><?=$dtAlasan[$i]['alasan_kontrol_nama']?></option>
                 <?php } ?>
                    <option value="00">Lain-lain</option>
                </select>
                <input type="text" id="another" class="form-control" name="lain_lain" style="display: none">
              </div>
          </div>
          <div class="form-group">
                <div class="col-md-6">
                  <label>Tanggal Kontrol : </label>
                </div>
          </div>
          <div class="form-group">
              <div class='input-group date col-md-6' id='datepicker10'>
                  <input type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" name="tgl_kontrol" id="tgl_kontrol">
                  <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
          </div>
          <h2><b>2. Kembali Ke FKTP</b></h2>
          <div class="col-md-2">
                <div class="col-md-12">
                  <label style="float: right;">Pengobatan dengan obat-obatan yang telah diberikan :</label>
                </div>
              </div>
          <div class="col-md-12">
            <div class="editable" style="border: 1px solid #ccc; height: 200px; overflow: auto; font-size: 16px; padding: 10px;" id="values" contenteditable></div>
          </div>
          <br>
          <div class="form-group">
          </div>
          <h2><b>3. Sembuh</b></h2>
          <input type="hidden" name="nama_pasien" id="nama_pasien_ob">
          <input type="hidden" name="nomor_rm" id="nomor_rm_ob">
          <div class="item form-group">&nbsp;</div>
          <div class="item form-group">&nbsp;</div>
          <div class="item form-group">&nbsp;</div>
          <div class="item form-group">&nbsp;</div>
          <div class="item form-group">
            <div class="col-md-2 col-sm-2 col-xs-12">
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

          </div>
        </div>
        
      </div>
    </form>
  </div>


<script>
  $(document).ready(function(){ 
    $("select#alasan").change( function(){
    var valuee = $(this).val();
    if(valuee == '00'){
      $("input#another").css("display", "block");
    }
    else{
      $("input#another").css("display", "none");
    }
  });

  });
  

  $('#cetak-resume-med-anak').click(function() {
     var id_rawat = $('#form_anak').find("#asd").val();
    BukaWindow('cetak_bpjs.php?id=' + id_rawat, "Resume");
  });

  

</script>