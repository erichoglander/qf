<?php
if ($logo && substr($logo, 0, 4) != "http")
  $logo = SITE_URL.$logo;
$logo_attr = "";
if (isset($logo_width))
  $logo_attr.= ' width="'.$logo_width.'"';
if (isset($logo_height))
  $logo_attr.= ' height="'.$logo_height.'"';
if (!isset($footer))
  $footer = null;

$shadow = SITE_URL.fileUrl("res/images/mail_triangle.png");
$font_family = 'font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;';
$font = $font_family.'font-size:14px; line-height:23px;';

// Add styles to td
$message = preg_replace(
    "/(<td[^>]*>)(.*?)<\/td>/is", 
    "$1<span style=\"".$font."\">$2</span></td>",
    $message);

// Replace paragraphs
$p1 = '
<tr>
  <td align="left" valign="top" style="padding-bottom:20px;">
    <p style="color:#59554e; '.$font.' padding:0; margin:0; text-align:left;">';
$p2 = '
    </p>
  </td>
</tr>';
$message = str_replace("<p>", $p1, $message);
$message = str_replace("</p>", $p2, $message);

// Replace buttons
$bfind = "/<a class\=\"btn\" href\=\"([^\"]+)\">(.*?)<\/a>/is";
$breplace = '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td align="center" valign="middle">
<!--[if gte mso 9]>
<table align="center" border="0" cellspacing="0" cellpadding="0" style="width:380px" width="380">
<tr>
<td align="center" bgcolor="#59554e" style="padding:15px;" valign="top">
<![endif]-->
<a class="button" href="$1" target="_blank" style="background-color:#59554e; border-collapse:separate; border-top:15px solid #59554e; border-right:40px solid #59554e; border-bottom:15px solid #59554e; border-left:40px solid #59554e; color:#FFFFFF; display:inline-block; '.$font_family.' font-size:13px; font-weight:400; letter-spacing:.3px; text-decoration:none; line-height: 22px;">$2</a>
<!--[if gte mso 9]>
</td>
</tr>
</table>
<![endif]-->
</td>
</tr>
</table>
</td>
</tr>';
$message = preg_replace($bfind, $breplace, $message);
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?=$subject?></title>

<style type="text/css">

/*////// RESET STYLES //////*/
p{margin:10px 0; padding:0;}
table{border-collapse:collapse;}
h1, h2, h3, h4, h5, h6{display:block; margin:0; padding:0;}
#header p{display:block; margin:0; padding:0;}
td{font-family:inherit; font-size:inherit;}
img, a img{border:0; height:auto; outline:none; text-decoration:none;}
body, #bodyTable, #bodyCell{height:100%; margin:0; padding:0; width:100%;}
#outlook a{padding:0;} /* Force Outlook 2007 and up to provide a "view in browser" message. */
@-ms-viewport{width:device-width;} /* Force IE "snap mode" to render widths normally. */
img{-ms-interpolation-mode:bicubic;} /* Force IE to smoothly render resized images. */
table{mso-table-lspace:0pt; mso-table-rspace:0pt;} /* Remove spacing between tables in Outlook Desktop. */
.ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Outlook.com to display emails at full width. */
p, a, li, td, blockquote{mso-line-height-rule:exactly;} /* Force Outlook Desktop to render line heights as they're originally set. */
a[href^="tel"], a[href^="sms"]{color:inherit; cursor:default; text-decoration:none;} /* Force mobile devices to inherit declared link styles. */
p, a, li, td, body, table, blockquote{-ms-text-size-adjust:100%; -webkit-text-size-adjust:100%;} /* Prevent Windows- and Webkit-based platforms from changing declared text sizes. */
.ExternalClass, .ExternalClass p, .ExternalClass td, .ExternalClass div, .ExternalClass span, .ExternalClass font{line-height:100%;} /* Force Outlook.com to display line heights normally. */
a[x-apple-data-detectors]{color:inherit !important; text-decoration:none !important; font-size:inherit !important; font-family:inherit !important; font-weight:inherit !important; line-height:inherit !important;} /* Force iOS devices to heed link styles set in CSS. */
.button a { color: #ffffff !important; }
.button a:visited { color: #ffffff !important; }
.button a:active { color: #ffffff !important; }
.button a:hover { color: #ffffff !important; text-decoration: underline; }

#footerContent a{color:#B7B7B7 !important;}

.button:hover {
	
	background-color:#7a6a56 !important; 
	border-top:15px solid #7a6a56 !important; 
	border-right:40px solid #7a6a56 !important; 
	border-bottom:15px solid #7a6a56 !important; 
	border-left:40px solid #7a6a56 !important; 
	cursor: pointer;
	
}

/*////// MOBILE STYLES //////*/

@media only screen and (min-width:481px){
#header p{font-size:28px !important;line-height:42px !important;}
#bodyContainer{padding-right:11% !important; padding-left:7% !important;}
}


@media only screen and (max-width:480px){
body{width:100% !important; min-width:100% !important;} 
#templateHeader{padding-right:20px !important; padding-left:20px !important;}
#headerContainer{padding-right:0 !important; padding-left:0 !important;}
#headerTable{border-top-left-radius:0 !important; border-top-right-radius:0 !important;}
#headerTable td{padding-top:30px !important;}
#bodyContainer{padding-right:20px !important; padding-left:20px !important;}
#bodyContainer p{font-size:15px !important; line-height:25px !important;}
#bodyContent{padding-right:0 !important;}
#footerContent p{border-bottom:1px solid #E5E5E5; font-size:14px !important; padding-bottom:40px !important;}
.utilityLink{border-bottom:1px solid #E5E5E5; display:block; font-size:13px !important; padding-top:20px; padding-bottom:20px; text-decoration:none !important;}
.mobileHide{display:none; visibility:hidden;}
#logoContainer {padding-top: 20px !important;padding-bottom:20px !important;}
#logoContainer img {width: 100px !important;height:60px !important;}
#header p{font-size:20px !important;}
.button{width: 190px;}
}

</style>

</head>

<body>
<center>
<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
<tr>
<td align="center" valign="top" id="bodyCell">
<!-- BEGIN TEMPLATE // -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td align="center" bgcolor="<?=$top_color?>" valign="top" id="templateHeader" style="background-color:<?=$top_color?>; padding-right:30px; padding-left:30px;">
<!--[if gte mso 9]>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="400">
<tr>
<td align="center" valign="top" width="400">
<![endif]-->
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:400px;" class="emailContainer">
<tr>
<td align="center" valign="top" id="logoContainer" style="padding-top:40px; padding-bottom:40px;">
<img alt="<?=$site_name?>" src="<?=$logo?>"<?=$logo_attr?> style="color:#FFFFFF; <?=$font_family?> font-size:12px; font-weight:400; letter-spacing:-1px; padding:0; margin:0; text-align:center;" />
</td>
</tr>
</table>
<!--[if gte mso 9]>
</td>
</tr>
</table>
<![endif]-->
</td>
</tr>
<tr>
<td align="center" bgcolor="<?=$top_color?>" valign="top" id="headerContainer" style="background-color:<?=$top_color?>; padding-right:30px; padding-left:30px;">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td align="center" valign="top">
<!--[if gte mso 9]>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="640">
<tr>
<td align="center" valign="top" width="640">
<![endif]-->
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;" class="emailContainer">
<tr>
<td align="center" valign="top">
<table align="center" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="100%" id="headerTable" style="background-color:#FFFFFF; border-collapse:separate; border-top-left-radius:4px; border-top-right-radius:4px;">
<tr>
<td align="center" valign="top" width="100%" style="padding-top:40px; padding-bottom:0;">
	<table class="mobileHide" align="center" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#FFFFFF; border-collapse:separate; width: 100%;">
		<tr>
			<td><div style="display: block; width: 100%; margin: 0 auto;">&nbsp;</div></td>
		</tr>
	</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<!--[if gte mso 9]>
</td>
</tr>
</table>
<![endif]-->
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td align="center" valign="top" id="templateBody">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td align="center" valign="top">
<!--[if gte mso 9]>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="700">
<tr>
<td align="center" valign="top" width="700">
<![endif]-->
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:700px;" class="emailContainer">
<tr>
<td align="right" valign="top" width="30" class="mobileHide">
<img src="<?=$shadow?>" width="30" style="display:block;" />
</td>
<td valign="top" width="100%" style="padding-right:14%; padding-left:5%;" id="bodyContainer">
<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td id="header" align="center" valign="top" style="padding-bottom:20px;">
<p style="color:#59554e; font-family: Georgia, Times, 'Times New Roman', serif; font-size:20px; font-style:normal; font-weight:100; line-height:32px; letter-spacing:normal; margin:0; padding:0; text-align:center;">
  <?=$subject?>
</p>
</td>
</tr>
  <?=$message?>
</table>
</td>
</tr>
</table>
<!--[if gte mso 9]>
</td>
</tr>
</table>
<![endif]-->
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td align="center" valign="top" id="templateFooter" style="padding-right:30px; padding-left:30px;">
<!--[if gte mso 9]>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="640">
<tr>
<td align="center" valign="top" width="640">
<![endif]-->
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;" class="emailContainer">
<tr>
<td valign="top" id="footerContent" style="border-top:2px solid #F2F2F2; color:#B7B7B7; <?=$font_family?> font-size:12px; font-weight:400; line-height:24px; padding-top:40px; padding-bottom:20px; text-align:center;">
<p style="color:#B7B7B7; <?=$font_family?> font-size:12px; font-weight:400; line-height:24px; padding:0; margin:0; text-align:center;">
  &copy; <?=date("Y")?> <?=$site_name?>, All Rights Reserved.<br />
  <?=$footer?>
</p>

</td>
</tr>
</table>
<!--[if gte mso 9]>
</td>
</tr>
</table>
<![endif]-->
</td>
</tr>
</table>
<!-- // END TEMPLATE -->
</td>
</tr>
</table>
</center>
</body>
</html>