<?php
#:: Module Installer 1.0 (Beta 3)
#::	Written By Raymond Irving - Dec 2004
#:::::::::::::::::::::::::::::::::::::::::
#:: Installs Modules, Plugins, Snippets, Chunks

	// start session
	session_start();

	// set error reporting
	error_reporting(E_ALL ^ E_NOTICE);
	
	// session loop-back tester
	if(!$_SESSION['session_test'] && $_GET['s']!='set') {
		$_SESSION['session_test'] = 1;
		// I had some problems with sessions when I used headers. This works ok
		echo "<html><head><title>Loading...</title><script>window.location.href='".$_SERVER['PHP_SELF']."?s=set';</script></head><body></body></html>";
		exit;
	}

	$moduleName 		= "Module Installation";
	$moduleVersion 		= "1.0 ";
	$moduleSQLBaseFile 	= "setup.sql";
	$moduleSQLDataFile 	= "setup.data.sql";
	$moduleWhatsNewFile = "setup.whatsnew.html";
	$moduleWhatsNewTitle= "What's New";
	
	$moduleWelcomeMessage= "";
	$moduleLicenseMessage= "";

	$moduleChunks 	 	= array(); // chunks - array : name, description, type - 0:file or 1:content, file or content
	$moduleSnippets 	= array(); // snippets - array : name, description, type - 0:file or 1:content, file or content,properties
	$modulePlugins		= array(); // plugins - array : name, description, type - 0:file or 1:content, file or content,properties
	$moduleTemplates 	= array(); // templates - array : name, description, type - 0:file or 1:content, file or content,properties
	$moduleTVs		 	= array(); // template variables - array : name, description, type - 0:file or 1:content, file or content,properties

	# function to call after setup
	$callBackFnc =""; 			
	
	# load setup information file
	$setupPath = dirname(__FILE__);
	include_once "$setupPath/setup.info.php";
	
	$errors = 0;
	$syscheck = ($_POST['syscheck']=="on") ? true:false;
	$upgradeable = file_exists("../manager/includes/config.inc.php") ? 1:0;

	$installMode = !$upgradeable ? 0:-1;
	if(count($_POST)) $installMode = $_POST['installmode']=='upd' ? 1:0;
	
	// get post back status
	$isPostBack = (count($_POST) && !$syscheck);
		
	// Test db connecttion
	if($isPostBack && isset($_POST["dbtest"])){
		$color 	= '';
		$uid	= $_POST["dbuid"];
		$pwd	= $_POST["dbpwd"];
		$host	= $_POST["dbhost"];
		$dbase	= $_POST["dbase"];
		$table_prefix = $_POST["tableprefix"];
		// connect to the database
		$status = ' Connection to host: ';
		if(!@$conn=mysql_connect($host, $uid, $pwd)) {
			$status .= "failed!";			
			$color = '#ff0000';
		}
		else {
			$status .= 'passed';
			// select database
			$status .= '...    Checking database: ';
			if(!@mysql_select_db(str_replace("`","",$dbase), $conn)) $status .= "failed - $dbase does not exist!"; 
			else {
				if(@$rs=mysql_query("SELECT COUNT(*) FROM $dbase.".$table_prefix."site_content")) $status .= "failed - table prefix already in use!";
				else {
					$status .= 'passed';
					$color = '#007700';
				}
			}
		}		 
		echo "<script>parent.testResult('$status','$color');</script>";
		exit;
	} // end - Test db connecttion
	
	// start install process
	if($isPostBack) {
		ob_start();
		include_once "$setupPath/instprocessor.php";
		$moduleWelcomeMessage = ob_get_contents();
		ob_end_clean();
	}
	
	// build Welcome Screen
	function buildWelcomeScreen() {
		global $moduleName;
		global $moduleWelcomeMessage;
		if ($moduleWelcomeMessage) return $moduleWelcomeMessage;
		else {
			ob_start();
			?>
				<table width="100%">
				<tr>
				<td valign="top">
					<p class='title'>Welcome to the <?php echo $moduleName; ?> installation program.</p>
					<p>This program will guide you through the rest of the installtion.</p>
					<p>Please select 'Next' button to continue:</p>
					<br />
					<center><img src="img_splash.gif" /></center>
				</td>
				<td align="center" width="280">
					<img src="img_box.png" />&nbsp;
				</td>
				</tr>
				</table>
				
			<?php
			$o = ob_get_contents();
			ob_end_clean();
			return $o;
		}		
	}

	// build License Screen
	function buildLicenseScreen() {
		global $moduleName;
		global $moduleLicenseMessage;
		if ($moduleLicenseMessage) return $moduleLicenseMessage;
		else {
			ob_start();
			?>
				<div style="padding-right:10px;">
				    <p class='title'><?php echo $moduleName; ?> License Agreement.</p>
				    <hr style="text-align:left;height:1px;width:90%" />
					<p><h4>You must agree to the License before continuing installation.</h4>
					Usage of this software is subject to the GPL license. To help you understand 
					what the GPL licence is and how it affects your ability to use the software, we 
					have provided the following summary:</p>
					<h4>The GNU General Public License is a Free Software license.</h4>
					<p>Like any Free Software license, it grants to you the four following freedoms:</p> 
					<ul>
                        <li>The freedom to run the program for any purpose. </li>
                        <li>The freedom to study how the program works and adapt it to your needs. </li>
                        <li>The freedom to redistribute copies so you can help your neighbor. </li>
                        <li>The freedom to improve the program and release your improvements to the 
                        public, so that the whole community benefits. </li>
					</ul>
					<p>You may exercise the freedoms specified here provided that you comply with 
					the express conditions of this license. The principal conditions are:</p>
					<ul>
                        <li>You must conspicuously and appropriately publish on each copy distributed an 
                        appropriate copyright notice and disclaimer of warranty and keep intact all the 
                        notices that refer to this License and to the absence of any warranty; and give 
                        any other recipients of the Program a copy of the GNU General Public License 
                        along with the Program. Any translation of the GNU General Public License must 
                        be accompanied by the GNU General Public License.</li>

                        <li>If you modify your copy or copies of the program or any portion of it, or 
                        develop a program based upon it, you may distribute the resulting work provided 
                        you do so under the GNU General Public License. Any translation of the GNU 
                        General Public License must be accompanied by the GNU General Public License. </li>

                        <li>If you copy or distribute the program, you must accompany it with the 
                        complete corresponding machine-readable source code or with a written offer, 
                        valid for at least three years, to furnish the complete corresponding 
                        machine-readable source code.</li>

                        <li>Any of these conditions can be waived if you get permission from the 
                        copyright holder.</li>

                        <li>Your fair use and other rights are in no way affected by the above. 
                        </li>
                    </ul>
					<p>The above is a summary of the GNU General Public License. By proceeding, you 
					are agreeing to the GNU General Public Licence, not the above. The above is 
					simply a summary of the GNU General Public Licence, and its accuracy is not 
					guaranteed. It is strongly recommended you read the <a href="http://www.gnu.org/copyleft/gpl.html" target=_blank>GNU General Public 
					License</a> in full before proceeding, which can also be found in the license 
					file distributed with this package.</p>
				</div>
			<?php
			$o = ob_get_contents();
			ob_end_clean();
			return $o;
		}		
	}

	// build install mode Screen
	function buildInstallModeScreen() {
		global $upgradeable;
		global $moduleName;
		ob_start();
		?>
			<table border="0" width="100%">
			  <tr>
				<td nowrap valign="top" width="37%">
				<input type="radio" name="installmode" id="installmode1" value="new" onclick="setInstallMode(0);" <?php echo !$upgradeable||$_POST['installmode']=='new' ? "checked='checked'":"" ?> /><label for="installmode1" class="nofloat">New Installation</label></td>
				<td width="61%">This will install a new copy of the <?php echo $moduleName; ?> software on your web site. Please note that this option may overwrite any data inside your database. <strong>NOTE:</strong> For new Linux/Unix installations, you will need to create a new empty file named <code>config.inc.php</code> inside the <code>/manager/includes</code> directory with permissions set to 777.</td>
			  </tr>
			  <tr>
				<td nowrap valign="top" width="37%">&nbsp;</td>
				<td width="61%">&nbsp;</td>
			  </tr>
			  <tr>
				<td nowrap valign="top" width="37%">
				<input type="radio" name="installmode" id="installmode2" value="upd" onclick="setInstallMode(1);" <?php echo !$upgradeable ? "disabled='diabled'":"" ?> <?php echo $_POST['installmode']=='upd' ? "checked='checked'":"" ?> /><label for="installmode2" class="nofloat">Upgrade Installation</label></td>
				<td width="61%">Select this option to upgrade your current files and database.</td>
			  </tr>
			</table>
		<?php
		$o = ob_get_contents();
		ob_end_clean();
		return $o;		
	}

	// build Connection Screen
	function buildConnectionScreen() {
		ob_start();
		?>  
			<p class="title">Connection Information</p>
			<p>Database connection and login information</p>
			<p>Please enter the name of the database created for MODX. If you there is no database yet, the installer will attempt to create a database for you. This may fail depending on the MySQL configuration or the database user permissions for your domain/installation.</p>
			<div class="labelHolder"><label for="databasename">Database name:</label>
			<input id="databasename" value="<?php echo isset($_POST['databasename']) ? $_POST['databasename']:"modx" ?>" name="databasename" /></div>
			<div class="labelHolder"><label for="tableprefix">Table prefix:</label>
			<input id="tableprefix" value="<?php echo isset($_POST['tableprefix']) ? $_POST['tableprefix']:"modx_" ?>" name="tableprefix" /></div>
			<br />
			<p>Now please enter the login data for your database.</p>
			<br />
			<div class="labelHolder"><label for="databasehost">Database host:</label>
			<input id="databasehost" value="<?php echo isset($_POST['databasehost']) ? $_POST['databasehost']:"localhost" ?>" name="databasehost" /></div>
			<div class="labelHolder"><label for="databaseloginname">Database login name:</label>
			<input id="databaseloginname" name="databaseloginname" value="<?php echo isset($_POST['databaseloginname']) ? $_POST['databaseloginname']:"" ?>" /></div>
			<div class="labelHolder"><label for="databaseloginpassword">Database password:</label>
			<input id="databaseloginpassword" type="password" name="databaseloginpassword"  value="<?php echo isset($_POST['databaseloginpassword']) ? $_POST['databaseloginpassword']:"" ?>" />&nbsp;
			<input type="button" name="cmdtest" value="Test connection" style="width:130px" onclick="testConnection()" /><br />
			<input id="testbox" name="testbox" value="" size="30" />
  			</div>

			<p>Now you&#39;ll need to enter some details for the main administrator account. You can fill in your own name here, and a password you&#39;re not likely to forget. You&#39;ll need these to log into Admin once setup is complete.</p>

			<div class="labelHolder"><label for="cmsadmin">Administrator username:</label>
			<input id="cmsadmin" value="<?php echo isset($_POST['cmsadmin']) ? $_POST['cmsadmin']:"admin" ?>" name="cmsadmin" /></div>
			<div class="labelHolder"><label for="cmspassword">Administrator password:</label>
			<input id="cmspassword" type="password" name="cmspassword" value="<?php echo isset($_POST['cmspassword']) ? $_POST['cmspassword']:"" ?>" /></div>
			<div class="labelHolder"><label for="cmspasswordconfirm">Confirm password:</label>
			<input id="cmspasswordconfirm" type="password" name="cmspasswordconfirm" value="<?php echo isset($_POST['cmspasswordconfirm']) ? $_POST['cmspasswordconfirm']:"" ?>" /></div>
			<br />

		<?php
		$o = ob_get_contents();
		ob_end_clean();
		return $o;		
	}
	
	// build Options Screen
	function buildOptionsScreen() {
		global $moduleChunks;
		global $modulePlugins;
		global $moduleSnippets;
		ob_start();	
		echo "<p class=\"title\">Optional Items</p><p>Please choose your installation options and click Install:</p>";
		
		// display chunks
		$chunks = isset($_POST['chunk']) ? $_POST['chunk']:array();
		$limit = count($moduleChunks);
		if ($limit>0) echo "<h1>Chunks</h1>";
		for ($i=0;$i<$limit;$i++) {
			$chk = in_array($i,$chunks)||(!count($_POST)) ? "checked='checked'": "";
			echo "&nbsp;<input type='checkbox' name='chunk[]' value='$i' $chk />Install/Update <span class='comname'>".$moduleChunks[$i][0]."</span> - ".$moduleChunks[$i][1]."<hr size='1' style='border:1px dotted silver;' />";
		}

		// display plugins
		$plugins = isset($_POST['plugin']) ? $_POST['plugin']:array();
		$limit = count($modulePlugins);
		if ($limit>0) echo "<h1>Plugins</h1>";
		for ($i=0;$i<$limit;$i++) {
			$chk = in_array($i,$plugins)||(!count($_POST)) ? "checked='checked'": "";
			echo "&nbsp;<input type='checkbox' name='plugin[]' value='$i' $chk />Install/Update <span class='comname'>".$modulePlugins[$i][0]."</span> - ".$modulePlugins[$i][1]."<hr size='1' style='border:1px dotted silver;' />";
		}

		// display snippets
		$snippets = isset($_POST['snippet']) ? $_POST['snippet']:array();
		$limit = count($moduleSnippets);
		if ($limit>0) echo "<h1>Snippets</h1>";
		for ($i=0;$i<$limit;$i++) {
			$chk = in_array($i,$snippets)||(!count($_POST)) ? "checked='checked'": "";
			echo "&nbsp;<input type='checkbox' name='snippet[]' value='$i' $chk />Install/Update <span class='comname'>".$moduleSnippets[$i][0]."</span> - ".$moduleSnippets[$i][1]."<hr size='1' style='border:1px dotted silver;' />";
		}

		$o = ob_get_contents();
		ob_end_clean();
		return $o;
	}

	// build Summary Screen
	function buildSummaryScreen() {
		global $errors;
		global $installMode;
		ob_start();
		echo "<p>Setup has carried out a number of checks to see if everything's ready to start the setup.</p>";
		$errors = 0;
		// check PHP version
		echo "<p>Checking PHP version: ";
		$php_ver_comp =  version_compare(phpversion(), "4.1.0");
		$php_ver_comp2 =  version_compare(phpversion(), "4.3.8");
		// -1 if left is less, 0 if equal, +1 if left is higher
		if($php_ver_comp < 0) {
			echo "<span class='notok'>Failed!</span> - You are running on PHP ".phpversion().", and ModX requires PHP 4.1.0 or later</p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
			if($php_ver_comp2 < 0) {
			   echo "<fieldset><legend>Security notice</legend><p>While MODx will work on your PHP version (".phpversion()."), usage of MODx on this version is not recommended. Your version of PHP is vulnerable to numerous security holes. Please upgrade to PHP version is 4.3.8 or higher, which patches these holes. It is recommended you upgrade to this version for the security of your own website.</p></fieldset>";	
			}
		}
		// check sessions
		echo "<p>Checking if sessions are properly configured: ";
		if($_SESSION['session_test']!=1 ) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// check directories
		// cache exists?
		echo "<p>Checking if <span class='mono'>assets/cache</span> directory exists: ";
		if(!file_exists("../assets/cache")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// cache writable?
		echo "<p>Checking if <span class='mono'>assets/cache</span> directory is writable: ";
		if(!is_writable("../assets/cache")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// cache files writable?
		echo "<p>Checking if <span class='mono'>assets/cache/siteCache.idx.php</span> file is writable: ";
		if(!is_writable("../assets/cache/siteCache.idx.php")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		echo "<p>Checking if <span class='mono'>assets/cache/sitePublishing.idx.php</span> file is writable: ";
		if(!is_writable("../assets/cache/sitePublishing.idx.php")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// images exists?
		echo "<p>Checking if <span class='mono'>assets/images</span> directory exists: ";
		if(!file_exists("../assets/images")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// images writable?
		echo "<p>Checking if <span class='mono'>assets/images</span> directory is writable: ";
		if(!is_writable("../assets/images")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// export exists?
		echo "<p>Checking if <span class='mono'>assets/export</span> directory exists: ";
		if(!file_exists("../assets/export")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// export writable?
		echo "<p>Checking if <span class='mono'>assets/export</span> directory is writable: ";
		if(!is_writable("../assets/export")) {
			echo "<span class='notok'>Failed!</span></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// config.inc.php writable?
		echo "<p>Checking if <span class='mono'>manager/includes/config.inc.php</span> is writable: ";
		$isWriteable = is_writable("../manager/includes/config.inc.php");
		if(!file_exists("../manager/includes/config.inc.php")) {
			// make an attempt to create the file
			@$hnd=fopen("../manager/includes/config.inc.php", 'w');
			@fwrite($hnd,"<?php //MODx configuration file ?>");
			@fclose($hnd);
			$isWriteable = file_exists("../manager/includes/config.inc.php");
			@unlink("../manager/includes/config.inc.php");
		}
		if(!$isWriteable) {
			echo "<span class='notok'>Failed!</span></p><p><strong>For new Linux/Unix installs, please create a blank file named <span class='mono'>config.inc.php</span> in the <span class='mono'>manager/includes/</span> directory with file permissions set to 777.</strong></p>";
			$errors += 1;
		} else {
			echo "<span class='ok'>OK!</span></p>";
		}
		
		// connect to the database
		if($installMode==1) {
			include "../manager/includes/config.inc.php";
		}
		else {		
			// get db info from post
			$database_server = $_POST['databasehost'];
			$database_user = $_POST['databaseloginname'];
			$database_password = $_POST['databaseloginpassword'];
			$dbase = $_POST['databasename'];
			$table_prefix = $_POST['tableprefix'];
		}
		echo "<p>Creating connection to the database: ";
		if(!@$conn = mysql_connect($database_server, $database_user, $database_password)) {
			$errors += 1;
			echo "<span class='notok'>Database connection failed!</span><p />Please check the database login details and try again.</p>";
		} 
		else {
			echo "<span class='ok'>OK!</span></p>";
		}
		// check table prefix
		if($conn && $installMode==0) {
			echo "<p>Checking table prefix `".$table_prefix."`: ";
			if(@$rs=mysql_query("SELECT COUNT(*) FROM $dbase.".$table_prefix."site_content")) {
				echo "<span class='notok'>Failed!</span></b> - Table prefix is already in use in this database!</p>";
				$errors += 1;
				echo "<p>Setup couldn't install into the selected database, as it already contains tables with the prefix you specified. Please choose a new table_prefix, and run Setup again.</p>";
			} 
			else {
				echo "<span class='ok'>OK!</span></p>";
			}
		}		

		if($errors>0) {
		?>
			<p>
			Unfortunately, Setup cannot continue at the moment, due to the above <?php echo $errors > 1 ? $errors." " : "" ; ?>error<?php echo $errors > 1 ? "s" : "" ; ?>. Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>, and try again. If you need help figuring out how to fix the problem<?php echo $errors > 1 ? "s" : "" ; ?>, visit the <a href="http://www.vertexworks.com/forums/" target="_blank">Operation MODx Forums</a>.
			</p>
		<?php
		}

		echo "<p>&nbsp;</p>";
		$o = ob_get_contents();
		ob_end_clean();
		return $o;		
	}	

?>
<!DOCTYPE html PUBliC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?php echo $moduleName; ?> &raquo; Install</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url(./style.css);
        </style>
	<script type="text/javascript" language="JavaScript" src="webelm.js"></script>
	<script language="JavaScript" type="text/javascript">
    	
    	var cursrc = 1;
		var syscheck = <?php echo $syscheck ? "true":"false"; ?>;
		var installMode = <?php echo $installMode; ?>; // -1 - not set, 0 - new, 1 - upgrade
		var sidebar = "<a href='<?php echo $moduleWhatsNewFile; ?>' target='_blank'><?php echo $moduleWhatsNewTitle; ?></a>&nbsp;<p /><img src='img_install.gif' width='48' height='48' />";		

		// set I Agree
		function setIAgree(){
			var btnnext = document.install.cmdnext;
			var chkagree = document.install.chkagree;
			if(!chkagree.checked) btnnext.disabled="disabled";
			else btnnext.disabled = "";					
		}
		
		// set install mode
		function setInstallMode(n) {
			var btnnext = document.install.cmdnext;
			installMode=n;
			btnnext.disabled = "";			
		}

		// test DB connection	
		function testConnection(){
			var f=document.testform;
			f.target="testPort";
			f.dbuid.value = document.install.databaseloginname.value;
			f.dbpwd.value = document.install.databaseloginpassword.value;
			f.dbase.value = document.install.databasename.value;
			f.dbhost.value= document.install.databasehost.value;
			f.tableprefix.value = document.install.tableprefix.value;
			f.submit();
			document.install.testbox.value="Testing connection...";
			var tb = document.getElementById("testbox");
			tb.style.display="block";
			tb.style.color = "#000099";
		}

		// test DB result	
		function testResult(msg,color){
			var tb = document.install.testbox;
			tb.value = msg;
			if (color && tb.style) tb.style.color = color;
		}

		// jumpTo
		function jumpTo(n) {
			cursrc = n;
			for(i=1;i<=5;i++) {
				o = document.getElementById("screen"+i);
				if (o) {
					if(i==cursrc) o.style.display="block";
					else o.style.display="none";
				}
			}
		}
			
		// change screen
		function changeScreen(n){
			var o;
			var viewer = document.getElementById("viewer");
			var agreebox = document.getElementById("iagreebox");
			var btnback = document.install.cmdback;
			var btnnext = document.install.cmdnext;
			
			//window.scrollTo(0,0);
			viewer.scrollTop = 0;
			// set default values
			btnback.value = "Back";
			btnnext.value = "Next";
			agreebox.style.display="none";

			if(n==1) cursrc += 1;
			else cursrc -= 1;
			if(cursrc > 7) cursrc = 7;
			if(cursrc < 1) cursrc = 1;
			switch (cursrc) {
				case 1:	// welcome
					btnnext.disabled = "";
					btnback.style.display="none";
					break;
				case 2:	// license
					var chkagree = document.install.chkagree;
					if(!chkagree.checked) btnnext.disabled="disabled";
					else btnnext.disabled = "";
					btnback.style.display="block";
					agreebox.style.display="block";
					break;
				case 3:	// new/upgrade
					if(installMode==-1) btnnext.disabled="disabled";
					btnback.style.display="block";
					break;
				case 4:	// connection settings
					btnnext.disabled = "";
					if(installMode==1 && n==1) {
						jumpTo(5);
					}
					else if(installMode==1 && n==-1) {
						jumpTo(3);
					}
					else {
						btnback.style.display="block";
						agreebox.style.display="none";
					}
					break;
				case 5:	// options
					if(installMode==0 && !validate()) {
						cursrc=4;
						return;
					}
					else {
						syscheck=false;
						btnnext.disabled = "";
						btnback.style.display="block";
					}
					break;
				case 6:	// summary
					if(!syscheck) {
						btnnext.disabled = "disabled";
						document.install.syscheck.value="on";
						document.install.submit();						
						return;
					}
					btnnext.value = "Install now";
					btnback.style.display="block";
					if(errors>0) {
						btnnext.value = "Retry";
					}
						
					break;
				case 7:	// final screen
					if(document.install.syscheck=="on") {
						btnnext.disabled = "disabled";
						document.install.submit();						
						return;
					}
					btnnext.value = "Close";
					btnback.style.display="none";
					document.install.submit();
					btnback.disabled = "disabled";
					btnnext.disabled = "disabled";
					break;
			}
			for(i=1;i<=7;i++) {
				o = document.getElementById("screen"+i);
				if (o) {
					if(i==cursrc) o.style.display="block";
					else o.style.display="none";
				}
			}
		}
		
		// validate
		function validate() {
			var f = document.install;
			if(f.databasename.value=="") {
				alert('You need to enter a value for database name!');
				f.databasename.focus();
				return false;
			}
			if(f.databasehost.value=="") {
				alert('You need to enter a value for database host!');
				f.databasehost.focus();
				return false;
			}
			if(f.databaseloginname.value=="") {
				alert('You need to enter your database login name!');
				f.databaseloginname.focus();
				return false;
			}
			if(f.cmsadmin.value=="") {
				alert('You need to enter a username for the system admin account!');
				f.cmsadmin.focus();
				return false;
			}
			if(f.cmspassword.value=="") {
				alert('You need to a password for the system admin account!');
				f.cmspassword.focus();
				return false;
			}
			if(f.cmspassword.value!=f.cmspasswordconfirm.value) {
				alert('The administrator password and the confirmation don\'t match!');
				f.cmspassword.focus();
				return false;
			}
			return true;
		}
		
		function rmInstallResult(msg){
			if(msg) alert(msg);
			gotoManager();
		}
		
		function closepage(){
			var chk = document.install.rminstaller;
			if(chk && chk.checked) {
				// remove install folder and files
				window.location.href = "../manager/processors/remove_installer.processor.php?rminstall=1";
			}
			else { 
				window.location.href = "../manager/";
			}
		}
		
    </script>
</head>	

<body>
<!-- start install screen-->
<table border="0" cellpadding="0" cellspacing="0" class="mainTable" style="width:100%;">
<tr>
    <td colspan="2">
		  <img style="padding:12px" src="img_banner.gif">
    </td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right"><?php echo $moduleName; ?> </b>&nbsp;<i>version <?php echo $moduleVersion; ?></i></td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">
			<table border="0" width="100%">
			<tr>
			<td valign="top" nowrap="nowrap"><div id="sidebar" class="sidebar"><script>document.write(sidebar);</script></div></td>
			<td style="border-left:1px dotted silver;padding-left:30px;padding-right:20px;">
			<form name="install" action="index.php?s=set" method="post">
			<div id="viewer" class="viewer" style="visibility:hidden">
				<div id="screen1" style="display:block"><?php echo buildWelcomeScreen(); ?></div>
				<?php if(!$isPostBack) { ?>
					<div id="screen2" style="display:none"><?php echo buildLicenseScreen(); ?></div>
					<div id="screen3" style="display:none"><?php echo buildInstallModeScreen(); ?></div>
					<div id="screen4" style="display:none"><?php echo buildConnectionScreen(); ?></div>
					<div id="screen5" style="display:none"><?php echo buildOptionsScreen(); ?></div>
					<div id="screen6" style="display:none"><?php if($syscheck) echo buildSummaryScreen(); ?></div>
					<div id="screen7" style="display:none"><p /><br /><h1>Running setup script... please wait</h1></div>
				<?php } ?>
			</div>
			<br />
			<div id="navbar">
				<?php if($isPostBack) { ?>
					<input type='button' value='Close' name='cmdclose' style='float:right;width:100px;' onclick="closepage();" />
					<?php if($errors==0) { ?>
						<span id="removeinstall" style='float:left;cursor:pointer;color:#505050;line-height:18px;' onclick="var chk=document.install.rminstaller; if(chk) chk.checked=!chk.checked;"><input type="checkbox" name="rminstaller" onclick="event.cancelBubble=true;" <?php echo empty($errors) ? 'checked="checked"':''; ?> style="cursor:default;" />Remove the install folder and files from my website. </span>
					<?php } ?>
				<?php } else {?>
					<input type='button' value='Next' name='cmdnext' style='float:right;width:100px;' onclick="changeScreen(1);" />
					<span style="float:right">&nbsp;</span>
					<input type='button' value='Back' name='cmdback' style='float:right;width:100px;' onclick="changeScreen(-1);" />
					<span id="iagreebox" style='float:left;cursor:pointer;background-color:#eee;line-height:18px'><input type='checkbox' value='1' id='chkagree' name='chkagree' onclick="setIAgree()" <?php echo isset($_POST['chkagree']) ? "checked='checked'":""; ?> style='line-height:18px'/><label for='chkagree' style='display: inline;float:none;line-height:18px;'> I agree to the terms set out in this license. </label></span>
				<?php } ?>
			</div>
			<input name="syscheck" type="hidden" value="<?php echo ($syscheck && $errors) ? "on":""; ?>" />
			</form>
			<form name="testform" method="post">
				<input name="dbtest" type="hidden" />
				<input name="dbuid" type="hidden" />
				<input name="dbpwd" type="hidden" />
				<input name="dbase" type="hidden" />
				<input name="dbhost" type="hidden" />
				<input name="tableprefix" type="hidden" />
			</form>
			<iframe name="testPort" width="1" height="1" style="visibility:hidden;'"></iframe>
            </td>
            </tr>
            </table>
		</td>
      </tr>
    </table></td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText" colspan="2"> 
    &nbsp;</td>
  </tr>
</table>
<!-- end install screen-->
<script type="text/javascript">
	var errors = <?php echo $errors; ?>;
</script>

<?php if(!$isPostBack) { ?>
	<script>
		<?php if ($syscheck) { ?>
			cursrc = 5;
			changeScreen(1);
		<?php } else {?>
		var agreebox = document.getElementById("iagreebox");
		var btnback = document.install.cmdback;
		agreebox.style.display="none";
		btnback.style.display="none";
		<?php } ?>
	</script>
<?php } ?>
<script type="text/javascript">
	var iviewer = document.getElementById("viewer");
	iviewer.style.visibility = 'visible';
</script>
</body>
</html>