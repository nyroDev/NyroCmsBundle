<?php if (count($results)): ?>
	<div class="listCont">
	<?php foreach ($results as $news): ?>
		<div class="list news">
			<h3><a href="<?php echo $view['nyrocms']->getUrlFor($news, false, [], $content); ?>"><?php echo $news->getTitle(); ?></a></h3>
			<p><?php echo nl2br($news->getSummary(500)); ?></p>
			<a href="<?php echo $view['nyrocms']->getUrlFor($news, false, [], $content); ?>" class="but"><?php echo $view['nyrodev']->trans('nyrocms.handler.news.more'); ?></a>
		</div>
	<?php endforeach; ?>
	</div>
	<?php if ($pager->hasToPaginate()): ?>
		<nav class="pagination">
			<?php if ($pager->hasPrevious()): ?>
				<a href="<?php echo $pager->getPreviousUrl(); ?>" class="prev"><?php echo $view['translator']->trans('admin.pager.prev'); ?></a>
			<?php endif; ?>
			<?php foreach ($pager->getPagesIndex() as $i => $page): ?>
				<?php if ($page[1]): ?>
					<strong><?php echo $i; ?></strong>
				<?php else: ?>
					<a href="<?php echo $page[0]; ?>"><?php echo $i; ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ($pager->hasNext()): ?>
				<a href="<?php echo $pager->getNextUrl(); ?>" class="next"><?php echo $view['translator']->trans('admin.pager.next'); ?></a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
<?php else: ?>
	<div class="block_text">
		<p><strong><?php echo $view['nyrodev']->trans('nyrocms.handler.news.empty'); ?></strong></p>
	</div>
<?php endif; ?>
