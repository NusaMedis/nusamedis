<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
	  
    <title>BPJS </title>

    <!-- Bootstrap -->
    <link href="../assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../assets/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="../assets/vendors/select2/dist/css/select2.min.css" rel="stylesheet">
    <!-- PNotify -->
    <link href="../assets/vendors/pnotify/dist/pnotify.css" rel="stylesheet">
    <link href="../assets/vendors/pnotify/dist/pnotify.buttons.css" rel="stylesheet">
    <link href="../assets/vendors/pnotify/dist/pnotify.nonblock.css" rel="stylesheet">
    <?php if (isset($custom_css)) { ?>
    <?php if (is_array($custom_css) || is_object($custom_css)) { ?>
    <?php foreach ($custom_css as $key => $value) { ?>
      <link href="<?php echo $value ?>" rel="stylesheet">
    <?php } ?>
    <?php } ?>
    <?php } ?>
    <!-- iCheck -->
    <!-- <link href="../assets/vendors/iCheck/skins/flat/green.css" rel="stylesheet"> -->
    <!-- bootstrap-wysiwyg -->
    <!-- <link href="../assets/vendors/google-code-prettify/bin/prettify.min.css" rel="stylesheet"> -->
    <!-- Switchery -->
    <!-- <link href="../assets/vendors/switchery/dist/switchery.min.css" rel="stylesheet"> -->
    <!-- starrr -->
    <!-- <link href="../assets/vendors/starrr/dist/starrr.css" rel="stylesheet"> -->
    <!-- bootstrap-daterangepicker -->
    <!-- <link href="../assets/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet"> -->

    <!-- Custom Theme Style -->
    <link href="../assets/build/css/custom.min.css" rel="stylesheet">
    <style type="text/css">
        .bpjs-loader {
          display: none;
          position: fixed;
          z-index: 9999;
        }

        .bpjs-loader span {
          display: inline-block;
          position: fixed;
          top: 50%;
          left: 37%;
          font-size: 1.3em;
          text-transform: bold;
          z-index: 9999;
          background: #2A3F54CC;
          color: #fff;
          padding: 2px 10px;
          border-radius: 5px;
        }
        .lds-ellipsis {
          display: inline-block;
          position: fixed;
          width: 64px;
          height: 64px;
          top: 40%;
          left: 50%;
        }
        .lds-ellipsis div {
          position: absolute;
          top: 27px;
          width: 11px;
          height: 11px;
          border-radius: 50%;
          background: #2A3F54;
          animation-timing-function: cubic-bezier(0, 1, 1, 0);
        }
        .lds-ellipsis div:nth-child(1) {
          left: 6px;
          animation: lds-ellipsis1 0.6s infinite;
        }
        .lds-ellipsis div:nth-child(2) {
          left: 6px;
          animation: lds-ellipsis2 0.6s infinite;
        }
        .lds-ellipsis div:nth-child(3) {
          left: 26px;
          animation: lds-ellipsis2 0.6s infinite;
        }
        .lds-ellipsis div:nth-child(4) {
          left: 45px;
          animation: lds-ellipsis3 0.6s infinite;
        }
        @keyframes lds-ellipsis1 {
          0% {
            transform: scale(0);
          }
          100% {
            transform: scale(1);
          }
        }
        @keyframes lds-ellipsis3 {
          0% {
            transform: scale(1);
          }
          100% {
            transform: scale(0);
          }
        }
        @keyframes lds-ellipsis2 {
          0% {
            transform: translate(0, 0);
          }
          100% {
            transform: translate(19px, 0);
          }
        }


    </style>
  </head>

  <body class="nav-md">
<div class="bpjs-loader">
    <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
    <span>Mohon Tunggu. Sedang menghubungi server bpjs.</span>
</div>