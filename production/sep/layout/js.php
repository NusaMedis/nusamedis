
    <!-- jQuery -->
    <script src="../assets/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../assets/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../assets/vendors/nprogress/nprogress.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../assets/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- Select2 -->
    <script src="../assets/vendors/select2/dist/js/select2.full.min.js"></script>
    <!-- PNotify -->
    <script src="../assets/vendors/pnotify/dist/pnotify.js"></script>
    <script src="../assets/vendors/pnotify/dist/pnotify.buttons.js"></script>
    <script src="../assets/vendors/pnotify/dist/pnotify.nonblock.js"></script>
    <!-- jQuery autocomplete -->
    <script src="../assets/vendors/devbridge-autocomplete/dist/jquery.autocomplete.min.js"></script>
    <!-- jquery.inputmask -->
    <script src="../assets/vendors/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../assets/build/js/custom.min.js"></script>
    <script type="text/javascript">
        // var uri = "http://125.213.159.147/muslimat/production/bpjs/sys/"
        // var uri = "http://101.50.2.69/muslimat/production/sep/sys/"
        var uri = "sys/"

        $(function() {
          //input mask
          $('.tgl').inputmask("dd-mm-yyyy");
          $('.tglwkt').inputmask("dd-mm-yyyy 99:99:99");
          $('.waktu').inputmask({"mask": "99:99:99"});
        })
    </script>
    <?php if (is_array($custom_script) || is_object($custom_script)) { ?>
    <?php foreach ($custom_script as $key => $value) { ?>
        <script src="<?php echo $value ?>"></script>
    <?php } ?>
    <?php } ?>

    <!-- iCheck -->
    <!-- <script src="../assets/vendors/iCheck/icheck.min.js"></script> -->
    <!-- bootstrap-daterangepicker -->
    <!-- <script src="../assets/vendors/moment/min/moment.min.js"></script> -->
    <!-- <script src="../assets/vendors/bootstrap-daterangepicker/daterangepicker.js"></script> -->
    <!-- bootstrap-wysiwyg -->
    <!-- <script src="../assets/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js"></script> -->
    <!-- <script src="../assets/vendors/jquery.hotkeys/jquery.hotkeys.js"></script> -->
    <!-- <script src="../assets/vendors/google-code-prettify/src/prettify.js"></script> -->
    <!-- jQuery Tags Input -->
    <!-- <script src="../assets/vendors/jquery.tagsinput/src/jquery.tagsinput.js"></script> -->
    <!-- Switchery -->
    <!-- <script src="../assets/vendors/switchery/dist/switchery.min.js"></script> -->
    <!-- Parsley -->
    <!-- <script src="../assets/vendors/parsleyjs/dist/parsley.min.js"></script> -->
    <!-- Autosize -->
    <!-- <script src="../assets/vendors/autosize/dist/autosize.min.js"></script> -->
    <!-- starrr -->
    <!-- <script src="../assets/vendors/starrr/dist/starrr.js"></script> -->