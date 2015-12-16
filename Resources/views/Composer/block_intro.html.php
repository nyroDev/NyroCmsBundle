<?php if ($admin): ?>
	<h1 class="composable composableSimple" data-name="contents_<?php echo $nb ?>_title"><?php echo $contents['title'] ?></h1>
	<h2 class="composable composableSimple" data-name="contents_<?php echo $nb ?>_subtitle"><?php echo $contents['subtitle'] ?></h2>
	
	<?php if ($handlerInContentsKey === 'text'): ?>
		<?php echo $handlerInContent ?>
	<?php else: ?>
		<div class="text1 composable composableHtml" data-name="contents_<?php echo $nb ?>_text">
			<?php echo $contents['text'] ?>
		</div>
	<?php endif; ?>
	<textarea name="contents[<?php echo $nb ?>][title]" id="contents_<?php echo $nb ?>_title"><?php echo $contents['title'] ?></textarea>
	<textarea name="contents[<?php echo $nb ?>][subtitle]" id="contents_<?php echo $nb ?>_subtitle"><?php echo $contents['subtitle'] ?></textarea>
	<textarea name="contents[<?php echo $nb ?>][text]" id="contents_<?php echo $nb ?>_text"><?php echo $contents['text'] ?></textarea>
<?php else: ?>
	<?php if (isset($contents['title']) && $contents['title'] && $contents['title'] != '&nbsp;'): ?>
		<h1><?php echo $contents['title'] ?></h1>
	<?php endif; ?>
	<?php if (isset($contents['subtitle']) && $contents['subtitle'] && $contents['subtitle'] != '&nbsp;'): ?>
		<h2><?php echo $contents['subtitle'] ?></h2>
	<?php endif; ?>
	<?php if ($handlerInContentsKey === 'text'): ?> 
		<?php echo $handlerInContent ?>
	<?php elseif (isset($contents['text']) && $contents['text'] && trim(strip_tags($contents['text']))): ?>
		<div class="text1"><?php echo $view['nyrodev_image']->resizeImagesInHtml($contents['text'], false, true) ?></div>
	<?php endif; ?>
<?php endif; ?>
