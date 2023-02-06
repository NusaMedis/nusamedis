$(function() {
  var form = $('#form_kontrol');

  form.submit(function(e) {
    e.preventDefault();
    var divs = $("div#values").find("div").toArray();

    var a = [];
    for ( var i = 0; i < divs.length; i++ ) {
      a.push( divs[ i ].innerHTML );
    }

    var forma = form.serialize();
    
    $.ajax({
      url: 'save_kontrol.php',
      type: 'POST',
      data: {forma : form.serialize(), terapi : a},
      success: function (rs) {
        
        alert(rs);
      }
    }).promise().done(function(){
      var id_rawat = form.find("input#asd").val();
      var url = "cetak_surat_kontrol.php?asd="+id_rawat;
      BukaWindow(url, "Surat Kontrol");
    });
  })

});

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
    } else {
      _wnd_new.focus();
    }
  }
     return false;
}

function kontrol(ok) {
  $.getJSON('get_kontrol.php', {rawat_id: ok },function(rs) {
   
    $("form#form_kontrol").find('#asd').val(rs.asd);
   

    var valuess = rs.terapi;
    var a = [];
    if(valuess != null){
      for ( var i = 0; i < valuess.length; i++ ) {
        a.push("<div>"+valuess[ i ]+"</div>");
      }
      $("form#form_kontrol").find('#values').html(a.join(""));
    }
    else{
      $("form#form_kontrol").find('#values').html("");
    }

    console.log(rs.alasan_kontrol);

    
        $("form#form_kontrol").find('select#alasan').val(rs.alasan_kontrol);
        if(rs.alasan_kontrol == '00'){
          $("form#form_kontrol").find('input#another').val(rs.alasan_lain);
          $("form#form_kontrol").find('input#another').css("display", "block");
        }
        else{
          $("form#form_kontrol").find('input#another').val("");
          $("form#form_kontrol").find('input#another').css("display", "none");
        }
        


        var dateRaw;

        if(rs.tgl_kembali == null){
          dateRaw = Date.now();
        }
        else{
          dateRaw = Date.parse(rs.tgl_kembali);
        }
        
        var dates = new Date(dateRaw);
        var dateFix = dates.getDate()+"-"+(dates.getMonth()+1)+"-"+dates.getFullYear();
     

    $("form#form_kontrol").find('input#tgl_kontrol').val(dateFix);


    //console.log(valuess.length);
    })
};