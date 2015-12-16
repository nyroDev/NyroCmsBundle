<?php
if ($admin) {
	echo '<div class="composer_handler">';
	if (!isset($isWrapped) || !$isWrapped)
		echo '<input type="hidden" name="contents['.$nb.'][handler]" value="handler" />';
}
if ($row->getContentHandler()) {
	$viewPrm = $view['nyrocms']->getHandler($row->getContentHandler())->prepareView($row, $handlerContent);
	if (is_array($viewPrm) && isset($viewPrm['view']) && isset($viewPrm['vars'])) {
		echo $view->render($viewPrm['view'], $viewPrm['vars']);
	}
}
if ($admin)
	echo '</div>';