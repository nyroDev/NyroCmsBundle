<!DOCTYPE html>
<html class="<?php echo $view['nyrocms_composer']->getWrapperCssTheme($row, NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent::POSITION_ADMIN_HTML); ?>">
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
<body class="<?php echo $view['nyrocms_composer']->getWrapperCssTheme($row, NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent::POSITION_ADMIN_BODY); ?><?php echo $canChangeStructure ? '' : ' noChangeStructure'; ?><?php echo $canChangeMedia ? '' : ' noChangeMedia'; ?>">
<?php
$prefixTinymce = 'data-tinymce_';
$prefixTinymceSimple = 'data-tinymcesimple_';

$attrs = array_merge(
    [
        'data-tinymceurl' => $view['assets']->getUrl('tinymce/tinymce.min.js'),
        $prefixTinymce.'inline' => 'true',
        $prefixTinymce.'language' => $view['request']->getLocale(),
        $prefixTinymce.'promotion' => 'false',
        $prefixTinymce.'branding' => 'false',
        $prefixTinymce.'license_key' => 'gpl',
        $prefixTinymce.'relative_urls' => 'false',
        $prefixTinymce.'browser_spellcheck' => 'true',
        $prefixTinymce.'contextmenu' => 'false',

        $prefixTinymceSimple.'inline' => 'true',
        $prefixTinymceSimple.'menubar' => 'false',
        $prefixTinymceSimple.'valid_elements' => '',
        $prefixTinymceSimple.'browser_spellcheck' => 'true',
        $prefixTinymceSimple.'contextmenu' => 'false',
    ],
    $view['nyrocms_composer']->tinymceAttrs($row, $prefixTinymce),
    $view['nyrocms_composer']->tinymceAttrs($row, $prefixTinymceSimple, true)
);

if (!$canChangeStructure) {
    $attrs['no-structure-change'] = true;
}
if (!$canChangeMedia) {
    $attrs['no-media-change'] = true;
}

if ($availableTemplates) {
    $attrs['data-available-templates'] = json_encode($availableTemplates);
}

$attrsHtml = null;
foreach ($attrs as $k => $v) {
    $attrsHtml .= sprintf('%s="%s" ', $view->escape($k), $view->escape($v));
}

?>

<nyro-composer <?php echo $attrsHtml; ?>>
	<?php foreach ($availableBlocks as $block): ?>
		<?php echo $view['nyrocms_composer']->renderBlockComposerTemplate($row, $block); ?>
	<?php endforeach; ?>
	<?php foreach ($availableItems as $item): ?>
		<?php echo $view['nyrocms_composer']->renderItemComposerTemplate($row, $item); ?>
	<?php endforeach; ?>

	<?php echo $view->render('@NyroDevNyroCms/Composer/_composerUiTemplates.html.php', [
	    'row' => $row,
	]); ?>

	<nyro-composer-top-panel
		cancel-url="<?php echo $view['nyrocms_composer']->cancelUrl($row); ?>"
	>
		<?php if ($canChangeTheme && count($themes) > 1): // @todo need implement and integration?>
			<span slot="nav">
				<label for="themeChoose"><?php echo $view['nyrodev']->trans('admin.content.themeSelectInput'); ?></label>
				<select id="themeChoose" name="theme">
					<?php if ($row->getParent()): ?>
						<option value=""
							<?php echo !$row->getTheme() ? 'selected' : ''; ?>
						><?php echo $view['nyrocms_composer']->getCssTheme($row); ?></option>
					<?php endif; ?>
					<?php foreach ($themes as $k => $v): ?>
						<option value="<?php echo $k; ?>"
							<?php echo $k == $row->getTheme() ? 'selected' : ''; ?>
						><?php echo $k; ?></option>
					<?php endforeach; ?>
				</select>
			</span>

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
		<?php if ($canChangeLang && count($langs) > 0): // @todo need implement and integration?>
			<span slot="nav">
				<label for="langSwitch"><?php echo $view['nyrodev']->trans('admin.content.lang'); ?></label>
				<select id="langSwitch" class="nyroComposerSelectAutoLocation">
					<?php foreach ($langs as $lg => $lang): ?>
						<option
							value="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', ['type' => $type, 'id' => $id, 'lang' => $lg]); ?>"
							<?php echo $lg === $lang ? 'selected' : ''; ?>
						><?php echo $lg; ?></option>
					<?php endforeach; ?>
				</select>
			</span>
		<?php endif; ?>

		<span slot="title"><?php echo $row; ?></span>
	</nyro-composer-top-panel>

	<nyro-composer-side-panel></nyro-composer-side-panel>

	<form id="composer" method="post" action="" class="<?php echo $view['nyrocms_composer']->getRenderCssTheme($row); ?>">
		<input type="hidden" name="theme" value="<?php echo $row->getTheme(); ?>" />

		<nyro-composer-workspace>
			<?php echo $view['nyrocms_composer']->render($row, true); ?>
		</nyro-composer-workspace>
		<button type="submit"><?php echo $view['translator']->trans('save', [], 'nyroComposer'); ?></button>
	</form>

	<textarea disabled id="uiTranslations"><?php echo json_encode($uiTranslations); ?></textarea>
</nyro-composer>
</body>
</html>