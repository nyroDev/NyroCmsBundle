<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface;
use NyroDev\UtilityBundle\Controller\AbstractAdminController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
		return 'nyrocms_admin_handler_contents';
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
	
	public function isIntroRequired() {
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
	public function getParameter($parameter, $default = null) {
		$value = $this->container->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;
		return !is_null($value) ? $value : $default;
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
				if ($type == FileType::class && isset($cfg['data']))
					unset($cfg['data']);
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
						if ($type == FileType::class)
							$data = null;
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
	
	/**
	 * Get the upload directory
	 *
	 * @return string
	 */
	public function getUploadRootDir() {
		return $this->getParameter('kernel.root_dir').'/../web/'.$this->getUploadDir();
	}

	/**
	 * Get the upload directory web name
	 *
	 * @return string 
	 */
	public function getUploadDir() {
		return 'uploads/contentHandler';
	}
	
	public function flushClb($action, ContentSpec $row, Form $form) {
		if (!$this->hasComposer()) {
			$newContents = $newContentTexts = array();
			foreach($this->getFormFields($action) as $k=>$cfg) {
				$data = $form->get($k)->getData();
				if ($cfg['type'] == FileType::class) {
					$newContents[$k] = $this->handleFileUpload($k, $data, $action, $row);
				} else {
					$newContents[$k] = $data;
					if ($cfg['type'] == TextType::class ||
						$cfg['type'] == TextareaType::class ||
						$cfg['type'] == ChoiceType::class) {
							$newContentTexts[] = $data;
					}
				}
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
				$data = $dataLg = $form->get($k)->getData();
				$fieldName = $k;
				if (isset($cfg['translatable']) && $cfg['translatable']) {
					$fieldName = 'lang_'.$lg.'_'.$k;
					$dataLg = $form->get($fieldName)->getData();
					if ($dataLg)
						$data = $dataLg;
				}
				if ($cfg['type'] == FileType::class) {
					$newContents[$k] = $this->handleFileUpload($k, $dataLg, $action, $row, $fieldName);
				} else {
					$newContents[$k] = $data;
					if ($cfg['type'] == TextType::class ||
						$cfg['type'] == TextareaType::class ||
						$cfg['type'] == ChoiceType::class) {
							$newContentTexts[] = $data;
					}
				}
			}
			$row->setContent($newContents);
			$row->setContentText(implode("\n", array_filter($newContentTexts)));
		}
	}
	
	protected $fileUploaded = array();
	protected function handleFileUpload($field, $data, $action, ContentSpec $row, $fieldForm = null) {
		$fieldForm = is_null($fieldForm) ? $fieldForm : $field;
		if (!isset($this->fileUploaded[$fieldForm])) {
			$this->fileUploaded[$fieldForm] = $row->getInContent($field);
			/* @var $data UploadedFile */
			if ($data) {
				// We have a file upload, handle it
				$rootDir = $this->getUploadRootDir();

				$fs = new Filesystem();
				if (!$fs->exists($rootDir))
					$fs->mkdir($rootDir);

				// Remove current files
				$this->deleteFileClb($row, $field);

				// Transfer new File
				$destPath = $this->get('nyrodev')->getUniqFileName($rootDir, $data->getClientOriginalName());
				$data->move($rootDir, $destPath);

				$this->fileUploaded[$fieldForm] = $destPath;
			}
		}
		return $this->fileUploaded[$fieldForm];
	}
	
	public function deleteClb(ContentSpec $row) {
		foreach($this->getFormFields(AbstractAdminController::ADD) as $k=>$cfg) {
			if ($cfg['type'] == FileType::class) {
				$this->deleteFileClb($row, $k);
			}
		}
	}
	
	protected function deleteFileClb(ContentSpec $row, $field) {
		$file = $row->getInContent($field);
		if ($file) {
			$fs = new Filesystem();
			$filePath = $this->getUploadRootDir().'/'.$file;
			if ($fs->exists($filePath)) {
				$fs->remove($filePath);
				$this->get('nyrodev_image')->removeCache($filePath);
			}
		}
	}
	
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
	
	public function getContentSpecs(Content $content = null, $start = null, $limit = null, array $where = array(), $state = ContentSpec::STATE_ACTIVE) {
		return $this->getContentSpecRespository()
						->getForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, $where, array('position'=>$this->isReversePositionOrder() ? 'DESC' : 'ASC'), $start, $limit);
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