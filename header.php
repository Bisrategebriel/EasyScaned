<?php
	require_once "scon.php";
	session_start();
	
	$user = "";
	$office = "";
	$loggedin = false;
		
	if(isset($_COOKIE["usrac4"])) {
		$user	= sanitizeString($con,$_COOKIE["usrac4"]);
		$pass	= sanitizeString($con,$_COOKIE["usrac4p"]);
		
		$sql	= mysqli_query($con,"select * from users where eop='$user'");
		$sqlrow	= mysqli_fetch_assoc($sql);
		$pwsql	= mysqli_query($con,"select * from passwords where id='".$sqlrow['password']."' && pass='$pass' && status='enabled'");
		if(mysqli_num_rows($sql) > 0 && mysqli_num_rows($pwsql) > 0)
			$loggedin = true;
		$_SESSION["esuser"] = $user;
		$drctsql = mysqli_query($con,"select * from dtsmembers where eop='$user' && status='approved'");
		if(mysqli_num_rows($drctsql) > 0)
			$office = mysqli_fetch_row($drctsql)[0];
	}
	if(isset($_POST["fileinfosbt"])) {
		$fname	= sanitizeString($con,$_POST["filename"]);
		$cfile	= sanitizeString($con,$_POST["cfilename"]);
		$source = $office;
		$descr	= sanitizeString($con,$_POST["descr"]);
		$subj	= sanitizeString($con,$_POST["subject"]);
		$sctime	= sanitizeString($con,$_POST["sctime"]);
		$odate	= sanitizeString($con,$_POST["date"]);
		$number	= sanitizeString($con,$_POST["number"]);
		if(isset($_POST["cc"]))
			$cc 	= sanitizeString($con,$_POST["cc"]);
		else $cc	= "";
		$type	= $bound = "";
		$dest	= sanitizeString($con,$_POST["dest"]);
		$from	= sanitizeString($con,$_POST["from"]);
		if(isset($_POST["seclvll"])) 
			$seclvl	= sanitizeString($con,$_POST["seclvll"]);
		else {
			$maxval	= mysqli_fetch_row(mysqli_query($con,"select max(value) from sectype"))[0];
			$seclvl	= mysqli_fetch_assoc(mysqli_query($con,"select name from sectype where value='$maxval'"))["name"];
		}
		if(getNum($con,"sectype where name='$seclvl'") > 0)
			$seclvldef	= true;
		else $seclvldef	= false;
		
		$date	= getCurTime();

		if(isset($_POST["bound"])) {
			$bound	= sanitizeString($con,$_POST["bound"]);
			if(isset($_POST[$bound."type"]))
				$type	= sanitizeString($con,$_POST[$bound."type"]);
			else $type	= $bound;
		}
										if(isset($_POST["sendmail"])) {
											$val = sanitizeString($con,$_POST["sendmail"]);
											if($val == "on") {
												$secval	= mysqli_query($con,"select value from sectype where name='$seclvl' order by value");
												$secval	= mysqli_fetch_row($secval)[0];
												$to		= mysqli_fetch_assoc(mysqli_query($con,"select email from office where name='$dest'"))["email"];
												if(getNum($con,"sectype where value>$secval") < 1) {
													echo "<script>alert('email => $to')</script>";
													logthis($con,$user,"email sent",$to,$office);
												}
												$usrsql	= mysqli_query($con,"select * from dtsmembers where dtsname='$dest' && emailsend='1'");
												$usrnum	= mysqli_num_rows($usrsql);
												for($i=0;$i<$usrnum;$i++) {
													$rows	= mysqli_fetch_assoc($usrsql);
													$usrscl	= mysqli_query($con,"select seclevel from pos where name='".$rows["position"]."' && office='$dest'");
													$usrscl	= mysqli_fetch_row($usrscl)[0];
													$usval	= mysqli_query($con,"select value from sectype where name='$usrscl' order by value");
													$usval	= mysqli_fetch_row($usval)[0];
													
													if($secval < $usval) continue;
													
													$to = $rows["eop"];
													//$from	= $user;
													$subject= $subj;
													$message= $descr;
													echo "<script>alert('email => $to');</script>";
													logthis($con,$user,"email sent",$to,$office);
													//sendMail($to,$from,$subject,$message,$file);
													if($cc != "") {
														$cc	= explode(",",$cc);
														for($k=0;$k<sizeof($cc);$k++) {
															if(strstr($cc[$k],"@")) {
																$suff	= explode("@",$cc[$k]);
																if(getNum($con,"emsuf where name='".$suff[1]."'") > 0) {
																	echo "<script>alert('email => ".$cc[$k]."');</script>";
																	logthis($con,$user,"email sent",$cc[$k],$office);
																}
															//sendMail($cc[$k],$user,$subj,$descr,$file);
															} else {
																$ccsql	= mysqli_query($con,"select * from dtsmembers where dtsname='".$cc[$k]."' && emailsend='1'");
																$ccnum	= mysqli_num_rows($ccsql);
																for($l=0;$l<$ccnum;$l++) {
																	$ccrow	= mysqli_fetch_assoc($ccsql);
																	
																	
																	$usrscl	= mysqli_query($con,"select seclevel from pos where name='".$ccrow["position"]."' && office='".$cc[$k]."'");
																	$usrscl	= mysqli_fetch_row($usrscl)[0];
																	$usval	= mysqli_query($con,"select value from sectype where name='$usrscl'");
																	$usval	= mysqli_fetch_row($usval)[0];
																	
																	//echo "<script>alert('msg lvl => $secval, usrlvl => $usval')</script>";
																	if($secval < $usval) {
																		echo "<script>alert('report only => ".$ccrow["eop"]."')</script>";
																	} else {
																		echo "<script>alert('cc => ".$ccrow["eop"]."')</script>";
																		$to		= mysqli_fetch_assoc(mysqli_query($con,"select email from office where name='$dest'"))["email"];
																		if(getNum($con,"sectype where value>$secval") < 1) {
																			echo "<script>alert('cc => office $to')</script>";
																			logthis($con,$user,"email sent",$to,$office);
																		}
																	}
																	logthis($con,$user,"email sent",$ccrow["eop"],$office);
																}
															}
														}
													}

												}
											}
										}

		//echo ($seclvldef == false && $fname != "" && $odate == "" && $source == "" && $dest != "" && $from !="" && $bound != "" && $seclvl != "-1");
		//echo "$seclvldef - $fname - $odate - $source - $dest - $from - $bound - $seclvl";
		if($odate <= $date) {
		if($seclvldef != false && $fname != "" && $odate != "" && $source != "" && $dest != "" && $from !="" && $bound != "" && $seclvl != "-1") {
			$cal	= "GC"; $ecpass	= true;
			if(isset($_POST["cal"]))
				$cal	= sanitizeString($con,$_POST["cal"]);
			if($cal == "EC") {
				if(sizeof(explode("-",$odate)) == 3) $ecpass = true;
				else $ecpass = false;
			}
			if($ecpass) {
			if($type != "" && $type != NULL && $type != "-1") {
				if($type != $bound) {
					$code	= decode($type);
					$code	= "$bound/$code";
				} else $code	= "$bound";
				if(file_exists("archives/$bound/$office/$cfile")) {
					if(file_exists("archives/$code")) {
						$i = 0;
						$done = false;
						if(file_exists("archives/$code/$fname.pdf")) {
							while(!$done) {
								//echo "lopping...<br />";
								if(file_exists("archives/$code/$fname($i).pdf"))
									$i++;
								else $done = true;
							}
						}
						if(!$done) $i = "";
						else $i = "(".$i.")";
						//echo "here i = ".$i;
						if(isset($_POST["bound"])) {
							//if(mysqli_num_rows(mysqli_query($con,"select * from office where name='$dest' && name!='$office'")) > 0) {
								if(mysqli_num_rows(mysqli_query($con,"select * from printform where source='$source' && user='$user' && subject='$subj' && destin='$dest' && bound='$bound' && type='$type'")) < 1) {
									$ren = rename("archives/$bound/$office/$cfile","archives/$code/$fname$i.pdf") or die("could not rename file");
									if($ren) {
										$file = "archives/$code/$fname$i.pdf";
										
										mysqli_query($con,"update printform set name='$fname$i.pdf',number='$number',odate='$odate',cal='$cal',user='$user',subject='$subj',fromm='$from',destin='$dest',type='$type',descr='$descr',ctime='$date',status='notseen' where status='empty' && source='$source' && bound='$bound' && scantime='$sctime'");
										logthis($con,$user,"form uploaded","$fname$i.pdf","$office");
										if(isset($_POST["sendmail"])) {
											$val = sanitizeString($con,$_POST["sendmail"]);
											if($val == "on") {
												$usrsql	= mysqli_query($con,"select * from dtsmembers where dtsname='$dest' && emailsend='1'");
												$usrnum	= mysqli_num_rows($usrsql);
												for($i=0;$i<$usrnum;$i++) {
													$rows	= mysqli_fetch_assoc($usrsql);
													$usrscl	= mysqli_query($con,"select seclevel from pos where name='".$rows["position"]."' && office='$dest'");
													$usrscl	= mysqli_fetch_row($usrscl)[0];
													$usval	= mysqli_query($con,"select value from sectype where name='$usrscl'");
													$usval	= mysqli_fetch_row($usval)[0];
													$secval	= mysqli_query($con,"select value from sectype where name='$seclvl'");
													$secval	= mysqli_fetch_row($secval)[0];
													if($secval < $usval) continue;
													
													$to = $rows["eop"];
													$from	= $user;
													$subject= $subj;
													$message= $descr;
													echo "<script>alert('email => $to');</script>";
													//sendMail($to,$from,$subject,$message,$file);
												}
											}
										}
										msg("succ","Succesfull");
										//echo "<meta http-equiv='refresh' content='0;index.php'>";
										//loc("index.php");
									} else msg("err","could not upload file");
								} else msg("err","File is already in the database");
							//} else msg("err","cant upload to that destination");
						} else msg("err","inbound/outbound not set");
					} else msg("err","Invalid Type $bound/$code");
				} else msg("err","File to be uploaded doesnt exist.");
			} else msg("err","Input Letter Code");
			} else msg("err","Invalid Date");
		} else msg("err","Please Input All Fields");
		} else msg("err","Improper date! date cant be from the future!");
	}

	if(isset($_GET["apprv"])) {
		if(isset($_GET["drctr"])) {
			$drctr = sanitizeString($con,$_GET["drctr"]);
			if(isset($_GET["emop"])) {
				$emop = sanitizeString($con,$_GET["emop"]);
				$role = sanitizeString($con,$_GET["role"]);
				if($role == 1)
					$role = "Restricted";
				else
					$role = "Not Restricted";
				mysqli_query($con,"update dtsmembers set status='approved',approver='$user',role='$role' where dtsname='$drctr' && eop='$emop'") or die("Approval Failed");
				logthis($con,$user,"account approved",$emop,$office);
				echo "<meta http-equiv='refresh' content='0;url=index.php'>";
			}
		}
	}
	$acstatus = "enabled";
	if(getNum($con,"office where name='$office' && status='enabled'") < 1)
		$acstatus = "disabled";

	$usstatus = "enabled";
	if(getNum($con,"users where eop='$user' && status='enabled'") < 1)
		$usstatus = "disabled";
	
echo '
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width; initial-scale=1.0">

	<!-- CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/custom.css">
	<link rel="stylesheet" href="font-awesome-5/css/font-awesome-all.min.css">
	<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/base.css">
	<link rel="stylesheet" href="css/style.css">
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
	<header>
		<nav class="nav navbar-inverse bg-aastu">
		  <div class="container-fluid bd-btm">
				<div class="row">
					<div class="right-side" >
					<div style="margin: 30px 10px 0 0;vertical-align: top;display: inline-block;">
					<a href="#amh" style="font-weight: bold;color: #fff;" onclick="langTrans(1)">Amh</a> | <a href="" style="font-weight: bold;color: #fff;" onclick="langTrans(0)">Eng</a>
					</div>
						<div class="img-profile" style="display: inline-block;">
							<span style="display: inline-block; border-radius: 100px;overflow: hidden; cursor: pointer;">
								<img src="img/profile.png" onclick="hs(event,\'settnav\')" style="width: 40px; height:40px;" alt="profile">
							</span>
							<div>
								<div class="card bg-light hatf" id="settnav" style="min-width: 200px;">
									<div class="card-body">
										'.$office.'
										<label class="hidden-bd"></label><br>
										<i class="fa fa-envelope"></i> Email <br /> '.$user.'
										<label class="hidden-bd" style="font-weight: 600; font-size: 18px;"><lng>Settings</lng></label>
											<a href="" class="fuser links"><i class="fa fa-user"></i> &nbsp; <lng>Change Email</lng></a> <br>
											<a href="index.php?espswdupd" class="links"><i class="fa fa-lock"></i> &nbsp; <lng>Change Password</lng></a> <br>
											<a href="index.php?addteam" class="links"><i class="fa fa-users"></i> &nbsp; Create Team</a><hr>
											<a href="logout.php" class="links"><i class="fa fa-door-open"></i>Logout</a><br>
									</div>
								</div>
							</div>
						</div>
					</div>								
					<div class="navbar-header">
						<a class="navbar-brand brStyle brnd-clr" href="index.php">EasyScan</a>
					</div>
					<form action="search.php" method="get">
						<div class="row no-gutters">
							<div class="col">
								<input class="srch border-secondary border-right-0 rounded-0" name="q" type="text" placeholder="Search">
							</div>
							<div class="srch-btn">
								<input type="submit" value="Search" class="btn btn-outline-warning border-left-0 rounded-0 rounded-right">
								<!-- i class="fa fa-search"></i -->
							</div>
						</div>
					</form>
					<!-- Button trigger modal -->
					<div class="filter">
						<button class="btn btn-warning rounded-5 btn-sm" data-toggle="modal" data-target="#filterModal" onclick="hs(event,\'modall\')">Filter</button>
					</div>
					<!--Modal -->
					';
					echo "<div class='modall hatf' id='modall'>";
					echo '
						<div class="filterModal">
							<h5 class="flt lft">Filter by</h5>
							<button class="btn btn-danger btn-sm cancel" onclick="hs(event,\'modall\')">Close</button> <br/>
							<form action="search.php" method="get">
								<input type="text" class="filter-option" name="q" class="text-center keyword" placeholder="keyword"> <br/>
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
					if($loggedin) {
						echo '
					<div class="setting">
						<a href="logout.php" class="setting-txt"></a>
					</div>
					';
					}
					echo '
				</div>
			</div> 
		</nav>
	</header>

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-xs-8 mr-left" style="margin-bottom: 50px;">
				<ul class="dropdown mr-top">
				<li>
				<form action="index.php" method="get">
				<ul class="dropdown">
					<li><button name="scannedin" value="'.$office.'" class="btn btn-warning bd-rad bt mr-top">upload</button>
						<ul>
							<table>
								<td>
									<li>
										<input type="hidden" name="upload">
										<input type="hidden" name="eop" value="'.$user.'">
										<input type="hidden" name="dirct" value="'.$office.'">
										<select name="bound" class="inp-sty" style="width: auto;">
											<option value="-1">Choose Letter Type</option>
	';
	$pendnum=0;
	$array = [];
	$typesql	= mysqli_query($con,"select * from typecat where status='enabled'");
	$typenum	= mysqli_num_rows($typesql);
	$pendnum	= getNum($con,"printform where status='empty'");
	$inboxnum	= mysqli_num_rows(mysqli_query($con,"select * from printform where destin='$office' && status!='viewed'"));
	if(mysqli_num_rows(mysqli_query($con,"select * from dtsmembers where eop='$user' && status='approved' && dtsname='$office'")) > 0) {
		$val = "Restricted";
		if(!denied($con,$user) && $acstatus == "enabled" && $usstatus == "enabled") {
			$typesql	= mysqli_query($con,"select * from typecat where status='enabled'");
			$typenum	= mysqli_num_rows($typesql);
			for($i=0;$i<$typenum;$i++) {
				$trow	= mysqli_fetch_assoc($typesql);
				echo "<option>";
				echo $trow['name'];
				echo "</option>";
			}
		}
	}
	echo '
										</select>
									</li>
									<li><input type="radio" name="uplchoice" value="scan" id="upl1" checked> <label for="upl1">scan</label></li>
									<li><input type="radio" name="uplchoice" value="attach" id="upl2"> <label for="upl2">attach</label></li>
									<li><input type="radio" name="uplchoice" value="link" id="upl3"> <label for="upl3">link</label></li>
									<li><input type="submit" value="Proceed" class="inp-sty" style="width: 100%;"></li>
								</td>
							</table>
						</ul>
					</li>
				</ul>
				</form>
	';
	echo '
		</li>
				<a href="index.php?pending"><button class="btn btn-warning bd-rad bt mr-top">Pending Files <span class="badge badge-pill badge-light">'.$pendnum.'</span></button></a>
	';
	if(($reqnum = getNum($con,"dtsmembers where status='notapproved'")) > 0) {
		echo "<a href='index.php?acrequest'><button class='btn btn-warning bx-rad bt mr-top'>Request <span class='badge badge-pill badge-light'>$reqnum</span></button></a>";
	}
	echo '
				<a href="index.php?inbox"><button class="btn btn-warning bd-rad bt mr-top">Inbox <span class="badge badge-pill badge-light">'.$inboxnum.'</span> </button></a>
				<a href="index.php?outbox"><button class="btn btn-warning bd-rad bt mr-top">Outbox</button></a>
		</ul>
			</div>
		</div>
	</div>
';
?>