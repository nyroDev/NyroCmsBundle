<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article id="<?php echo $name; ?>" class="form">
		<h1><?php echo isset($title) ? $title : $view['nyrodev']->trans('admin.'.$name.'.viewTitle'); ?></h1>

		<?php if (isset($intro) && $intro): ?>
			<?php echo $intro; ?>
		<?php endif; ?>
		
		<?php echo $view['form']->form($form); ?>
	</article>
<?php $view['slots']->stop(); ?>