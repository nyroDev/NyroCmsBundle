<!DOCTYPE html>
<html class="<?php echo $view['nyrocms_composer']->getWrapperCssTheme($row, \NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent::POSITION_ADMIN_HTML); ?>">
<head>
    <meta charset="utf-8"/>
    <title>Composer for <?php echo $row; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<?php echo $view->render($view['nyrocms_composer']->cssTemplate($row), [
	    'row' => $row,
	]); ?>

	<?php echo $view['nyrodev_tagRenderer']->renderWebpackLinkTags('css/admin/nyroCmsComposer', 'type="text/css" media="screen"'); ?>

	<?php echo $view['nyrodev_tagRenderer']->renderWebpackScriptTags('js/admin/nyroCmsComposer', 'defer'); ?>
</head>
<body class="<?php echo $view['nyrocms_composer']->getWrapperCssTheme($row, \NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent::POSITION_ADMIN_BODY); ?><?php echo $canChangeStructure ? '' : ' noChangeStructure'; ?><?php echo $canChangeMedia ? '' : ' noChangeMedia'; ?>">
<?php
$prefixTinymce = 'data-tinymce_';
$prefixTinymceSimple = 'data-tinymcesimple_';

$attrs = array_merge(
    $view['nyrodev_form']->getPluploadAttrs(),
    [
        'data-tinymceurl' => $view['assets']->getUrl('bundles/nyrodevutility/vendor/tinymce/tinymce.min.js'),
        $prefixTinymce.'inline' => 'true',
        $prefixTinymce.'language' => $view['request']->getLocale(),
        $prefixTinymce.'theme' => 'silver',
        $prefixTinymce.'relative_urls' => 'false',
        $prefixTinymce.'browser_spellcheck' => 'true',

        $prefixTinymceSimple.'inline' => 'true',
        $prefixTinymceSimple.'menubar' => 'false',
        $prefixTinymceSimple.'valid_elements' => '',
    ],
    $view['nyrocms_composer']->tinymceAttrs($row, $prefixTinymce),
    $view['nyrocms_composer']->tinymceAttrs($row, $prefixTinymceSimple, true)
);

$attrs['data-confirm'] = $view['nyrodev']->trans('admin.composer.action.confirm');
$attrs['data-cancel'] = $view['nyrodev']->trans('admin.composer.action.cancel');
$attrs['data-addphoto'] = $view['nyrodev']->trans('admin.composer.action.addPhoto');
$attrs['data-slideshowtitle'] = $view['nyrodev']->trans('admin.composer.action.slideshowTitle');
$attrs['data-slideshowdelete'] = $view['nyrodev']->trans('admin.composer.action.slideshowDelete');
$attrs['data-deleteblock'] = $view['nyrodev']->trans('admin.composer.action.deleteBlock');
$attrs['data-linkurl'] = $view['nyrodev']->trans('admin.composer.action.linkUrl');
$attrs['data-icon'] = $view['nyrocms_admin']->getIcon('TPL');

$attrsHtml = null;
foreach ($attrs as $k => $v) {
    $attrsHtml .= sprintf('%s="%s" ', $view->escape($k), $view->escape($v));
}

$nbButtons = 0;
$maxButtons = $view['nyrocms_composer']->getMaxComposerButtons($row);
?>
<form id="composer" <?php echo $attrsHtml; ?> method="post" enctype="multipart/form-data">

	<?php if (
	    ($canChangeLang && count($langs) > 0)
	    ||
	    $canChangeStructure
	    ||
	    ($canChangeTheme && count($themes) > 1)
	): ?>
	<nav id="composerNavTool">
		<?php if ($canChangeStructure): ?>
			<nav id="composerAvlBlocks" class="composerNav">
				<input type="checkbox" name="" id="avlBlocksInput" />
				<label for="avlBlocksInput">Ajouter</label>
				<nav id="availableBlocks">
					<?php foreach ($availableBlocks as $b): ?>
						<a href="<?php echo $composerUrl.'?block='.$b; ?>"
							class="composerNavElt availableBlock <?php echo $b; ?>"
							title="<?php echo $view['translator']->trans('admin.composer.blocks.'.$b); ?>">
							<span><?php echo $view['translator']->trans('admin.composer.blocks.'.$b); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>
			</nav>
		<?php endif; ?>
		<?php if ($canChangeStructure && $canChangeTheme && count($themes) > 1): ?>
			<nav id="themeSelect" class="composerNav">
				<input type="checkbox" name="" id="themeSelectInput" />
				<label for="themeSelectInput">
					<?php echo $view['nyrodev']->trans('admin.content.themeSelectInput'); ?>
					<span id="themeDemo" class="bg_theme bg_<?php echo $view['nyrocms_composer']->getCssTheme($row); ?>"></span>
				</label>
				<nav id="themeSelectChoices">
					<?php if ($row->getParent()): ?>
						<input id="theme_parent" type="radio" name="theme" value="" <?php echo !$row->getTheme() ? 'checked="checked"' : ''; ?> data-parent="<?php echo $view['nyrocms_composer']->getCssTheme($row); ?>"/>
						<label class="composerNavElt" for="theme_parent" title="<?php echo $view['nyrodev']->trans('admin.content.themeEmpty'); ?>">
							<span class="bg_theme bg_<?php echo $view['nyrocms_composer']->getCssTheme($row); ?>"></span>
						</label>
					<?php endif; ?>
					<?php foreach ($themes as $k => $v): ?>
						<input id="theme_<?php echo $k; ?>" type="radio" name="theme" value="<?php echo $k; ?>"<?php echo $k == $row->getTheme() ? ' checked="checked" ' : ''; ?>/>
						<label class="composerNavElt" for="theme_<?php echo $k; ?>" title="<?php echo $v; ?>">
							<span class="bg_theme bg_<?php echo $k; ?>"></span>
						</label>
					<?php endforeach; ?>
				</nav>
			</nav>
		<?php endif; ?>
	</nav>
	<?php endif; ?>

	<?php echo $view->render($view['nyrocms_composer']->composerTemplate($row), [
	    'row' => $row,
	]); ?>
	
	<nav id="composerNavButtons">
		<button type="submit" class="composerSubmit"><?php echo $view['nyrodev']->trans('admin.composer.action.save'); ?></button>
		<a href="<?php echo $view['nyrocms_composer']->cancelUrl($row); ?>" class="cancel button" data-confirm="<?php echo $view['nyrodev']->trans('admin.composer.action.cancelConfirm'); ?>"><?php echo $view['nyrodev']->trans('admin.misc.cancel'); ?></a>

		<?php if ($canChangeLang && count($langs) > 0): ?>
			<div id="langSelect" class="composerNav composerNavLeft">
				<input type="checkbox" name="" id="langSelectInput" />
				<label for="langSelectInput">
					<span><?php echo $view['nyrodev']->trans('admin.content.lang'); ?></span>
					<strong><?php echo $lang; ?></strong>
				</label>
				<nav>
					<?php foreach ($langs as $lg => $lang): ?>
						<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', ['type' => $type, 'id' => $id, 'lang' => $lg]); ?>"
							class="composerNavElt langChange composerNavConfirm"
							data-confirm="<?php echo $view['nyrodev']->trans('admin.composer.action.langChange'); ?>"
						><?php echo $lg; ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
		<?php endif; ?>
	</nav>
</form>
</body>
</html>