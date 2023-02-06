$(function() {
  var form = $('#form_obgyn');
  form.submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'save_obgyn.php',
      type: 'POST',
      data: form.serialize(),
      success: function (rs) {
        // console.log(rs);
        alert(rs);
      }
    });
  });



});

function tbui(ok) {
  $.getJSON('get_obgyn.php', {rawat_id: ok },function(rs) {
    // end table riwayat kehamilan
    $('#form_obgyn').find(':checkbox').removeAttr('checked');
    $('#form_obgyn').trigger('reset');
    $("input#janin_kembara_det").val("");
    $("input#janin_kembara_det").css("display", "none");
    $("input#janin_kembara_det").attr("disabled", true);
    $("input#janin_kembara_det").focusout();
    if(rs.dataObgyn != false){
        $.each(rs.dataObgyn, function(a,b) {
          if(a != 'diagnoseForm'){
            $('#'+a).val(b);
          }
          
          if (b == 'true') {
            $('#'+a).attr('checked', true);
          }


        });
    }
    else{
        $('#form_obgyn').find("select").val('');
    }
    

    $.each(rs.ttv, function(e, r) {
      $('#'+r.id).val(r.anamnesa_isi_nilai);
    });

    if (rs.cek == '0bstetri') {
      $('#keluhanUtama').val(rs.keluhamutama);
      $('#diagnose_skr').val(rs.diagnose_skr);
    } else {
      //$('#keluhan_utama').val(rs.keluhanUtama);
      $('#keluhan_utama').val(rs.keluhamutama);
      $('#diagnose_gynek').val(rs.diagnose_skr);
    }
    if (rs.cek == '0bstetri') {

        $('#pemeriksaanPenunjang').val(rs.pemeriksaanPenunjang);

        if (rs.mual != '') {$('#mual').attr('checked', true);}
        if (rs.muntah != '') {$('#muntah').attr('checked', true);}
        if (rs.pusing != '') {$('#pusing').attr('checked', true);}
        if (rs.perut_sakit != '') {$('#perut_sakit').attr('checked', true);}
        if (rs.kepala != '') {$('#kepala').attr('checked', true);}
        if (rs.sungsang != '') {$('#sungsang').attr('checked', true);}
        if (rs.ablique != '') {$('#oblique').attr('checked', true);}
        if (rs.lintang != '') {$('#lintang').attr('checked', true);}
        if (rs.sedikit != '') {$('#pendarahan_sedikit').attr('checked', true);}
        if (rs.banyak != '') {$('#pendarahan_banyak').attr('checked', true);}

        //Edukasi

        if (rs.diagnosa != null) {$('#diagnosa').attr('checked', true);}
        if (rs.penjelasan_penyakit != null) {$('#penjelasan_penyakit').attr('checked', true);}
        if (rs.pemeriksaan_penunjang != null) {$('#pemeriksaan_penunjang').attr('checked', true);}
        if (rs.terapi_edukasi != null) {$('#terapi_edukasi').attr('checked', true);}
        if (rs.terapi_alter != null) {$('#terapi_alter').attr('checked', true);}
        if (rs.tindakan_medis != null) {$('#tindakan_medis').attr('checked', true);}
        if (rs.prognosa != null) {$('#prognosa').attr('checked', true);}
        if (rs.perkiraan_hari_rawat != null) {$('#perkiraan_hari_rawat').attr('checked', true);}
        if (rs.penjelasan_komplikasi != null) {$('#penjelasan_komplikasi').attr('checked', true);}
        if (rs.informed_concent != null) {$('#informed_concent').attr('checked', true);}
        if (rs.kondisi != null) {$('#kondisi').attr('checked', true);}

        if (rs.konsul != null) {
            $('#konsul').attr('checked', true);
            $("input#konsul_det").css("display", "block");
            $('input#konsul_det').attr('disabled', false);
            $('#konsul_det').val(rs.konsul_det);
        }
        else{
            $("input#konsul_det").css("display", "none");
            $('input#konsul_det').attr('disabled', true);
            $('#konsul_det').val("");
        }

        if (rs.edukasi_pulang != null) {$('#edukasi_pulang').attr('checked', true);}

        if (rs.edukasi_lain != null) {
            $('#edukasi_lain').attr('checked', true);
            $("input#lain_det").css("display", "block");
            $('input#lain_det').attr('disabled', false);
            $('#lain_det').val(rs.lain_det);
        }
        else{
            $("input#lain_det").css("display", "none");
            $('input#lain_det').attr('disabled', true);
            $('#lain_det').val("");
        }

        if (rs.memahamiMateri != null) {$('#memahamiMateri').attr('checked', true);}
        if (rs.butuhLeaflet != null) {$('#butuhLeaflet').attr('checked', true);}
        if (rs.membatasiMateri != null) {$('#membatasiMateri').attr('checked', true);}
        if (rs.pengulanganMateri != null) {$('#pengulanganMateri').attr('checked', true);}
        if (rs.bisaMengulang != null) {$('#bisaMengulang').attr('checked', true);}

        if (rs.lain_lainEdukasi != null) {
            $('#lain_lainEdukasi').attr('checked', true);
            $("input#lainEd_det").css("display", "block");
            $('input#lainEd_det').attr('disabled', false);
            $('#lainEd_det').val(rs.lainEd_det);
        }
        else{
            $("input#lainEd_det").css("display", "none");
            $('input#lainEd_det').attr('disabled', true);
            $('#lainEd_det').val("");
        }


        
    }

    if (rs.cek == 'Ginekology') {

        $('#pemeriksaanPenunjang_g').val(rs.pemeriksaanPenunjang_g);

        if (rs.sedikitGin != '') {$('#sedikit').attr('checked', true);}
        if (rs.banyakGin != '') {$('#banyak').attr('checked', true);}

        //Edukasi

        if (rs.diagnosa != null) {$('#diagnosa_g').attr('checked', true);}
        if (rs.penjelasan_penyakit != null) {$('#penjelasan_penyakit_g').attr('checked', true);}
        if (rs.pemeriksaan_penunjang != null) {$('#pemeriksaan_penunjang_g').attr('checked', true);}
        if (rs.terapi_edukasi != null) {$('#terapi_edukasi_g').attr('checked', true);}
        if (rs.terapi_alter != null) {$('#terapi_alter_g').attr('checked', true);}
        if (rs.tindakan_medis != null) {$('#tindakan_medis_g').attr('checked', true);}
        if (rs.prognosa != null) {$('#prognosa_g').attr('checked', true);}
        //if (rs.perkiraan_hari_rawat != null) {$('#perkiraan_hari_rawat_g').attr('checked', true);}
        //if (rs.penjelasan_komplikasi != null) {$('#penjelasan_komplikasi_g').attr('checked', true);}
        //if (rs.informed_concent != null) {$('#informed_concent_g').attr('checked', true);}
        //if (rs.kondisi != null) {$('#kondisi_g').attr('checked', true);}
        if (rs.konsul != null) {
            $('#konsul_g').attr('checked', true);
            $("input#konsul_det_g").css("display", "block");
            $('input#konsul_det_g').attr('disabled', false);
            $('#konsul_det_g').val(rs.konsul_det);
        }
        else{
            $("input#konsul_det_g").css("display", "none");
            $('input#konsul_det_g').attr('disabled', true);
            $('#konsul_det_g').val("");
        }

        if (rs.edukasi_pulang != null) {$('#edukasi_pulang_g').attr('checked', true);}

        if (rs.edukasi_lain != null) {
            $('#edukasi_lain_g').attr('checked', true);
            $("input#lain_det_g").css("display", "block");
            $('input#lain_det_g').attr('disabled', false);
            $('#lain_det_g').val(rs.lain_det);
        }
        else{
            $("input#lain_det_g").css("display", "none");
            $('input#lain_det_g').attr('disabled', true);
            $('#lain_det_g').val("");
        }

        if (rs.memahamiMateri != null) {$('#memahamiMateri_g').attr('checked', true);}
        if (rs.butuhLeaflet != null) {$('#butuhLeaflet_g').attr('checked', true);}
        if (rs.membatasiMateri != null) {$('#membatasiMateri_g').attr('checked', true);}
        if (rs.pengulanganMateri != null) {$('#pengulanganMateri_g').attr('checked', true);}
        if (rs.bisaMengulang != null) {$('#bisaMengulang_g').attr('checked', true);}

        if (rs.lain_lainEdukasi != null) {
            $('#lain_lainEdukasi_g').attr('checked', true);
            $("input#lainEd_det_g").css("display", "block");
            $('input#lainEd_det_g').attr('disabled', false);
            $('#lainEd_det_g').val(rs.lainEd_det);
        }
        else{
            $("input#lainEd_det_g").css("display", "none");
            $('input#lainEd_det_g').attr('disabled', true);
            $('#lainEd_det_g').val("");
        }
        
        
    }

    $('#hpht2').val(rs.hpht);
    // $('#hpltp').val(rs.hpht);
    $('#hpl2').val(rs.hpl);
    // $('#hpltp').val(rs.hpl);

     $('#asd').val(rs.asd);

    $('#tinggi_fu').val(rs.FU);
    $('#rawat').val(rs.rawat);

    if (rs.Amenore == 'y') {
        $('#amenore_ya').attr('checked', true);
        console.log("test");
    }
    $('#amenore_hari').val(rs.AmenoreHari);
    $('#amenore_bulan').val(rs.AmenoreBln);

    $('#haid_lama_hari').val(rs.HailLama);
    $('#haid_lama_banyak_hari').val(rs.HaidLamaBanyak);
    $('#haid_lama_bulan').val(rs.HaidBulan);
    $('#terus_menerus_lama').val(rs.TerusMenerus);

    $('#terus_menerus_hari').val(rs.TerusMenerusHari);
    
    
    $('#sudah_berapa_lama').val(rs.BerapaLama);
    
    $('#status_lokalis').val(rs.status_lokalis);
    $('#gs2').val(rs.gs2);
    $('#crl').val(rs.crl);
    $('#usia_kehamilan_minggu').val(rs.ga_minggu);
    $('#usia_kehamilan_hari').val(rs.ga_hari);
    if (rs.janin_tunggal == 'true') {
        $('#janin_tunggala').attr('checked', true);
        $("input#janin_kembara_det").css("display", "none");
        $("input#janin_kembara_det").attr("disabled", true);
    }

    if (rs.janin_kembar == 'true') {
        $('#janin_kembara').attr('checked', true);
        $("input#janin_kembara_det").css("display", "block");
        $("input#janin_kembara_det").attr("disabled", false);
        $('input#janin_kembara_det').val(rs.janin_kembar_det);
    }

    var jumlah = rs.janin_kembar_det;
    var countD = $("form#form_obgyn div#divJenkel div#detJenkel").length - 1;

    if(countD >= 1){
        $("form#form_obgyn div#divJenkel div#detJenkel").slice(-countD).remove();
    }

    var html = "";
    for(var x = 1; x < jumlah; x++){
        html += "<div class='col-md-12' id='detJenkel' style='margin: 5px 0;'>";
        html += "<select class='form-control' name='jenis_kelamin_"+x+"'>";
        html += "<option value=''> - </option>";
        html += "<option value='Laki'>Laki-laki</option>";
        html += "<option value='Perempuan'>Perempuan</option>";
        html += "</select>";
        html += "</div>";
    }

    $("form#form_obgyn div#divJenkel").append(html);

    for(var x = 1; x < jumlah; x++){
        $("form#form_obgyn").find("select[name='jenis_kelamin_"+x+"']").val(rs['jenis_kelamin_'+x]);
    }

    if (rs.janin_hidup == 'true') {$('#janin_hidupa').attr('checked', true)};
    if (rs.janin_iufd == 'true') {$('#janin_iufda').attr('checked', true)};
    if (rs.letak_janin_sungsang == 'true') {$('#letak_janin_sungsanga').attr('checked', true)};
    if (rs.letak_janin_kepala == 'true') {$('#letak_janin_kepalaa').attr('checked', true)};
    if (rs.letak_janin_melintang == 'true') {$('#letak_janin_melintanga').attr('checked', true)};
    if (rs.letak_janin_oblique == 'true') {$('#letak_janin_obliquea').attr('checked', true)};
    $('#bpd').val(rs.bpd);
    $('#fl').val(rs.fl);
    $('#ac').val(rs.ac);
    $('#efw').val(rs.efw);
    $('#usia_kehamilan_minggu1').val(rs.ga_minggu1);
    $('#usia_kehamilan_hari1').val(rs.ga_hari1);
    if (rs.insersi_fudus == 'true') {$('#insersi_fudusa').attr('checked', true)};
    if (rs.insersi_corpus == 'true') {$('#insersi_corpusa').attr('checked', true)};
    if (rs.insersi_sbr == 'true') {$('#insersi_sbra').attr('checked', true)};
    if (rs.insersi_posterior == 'true') {$('#insersi_posteriora').attr('checked', true)};
    if (rs.insersi_anterior == 'true') {$('#insersi_anterior').attr('checked', true)};
    if (rs.ketubah_cukup == 'true') {$('#ketubah_cukupa').attr('checked', true)};
    if (rs.ketuban_kurang == 'true') {$('#ketuban_kuranga').attr('checked', true)};
    if (rs.ketuban_banyak == 'true') {$('#ketuban_banyaka').attr('checked', true)};
    if (rs.menopause == 'y') {$('#menopause').attr('checked', true)};
    $('#hpltp').val(rs.hpltp);
    $('#hpl_muda').val(rs.hpl_muda);
    $('#afi').val(rs.afi);
    
    $('#usg_ginekologi').val(rs.usg_ginekologi);
    $('#pemeriksaan_penunjang').val(rs.pemeriksaan_penunjang);
    $('#ket_diagnosa_satu').val(rs.ket_diagnosa_satu);
    $('#ket_diagnosa_empat').val(rs.ket_diagnosa_empat);
    $('#lap_tindakan').val(rs.lap_tindakan);
    $('#tindakan').val(rs.tindakan);
    $('#planning_penatalaksanaan_ginek').val(rs.planning_penatalaksanaan_ginek);
    $('#pemerisaan_penunjang_ginek').val(rs.pemeriksaan_penunjang_ginek);
    $('#pemerisaan_dalam_vt').val(rs.pemeriksaan_dalam_vt);
    $('#analisa_diagnosaa_ginek').val(rs.analisa_diagnosaa_ginek);
    
    //$('#planning_penatalaksanaan').val(rs.terapi[0]);

    $('keadaan_umum_pasien_ginek').val(rs.keadaan_umum_pasien);
        var mantab = '';
    $.each(rs.terapi, function(p,q) {
        mantab+=q;
    });

    if (rs.cek == '0bstetri') {
     // if (mantab == rs.planning) {
      $('#planning_penatalaksanaan').val(rs.planning);
     // }
     // else{
     //  $('#planning_penatalaksanaan').val(rs.planning+" ;"+mantab);
     // }
    }else{
      $('#planning_penatalaksanaan_ginek').val(rs.planning);
    }
    if (rs.cek == '0bstetri') {
        $('#g_obstet').val(rs.abc);
        $('#p_obstet').val(rs.abcd);
        $('#a_obstet').val(rs.abcde);
    }else{
        $('#g_ginek').val(rs.abc);
        $('#p_ginek').val(rs.abcd);
        $('#a_ginek').val(rs.abcde);
    }
    if (rs.cek == 'Ginekology') {
        if (rs.gatal != '') {$('#gatal').attr('checked', true);}
        if (rs.tidak_gatal != '') {$('#tidak_gatal').attr('checked', true);}
        if (rs.bau != '') {$('#bau').attr('checked', true);}
        if (rs.tidak_bau != '') {$('#tidak_bau').attr('checked', true);}
        if (rs.Lainnya != '') {$('#lainnya').attr('checked', true);}
        if (rs.perut_sakitt != '') {$('#perut_sakitt').attr('checked', true);}
        if (rs.tumor != '') {$('#tumor').attr('checked', true);}
        if (rs.myom_uteri != '') {$('#myom_uteri').attr('checked', true);}
        if (rs.kista_ovari != '') {$('#kista_ovari').attr('checked', true);}
        if (rs.ca_cx != '') {$('#ca_cx').attr('checked', true);}
        if (rs.lainnya != '') {$('#lainnyaa').attr('checked', true);}
        $('#warna').val(rs.warna);
    }  
    //G Analisa Pemeriksaan Baru
    // if (rs.abc == '1') {$('#g_analisa1').attr('selected',true);}
    // if (rs.abc == '2') {$('#g_analisa2').attr('selected',true);}
    // if (rs.abc == '3') {$('#g_analisa3').attr('selected',true);}
    // if (rs.abc == '4') {$('#g_analisa4').attr('selected',true);}
    // if (rs.abc == '5') {$('#g_analisa5').attr('selected',true);}
    // if (rs.abc == '6') {$('#g_analisa6').attr('selected',true);}

    $('#g_analisa').val(rs.abc);
    // P Analisa Pemeriksaan Baru
    if (rs.abcd == '0') {$('#p_analisa0').attr('selected',true);}
    if (rs.abcd == '1') {$('#p_analisa1').attr('selected',true);}
    if (rs.abcd == '2') {$('#p_analisa2').attr('selected',true);}
    if (rs.abcd == '3') {$('#p_analisa3').attr('selected',true);}
    if (rs.abcd == '4') {$('#p_analisa4').attr('selected',true);}
    if (rs.abcd == '5') {$('#p_analisa5').attr('selected',true);}
    // A Analisa Pemeriksaan Baru
    if (rs.abcde == '0') {$('#a_analisa0').attr('selected',true);}
    if (rs.abcde == '1') {$('#a_analisa1').attr('selected',true);}
    if (rs.abcde == '2') {$('#a_analisa2').attr('selected',true);}
    if (rs.abcde == '3') {$('#a_analisa3').attr('selected',true);}
    if (rs.abcde == '4') {$('#a_analisa4').attr('selected',true);}
    if (rs.abcde == '5') {$('#a_analisa5').attr('selected',true);}
    //KET DIAGNOSA DUA

    if(rs.janin_tunggal == null && rs.janin_kembar == null){
        if(rs.gs1 == '1'){
            $('#ket_diagnosa_dua').val('T');
        }
        else if(rs.gs1 == '2'){
            $('#ket_diagnosa_dua').val('G');
        }
        else{
             $('#ket_diagnosa_dua').val('');
        }
    }
    else if(rs.janin_tunggal == 'true'){
        $('#ket_diagnosa_dua').val('T');
    }
    else if(rs.janin_kembar == 'true'){
        $('#ket_diagnosa_dua').val('G');
    }


    if(rs.janin_hidup == null && rs.janin_iufd == null){
        
        if(rs.djj == '+'){
            $('#ket_diagnosa_tiga').val('Hidup');
        }
        else if(rs.djj == '-'){
            $('#ket_diagnosa_tiga').val('IUFD');
        }
        else{
            $('#ket_diagnosa_tiga').val('');
        }
    }
    else if(rs.janin_hidup == 'true'){
        $('#ket_diagnosa_tiga').val('Hidup');
    }
    else if(rs.janin_iufd == 'true'){
        $('#ket_diagnosa_tiga').val('IUFD');
    }

    if(rs.letak_janin_kepala == null && rs.letak_janin_sungsang == null && rs.letak_janin_melintang == null && rs.letak_janin_oblique == null ){
        
        $('#ket_diagnosa_lima').val('');
    }
    else{
        if (rs.letak_janin_kepala == 'true') { 
            $('#ket_diagnosa_lima').val('Kepala');
        }
        
        else if (rs.letak_janin_sungsang == 'true') {
            $('#ket_diagnosa_lima').val('Sungsang');
        }
        
        else if (rs.letak_janin_melintang == 'true') {
            $('#ket_diagnosa_lima').val('Melintang');
        }
        
        else if (rs.letak_janin_oblique == 'true') {
            $('#ket_diagnosa_lima').val('Oblique');
        }
    }
    
    //$('#ket_diagnosa_lima').val('');
    });
};

    //if (rs.cek == '0bstetri') {
    

function get_obgyn(id) {

  $.getJSON('get_obgyn.php', {rawat_id: id },function(rs) {
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

    $('#form_obgyn').find('table > tbody > tr').remove();
    $('#form_obgyn').find('table > tbody').append(html);
    // end table riwayat kehamilan
    $('#form_obgyn').find(':checkbox').removeAttr('checked');
    $('#form_obgyn').trigger('reset');
    $.each(rs.dataObgyn, function(x,y) {
      $('#'+x).val(y);
      if (y == 'true') {
        $('#'+x).attr('checked', true);
      }


    });

    $.each(rs.ttv, function(x, y) {
      $('#'+y.id).val(y.anamnesa_isi_nilai);
    });

    if (rs.cek == '0bstetri') {
      $('#keluhanUtama').val(rs.keluhanUtama);
    } else {
      //$('#keluhan_utama').val(rs.keluhanUtama);
      $('#keluhan_utama').val(rs.keluhanGinek);
    }

    if (rs.mual != '') {$('#mual').attr('checked', true);}
    if (rs.muntah != '') {$('#muntah').attr('checked', true);}
    if (rs.pusing != '') {$('#pusing').attr('checked', true);}
    if (rs.perut_sakit != '') {$('#perut_sakit').attr('checked', true);}

    if (rs.kepala != '') {$('#kepala').attr('checked', true);}
    if (rs.sungsang != '') {$('#sungsang').attr('checked', true);}
    if (rs.ablique != '') {$('#oblique').attr('checked', true);}
    if (rs.lintang != '') {$('#lintang').attr('checked', true);}

    if (rs.sedikit != '') {$('#pendarahan_sedikit').attr('checked', true);}
    if (rs.banyak != '') {$('#pendarahan_banyak').attr('checked', true);}

    if (rs.sedikitGin != '') {$('#sedikit').attr('checked', true);}
    if (rs.banyakGin != '') {$('#banyak').attr('checked', true);}

    $('#hpht2').val(rs.hpht);
    // $('#hpltp').val(rs.hpht);
    $('#hpl2').val(rs.hpl);

    $('#tinggi_fu').val(rs.FU);

    $('#amenore_hari').val(rs.AmenoreHari);
    $('#amenore_bulan').val(rs.AmenoreBln);

    $('#haid_lama_hari').val(rs.HailLama);
    $('#haid_lama_banyak_hari').val(rs.HaidLamaBanyak);
    $('#haid_lama_bulan').val(rs.HaidBulan);
    $('#terus_menerus_lama').val(rs.TerusMenerus);
    $('#abc').val(rs.id_rawat);

    $('#terus_menerus_hari').val(rs.TerusMenerusHari);

    // $('#planning_penatalaksanaan').val(rs.planning_penatalaksanaan);
    
    $('#sudah_berapa_lama').val(rs.BerapaLama);

    if (rs.cek == '0bstetri') {
        $('#g_obstet').val(rs.g);
        $('#p_obstet').val(rs.p);
        $('#a_obstet').val(rs.a);
    }else{
        $('#g_ginek').val(rs.g);
        $('#p_ginek').val(rs.p);
        $('#a_ginek').val(rs.a);
    }
    // if (rs.layanan == 'Ginekology') {
    //   $('#keadaan_umum_pasien_ginek').val(rs.keadaan_umum_pasien);
    //   $('#kesadaran').val(rs.kesadaran);
    // } else {
    //   $('#kesadaran_ginek').val(rs.kesadaran);
    //   $('#keadaan_umum_pasien').val(rs.keadaan_umum_pasien);
    // }
    $('keadaan_umum_pasien_ginek').val(rs.keadaan_umum_pasien);

    if (rs.gatal != '') {$('#gatal').attr('checked', true);}
    if (rs.tidak_gatal != '') {$('#tidak_gatal').attr('checked', true);}
    if (rs.bau != '') {$('#bau').attr('checked', true);}
    if (rs.tidak_bau != '') {$('#tidak_bau').attr('checked', true);}
    if (rs.campur_darah != '') {$('#campur_darah').attr('checked', true);}
    if (rs.Lainnya != '') {$('#lainnya').attr('checked', true);}
    if (rs.perut_sakitt != '') {$('#perut_sakitt').attr('checked', true);}
    if (rs.tumor != '') {$('#tumor').attr('checked', true);}
    if (rs.myom_uteri != '') {$('#myom_uteri').attr('checked', true);}
    if (rs.kista_ovari != '') {$('#kista_ovari').attr('checked', true);}
    if (rs.ca_cx != '') {$('#ca_cx').attr('checked', true);}
    if (rs.lainnya != '') {$('#lainnyaa').attr('checked', true);}
    
  })
}
