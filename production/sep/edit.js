$(function() {
  init_asalRujukan();
  init_icd10();
  init_page();
  init_poliBpjs();

  var form_sep = $('#form_sep');
  form_sep.submit(function(e) {
    e.preventDefault();
    var datas = form_sep.serializeArray();
    datas.push(
      { name: "cust_usr_no_hp", value: $('#cust_usr_no_hp').val() },
      { name: "cust_usr_kode", value: $('#cust_usr_kode').val() }
    );
    $.ajax({
      url: 'update.php',
      type: 'POST',
      data: datas,
      dataType: 'json',
      beforeSend: function () {
        $('.bpjs-loader').css('display', 'block');
      },
      success: function(rspns) {
        $('.bpjs-loader').css('display', 'none');
        if (rspns.metaData.code != 200) {
          new PNotify({
            title: 'Error',
            text: rspns.metaData.message,
            type: 'error',
            styling: 'bootstrap3'
          });
        } else {
          new PNotify({
            title: 'Sukses',
            text: 'Data SEP '+rspns.response+' berhasil diperbaharui',
            type: 'success',
            styling: 'bootstrap3',
            addclass: 'dark'
          });
          
        }
      }
    });
  })
});

function init_page() {
  console.log('init combobox di page edit');
  reg_id = $('#id_reg').val();
  $.getJSON('ref-sep.php', {reg_id: reg_id}).done(function(r) {
    init_dpjp();
    setTimeout(function() {$('#skdp_noDPJP').val(r.skdp_no_dpjp);}, 400);
    $('#rujukan_asalRujukan').val(r.rujukan_asal_rujukan);
    $('#klsRawat').val(r.kls_rawat);
    $('#klsRawat').val(r.kls_rawat);
    $('#poli_eksekutif').val(r.poli_eksekutif);
    $('#cob').val(r.cob);
    $('#katarak').val(r.katarak);
    $('#jaminan_lakaLantas').val(r.jaminan_lakalantas);
    if(r.jaminan_lakalantas == '1'){
      init_laka(r.jaminan_lakalantas);
      var sl = r.laka_penjamin.split(',');
      $('#laka_penjamin').val(sl).trigger("change");
      $("#laka_noSepSuplesi").val(r.laka_nosep_suplesi);
      $('#laka_kdPropinsi').on( "loaded", function(e) {
        e.preventDefault();
        init_kabupaten(r.laka_kdpropinsi);
        $('#laka_kdPropinsi').val(r.laka_kdpropinsi);
      });
      $('#laka_kdKabupaten').on( "loaded", function(e) {
        e.preventDefault();
        init_kecamatan(r.laka_kdkabupaten);
        $('#laka_kdKabupaten').val(r.laka_kdkabupaten);
      });
      $('#laka_kdKecamatan').on( "loaded", function() {
        $('#laka_kdKecamatan').val(r.laka_kdkecamatan);
        // init_suplesi(r.laka_suplesi);
      });
    }
  });
}

function init_asalRujukan() { //non mapping
  console.log('init_init_asalRujukan_bpjs');
  $('#rujukan_ppkRujukan_txt').autocomplete({
    serviceUrl: uri+'ref-faskes.php',
      paramName: 'param',
      params: {'jenis': $('#rujukan_asalRujukan').val()},
      minChars: 3,
      transformResult: function(rspns) {
        var data = jQuery.parseJSON(rspns);
        return {
            suggestions: $.map(data.response.faskes, function(item) {
                return { value: item.nama, data: item.kode };
            })
        };
      },
      onSelect: function (suggestion) {
      $('#rujukan_ppkRujukan').val(suggestion.data);
        // getDPJP();
      }
    })
};

function dateToDMY(dt) {
  var date = new Date(dt);
    var d = date.getDate();
    var m = date.getMonth()+1;
    var y = date.getFullYear();
    return d + '-' + m + '-' + y;
}


function init_icd10() { //non mapping
  console.log('init_icd10_bpjs');
  $('#diagAwal_txt').autocomplete({
    serviceUrl: uri+'ref-diagnosa.php',
      paramName: 'q',
      minChars: 3,
      transformResult: function(rspns) {
        var data = jQuery.parseJSON(rspns);
        return {
            suggestions: $.map(data.response.diagnosa, function(item) {
                return { value: item.nama, data: item.kode };
            })
        };
      },
      onSelect: function (suggestion) {
      $('#diagAwal').val(suggestion.data);
        // getDPJP();
      }
    });
};

function init_dpjp() {

  console.log('init_dpjp');

  jnsLayanan = $('#jnsPelayanan').val();
  tglLayanan = $('#tglSep').val();
  poli_bpjs = $('#poli_tujuan').val();
  dpjp = $('#skdp_noDPJP');

  dpjp.empty();

  dpjp.append('<option selected="true" value="">Pilih DPJP</option>');
  dpjp.prop('selectedIndex', 0);

  $.ajax({
    url: uri+'ref-dpjp.php',
    type: 'get',
    data: { jnsLayanan: jnsLayanan , tglLayanan: tglLayanan , poli_bpjs: poli_bpjs },
    dataType: 'json',
    beforeSend: function () {
      $('.bpjs-loader').css('display', 'block');
    },
    success: function(rspns) {
      $('.bpjs-loader').css('display', 'none');
      if (rspns.metaData.code == '200') {
        $.each(rspns.response.list, function (key, item) {
          dpjp.append($('<option></option>').attr('value', item.kode+'-'+item.nama).text(item.nama));

        })
      } else {
        new PNotify({
          title: 'Info',
          text: 'Data Dokter : '+rspns.metaData.message,
          type: 'info',
          styling: 'bootstrap3'
        });
      }
    },
    error: function () {
      $('.bpjs-loader').css('display', 'none');

      new PNotify({
            title: 'Error',
            text: 'Sambungan gagal',
            type: 'error',
            styling: 'bootstrap3'
        });
    }
  });
}
function init_penjamin() {       
  console.log('init_penjamin');
  var data = [
      {
          id: 1,
          text: 'Jasa raharja PT'
      },
      {
          id: 2,
          text: 'BPJS Ketenagakerjaan'
      },
      {
          id: 3,
          text: 'TASPEN PT'
      },
      {
          id: 4,
          text: 'ASABRI PT'
      }
  ];
  $("#laka_penjamin").select2({
      multiple: true,
      data: data,
      width: 'resolve',
      tokenSeparators: [',']
    });
};

function init_propinsi() {
  console.log('init propinsi');

    dropdown = $('#laka_kdPropinsi');

    dropdown.empty();

    dropdown.append('<option selected="true" value="">Pilih Propinsi</option>');
    dropdown.prop('selectedIndex', 0);


    $.ajax({
    url: uri+'ref-propinsi.php',
    type: 'get',
    dataType: 'json',
    beforeSend: function () {
      $('.bpjs-loader').css('display', 'block');
    },
    success: function(rspns) {
      $('.bpjs-loader').css('display', 'none');
      $.each(rspns.response.list, function (key, item) {
          dropdown.append($('<option></option>').attr('value', item.kode).text(item.nama));
      });
    },
    error: function () {
      $('.bpjs-loader').css('display', 'none');

      new PNotify({
                title: 'Error',
                text: 'Sambungan gagal',
                type: 'error',
              styling: 'bootstrap3'
            });
    }
  }).done(function () {
    dropdown.trigger('loaded');
  });
}

function init_kabupaten(prop) {
  console.log('init kabupaten')
    if (!prop) { prop = $('#laka_kdPropinsi').val(); }
    dropdown = $('#laka_kdKabupaten');

    dropdown.empty();

    dropdown.append('<option selected="true" value="">Pilih Kabupaten/Kota</option>');
    dropdown.prop('selectedIndex', 0);

    $.ajax({
    url: uri+'ref-kabupaten.php',
    type: 'get',
    data: { kdPropinsi: prop },
    dataType: 'json',
    beforeSend: function () {
      $('.bpjs-loader').css('display', 'block');
    },
    success: function(rspns) {
      $('.bpjs-loader').css('display', 'none');
      $.each(rspns.response.list, function (key, item) {
          dropdown.append($('<option></option>').attr('value', item.kode).text(item.nama));
      });
    },
    error: function () {
      $('.bpjs-loader').css('display', 'none');

      new PNotify({
                title: 'Error',
                text: 'Sambungan gagal',
                type: 'error',
              styling: 'bootstrap3'
            });
    }
  }).done(function () {
    dropdown.trigger('loaded');
  });
}

function init_kecamatan(kab) {
    console.log('init kecamatan');
    if (!kab) { kab = $('#laka_kdKabupaten').val(); }
    dropdown = $('#laka_kdKecamatan');

    dropdown.empty();

    dropdown.append('<option selected="true" value="">Pilih Kecamatan</option>');
    dropdown.prop('selectedIndex', 0);

    $.ajax({
    url: uri+'ref-kecamatan.php',
    type: 'get',
    data: { kdKabupaten: kab },
    dataType: 'json',
    beforeSend: function () {
      $('.bpjs-loader').css('display', 'block');
    },
    success: function(rspns) {
      $('.bpjs-loader').css('display', 'none');
      $.each(rspns.response.list, function (key, item) {
          dropdown.append($('<option></option>').attr('value', item.kode).text(item.nama));
      });
      setTimeout(function() {dropdown.trigger('loaded')}, 200);      
    },
    error: function () {
      $('.bpjs-loader').css('display', 'none');

      new PNotify({
                title: 'Error',
                text: 'Sambungan gagal',
                type: 'error',
              styling: 'bootstrap3'
            });
    }
  });
}

function init_laka(val) {
  console.log('init laka '+val);
    if (val == 0) {
      $("#laka-group").css('display','none');
    } else {
      $("#laka-group").css('display','block');
      init_penjamin();
      init_propinsi();
      init_suplesi(0);
    }
}

function init_suplesi(val) {
  console.log('init suplesi '+val);

   // val = $('#reg_lakalantas_suplesi').val();
    if (val == 0) {
      $("#suplesiY").css('display','none');
      $("#suplesiN").css('display','block');
      $("#btnCariSuplesi").attr("disabled","disabled");
    } else {
      $("#suplesiY").css('display','block');
      $("#suplesiN").css('display','none');
      $("#btnCariSuplesi").removeAttr("disabled"); //enable 
      $("#btnCariSuplesi").trigger( "click" ); // buka modal
    }
}

function cariSuplesi() {
    tglLayanan = $('#tglSep').val();
    noKartu = $('#noKartu').val();
    $.ajax({
      type: 'GET',
      url: uri+'sep-suplesi.php',
      data: { noKartu: noKartu, tglLayanan: tglLayanan },
      dataType: 'json',
      beforeSend: function() {
    $('.bpjs-loader').css('display', 'block');
      },
      success:function(result){
    $('.bpjs-loader').css('display', 'none');

        var status = result.metaData.code;
        var messagesresponse = result.metaData.message;

        if(status != '200'){
      new PNotify({
                title: 'Error',
                text: messagesresponse,
                type: 'error',
              styling: 'bootstrap3'
            });

          $('#laka_suplesi').val(0);
          init_suplesi(0);
        }else{
          datas = result.response.jaminan;
          var html = '';

          html  +=  '<table id="tabel" class="table table-bordered table-hover">';
          html  +=    '<thead>';
          html  +=      '<th>No SEP</th>';
          html  +=      '<th>No SEP Awal</th>';
          html  +=      '<th>Tgl SEP</th>';
          html  +=      '<th>Tgl Kejadian</th>';
          html  +=      '<th>No Register</th>';
          html  +=      '<th>No Surat Jaminan</th>';
          html  +=      '<th></th>';
          html  +=    '</thead>';
          html  +=    '<tbody>';
          for (i = datas.length - 1; i >= 0; i--) {
            //var data = new Array('\''+datas[i].noSep+'\'', '\''+datas[i].tglSep+'\'', '\''+datas[i].noRegister+'\'', '\''+datas[i].noSuratJaminan+'\'' );
            //console.log(data);
            html  +=      '<tr>';
            html  +=        '<td>'+datas[i].noSep+'</td>';
            html  +=        '<td>'+datas[i].noSepAwal+'</td>';
            html  +=        '<td>'+datas[i].tglSep+'</td>';
            html  +=        '<td>'+datas[i].tglKejadian+'</td>';
            html  +=        '<td>'+datas[i].noRegister+'</td>';
            html  +=        '<td>'+datas[i].noSuratJaminan+'</td>';
            html  +=        '<td><button class="btn btn-xs btn-success" onclick="setSepSuplesi(\''+datas[i].noSep+'\', \''+datas[i].tglSep+'\', \''+datas[i].noRegister+'\', \''+datas[i].noSuratJaminan+'\')"> Pilih </button></td>';
            html  +=      '</tr>';
          }
          html  +=    '</tbody>';
          html  +=  '</table>';

          $('#listSEPSuplesi').html(html); 
          $('#modal-suplesi').modal('show'); 
          
        }
        
      },
      error: function () {
        $('.bpjs-loader').css('display', 'none');

    new PNotify({
            title: 'Error',
            text: 'Sambungan gagal',
            type: 'error',
          styling: 'bootstrap3'
        });
      }
    });    
}

function setSepSuplesi() {
    var datas = Array.from(arguments);
    $('#modal-suplesi').modal('hide'); 
    $("#laka_keterangan").removeAttr("disabled"); //enable 
    $('#laka_penjamin').select2( 'val', '1' );
    $('#laka_noSepSuplesi').val(datas[0]);
    msg = 'Kasus Penjaminan ini masih tanggungan dari No.SEP '+datas[0]+' Tgl.SEP '+datas[1]+' dengan No.Register '+datas[2]+' dan Surat Jaminan '+datas[3];
    $('#suplesiY').append(msg); 
    $('#laka_keterangan').text(msg);
}

function init_poliBpjs() {  //non mapping
  console.log('init_poli_bpjs');
  $('#poli_tujuan_txt').autocomplete({
    serviceUrl: uri+'ref-poli.php',
      paramName: 'q',
      minChars: 3,
      transformResult: function(rspns) {
        var data = jQuery.parseJSON(rspns);
        return {
            suggestions: $.map(data.response.poli, function(item) {
                return { value: item.nama, data: item.kode };
            })
        };
      },
      onSelect: function (suggestion) {
      $('#poli_tujuan').val(suggestion.data);
        // getDPJP();
      }
    });
};

function createSep() {
  var form_sep = $('#form_sep');
  var datas = form_sep.serializeArray();
  datas.push(
    { name: "noMR", value: $('#cust_usr_kode').val() },
    { name: "noTelp", value: $('#cust_usr_no_hp').val() },
  );

  $.ajax({
    url: uri+'create-sep.php',
    type: 'post',
    data: datas,
    dataType: 'json',
    beforeSend: function () {
      $('.bpjs-loader').css('display', 'block');
    },
    success: function(rspns) {
      $('.bpjs-loader').css('display', 'none');
      if (rspns.metaData.code != 200) {
        new PNotify({
                    title: 'Error',
                    text: rspns.metaData.message,
                    type: 'error',
                  styling: 'bootstrap3'
                });
      } else {
        rspn = rspns.response.sep;
        new PNotify({
                    title: 'Sukses',
                    text: rspn.noSep,
                    type: 'success',
                  styling: 'bootstrap3',
                  addclass: 'dark'
                });

                $('#noSep').val(rspn.noSep);
                $('#btnSep').attr('disabled', 'disabled');
      }
    },
    error: function () {
      $('.bpjs-loader').css('display', 'none');

      new PNotify({
                title: 'Error',
                text: 'Sambungan gagal',
                type: 'error',
              styling: 'bootstrap3'
            });
    }
  });
}
