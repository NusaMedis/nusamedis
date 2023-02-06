$(function() {
  var form = $('#form_anak');

  form.submit(function(e) {
    e.preventDefault();
    var divs = $("div#values").find("div").toArray();

    var a = [];
    for ( var i = 0; i < divs.length; i++ ) {
      a.push( divs[ i ].innerHTML );
    }

    var forma = form.serialize();
    
    $.ajax({
      url: 'save_anak.php',
      type: 'POST',
      data: {forma : form.serialize(), terapi : a},
      success: function (rs) {
        // console.log(rs);
        alert(rs);
      }
    });
  })

});

function tbuia(ok) {
  $.getJSON('get_anak.php', {rawat_id: ok },function(rs) {
    // end table riwayat kehamilan
    $('#form_anak').find(':checkbox').removeAttr('checked');
    $('#form_anak').trigger('reset');
    $.each(rs.dataObgyn, function(a,b) {
      if(a != 'diagnoseForm'){
        $('#'+a).val(b);
      }
      
      


    });
    
    $.each(rs.ttv, function(e, r) {
      $('#'+r.id).val(r.anamnesa_isi_nilai);
    });

    $("form#form_anak").find('#keluhanUtama').val(rs.keluhamutama);
    $("form#form_anak").find('#diagnose_skr').val(rs.diagnose_skr);
    $("form#form_anak").find('#asd').val(rs.asd);
    $("form#form_anak").find('#rawat').val(rs.rawat);
    $("form#form_anak").find('#status_lokalis').val(rs.status_lokalis);
    $("form#form_anak").find('#pemeriksaanPenunjang').val(rs.pemeriksaanPenunjang);
    $("form#form_anak").find('#ket_diagnosa_satu').val(rs.ket_diagnosa_satu);
    $("form#form_anak").find('#ket_diagnosa_empat').val(rs.ket_diagnosa_empat);
    $("form#form_anak").find('#lap_tindakan').val(rs.lap_tindakan);
    $("form#form_anak").find('#planning_penatalaksanaan').val(rs.planning_penatalaksanaan);

    var valuess = rs.terapi;
    var a = [];
    if(valuess != null){
      for ( var i = 0; i < valuess.length; i++ ) {
        a.push("<div>"+valuess[ i ]+"</div>");
      }
      $("form#form_anak").find('#values').html(a.join(""));
    }
    else{
      $("form#form_anak").find('#values').html("");
    }
    //console.log(valuess.length);


    $("form#form_anak").find('#keadaan_umum_pasien').val(rs.keadaan_umum_pasien);
    $("form#form_anak").find('#kesadaran').val(rs.kesadaran);
    $("form#form_anak").find('#tekanan_darah_sistole').val(rs.tekanan_darah_sistole);
    $("form#form_anak").find('#tekanan_darah_diastole').val(rs.tekanan_darah_diastole);
    $("form#form_anak").find('#nadi').val(rs.nadi);
    $("form#form_anak").find('#pernafasan').val(rs.pernafasan);
    $("form#form_anak").find('#suhu_badan').val(rs.suhu_badan);
    $("form#form_anak").find('#berat_badan').val(rs.berat_badan);
    $("form#form_anak").find('#tinggi_badan').val(rs.tinggi_badan);
    $("form#form_anak").find('#lingkar_kepala').val(rs.lingkar_kepala);

    if (rs.memahamiMateri != null) {$("form#form_anak").find('#memahamiMateri').attr('checked', true);}
    if (rs.butuhLeaflet != null) {$("form#form_anak").find('#butuhLeaflet').attr('checked', true);}
    if (rs.membatasiMateri != null) {$("form#form_anak").find('#membatasiMateri').attr('checked', true);}
    if (rs.pengulanganMateri != null) {$("form#form_anak").find('#pengulanganMateri').attr('checked', true);}
    if (rs.bisaMengulang != null) {$("form#form_anak").find('#bisaMengulang').attr('checked', true);}

    if (rs.lain_lainEdukasi != null) {
        $("form#form_anak").find('#lain_lainEdukasi').attr('checked', true);
        $("form#form_anak").find("input#lainEd_det").css("display", "block");
        $("form#form_anak").find('input#lainEd_det').attr('disabled', false);
        $("form#form_anak").find('#lainEd_det').val(rs.lainEd_det);
    }
    else{
        $("form#form_anak").find("input#lainEd_det").css("display", "none");
        $("form#form_anak").find('input#lainEd_det').attr('disabled', true);
        $("form#form_anak").find('#lainEd_det').val("");
    }
   
    
    if (rs.diagnosa != null) {$("form#form_anak").find('#diagnosa').attr('checked', true);}
    if (rs.penjelasan_penyakit != null) {$("form#form_anak").find('#penjelasan_penyakit').attr('checked', true);}
    if (rs.pemeriksaan_penunjang != null) {$("form#form_anak").find('#pemeriksaan_penunjang').attr('checked', true);}
    if (rs.terapi_edukasi != null) {$("form#form_anak").find('#terapi_edukasi').attr('checked', true);}
    if (rs.tindakan_medis != null) {$("form#form_anak").find('#tindakan_medis').attr('checked', true);}
    if (rs.prognosa != null) {$("form#form_anak").find('#prognosa').attr('checked', true);}
    if (rs.perkiraan_hari_rawat != null) {$("form#form_anak").find('#perkiraan_hari_rawat').attr('checked', true);}
    if (rs.penjelasan_komplikasi != null) {$("form#form_anak").find('#penjelasan_komplikasi').attr('checked', true);}
    if (rs.informed_concent != null) {$("form#form_anak").find('#informed_concent').attr('checked', true);}
    if (rs.kondisi != null) {$("form#form_anak").find('#kondisi').attr('checked', true);}
    if (rs.konsul != null) {
        $("form#form_anak").find('#konsul').attr('checked', true);
        $("form#form_anak").find("input#konsul_det").css("display", "block");
        $("form#form_anak").find('input#konsul_det').attr('disabled', false);
        $("form#form_anak").find('#konsul_det').val(rs.konsul_det);
    }
    else{
        $("form#form_anak").find("input#konsul_det").css("display", "none");
        $("form#form_anak").find('input#konsul_det').attr('disabled', true);
        $("form#form_anak").find('#konsul_det').val("");
    }

    if (rs.edukasi_pulang != null) {$("form#form_anak").find('#edukasi_pulang').attr('checked', true);}

    if (rs.edukasi_lain != null) {
        $("form#form_anak").find('#edukasi_lain').attr('checked', true);
        $("form#form_anak").find("input#lain_det").css("display", "block");
        $("form#form_anak").find('input#lain_det').attr('disabled', false);
        $("form#form_anak").find('#lain_det').val(rs.lain_det);
    }
    else{
        $("form#form_anak").find("input#lain_det").css("display", "none");
        $("form#form_anak").find('input#lain_det').attr('disabled', true);
        $("form#form_anak").find('#lain_det').val("");
    }

    
    

   
   
    })
};

    //if (rs.cek == '0bstetri') {
    

function get_obgyn(id) {

  $.getJSON('get_anak.php', {rawat_id: id },function(rs) {
    var html = '';
    // table riwayat kehamilan
    n = 1;
    $.each(rs.tb, function(x,y) {
      html += '<tr>';
        html += '<td>' + (n++) + '</td>';
        html += '<td>' + y[0] + '</td>';
        html += '<td>' + y[1] + '</td>';
        html += '<td>' + y[2] + '</td>';
        html += '<td>' + y[3] + '</td>';
        html += '<td>' + y[4] + '</td>';
        html += '<td>' + y[5] + '</td>';
        html += '<td>' + y[6] + '</td>';
        html += '<td>' + y[7] + '</td>';
        html += '<td>' + y[8] + '</td>';
      html += '</tr>';
    })

    $('#form_anak').find('table > tbody > tr').remove();
    $('#form_anak').find('table > tbody').append(html);
    // end table riwayat kehamilan
    $('#form_anak').find(':checkbox').removeAttr('checked');
    $('#form_anak').trigger('reset');
    $.each(rs.dataObgyn, function(x,y) {
      $('#'+x).val(y);
      if (y == 'true') {
        $('#'+x).attr('checked', true);
      }


    });

    $.each(rs.ttv, function(x, y) {
      $('#'+y.id).val(y.anamnesa_isi_nilai);
    });

  
      $('#keluhanUtama').val(rs.keluhanUtama);
    

    
  })
}
