<div class="text text1<?php echo $admin && 'text1' !== $handlerInContentsKey ? ' composable composableHtml" data-name="contents_'.$nb.'_text1' : ''; ?>">
<?php echo 'text1' === $handlerInContentsKey ? $handlerInContent : ($admin ? $contents['text1'] : $view['nyrodev_image']->resizeImagesInHtml($contents['text1'], false, true)); ?>
</div>
<div class="text text2<?php echo $admin && 'text2' !== $handlerInContentsKey ? ' composable composableHtml" data-name="contents_'.$nb.'_text2' : ''; ?>">
<?php echo 'text2' === $handlerInContentsKey ? $handlerInContent : ($admin ? $contents['text2'] : $view['nyrodev_image']->resizeImagesInHtml($contents['text2'], false, true)); ?>
</div>
<div class="text text3<?php echo $admin && 'text2' !== $handlerInContentsKey ? ' composable composableHtml" data-name="contents_'.$nb.'_text3' : ''; ?>">
<?php echo 'text3' === $handlerInContentsKey ? $handlerInContent : ($admin ? $contents['text3'] : $view['nyrodev_image']->resizeImagesInHtml($contents['text3'], false, true)); ?>
</div>
<?php if ($admin): ?>
	<textarea name="contents[<?php echo $nb; ?>][text1]" id="contents_<?php echo $nb; ?>_text1"><?php echo $contents['text1']; ?></textarea>
	<textarea name="contents[<?php echo $nb; ?>][text2]" id="contents_<?php echo $nb; ?>_text2"><?php echo $contents['text2']; ?></textarea>
	<textarea name="contents[<?php echo $nb; ?>][text3]" id="contents_<?php echo $nb; ?>_text3"><?php echo $contents['text3']; ?></textarea>
<?php endif; ?>
