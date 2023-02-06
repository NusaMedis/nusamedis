<?php
$sql = "SELECT usr_name, usr_id FROM global.global_auth_user where id_rol = '2'";
$dataDokter = $dtaccess->FetchAll($sql);
?>


<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<style type="text/css">
  .inlined {
    display: inline-block;
  }

  .long80 {
    width: 90%;
  }

  .bordered {
    border: 1px solid black;
  }

  div.tittle {
    padding: 10px;
    border-bottom: 1px solid black;
    margin: 0 0 20px 0;
  }

  .left-tittle {
    padding-top: 10px;
  }
</style>

<div title="Konsultasi Operasi" style="padding:5px">
  <div class="form-horizontal form-label-left">

    <form id="perimtaanOP" class="form-horizontal form-label-left bordered">

      <div class="col-md-12 tittle">
        <center>
          <h2><b>PERMINTAAN KONSULTASI</b></h2>
        </center>
      </div>

      <div class="col-md-12 ">

        <div class="col-md-6">
          <div class="form-group">
            <label class="col-md-1 left-tittle">Kepada </label>
          </div>
          <div class="form-group col-md-5">
            <input type="text" class="form-control" name="kepada">
          </div>
          <div class="form-group">&nbsp;</div>
          <div class="form-group">
            <label class="col-md-1 left-tittle" style="text-align: right">
              Yth / TS
            </label>
            <div class="col-md-5">
              <select class="form-control" name="kepada_dokter">
                <option value="">-PIlih Dokter-</option>
                <?php for ($i = 0; $i < count($dataDokter); $i++) { ?>
                  <option value="<?= $dataDokter[$i]['usr_id'] ?>"><?= $dataDokter[$i]['usr_name'] ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-1 left-tittle" style="text-align: right">
              di
            </label>
            <div class="col-md-5">
              <input type="text" class="form-control" name="tempat">
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <label class="col-md-5 left-tittle" style="text-align: right">Tgl / Jam</label>
          <div class="col-md-3 input-group date" id="tgl">
            <input type="text" class="form-control" name="waktu_tanggal">
            <span class="input-group-addon">
              <span class="glyphicon glyphicon-calendar"></span>
            </span>
          </div>
          <script type="text/javascript">
            $(function() {
              $('#tgl').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss'
              });
            });
          </script>
        </div>

      </div>

      <div class="col-md-12">
        <div class="form-group">
          <label>Ringkasan singkat pemeriksaan</label>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="ringkasan"></textarea>
        </div>
      </div>

      <div class="col-md-12">
        <div class="form-group">
          <label>Konsultasi</label>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="konsultasi"></textarea>
        </div>
      </div>

      <div class="col-md-12">
        <div class="col-md-6">
          <center>
            <b>Mengetahui Keluarga</b><br>
            <img id="imgTtd_kel" src="" width="250" style="display: none;">
            <canvas class="canvas" id="canvasTtd_kel" style="border: 1px solid silver; "></canvas><br>
            <input type="hidden" name="ttd_keluarga" class="form-control" value="">
            <p><input type="text" class="form-control texty" name="nama_ttd_keluarga" class="form-control" value="" style="text-align: center;"></p>
            <button type="button" class="btn btn-warning" id="resetTtd_kel"><i class="fa fa-refresh"></i> Reset</button>
          </center>
        </div>
        <div class="col-md-6">
          <center>
            <b>Paraf Dokter</b><br>
            <img id="ttd_dokter" src="" width="250">

            <input type="hidden" name="ttd_dokter" class="form-control" value="">
            <p><input type="text" class="form-control texty" id="nama_ttd_dokter" name="nama_ttd_dokter" class="form-control" value="" style="text-align: center;"></p>

          </center>
        </div>
      </div>

      <div class="col-md-12">
        <center>
          <button type="button" id="saveFormKonsul" class="btn btn-success">Simpan</button>
        </center>
      </div>

    </form>

    <form id="jawabanOP" class="form-horizontal form-label-left bordered">

      <div class="col-md-12 tittle">
        <center>
          <h2><b>JAWABAN KONSULTASI</b></h2>
        </center>
      </div>

      <div class="col-md-12 ">

        <div class="col-md-6">
          <div class="form-group">
            <label class="col-md-1 left-tittle">Kepada </label>
          </div>
          <div class="form-group col-md-5">
            <input type="text" class="form-control" name="kepada">
          </div>
          <div class="form-group">&nbsp;</div>
          <div class="form-group">
            <label class="col-md-1 left-tittle" style="text-align: right">
              Yth / TS
            </label>
            <div class="col-md-5">
              <select class="form-control" name="kepada_dokter">
                <option value="">-PIlih Dokter-</option>
                <?php for ($i = 0; $i < count($dataDokter); $i++) { ?>
                  <option value="<?= $dataDokter[$i]['usr_id'] ?>"><?= $dataDokter[$i]['usr_name'] ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-1 left-tittle" style="text-align: right">
              di
            </label>
            <div class="col-md-5">
              <input type="text" class="form-control" name="tempat">
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <label class="col-md-5 left-tittle" style="text-align: right">Tgl / Jam</label>
          <div class="col-md-3 input-group date" id="tgl2">
            <input type="text" class="form-control" name="waktu_tanggal">
            <span class="input-group-addon">
              <span class="glyphicon glyphicon-calendar"></span>
            </span>
          </div>
          <script type="text/javascript">
            $(function() {
              $('#tgl2').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss'
              });
            });
          </script>
        </div>

      </div>

      <div class="col-md-12">&nbsp;</div>

      <div class="col-md-12">
        <label class="col-md-2 left-tittle">Pasien telah kami periksa pada </label>
        <div class="col-md-2 input-group date" id="tgl3">
          <input type="text" class="form-control" name="waktu_tanggal_pemeriksaan">
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
        </div>
        <script type="text/javascript">
          $(function() {
            $('#tgl3').datetimepicker({
              format: 'YYYY-MM-DD HH:mm:ss'
            });
          });
        </script>
      </div>

      <div class="col-md-12">
        <div class="form-group">
          <label>Hasil Pemeriksaan</label>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="hasil_pemeriksaan"></textarea>
        </div>
      </div>

      <div class="col-md-12">
        <div class="form-group">
          <label>Kesumpulan</label>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="kesimpulan"></textarea>
        </div>
      </div>

      <div class="col-md-12">
        <div class="form-group">
          <label>Advis/Tindakan</label>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="advis"></textarea>
        </div>
      </div>

      <div class="col-md-12">
        <div class="col-md-6">

        </div>
        <div class="col-md-6">
          <center>
           

            <b>Paraf Dokter</b><br>
            <img id="ttd_dokter" src="" width="250">

            <input type="hidden" name="ttd_dokter" class="form-control" value="">
            <p><input type="text" class="form-control texty" id="nama_ttd_dokter" name="nama_ttd_dokter" class="form-control" value="" style="text-align: center;"></p>
          </center>
        </div>
      </div>

      <div class="col-md-12">
        <center>
          <button type="button" id="jawabForm" class="btn btn-success">Simpan</button>
          <button onclick="konsul_operasi_cetak()" style="display: none;" type="button" id="btnKonsulCetak" class="btn btn-success">Cetak</button>
        </center>
      </div>

    </form>
  </div>
</div>
<script type="text/javascript">
  function konsul_operasi_cetak() {
    var id_rawat = $("input#rawat_id").val();

    window.open('konsul_operasi_cetak.php?id_rawat=' + id_rawat + '', '_blank');
  }

  if ($("canvas#canvasTtd_kel").length > 0) {
    var canvasTtd_kel = document.querySelector("canvas#canvasTtd_kel");
    var signaturePadTtd_kel = new SignaturePad(canvasTtd_kel);
  }

  // if ($("canvas#canvasTtd_dok_jawab").length > 0) {
  //   var canvasTtd_dok_jawab = document.querySelector("canvas#canvasTtd_dok_jawab");
  //   var signaturePadTtd_dok_jawab = new SignaturePad(canvasTtd_dok_jawab);
  // }

  // if ($("canvas#canvasTtd_dok").length > 0) {
  //   var canvasTtd_dok = document.querySelector("canvas#canvasTtd_dok");
  //   var signaturePadTtd_dok = new SignaturePad(canvasTtd_dok);
  // }

  function nama_ttd_dokter(id) {

    $("form#" + id + "").find('#nama_ttd_dokter').autocomplete({
      serviceUrl: 'dataPerawat.php',
      paramName: 'q',
      transformResult: function(response) {
        var data = jQuery.parseJSON(response);
        return {
          suggestions: $.map(data, function(item) {
            return {
              value: item.usr_name,
              data: {
                usr_name: item.usr_name,
                usr_id: item.usr_id,

              }
            };
          })
        };
      },

      onSelect: function(suggestion) {
        $(this).val(suggestion.data.usr_name);

        $("form#" + id + "").find("input[name='ttd_dokter']").val(suggestion.data.usr_id);

      }
    });
  }

  nama_ttd_dokter("perimtaanOP");
  nama_ttd_dokter("jawabanOP");



  function check_img(img_name, form_id, img_id, canvas_id) {
    $.ajax({
      url: "../gambar/asset_ttd/" + img_name + ".jpg",
      type: 'GET',
      statusCode: {
        404: function() {
          $("form#" + form_id).find("img#" + img_id).css("display", "none");
          $("form#" + form_id).find("canvas#" + canvas_id).css("display", "inline-block");

        },
        200: function() {
          $("form#" + form_id).find("img#" + img_id).css("display", "inline-block");
          $("form#" + form_id).find("img#" + img_id).attr("src", "../gambar/asset_ttd/" + img_name + ".jpg");
          $("form#" + form_id).find("canvas#" + canvas_id).css("display", "none");
        },
        304: function() {
          $("form#" + form_id).find("img#" + img_id).css("display", "inline-block");
          $("form#" + form_id).find("img#" + img_id).attr("src", "../gambar/asset_ttd/" + img_name + ".jpg");
          $("form#" + form_id).find("canvas#" + canvas_id).css("display", "none");
        }
      },
      success: function() {



      }
    });
  }

  function get_dataFormKonsultasi(id_rawat) {
    $("form#perimtaanOP").find("input").val("");
    $("form#perimtaanOP").find("select").val("");
    $("form#perimtaanOP").find("textarea").val("");
     $("form#perimtaanOP").find("img#ttd_dokter").attr("src", "");

    $("form#jawabanOP").find("input").val("");
    $("form#jawabanOP").find("select").val("");
    $("form#jawabanOP").find("textarea").val("");
    $("form#jawabanOP").find("img#ttd_dokter").attr("src", "");

    $.post("konsultasi_action.php", {
      id_rawat: id_rawat,
      type: "get"
    }).done(function(data) {
      var data = JSON.parse(data);
      var permintaan = data.permintaan;
      var jawaban = data.jawaban;
      var context;

      $.each(permintaan, function(ind, val) {
        $("form#perimtaanOP").find("[name='" + ind + "']").val(val);
      });

      check_img(permintaan.ttd_keluarga, "perimtaanOP", "imgTtd_kel", "canvasTtd_kel");
      // check_img(permintaan.ttd_dokter, "perimtaanOP", "imgTtd_dok", "canvasTtd_dok");

      var ttd_dokter_p = permintaan.ttd_dokter;

      $("form#perimtaanOP").find("img#ttd_dokter").attr("src", "../gambar/asset_ttd/" + ttd_dokter_p + ".jpg");


      $.each(jawaban, function(ind, val) {
        $("form#jawabanOP").find("[name='" + ind + "']").val(val);
      });

      // check_img(jawaban.ttd_dokter_jawab, "jawabanOP", "imgTtd_dok_jawab", "canvasTtd_dok_jawab");

      var ttd_dokter_j = jawaban.ttd_dokter;

      $("form#jawabanOP").find("img#ttd_dokter").attr("src", "../gambar/asset_ttd/" + ttd_dokter_j + ".jpg");

      context = canvasTtd_kel.getContext('2d');
      context.clearRect(0, 0, canvasTtd_kel.width, canvasTtd_kel.height);

      
      $("#btnKonsulCetak").css('display', 'inline-block');
      

      // $("form#perimtaanOP").find('#nama_ttd_dokter').keyup(function() {
      //   var isi = $(this).val();
      //   if (isi == '') {
      //     $(this).val('');
      //   }
      // });

      // nama_ttd_dokter();

    });
  }



  $("button#resetTtd_kel").click(function() {
    var id = $("input[name='ttd_keluarga']").val();

    $.post("konsultasi_action.php", {
      id: id,
      type: "hapus_ttd"
    }).done(function(data) {
      $("form#perimtaanOP").find("img#imgTtd_kel").css("display", "none");
      $("form#perimtaanOP").find("img#imgTtd_kel").attr("src", "");
      $("form#perimtaanOP").find("canvas#canvasTtd_kel").css("display", "inline-block");

      const context = canvasTtd_kel.getContext('2d');
      context.clearRect(0, 0, canvasTtd_kel.width, canvasTtd_kel.height);
    });

  });

  $("button#resetTtd_dok").click(function() {
    var id = $("input[name='ttd_dokter']").val();

    $.post("konsultasi_action.php", {
      id: id,
      type: "hapus_ttd"
    }).done(function(data) {
      $("form#perimtaanOP").find("img#imgTtd_dok").css("display", "none");
      $("form#perimtaanOP").find("img#imgTtd_dok").attr("src", "");
      $("form#perimtaanOP").find("canvas#canvasTtd_dok").css("display", "inline-block");

      const context = canvasTtd_dok.getContext('2d');
      context.clearRect(0, 0, canvasTtd_dok.width, canvasTtd_dok.height);
    });

  });

  $("button#resetTtd_dok_jawab").click(function() {
    var id = $("input[name='ttd_dokter_jawab']").val();

    $.post("konsultasi_action.php", {
      id: id,
      type: "hapus_ttd"
    }).done(function(data) {
      $("form#perimtaanOP").find("img#imgTtd_dok_jawab").css("display", "none");
      $("form#perimtaanOP").find("img#imgTtd_dok_jawab").attr("src", "");
      $("form#perimtaanOP").find("canvas#canvasTtd_dok_jawab").css("display", "inline-block");

      const context = canvasTtd_dok_jawab.getContext('2d');
      context.clearRect(0, 0, canvasTtd_dok_jawab.width, canvasTtd_dok_jawab.height);
    });

  });

  $("button#saveFormKonsul").click(function() {
    var id_rawat = $("input#rawat_id").val();
    var dataForm = $("form#perimtaanOP").serializeArray();
    // var ttd_dokter = signaturePadTtd_dok.toDataURL();
    var ttd_keluarga = signaturePadTtd_kel.toDataURL();

    $.post("konsultasi_action.php", {
      id_rawat: id_rawat,
      dataForm: dataForm,
      // ttd_dokter: ttd_dokter,
      ttd_keluarga: ttd_keluarga,
      type: "saveFormKonsul"
    }).done(function(data) {
      
        alert("Berhasil Menyimpan Data");
      
      get_dataFormKonsultasi(id_rawat);
    });

  });

  $("button#jawabForm").click(function() {
    var id_rawat = $("input#rawat_id").val();
    var dataForm = $("form#jawabanOP").serializeArray();
    // var ttd_dokter_jawab = signaturePadTtd_dok_jawab.toDataURL();

    var _data = {
      id_rawat: id_rawat,
      dataForm: dataForm,
      // ttd_dokter_jawab: ttd_dokter_jawab,
      type: "jawabForm"
    }

    console.log(_data);

    $.post("konsultasi_action.php", _data).done(function(data) {
      console.log(data)
      if (data == 'ok') {
        alert("Berhasil Menyimpan Data");
      } else {
        alert("Gagal Menyimpan Data");
      }
      get_dataFormKonsultasi(id_rawat);
    });

  });
</script>