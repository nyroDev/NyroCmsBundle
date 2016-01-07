<?php if (count($results)): ?>
	<div class="listCont">
	<?php foreach($results as $file): ?>
		<div class="list files">
			<h3><?php echo $file->getTitle() ?></h3>
			<p><?php echo $file->getIntro() ?></p>
			<a href="<?php echo $view['assets']->getUrl($uploadDir.'/'.$file->getInContent('file')) ?>" class="but"><?php echo $view['nyrodev']->trans('nyrocms.handler.files.download') ?></a>
		</div>
	<?php endforeach; ?>
	</div>
<?php else: ?>
	<div class="block_text">
		<p><strong><?php echo $view['nyrodev']->trans('nyrocms.handler.files.empty') ?></strong></p>
	</div>
<?php endif; ?>
