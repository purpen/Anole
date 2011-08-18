<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>data</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="purpen">
	<!-- Date: 2009-02-05 -->
</head>
<body>
<h3>User Data:</h3>

{foreach from=$users item=user}
<div>
	<h4>Name:{$user.nick_name}－{$user.passport_id}</h4>
	<p>Passport:{$user.passport}</p>
</div>
{/foreach}

<h3>参数</h3>
<p>ID:<a href="#">{$id}</a></p>
</body>
</html>
