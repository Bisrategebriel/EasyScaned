<?php
?>
<script>
var lang = [{eng:"search", amh:"ፈልግ"},
			{eng:"go",amh:"ፈልግ"},
			{eng:"Letter number",amh:"የደብዳብ ቁጥር"},
			{eng: "Office Name",  amh:" የቢሮ ስም"},
			{eng:"Office",  amh:"ቢሮ"},
			{eng:"setting",  amh:"ማስተካከያ/መቼት"},
			{eng:"settings",  amh:"ማስተካከያ/መቼት"},
			{eng:"register user",  amh: "ተጠቃሚ መመዝገቢያ"},
			{eng:"view",  amh:"ማየት"},
			{eng:"add",  amh:"መጨመር"},
			{eng:"outbox",  amh:"ወጪ ደብዳብ"},
			{eng:"inbox",  amh:"ገቢ ደብዳብ"},
			{eng:"employee id",  amh:"የተቀጣሪ መለያ ቁጥር"},
			{eng:"register users",  amh:"ሰራተኛ መመዝገብ"},
			{eng: "minutes of meeting",  amh:"ቃለጉባኤ"},
			{eng:"number of allowed position",  amh:"የስራ ደረጃ ብዛት"},
			{eng:"Parent Office",  amh:"የበላይ ቢሮ"},
			{eng:"view users",  amh:"ተጠቃሚዎችን ማየት"},
			{eng:"view office",  amh:"ቢሮ ማየት"},
			{eng:"Add Office",   amh:"ቢሮ መጨመር"},
			{eng:"Add Letter Type",  amh:"የደብዳቤ አይነት መጨመር"},
			{eng:"Add Position",   amh:"የስራ ደረጃ መጨመር"},
			{eng:"Add Security Type",  amh:"የጥበቃ አይነት መጨመር"},
			{eng:"Add Letter Category",   amh:"የደብዳቤ ክፍል መጨመር"},
			{eng:"code",   amh:"ኮድ"},
			{eng:"name",   amh:"ስም"},
			{eng:"type",   amh:"አይነት"},
			{eng:"submit",   amh:"አስገባ"},
			{eng:"inbound letter",   amh:"የውስጥ ደብዳቤ"},
			{eng:"outbound letter",   amh:"የውጭ ደብዳቤ"},
			{eng:"Enable",  amh:"ማስቻል"},
			{eng:"Disable",   amh:"አለማስቻል"},
			{eng:"Email",   amh:"ኢሜይል"},
			{eng: "Letters",  amh:"ደብዳቤዎች"},
			{eng: "Email Suffix",  amh:"የኢሜል ድሀረ ቅጥያ መቼት"},
			{eng: "security",  amh:"ጥበቃ"},
			{eng: "status",  amh:"ሁኔታ"},
			{eng: "suffix",  amh:"ቅድመ ቅጣያ"},
			{eng: "confidential",  amh:"ሁለተኛ ደረጃ ምስጢር"},
			{eng: "public",  amh:"ምስጢር የሌለው ደረጃ"},
			{eng: "secret",  amh:"አንደኛ ደረጃ ምስጢር"},
			{eng: "is",  amh:"ነው"},
			{eng: "a powerful",  amh:"ትልቅ ተጽዕኖ ያለው"},
			{eng: "scan",  amh:"አገላብጦ መመልከት"},
			{eng: "archive",  amh:"መዝገብ ቤት"},
			{eng: "messaging",  amh:"መልዕክት"},
			{eng: "quality service",  amh:"ጥራቱን የጠበቀ አገልግሎት"},
			{eng: "communication",  amh:"ግንኙነት"},
			{eng: "helps",  amh:"እርዳታ"},
			{eng: "software",  amh:"የኮምፒዩተር ፕሮግራም"},
			{eng: "first name",  amh:"ስም"},
			{eng: "middle name",  amh:"የአባት ስም"},
			{eng: "last name",  amh:"የአያት ስም"},
			{eng: "select office",  amh:"ቢሮ ምረጥ"},
			{eng: "select office position",  amh:"የቢሮ የስራ ደረጃ"},
			{eng: " employee ID",  amh:"የሠራተኛ መታወቂያ ቁጥር"},
			{eng: "Grant Account Access",  amh:"የአካውንት መጠቀምን ፍቀድ"},
			{eng: "Institutional Email",  amh:"የተቋም ኢሜይል"},
			{eng: "notice",  amh:"ማስታወቂያ"},
			{eng: "memo",  amh:"የውስጥ ማስታወሻ"},
			{eng: "File Name",  amh:"የፋይል ስም"},
			{eng: "Subject",  amh:"ርዕስ"},
			{eng: "From",  amh:"ከ"},
			{eng: "To",  amh:"ለ"},
			{eng: "Action",  amh:"ተጨማሪ ድርጊት"},
			{eng: "pending files",  amh:"እንጥልጥል ላይ ያሉ ፋይሎች"},
			{eng: "scanned at",  amh:"በሲስተሙ የታየበት ቀን"},
			{eng: "origin date",  amh:"የደረሰበት ቀን"},
			{eng: "File Receive",  amh:"የፋይል ምሰጢራዊነት "},
			{eng: "send email ",  amh:"ኢሜይል ላክ"},
			{eng: "upload",  amh:" ላክ"},
			{eng: "Filter",  amh:"አጥልል"},
			{eng: "change email",  amh:"ኢሜይል ቀይር"},
			{eng: "change password",  amh:"የይለፍ ቃል ቀይር"},
			{eng: "Library",  amh:"ቤተ፟-መጽሐፍ"},
			{eng: "Secretary",  amh:"ፀሃፊ"},
			{eng: "Expert",  amh:"ባለሙያ"},
			{eng: "Dean",  amh:"ዲን"},
			{eng: "Director",  amh:"ዳይሬክተር"},
			{eng: "President",  amh:"ፕሬዘዳንት"},
			{eng: "Edit",  amh:"አስተካክል"},
			{eng: "office location",  amh:"የቢሮ አድራሻ"},
			{eng: "Description about the office",  amh:"ስለ ቢሮው አጭር ገለፃ"},
			{eng: "Choose file",  amh:"ፋይል ምረጥ"},
			{eng: "create",  amh:"ፍጠር"},
			{eng: "minute number",  amh:"የቃለጉባኤ ቁጥር"},
			{eng: "Date",  amh:"ቀን"},
			{eng: "time",  amh:"ሰዓት"},
			{eng:"language",amh:"ቋንቋ"}
			];

var curlang = getCookie("eslang");
if(curlang == "amh") langTrans(1);

function langTrans(langnum) {
	if(langnum == 1) document.cookie = "eslang=amh;path=/;";
	else if(langnum == 0) document.cookie = "eslang=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
	var inputs	= document.getElementsByTagName("input");
	var lngs	= document.getElementsByTagName("lng");
	var btns	= document.getElementsByTagName("button");
	for(i=0;i<inputs.length;i++) {
		lang.find((o,j) => {
			if(o.eng.toLowerCase() == inputs[i].value.toLowerCase()) {
				if(langnum == 1)
					inputs[i].value = o.amh;
			}
			if(o.eng.toLowerCase() == inputs[i].placeholder.toLowerCase()) {
				if(langnum == 1)
				inputs[i].placeholder = o.amh;
			}
		});
	}
	for(i=0;i<lngs.length;i++) {
		lang.find((o,j) => {
			if(o.eng.toLowerCase() == lngs[i].innerHTML.toLowerCase()) {
				if(langnum == 1)
					lngs[i].innerHTML = o.amh;
			}
		});
	}
	for(i=0;i<btns.length;i++) {
		lang.find((o,j) => {
			if(o.eng.toLowerCase() == btns[i].innerHTML.toLowerCase()) {
				if(langnum == 1)
					btns[i].innerHTML = o.amh;
			}
		});
	}
}
</script>