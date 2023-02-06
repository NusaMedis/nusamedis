  <?php
  	$userLogData = $auth->GetUserData();

  	if(!$userLogData){
  		header("location: ../../login/login.php");
  		exit();
  	}
  ?>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $tableHeader?></title>
    <!-- Bootstrap -->
    <link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?php echo $ROOT; ?>assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- NProgress
    <link href="<?php echo $ROOT; ?>assets/vendors/nprogress/nprogress.css" rel="stylesheet"> -->
    <!-- iCheck 
    <link href="<?php echo $ROOT; ?>assets/vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar 
    <link href="<?php echo $ROOT; ?>assets/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap 
    <link href="<?php echo $ROOT; ?>assets/vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    -->
    
    <!-- Datatables -->
    <link href="<?php echo $ROOT; ?>assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ROOT; ?>assets/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ROOT; ?>assets/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ROOT; ?>assets/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ROOT; ?>assets/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Theme Style -->
    <link href="<?php echo $ROOT; ?>assets/build/css/custom.min.css" rel="stylesheet">
    
    <!-- Custom Theme Style Thickbox -->
    <link href="<?php echo $ROOT; ?>lib/script/jquery/thickbox/thickbox.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="<?php echo $ROOT; ?>assets/vendors/jquery/dist/jquery.min.js"></script>
    
    <!-- Bootstrap -->
    <script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    
      
    <!-- select2 -->
    <link href="<?php echo $ROOT; ?>assets/vendors/select2/dist/css/select2.css" rel="stylesheet">
    <script src="<?php echo $ROOT; ?>assets/vendors/select2/dist/js/select2.js"></script>
    
    <!-- autocomplete -->
    <script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/jQuery-Autocomplete/src/jquery.autocomplete.js"></script>
    <style>
    .autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
    .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
    .autocomplete-selected { background: #F0F0F0; }
    .autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
    .autocomplete-group { padding: 2px 5px; }
    .autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
    </style>
    
    <!-- Format Currency -->
    <script src="<?php echo $ROOT; ?>assets/build/js/func_curr.js"></script>
    
    <!-- easy ui css -->
    <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>assets/vendors/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>assets/vendors/easyui/themes/icon.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>assets/vendors/easyui/themes/color.css">
    
     <!-- easy ui js -->
    <script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/easyui/jquery.edatagrid.js"></script>
    <script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/easyui/datagrid-filter.js"></script> 
    <script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/easyui/datagrid-detailview.js"></script> 
    
    <!-- js ajax upload -->
    <script type="text/javascript" src="<?php echo $LIB; ?>script/jquery/ajaxupload/ajaxfileupload.js"></script> 
    <!-- validation -->
  <link href="<?php echo $ROOT; ?>assets/vendors/validator/fv.css" rel="stylesheet" type="text/css" />
    
    <!-- bootstrap-datetimepicker -->
    <link href="<?php echo $ROOT; ?>assets/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <!-- bootstrap-daterangepicker -->
    <script src="<?php echo $ROOT; ?>assets/vendors/moment/min/moment.min.js"></script>
    <script src="<?php echo $ROOT; ?>assets/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
    <!-- bootstrap-datetimepicker -->    
    <script src="<?php echo $ROOT; ?>assets/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript">
        $(function () {
            $('#datepicker').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker2').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker3').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker4').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker5').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker6').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker7').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker8').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker9').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });
            $('#datepicker10').datetimepicker({
                format: 'DD-MM-YYYY',
                
            });


            setInterval(function(){

            	$.post("../layouts/checkLog.php").done(function(data){
            		var data = JSON.parse(data);

            		if(data.userName == null){
            			location.replace("../../login/login.php");
            			
            		}

            	});

        	}, 360000000);
        	
        });

        
    </script>
    
  </head>