<?php echo '<?xml'; ?> version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">
<?php
foreach ($urls as $url) {
    echo '<url><loc>'.$url.'</loc><mobile:mobile/></url>'."\n";
}
?>
</urlset>