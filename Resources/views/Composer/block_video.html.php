<?php if ($admin): ?>
	<div data-name="contents_<?php echo $nb ?>_image" class="composableVideo">
		<iframe <?php echo $contents['embed'] ? 'src="'.$contents['embed'].'"' : null ?> frameborder="0" allowfullscreen></iframe>
		<a href="#"><?php echo $view['nyrodev']->trans('admin.composer.action.video') ?></a>
		<textarea name="contents[<?php echo $nb ?>][url]" id="contents_<?php echo $nb ?>_url"><?php echo $contents['url'] ?></textarea>
		<textarea name="contents[<?php echo $nb ?>][embed]" id="contents_<?php echo $nb ?>_embed"><?php echo $contents['embed'] ?></textarea>
	</div>
<?php elseif ($contents['embed']): ?>
	<iframe src="<?php echo $contents['embed'] ?>" frameborder="0" allowfullscreen></iframe>
<?php endif; ?>