<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $subject ?></title>
		<?php echo $view->render('NyroDevNyroCmsBundle:Tpl:emailStyles.html.php', array(
		)) ?>
	</head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    	<?php echo $view->render('NyroDevNyroCmsBundle:Tpl:emailBody.html.php', array(
			'locale'=>$locale,
			'content'=>$content
		)) ?>
    </body>
</html>