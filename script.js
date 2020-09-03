var myajax	= {
	fetchdata: function(event,val,dispid,apd=false,exc="") {
		var inpval	= event.target;
		var mysel	= document.getElementById(dispid);
		if(mysel.innerHTML == "") {
		$.ajax({
			type: "post",
			url: "ajaxresp.php",
			data: {
				getdata: val,
				val: inpval.value,
				exc: exc,
			},
			success: function(response) {
				//document.getElementById("ssf").innerHTML=response;
				var res = response.split(",$#");
				mysel.innerHTML = "";
				mysel.style.zIndex = 100;
				$i=0;
				for(pos in res) {
					if(res[pos] == "") continue;
					var opt = document.createElement("option");
					if(apd)
						opt.setAttribute("onclick","myajax.apdata(event,'"+inpval.id+"','"+res[pos]+"')");
					else
						opt.setAttribute("onclick","myajax.wdata(event,'"+inpval.id+"','"+res[pos]+"')");
					opt.value = res[pos];
					opt.innerHTML = res[pos];
					mysel.appendChild(opt);
					$i++;
				}
			}
		});
		} else mysel.innerHTML = "";
	},
	wdata: function(event,inpelem,val) {
		document.getElementById(inpelem).value = val;
		event.target.parentElement.innerHTML = "";
	},
	apdata: function(event,inpelem,val) {
		document.getElementById(inpelem).value += val+",";
		event.target.parentElement.innerHTML = "";
	}
}
function changeType(event,val,type,uid) {
	var elem = document.getElementById(uid);
	if(event.target.value == val) {
		elem.type = type;
	}
	//console.log(event.target.value);
}
function confirmation(idd) {
	document.getElementById("confi").innerHTML = document.getElementById(idd).innerHTML;
	document.getElementById("confi").style.display = "block";
}
function myedit(edt) {
	var self = event.target;
	var edt	= document.getElementById(edt);
	edt.setAttribute("name",edt.id);
	edt.disabled = false;
	edt.focus();
	//alert(self.parentElement);
	var tr	= self.parentElement.parentElement;
	self.parentElement.innerHTML="";
	var td	= document.createElement("td");
	var input	= document.createElement("input");
	input.type	= "submit";
	input.name	= "changesbt";
	input.value	= "Change";
	input.className	= "inp-sty c";
	td.appendChild(input);
	tr.appendChild(td);
}
function seclvl(event,valid) {
	var elem = document.getElementById(valid);
	var targ = event.target.value;
	val = secarr[targ-1];
	
	elem.value = val;
}
function hm(val) {
	var elem = document.getElementById(val);
	elem.style.display="none";
}
function clearr(val) {
	var elem = document.getElementById(val);
	elem.innerHTML="";
}
function hc(val) {
	var elem = document.getElementsByClassName(val);
	for(var i=0;i<elem.length;i++) {
		elem[i].style.display="none";
	}
}
function hs(event,val) {
	var elem = document.getElementById(val);

	if(elem.style.display == "block") {
		elem.style.display = "none";
	} else {
		elem.style.display = "block";
	}

	if(event.target.type == "checkbox" || event.target.type == "radio") {
		if(event.target.checked) {
			elem.style.display = "block";
		} else {
			elem.style.display = "none";
		}
	}
}
function scanasPdf(directorate,bound,eop) {
	scanner.scan(servresp,
	{
		"output_settings": [{
			"type": "upload",
			"format": "pdf",
			"upload_target": {
				"url": "localhost/workspace/EasyScan/upload.php?eop="+eop+"&bound="+bound+"&dirct="+directorate
			}
		}]
	},true);
}
function easyScan(type,val,eop,office) {
	scanner.scan(servresp,
	{
		"output_settings": [{
			"type": "upload",
			"format": "pdf",
			"upload_target": {
				"url": "localhost/workspace/EasyScan/upload.php?office="+office+"&eop="+eop+"&type="+type+"&min="+val
			}
		}]
	},true);
}
function servresp(succ,msg,response) {
	document.getElementsByClassName('infdispcont')[0].innerHTML = scanner.getUploadResponse(response);
}
function getCookie(cname) {
	var name	= cname + "=";
	var decodedc	= decodeURIComponent(document.cookie);
	var ca	= decodedc.split(';');
	for(var i=0;i<ca.length;i++) {
		var c = ca[i];
		while(c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if(c.indexOf(name) == 0) {
			return c.substring(name.length,c.length);
		}
	}
}
