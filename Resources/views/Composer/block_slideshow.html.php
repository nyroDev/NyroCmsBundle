<?php if ($admin): ?>
<div class="composableSlideshow"
	data-nb="<?php echo $nb; ?>"
	data-sizebig="<?php echo(isset($config['images']['big']['placeholdW']) ? $config['images']['big']['placeholdW'] : $config['images']['big']['w']).'x'.(isset($config['images']['big']['placeholdH']) ? $config['images']['big']['placeholdH'] : $config['images']['big']['h']); ?>"
	data-sizebigcfg="<?php echo $view->escape(json_encode($config['images']['big'])); ?>"
	data-sizethumb="<?php echo(isset($config['images']['thumb']['placeholdW']) ? $config['images']['thumb']['placeholdW'] : $config['images']['thumb']['w']).'x'.(isset($config['images']['thumb']['placeholdH']) ? $config['images']['thumb']['placeholdH'] : $config['images']['thumb']['h']); ?>"
	data-sizethumbcfg="<?php echo $view->escape(json_encode($config['images']['thumb'])); ?>"
    data-multiplefields="<?php echo $view->escape(implode(',', $config['images']['multipleFields'])); ?>"
	data-placehold="https://placehold.it/">
<?php endif; ?>
<?php if ((isset($contents['images']) && is_array($contents['images']) && count($contents['images'])) || $admin): ?>
	<?php
    $thumbs = array();
    $big = null;
    $first = true;
    if (is_array($contents['images'])) {
        $addThumbOrig = null;
        if ($admin) {
            if ($view['nyrocms_composer']->canChangeMedia($row)) {
                $addThumbOrig .= '<a href="#" class="composableSlideshowUpload">Upload</a>';
                $addThumbOrig .= '<a href="#" class="composableSlideshowDrag">'.$view['nyrocms_admin']->getIcon('drag').'</a>';
                $addThumbOrig .= '<a href="#" class="composableSlideshowEdit">'.$view['nyrocms_admin']->getIcon('pencil').'</a>';
                $addThumbOrig .= '<a href="#" class="composableSlideshowDelete">'.$view['nyrocms_admin']->getIcon('delete').'</a>';
            } else {
                $addThumbOrig .= '<a href="#" class="composableSlideshowEdit">'.$view['nyrocms_admin']->getIcon('pencil').'</a>';
            }
        }

        foreach ($contents['images'] as $img) {
            $bigSrc = $view['nyrocms_composer']->imageResizeConfig($img['file'], $config['images']['big']);
            $thumbSrc = $view['nyrocms_composer']->imageResizeConfig($img['file'], $config['images']['thumb']);
            if (!$big) {
                $big = '<div class="block_slideshow_big">';
                $big .= '<img src="'.$bigSrc.'" alt="'.$view->escape($img['title']).'" />';
                $big .= '<span>'.$img['title'].'</span>';
                $big .= '</div>';
            }
            $addThumb = null;
            if ($admin) {
                $addThumb = $addThumbOrig;
                $addThumb .= '<textarea name="contents['.$nb.'][ids][]">'.$img['id'].'</textarea>';
                $addThumb .= '<textarea name="contents['.$nb.'][images][]">'.$img['file'].'</textarea>';
                $addThumb .= '<textarea name="contents['.$nb.'][titles][]">'.$img['title'].'</textarea>';
                $addThumb .= '<textarea name="contents['.$nb.'][deletes][]"></textarea>';
            }
            $thumbs[] = '<li'.($first ? ' class="active"' : '').'><a href="'.$bigSrc.'" class="block_slideshow_thumb"><img src="'.$thumbSrc.'" alt="'.$view->escape($img['title']).'" /></a>'.$addThumb.'</li>';
            $first = false;
        }
    }
    if (!$big && $admin) {
        $big = '<div class="block_slideshow_big"><img src="" alt="" /><span></span></div>';
    }
    echo $big;
    echo '<ul>'.implode(' ', $thumbs).'</ul>';
    ?>
<?php endif; ?>
<?php if ($admin): ?>
</div>
<?php endif; ?>
