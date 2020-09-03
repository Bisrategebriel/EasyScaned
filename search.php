<?php
	include_once "header.php";

	if($acstatus == "enabled" && $usstatus == "enabled") {
	if(isset($_GET["q"])) {
		$time = "";
		$filter = "";
		$q = sanitizeString($con,$_GET["q"]);
		//$q = strtolower($q);
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
					if($destin != -1 && $destin != "")
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
		if(mysqli_num_rows(mysqli_query($con,"select * from office where name='$office' && parent='None'")) > 0);
		else $inthis .= " && (source like '%$office%' || destin like '%$office%')";

		if($filter == "time")
			$inthis .= " order by time desc'";

		
		$plim	= 12;
		$allnum	= mysqli_num_rows(mysqli_query($con,"select * from printform ".$inthis));
		if(isset($_GET["p"]))
			$p	= sanitizeString($con,$_GET["p"]);
		else $p	= 1;
		
		$offset	= ($plim)*($p-1);
		
		$sql	= mysqli_query($con,"select * from printform ".$inthis."limit $offset, $plim");
		$num	= mysqli_num_rows($sql);
		
		$page	= ($allnum/$plim);
		if($page > (int)$page) $page++;
		$page	= (int)$page;
		
		//echo "sql : $inthis <br />";

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
					if(getNum($con,"office where (name='".$rows['source']."' || name='".$rows['destin']."')") < 1)
						continue;

				dispForm($con,$rows,$q,$user);
				echo "<hr />";
			}
		} else echo "<span class='restr'>Restricted</span>";
		echo '
				</div>
			</div>
		';
			echo "<div class='pagecont'>";
			for($pg=1;$pg<=$page;$pg++) {
				echo "<a href='".$_SERVER['REQUEST_URI']."&p=$pg'>$pg</a>&nbsp;";
			}
			echo "</div>";
	}
	} else {
		echo "Account Disabled! Please Contact the admin";
	}
	include_once "footer.php";
?>