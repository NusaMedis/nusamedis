<footer>
  <div class="pull-right"></div>
  <div class="clearfix"></div>
</footer>

 <script>
var idleTime = 0;
$(document).ready(function () {
    //Increment the idle time counter every minute.
    var idleInterval = setInterval(timerIncrement, 3600000); // 1 jam, satuan ms
    //Zero the idle timer on mouse movement.
    $(this).mousemove(function (e) {
        idleTime = 0;
    });
    $(this).keypress(function (e) {
        idleTime = 0;
    });
    console.log(idleInterval);
});

function timerIncrement() {
    idleTime = idleTime + 1;
    if (idleTime > 10 ){ // 60 minutes
        logoutOtomatis()
    }
}

function logoutOtomatis(){
    window.parent.document.location.href="../../login/logout.php";
}
</script>