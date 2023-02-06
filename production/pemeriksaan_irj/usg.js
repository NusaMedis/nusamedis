$(function() {
  var form = $('#form_obgyns');
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
  })
});

function tbuiusg(as) {
  $.getJSON('get_usg.php', {rawat_id: as },function(rs) {
    // end table riwayat kehamilan
    $('#form_obgyn').find(':checkbox').removeAttr('checked');
    // $('#form_obgyn').trigger('reset');
    $.each(rs.dataObgyn, function(a,b) {
      $('#'+a+'l').val(b);
      if (b == 'true') {
        $('#'+a+'l').attr('checked', true);
      }
    });
};