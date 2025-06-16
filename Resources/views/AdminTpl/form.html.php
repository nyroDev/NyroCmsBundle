<?php $title = isset($title) ? $title : $view['nyrodev']->trans('admin.'.$name.'.viewTitle'); ?>
<?php if (!$view['nyrodev']->isAjax()): ?>
<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<?php echo $view->render('@NyroDevNyroCms/AdminTpl/breadcrumbs.html.php', [
	    'links' => [
	        [
	            'url' => $view['nyrodev']->generateUrl($route, $routePrm),
	            'label' => $title,
	        ],
	    ],
	    'title' => $view['translator']->trans('admin.misc.'.$action),
	]); ?>
<?php endif; ?>
	<article id="<?php echo $name; ?>" class="form">
		<h1><?php echo $title; ?></h1>

		<?php if (isset($intro) && $intro): ?>
			<?php echo $intro; ?>
		<?php endif; ?>
		
		<?php echo $view['form']->form($form); ?>

		<?php if (isset($outro) && $outro): ?>
			<?php echo $outro; ?>
		<?php endif; ?>
	</article>

<?php if (!$view['nyrodev']->isAjax()): ?>
<?php $view['slots']->stop(); ?>
<?php endif; ?>