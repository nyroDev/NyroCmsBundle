<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Composer for <?php echo $row ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<?php echo $view->render($view['nyrocms_composer']->cssTemplate($row), array(
		'row'=>$row,
	)) ?>
	
	<?php foreach ($view['assetic']->stylesheets(
		'@nyrocms_css_admin_composer',
		array('?yui_css'),
		array('output'=>'css/adminComposer.css')) as $url): ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $view->escape($view['nyrodev']->getAsseticVersionUrl($url)) ?>" />
	<?php endforeach; ?>
	<?php foreach ($view['assetic']->javascripts(
		'@nyrocms_js_admin_composer',
		array('?closure'),
		array('output'=>'js/adminComposer.js')) as $url): ?>
		<script type="text/javascript" src="<?php echo $view->escape($view['nyrodev']->getAsseticVersionUrl($url)) ?>" defer></script>
	<?php endforeach; ?>
</head>
<body>
<?php
$prefixTinymce = 'data-tinymce_';
$prefixTinymceSimple = 'data-tinymcesimple_';

$attrs = array_merge(
	$view['nyrodev']->get('nyrodev_form')->getPluploadAttrs(),
	array(
		'data-tinymceurl'=>$view['assets']->getUrl('bundles/nyrodevutility/vendor/tinymce/tinymce.jquery.min.js'),
		$prefixTinymce.'inline'=>true,
		$prefixTinymce.'language'=>$view['request']->getLocale(),
		$prefixTinymce.'theme'=>'modern',
		$prefixTinymce.'relative_urls'=>'false',
		$prefixTinymce.'browser_spellcheck'=>'true',

		$prefixTinymceSimple.'inline'=>true,
		$prefixTinymceSimple.'menubar'=>false,
		$prefixTinymceSimple.'valid_elements'=>'',
	),
	$view['nyrocms_composer']->tinymceAttrs($row, $prefixTinymce),
	$view['nyrocms_composer']->tinymceAttrs($row, $prefixTinymceSimple, true)
);

// Add filemanager plugin and configuration needed
$attrs[$prefixTinymce.'plugins'].= ',responsivefilemanager';
$attrs[$prefixTinymce.'external_filemanager_path'] = $view['nyrodev']->generateUrl($view['nyrodev']->getParameter('nyrodev_utility.browser.defaultRoute')).'/';
$attrs[$prefixTinymce.'filemanager_title'] = $view['translator']->trans('nyrodev.browser.title');
$attrs[$prefixTinymce.'external_plugins'] = json_encode(array('filemanager'=>$attrs[$prefixTinymce.'external_filemanager_path'].'plugin.min.js'));

$attrs['data-confirm'] = $view['nyrodev']->trans('admin.composer.action.confirm');
$attrs['data-cancel'] = $view['nyrodev']->trans('admin.composer.action.cancel');
$attrs['data-addphoto'] = $view['nyrodev']->trans('admin.composer.action.addPhoto');
$attrs['data-slideshowtitle'] = $view['nyrodev']->trans('admin.composer.action.slideshowTitle');
$attrs['data-slideshowdelete'] = $view['nyrodev']->trans('admin.composer.action.slideshowDelete');
$attrs['data-deleteblock'] = $view['nyrodev']->trans('admin.composer.action.deleteBlock');
$attrs['data-icon'] = $view['nyrocms_admin']->getIcon('TPL');

$attrsHtml = null;
foreach ($attrs as $k=>$v)
	$attrsHtml.= sprintf('%s="%s" ', $view->escape($k), $view->escape($v));
?>
<form id="composer" <?php echo $attrsHtml ?> method="post" enctype="multipart/form-data">
	<div id="composerTools"><!--
		<?php if ($canChangeTheme && count($themes) > 1): ?>
		--><div class="select">
			<a href="#themeSelect" class="selectLink">
				<span><?php echo $view['nyrodev']->trans('admin.content.theme') ?></span>
				<span id="themeDemo" class="bg_<?php echo $view['nyrocms_composer']->getCssTheme($row) ?>"></span>
			</a>
			<div id="themeSelect" class="selecter">
				<div class="selectList">
					<input id="theme_parent" type="radio" name="theme" value="" <?php echo !$row->getTheme() ? 'checked="checked" ' : '' ?> data-parent="<?php echo $view['nyrocms_composer']->getCssTheme($row->getParent()) ?>"/>
					<label class="bg_<?php echo $view['nyrocms_composer']->getCssTheme($row->getParent()) ?>" for="theme_parent">
						<?php echo $view['nyrodev']->trans('admin.content.themeEmpty') ?>
					</label>
					<?php foreach($themes as $k=>$v): ?>
						<input id="theme_<?php echo $k ?>" type="radio" name="theme" value="<?php echo $k ?>"<?php echo $k == $row->getTheme() ? ' checked="checked" ' : '' ?>/>
						<label class="bg_<?php echo $k ?>" for="theme_<?php echo $k ?>"></label>
					<?php endforeach; ?>
				</div>
			</div>
		</div><!--
		<?php endif; ?>
		<?php if ($canChangeLang && count($langs) > 1 ): ?>
		--><div class="select">
			<a href="#langSelect" class="selectLink">
				<span><?php echo $view['nyrodev']->trans('admin.content.lang') ?></span>
				<strong><?php echo $row->getTranslatableLocale() ? $row->getTranslatableLocale() : $view['nyrocms']->getDefaultLocale($row); ?></strong>
			</a>
			<div id="langSelect" class="selecter selecterLink" data-confirm="<?php echo $view['nyrodev']->trans('admin.composer.action.langChange') ?>">
				<div class="selectList">
					<?php foreach($langs as $lg=>$lang): ?>
						<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', array('type'=>$type, 'id'=>$id, 'lang'=>$lg)) ?>" class="langChange"><?php echo $lg ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div><!--
		<?php endif; ?>
		--><nav id="availableBlocks"><!--
			<?php foreach($availableBlocks as $b): ?>
			--><a href="<?php echo $composerUrl.'?block='.$b ?>" class="<?php echo $b ?>" title="<?php echo $view['translator']->trans('admin.composer.blocks.'.$b) ?>">
				<span><?php echo $view['translator']->trans('admin.composer.blocks.'.$b) ?></span>
			</a><!--
			<?php endforeach; ?>
		--></nav>
	</div>
	<?php echo $view->render($view['nyrocms_composer']->composerTemplate($row), array(
		'row'=>$row,
	)) ?>
	<button type="submit" class="composerSubmit"><?php echo $view['nyrodev']->trans('admin.composer.action.save') ?></button>
	<a href="<?php echo $view['nyrocms_composer']->cancelUrl($row) ?>" class="cancel button" data-confirm="<?php echo $view['nyrodev']->trans('admin.composer.action.cancelConfirm') ?>"><?php echo $view['nyrodev']->trans('admin.misc.cancel') ?></a>
</form>
</body>
</html>