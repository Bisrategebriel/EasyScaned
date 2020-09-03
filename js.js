function scanasPdf() {
	alert("begin");
	scanner.scan(servresp,
	{
		"output_settings": [{
			"type": "upload",
			"format": "pdf",
			"upload_target": {
				"url": "localhost:8008/workspace/SchoolProject/upload.php"
			}
		}]
	},false);
	alert("end");
}
function servresp(succ,msg,response) {
	document.getElementsByClassName('infdispcont')[0].innerHTML = scanner.getUploadResponse(response);
}
