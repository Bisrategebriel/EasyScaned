<head>
	<script src="js/script.js"></script>
</head>
<?php
	include_once "scon.php";
	echo "<link rel='stylesheet' href='css/custom.css'>";
	echo "<link rel='stylesheet' href='css/style.css'>";
	
	if(isset($_GET["dirct"])) {
		if(isset($_GET["type"])) {
			$type	= sanitizeString($con,$_GET["type"]);
		} else $type	= "scan";
		handleUploadedFiles($con,$type);
	}
	else if(isset($_GET["type"])) {
		global $user;
		$eop	= sanitizeString($con,$_GET["eop"]);
		$type	= sanitizeString($con,$_GET["type"]);
		$min	= sanitizeString($con,$_GET["min"]);
		if($type == "min") {
			if(!file_exists("minutes")) mkdir("minutes");
			if(is_array($_FILES)) {
				foreach($_FILES as $name => $fileSpec) {
					$tmpname	= $fileSpec['name'].time();
					$fupd	= move_uploaded_file($fileSpec['tmp_name'],"minutes/$tmpname");
					$fname	= "$min".time().".pdf";
					rename("minutes/$tmpname","minutes/$fname");
					logthis($con,$eop,"minute added","$type $fname","admin");
					echo "<a target='_blank' href='admin.php?eop=$user&fileview=minutes/$fname'>View</a>";
					echo "<input type='hidden' name='min' value='$fname'>";
				}
			}
		}
	}
	function handleUploadedFiles($con,$type) {
		$date = getCurTime();
		
		$office = ".";
		if(isset($_GET["dirct"])) {
			$office = $_GET["dirct"];
		}
		$bound	= "";
		if(isset($_GET["bound"])) {
			$bound	= sanitizeString($con,$_GET["bound"]);
		}
		$eop	= "";
		if(isset($_GET["eop"])) {
			$eop	= sanitizeString($con,$_GET["eop"]);
		}
		$filelink	= "";
		if(isset($_GET["ltrfile"]))
			$filelink	= sanitizeString($con,$_GET["ltrfile"]);
		
		$filename	= "";
		if($bound != "") {

		if(is_array($_FILES)) {
			foreach($_FILES as $name => $fileSpec) {
				if(!is_array($fileSpec)) {
					continue;
				}
				if(!file_exists("archives/$bound/$office"))
					mkdir("archives/$bound/$office");
				
				$fupd	= move_uploaded_file($fileSpec['tmp_name'],"archives/$bound/$office/".$fileSpec['name']);
				$filename	= $fileSpec['name'];
			}
		}
			if($type == "link" || $type == "attach") {
				$filename	= $filelink;
			}
				$sctime	= $date;
				if(getNum($con,"printform where name='$filename' && source='$office' && bound='$bound' && status='empty'") < 1)
					$dbupd	= mysqli_query($con,"insert into printform(name,source,user,bound,scantime,status,upltype) values('".$filename."','$office','$eop','$bound','$sctime','empty','$type')");
				else $dbupd = 1;
				logthis($con,$eop,"file scanned",$filename,$office);
				//$frmrow	= mysqli_fetch_assoc(mysqli_query($con,"select * from printform where name='".$filename."' && source='$office' && bound='$bound' && status='empty'"));
				
				if($dbupd) {
					dispFileForm($con,$filename,$office,$bound,$eop);
				} else msg("err","Error while posting to database! Please report this error");
			
		}
	}
?>