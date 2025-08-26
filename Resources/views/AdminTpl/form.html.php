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
<?php if ($view['nyrodev']->isAjax()): ?>
	<h1 slot="title">
		<?php echo $title; ?>
		<?php echo $view['nyrocms_tooltip']->renderIdent(isset($tooltipIdent) ? $tooltipIdent : $name.'/form'); ?>
	</h1>
<?php endif; ?>
	<article id="<?php echo $name; ?>" class="form"<?php echo $view['nyrodev']->isAjax() ? ' slot="content"' : ''; ?>>
		<?php if (!$view['nyrodev']->isAjax()): ?>
			<h1>
				<?php echo $title; ?>
				<?php echo $view['nyrocms_tooltip']->renderIdent(isset($tooltipIdent) ? $tooltipIdent : $name.'/form'); ?>
			</h1>
		<?php endif; ?>

		<?php if (isset($intro) && $intro): ?>
			<?php echo $intro; ?>
		<?php endif; ?>

		<?php if (isset($form) && $form): ?>
			<?php echo $view['form']->form($form); ?>
		<?php endif; ?>

		<?php if (isset($outro) && $outro): ?>
			<?php echo $outro; ?>
		<?php endif; ?>
	</article>
<?php if (!$view['nyrodev']->isAjax()): ?>
<?php $view['slots']->stop(); ?>
<?php endif; ?>