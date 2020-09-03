<div class="statscont">
	<div class="inline str txtalign lft">
	<div class="inline txtalign lft rltv aln ver" style="width: 60%;">
		<canvas class="abs rgt aln ver b statspec" id="canvasreportkey"></canvas>
		<canvas class="aln str ver statspec" id="canvasreport"></canvas>
	</div>
	<div class="inline txtalign lft aln ver">
	<h4>Filter by</h4>
	<ul class="dropmenu str">
		<li class="bg light str padd mrg ver">Files
			<ul class="padd hor nopadd" <?php if(isset($_GET["filesfltsbt"])) echo "style='display: block;'" ?>>
				<li >
					<div >
						<form action="">
						<input type="text" name="q" placeholder="Key">
						<div>
							<select name="from">
								<option value="-1">From</option>
							<?php
								$sqlval = "select distinct name from office";
								echo getSqlResult($sqlval,"<option>","</option>");
							?>
							</select>
							<select name="to">
								<option value="-1">To</option>
							<?php
								$sqlval = "select distinct name from office";
								echo getSqlResult($sqlval,"<option>","</option>");
							?>
							</select>
						</div>
						<select name="ltrtype">
							<option value="-1">Letter Type</option>
						<?php
							$sqlval	= "select distinct bound from printform";
							echo getSqlResult($sqlval,"<option>","</option>");
						?>
						</select>
						<hr class="sml" />
						Date
						<div class="mrg lft mdl">
							<div >
								<label class="nowrap"><input type="radio" name="datetype" value="scdate"> Scan Date</label>
								<label class="nowrap"><input type="radio" name="datetype" value="odate" checked> Orginal Letter Date</label>
							</div>
							Calendar
							<div class="mrg lft">
								<label class="nowrap"><input type="radio" name="cal" value="gc" checked> GC</label>
								<label class="nowrap"><input type="radio" name="cal" value="ec" > EC</label>
							</div>
							<div>
							From<br />
							<input type="date" name="ftime">
							</div>
							<div>
							To<br />
							<input type="date" name="ttime">
							</div>
						</div>
						<input class="mrg ver str" type="submit" name="filesfltsbt" value="Filter">
						</form>
					</div>
				</li>
			</ul>
		</li>
		<li class="bg light str padd mrg ver">Offices
			<ul class="padd hor nopadd" <?php if(isset($_GET["officesfltsbt"])) echo "style='display: block;'" ?>>
				<li>
					<div>
						<form>
							<input type="text" placeholder="key" name="q">
							<div>
							<select name="parent">
								<option value="-1">Parent</option>
								<?php
									$sqlval = "select distinct parent from office";
									echo getSqlResult($sqlval,"<option>","</option>");
								?>
							</select>
							</div>
							<div>
								<select name="status">
									<option value="-1">Status</option>
									<?php
										$sqlval	= "select distinct status from office";
										echo getSqlResult($sqlval,"<option>","</option>");
									?>
								</select>
							</div>
							Date
							<div class="mrg lft mdl">
								<div>
								From<br />
								<input type="date" name="ftime">
								</div>
								<div>
								To<br />
								<input type="date" name="ttime">
								</div>
							</div>
							<input class="mrg ver str" type="submit" name="officesfltsbt" value="Filter">
						</form>
					</div>
				</li>
			</ul>
		</li>
		<li class="bg light str padd mrg ver">Users
			<ul class="padd hor nopadd" <?php if(isset($_GET["usersfltsbt"])) echo "style='display: block;'" ?>>
				<li>
					<div>
						<form action="">
							<input type="text" placeholder="key" name="q">
							<div>
							<select name="status">
								<option value="-1">Status</option>
								<?php
									$sqlval	= "select distinct status from users";
									echo getSqlResult($sqlval,"<option>","</option>");
								?>
							</select>
							</div>
							Date
							<div class="mrg lft mdl">
								<div>
								From<br />
								<input type="date" name="ftime">
								</div>
								<div>
								To<br />
								<input type="date" name="ttime">
								</div>
							</div>
							<input class="mrg ver str" type="submit" name="usersfltsbt" value="Filter">
						</form>
					</div>
				</li>
			</ul>
		</li>
	</ul>
	</div>
	</div>

	<script>
		document.addEventListener("click",function(ev) {
			ul = ev.target.parentElement;
			if(ul.className.search("dropmenu") >= 0) {
				for(i=0;i<ul.children.length;i++) {
					ul.children[i].children[0].style.display = "none";
				}
				ev.target.children[0].style.display = "block";
			}
		});
	</script>
</div>
<?php
	$val	= getNum($con,"printform");
	echo "$val";
?>
<script>
window.addEventListener("load",function() {
	<?php
		$sql	= mysqli_query($con,"select * from printform");
		$num	= mysqli_num_rows($sql);
		for($i=0;$i<$num;$i++) {
			$row	= mysqli_fetch_assoc($sql);
			$curyr	= explode("-",$row["scantime"])[2];
		}
		$bignum		= $num;
		$officenum	= getNum($con,"office");
		$usersnum	= getNum($con,"users");
		
		if($officenum > $bignum)
			$bignum	= $officenum;
		if($usersnum > $bignum)
			$bignum	= $usersnum;
		
		if($bignum < 40)
			$bignum = 40;
		

		function fltConcat($name,$attr,$result,$chkwith="like",$func=null,...$params) {
			global $con;

			if(isset($_GET["$name"]))
				$val	= sanitizeString($con,$_GET["$name"]);
			else
				$val	= "";
			if($val != "-1" && $val != "") {
				if($func != null)
					$func(implode(",",$params),$val);
				
				if($result == "")
					$result .= " where ";
				else $result .= " && ";
				
				$perc = "";
				if($chkwith == "like")
					$perc = "%";
				$result	.= " ( ";
				$result	.= " $attr $chkwith '$perc$val$perc'";
				$result .= " ) ";
			}
			return $result;
		}
		
		$fileflt	= "";
		$officeflt	= "";
		$userflt	= "";
		if(isset($_GET["filesfltsbt"])) {
			$attrsarr	= ["name","source","user","subject","destin","bound","type","descr","ctime","scantime","status","fromm","number","odate","cal"];
			if(isset($_GET["q"]))
				$qval	= sanitizeString($con,$_GET["q"]);
			else $qval	= "";
			if($qval != "") {
				if($fileflt == "")
					$fileflt .= " where ";
				else $fileflt .= " && ";
				
				$fileflt .= " ( ";
				$i = 0;
				foreach($attrsarr as $attr) {
					if($i != 0)
						$sep = " || ";
					else $sep = "";
					$fileflt .= " $sep $attr like '%$qval%'";
					$i++;
				}
				$fileflt .= " ) ";
			}
			
			
			$fileflt = fltConcat("ltrtype","bound",$fileflt);
			$fileflt = fltConcat("from","source",$fileflt);
			$fileflt = fltConcat("to","destin",$fileflt);
			if(isset($_GET["datetype"])) {
				$val = sanitizeString($con,$_GET["datetype"]);
				if($val == "sctime")
					$val	= "scantime";
				else $val	= "odate";
				$fileflt	= fltConcat("ftime",$val,$fileflt,">=");
				$fileflt	= fltConcat("ttime",$val,$fileflt,"<=");
				$fileflt	= fltConcat("cal","cal",$fileflt,"=");
			}
			
		}
		else if(isset($_GET["officesfltsbt"])) {
			$attrsarr	= ["location","status","parent","poslim"];
			if(isset($_GET["q"]))
				$qval	= sanitizeString($con,$_GET["q"]);
			else $qval	= "";
			if($qval != "") {
				if($officeflt == "")
					$officeflt	.= " where ";
				else $officeflt	.= " && ";
				
				$officeflt .= " ( ";
				$i=0;
				foreach($attrsarr as $attr) {
					if($i != 0)
						$sep	= " || ";
					else $sep	= "";
					$officeflt	.= "$sep $attr like '%$qval%'";
					$i++;
				}
				$officeflt	.= " ) ";
			}
			
			$officeflt = fltConcat("parent","parent",$officeflt,"=");
			$officeflt = fltConcat("ftime","ctime",$officeflt,">=");
			$officeflt = fltConcat("ttime","ctime",$officeflt,"<=");
		}
		else if(isset($_GET["usersfltsbt"])) {
			$attrsarr	= ["ctime","status","name","eop"];
			if(isset($_GET["q"]))
				$qval	= sanitizeString($con,$_GET["q"]);
			else $qval	= "";
			if($qval != "") {
				if($userflt == "")
					$userflt	.= " where ";
				else $userflt	.= " && ";
				
				$userflt .= " ( ";
				$i=0;
				foreach($attrsarr as $attr) {
					if($i != 0)
						$sep	= " || ";
					else $sep	= "";
					$userflt	.= "$sep $attr like '%$qval%'";
					$i++;
				}
				$userflt	.= " ) ";
			}
			
			$userflt = fltConcat("status","status",$userflt);
			$userflt = fltConcat("ftime","ctime",$userflt,">=");
			$userflt = fltConcat("ttime","ctime",$userflt,"<=");
		}

	?>

	//console.log("<?php echo "$fileflt ----" ?>");
	var mydatanum	= <?php echo $bignum ?>;
	var files		= <?php echo getNum($con,"printform $fileflt")?>;
	var offices		= <?php echo getNum($con,"office $officeflt")?>;
	var users		= <?php echo getNum($con,"users $userflt")?>;

	var canvas	= document.getElementById("canvasreport"),
		canvas2	= document.getElementById("canvasreportkey"),
		context	= canvas.getContext("2d"),
		context2	= canvas2.getContext("2d"),
		width	= canvas.width,
		height	=  canvas.height;
		
	var rwidth	= width/2;
	height	= canvas.height	= rwidth+40; 
	
	context.lineWidth = 1;
		
	
	var bar		= {
		x: null,
		y: null,
		w: null,
		h: null,
		oh: null,
		c: null,
		name: null,
		create: function(name,x,y,w,h,c) {
			var obj	= Object.create(this);
			obj.name= name;
			obj.x	= x;
			obj.y	= y;
			obj.w	= w;
			obj.oh	= h;
			obj.h	= h*(report.h/mydatanum);
			obj.c	= c;
			return obj;
		},
		subBar: function(h,c="black") {
			if(h > this.oh) h = this.oh;
			context.save();
			context.lineWidth = 3;
			context.beginPath();
			context.strokeStyle = c;
			context.moveTo(this.x,h*-(report.h/mydatanum));
			context.lineTo(this.x+this.w,h*-(report.h/mydatanum));
			context.stroke();
			context.restore();
		},
		render: function() {
			context.save();
			context.beginPath();
			context.fillStyle = this.c;
			context.fillRect(this.x,this.y,this.w,-this.h);
			context.restore();
			/*
							context.beginPath();
				alert(this.name+" - "+this.subbars.length);
				context.strokeStyle = "Red";
				context.moveTo(this.x,this.subbars[j].h*-(report.h/mydatanum));
				context.lineTo(this.w,this.subbars[j].h*-(report.h/mydatanum));
				context.stroke();
*/
		}
	}
	var report	= {
		syear: 2012,
		eyear: 2020,
		xh: rwidth,
		yh: mydatanum,
		h: rwidth,
		range: rwidth/7,
		yrange: 100,
		bargap: 3,
		fromdate: function(syear,varyear) {
			for(i=0,j=0;i<=this.h;i+=this.range,j++) {
				context.font = rwidth/100+"px calibri";
				context.beginPath();
				context.moveTo(i,0);
				context.lineTo(i,5);
				context.stroke();
				context.strokeText(this.syear+j,i,25);
			}
		},
		render: function() {
			/*
				const constraint = this.h
				const revceivedAmount = this.yh
				if (receievedAmount > 10) {
					difference = receievedAmount / constraint
					difference = parseInt(difference)
					this.range = difference
				}
				
			*/
			
			context.beginPath();
			context.moveTo(0,0);
			context.lineTo(this.h,0);
			context.moveTo(0,0);
			context.lineTo(0,-this.h);
			context.stroke();
			
			this.yrange	= parseInt(this.yh/5);
			for(i=0,j=0;i<=this.h&&j*this.yrange<=this.yh;i+=this.h/this.yh,j+=1) {
				context.save();
				context.font = 10+"px calibri";
				context.beginPath();
				context.moveTo(0,-i*this.yrange);
				context.lineTo(-5,-i*this.yrange);
				context.stroke();
				context.fillText(j*this.yrange,-((this.yrange*j).toString().length*5)-10,-i*this.yrange);
				context.restore();
			}
		}
	}
	context.translate(rwidth/4,(canvas.height)-20);
	context.scale(1,1);
	
	
	report.render();

	var sc	= [];
	sc[0]	= bar.create("Files",(report.range+report.bargap)*(sc.length+1),0,report.range-1,files,"grey");
	sc[1]	= bar.create("Office",(report.range+report.bargap)*(sc.length+1),0,report.range-1,offices,"#aaa");
	sc[2]	= bar.create("Users",(report.range+report.bargap)*(sc.length+1),0,report.range-1,users,"#aca");


	renderBars();
	function renderBars() {
		sc.forEach((bar,ind) => {
			bar.render();
		});
	}
	
	var keys	= {
		x: 0,
		y: 0,
		space: 0,
		vgap: 35,
		render: function() {
			canvas2.width = 111;
			for(i=0;i<sc.length;i++) {
				context2.beginPath();
				context2.font = rwidth/10+"px arial";
				context2.fillStyle = sc[i].c;
				context2.fillRect(this.x,-(this.y+this.space),rwidth/7,rwidth/7);
				context2.fillStyle = "#000";
				context2.fillText(sc[i].name+" = "+sc[i].oh,this.x+(rwidth/7)+6,-(this.y+this.space-20));
				this.space -= this.vgap;
			}
		}
	}
	keys.render();
});
</script>