<?php

$attrs = [];
if ($src && !str_starts_with($src, 'data:')) {
    $path = substr($src, 1);
    if (isset($widthContainer) && is_array($widthContainer)) {
        $attrs['src'] = $view['router']->path('nyrodev_assets_resize', [
            'dims' => $widthContainer['dims'],
            'path' => $path,
        ]);
        if (isset($widthContainer['sizes'])) {
            $attrs['sizes'] = $widthContainer['sizes'];
        }
        if (isset($widthContainer['srcset']) && is_array($widthContainer['srcset']) && count($widthContainer['srcset'])) {
            $srcset = [];
            foreach ($widthContainer['srcset'] as $srcsetItem) {
                $srcset[] = sprintf('%s %s', $view['router']->path('nyrodev_assets_resize', [
                    'dims' => $srcsetItem['dims'],
                    'path' => $path,
                ]), $srcsetItem['width']);
            }
            $attrs['srcset'] = implode(', ', $srcset);
        }
    } else {
        // Nothing defined, use default resize
        $attrs['src'] = $view['router']->path('nyrodev_assets_resize', [
            'dims' => '1200x1200',
            'path' => $path,
        ]);
    }
} else {
    $attrs['src'] = $src;
}

$allAttrs = [];
foreach ($attrs as $k => $v) {
    $allAttrs[] = sprintf('%s="%s"', $k, $view->escape($v));
}

echo implode(' ', $allAttrs);
