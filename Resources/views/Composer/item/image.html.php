<img src="<?php echo $src ? $view['router']->path('nyrodev_assets_resize', [
    'dims' => '1200x1200',
    'path' => substr($src, 1),
]) : ''; ?>" alt="<?php echo $alt; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" loading="lazy" />