<?php echo '<?xml'; ?> version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
foreach ($urls as $url) {
    echo '<sitemap><loc>'.$url.'</loc></sitemap>'."\n";
}
?>
</sitemapindex>