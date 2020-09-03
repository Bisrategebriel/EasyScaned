<!Doctype html>
<html>
  <head>
	<?php
		require_once "header.php";
	?>
  </head>
  <body>
<?php
	if($loggedin) {
		if($acstatus == "enabled" && $usstatus == "enabled") {
		if(getNum($con,"dtsmembers where eop='$user' && status='approved' && dtsname='$office'") > 0) {
?>
    <!-- section  -->
	<div class="infdispcont">
	</div>
     <section>	
	<?php
		$usrsql	= mysqli_query($con,"select * from users where eop='$user'");
		$usrrow	= mysqli_fetch_assoc($usrsql);
		if(!isset($_GET["espswdupd"])) {
			$pwsql	= mysqli_query($con,"select * from passwords where id='".$usrrow["password"]."'");
			$pwrow	= mysqli_fetch_assoc($pwsql);
			if(substr($pwrow["pass"],0,5) == $pwrow["confcode"]) {
				loc("index.php?espswdupd&msg=npns");
			}
		}
		if(isset($_GET["addteam"]) && !denied($con,$user)) {
			if(denied($con,$user)) {
				echo "Restricted";
			}
	?>
			<div>
				<form action="" method="post">
					<ul>
						<li><input type="text" placeholder="office Name" name="dircname" class='inp-sty'></li>
						<li><input type="hidden" name="dircpar" value="<?php echo $office ?>" class='inp-sty'></li>
						<li><input type="text" placeholder="Location" name="dircloc" class='inp-sty'></li>
						<li><textarea placeholder='Description...' name="dircdesc" class='inp-sty'></textarea></li>
						<li><input type="submit" value="Create" name="newdircsbt" class='btn btn-primary'></li>
					</ul>
				</form>
			</div>
	<?php
			if(isset($_POST["newdircsbt"]) && !denied($con,$user)) {
				if(denied($con,$user)) {
					echo "Restricted";
				}
				$name	= sanitizeString($con,$_POST["dircname"]);
				$parent	= sanitizeString($con,$_POST["dircpar"]);
				$locat	= sanitizeString($con,$_POST["dircloc"]);
				$desc	= sanitizeString($con,$_POST["dircdesc"]);
				
				if($name != "" && $parent != "" && $locat != "") {
					if(mysqli_num_rows(mysqli_query($con,"select * from office where name='$name'")) < 1) {
						$sql = mysqli_query($con,"select * from office where name='$parent'");
						$num = mysqli_num_rows($sql);
						if($num > 0) {
							$rows = mysqli_fetch_row($sql);
							if($rows[2] != "None" || $rows[2] != ".") {
								$parent = "$rows[2]/$parent";
							}
						}
						mysqli_query($con,"insert into office values('','$name','$parent','$locat','$desc')") or die("failed to do that");
						msg("succ","Successful");
						logthis($con,$user,"new office added","$name","$office");
						//echo "<meta http-equiv='refresh' content='0'>";
					} else {
						echo "Error : office already exists";
					}
				}
			}

		} else if(isset($_GET["fileview"])) {
			$eop	= sanitizeString($con,$_GET["eop"]);
			$file	= sanitizeString($con,$_GET["fileview"]);
			echo "
				<div class='iframecont'>
					<iframe src=\"$file\">
					</iframe>
				</div>
			";
			logthis($con,$eop,"file viewed","$file","$office");
		} else if(isset($_GET["inbox"])) {
			$sql = mysqli_query($con,"select * from printform where destin='$office'");
			$num = mysqli_num_rows($sql);
			
			if($num < 1)
				echo "Nothing Found";
			else {
				echo "<div class='container'>";
				echo "	<div class='row'>";
				for($i=0;$i<$num;$i++) {
					$rows = mysqli_fetch_assoc($sql);
					if($rows['source'] != $office) {
						mysqli_query($con,"update printform set status='viewed' where id='".$rows['id']."'");
						logthis($con,$user,"inbox viewed",$rows['id'],"$office");
					}
					dispForm($con,$rows,null,$user);
				}
				echo "	</div>";
				echo "</div>";
			}
		} else if(isset($_GET["outbox"])) {
			$sql = mysqli_query($con,"select * from printform where source='$office' && status!='empty'");
			$num = mysqli_num_rows($sql);
			if($num < 1)
				echo "No Result Found";
			else {
				echo "<div class='container'>";
				echo "	<div class='row'>";
				for($i=0;$i<$num;$i++) {
					$rows = mysqli_fetch_assoc($sql);
					dispForm($con,$rows,null,$user);
				}
				echo "
						</div>
					</div>
				";
			}
		} else if(isset($_GET["pending"]) && !denied($con,$user)) {
			echo '
				<div class="row mr-left">
			';
			$arr = [];
			$printsql	= mysqli_query($con,"select * from printform where status='empty' order by id desc");
			$printnum	= mysqli_num_rows($printsql);
			$i=0;
			for($i=0;$i<$printnum;$i++) {
				$row	= mysqli_fetch_assoc($printsql);
				dispFileForm($con,$row["name"],$office,$row["bound"],$user);
			}

			if($i < 1)
				echo "No Result Found";
			echo '
				</div>
			';
		} else if(isset($_GET["acrequest"])) {
			$notaprvdsql = mysqli_query($con,"select * from dtsmembers where status='notapproved'");
			$num2 = mysqli_num_rows($notaprvdsql);
			if($num2 > 0) {
				echo "<div class='container'>";
				echo "<div class='row'>";
				for($i=0;$i<$num2;$i++) {
					$rows = mysqli_fetch_assoc($notaprvdsql);
					if($rows["dtsname"] != $office)
						if(getNum($con,"office where name='".$rows['dtsname']."' && parent like '%$office%'") < 1) continue;
					$name = mysqli_fetch_row(mysqli_query($con,"select name from users where eop='".$rows['eop']."'"))[0];
					echo '
					<div class="col-md-12 notification">
					  <h5 style="color: black">User '.$name.' wants an approval on '.$rows['dtsname'].' 
					  <span>
						<form action="index.php?acrequest" method="get" style="display: inline-block;">
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
				}
				echo "</div>";
				echo "</div>";
			}
		} else if(isset($_GET["espswdupd"]) && !denied($con,$user)) {
			if(isset($_POST["passupdsbt"]) && !denied($con,$user)) {
				$oldpass	= sanitizeString($con,$_POST["opass"]);
				$oldpass	= md5("@#.$".$oldpass."@#.$");
				$newpass	= sanitizeString($con,$_POST["npass"]);
				$newpass	= md5("@#.$".$newpass."@#.$");
				$confpass	= sanitizeString($con,$_POST["ncpass"]);
				$confpass	= md5("@#.$".$confpass."@#.$");
				
				$sql	= mysqli_query($con,"select * from users where eop='$user'");
				$row	= mysqli_fetch_assoc($sql);
				$rpass	= mysqli_fetch_assoc(mysqli_query($con,"select * from passwords where id='".$row['password']."' && status='enabled'"));
				
				if($oldpass != "" && $newpass != "" && $confpass != "") {
					if($oldpass == $rpass['pass']) {
						if($newpass == $confpass) {
							$good = mysqli_query($con,"update passwords set pass='$newpass',confcode='0' where id='".$row['password']."'");
							if($good) {
								msg("succ","Password changed ");
								logthis($con,$user,"user password changed","$user","$office");
								$_SESSION["espass"] = $newpass;
							} else {
								msg("err","Something went wrong! please contact admins");
								logthis($con,$user,"0 error : Failed changeing user password","$user","$office");
							}
						} else {
							echo msg("err","Password doesnt match");
						}
					} else msg("err","Password Incorrect");
				} else msg("err","All fields must be filled");
			}
			$msg = "";
			if(isset($_GET["msg"])) {
				$val	= sanitizeString($con,$_GET["msg"]);
				if($val == "npns") {
					$msg = "Change your password to be more secure.";
				}
			}
			echo "
			<div class='container'>
				<h4>$msg</h4>
				<div class='row'>
					<form action='' method='post' autocomplete=off>
						<li><input type='text' placeholder='Old Password' name='opass' value=''>
						<li><input type='password' placeholder='New Password' name='npass' value=''>
						<li><input type='password' placeholder='Confirm Password' name='ncpass'>
						<li><input type='submit' value='Change' name='passupdsbt'>
					</form>
				</div>
			</div>
			";
		} else if(isset($_GET["upload"])) {
			$uplc	= sanitizeString($con,$_GET["uplchoice"]);
			$dirct	= $office;
			$eop	= sanitizeString($con,$_GET["eop"]);
			$bound	= sanitizeString($con,$_GET["bound"]);
			if($uplc != "" && $dirct != "" && $eop != "") {
				if(getNum($con,"users where eop='$eop'") > 0) {
				if(getNum($con,"typecat where name='$bound'") > 0) {
					if($uplc == "scan") {
						echo "
							Processing...
							<script>
								scanasPdf('$dirct','$bound','$eop');
							</script>
						";
					} else if($uplc == "attach") {
						if(isset($_POST["ltrattachedsbt"])) {
							$bound	= sanitizeString($con,$_POST["bound"]);
							$type	= sanitizeString($con,$_POST["type"]);
							$dirct	= $office;
							$sctime	= getCurTime();
							if($office != "" && (getNum($con,"typecat where name='$bound' && status='enabled'") > 0)) {
								if(isset($_FILES["ltrfile"]["name"])) {
									$file = $_FILES["ltrfile"]["name"];
									if(strstr($file,".pdf")) {
										if(!file_exists("archives/$bound/$office"))
											mkdir("archives/$bound/$office");
										$upldd = move_uploaded_file($_FILES["ltrfile"]["tmp_name"],"archives/$bound/$office/AASTU-archive-$file");
										if($upldd) {
											if(getNum($con,"printform where name='AASTU-archive-$file' && source='$office' && bound='$bound' && status='empty'") < 1) {
												$dbupd	= mysqli_query($con,"insert into printform(name,source,user,bound,scantime,status,upltype) values('AASTU-archive-".$file."','$office','$user','$bound','$sctime','empty','$type')");
												if($dbupd) {
													logthis($con,$user,"file attached",$file,$office);
													dispFileForm($con,"AASTU-archive-$file",$office,$bound,$user);
												} else {
													msg("err","Something went wrong! Please contact admins 0x261");
													logthis($con,$user,"0 error","inserting file to table -printform");
												}
											} else msg("err","File already Posted! Please upload the file from pending");
										} else msg("err","Failed on uploading file!");
									} else msg("err","Only pdf is allowed");
								} else msg("err","Did not find any file! Please try again");
							} else msg("err","Please try again");
						}
						echo "
							<div class='container'>
							<div class='row'>
							<h3>$bound</h3>
							<form action='' method='post' style='width: 100%;' enctype='multipart/form-data'>
								<input type='hidden' name='bound' value='$bound'>
								<input type='hidden' name='type' value='$uplc'>
								<input type='file' name='ltrfile' class='inp-sty'>
								<input type='submit' value='Proceed' name='ltrattachedsbt' class='inp-sty'>
							</form>
							</div>
							</div>
						";
					} else if($uplc == "link") {
						if(isset($_POST["ltrlinkedsbt"])) {
							$bound	= sanitizeString($con,$_POST["bound"]);
							$type	= sanitizeString($con,$_POST["type"]);
							$file	= sanitizeString($con,$_POST["ltrfile"]);
							$dirct	= $office;
							$sctime	= getCurTime();
							$filename	= basename($file);
							if($office != "" && $file != "" && (getNum($con,"typecat where name='$bound' && status='enabled'") > 0)) {
								if(strstr($file,".pdf")) {
									if(!file_exists("archives/$bound/$office"))
										mkdir("archives/$bound/$office");
									$uplfile	= fopen($file,"r");
									if($uplfile) {
										$upldd		= file_put_contents("archives/$bound/$office/AASTU-archive-$filename",$uplfile);
										if($upldd) {
											if(getNum($con,"printform where name='AASTU-archive-$filename' && source='$office' && bound='$bound' && status='empty'") < 1) {
												$dbupd	= mysqli_query($con,"insert into printform(name,source,user,bound,scantime,status,upltype) values('AASTU-archive-".$filename."','$office','$user','$bound','$sctime','empty','$type')");
												if($dbupd) {
													logthis($con,$user,"file linked",$file,$office);
													dispFileForm($con,"AASTU-archive-$filename",$office,$bound,$user);
												} else {
													msg("err","Something went wrong! Please contact admins 0x300");
													logthis($con,$user,"0 error","Failed inserting file to table -printform",$office);
												}
											} else msg("err","File pending! Please upload it from pending");
										} else msg("err","Failed on uploading file! Please report this error");
									} else msg("err","Could'nt Download from that link");
								} else msg("err","Only pdf allowed");
							} else msg("err","Please try again");
						}
						echo "
							<div class='container'>
							<div class='row'>
							<h3>$bound</h3>
							<form action='' method='post' style='width: 100%;'>
								<input type='hidden' name='bound' value='$bound'>
								<input type='hidden' name='type' value='$uplc'>
								<input type='text' name='ltrfile' placeholder='Link' class='inp-sty'>
								<input type='submit' name='ltrlinkedsbt' value='Proceed' class='inp-sty'>
							</form>
							</div>
							</div>
						";
					}
				} else msg("err","Letter Type Required");
				}
			} else msg("err","Letter Type Required");
		} else {
		if(denied($con,$user)) {
			echo "
				<span class='restr'>
					Restricted
				</span>
			";
		}
	?>

       <div class="right-container">
         <div class="container">
          <div class="row">
			<div class="container">
				<div class="row">
					<div class="col-md-12 col-xs-12 text-center">
						<div style='text-align: center;'>
							<form action='index.php' method='post'>
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
									
									$inthis = "";
									if($srch != "")
										$inthis .= " && (name like '%$srch%' || source like '%$srch%' || user like '%$srch%' || subject like '%$srch%' || destin like '%$srch%' || bound like '%$srch%' || type like '%%$srch%' || descr like '%$srch%' || ctime like '%$srch%' || scantime like '%$srch%' || fromm like '%$srch%' || number like '%$srch%' || odate like '%$srch%' || cal like '%$srch%') ";
									else $inthis = "";

									$sqlval	= "select * from printform ";
									
									$sqlval .= "where destin='$office' || source='$office'";
									$sqlval .= " && status!='empty' $inthis";
									
									$sqlval	.= " order by id limit 12";
									//echo "$sqlval";
									$sql	= mysqli_query($con,$sqlval);
									$num	= mysqli_num_rows($sql);
									if($num < 1)
										echo "Nothing Found";
									else {
									echo '
										<thead>
											<th colspan="11">Recent</th>
										</thead>
										<tr>
											<td class="text-center">No</td>
											<td class="text-center">FileName</td>
											<td class="text-center">Subject</td>
											<td class="text-center">From</td>
											<td class="text-center">To</td>
											<td class="text-center">Type</td>
											<td class="text-center">Catagory</td>
											<td class="text-center">Date</td>
											<td class="text-center">ScanTime</td>
											<td class="text-center">Action</td>
										</tr>
									';
										for($i=0;$i<$num;$i++) {
											$rows = mysqli_fetch_assoc($sql);
											
											if(getNum($con,"office where name='$office' && parent='None'") > 0)
												if(getNum($con,"office where (name='".$rows['source']."' || name='".$rows['destin']."')") < 1)
													continue;
											

											if(getNum($con,"lettercat where type='".$rows["bound"]."'") > 0)
												$code = decode($rows["type"]);
											else $code = ".";
											echo "
											<tr class='text-center'>
												<td>".$rows["number"]."</td>
												<td><a target='_blank' href='index.php?eop=".$user."&fileview="."archives/".$rows["bound"]."/".$code."/".$rows["name"]."'>".$rows["name"]."</a></td>
												<td>".$rows["subject"]."</td>
												<td>".$rows["source"]."</td>
												<td>".$rows["destin"]."</td>
												<td>".$rows["bound"]."</td>
												<td>".$rows["type"]."</td>
												<td>".$rows["odate"]."</td>
												<td>".$rows["scantime"]."</td>
												<td><a target='_blank' href='index.php?eop=".$user."&fileview="."archives/".$rows["bound"]."/".$code."/".$rows["name"]."' class='btn btn-info'>View</a></td>
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

   ?>
     </section>
    <!-- end of section -->
<?php
		} else {
			echo "Account not approved! <a href='logout.php'>Logout</a>";
		}
		} else {
			echo "Account Disabled! Please Contact the Admin";
		}
	} else {
		echo "<meta http-equiv='refresh' content='0;losi.php'>";
	}
	include_once "footer.php";
?>
</body>
</html>