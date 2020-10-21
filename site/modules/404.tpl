<!DOCTYPE html>
<html lang="ru">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta charset="utf-8">
	<title>Pizza</title>
	~~include file="tpl/css.tpl"~
	
	<style>
	    body.page-not-found{background:#fff}
	</style>
</head>
<body class="page-not-found">

	<style>
	
	    .wrap-404{text-align:center;padding:175px 0 50px;font:15px/18px Arial,Helvetica,sans-serif;background:url(/images/bg-404.png) 50% 65% no-repeat}
	    .wrap-404 .logo{width:260px;height:40px;display:block;margin:0 auto 60px;text-indent:-9999px;background:url(/images/logo.svg) no-repeat;background-size:100% 100%}
	    .wrap-404 h1{font:700 40px/45px 'Roboto Slab','Times New Roman',serif;margin:0 0 20px}
	    .wrap-404 p{min-height:391px}
	    .wrap-404 a{color:#25a0ef;border-bottom:1px solid #d4e8fb;padding-bottom:2px}
	    
	    @media screen and (max-width:480px){
		.wrap-404{text-align:center;padding:30px 0 40px;font:15px/18px Arial,Helvetica,sans-serif;background:url(/images/bg-404.png) 50% 60% no-repeat;background-size:217px 316px}
		.wrap-404 .logo{width:260px;height:40px;display:block;margin:0 auto 25px;text-indent:-9999px;background:url(/images/logo.png) no-repeat;background-size:100% 100%}
		.wrap-404 h1{font:700 22px/25px 'Roboto Slab','Times New Roman',serif;margin:0 0 20px}
		.wrap-404 p{min-height:270px}
		.wrap-404 a{color:#25a0ef;border-bottom:1px solid #d4e8fb;padding-bottom:2px}
	    }
	    
	</style>

	<div class="wrap-404">
		<a href="/" class="logo">pizzasushiwok.ru</a>
		<h1>Страница не найдена</h1>
		<p>Неправильно набран адрес, или такой страницы на сайте не существует</p>
		<a href="/">На главную</a>
	</div>
	~~include file="tpl/metrika.tpl"~

	    ~~*include file="tpl/ga.tpl"*~
	    
</body>
</html>