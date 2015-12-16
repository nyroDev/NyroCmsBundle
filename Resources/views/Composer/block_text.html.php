<?php echo $admin && $handlerInContentsKey !== 'text' ? '<div class="composable composableHtml" data-name="contents_'.$nb.'_text">' : null ?>
<?php echo $admin ? ($handlerInContentsKey === 'text' ? $handlerInContent : $contents['text']) : $view['nyrodev_image']->resizeImagesInHtml($contents['text'], false, true) ?>
<?php echo $admin && $handlerInContentsKey !== 'text' ? '</div>' : null ?>
<?php if ($admin): ?>
	<textarea name="contents[<?php echo $nb ?>][text]" id="contents_<?php echo $nb ?>_text"><?php echo $contents['text'] ?></textarea>
<?php endif; ?>