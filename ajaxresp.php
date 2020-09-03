<?php
	require_once "scon.php";
	if(isset($_POST["offpos"])) {
		$office = sanitizeString($con,$_POST["offpos"]);
		$sql = mysqli_query($con,"select * from pos where office='$office' && status='enabled'");
		$num = mysqli_num_rows($sql);
		for($i=0;$i<$num;$i++) {
			$row = mysqli_fetch_assoc($sql);
			if($i != 0) echo ",$#";
			echo $row["name"];
		}
	} else if(isset($_POST["getdata"])) {
		$data	= sanitizeString($con,$_POST["getdata"]);
		$val	= sanitizeString($con,$_POST["val"]);
		if(isset($_POST["exc"]))
			$exc	= sanitizeString($con,$_POST["exc"]);
		else $exc	= "";
		if($data == "email") {
			$sql	= mysqli_query($con,"select * from users where eop like '$val%' && eop!='$exc'");
			$num	= mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row = mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["eop"];
			}
		} else if($data == "ibfrom") {
			$sql	= mysqli_query($con,"select * from office where name like '$val%' && name != '$exc'");
			$num	= mysqli_num_rows($sql);
			$arr	= [""];
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["name"];
				array_push($arr,$row["name"]);
			}
			$sql	= mysqli_query($con,"select fromm from printform where fromm like '$val%' && fromm != '$exc'");
			$num	= mysqli_num_rows($sql);
			echo ",$#";
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_row($sql);
				if(!in_array($row[0],$arr) && strlen($row[0]) > 1) {
					if($i != 0) echo ",$#";
					echo $row[0];
				}
			}
		} else if($data == "ibfromm") {
			$sql	= mysqli_query($con,"select * from office where name!='$exc'");
			$num	= mysqli_num_rows($sql);
			$arr	= [""];
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["name"];
				array_push($arr,$row["name"]);
			}
			$sql	= mysqli_query($con,"select fromm from printform where fromm!='$exc'");
			$num	= mysqli_num_rows($sql);
			echo ",$#";
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_row($sql);
				if(!in_array($row[0],$arr) && strlen($row[0]) > 1) {
					if($i != 0) echo ",$#";
					echo $row[0];
				}
			}
		} else if($data == "office") {
			$sql	= mysqli_query($con,"select * from office where name like '$val%' && name!='$exc'");
			$num	= mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["name"];
			}
		} else if($data == "officee") {
			$sql	= mysqli_query($con,"select * from office && name!='$exc'");
			$num	= mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["name"];
			}
		} else if($data == "pos") {
			$sql	= mysqli_query($con,"select distinct(name) from pos where status='enabled' && name!='$exc'");
			$num	= mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["name"];
			}
		} else if($data == "poss") {
			$sql	= mysqli_query($con,"select distinct(name) from pos where name like '$val%' && status='enabled' && name!='$exc'");
			$num	= mysqli_num_rows($sql);
			for($i=0;$i<$num;$i++) {
				$row	= mysqli_fetch_assoc($sql);
				if($i != 0) echo ",$#";
				echo $row["name"];
			}
		} else if($data == " ") echo "";
		
	}
?>