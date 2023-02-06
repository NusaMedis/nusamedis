$(document).ready(function() {
	init_poliBpjs();
	init_asalRujukan();
	init_icd10();

	var form_list_rujukan = $('#form_list_rujukan');
	form_list_rujukan.submit(function(e) {
		e.preventDefault();
		$.ajax({
			url: uri+'rujukan-list.php',
			type: 'GET',
			data: form_list_rujukan.serialize(),
			dataType: 'json',
			beforeSend: function () {
				$('.bpjs-loader').css('display', 'block');
			},
			success: function (rspns) {
				$('.bpjs-loader').css('display', 'none');
				if (rspns.metaData.code != 200) {
					new PNotify({
						title: 'Info',
						text: rspns.metaData.message,
						type: 'info',
						styling: 'bootstrap3'
					});
				} else {
					rspn = rspns.response.rujukan;
					new PNotify({
						title: 'Sukses',
						text: 'Rujukan ditemukan',
						type: 'success',
						styling: 'bootstrap3',
						addclass: 'dark'
					});
					var html = '';
					html  +=  '<table id="table" class="table table-bordered table-hover">';
					html  +=    '<thead>';
					html  +=      '<th>No Rujukan</th>';
					html  +=      '<th>Tgl Rujukan</th>';
					html  +=      '<th>No Kartu</th>';
					html  +=      '<th>Nama</th>';
					html  +=      '<th>PPK Perujuk</th>';
					html  +=      '<th>Sub/Spesialis</th>';
					html  +=    '</thead>';
					html  +=    '<tbody>';
					$.each(rspn, function (x,y) {
						html  +=      '<tr>';
						html  +=        '<td><a class="btn btn-xs btn-warning" onclick="setNoRujukan(\''+y.noKunjungan+'\')">'+y.noKunjungan+'</a></td>';
						html  +=        '<td>'+y.tglKunjungan+'</td>';
						html  +=        '<td>'+y.peserta.noKartu+'</td>';
						html  +=        '<td>'+y.peserta.nama+'</td>';
						html  +=        '<td>'+y.provPerujuk.nama+'</td>';
						html  +=        '<td>'+y.poliRujukan.nama+'</td>';
						html  +=      '</tr>';
					})
					html  +=    '</tbody>';
					html  +=  '</table>';

					$('#listRujukan').html(html);					
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
		})
	})

	var form_manual = $('#form_manual');
	form_manual.submit(function(e) {
		e.preventDefault();
		var datas = form_manual.serializeArray();
		tglSep = $('#tglSep_').val();
		noSep = $('#noSep').val();
		datas.push({ name: "tglSep", value: tglSep });
		if (tglSep != '' ) {
			$.ajax({
				url: uri+'cek-kepesertaan.php',
				type: 'GET',
				data: datas,
				dataType: 'json',
				beforeSend: function () {
					$('.bpjs-loader').css('display', 'block');
					if ($('#jnsPelayanan_').val() == '1') {
						$('#poli_eksekutif').attr('disabled', 'disabled');
						$('#poli_tujuan_txt').attr('disabled', 'disabled');
						$('#poli_tujuan_txt').val('');
					} else {
						$('#poli_eksekutif').removeAttr('disabled');
						$('#poli_tujuan_txt').removeAttr('disabled');
					}
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

						if (rspn.statusPeserta.kode == 0) {
							$('#panelA').css('display', 'none');
							$('#panelB').css('display', 'inline-block');
							$('#tglSep').attr('readonly', 'readonly');
							$('#klsRawat_txt').attr('readonly', 'readonly');

							var jns = rspn.jenisPeserta.keterangan;
							var pbi = jns.match(/PBI/);
							if (pbi) {
								$('#tipe_jkn').val('1');
							} else {
								$('#tipe_jkn').val('2');
							}



							$('#noKartu').val(rspn.noKartu);
							$('#namatxt').val(rspn.nama);
							$('#jenisPeserta_txt').val(rspn.jenisPeserta.keterangan);
							$('#noKartu1').val(rspn.noKartu);
							$('#namatxt1').val(rspn.nama);
							$('#jenisPeserta_txt1').val(rspn.jenisPeserta.keterangan);
							$('#rujukan_ppkRujukan').val(rspn.provUmum.kdProvider);
							$('#rujukan_ppkRujukan_txt').val(rspn.provUmum.nmProvider);
							$('#tglSep').val($('#tglSep_').val());
							$('#klsRawat_txt').val(rspn.hakKelas.keterangan);
							$('#klsRawat').val(rspn.hakKelas.kode);
							$('#jnsPelayanan').val($('#jnsPelayanan_').val());
							$('#tglLahir').val(rspn.tglLahir);
							$('#rujukan_noRujukan').val(rspn.noKunjungan);
							var tglLahir=$("#tglLahirInt").val();


							if (rspn.tglLahir != tglLahir) {
								new PNotify({
									title: 'Error',
									text: 'Tanggal Lahir di BPJS '+rspn.tglLahir+' dengan data SIMRS Berbeda '+tglLahir,
									type: 'error',
									styling: 'bootstrap3'

								});

							}
						}
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
	})

	//
	var form_rujukan = $('#form_rujukan');
	form_rujukan.submit(function(e) {
		e.preventDefault();
		tglSep = $('#tglSep_').val();
		var datas = form_rujukan.serializeArray();
		datas.push(
			{ name: "tipe_param", value: 1 }
		);
		if (tglSep != '') {
			$.ajax({
				url: uri+'cek-rujukan.php',
				type: 'GET',
				data: datas,
				dataType: 'json',
				beforeSend: function () {
					$('.bpjs-loader').css('display', 'block');
					$('#rujukan_noRujukan').attr('readonly', 'readonly');

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
						rspn = rspns.response.rujukan;

						var jmlReg = $("#jumlahReg").val();
						var rows = (jmlReg * 1) + 1;
						// alert(rows+1);
						new PNotify({
		                    title: 'Notice',
		                    text: 'Data Kunjungan ke '+rows+' berdasarkan no rujukan '+rspn.noKunjungan,
		                    type: 'success',
		                	styling: 'bootstrap3',
		                	addclass: 'dark'
		                });
						new PNotify({
		                    title: 'Sukses',
		                    text: 'Rujukan ditemukan ke poli '+rspn.poliRujukan.nama+' dari '+rspn.provPerujuk.nama,
		                    type: 'success',
		                	styling: 'bootstrap3',
		                	addclass: 'dark'
		                });

		                if (rspn.peserta.statusPeserta.kode == 0) {
		                	new PNotify({
			                    title: 'Sukses',
			                    text: 'Status peserta '+rspn.peserta.statusPeserta.keterangan+' atas nama '+rspn.peserta.nama,
			                    type: 'success',
			                	styling: 'bootstrap3',
			                	addclass: 'dark'
			                });

							$('#panelA').css('display', 'none');
		                	$('#panelB').css('display', 'inline-block');
		                	$('#tglSep').attr('readonly', 'readonly');
					        $('#klsRawat_txt').attr('readonly', 'readonly');

							var jns = rspn.peserta.jenisPeserta.keterangan;
					        var pbi = jns.match(/PBI/);
					        if (pbi) {
					            $('#tipe_jkn').val('1');
					        } else {
					            $('#tipe_jkn').val('2');
					        }
					        var tglRujuk = rspn.tglKunjungan.split("-");

							$('#noKartu').val(rspn.peserta.noKartu);
							$('#namatxt').val(rspn.peserta.nama);
							$('#jenisPeserta_txt').val(rspn.peserta.jenisPeserta.keterangan);
							$('#poli_tujuan').val(rspn.poliRujukan.kode);
							$('#poli_tujuan_txt').val(rspn.poliRujukan.nama);
							$('#rujukan_asalRujukan').val($('#rujukan_asalRujukan_').val());
							$('#rujukan_ppkRujukan').val(rspn.provPerujuk.kode);
							$('#rujukan_ppkRujukan_txt').val(rspn.provPerujuk.nama);
				            $('#rujukan_tglRujukan').val(tglRujuk[2]+'-'+tglRujuk[1]+'-'+tglRujuk[0]);
							$('#rujukan_noRujukan').val(rspn.noKunjungan);
							$('#tglSep').val($('#tglSep_').val());
							$('#klsRawat_txt').val(rspn.peserta.hakKelas.keterangan);
							$('#klsRawat').val(rspn.peserta.hakKelas.kode);
							$('#jnsPelayanan').val(rspn.pelayanan.kode);
							$('#diagAwal').val(rspn.diagnosa.kode);
							$('#diagAwal_txt').val(rspn.diagnosa.kode+' - '+rspn.diagnosa.nama);
							$('#tglLahir').val(rspn.peserta.tglLahir);

							var tglLahirInt=$("#tglLahirInt").val();


							if (rspn.peserta.tglLahir != tglLahirInt) {
									new PNotify({
				                    title: 'Error',
				                    text: 'Tanggal Lahir di BPJS '+rspn.peserta.tglLahir+' dengan data SIMRS Berbeda '+tglLahirInt,
				                    type: 'error',
				                	styling: 'bootstrap3'
				                	
				                });

							}

						} else {
							new PNotify({
			                    title: 'Error',
			                    text: 'Status peserta '+rspn.peserta.statusPeserta.keterangan+' atas nama '+rspn.peserta.nama,
			                    type: 'error',
			                	styling: 'bootstrap3'
			                });
						}
					}
				},
				error: function () {
					$('.bpjs-loader').css('display', 'none');
					new PNotify({
	                    title: 'Gagal',
	                    text: 'gagal',
	                    type: 'error',
	                	styling: 'bootstrap3'
	                });
				}
			}).done(function() {
				init_dpjp();
			});
		} else {
			new PNotify({
                title: 'Error',
                text: 'Tanggal Sep tidak boleh kosong',
                type: 'error',
            	styling: 'bootstrap3'
            });
		}
	})
var form_sep = $('#form_sep');
form_sep.submit(function(e) {
	e.preventDefault();
	var datas = form_sep.serializeArray();
	noSep = $('#noSep').val();
	datas.push(
		{ name: "cust_usr_no_hp", value: $('#cust_usr_no_hp').val() }
		);

	if (noSep=='') {
		new PNotify({
			title: 'Error',
			text: 'No Sep tidak boleh kosong Pastikan Sudah Menekan Tombil Create SEP dan No SEP sudah tampil',
			type: 'error',
			styling: 'bootstrap3'
		});


	}
	else{
		$.ajax({
			url: 'store.php',
			type: 'POST',
			data: datas,
			dataType: 'json',
			beforeSend: function () {
				$('.bpjs-loader').css('display', 'block');
			},
			success: function(rspns) {
				$('.bpjs-loader').css('display', 'none');
				if (rspns.success) {
					window.open('print-sep.php?reg_id='+rspns.success+'','_blank');
					// window.location.replace('index.php');
				}
			}
		});

	}



	
})
})

function init_poliBpjs() {	//non mapping
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

function init_asalRujukan() {	//non mapping
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


function init_icd10() {	//non mapping
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
	dropdown = $('#skdp_noDPJP');

	dropdown.empty();

	dropdown.append('<option selected="true" value="">Pilih DPJP</option>');
	dropdown.prop('selectedIndex', 0);

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
					dropdown.append($('<option></option>').attr('value', item.kode+'-'+item.nama).text(item.nama));
				})
			} else {
				new PNotify({
					title: 'Error',
					text: rspns.metaData.message,
					type: 'error',
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
			})
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

function init_kabupaten() {
	console.log('init kabupaten')
	prop = $('#laka_kdPropinsi').val();
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
			})
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

function init_kecamatan() {
	console.log('init kecamatan');
	kab = $('#laka_kdKabupaten').val();
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
			})
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
		$("#laka_noSepSuplesi").val(null);
		$("#laka_penjamin").val(null);
		$("#laka_tglKejadian").val(null);
		$("#laka_kdPropinsi").val(null);
		$("#laka_kdKabupaten").val(null);
		$("#laka_kdKecamatan").val(null);
		$("#laka_keterangan").val(null);
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
   	$("#laka_noSepSuplesi").val(null);
   	$("#laka_penjamin").val(null);
   	$("#laka_tglKejadian").val(null);
   	$("#laka_kdPropinsi").val(null);
   	$("#laka_kdKabupaten").val(null);
   	$("#laka_kdKecamatan").val(null);
   	$("#laka_keterangan").val(null);
   } else {
   	$("#suplesiY").css('display','block');
   	$("#suplesiN").css('display','none');
      $("#btnCariSuplesi").removeAttr("disabled"); //enable
      $("#laka_noSepSuplesi").val(null);
      $("#laka_penjamin").val(null);
      $("#laka_tglKejadian").val(null);
      $("#laka_kdPropinsi").val(null);
      $("#laka_kdKabupaten").val(null);
      $("#laka_kdKecamatan").val(null);
      $("#laka_keterangan").val(null);
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

function setNoRujukan(val) {

	$('#rujukan_noRujukan_').val(val);
	$('#modal-rujukan').modal('hide');
	var kirim = $.post("CekRujukan.php", {
		param : val
	});

	kirim.done(function(data){
		$('#jumlahReg').val(data);
	});

}

function createSep() {
	var form_sep = $('#form_sep');
	var datas = form_sep.serializeArray();
	datas.push(
		{ name: "noMR", value: $('#cust_usr_kode').val() },
		{ name: "noTelp", value: $('#cust_usr_no_hp').val() },
		{ name: "skdp_noDPJP", value: $('#skdp_noDPJP').val() },
		);

	$.ajax({
		url: uri+'sep-create.php',
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
