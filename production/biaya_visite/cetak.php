<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $PageJenisBiaya = "page_jenis_biaya.php";    

     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     
      $sql_where[] = "1=1"; 
	  if($idKategoriTindakanHeader && $idKategoriTindakanHeader!="--") $sql_where[] = "b.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$idKategoriTindakanHeader);
      if($idKategori && $idKategori!="--" ) $sql_where[] = "a.biaya_kategori = ".QuoteValue(DPE_CHAR,$idKategori);
      if($biayaJenis && $biayaJenis!="--") $sql_where[] = "a.biaya_jenis = ".QuoteValue(DPE_CHAR,$biayaJenis); 
      if($_GET['biaya_id'] && $_GET['biaya_id']!="--") $sql_where[] = "h.id_biaya = ".QuoteValue(DPE_CHAR,$_GET['biaya_id']);    
      $sql_where = implode(" and ",$sql_where);

// QUERY PERKIRAAN NANTI DULU
//              f.no_prk as no_prk_beban, 
//              e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, 
//            left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
//              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
    
   	  $sql = "select h.*,a.*, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, c.dep_nama, j.jenis_kelas_nama, 
              d.kegiatan_kategori_nama, 
              g.kategori_tindakan_header_nama,i.kelas_nama, jenis_nama
              from  klinik.klinik_biaya_tarif h            
              left join klinik.klinik_biaya a on h.id_biaya = a.biaya_id     
              left join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join global.global_departemen c on c.dep_id = a.id_dep
              left join klinik.klinik_kegiatan_kategori_tindakan d on d.kegiatan_kategori_id = a.id_kegiatan_kategori 
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              left join klinik.klinik_kelas i on h.id_kelas = i.kelas_id
              left join klinik.klinik_jenis_kelas j on h.id_jenis_kelas = j.jenis_kelas_id
              left join global.global_jenis_pasien k on k.jenis_id = h.id_jenis_pasien
              where ".$sql_where;
      $sql .= " order by g.kategori_tindakan_header_urut,b.kategori_urut,a.biaya_urut";
      // echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataTable = $dtaccess->FetchAll($rs);
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<table width="100%" border="1">
		<thead>
			<tr>
				<th rowspan="2">Nama Tindakan</th>
				<th rowspan="2">Kelas</th>
				<th rowspan="2">Jenis Kelas</th>
				<th rowspan="2">Jenis Pasien</th>
				<th rowspan="2">Tarif</th>
				<th rowspan="2">Jasa Sarana</th>
				<th rowspan="2">Jasa Pelayanan</th>
				<th colspan="2">Split</th>
			</tr>
			<tr>
				<th>Dokter Operator</th>
				<th>Pelaksana 1</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				for ($i = 0; $i < count($dataTable); $i++) { 
					$sql = "select * from klinik.klinik_biaya_split where id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id'])." and id_split = '2'";
					// echo $sql;
					$JasaSarana = $dtaccess->Fetch($sql);

					$sql = "select * from klinik.klinik_biaya_split where id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id'])." and id_split = '1'";
					$JasaPelayanan = $dtaccess->Fetch($sql);

					$sql = "select * from klinik.klinik_biaya_remunerasi a
									left join klinik.klinik_folio_posisi b on b.fol_posisi_id = a.id_folio_posisi
									where id_folio_posisi = '10' and id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id']);
					$RemunDokter = $dtaccess->Fetch($sql);

					$sql = "select * from klinik.klinik_biaya_remunerasi a
									left join klinik.klinik_folio_posisi b on b.fol_posisi_id = a.id_folio_posisi
									where id_folio_posisi = '2' and id_biaya_tarif = ".QuoteValue(DPE_CHAR,$dataTable[$i]['biaya_tarif_id']);
					$RemunPerawat = $dtaccess->Fetch($sql);
			?>
				<tr>
					<td><?php echo $dataTable[$i]['biaya_nama'] ?></td>
					<td><?php echo $dataTable[$i]['kelas_nama'] ?></td>
					<td><?php echo $dataTable[$i]['jenis_kelas_nama'] ?></td>
					<td><?php echo $dataTable[$i]['jenis_nama'] ?></td>
					<td><?php echo $dataTable[$i]['biaya_total'] ?></td>
					<td><?php echo $JasaSarana['bea_split_nominal'] ?></td>
					<td><?php echo $JasaPelayanan['bea_split_nominal'] ?></td>
					<td><?php echo $RemunDokter['biaya_remunerasi_nominal'] ?></td>
					<td><?php echo $RemunPerawat['biaya_remunerasi_nominal'] ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</body>
</html>