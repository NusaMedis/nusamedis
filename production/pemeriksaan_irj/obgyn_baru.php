<?php //if ($_POST['id_poli']) {

//} ?>
      <div title="Asuhan Medis Awal" style="padding:5px">
              <div class="form-horizontal form-label-left"> 
    <div class="x_title" >
      <div class="col-md-12 col-sm-8 col-xs-12">
        <label class="col-md-11 col-sm-12 col-xs-12"><h2>Asuhan Medis Awal</h2></label>
        <div class="col-md-1 col-sm-12 col-xs-12"><h2><?php echo $tglSekarang; ?></h2></div>
      </div>
        <div class="clearfix"></div>
      </div>
  <form id="form_utama" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
        <input type="hidden" name="cust_usr_id" id="cust_usr_id" value="<?php echo $row['cust_usr_id']; ?>">
        <div class="item form-group">
    <div class="col-md-6 col-sm-6 col-xs-12" >
    
    	<div class="col-md-12 col-sm-12 col-xs-12">  
        <br>      
        <h4> Kasus Obstetri</h4> 
      </div>
          <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
        <div class="col-md-3 col-sm-12 col-xs-12">                                   
            <label>Keluhan Utama:</label>

            <input type="hidden" name="rawat_id" id="rawat_id" value="<? echo $dataPasien['rawat_id']?>">
            <input type="hidden" name="usr_id" id="usr_id">
        </div>
        <input type="hidden" name="id_reg" id="idreg">
          <div class="col-md-5 col-sm-3 col-xs-12">
            <select id="isi0" class="form-control" name="isi[0]" >
              <option value="="></option>
            	<option value="Hamil">Hamil</option>
            	<option value="Amenore">Amenore</option>
            </select>
          </div>
          <div class="col-md-1 col-sm-2 col-xs-12">
            <input style="width: 45px;" class="form-control" type="text" id="isi1" name="isi[1]"> 
          </div>

          <div class="col-md-2 col-sm-2 col-xs-12">
          	<label>bulan</label>
          </div>

          <div class=" col-md-offset-3 col-md-5 col-sm-3 col-xs-12">
          	<br>
            <select id="isi26" class="form-control" name="isi[2]" >
              <option value="="> </option>
            	<option value="Mengeluarkan Cairan">Mengeluarkan Cairan </option>
            	<option value="Pendarahan">Pendarahan </option>
            </select>
          </div>

          <div class="col-md-2 col-sm-2 col-xs-12">
          	<br>
          	<label>Berapa Lama</label>
          </div>  
          <div class="col-md-1 col-sm-2 col-xs-12">
          	<br>
            <input style="width: 45px;" class="form-control" type="text" id="isi37" name="isi[3]"> 
          </div>

          
        <div class="col-md-5 col-sm-8 col-xs-12"><br></div>
        <div class=" col-md-7 col-sm-6 col-xs-12">
          <div class="col-md-4 col-sm-12 col-xs-12">
            <input type="hidden" id="isi92" name="isi[8]"  value="Pendarahan" >
             <label>Pendarahan :</label>
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi103" name="isi[9]" <? if ($dataKU[67] != '') echo "checked"; ?>value="Sedikit"> Sedikit
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi2" name="isi[10]"  <? if ($dataKU[78] != '') echo "checked"; ?>value="Banyak"> Banyak
          </div>
        </div>
        <div class="col-md-offset-3">
          <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi48" name="isi[4]" value="Mual" > Mual
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi59" name="isi[5]" value="Pusing" > Pusing
          </div>
        </div>
         <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi70" name="isi[6]" value="Muntah" > Muntah
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi81" name="isi[7]" value="Perut Sakit"> Perut Sakit
          </div>
        </div>
        </div>

        <br>
        

    <div class="col-md-12 col-sm-12 col-xs-12">
      <br>
    </div>
		<div class="col-md-6 col-sm-12 col-xs-12">
        <div class="col-md-2 col-sm-12 col-xs-12" >
            <label>HPHT</label>
          </div>
          <div class='input-group date col-md-8 col-sm-6 col-xs-12' id='datepicker'>
            <input type='text' class="form-control" data-inputmask="'mask': '99-99-9999'" id="isi13" name="isi[11]" >
              <span class="input-group-addon">
              <span class="fa fa-calendar">
              </span>
              </span>
          </div>
        </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
          <div class="col-md-2 col-sm-12 col-xs-12">
            <label >HPL</label>
          </div>
          <div class='input-group date col-md-8 col-sm-6 col-xs-12' id='datepicker2'>
            <input type='text' class="form-control" data-inputmask="'mask': '99-99-9999'" id="isi18" name="isi[12]"value="">
              <span class="input-group-addon">
              <span class="fa fa-calendar">
              </span>
              </span>
          </div>
     	  </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-2 col-sm-12 col-xs-12">
            <label>Kehamilan Ke :</label></div>
        <div class="col-md-5 col-sm-12 col-xs-12">
          <select style="width: 55px;" id="isi19" class="form-control" name="isi[13]" >
              <option value=""> </option>
              <option value="I"> I</option>
              <option value="II"> II </option>
              <option value="III"> III</option>
              <option value="IV"> IV </option>
              <option value="V">V </option>
            </select>
        </div>
      </div>
      <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-2 col-sm-12 col-xs-12">
            <label>Persalinan Ke :</label></div>
        <div class="col-md-5 col-sm-12 col-xs-12">
          <select style="width: 55px;" id="isi107" class="form-control" name="isi[93]" >
              <option value=""> </option>
              <option value="I"> I</option>
              <option value="II"> II </option>
              <option value="III"> III</option>
              <option value="IV"> IV </option>
              <option value="V">V </option>
            </select>
        </div>
      </div>
      <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-2 col-sm-12 col-xs-12">
            <label>Abortus Ke :</label></div>
        <div class="col-md-5 col-sm-12 col-xs-12">
          <select style="width: 55px;" id="isi108" class="form-control" name="isi[94]" >
              <option value=""> </option>
              <option value="I"> I</option>
              <option value="II"> II </option>
              <option value="III"> III</option>
              <option value="IV"> IV </option>
            </select>
        </div>
      </div>
        <div class="col-md-12 col-sm-12 col-xs-12">                                   
            <h4>Riwayat Persalinan Yang Lalu:</h4>
        </div>
        
        
        <div class=" col-md-12 col-sm-12 col-xs-12"> <br></div>

        <div class=" col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-3 col-sm-12 col-xs-12">

        <div class=" col-md-12 col-sm-12 col-xs-12">
            <label>Riwayat persalinan spontan</label>
            <input type="hidden" name="a" value="Riwayat persalinan spontan">
          </div>
        <!-- <div class="col-md-6 col-sm-12 col-xs-12">
          <select id="isi12" class="form-control" name="isi[2]" >
              <option value=""> </option>
              <option value="Anak I">Anak I</option>
              <option value="Anak II">Anak II </option>
              <option value="Anak III">Anak III</option>
              <option value="Anak IV">Anak IV </option>
              <option value="Lainnya">Lainnya </option>
            </select>
        </div> -->

          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi20" name="isi[14]" <? if ($dataKU[2] != '') echo "checked"; ?> value="Anak I"> Anak I
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi21" name="isi[15]" <? if ($dataKU[3] != '') echo "checked"; ?> value="Anak II"> Anak II
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi22" name="isi[16]" <? if ($dataKU[4] != '') echo "checked"; ?> value="Anak III"> Anak III
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi23" name="isi[17]" <? if ($dataKU[5] != '') echo "checked"; ?> value="Anak IV"> Anak IV
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi24" name="isi[18]" <? if ($dataKU[6] != '') echo "checked"; ?> value="Lainnya"> Lainnya
          </div>
        </div>
<!-- 
          <div class="col-md-12 col-sm-12 col-xs-12">
          <br>
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <label>Riwayat SC</label>
            <input type="hidden" name="a" value="Riwayat persalinan spontan">
          </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <select id="isi12" class="form-control" name="isi[2]" >
              <option value=""> </option>
              <option value="Anak I">Anak I</option>
              <option value="Anak II">Anak II </option>
              <option value="Anak III">Anak III</option>
              <option value="Anak IV">Anak IV </option>
              <option value="Lainnya">Lainnya </option>
            </select>
        </div> -->

          <div class="col-md-3 col-sm-12 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <label>Riwayat SC</label>
            <input type="hidden" name="is" value="Riwayat SC">
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi25" name="isi[19]" <? if ($dataKU[7] != '') echo "checked"; ?> value="Anak I"> Anak I
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi27" name="isi[20]" <? if ($dataKU[8] != '') echo "checked"; ?> value="Anak II"> Anak II
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi28" name="isi[21]" <? if ($dataKU[9] != '') echo "checked"; ?> value="Anak III"> Anak III
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi29" name="isi[22]" <? if ($dataKU[10] != '') echo "checked"; ?> value="Anak IV"> Anak IV
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi30" name="isi[23]" <? if ($dataKU[11] != '') echo "checked"; ?> value="Lainnya"> Lainnya
          </div>
      </div>

      	<div class="col-md-6 col-sm-12 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <label>Riwayat Abortus</label>
            <input type="hidden" name="i1si" value="Riwayat Abortus">
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi31" name="isi[24]" <? if ($dataKU[13] != '') echo "checked"; ?> value="Anak I"> Anak I 
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi32" name="isi[25]" <? if ($dataKU[14] != '') echo "checked"; ?> value="Kuret"> Kuret 
          </div>
          </div>


         <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi33" name="isi[26]" <? if ($dataKU[15] != '') echo "checked"; ?> value="Anak II"> Anak II
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi34" name="isi[27]" <? if ($dataKU[16] != '') echo "checked"; ?> value="Kuret"> Kuret 
          </div>
      	</div>

        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi35" name="isi[28]" <? if ($dataKU[17] != '') echo "checked"; ?> value="Anak III"> Anak III
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi36" name="isi[29]" <? if ($dataKU[18] != '') echo "checked"; ?> value="Kuret"> Kuret
          </div>
        </div>

        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi38" name="isi[30]" <? if ($dataKU[19] != '') echo "checked"; ?> value="Anak IV"> Anak IV
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi39" name="isi[31]" <? if ($dataKU[20] != '') echo "checked"; ?> value="Kuret"> Kuret
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi40" name="isi[32]" <? if ($dataKU[21] != '') echo "checked"; ?> value="Lainnya"> Lainnya
          </div>
        </div>
        
      </div>
          
        </div>
    	</div>
        <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="col-md-12 col-sm-12 col-xs-12">  
        <br>      
        <h4> Kasus Ginekologi</h4>
      </div>
      <div class="col-md-offset-1 col-md-8 col-sm-8 col-xs-12" >
        
          <div class="col-md-3 col-sm-12 col-xs-12">
            <label> Keluhan Utama</label>
          </div>
          <div class="col-md-9  col-sm-12 col-xs-12">
            <textarea class="form-control" id="isi109" name="isi[95]"></textarea>
            <!-- <input class="form-control" style="width: 50px;" type="textarea" id="isi95" name="isi[95]" > -->
          </div>
        
        <div class=" col-md-9 col-sm-8 col-xs-12">                                   
            <label>Gangguan Haid:</label>
            <input type="hidden" name="a" value="Gangguan Haid">
        </div>
    </div>
        <div class="col-md-offset-1 col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12">
           Aminore &nbsp; <input type="text" class="form-group" style="width: 50px;" name="isi[110]" id="isi14"> &nbsp; hari <input type="text" class="form-group" style="width: 50px;" name="isi[111]" id="isi15"> &nbsp; bulan
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
           Haid Lama <input type="text" class="form-group" style="width: 50px;" name="isi[112]" id="isi16"> &nbsp; hari 
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
           Haid Lama dan Banyak <input type="text" class="form-group" style="width: 50px;" name="isi[113]" id="isi17"> &nbsp; hari 
          </div>
          <div class="col-md-2 col-sm-12 col-xs-12">
            Haid : 1 bulan 
          </div>
          <div class="col-md-1 col-sm-12 col-xs-12">
            <input class="form-control" style="width: 50px;" type="text" id="isi41" name="isi[33]" >
          </div>
          <div class="col-md-2 col-sm-12 col-xs-12">
            <p class="col-md-12 col-sm-12 col-xs-12">Kali</p>
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
          <div class=" col-md-2 col-sm-12 col-xs-12">
            <input type="hidden" id="isi42" name="isi[34]" value="Pendarahan terus menerus">
          </div>
          <div class=" col-md-2 col-sm-12 col-xs-12">
            Terus menerus berapa lama :
          </div>
          <div class="col-md-1 col-sm-12 col-xs-12">
            <input type="text" style="width: 50px;" id="isi43" name="isi[35]" class="form-control" value="<?php echo $dataKU[25]["anamnesa_isi_nilai"]; ?>"> 
          </div>
          <div class="col-md-2 col-sm-12 col-xs-12">
            <p class="col-md-12 col-sm-12 col-xs-12">hari</p>
          </div>
      </div>
        <div class="col-md-12 col-sm-12 col-xs-12"> <br></div>
      <div class="col-md-offset-1 col-md-8 col-sm-8 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <label>Pendarahan :</label>
            <input type="hidden" name="i2" value="Pendarahan">
          </div>
      </div>

        <div class="col-md-offset-1 col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-2 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi52" name="isi[43]" <? if ($dataKU[32] != '') echo "checked"; ?> value="Sedikit"> Sedikit
          </div>
          <div class="col-md-2 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi53" name="isi[44]" <? if ($dataKU[33] != '') echo "checked"; ?> value="Banyak"> Banyak
          </div>

          <div class="col-md-2 col-sm-12 col-xs-12">
            Sudah berapa lama :
          </div>
          <div class="col-md-1 col-sm-12 col-xs-12">
            <input type="text" style="width: 50px;" id="isi54" name="isi[45]" class="form-control" value="<?php echo $dataKU[34]["anamnesa_isi_nilai"]; ?>">
            <br>
          </div>
          <div class="col-md-1 col-sm-12 col-xs-12">
      <p class="col-md-12 col-sm-12 col-xs-12">hari</p>          </div>
          <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
        </div>
        <div class="col-md-offset-1 col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-9 col-sm-8 col-xs-12">
            <label>Flour Albus</label>
            <input type="hidden" name="a" value="Flour Albus">
          </div>
      </div>
        <div class="col-md-offset-1 col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-2 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi44" name="isi[36]" <? if ($dataKU[26] != '') echo "checked"; ?> value="Gatal"> Gatal
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi45" name="isi[37]" <? if ($dataKU[26] != '') echo "checked"; ?> value="Tidak Gatal"> Tidak Gatal
          </div>
          <div class="col-md-2 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi46" name="isi[38]" <? if ($dataKU[27] != '') echo "checked"; ?> value="Bau"> Bau

          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi47" name="isi[39]" <? if ($dataKU[27] != '') echo "checked"; ?> value="Tidak Bau"> Tidak Bau

          </div>
          <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
          <div class="col-md-2 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi49" name="isi[40]" <? if ($dataKU[28] != '') echo "checked"; ?> value="Campur darah"> Campur darah
          </div>
          
          <div class="col-md-2 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi50" name="isi[41]" <? if ($dataKU[30] != '') echo "checked"; ?> value="Lainnya"> Lainnya
        </div>
          <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
        <div class="col-md-2 col-sm-12 col-xs-12">
            Sudah berapa lama :
          </div>
          <div class="col-md-1 col-sm-12 col-xs-12">
            <input type="text" style="width: 50px;" id="isi51" name="isi[42]" class="form-control" value="<?php echo $dataKU[29]["anamnesa_isi_nilai"]; ?>">
          </div>

          <div class="col-md-2 col-sm-12 col-xs-12">
           <p class="col-md-12 col-sm-12 col-xs-12">hari</p>
          </div>
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
          <div class="col-md-offset-1 col-md-12 col-sm-12 col-xs-12">
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi55" name="isi[46]" <? if ($dataKU[35] != '') echo "checked"; ?> value="Perut sakit"> Perut sakit
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi56" name="isi[47]" <? if ($dataKU[36] != '') echo "checked"; ?> value="Tumor"> Tumor
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi57" name="isi[48]" <? if ($dataKU[37] != '') echo "checked"; ?> value="Myom Uteri"> Myom Uteri
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi58" name="isi[49]" <? if ($dataKU[38] != '') echo "checked"; ?> value="Kista Ovari"> Kista Ovari
          </div>
          <div class="col-md-3 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi60" name="isi[50]" <? if ($dataKU[39] != '') echo "checked"; ?> value="Ca CX"> Ca CX
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <input type="checkbox" id="isi61" name="isi[51]" <? if ($dataKU[40] != '') echo "checked"; ?> value="Lainnya" <?php if($dataKU[40] != '') echo "checked"; ?>> Lainnya
          </div>
         </div>
      </div>        
                    <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                  <br>
                    <h4>Data Objektif</h4>
                        <input type="hidden" name="a" value="Data Objektif">
            </div>

                      <div class="col-md-12 col-sm-12 col-xs-12" >
                      <div class="col-md-3 col-sm-8 col-xs-12">
                         <p class="col-md-5 col-sm-8 col-xs-12">Tekanan Darah : </p> 
                      
                      <input type="text" style="width: 50px;" class="form-control col-md-1 col-sm-8 col-xs-12" id="isi62" name="isi[52]" value="<?php echo $dataKU[41]["anamnesa_isi_nilai"]; ?>"> 
                    
                        <p class="col-md-2 col-sm-8 col-xs-12">mm/Hg</p>   
                      </div>
                      <div class="col-md-3 col-sm-8 col-xs-12">
                        <p class="col-md-4 col-sm-8 col-xs-12">Nadi:</p> 
                     
                        <input type="text" style="width: 50px;" class="form-control col-md-1 col-sm-8 col-xs-12" id="isi63" name="isi[53]" value="<?php echo $dataKU[42]["anamnesa_isi_nilai"]; ?>">
                      
                         <p class="col-md-2 col-sm-8 col-xs-12">x/Menit</p> 
                      </div>
                      <div class="col-md-3 col-sm-8 col-xs-12">
                       <p class="col-md-4 col-sm-8 col-xs-12">Suhu Badan:</p> 
                      
                        <input type="text" style="width: 50px;" class="form-control col-md-2 col-sm-8 col-xs-12" id="isi64" name="isi[54]" value="<?php echo $dataKU[43]["anamnesa_isi_nilai"]; ?>">
                      <p class="col-md-2 col-sm-8 col-xs-12">°C </p>
                      </div>
                      <div class="col-md-12 col-sm-8 col-xs-12">
                         <br> 
                      </div>
                      <div class="col-md-3 col-sm-8 col-xs-12">
                        <p class="col-md-4 col-sm-8 col-xs-12">Jantung:</p>
                      
                        <input type="text" style="width: 50px;" class="form-control col-md-1 col-sm-8 col-xs-12" id="isi65" name="isi[55]" value="<?php echo $dataKU[44]["anamnesa_isi_nilai"]; ?>">
                      </div>
                                            
                      <div class="col-md-3 col-sm-8 col-xs-12">
                       <p class="col-md-4 col-sm-8 col-xs-12"> Paru:</p>
                      
                        <input type="text" style="width: 50px;" class="form-control col-md-1 col-sm-8 col-xs-12" id="isi66" name="isi[56]" value="<?php echo $dataKU[46]["anamnesa_isi_nilai"]; ?>">
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12">
                        <h4>Letak Anak</h4>
                      </div>

                    <div class="col-md-6 col-sm-8 col-xs-12">
                      <div class="col-md-12 col-sm-12 col-xs-12">
                      <div class="col-md-4 col-sm-8 col-xs-12">
                        
                         <input type="checkbox" id="isi67" name="isi[57]" <? if ($dataKU[39] != '') echo "checked"; ?> value="Kepala">
                       Kepala
                      </div>
                      <div class="col-md-4 col-sm-8 col-xs-12">
                          <input type="checkbox" id="isi68" name="isi[58]" <? if ($dataKU[39] != '') echo "checked"; ?> value="Sungsang">
                     Sungsang
                      </div>
                      <div class="col-md-4 col-sm-8 col-xs-12">
                           <input type="checkbox" id="isi69" name="isi[59]" <? if ($dataKU[39] != '') echo "checked"; ?> value="Oblique">
                     Oblique
                      </div>
                      <div class="col-md-4 col-sm-8 col-xs-12">
                          <input type="checkbox" id="isi71" name="isi[60]" <? if ($dataKU[39] != '') echo "checked"; ?> value="Lintang">
                      Lintang
                      </div>
                    <div class="col-md-3 col-sm-8 col-xs-12">
                        Tinggi FU: 
                      </div>
                      <div class="col-md-3 col-sm-8 col-xs-12">
                        <input type="text" class="form-control" id="isi72" name="isi[61]" value="<?php echo $dataKU[47]["anamnesa_isi_nilai"]; ?>">                       
                      </div>
                      <div class="col-md-1 col-sm-8 col-xs-12">
                        cm                         
                      </div>
                      </div>
                    </div>
                    <div class="col-md-12 col-sm-8 col-xs-12"><br></div>
                      <h4 class="col-md-12 col-sm-1">Pemeriksaan USG</h4>
                        <div class="col-md-4 col-sm-12 col-xs-12">
                        <label class="col-md-4 col-sm-12 col-xs-12">GS</label>
                          <select id="isi111" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[97]" >
                    <option value="="></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                        
                        <label class="col-md-1 col-sm-12 col-xs-12">-</label>
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi112" name="isi[98]" >
                        <label class="col-md-1 col-sm-12 col-xs-12">mm</label>
                    
                      </div>
                      <div class="col-md-4 col-sm-12 col-xs-12">
                    
                          <label class="col-md-4 col-sm-12 col-xs-12">CRL</label>
                      
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi110" name="isi[96]" >
                        <label class="col-md-2 col-sm-12 col-xs-12">mm</label>
                    
                      </div>
                        <div class="col-md-4 col-sm-12 col-xs-12">
                        <label class="col-md-4 col-sm-12 col-xs-12">DJJ</label>
                          <select id="isi4" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[101]" >
                    <option value="="></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                    
                      </div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                       <label>Usia Kehamilan :</label> 
                      
                      </div>
                        <div class=" col-md-4 col-sm-1 col-xs-1">
                      <div class=" col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-2 col-sm-1 col-xs-1" id="isi6" name="isi[103]">
                       
                       <p class="col-md-3 col-sm-12 col-xs-12">Minggu </p>
                      </div>
                      <div class="col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-2 col-sm-1 col-xs-1" id="isi7" name="isi[104]" value="<?php echo $dataKU[65]["anamnesa_isi_nilai"]; ?>"> 
                        
                        <p class="col-md-3 col-sm-12 col-xs-12">hari</p>
                      </div>
                      </div>
                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                       <label> Janin :</label> 
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi73" name="isi[62]" <? if ($dataKU[52] != '') echo "checked"; ?> value="Tunggal"> Tunggal
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi74" name="isi[63]" <? if ($dataKU[53] != '') echo "checked"; ?> value="Kembar"> Kembar
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi75" name="isi[64]" <? if ($dataKU[54] != '') echo "checked"; ?> value="Hidup"> Hidup
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi76" name="isi[65]" <? if ($dataKU[55] != '') echo "checked"; ?> value="IUFD"> IUFD
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Letak Janin :</label> 
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi77" name="isi[66]" <? if ($dataKU[57] != '') echo "checked"; ?> value="Kepala"> Kepala
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi78" name="isi[67]" <? if ($dataKU[58] != '') echo "checked"; ?> value="Sungsang"> Sungsang
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi79" name="isi[68]" value="Melintang"> Melintang
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-12 col-sm-12 col-xs-12">
                       <label>Ukuran :</label>  
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-4 col-sm-12 col-xs-12">
                        <label class="col-md-3 col-sm-12 col-xs-12">BPD</label>
                      
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi80" name="isi[69]" >
                        <label class="col-md-4 col-sm-12 col-xs-12">mm</label>
                    
                      </div>

                      <div class="col-md-4 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">FL</label>
                      
                      
                        <input type="text" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" id="isi82" name="isi[70]" value="">

                        <label class="col-md-4 col-sm-12 col-xs-12">mm</label>
                       </div> 

                      <div class="col-md-4 col-sm-12 col-xs-12">
                        <label class="col-md-3 col-sm-12 col-xs-12">AC</label>
                     
                        <input type="text" class="form-control col-md-1 col-sm-12 col-xs-12" style="width : 50px;" id="isi83" name="isi[71]" value="<?php echo $dataKU[62]["anamnesa_isi_nilai"]; ?>">
                     
                        <label class="col-md-4 col-sm-12 col-xs-12">mm</label>
                      </div>

                      <div class=" col-md-4 col-sm-12 col-xs-12">
                        <label class="col-md-3 col-sm-12 col-xs-12">EFW</label>
                      
                        <input type="text" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" id="isi84" name="isi[72]" value="<?php echo $dataKU[63]["anamnesa_isi_nilai"]; ?>">
                     
                        <label class="col-md-4 col-sm-12 col-xs-12">gram</label>
                      </div>
                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Usia Kehamilan:</label> 
                      </div>
                      <div class=" col-md-6 col-sm-1 col-xs-1">
                      <div class=" col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-1 col-sm-1 col-xs-1" id="isi85" name="isi[73]" value="<?php echo $dataKU[64]["anamnesa_isi_nilai"]; ?>">
                       
                       <p class="col-md-4 col-sm-12 col-xs-12">Minggu </p>
                      </div>
                      <div class="col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-1 col-sm-1 col-xs-1" id="isi86" name="isi[74]" value="<?php echo $dataKU[65]["anamnesa_isi_nilai"]; ?>"> 
                        
                        <p class="col-md-2 col-sm-12 col-xs-12">hari</p>
                      </div>
                      </div>
                      <div class="col-md-12 col-sm-8 col-xs-12"><br></div>
                    <!-- </div>
                  </div> -->
                      <div class="col-md-12 col-sm-8 col-xs-12">
                        <label>Plasenta :</label> 
                      </div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Insersi :</label> 
                      </div>
                      <div class="col-md-2 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi87" name="isi[75]" <? if ($dataKU[66] != '') echo "checked"; ?> value="Insersi Fundus"> Fundus
                      </div>
                      <input type="hidden" name="isi[76]">
                      
                      <div class="col-md-2 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi89" name="isi[77]" <? if ($dataKU[68] != '') echo "checked"; ?> value="Corpus"> Corpus
                      </div>
                      <div class=" col-md-2 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi90" name="isi[78]" <? if ($dataKU[69] != '') echo "checked"; ?> value="SBR"> SBR
                      </div>

                      <div class="col-md-2 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi91" name="isi[79]" <? if ($dataKU[70] != '') echo "checked"; ?> value="Antenir"> Anterior
                      </div>

                      <div class="col-md-2 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi93" name="isi[80]" <? if ($dataKU[71] != '') echo "checked"; ?> value="Posl"> Posterior
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                  </div>
                    </div> <!--tutup div 6 kiri -->
                    <div class="col-md-6 col-sm-12 col-xs-12">
                      <div class="col-md-2 col-sm-8 col-xs-12">
                        <h4>USG Ginekologi<!-- <img src="<?php echo $logoGinekologi; ?>" width="150px" height="150px"> --></h4>
                      </div>
<!-- <div class="col-md-12 col-sm-12 col-xs-12">
                       <label>Ukuran :</label>  
                      </div> -->

                        <!-- <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">GS</label>
                          <select id="isi5" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[102]" >
                    <option value=""></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                        
                        <label class="col-md-1 col-sm-12 col-xs-12">-</label>
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi9" name="isi[106]" >
                        <label class="col-md-1 col-sm-12 col-xs-12">mm</label>
                    
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">FP</label>
                          <select id="isi106" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[99]"  >
                    <option value="="></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                       
                      </div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">Sisa-sisa Kehamilan</label>
                         <select id="isi3" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[100]" >
                    <option value=""></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                    
                      </div>  -->
                      <div class="col-md-9 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-9 col-sm-12 col-xs-12">
                      <!-- <div class="col-md-2 col-sm-8 col-xs-12"><img src="<?php echo $logoGinekologi; ?>"></div>                     -->
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi101" name="isi[88]"></textarea>
                      </div>
                      </div>
                  
                  <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                     <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Pemeriksaan Dalam/ VT</label>
                      </div>

                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi102" name="isi[89]"></textarea>
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>

                     <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Pemeriksaan Penunjang</label>
                      </div>

                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi9" name="isi[106]"></textarea>
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>

                     <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Analisa / Diagnosa</label>
                      </div>

                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi10" name="isi[107]"></textarea>
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>

                     <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Planning / Penatalaksanaan</label>
                      </div>

                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi11" name="isi[108]"></textarea>
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>

                     <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Tindakan</label>
                      </div>

                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi12" name="isi[109]"></textarea>
                      </div>
                    </div>

    </div>
    
    <div class="item form-group">
    	
        </div>
                <div class="item form-group">

                    <div class="col-md-12 col-sm-12 col-xs-12">
                    <!-- <h4 class="col-md-12 col-sm-12 col-xs-12">Pemeriksaan USG</h4> -->
                    <!-- <input type="hidden" name="a" value="Pemeriksaan USG"> -->
                    <!-- <div> <img src="<?php echo $logoObstetri; ?>"> &nbsp;</div> -->

                    <!-- <div class="col-md-offset-1 col-md-12 col-sm-12 col-xs-12"> -->
                      <!-- <div class="col-md-1 col-sm-12 col-xs-12"><br><br><br><br><br><br><br><br><br><br><br><br><img src="<?php echo $logoObstetri; ?>"></div> -->
                      <div class="col-md-10 col-sm-12 col-xs-12" >
                        
                        <!-- <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">GS</label>
                          <select id="isi104" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[97]" >
                    <option value="="></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                        
                        <label class="col-md-1 col-sm-12 col-xs-12">-</label>
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi105" name="isi[98]" >
                        <label class="col-md-1 col-sm-12 col-xs-12">mm</label>
                    
                      </div> -->
                      <!-- <div class="col-md-2 col-sm-12 col-xs-12">
                    
                          <label class="col-md-2 col-sm-12 col-xs-12">CRL</label>
                      
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi103" name="isi[96]" >
                        <label class="col-md-2 col-sm-12 col-xs-12">mm</label>
                    
                      </div> -->
                        <!-- <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">DJJ</label>
                          <select id="isi4" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" name="isi[101]" >
                    <option value="="></option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                    
                      </div> -->
                      <!-- <div class="col-md-1 col-sm-8 col-xs-12">
                       <label>Usia Kehamilan :</label> 
                      
                      </div>
                        <div class=" col-md-2 col-sm-1 col-xs-1">
                      <div class=" col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-2 col-sm-1 col-xs-1" id="isi6" name="isi[103]">
                       
                       <p class="col-md-3 col-sm-12 col-xs-12">Minggu </p>
                      </div>
                      <div class="col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-2 col-sm-1 col-xs-1" id="isi7" name="isi[104]" value="<?php echo $dataKU[65]["anamnesa_isi_nilai"]; ?>"> 
                        
                        <p class="col-md-3 col-sm-12 col-xs-12">hari</p>
                      </div>
                      </div> -->
                      
<!--                       <div class="col-md-1 col-sm-8 col-xs-12">
                       <label> Janin :</label> 
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi66" name="isi[62]" <? if ($dataKU[52] != '') echo "checked"; ?> value="Tunggal"> Tunggal
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi67" name="isi[63]" <? if ($dataKU[53] != '') echo "checked"; ?> value="Kembar"> Kembar
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi68" name="isi[64]" <? if ($dataKU[54] != '') echo "checked"; ?> value="Hidup"> Hidup
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi69" name="isi[65]" <? if ($dataKU[55] != '') echo "checked"; ?> value="IUFD"> IUFD
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-1 col-sm-8 col-xs-12">
                        <label>Letak Janin :</label> 
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi70" name="isi[66]" <? if ($dataKU[57] != '') echo "checked"; ?> value="Kepala"> Kepala
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi71" name="isi[67]" <? if ($dataKU[58] != '') echo "checked"; ?> value="Sungsang"> Sungsang
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <input type="checkbox" id="isi72" name="isi[68]" value="Melintang"> Melintang
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>

                      <div class="col-md-12 col-sm-12 col-xs-12">
                       <label>Ukuran :</label>  
                      </div>

                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-3 col-sm-12 col-xs-12">BPD</label>
                      
                        <input class="form-control col-md-1 col-sm-12 col-xs-12"type="text" style="width : 50px;"  id="isi73" name="isi[69]" >
                        <label class="col-md-2 col-sm-12 col-xs-12">mm</label>
                    
                      </div>

                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-2 col-sm-12 col-xs-12">FL</label>
                      
                      
                        <input type="text" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" id="isi75" name="isi[70]" value="">
                     
                        
                    
                        <label class="col-md-2 col-sm-12 col-xs-12">mm</label>
                       </div> 

                      <div class="col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-3 col-sm-12 col-xs-12">AC</label>
                     
                        <input type="text" class="form-control col-md-1 col-sm-12 col-xs-12" style="width : 50px;" id="isi76" name="isi[71]" value="<?php echo $dataKU[62]["anamnesa_isi_nilai"]; ?>">
                     
                        <label class="col-md-2 col-sm-12 col-xs-12">mm</label>
                      </div>

                      <div class=" col-md-2 col-sm-12 col-xs-12">
                        <label class="col-md-3 col-sm-12 col-xs-12">EFW</label>
                      
                        <input type="text" style="width : 50px;" class="form-control col-md-1 col-sm-12 col-xs-12" id="isi77" name="isi[72]" value="<?php echo $dataKU[63]["anamnesa_isi_nilai"]; ?>">
                     
                        <label class="col-md-2 col-sm-12 col-xs-12">gram</label>
                      </div>


                      <div class="col-md-12 col-sm-8 col-xs-12"><br></div>
                      <div class="col-md-1 col-sm-8 col-xs-12">
                       <label>Usia Kehamilan :</label> 
                      
                      </div>
                      	<div class=" col-md-3 col-sm-1 col-xs-1">
                      <div class=" col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-1 col-sm-1 col-xs-1" id="isi78" name="isi[73]" value="<?php echo $dataKU[64]["anamnesa_isi_nilai"]; ?>">
                       
                       <p class="col-md-4 col-sm-12 col-xs-12">Minggu </p>
                      </div>
                      <div class="col-md-6 col-sm-1 col-xs-1">
                        <input type="text" style="width : 50px;"class="form-control col-md-1 col-sm-1 col-xs-1" id="isi79" name="isi[74]" value="<?php echo $dataKU[65]["anamnesa_isi_nilai"]; ?>"> 
                        
                        <p class="col-md-2 col-sm-12 col-xs-12">hari</p>
                      </div>
                      </div>
                  
                      <div class="col-md-12 col-sm-8 col-xs-12"><br></div>
                      <div class="col-md-12 col-sm-8 col-xs-12">
                        <label>Plasenta :</label> 
                      </div>
                      <div class="col-md-1 col-sm-8 col-xs-12">
                        <label>Insersi :</label> 
                      </div>
                      <div class="col-md-1 col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi80" name="isi[75]" <? if ($dataKU[66] != '') echo "checked"; ?> value="Insersi Fundus"> Fundus
                      </div>
                      <input type="hidden" name="isi[76]">
                      
                      <div class="col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi82" name="isi[77]" <? if ($dataKU[68] != '') echo "checked"; ?> value="Corpus"> Corpus
                      </div>
                      <div class=" col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi83" name="isi[78]" <? if ($dataKU[69] != '') echo "checked"; ?> value="SBR"> SBR
                      </div>

                      <div class="col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi84" name="isi[79]" <? if ($dataKU[70] != '') echo "checked"; ?> value="Antenir"> Anterior
                      </div>

                      <div class="col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi86" name="isi[80]" <? if ($dataKU[71] != '') echo "checked"; ?> value="Posl"> Posterior
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div> -->
                      <div class=" col-md-1 col-sm-12 col-xs-12">
                      <label>Grade :</label>  
                      </div>
                      <div class="col-md-1 col-sm-12 col-xs-12">

                        <select id="isi94" class="select2_single form-control"  name="isi[81]" >
                      <option value=""></option>
                      <option value="I">I</option>       
                      <option value="II">II</option>
                      <option value="III">III</option>
                      <option value="IV">IV</option>
                  </select>                     
                        
                      </div>
                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-1 col-sm-12 col-xs-12">
                        <label>Ketuban :</label>
                      </div>

                      <div class=" col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi95" name="isi[82]" <? if ($dataKU[66] != '') echo "checked"; ?> value="Cukup"> Cukup
                      </div>
                      <div class="col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi96" name="isi[83]" <? if ($dataKU[68] != '') echo "checked"; ?> value="Kurang"> Kurang
                      </div>
                      <div class=" col-md-1 col-sm-1 col-xs-1">
                        <input type="checkbox" id="isi97" name="isi[84]" <? if ($dataKU[69] != '') echo "checked"; ?> value="Banyak"> Banyak
                      </div>
                      <div class="col-md-2 col-sm-1 col-xs-1"><label class="col-md-3 col-sm-1 col-xs-1">AFI</label> 
                       <input class="form-control col-md-1 col-sm-1 col-xs-1 type="text" style="width: 75px;" id="isi98" name="isi[85]">     

                    </div>
                   
                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                    <div class="col-md-1 col-sm-12 col-xs-12">
                    	<label>HPL/TP</label>
                    </div>
                    <div class="col-md-3 col-sm-12 col-xs-12">
                    <div class='input-group date col-md-8 col-sm-6 col-xs-12' id='datepicker4'>
                    <input type='text' class="form-control" data-inputmask="'mask': '99-99-9999'" id="isi99" name="isi[86]"value="">
                      <span class="input-group-addon">
                      <span class="fa fa-calendar">
                      </span>
                      </span>
                  </div>
		              </div>
                <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                     <div class="col-md-1 col-sm-8 col-xs-12">
                       <label>Lain - Lain</label>
                      </div>
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi100" name="isi[87]"></textarea>
                      </div>
                      </div>
                      <div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                     
                    <!-- </div> -->
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12"><br></div>

                <div class="item form-group">
                    <div class="col-md-12 col-sm-8 col-xs-12">
                      <div class="col-md-2 col-sm-8 col-xs-12">
                       <label>Pemeriksaan Penunjang</label>  
                      </div>
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi104" name="isi[90]"></textarea>
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                       <label>Analisa / Diagnosa</label>
                      </div>
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea class="form-control" id ="isi105" name="isi[91]"></textarea>
                      </div><div class="col-md-12 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Planning / Penatalaksanaan</label> 
                      </div>
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea id="isi106" class="form-control"  name="isi[92]"></textarea>
                      </div>
                      <div class="col-md-11 col-sm-12 col-xs-12"><br></div>
                      <div class="col-md-2 col-sm-8 col-xs-12">
                        <label>Lap Tindakan</label> 
                      </div>
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <textarea id="isi8" class="form-control"  name="isi[105]"></textarea>
                      </div>
                    </div>
                    <div class="col-md-12 col-xs-12 col-sm-12"><br></div>
          			<div class="col-md-offset-10 col-md-6 col-sm-6 col-xs-12 ">
          				<button id="btnUpdate" type="submit" class="btn col-md-3 btn-success" name="btnUpdate">Simpan</button>
                    
                </div>
              </div> 
                </div></form>
						</div>
					</div>
					
						<!-- tab 4 
						<div title="Gas Medis" style="padding:5px">
							<table id="dg3" style="width:100%;"
									toolbar="#toolbar3" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										<th data-options="field:'biaya_tarif_id',width:300,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_tarif_id',
													textField:'biaya_nama',
													url:'get_biaya_gas.php',
													mode: 'remote',
													onBeforeLoad:function(param){
														param.id_poli = document.getElementById('id_poli').value;
													}, 
													delay: 200,
													columns:[[
														{field:'biaya_nama',title:'Nama',width:300},
														{field:'biaya_total',title:'Biaya',width:100,options:{min:0,decimalSeparator:3}},
													]],
													required:true
												}
											}">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner',options:{precision:0}}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>										
									</tr>
								</thead>
							</table> 
							<div id="toolbar3">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_gas();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg3').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_gas()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_gas()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg3').edatagrid('reload')">refresh</a>
							</div>
						</div>
						
						<!-- tab 5 
						<div title="Ambulance" style="padding:5px">
							<table id="dg4" style="width:100%;"
									toolbar="#toolbar4" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										<th data-options="field:'biaya_tarif_id',width:300,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_tarif_id',
													textField:'biaya_nama',
													url:'get_biaya_ambulance.php',
													mode: 'remote',
													onBeforeLoad:function(param){
														param.id_poli = document.getElementById('id_poli').value;
													}, 
													delay: 200,
													columns:[[
														{field:'biaya_nama',title:'Nama',width:300},
														{field:'biaya_total',title:'Biaya',width:100,options:{min:0,decimalSeparator:3}},
													]],
													required:true
												}
											}">Tindakan</th>
																				
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100,
											editor:{
												type:'textbox'
											}">Supir</th>
										
										<th data-options="field:'no_plat',width:100,editor:{type:'textbox'}">No Plat</th>
											
									</tr>
								</thead>
							</table> 
							<div id="toolbar4">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_ambulance();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg4').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_ambulance()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_ambulance()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg4').edatagrid('reload')">refresh</a>
							</div>
						</div>
						<!-- tab 6 
						<div title="Labu Darah" style="padding:5px">
							<table id="dg5" style="width:100%;"
									toolbar="#toolbar5" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										<th data-options="field:'biaya_tarif_id',width:200,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_tarif_id',
													textField:'biaya_nama',
													url:'get_biaya_darah.php',
													mode: 'remote',
													delay: 200,
													onBeforeLoad:function(param){
														param.id_poli = document.getElementById('id_poli').value;
													}, 
													columns:[[
														{field:'biaya_nama',title:'Nama',width:300},
														{field:'biaya_total',title:'Biaya',width:100,options:{min:0,decimalSeparator:3}},
													]],
													required:true
												}
											}">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:50
										,editor:{type:'numberspinner',options:{precision:0}}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:0">Lunas</th>
										
										<th data-options="field:'no_kantong',width:50,editor:{type:'textbox'}">No Kantung</th>
										
										<th data-options="field:'gol_darah',width:100,
											editor:{
												type:'combobox',
												options:{
													data: [{
														label: 'A',
														value: 'A'
													},{
														label: 'AB',
														value: 'AB'
													},{
														label: 'B',
														value: 'B'
													},{
														label: 'O',
														value: 'O'
													}],
													valueField:'value',
													textField:'label',
													panelHeight: 'auto',
													required:true
												}
											}">Gol. Darah</th>
											
										<th data-options="field:'rhesus',width:100,
											editor:{
												type:'combobox',
												options:{
													data: [{
														label: 'Positif',
														value: 'Positif'
													},{
														label: 'Negatif',
														value: 'Negatif'
													}],
													valueField:'value',
													textField:'label',
													panelHeight: 'auto',
													required:true
												}
											}">Rhesus</th>
												
									</tr>
								</thead>
							</table> 
							<div id="toolbar5">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_darah();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg5').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_darah()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_darah()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg5').edatagrid('reload')">refresh</a>
							</div>
						</div>