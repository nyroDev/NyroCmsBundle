<?php

if ($row->getContentHandler()) {
    $viewPrm = $view['nyrocms']->getHandler($row->getContentHandler())->prepareView($row);
    if (is_array($viewPrm) && isset($viewPrm['view']) && isset($viewPrm['vars'])) {
        echo $view->render($viewPrm['view'], $viewPrm['vars']);
    }
}
