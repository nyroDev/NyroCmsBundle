<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title><?php $view['slots']->output('title', $view['nyrodev']->trans('admin.misc.title')); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<?php echo $view['nyrodev_tagRender']->renderWebpackLinkTags('css/admin/nyroCms', 'type="text/css" media="screen"'); ?>
	
	<?php echo $view['nyrodev_tagRender']->renderWebpackScriptTags('js/admin/nyroCms', 'defer'); ?>
</head>
<body id="<?php $view['slots']->output('bodyId', 'default'); ?>">
	<?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('NyroDevNyroCmsBundle:AdminTpl:header')); ?>
	
	<main>
		<?php $view['slots']->output('content'); ?>
		<?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('NyroDevNyroCmsBundle:AdminTpl:footer')); ?>
	</main>
		
	<?php if ($view['session']->hasFlash('confirm')): ?>
		<div id="confirmMsg"><?php echo implode('<br />', $view['session']->getFlash('confirm')); ?></div>
	<?php endif; ?>
</body>
</html>