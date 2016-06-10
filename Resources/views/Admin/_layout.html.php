<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title><?php $view['slots']->output('title', $view['nyrodev']->trans('admin.misc.title')) ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<?php foreach ($view['assetic']->stylesheets(
        '@nyrocms_css_admin',
        array('?yui_css'),
        array('output' => 'css/nyroCmsAdmin.css')) as $url): ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $view->escape($view['nyrodev']->getAsseticVersionUrl($url)) ?>" />
	<?php endforeach; ?>
	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<script></script>
	<![endif]-->
	
	<?php foreach ($view['assetic']->javascripts(
        '@nyrocms_js_admin',
        array('?closure'),
        array('output' => 'js/nyroJsAdmin.js')) as $url): ?>
		<script type="text/javascript" src="<?php echo $view->escape($view['nyrodev']->getAsseticVersionUrl($url)) ?>" defer></script>
	<?php endforeach; ?>
</head>
<body id="<?php $view['slots']->output('bodyId', 'default') ?>">
	<?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('NyroDevNyroCmsBundle:AdminTpl:header')) ?>
	
	<main>
		<?php $view['slots']->output('content') ?>
		<?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('NyroDevNyroCmsBundle:AdminTpl:footer')) ?>
	</main>
		
	<?php if ($view['session']->hasFlash('confirm')): ?>
		<div id="confirmMsg"><?php echo implode('<br />', $view['session']->getFlash('confirm')) ?></div>
	<?php endif; ?>
</body>
</html>
