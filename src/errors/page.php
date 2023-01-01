<?php
/**
 * Created W/30/05/2012
 * Updated J/08/12/2022
 *
 * Copyright 2011-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-versioning
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

if (!is_object($this))
	exit(0);

$locale = substr($this->getData('locale'), 0, 2);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale ?>" lang="<?php echo $locale ?>">
<head>
	<title><?php echo $this->getPageTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Language" content="<?php echo $locale ?>" />
	<link rel="icon" type="image/x-icon" href="<?php echo $this->getUrl('favicon.ico') ?>" />
	<?php /* <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->getUrl('config/my.css') ?>" /> */ ?>
	<?php /* <script type="text/javascript" src="<?php echo $this->getUrl('config/my.js') ?>"></script> */ ?>
<style type="text/css">
* { margin:0; padding:0; background-clip:padding-box; }
body { font:0.85em sans-serif; background-color:#DDD; overflow-y:scroll; }
abbr, [title] { border-bottom:0; text-decoration:none; }
div.wrapmain { position:absolute; top:0; left:0; right:0; z-index:999999; }
div.box { margin:auto; width:28rem; padding:3em 2em; border:1px dashed #DDD; background-color:#DDD; }
div.number { margin:0 0 0.4em -0.1em; font-size:4em; font-weight:700; color:#CCC; }
div.number span {
display:inline-block; margin-right:0.1em; width:1.3em; height:1.3em; line-height:135%; text-align:center;
border-radius:50%; border-bottom:1px solid #CACACA; background-color:#EEE;
}
h1 { margin-bottom:1em; font-size:1.3em; font-weight:400; }
p { margin:1em 0; font-size:0.85em; line-height:140%; }
pre { margin-top:3em; padding:2em; white-space:pre-wrap; word-break:break-all; background-color:#D0D0D0; }
pre span.line { color:#555; }
@media screen and (max-width:33rem),(max-device-width:33rem) {
div.box { margin:0 1em 1em; width:auto; }
pre { display:none; }
}
</style>
<script type="text/javascript">
self.cnt = 0;
self.addEventListener('load', function () {
	if (document.getElementById('reload')) {
		self.cnt = parseInt(document.getElementById('reload').querySelector('span').innerHTML, 10);
		self.setInterval(function () {
			if (--self.cnt > 1)
				document.getElementById('reload').querySelector('span').innerHTML = self.cnt;
		}, 1000);
		self.setTimeout(function () { self.location.reload(); }, (self.cnt - 1) * 1000);
	}
});
</script>
</head>
<body>
	<div class="wrapmain">
		<div class="box">
			<div class="number">
				<span><?php echo $code[0] ?></span><span><?php echo $code[1] ?></span><span><?php echo $code[2] ?></span>
			</div>
			<h1><?php echo $this->getTitle() ?></h1>
			<?php echo $this->getHtmlContent() ?>
			<?php echo $this->getHtmlReload() ?>
			<?php if (!empty($id = $this->getData('report'))): ?>
				<p><?php echo $this->__('Error number: §', $id) ?></p>
			<?php endif ?>
		</div>
		<?php if (!empty($txt = $this->canShowReport())): ?>
			<pre><?php echo preg_replace('/\n(#\d+) /', "\n".'<span class="line">$1</span> ', $txt) ?></pre>
		<?php endif ?>
	</div>
</body>
</html>