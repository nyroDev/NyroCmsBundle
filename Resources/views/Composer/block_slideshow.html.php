<?php if ($admin): ?>
<div class="composableSlideshow"
	data-nb="<?php echo $nb ?>"
	data-sizebig="<?php echo $config['images']['big']['w'].'x'.$config['images']['big']['h'] ?>"
	data-sizethumb="<?php echo $config['images']['thumb']['w'].'x'.$config['images']['thumb']['h'] ?>"
	data-placehold="https://placehold.it/">
<?php endif; ?>
<?php if (count($contents['images']) || $admin): ?>
	<?php
	$thumbs = array();
	$big = null;
	$first = true;
	$addThumbOrig = $admin ? '<a href="#" class="composableSlideshowUpload">Upload</a><a href="#" class="composableSlideshowDrag">'.$view['nyrocms_admin']->getIcon('drag').'</a><a href="#" class="composableSlideshowEdit">'.$view['nyrocms_admin']->getIcon('pencil').'</a><a href="#" class="composableSlideshowDelete">'.$view['nyrocms_admin']->getIcon('delete').'</a>' : null;
	if (is_array($contents['images'])) {
		foreach($contents['images'] as $img) {
			$bigSrc = $view['nyrocms_composer']->imageResize($img['file'], $config['images']['big']['w'], $config['images']['big']['h']);
			$thumbSrc = $view['nyrocms_composer']->imageResize($img['file'], $config['images']['thumb']['w'], $config['images']['thumb']['h']);
			if (!$big) {
				$big = '<div class="block_slideshow_big">';
					$big.= '<img src="'.$bigSrc.'" alt="'.$view->escape($img['title']).'" />';
					$big.= '<span>'.$img['title'].'</span>';
				$big.= '</div>';
			}
			$addThumb = null;
			if ($admin) {
				$addThumb = $addThumbOrig;
				$addThumb.= '<textarea name="contents['.$nb.'][images][]">'.$img['file'].'</textarea>';
				$addThumb.= '<textarea name="contents['.$nb.'][titles][]">'.$img['title'].'</textarea>';
				$addThumb.= '<textarea name="contents['.$nb.'][deletes][]"></textarea>';
			}
			$thumbs[] = '<li'.($first ? ' class="active"' : '').'><a href="'.$bigSrc.'" class="block_slideshow_thumb"><img src="'.$thumbSrc.'" alt="'.$view->escape($img['title']).'" /></a>'.$addThumb.'</li>';
			$first = false;
		}
	}
	if (!$big && $admin)
		$big = '<div class="block_slideshow_big"><img src="" alt="" /><span></span></div>';
	echo $big;
	echo '<ul>'.implode(' ', $thumbs).'</ul>';
	?>
<?php endif; ?>
<?php if ($admin): ?>
</div>
<?php endif; ?>
