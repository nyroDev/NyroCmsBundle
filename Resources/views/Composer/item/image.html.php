<img src="<?php echo $src && !str_starts_with($src, 'data:') ? $view['router']->path('nyrodev_assets_resize', [
    'dims' => '1200x1200',
    'path' => substr($src, 1),
]) : $src; ?>" alt="<?php echo $alt; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" loading="lazy" />