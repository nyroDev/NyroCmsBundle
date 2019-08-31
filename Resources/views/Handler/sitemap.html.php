<?php if (count($contents)): ?>
<ul<?php echo isset($isRoot) && $isRoot ? ' class="sitemap"' : ''; ?>>
	<?php foreach ($contents as $content): ?>
	<li>
		<?php if (isset($content['content']) && $content['content']): ?>
			<a href="<?php echo $content['content']->getGoUrl() ? $content['content']->getGoUrl() : $view['nyrocms']->getUrlFor($content['content']); ?>"<?php echo $content['content']->getGoBlank() ? ' target="_blank"' : ''; ?>><?php echo $content['content']->getTitle(); ?></a>
		<?php endif; ?>
		<?php if (isset($content['url'], $content['name'])): ?>
			<a href="<?php echo $content['url']; ?>"><?php echo $content['name']; ?></a>
		<?php endif; ?>
		<?php if (isset($content['contents']) && count($content['contents'])): ?>
			<?php echo $view->render('NyroDevNyroCmsBundle:Handler:sitemap.html.php', array(
                'contents' => $content['contents'],
            )); ?>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
