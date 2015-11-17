<div class="text text1<?php echo $admin ? ' composable composableHtml" data-name="contents_'.$nb.'_text1' : '' ?>">
<?php echo $admin ? $contents['text1'] : $view['nyrodev_image']->resizeImagesInHtml($contents['text1'], false, true) ?>
</div>
<div class="text text2<?php echo $admin ? ' composable composableHtml" data-name="contents_'.$nb.'_text2' : '' ?>">
<?php echo $admin ? $contents['text2'] : $view['nyrodev_image']->resizeImagesInHtml($contents['text2'], false, true) ?>
</div>
<?php if ($admin): ?>
	<textarea name="contents[<?php echo $nb ?>][text1]" id="contents_<?php echo $nb ?>_text1"><?php echo $contents['text1'] ?></textarea>
	<textarea name="contents[<?php echo $nb ?>][text2]" id="contents_<?php echo $nb ?>_text2"><?php echo $contents['text2'] ?></textarea>
<?php endif; ?>
