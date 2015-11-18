<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;

class MainService extends AbstractService {
	
	protected $handlers = array();
	public function getHandler(\NyroDev\NyroCmsBundle\Model\ContentHandler $contentHandler) {
		if (!isset($this->handlers[$contentHandler->getId()])) {
			$class = $contentHandler->getClass();
			if (!class_exists($class))
				throw new \RuntimeException($class.' not found when trying to create handler.');
			
			$this->handlers[$contentHandler->getId()] = new $class($contentHandler, $this->container);
		}
		return $this->handlers[$contentHandler->getId()];
	}
	
	protected $activeIds = array();
	public function setActiveIds($activeIds) {
		$this->activeIds = $activeIds;
	}
	public function getActiveIds() {
		return $this->activeIds;
	}
	
	public function getUrlFor($object, $absolute = false, array $prm = array()) {
		$ret = '#';
		
		return $ret;
	}

	
	public function getDateFormOptions() {
		return array(
			'widget'=>'single_text',
			'format'=>'dd/MM/yyyy',
			'attr'=>array(
				'class'=>'datepicker',
			)
		);
	}
	
	public function getTinymceOptions() {
		return array(
			'tinymcePlugins'=>'template,importcss',
			'tinymce'=>array(
				'width'=>787,
				'body_class'=>'cont',
				'content_css'=>json_encode(array(
					$this->container->get('templating.helper.assets')->getUrl('css/global.css'),
					$this->container->get('templating.helper.assets')->getUrl('css/responsiveGlobal.css'),
				)),
				'importcss_append'=>true,
				'importcss_selector_filter'=>'.ed_',
				'style_formats'=>array(
					array('title'=>'Titre', 'block'=>'h1'),
					array('title'=>'Sous-Titre', 'block'=>'h2'),
					array('title'=>'Sous-Titre 2', 'block'=>'h3'),
					array('title'=>'Paragraphe', 'block'=>'p'),
					array('title'=>'Légende', 'inline'=>'small'),
				),
				'link_class_list'=>array(
					array('title'=>'Aucun', 'value'=>''),
					array('title'=>'Bouton', 'value'=>'but'),
				),
				/*
				'templates'=>json_encode(array(
					array(
						'title'=>'Onglet',
						'description'=>'Onglet dans une page',
						'content'=>'<p>&nbsp;</p><section class="tabbed switchable">'
						. '<nav><a href="#content1">Contenu 1</a><a href="#content2">Contenu 2</a><a href="#content3">Contenu 3</a></nav>'
						. '<div id="content1"><h3>Contenu 1</h3><p>Le contenu 1<br />Le contenu 1<br />Le contenu 1<br />Le contenu 1<br /></p></div>'
						. '<div id="content2"><h3>Contenu 2</h3><p>Le contenu 2<br />Le contenu 2<br />Le contenu 2<br />Le contenu 2<br /></p></div>'
						. '<div id="content3"><h3>Contenu 3</h3><p>Le contenu 3<br />Le contenu 3<br />Le contenu 3<br />Le contenu 3<br /></p></div>'
						. '</section><br /><p>&nbsp;</p>',
					),
					array(
						'title'=>'2 colonnes',
						'description'=>'Texte affiché sur 2 colonnes',
						'content'=>'<p>&nbsp;</p><section class="columns_2">'
						. '<div class="col">'
						. '<p>Le contenu 1<br />Le contenu 1<br />Le contenu 1<br />Le contenu 1<br /></p>'
						. '<p>Le contenu 2<br />Le contenu 2<br />Le contenu 2<br />Le contenu 2<br /></p>'
						. '<p>Le contenu 3<br />Le contenu 3<br />Le contenu 3<br />Le contenu 3<br /></p>'
						. '</div>'
						. '<div class="col">'
						. '<p>Le contenu 1<br />Le contenu 1<br />Le contenu 1<br />Le contenu 1<br /></p>'
						. '<p>Le contenu 2<br />Le contenu 2<br />Le contenu 2<br />Le contenu 2<br /></p>'
						. '<p>Le contenu 3<br />Le contenu 3<br />Le contenu 3<br />Le contenu 3<br /></p>'
						. '</div>'
						. '</section><br /><p>&nbsp;</p>'
					),
				)),
				 */
			),
		);
	}
	
	public function getLightTinymceOptions() {
		return array(
			'tinymce'=>array(
				'width'=>'100%',
				'height'=>200,
				'menubar'=>'false',
				'statusbar'=>'false',
				'toolbar'=>'undo redo | styleselect | bold italic | bullist numlist outdent indent',
				'valid_elements'=>'h1,h2,p,br,ul,ol,li,strong,em',
				'style_formats'=>array(
					array('title'=>'Titre', 'block'=>'h1'),
					array('title'=>'Sous-Titre', 'block'=>'h2'),
					array('title'=>'Paragraphe', 'block'=>'p'),
				),
			),
		);
	}
	
	public function sendEmail($to, $subject, $content, $from = null, $locale = null) {
        $response = $this->get('templating')->renderResponse('NyroDevNyroCmsBundle:Tpl:email.html.php', array(
			'subject'=>$subject,
			'locale'=>$locale ? $locale : $this->getLocale(),
			'content'=>$content,
		));
		$html = $response->getContent();
		$text = $this->get('nyrodev')->html2text($html);
		
		if (!$from)
			$from = $this->getParameter('noreply_email');
		
		$msg = $this->get('mailer')->createMessage()
					->setTo($to)
					->setSubject($subject)
					->setFrom($from)
					->setBody($text)
					->addPart($html, 'text/html');
		
		return $this->get('mailer')->send($msg);
	}
	
	public function getLocale() {
		return $this->getRequest()->getLocale();
	}
	
	public function getLocales() {
		return explode('|', $this->getParameter('locales'));
	}
	
	protected $pathInfoObject;
	public function setPathInfoObject($object) {
		$this->pathInfoObject = $object;
	}
	
	public function getPathInfo() {
		$request = $this->getRequest();
		return array(
			'route'=>$request->get('_route'),
			'routePrm'=>$request->get('_route_params'),
			'object'=>$this->pathInfoObject
		);
	}
	
	public function getLocalesUrl($pathInfo, $absolute = false, $onlyLangs = null) {
		$ret = array();
		$defaultLocale = $this->getParameter('locale');
		$locales = $this->getLocales();
		$curLocale = $this->getLocale();
		if ($onlyLangs && !is_array($onlyLangs))
			$onlyLangs = explode(',', $onlyLangs);
		
		$isObjectPage = isset($pathInfo['object']) && $pathInfo['object'];
		
		foreach($locales as $locale) {
			if ($locale != $curLocale && ($locale == $defaultLocale || empty($onlyLangs) || in_array($locale, $onlyLangs))) {
				$prm = array('_locale'=>$locale);
				if (!$pathInfo['route']) {
					$ret[$locale] = $this->generateUrl('_homepage_noLocale', array(), $absolute);
				} else if ($pathInfo['route'] == '_homepage_noLocale' && $curLocale == $defaultLocale) {
					$ret[$locale] = $this->generateUrl('_homepage', array_merge($pathInfo['routePrm'], $prm), $absolute);
				} else if ($pathInfo['route'] == '_homepage' && $locale == $defaultLocale) {
					$ret[$locale] = $this->generateUrl('_homepage_noLocale', array(), $absolute);
				} else if ($isObjectPage) {
					$pathInfo['object']->setTranslatableLocale($locale);
					$this->get('nyrocms_db')->refresh($pathInfo['object']);
					$ret[$locale] = $this->getUrlFor($pathInfo['object'], $absolute, $prm);
				} else {
					$ret[$locale] = $this->generateUrl($pathInfo['route'], array_merge($pathInfo['routePrm'], $prm), $absolute);
				}
			}
		}
		return $ret;
	}
	
}