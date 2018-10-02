<?php $cssAll = array(); foreach ($view['assetic']->stylesheets(
    '@css_all',
    array('?yui_css'),
    array('output' => 'css/all.css')) as $url): ?>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo $view->escape($view['nyrodev']->getAsseticVersionUrl($url)); ?>" />
<?php $cssAll[] = $view['nyrodev']->getAsseticVersionUrl($url); endforeach; ?>

<?php
$ieCss = array();
foreach ($view['assetic']->stylesheets(
    '@css_tablet',
    array('?yui_css'),
    array('output' => 'css/tablet.css')) as $url) {
    $url = $view->escape($view['nyrodev']->getAsseticVersionUrl($url));
    $ieCss[] = $url; ?>
	<link rel="stylesheet" type="text/css" media="all and (min-width: <?php echo $tabletWidth; ?>), print" href="<?php echo $url; ?>" />
	<?php
}
foreach ($view['assetic']->stylesheets(
    '@css_desktop',
    array('?yui_css'),
    array('output' => 'css/desktop.css')) as $url) {
    $url = $view->escape($view['nyrodev']->getAsseticVersionUrl($url));
    $ieCss[] = $url; ?>
	<link rel="stylesheet" type="text/css" media="all and (min-width: <?php echo $desktopWidth; ?>), print" href="<?php echo $url; ?>" />
	<?php
}
?>
<!--[if lt IE 9]>
	<?php foreach ($ieCss as $css): ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
	<?php endforeach; ?>
	<?php foreach ($view['assetic']->stylesheets(
        '@css_ie',
        array('?yui_css'),
        array('output' => 'css/ie.css')) as $url): ?>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $view->escape($view['nyrodev']->getAsseticVersionUrl($url)); ?>" />
	<?php endforeach; ?>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<script></script>
<![endif]-->