<img src="<?php echo $image ? $view['nyrocms_composer']->imageResizeConfig($image, $config) : 'https://placehold.it/'.(isset($config['placeholdW']) ? $config['placeholdW'] : $config['w']).'x'.(isset($config['placeholdH']) ? $config['placeholdH'] : $config['h']) ?>" alt="<?php echo $view->escape($title) ?>" class="<?php echo $class ?>" />