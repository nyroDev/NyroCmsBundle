<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title><?php $view['slots']->output('title', $view['nyrodev']->trans('admin.misc.title')); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<?php echo $view['nyrodev_tagRenderer']->renderWebpackLinkTags('css/admin/nyroCms', 'type="text/css" media="screen"'); ?>

	<?php
    // 56.25em = 900px based on regular 16px default font-size
    echo $view['nyrodev_tagRenderer']->renderWebpackLinkTags('css/admin/nyroCmsMobile', 'type="text/css" media="all and (max-width: 56.25em)"');
    echo $view['nyrodev_tagRenderer']->renderWebpackLinkTags('css/admin/nyroCmsTablet', 'type="text/css" media="all and (min-width: 56.25em), print"');
    ?>
	
	<?php echo $view['nyrodev_tagRenderer']->renderWebpackScriptTags('js/admin/nyroCms', 'defer'); ?>
</head>
<body id="<?php $view['slots']->output('bodyId', 'default'); ?>">
	<?php echo $view->render('@NyroDevNyroCms/AdminTpl/header.html.php', $view['nyrocms_admin']->getHeaderVars()); ?>
	
	<main>
		<?php $view['slots']->output('content'); ?>
	</main>

	<?php echo $view->render('@NyroDevNyroCms/AdminTpl/footer.html.php'); ?>
	<?php echo $view->render('@NyroDevNyroCms/AdminTpl/templates.html.php'); ?>
		
	<?php if ($view['session']->hasFlash('confirm')): ?>
		<dialog id="confirmMsg"><?php echo implode('<br />', $view['session']->getFlash('confirm')); ?></dialog>
	<?php endif; ?>
</body>
</html>