<?php
	$dbname = "easyscan";
	$dbuser = "root";
	$dbpass = "";
	$dbhost = "localhost";
	$sitemail	= "abebey348@gmail.com";

	$date = getCurTime();
	$date = explode(' ',$date);
	$time = $date[1];
	$time = explode(":",$time);
	$time[0] += 3;
	$date = $date[0];
	$time = "$time[0]:$time[1]";
	
	$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname) or die("failed to connect to databse");
		
	createTable($con,"sectype",
					 "id int unsigned primary key auto_increment,
					  name varchar(16) not null,
					  value int unsigned not null,
					  status varchar(10) not null");
	createTable($con,"lettercat",
					 "code varchar(16) primary key,
					  name varchar(4096),
					  type varchar(100),
					  status varchar(10)");
	createTable($con,"typecat",
					 "name varchar(100) primary key,
					  status varchar(10)");
	createTable($con,"printform",
					 "id int unsigned primary key auto_increment,
					  name varchar(64),
					  source varchar(32),
					  user varchar(32),
					  subject varchar(32),
					  destin varchar(32),
					  bound varchar(8),
					  type varchar(32),
					  descr varchar(4096),
					  ctime varchar(25),
					  scantime varchar(25),
					  worker varchar(32),
					  status varchar(16),
					  fromm varchar(32),
					  number varchar(16),
					  odate varchar(10),
					  cal varchar(3),
					  upltype varchar(16)");
	
	createTable($con,"office",
					 "id int unsigned primary key auto_increment,
					  name varchar(32),
					  parent varchar(32),
					  location varchar(32),
					  info varchar(1000),
					  status varchar(16),
					  code varchar(16),
					  poslim int unsigned,
					  email varchar(255),
					  ctime varchar(25)");
	
	createTable($con,"users",
					 "id int unsigned auto_increment,
					  name varchar(32),
					  eop varchar(255),
					  password int unsigned,
					  ctime varchar(25),
					  status varchar(16),
					  usrid varchar(16),
					  primary key(id,eop,password)");
					  
	createTable($con,"passwords",
					 "id int unsigned auto_increment primary key,
					  pass varchar(32),
					  direct varchar(32),
					  confcode varchar(5),
					  status varchar(16)");
	
	createTable($con,"dtsmembers",
					 "dtsname varchar(32),
					  eop varchar(32),
					  status varchar(32),
					  approver varchar(64),
					  role varchar(16),
					  position varchar(64),
					  emailsend int unsigned");
	
	createTable($con,"cntrlpnl",
					 "id int unsigned,
					  name varchar(32),
					  eop varchar(32),
					  password varchar(32),
					  status varchar(16)");
					  
	createTable($con,"pos",
					 "id int unsigned primary key auto_increment,
					  name varchar(32),
					  office varchar(32),
					  seclevel varchar(32),
					  status varchar(16)");
	
	createTable($con,"emsuf",
					 "name varchar(64),
					  status varchar(16)");
	
	createTable($con,"minutes",
					 "id int unsigned primary key auto_increment,
					  code varchar(16),
					  loc varchar(32),
					  type varchar(32),
					  name varchar(32),
					  ctime	varchar(25)");
	
	createTable($con,"log",
					 "id int unsigned primary key auto_increment,
					  user varchar(64),
					  name varchar(32),
					  action varchar(200),
					  target varchar(200),
					  account varchar(32),
					  date varchar(25)");
	
	function createTable($con,$name,$val) {
		$delTables = false;
		//$delTables = mysqli_query($con,"drop table $name");
		$created = !$delTables && mysqli_query($con,"create table if not exists $name($val)");
		if(!$created) {
			echo "Table $name failed to be created.";
		} else {
			//echo "Table $name exists <br />";
		}
	}
	function getCurTime($time="default") {
		if($time == "default")
			$time = time();
		return gmDate("Y-m-d h:i:s A",$time);
	}
	function logthis($con,$user,$action,$target,$account) {
		$date	= getCurTime();
		$date 	= explode(' ',$date);
		$time	= $date[1];
		$time	= explode(":",$time);
		$time[0]	+= 3;
		$date	= $date[0];
		$time	= "$time[0]:$time[1]";
		$sql	= mysqli_query($con,"select * from cntrlpnl where eop='$user'");
		$num	= mysqli_num_rows($sql);
		if($num > 0)
			$name	= mysqli_fetch_assoc($sql)["name"];
		else {
			$sql	= mysqli_query($con,"select * from users where eop='$user'");
			$num	= mysqli_num_rows($sql);
			if($num > 0)
				$name	= mysqli_fetch_assoc($sql)["name"];
			else $name	= "unknown";
		}
		mysqli_query($con,"insert into log(name,user,action,target,account,date,time) values('$name','$user','$action','$target','$account','$date','$time')");
	}
	function sanitizeString($con,$val) {
		$val = htmlentities($val);
		$val = stripslashes($val);
		$val = strip_tags($val);
		
		return mysqli_real_escape_string($con,$val);
	}
	function dispForm($con,$rows,$q="",$eop) {
		if(getNum($con,"lettercat where type='".$rows["bound"]."'") > 0)
			$code = decode($rows["type"]);
		else $code = ".";
		
		echo '
			<div class="col-md-4 col-xs-4 mr-cards">
				<div class="card card-colr">
					<div class="card-body">
						<form action="" method="POST">
		';
		echo '<a target="_blank" href="index.php?eop='.$eop.'&fileview=archives/'.$rows['bound'].'/'.$code.'/'.$rows['name'].'" class="btn btn-primary btn-sm btn-sm-align">view</a>';
		echo "<label class='in-files-txt'>Letter Number</label>";
		if($q != "" && strstr(strtolower($rows['number']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['number']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['number']."</h5>";
		if($q != "" && strstr(strtolower($rows['name']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['name']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['name']."</h5>";
		echo "<label class='in-files-txt'>Subject</label>";
		if($q != "" && strstr(strtolower($rows['subject']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['subject']."</span></span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['subject']."</h5>";
		echo "<label class='in-files-txt'>From</label>";
		if($q != "" && strstr(strtolower($rows['source']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['source']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['source']."</h5>";
		echo "<label class='in-files-txt'>To</label>";
		if($q != "" && strstr(strtolower($rows['destin']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['destin']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['destin']."</h5>";
		echo "<label class='in-files-txt'>Type</label>";
		if($q != "" && strstr(strtolower($rows['bound']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['bound']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['bound']."</h5>";
		echo "<label class='in-files-txt'>Catagory</label>";
		if($q != "" && strstr(strtolower($rows['type']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['type']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option'>".$rows['type']."</h5>";
		echo "<label class='in-files-txt'>Description</label>";
		if($q != "" && strstr(strtolower($rows['descr']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>". $rows['descr']."</span></h5>";
		else
			echo "<textarea name='description' disabled class='txt-area clr' cols='30' rows='2'>".$rows['descr']."</textarea>";
		// archives/".$rows["bound"]."/".$rows["destin"]."/".$rows["name"]
		echo "<label class='in-files-txt' style='display: inline-block;'>Posted By</label>";
		if($q != "" && strstr(strtolower($rows['user']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['user']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option' style='display: inline-block;'>".$rows['user']."</h5>";
		
		echo "<br />";
		echo "<label class='in-files-txt' style='display: inline-block;'>Scanned Time</label>";
		if($q != "" && strstr(strtolower($rows['scantime']),$q) != NULL)
			echo "&nbsp; <h5 class='option'><span class='found'>".$rows['scantime']."</span></h5>";
		else
			echo "&nbsp; <h5 class='option' style='display: inline-block;'>".$rows['scantime']."</h5>";
		echo "
						</from>
					</div>
				</div>
			</div>";
	}
	function defDate($frmt,$dt) {
		$frmt	= explode("-",$frmt);
		$dfdt	= explode("-",$dt);
		$d = $m = $y = "";
		for($i=0;$i<sizeof($frmt);$i++) {
			if($frmt[$i] == "d") {
				$d	= $dfdt[$i];
			} else if($frmt[$i] == "m") {
				$m	= $dfdt[$i];
			} else if($frmt[$i] == "y") {
				$y	= $dfdt[$i];
			}
		}
		return "$d-$m-$y";
	}
	function decode($val) {
		if(strstr($val," ") && strstr($val,"(") && strstr($val,")")) {
			$code = explode(" ",$val)[0];
			$code = explode("(",$code)[1];
			$code = explode(")",$code)[0];
			return $code;
		} else return $val;
	}
	function getNum($con,$sqlval) {
		return mysqli_num_rows(mysqli_query($con,"select * from $sqlval"));
	}
	function loc($link) {
		echo "<meta http-equiv='refresh' content='0;url=$link'>";
	}
	function denied($con,$eop) {
		$n = getNum($con,"dtsmembers where eop='$eop' && role='Restricted'");
		if($n > 0) return true;
		else return false;
	}
	function sendMail($to,$from,$subject,$message,$file="") {
		global $con,$user,$office;
		
		if(file_exists('PHPMailer/src')) {
			require_once 'PHPMailer/src/PHPMailer.php';
			require_once 'PHPMailer/src/SMTP.php';
			require_once 'PHPMailer/src/Exception.php';
			$mail = new PHPMailer();
			$mail->AddAddress($to);
			$mail->From     = 'aastu@easyscan.com';
			$mail->FromName = 'EasyScan';
			$mail->Subject  = $subject;
			$MESSAGE_BODY   = "From: $from "."\r\n";
			$MESSAGE_BODY  .= "Message : $message "."\r\n";
			$mail->Body     = $MESSAGE_BODY;
			
			if($file != "")
				$mail->AddStringAttachment(file_get_contents($file),basename($file));
			
			if ($mail->Send()) {
				logthis($con,$user,"Email sent","$from -> $to",$office);
				return 1;
			}
			else {
				logthis($con,$user,"Error sending email","$from -> $to",$office);
				return 0;
			}
			
		} else {
			$file	= fopen("email.txt","a");
			$data	= "To: $to\n";
			$data	.= "From: $from\n";
			$data	.= "Subject: $subject\n";
			$data	.= "Message: $message\n";
			$data	.= "File: $file\n";
			$data	.= "-------------------------------\n";
			if (fwrite($file,$data)) {
				logthis($con,$user,"Email sent","$from -> $to",$office);
				return 1;
			}
			else {
				logthis($con,$user,"Error sending email","$from -> $to",$office);
				return 0;
			}
		}
	}
	function msg($type,$val) {
		if($type != "err" && $type != "succ") $type = "";
		echo '
			<div class="card msg" id="msg" style="display: block;">
				<div class="card-header">
		';
				if($type == "err") echo "<span><i class='fa fa-exclamation-triangle'></i></span> Error";
				else if($type == "succ") echo "<span><i class='fa fa-check-circle'></i></span>";
		echo '
					<span class="close" onclick="hs(\'msg\')">
						&cross;
					</span>
				</div>
				<div class="card-body '.$type.'">
					'.$val.'
				</div>
			</div>
		';
	}
	function jslog($msg) {
		global $con;
		$msg = sanitizeString($con,$msg);
		echo "<script>console.log('$msg')</script>";
	}
	function getSqlResult($sql,$start="",$end="",$func=null) {
		global $con;
		
		$result = "";
			
		$sql	= mysqli_query($con,$sql);
		$num	= mysqli_num_rows($sql);
		for($i=0;$i<$num;$i++) {
			$row	= mysqli_fetch_row($sql);
			$val	= $row[0];
			if($func != null)
				$val = $func("$val");
			$result	.= "$start".$val."$end";
		}
		return $result;
	}
	function inDateRange($from,$mydate,$to) {
		if($from == "")
			$from	= getCurTime(0);
		if($to == "")
			$to		= getCurTime();
		
		//echo "$from<br />";
		$fdate	= explode("-",$from);
		$fyear	= $fdate[0];
		$fmonth	= $fdate[1];
		$fday	= $fdate[2];
		//echo "<br />From Year : &nbsp;&nbsp;&nbsp;$fday-$fmonth-$fyear <br /><br />";
		
		//echo "$to<br />";
		$tdate	= explode("-",$to);
		$tyear	= $tdate[0];
		$tmonth	= (int)$tdate[1];
		$tday	= $tdate[2];
		//echo "<br />To Year : &nbsp;&nbsp;&nbsp;$tday-$tmonth-$tyear <br /><br />";
		
		$mydate	= explode("-",$mydate);
		$myyear	= $mydate[2];
		$mymonth	= (int)$mydate[1];
		$myday	= $mydate[0];
		//echo "<br />My Year : &nbsp;&nbsp;&nbsp;$myday-$mymonth-$myyear <br /><br />";
		
		$big = $small = false;
		if($fyear == $myyear) {
			if($fmonth == $mymonth) {
				if($fday <= $myday) $big = true;
			} else if($fmonth < $mymonth) {
				$big = true;
			}
		} else if($fyear < $myyear) $big = true;
		
		if($big) {
			if($myyear == $tyear) {
				if($mymonth == $tmonth) {
					if($myday <= $tday) $small = true;
				} else if($mymonth <= $tmonth) $small = true;
			} else if($myyear <= $tyear) $small = true;
		}

		if($small && $big) return 1;

		return 0;
	}
	function dispFileForm($con,$a,$office,$bound,$user) {
		$date = getCurTime();
		$date = explode(' ',$date);
		$time = $date[1];
		$time = explode(":",$time);
		$time[0] += 3;
		$date = $date[0];
		$time = "$time[0]:$time[1]";
		
		$frmsql	= mysqli_query($con,"select * from printform where name='$a' && source='$office' && bound='$bound' && status='empty' order by id desc");
		if(mysqli_num_rows($frmsql) > 0) {

		$frmrow	= mysqli_fetch_assoc($frmsql);

		if(strstr($a,".pdf")) {
			echo '
					<div class="col-md-4" id="'.$a.$frmrow["scantime"].'">
						<div class="card bg-light sp">
							<div class="card-btm"><h5 class="card-title text-center pd-title cust">'.$bound.'</h5></div>
							<div class="card-body">
								<form action="" method="post" class="form-space" autocomplete="off">
				';
				if($frmrow["upltype"] != "scan")
					echo 'Uploaded At : '.$frmrow["scantime"].'<br />';
				else echo 'Scanned At : <span class="inline">'.$frmrow["scantime"].'</span>';
			echo '
									<a target="_blank" href="index.php?eop='.$user.'&fileview=archives/'.$bound.'/'.$office.'/'.$a.'">View</a>
									<br />
									<h6>FileName</h6>
									<input type="text" class="inp-sty" name="filename" placeholder="FileName" value="AASTU-'.$office.'-'.$date.'">
									<input type="text" class="inp-sty" name="subject" placeholder="Subject" title="eg. Request For Budget Allocation">
									<label for="ecal'.$a.$frmrow["scantime"].'"><input type="radio" id="ecal'.$a.$frmrow["scantime"].'" name="cal" value="EC" onchange="changeType(event,\'EC\',\'text\',\'dt'.$a.$frmrow["scantime"].'\')"> EC</label>&nbsp;
									<label for="gcal'.$a.$frmrow["scantime"].'"><input type="radio" id="gcal'.$a.$frmrow["scantime"].'" name="cal" value="GC" checked onchange="changeType(event,\'GC\',\'date\',\'dt'.$a.$frmrow["scantime"].'\')"> GC</label>
									<input type="date" class="inp-sty" name="date" id="dt'.$a.$frmrow["scantime"].'" placeholder="Date (mm-dd-yyyy)" >
									<input type="text" class="inp-sty" name="number" placeholder="Letter Number" >
									<input type="hidden" name="sctime" value="'.$frmrow["scantime"].'">
									<input type="hidden" name="bound" value="'.$bound.'">
									<input type="hidden" name="cfilename" value="'.$a.'">
									
									<input type="text" class="inp-sty" id="to'.$a.$frmrow["scantime"].'" name="dest" placeholder="To (select or write name/office)" onkeyup="myajax.fetchdata(event,\'ibfrom\',\'srch'.$a.$frmrow["scantime"].'\',false,\''.$office.'\')" onclick="myajax.fetchdata(event,\'ibfromm\',\'srch'.$a.$frmrow["scantime"].'\',false,\''.$office.'\')">
									<div id="srch'.$a.$frmrow["scantime"].'" class="srchdisp"></div>
			';
			echo '
								<input type="text" class="inp-sty" name="from" id="from'.$a.$frmrow["scantime"].'" placeholder="From (select or write name/office)" onclick="myajax.fetchdata(event,\'ibfromm\',\'fsrch'.$office.$a.'\')" onkeyup="myajax.fetchdata(event,\'ibfrom\',\'fsrch'.$office.$a.'\')" >
								<div id="fsrch'.$office.$a.'" class="srchdisp"></div>
								<textarea class="inp-sty desc-sty hatf" name="descr" placeholder="Short Summary of the letter"></textarea>
				';
								$sql = mysqli_query($con,"select * from typecat where status='enabled' && name='$bound'");
								$num = mysqli_num_rows($sql);
								for($i=0;$i<$num;$i++) {
									$rows = mysqli_fetch_assoc($sql);
									$sql2	= mysqli_query($con,"select * from lettercat where status='enabled' && type='".$rows['name']."'");
									$num2	= mysqli_num_rows($sql2);
									if($num2 > 0) {
									echo '
										<div>
											<select name="'.$rows['name'].'type" class="inp-sty">
												<option value="-1">Letter Code</option>
									';
												for($j=0;$j<$num2;$j++) {
													$rows2 = mysqli_fetch_assoc($sql2);
													echo "<option>(".$rows2['code'].") ".$rows2['name']."</option>";
												}
									echo'
											</select>
										</div>
									';
									}
								}
								$secsql = mysqli_query($con,"select * from sectype where status='enabled' order by value");
								$secnum = mysqli_num_rows($secsql);
								if($bound == "Outbound" || $bound == "outbound" || $bound == "inbound" || $bound == "Inbound") {
									echo '
									<hr />
									<select name="seclvll" class="inp-sty">
										<option value="-1">Security Type</option>
									';
									for($i=0;$i<$secnum;$i++) {
										$secrow	= mysqli_fetch_assoc($secsql);
										echo "<option>".$secrow['name']."</option>";
									}
									echo '
									</select>
									';
								}
								if($bound == "Outbound" || $bound == "outbound") {
									echo "<input type='text' name='cc' class='inp-sty' placeholder='CC (select office or write reciever email)' id='ccinp$a".$frmrow["scantime"]."' onclick='myajax.fetchdata(event,\"ibfromm\",\"cc".$a.$frmrow["scantime"]."\",\"true\")'>";
									echo '<div id="cc'.$a.$frmrow["scantime"].'" class="srchdisp"></div>';
								}
			echo '
									<label><input type="checkbox" name="sendmail" checked> Send Email</label>
									<br />
									<input type="submit" name="fileinfosbt" class="btn btn-primary btn-align btn-sm" value="Upload">
								</form>
							</div>
						</div>
					</div>
			';
		}

		}
	}
?>