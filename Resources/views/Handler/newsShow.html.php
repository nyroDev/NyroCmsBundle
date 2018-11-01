<?php if (isset($backUrl) && $backUrl): ?>
	<div class="newsBack"><a href="<?php echo $backUrl; ?>"><?php echo $view['nyrodev']->trans('nyrocms.handler.news.back'); ?></a></div>
<?php endif; ?>
<?php echo $view['nyrocms_composer']->render($news); ?>