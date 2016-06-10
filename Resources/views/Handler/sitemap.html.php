<?php if (count($contents)): ?>
<ul<?php echo isset($isRoot) && $isRoot ? ' class="sitemap"' : '' ?>>
	<?php foreach ($contents as $content): ?>
	<li>
		<a href="<?php echo $content['content']->getGoUrl() ? $content['content']->getGoUrl() : $view['nyrocms']->getUrlFor($content['content']) ?>"<?php echo $content['content']->getGoBlank() ? ' target="_blank"' : '' ?>><?php echo $content['content']->getTitle() ?></a>
		<?php if (isset($content['contents']) && count($content['contents'])): ?>
		<?php echo $view->render('NyroDevNyroCmsBundle:Handler:sitemap.html.php', array(
            'contents' => $content['contents'],
        )) ?>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
