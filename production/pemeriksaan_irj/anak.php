<?
$sql = "select * from klinik.klinik_anamnesa_pilihan where id_anamnesa=" . QuoteValue(DPE_CHAR, '9dafa78dca4a01f50d21fbc884a5eecb') . "
        order by anamnesa_pilihan_urut asc, anamnesa_pilihan_id asc";
$rs = $dtaccess->Execute($sql);
$dataAnamnesaDetail = $dtaccess->FetchAll($rs);
?>

<script type="text/javascript" src="anak.js"></script>
<script>
 

</script>


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
    <form id="form_anak" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
      <input id="asd" type="hidden" name="asd" value="">
      <div class="col-md-12">
        <div class="col-md-12">
          <h2><b>I. SUBJECTIVE</b></h2>
          
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
                  <label style="float: left;">Lingkar Kepala</label>
                </div>
                <div class="col-md-6">
                  <input type="text" name="lingkar_kepala" id="lingkar_kepala" value="" placeholder="" class="form-control">
                </div>
                <div class="col-md-1">
                  <label>Cm</label>
                </div>
              </div>
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
         <!--  <br>
          <div class="form-group">
          </div>
          <h2><b>III. ANALISA</b></h2>
         
          <div class="col-md-12 col-sm-12 col-xs-12">&nbsp;</div>
          <div class="col-md-8 col-sm-12 col-xs-12">
            <textarea class="form-control" name="ket_diagnosa_empat" id="ket_diagnosa_empat"></textarea>
          </div> -->
          <br>
          <div class="form-group">
          </div>
          <h2><b>III. DIAGNOSA</b></h2>
          <div class="col-md-8 col-sm-8 col-xs-12">
            <textarea class="form-control" name="diagnose_skr" id="diagnose_skr"></textarea>
          </div>
          <div class="form-group">
          </div>
          <h2><b>IV. PLANNING</b></h2>
          <div class="col-md-8 col-sm-8 col-xs-12">
            <textarea class="form-control" name="planning_penatalaksanaan" id="planning_penatalaksanaan"></textarea>
          </div>

          <div class="form-group">
          </div>
          <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Terapi</h4>
          <div class="col-md-8 col-sm-8 col-xs-12">
            <div class="editable" style="border: 1px solid #ccc; height: 200px; overflow: auto; font-size: 16px; padding: 10px;" id="values" contenteditable></div>

          </div>

          <div class="form-group">
          </div>
         
          <div class="col-md-8 col-sm-8 col-xs-12" style="display: flex;">
           <div class="col-md-6">
             <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Materi Edukasi :</h4>
             <input type="checkbox" name="diagnosa" id="diagnosa" value="true"> Diagnosa <br>
             <input type="checkbox" name="penjelasan_penyakit" id="penjelasan_penyakit" value="true"> Penjelasan penyakit (penyebab, tanda, gejala) <br>
             <input type="checkbox" name="pemeriksaan_penunjang" id="pemeriksaan_penunjang" value="true"> Pemeriksaan Penunjang <br>
             <input type="checkbox" name="terapi_edukasi" id="terapi_edukasi" value="true"> Terapi / terapi alternative <br>
             <input type="checkbox" name="tindakan_medis" id="tindakan_medis" value="true"> Tindakan Medis <br>
             <input type="checkbox" name="prognosa" id="prognosa" value="true"> Prognosa <br>
             <!-- <input type="checkbox" name="perkiraan_hari_rawat" id="perkiraan_hari_rawat" value="true"> Perkiraan Hari Rawat <br>
             <input type="checkbox" name="penjelasan_komplikasi" id="penjelasan_komplikasi" value="true"> Penjelasan komplikasi / resiko yang mungkin terjadi <br>
             <input type="checkbox" name="informed_concent" id="informed_concent" value="true"> Edukasi pengambilan informed concent <br>
             <input type="checkbox" name="kondisi" id="kondisi" value="true"> Kondisi kesehatan saat ini <br> -->
             <input type="checkbox" name="konsul" id="konsul" value="true"> Konsul ke : <br>
             <input type="text" name="konsul_det" id="konsul_det" class="form-control" style="display: none" disabled>
             <input type="checkbox" name="edukasi_pulang" id="edukasi_pulang" value="true"> Edukasi sebelum pulang <br>
             <input type="checkbox" name="edukasi_lain" id="edukasi_lain" value="true"> Lain lain : <br>
             <input type="text" name="lain_det" id="lain_det" class="form-control" style="display: none" disabled>
           </div>
           <div class="col-md-6">
              <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Evaluasi Edukasi :</h4>
              <input type="checkbox" name="memahamiMateri" id="memahamiMateri" value="true"> Memahami Materi <br>
               <input type="checkbox" name="butuhLeaflet" id="butuhLeaflet" value="true"> Butuh Leaflet <br>
               <input type="checkbox" name="membatasiMateri" id="membatasiMateri" value="true"> Membatasi Materi <br>
               <input type="checkbox" name="pengulanganMateri" id="pengulanganMateri" value="true"> Butuh Pengulangan Materi <br>
               <input type="checkbox" name="bisaMengulang" id="bisaMengulang" value="true"> Bisa Mengulang Materi <br>

               
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
              <button type="button" id="cetak-resume-anak" class="btn btn-success">Cetak Asmed</button>
            </div>
            <div class="col-md-2 col-sm-2 col-xs-12" style="margin-top: 10px">
              <button type="button" id="cetak-resume-med-anak" class="btn btn-success">Cetak Resume</button>
            </div>
          </div>
        </div>
        
      </div>
    </form>
  </div>


<script>
  
  $('#cetak-resume-anak').click(function() {
    var id_rawat = $('#form_anak').find("#asd").val();
    // BukaWindow('cetak_usg.php?' + data, 'cetak usg');
    window.open('cetak_resume_poli_anak.php?asd=' + id_rawat, '_blank');
  });
  $('#cetak-resume-med-anak').click(function() {
     var id_rawat = $('#form_anak').find("#asd").val();
    BukaWindow('cetak_bpjs.php?id=' + id_rawat, "Resume");
  });

  $("form#form_anak").find("input#lain_lainEdukasi").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("form#form_anak").find("input#lainEd_det").css("display", "block");
        $("form#form_anak").find('input#lainEd_det').attr('disabled', false);
    }
    else{
        $("form#form_anak").find("input#lainEd_det").css("display", "none");
        $("form#form_anak").find('input#lainEd_det').attr('disabled', false);
    }
    
  });

  $("form#form_anak").find("input#konsul").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("form#form_anak").find("input#konsul_det").css("display", "block");
        $("form#form_anak").find('input#konsul_det').attr('disabled', false);
    }
    else{
        $("form#form_anak").find("input#konsul_det").css("display", "none");
        $("form#form_anak").find('input#konsul_det').attr('disabled', false);
    }

  });

  $("form#form_anak").find("input#edukasi_lain").click(function(){
    var check = $(this).is(":checked");
    if(check == true){
        $("form#form_anak").find("input#lain_det").css("display", "block");
        $("form#form_anak").find('input#lain_det').attr('disabled', false);
    }
    else{
        $("form#form_anak").find("input#lain_det").css("display", "none");
        $("form#form_anak").find('input#lain_det').attr('disabled', false);
    }
    
  });

</script>