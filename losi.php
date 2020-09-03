<!DOCTYPE html>
<html>
    <head>
        <title>EasyScan</title>
		<?php
			include_once "scon.php";
			$asdf = "asdf";

			if(isset($_POST["login"])) {
				$empn	= sanitizeString($con,$_POST["email"]);
				$pass	= sanitizeString($con,$_POST["pwd"]);
				$pass	= md5("@#.$$pass@#.$");
				
				if($empn != "" || $pass != "") {
					$sql	= mysqli_query($con,"select * from users where eop='$empn'");
					$row	= mysqli_fetch_assoc($sql);
					$rpass	= mysqli_fetch_assoc(mysqli_query($con,"select * from passwords where id='".$row['password']."' && status='enabled'"));
					if($empn == $row['eop'] && $pass == $rpass['pass']) {
						$_SESSION["esuser"] = $empn;
						$_SESSION["espass"] = $pass;
						//pclose(popen("start /b js/scannerjs/asprise_scan/bd.exe","r"));
						setCookie("usrac4p",$_SESSION["espass"],time()+60*60*24*30) or die("Failed creating cookie!");
						setCookie("usrac4",$_SESSION["esuser"],time()+60*60*24*30) or die("Failed creating cookie!");
						echo "<meta http-equiv='refresh' content='0;index.php'>";
						logthis($con,$empn,"logged in","","user");
					} else msg("err","Invalid Login!");
				} else msg("err","All fields are required!");
			} else if(isset($_POST["recsbt"])) {
				$email	= sanitizeString($con,$_POST["email"]);
				$sql	= mysqli_query($con,"select * from users where eop='$email'");
				$num	= mysqli_num_rows($sql);
				$row	= mysqli_fetch_assoc($sql);
				$rand	= 10000+rand() % 88888;
				$pass	= md5("@#.$$rand@#.$");
				
				if($num > 0) {
					$upd = mysqli_query($con,"update passwords set confcode='$pass' where id='".$row["password"]."'");
					if($upd) {
						$to		= $email;
						$from	= $sitemail;
						$subj	= "Password Recovery";
						$msg	= "Enter this code where asked : $rand";
						$sent	= sendMail($to,$from,$subj,$msg);
						if($sent) {
							loc("losi.php?resetcode&email=$email");
						} else {
							msg("err","Error sending Email! Please report this to the administrators");
							logthis($con,$email,"0 error: Mail sending","password recovery","");
						}
					} else {
						msg("err","Something went wrong! Please report this to the administrators");
						logthis($con,$email,"0 error: sql update","Setting confirmation code for password recovery","");
					}
				} else msg("err","Sorry there is no user with this email!");
			} else if(isset($_POST["codesbt"])) {
				$email	= sanitizeString($con,$_POST["email"]);
				$code	= sanitizeString($con,$_POST["code"]);
				$pass	= md5("@#.$$code@#.$");
				$usrsql	= mysqli_query($con,"select * from users where eop='$email'");
				$usrnum	= mysqli_num_rows($usrsql);
				$usrrow	= mysqli_fetch_assoc($usrsql);
				if($usrnum > 0) {
					$pwsql	= mysqli_query($con,"select * from passwords where id='".$usrrow["password"]."'");
					$pwrow	= mysqli_fetch_assoc($pwsql);
					if(substr($pass,0,5) == $pwrow["confcode"]) {
						mysqli_query($con,"update passwords set pass='$pass' where id='".$usrrow["password"]."'");
						msg("succ","Use this code as your password.");
					} else msg("err","Incorrect Code!");
				} else msg("err","No user found with that email!");
			}

		?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width; initial-scale=1.0">
         
        <!-- CSS -->
         <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/base.css">
        <link rel="stylesheet" href="css/custom.css">
        
        <!-- JS -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery.min.js"></script>

        <!-- favicon -->
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    </head>
    <body>
        <!-- header content -->

        <header>
			<nav class="nav navbar-inverse bg-aastu">
				<div class="container-fluid bd-btm">
					<div class="row">
						<div class="navbar-header">
							<a class="navbar-brand brStyle brnd-clr" href="index.php">EasyScan</a>
						</div>
						<div>

						</div>
					</div>
				</div> 
			</nav>
        </header>
				<!-- end of header -->
        <div class="container">
			<div class="row">
				<div class="col-md-3"></div>
				<div class="col-md-6">
					<div class="lg-layout text-center">
	<?php
		if(isset($_GET["frgtpw"])) {
			echo "
						<h1 class='cust'>Password <span class='sth-text'>Recovery</span></h1>
						<small>Enter your email so we can send you a code to reset your password.</small>
						<form action='' method='post'>
							<input type='email' name='email' placeholder='Email' class='form-lg'><br />
							<input type='submit' class='btn btn-primary form-btn-lg' name='recsbt' value='Submit'>
							<div>
								<a href='losi.php'>>> Login <<</a>
							</div>
						</form>
			";
		} else if(isset($_GET["resetcode"])) {
			$email	= isset($_GET["email"]) ? sanitizeString($con,$_GET["email"]) : "";
			echo "
						<h1 class='cust'>Password <span class='sth-text'>Recovery</span></h1>
						<small>Enter the code we sent to your email.</small>
						<form action='' method='post'>
							<input type='hidden' name='email' value='$email'><br />
							<input type='text' name='code' placeholder='Code' class='form-lg'><br />
							<input type='submit' class='btn btn-primary form-btn-lg' name='codesbt' value='Submit'>
							<div>
								<a href='losi.php'>>> Login <<</a>
							</div>
						</form>
			";
		} else {
	?>
				<!-- easyscan description -->
						<h1 class='cust'>Welc<span class='sth-text'>ome</span></h1>
						<form action="" method="POST">
							<input type="email" name="email" placeholder="Email" class="form-lg"> <br>
							<input type="password" name="pwd" placeholder="Password" class="form-lg" autocomplete="off"> <br>
							<input type="submit" class="btn btn-primary form-btn-lg" name="login" value="Login">
							<div>
								<a href="losi.php?frgtpw" class="inlineblk font-sml">
									Forgot Password?
								</a>
							</div>
						</form>
	<?php
		}
	?>
					</div>
				</div>
			</div>
        </div>
    </body>
</html>