<?php
$tmpDefaultMessage = '
<html>
<head>
<title>
'. get_option('blogname') . ': Access Restricted
</title>
<style>
body
{
	margin: 0;
	padding: 0;
	padding-top: 9px;
	color: #ffffff;
	text-align: center;
	min-width: 508px;
}
#wrapper
{
	margin: 0 auto;
	width: 508px;
	padding-top: 7px;
}
#header
{
	background-image: url(\'' . get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/access-restricted.gif\');
	width: 508px;
	height: 125px;
}
#content
{
	border-right: 3px solid #f20100;
	border-bottom: 3px solid #f20100;
	text-align: left;
	color: #000000;
	padding: 25px 10px 10px 10px;
	font-size: 12px;
	font-family: \'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif;
}
#jc_ipr_css_input
{
	float: right;
	border: 2px solid #f20100;
	padding: 2px;
	color: #f20100;
	width: 150px;
	margin-right: 20px;
}
</style>
</head>
<body>
<div id="wrapper">
<div id="header"></div>
<div id="content">
[MESSAGE]
</div>
</div>
<!-- Timestamp: [TIMESTAMP] -->
</body>
</html>
';
return $tmpDefaultMessage;
?>