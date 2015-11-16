<?php foreach($news as $featured): ?>
	<div class="newsHome">
		<strong><a href="<?php echo $view['nyrocms']->getUrlFor($featured, false, array(), $handlerContent) ?>"><?php echo $featured->getTitle() ?></a></strong>
		<p><?php echo $featured->getSummary() ?></p>
		<a href="<?php echo $view['nyrocms']->getUrlFor($featured, false, array(), $handlerContent) ?>" class="but"><?php echo $view['nyrodev']->trans('nyrocms.handler.news.more') ?></a>
	</div>
<?php endforeach; ?>