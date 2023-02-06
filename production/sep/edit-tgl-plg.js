$(function() {
	var form_sep = $('#form_sep');
	form_sep.submit(function(e) {
		e.preventDefault();
		var datas = form_sep.serializeArray();
    datas.push(
      { name: "noSep", value: $('#noSep').val() }
    );
		$.ajax({
			url: 'update-tgl-plg.php',
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
            text: 'Tanggal Pulang SEP '+rspns.response+' berhasil diperbaharui',
            type: 'success',
            styling: 'bootstrap3',
            addclass: 'dark'
          });
          window.history.back();
        }
			}
		});
	})
});
