<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $subject ?></title>
		<?php echo $view->render($stylesTemplate, array(
			'locale'=>$locale,
			'content'=>$content,
			'dbContent'=>$dbContent,
		)) ?>
	</head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    	<?php echo $view->render($bodyTemplate, array(
			'locale'=>$locale,
			'content'=>$content,
			'dbContent'=>$dbContent,
		)) ?>
    </body>
</html>