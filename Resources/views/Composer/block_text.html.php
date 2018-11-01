<div class="text<?php echo $admin && 'text' !== $handlerInContentsKey ? ' composable composableHtml" data-name="contents_'.$nb.'_text' : null; ?>">
<?php echo $admin ? ('text' === $handlerInContentsKey ? $handlerInContent : $contents['text']) : $view['nyrodev_image']->resizeImagesInHtml($contents['text'], false, true); ?>
</div>
<?php if ($admin): ?>
	<textarea name="contents[<?php echo $nb; ?>][text]" id="contents_<?php echo $nb; ?>_text"><?php echo $contents['text']; ?></textarea>
<?php endif; ?>