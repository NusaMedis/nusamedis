$(function() {

  init_page();

function init_page() {
  console.log('init combobox di page edit');
  noKontrol = $('#noKontrol').val();
  $.getJSON('ref-kontrol.php', {noKontrol: noKontrol}).done(function(r) {
  
    $('#nosep').val(r.rujukan_asal_rujukan);
    $('#noKartu').val(r.kls_rawat);
    $('#nama').val(r.kls_rawat);
    $('#tgllahir').val(r.poli_eksekutif);
    $('#diagnosa').val(r.cob);
    $('#katarak').val(r.katarak);
    $('#jaminan_lakaLantas').val(r.jaminan_lakalantas);

  });
}

