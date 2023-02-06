$(function() {
	init_poliBpjs();
	init_asalRujukan();
	init_icd10();

	var form_sep = $('#form_sep');
	form_sep.submit(function(e) {
		e.preventDefault();
		datas = form_sep.serializeArray();
	    datas.push(
	      { name: "no_rujukan", value: $('#no_rujukan').val() }
	    );
		
		$.ajax({
			url: 'update-rujukan.php',
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
		            text: 'Update SEP '+rspns.response+' berhasil diperbaharui',
		            type: 'success',
		            styling: 'bootstrap3',
		            addclass: 'dark'
		          });
		          // window.history.back();
		        }
			}
		})

	})
});

function init_poliBpjs() {	//non mapping
	console.log('init_poli_bpjs');
	$('#poli_rujukan_txt').autocomplete({
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
	    $('#poli_rujukan').val(suggestion.data);
      }
    });
};

function init_asalRujukan() {	//non mapping
	console.log('init_init_asalRujukan_bpjs');
	$('#ppk_dirujuk_txt').autocomplete({
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
	    $('#ppk_dirujuk').val(suggestion.data);
      }
    })
};

function init_icd10() {	//non mapping
	console.log('init_icd10_bpjs');
	$('#diag_rujukan_txt').autocomplete({
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
	    $('#diag_rujukan').val(suggestion.data);
      }
    });
};