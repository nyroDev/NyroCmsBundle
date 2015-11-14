<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractHandler {
	
	/**
	 *
	 * @var ContentHandler
	 */
	protected $contentHandler;
	
	/**
	 *
	 * @var ContainerInterface 
	 */
	protected $container;
	
	public function __construct(ContentHandler $contentHandler, ContainerInterface $container) {
		$this->contentHandler = $contentHandler;
		$this->container = $container;
	}
	
	public function getAdminRouteName() {
		return 'sis_admin_hanlder_contents';
	}
	
	public function getAdminRoutePrm() {
		return array(
			'chid'=>$this->contentHandler->getId(),
		);
	}
	
	public function isReversePositionOrder() {
		return true;
	}
	
	public function hasIntro() {
		return false;
	}
	
	public function hasComposer() {
		return true;
	}
	
	public function hasContentSpecUrl() {
		return true;
	}
	
	public function hasHome() {
		return false;
	}
	
	protected function getFormFields($action) {
		return array();
	}
	
	protected function hasContentSpecificContent() {
		return false;
	}
	
	/**
	 * Get an application parameter
	 *
	 * @param string $parameter
	 * @return mixed
	 */
	public function getParameter($parameter) {
		return $this->container->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;
	}
	
	/**
     * Gets a service by id.
     *
     * @param string $id The service id
     * @return object The service
     */
	public function get($id) {
		return $this->container->get($id);
	}
	
	/**
	 * Get the translation for a given keyword
	 *
	 * @param string $key Translation key
	 * @param array $parameters Parameters to replace
	 * @param string $domain Translation domain
	 * @param string $locale Local to use
	 * @return string The translation
	 */
	public function trans($key, array $parameters = array(), $domain = 'messages', $locale = null) {
		return $this->get('translator')->trans($key, $parameters, $domain, $locale);
	}
	
    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = array(), $absolute = false) {
        return $this->container->get('nyrodev')->generateUrl($route, $parameters, $absolute);
    }
	
	/**
	 * 
	 * @return ContentRepositoryInterface
	 */
	public function getContentRepo() {
		return $this->get('nyrocms_db')->getContentRepository();
	}
	
	/**
	 * 
	 * @return ContentSpecRepositoryInterface
	 */
	public function getContentSpecRespository() {
		return $this->get('nyrocms_db')->getContentSpecRepository();
	}
	

	protected $contents = array();
	
	/**
	 * Get content by id
	 *
	 * @param int $id
	 * @return Content
	 */
	public function getContentById($id) {
		if (!isset($this->contents[$id]))
			$this->contents[$id] = $this->getContentRepo()->find($id);
		return $this->contents[$id];
	}
	
	public function formClb($action, ContentSpec $row, FormBuilder $form, array $langs = array(), array $translations = array()) {
		if (!$this->hasComposer()) {
			$after = 'validEnd';
			$content = $row->getContent();
			$translationsContent = array();
			foreach($translations as $lg=>$trs) {
				$translationsContent[$lg] = array();
				foreach($trs as $field=>$tr) {
					if ($field == 'content')
						$translationsContent[$lg] = json_decode($tr->getContent(), true);
				}
			}
			
			foreach($this->getFormFields($action) as $k=>$cfg) {
				$type = $cfg['type'];
				unset($cfg['type']);
				$translatable = false;
				if (isset($cfg['translatable'])) {
					$translatable = $cfg['translatable'];
					unset($cfg['translatable']);
				}
				$cfg['mapped'] = false;
				if (isset($content[$k])) {
					$cfg['data'] = $content[$k];
					if ($type == 'date')
						$cfg['data'] = new \DateTime($cfg['data']['date']);
				}
				$cfg['position'] = array('after'=>$after);
				$form->add($k, $type, $cfg);
				$after = $k;
				
				if ($translatable && count($langs)) {
					foreach($langs as $lg=>$lang) {
						$fieldName = 'lang_'.$lg.'_'.$k;
						$cfg['position']['after'] = $after;
						$data = isset($translationsContent[$lg]) && isset($translationsContent[$lg][$k]) ? $translationsContent[$lg][$k] : null;
						if ($data && $type == 'date' && !is_object($data))
							$data = new \DateTime($data['date']);
						$form->add($fieldName, $type, array_merge($cfg, array(
							'label'=>$cfg['label'].' '.strtoupper($lg),
							'data'=>$data
						)));
						$after = $fieldName;
					}
				}
			}
		}
	}
	
	public function flushClb($action, ContentSpec $row, Form $form) {
		if (!$this->hasComposer()) {
			$newContents = $newContentTexts = array();
			foreach($this->getFormFields($action) as $k=>$cfg) {
				$data = $form->get($k)->getData();
				$newContents[$k] = $data;
				if ($cfg['type'] == 'text' || $cfg['type'] == 'textarea' || $cfg['type'] == 'choice')
					$newContentTexts[] = $data;
			}
			$row->setContent($newContents);
			$row->setContentText(implode("\n", array_filter($newContentTexts)));
		}
	}
	
	public function afterFlushClb($response, $action, $row) {}
	
	public function flushLangClb($action, ContentSpec $row, Form $form, $lg) {
		if (!$this->hasComposer()) {
			$newContents = $newContentTexts = array();
			foreach($this->getFormFields($action) as $k=>$cfg) {
				$data = $form->get($k)->getData();
				if (isset($cfg['translatable']) && $cfg['translatable']) {
					$fieldName = 'lang_'.$lg.'_'.$k;
					$dataLg = $form->get($fieldName)->getData();
					if ($dataLg)
						$data = $dataLg;
				}
				$newContents[$k] = $data;
				if ($cfg['type'] == 'text' || $cfg['type'] == 'textarea' || $cfg['type'] == 'choice')
					$newContentTexts[] = $data;
			}
			$row->setContent($newContents);
			$row->setContentText(implode("\n", array_filter($newContentTexts)));
		}
	}
	
	public function deleteClb(ContentSpec $row) {}
	
	/**
	 *
	 * @var Request
	 */
	protected $request;
	
	/**
	 *
	 * @var boolean
	 */
	protected $isAdmin = false;
	
	public function init(Request $request = null, $isAdmin = false) {
		$this->request = $request;
		$this->isAdmin = $isAdmin;
	}
	
	protected $contentSpec = array();
	
	/**
	 * 
	 * @param int $id
	 * @param Content $content
	 * @param int $state
	 * @return ContentSpec
	 */
	public function getContentSpec($id, $locale = null, Content $content = null, $state = ContentSpec::STATE_ACTIVE) {
		if (!isset($this->contentSpec[$id])) {
			$this->contentSpec[$id] = $this->getContentSpecRespository()
										->getOneOrNullForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, array(
											'id'=>$id
										));
			
			if ($this->contentSpec[$id] && $locale) {
				$this->contentSpec[$id]->setTranslatableLocale($locale);
				$this->get('nyrocms_db')->refresh($this->contentSpec[$id]);
			}
		}
		return $this->contentSpec[$id];
	}
	
	public function getTotalContentSpec(Content $content = null, $state = ContentSpec::STATE_ACTIVE) {
		return $this->getContentSpecRespository()
						->countForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null);
	}
	
	protected $preparedView;
	public function prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null) {
		if (is_null($this->preparedView))
			$this->preparedView = $this->_prepareView($content, $handlerContent, $handlerAction);
		return $this->preparedView;
	}
	
	abstract protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null);
	
	protected $preparedHomeView;
	public function prepareHomeView(Content $content) {
		if (is_null($this->preparedHomeView))
			$this->preparedHomeView = $this->_prepareHomeView($content);
		return $this->preparedHomeView;
	}
	
	protected function _prepareHomeView(Content $content) {
		return array();
	}
	
}