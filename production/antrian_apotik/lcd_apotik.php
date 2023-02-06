<?php
 	require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");                                                                  
     require_once($LIB."expAJAX.php");    
     require_once($LIB."tampilan.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Custom Theme Style -->
    <link href="<?php echo $ROOT; ?>assets/build/css/custom.min.css" rel="stylesheet">
	<!-- jQuery -->
    <script src="<?php echo $ROOT; ?>assets/vendors/jquery/dist/jquery.min.js"></script>
	
    <!-- Bootstrap -->
    <script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
	
	<!-- Bootstrap -->

    <link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <style type="text/css">
    	div.containAll{
    		background: #2b344f;
    	}

    	div.containTab{
    		display: flex;
    	}

    	div.tabDiv{
    		background: white;
    		min-height: 90vh;
    		max-height: 90vh;
    		overflow-y: auto;
    	}

    	div.labelDiv{
    		min-height: 10vh;
    		color: white;
    	}
    </style>
</head>
<body>
	<div class="col-md-12 containAll">
		<div class="col-md-12 labelDiv">
			<center>
				<h2>DAFTAR RESEP APOTIK</h2>
				<h3>RSIA MUSLIMAT JOMBANG</h3>
			</center>
		</div>
		<div class="col-md-12 containTab">
			<div class="col-md-6 tabDiv">
				<center>
					<h3 style="color: #f01515;">Resep Masih dalam Proses</h3>
				</center>
				<table id="penjUnServed" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
		        	<thead>
		        		<tr>
		        			<th>No Resep</th>
		        			<th>Nama</th>
		        			<th>Poli / Ruangan</th>
		        		</tr>
		        	</thead>
		        	<tbody>
		        		
		        	</tbody>
			    </table>
			</div>
	    	<div class="col-md-6 tabDiv">
	    		<center>
					<h3 style="color: #489f17;">Resep Sudah Selesai</h3>
				</center>
				<table id="penjServed" class="table table-striped table-bordered dt-responsive nowrap" width="100%">
		        	<thead>
		        		<tr>
		        			<th>No Resep</th>
		        			<th>Nama</th>
		        			<th>Poli / Ruangan</th>
		        		</tr>
		        	</thead>
		        	<tbody>
		        		
		        	</tbody>
			    </table>
			</div>
		</div>
	</div>
</body>
<script type="text/javascript">

	function refresh(){
		$.post("get_penj.php", {
			getPenj : 1
		}).done(function(data){
			var data = JSON.parse(data);
			var htmlBelum = "", htmlSudah = "";
			var dataBelum = data.dataBelum, dataSudah = data.dataSudah;

			$.each(dataBelum, function(ind, val){
				htmlBelum += "<tr>";
				htmlBelum += "<td>"+val.penjualan_nomor+"</td>";
				htmlBelum += "<td>"+val.cust_usr_nama+"</td>";
				htmlBelum += "<td>"+val.poli_nama+"</td>";
				htmlBelum += "</tr>";
			});

			$("table#penjUnServed tbody").html(htmlBelum);

			$.each(dataSudah, function(ind, val){
				htmlSudah += "<tr>";
				htmlSudah += "<td>"+val.penjualan_nomor+"</td>";
				htmlSudah += "<td>"+val.cust_usr_nama+"</td>";
				htmlSudah += "<td>"+val.poli_nama+"</td>";
				htmlSudah += "</tr>";
			});

			$("table#penjServed tbody").html(htmlSudah);
		});
	}

	$(document).ready(function(){
		refresh();

		setInterval(function(){
			refresh();
		}, 10000);

	});
</script>
</html>