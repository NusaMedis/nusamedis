		<div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="#" onClick="javascript: return Logout();" data-original-title="Logout">
                    <h2><span class="glyphicon glyphicon-off" aria-hidden="true"></span>  Logout</h2>
                  </a>
                </li>
				<li class="">
                  <a href="#" data-original-title="Username anda">
                    <h2><?php echo $userName ; ?></h2>
                  </a>
                </li>
				
			  </ul>	
            </nav>
          </div>
        </div>
		
		<script language="javascript">
		function Logout()
		{
			if(confirm("Apakah anda yakin ingin keluar?")) window.parent.document.location.href="../../login/logout.php";
			else return false;
		}
		</script>