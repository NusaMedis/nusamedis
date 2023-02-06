$(function() {
	var form = $('#form_pengajuan');
	form.submit(function(e) {
		e.preventDefault();
		$.ajax({
			url:'sep-pengajuan.php',
			type: 'POST',
			data: form.serialize(),
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
					rspn = rspns.response;
					new PNotify({
	                    title: 'Sukses',
	                    text: 'Pengajuan SEP Noka '+rspn.response+' berhasil',
	                    type: 'success',
	                	styling: 'bootstrap3',
	                	addclass: 'dark'
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
	}) 
})


function aproval(sep_id, nama,noka,jnspelayanan,keterangan,tglSep) {
	var datas = {sep_id: sep_id, nama: nama,noka:noka,jnspelayanan:jnspelayanan,keterangan:keterangan,tglSep:tglSep};
 
  var r = confirm("Yakin Anda Akan Setujui Pembuatan SEP an "+nama);
  if (r == true) {
        	$.ajax({
			url: 'kirim_aproval.php',
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
					rspn = rspns.response;
					new PNotify({
	                    title: 'Sukses',
	                    text: 'Pengajuan SEP Noka '+rspn.response+' berhasil',
	                    type: 'success',
	                	styling: 'bootstrap3',
	                	addclass: 'dark'
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
  } else {
    
  }
  
}

function cek_kepesertaan(noka) {
	var dt = new Date();
	var now = [ dt.getDate(), dt.getMonth()+1, dt.getFullYear() ].join('-');
	var datas = {param: noka, tglSep: now};
	if (tglSep != '') {
		$.ajax({
			url: uri+'cek-kepesertaan.php',
			type: 'GET',
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
					rspn = rspns.response.peserta;
					new PNotify({
	                    title: 'Sukses',
	                    text: 'Status peserta '+rspn.statusPeserta.keterangan+' atas nama '+rspn.nama,
	                    type: 'success',
	                	styling: 'bootstrap3',
	                	addclass: 'dark'
	                });

	                $('#nama_peserta').val(rspn.nama);
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
	} else {
		new PNotify({
            title: 'Error',
            text: 'Tanggal Sep tidak boleh kosong',
            type: 'error',
        	styling: 'bootstrap3'
        });
	}
}