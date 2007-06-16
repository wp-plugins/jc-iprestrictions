<?php
if (isset($_POST['tmpPreview']))
{
	$tmpPreview		= urldecode($_POST['tmpPreview']);
	$replacethis	= array('/\\\\"/', "/\\\\'/");
	$replacethat	= array('"', "'");
	$tmpPreview		=  preg_replace($replacethis, $replacethat, $tmpPreview);
	die($tmpPreview);
}
else
{
?>
<html>
<head>
<title>
IP Restrictions Preview
</title>
<script language="Javascript">
<!--
function transferpreview()
{
	var tmpPreview;
	tmpPreview=opener.document.forms['jc_ipr_form'].elements['jc_ipr_v_RestrictedMessage'].value;
	tmpPreview=encodeURI(tmpPreview);
	document.previewform.tmpPreview.value=tmpPreview;
	document.previewform.submit();
}
-->
</script>
<body onload="transferpreview();">
<p style="font-weight: bold;">L O A D I N G . . .</p>
<p>If this window doesn't reload quickly (30 seconds at most), either your server crashed or your web browser doesn't support standard javascript.</p>
<form name="previewform" id="previewformID" action="preview.php" method="post">
<textarea name="tmpPreview" style="height: 450px; width: 700px; visibility: hidden;" readonly></textarea>
</form>
</body>
</html>
<?php
}
?>