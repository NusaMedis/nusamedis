$(function() {
  var form = $('#form_dalam');

  form.submit(function(e) {
    e.preventDefault();
    var divs = $("div#values").find("div").toArray();

    var a = [];
    for ( var i = 0; i < divs.length; i++ ) {
      a.push( divs[ i ].innerHTML );
    }

    var forma = form.serialize();
    
    $.ajax({
      url: 'save_dalam.php',
      type: 'POST',
      data: {forma : form.serialize(), terapi : a},
      success: function (rs) {
        // console.log(rs);
        alert(rs);
      }
    });
  })

});

function tbuid(ok) {
  $.getJSON('get_dalam.php', {rawat_id: ok },function(rs) {
    // end table riwayat kehamilan
    $('#form_dalam').find(':checkbox').removeAttr('checked');
    $('#form_dalam').trigger('reset');
    $.each(rs.dataObgyn, function(a,b) {
      if(a != 'diagnoseForm'){
        $('#'+a).val(b);
      }
      
      


    });
    
    $.each(rs.ttv, function(e, r) {
      $('#'+r.id).val(r.anamnesa_isi_nilai);
    });

    $("form#form_dalam").find('#keluhanUtama').val(rs.keluhamutama);
    $("form#form_dalam").find('#diagnose_skr').val(rs.diagnose_skr);
    $("form#form_dalam").find('#asd').val(rs.asd);
    $("form#form_dalam").find('#rawat').val(rs.rawat);
    $("form#form_dalam").find('#status_lokalis').val(rs.status_lokalis);
    $("form#form_dalam").find('#pemeriksaanPenunjang').val(rs.pemeriksaanPenunjang);
    $("form#form_dalam").find('#ket_diagnosa_satu').val(rs.ket_diagnosa_satu);
    $("form#form_dalam").find('#ket_diagnosa_empat').val(rs.ket_diagnosa_empat);
    $("form#form_dalam").find('#lap_tindakan').val(rs.lap_tindakan);
    $("form#form_dalam").find('#planning_penatalaksanaan').val(rs.planning_penatalaksanaan);

    var valuess = rs.terapi;
    var a = [];
    if(valuess != null){
      for ( var i = 0; i < valuess.length; i++ ) {
        a.push("<div>"+valuess[ i ]+"</div>");
      }
      $("form#form_dalam").find('#values').html(a.join(""));
    }
    else{
      $("form#form_dalam").find('#values').html("");
    }
    //console.log(valuess.length);


    $("form#form_dalam").find('#keadaan_umum_pasien').val(rs.keadaan_umum_pasien);
    $("form#form_dalam").find('#kesadaran').val(rs.kesadaran);
    $("form#form_dalam").find('#tekanan_darah_sistole').val(rs.tekanan_darah_sistole);
    $("form#form_dalam").find('#tekanan_darah_diastole').val(rs.tekanan_darah_diastole);
    $("form#form_dalam").find('#nadi').val(rs.nadi);
    $("form#form_dalam").find('#pernafasan').val(rs.pernafasan);
    $("form#form_dalam").find('#suhu_badan').val(rs.suhu_badan);
    $("form#form_dalam").find('#berat_badan').val(rs.berat_badan);
    $("form#form_dalam").find('#tinggi_badan').val(rs.tinggi_badan);
    $("form#form_dalam").find('#saturasi').val(rs.saturasi);

    if (rs.memahamiMateri != null) {$("form#form_dalam").find('#memahamiMateri').attr('checked', true);}
    if (rs.butuhLeaflet != null) {$("form#form_dalam").find('#butuhLeaflet').attr('checked', true);}
    if (rs.membatasiMateri != null) {$("form#form_dalam").find('#membatasiMateri').attr('checked', true);}
    if (rs.pengulanganMateri != null) {$("form#form_dalam").find('#pengulanganMateri').attr('checked', true);}
    if (rs.bisaMengulang != null) {$("form#form_dalam").find('#bisaMengulang').attr('checked', true);}

    if (rs.lain_lainEdukasi != null) {
        $("form#form_dalam").find('#lain_lainEdukasi').attr('checked', true);
        $("form#form_dalam").find("input#lainEd_det").css("display", "block");
        $("form#form_dalam").find('input#lainEd_det').attr('disabled', false);
        $("form#form_dalam").find('#lainEd_det').val(rs.lainEd_det);
    }
    else{
        $("form#form_dalam").find("input#lainEd_det").css("display", "none");
        $("form#form_dalam").find('input#lainEd_det').attr('disabled', true);
        $("form#form_dalam").find('#lainEd_det').val("");
    }
   
    
    if (rs.diagnosa != null) {$("form#form_dalam").find('#diagnosa').attr('checked', true);}
    if (rs.penjelasan_penyakit != null) {$("form#form_dalam").find('#penjelasan_penyakit').attr('checked', true);}
    if (rs.pemeriksaan_penunjang != null) {$("form#form_dalam").find('#pemeriksaan_penunjang').attr('checked', true);}
    if (rs.terapi_edukasi != null) {$("form#form_dalam").find('#terapi_edukasi').attr('checked', true);}
    if (rs.tindakan_medis != null) {$("form#form_dalam").find('#tindakan_medis').attr('checked', true);}
    if (rs.prognosa != null) {$("form#form_dalam").find('#prognosa').attr('checked', true);}
    if (rs.perkiraan_hari_rawat != null) {$("form#form_dalam").find('#perkiraan_hari_rawat').attr('checked', true);}
    if (rs.penjelasan_komplikasi != null) {$("form#form_dalam").find('#penjelasan_komplikasi').attr('checked', true);}
    if (rs.informed_concent != null) {$("form#form_dalam").find('#informed_concent').attr('checked', true);}
    if (rs.kondisi != null) {$("form#form_dalam").find('#kondisi').attr('checked', true);}
    if (rs.konsul != null) {
        $("form#form_dalam").find('#konsul').attr('checked', true);
        $("form#form_dalam").find("input#konsul_det").css("display", "block");
        $("form#form_dalam").find('input#konsul_det').attr('disabled', false);
        $("form#form_dalam").find('#konsul_det').val(rs.konsul_det);
    }
    else{
        $("form#form_dalam").find("input#konsul_det").css("display", "none");
        $("form#form_dalam").find('input#konsul_det').attr('disabled', true);
        $("form#form_dalam").find('#konsul_det').val("");
    }

    if (rs.edukasi_pulang != null) {$("form#form_dalam").find('#edukasi_pulang').attr('checked', true);}

    if (rs.edukasi_lain != null) {
        $("form#form_dalam").find('#edukasi_lain').attr('checked', true);
        $("form#form_dalam").find("input#lain_det").css("display", "block");
        $("form#form_dalam").find('input#lain_det').attr('disabled', false);
        $("form#form_dalam").find('#lain_det').val(rs.lain_det);
    }
    else{
        $("form#form_dalam").find("input#lain_det").css("display", "none");
        $("form#form_dalam").find('input#lain_det').attr('disabled', true);
        $("form#form_dalam").find('#lain_det').val("");
    }

    
    

   
   
    })
};

    