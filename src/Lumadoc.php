<?php

	namespace Lumadoc;


	class Lumadoc
	{
		const UriScheme = 'lumadoc';

		/** @var Settings */
		private $settings;

		/** @var PageProvider */
		private $pageProvider;

		/** @var \Latte\Engine */
		private $latte;

		/** @var LinkGenerator */
		private $linkGenerator;

		/** @var TemplateLoader */
		private $templateLoader;


		public function __construct(
			Settings $settings,
			ContentProcessor $contentProcessor,
			LinkGenerator $linkGenerator,
			PageProvider $pageProvider,
			\Latte\Engine $latte
		)
		{
			$this->settings = $settings;
			$this->pageProvider = $pageProvider;
			$this->linkGenerator = $linkGenerator;
			$this->templateLoader = new TemplateLoader(
				__DIR__ . '/templates/@layout-fiddle.latte',
				$contentProcessor,
				$this->pageProvider
			);
			$this->latte = clone $latte;
			$this->latte->setLoader($this->templateLoader);
			$this->latte->addProvider('coreParentFinder', function (\Latte\Runtime\Template $template) {
				if (!$template->getReferenceType()) {
					$layoutFile = $this->settings->getDirectory() . '/@layout.latte';

					if (is_dir($layoutFile)) {
						return $layoutFile;
					}

					return __DIR__ . '/templates/@layout.latte';
				}
			});
			$this->latte->addProvider('lumadocPageLinker', function ($pageId) {
				if (!($pageId instanceof PageId)) {
					try {
						$id = (string) $pageId;

						if ($id === '') {
							throw new InvalidIdException('PageId must be non-empty-string.');
						}

						$pageId = new PageId($id);

					} catch (InvalidIdException $e) {
						return '#invalid-page-id';
					}
				}

				return $this->linkGenerator->getPageUrl($pageId);
			});
			LatteMacros::install($this->latte->getCompiler());
		}


		/**
		 * @return void
		 * @throws PageNotFoundException
		 */
		public function renderPage(PageId $pageId)
		{
			$page = $this->pageProvider->findPage($pageId);

			if ($page === NULL) {
				throw new PageNotFoundException("Missing page '$pageId'");
			}

			$this->latte->render($page->toUri(), [
				'docName' => $this->settings->getDocName(),
				'linkGenerator' => $this->linkGenerator,
				'assets' => $this->getAssets(),
				'pages' => $this->pageProvider,
				'currentPage' => $page,
				'installation' => $this->getInstallationAssets($page, $this->settings->getInstallationBaseUrl()),
			]);
		}


		/**
		 * @param  non-empty-string $fiddleId
		 * @return void
		 * @throws PageNotFoundException
		 * @throws FiddleNotFoundException
		 */
		public function renderFiddle(PageId $pageId, $fiddleId)
		{
			$page = $this->pageProvider->findPage($pageId);

			if ($page === NULL) {
				throw new PageNotFoundException("Missing page '$pageId'");
			}

			$fiddle = new PageFiddle($page, $fiddleId);
			$this->latte->render($fiddle->toUri(), [
				'docName' => $this->settings->getDocName(),
				'linkGenerator' => $this->linkGenerator,
				'page' => $page,
				'fiddleId' => $fiddleId,
				'assets' => $this->getFiddleAssets($page),
			]);
		}


		/**
		 * @return string[]
		 */
		public function getThemeCssFiles()
		{
			$res = [];
			$stylesFile = $this->settings->getDirectory() . '/styles.css';

			if (is_file($stylesFile)) {
				$res[] = $stylesFile;

			} else {
				$res[] = __DIR__ . '/templates/styles.css';
			}

			$themeFile = $this->settings->getDirectory() . '/theme.css';

			if (is_file($themeFile)) {
				$res[] = $themeFile;
			}

			$res[] = __DIR__ . '/templates/prism.css';

			return $res;
		}


		/**
		 * @return string[]
		 */
		public function getFiddleCssFiles()
		{
			$res = [];
			$stylesFile = $this->settings->getDirectory() . '/fiddles.css';

			if (is_file($stylesFile)) {
				$res[] = $stylesFile;
			}

			return $res;
		}


		/**
		 * @return \stdClass
		 */
		private function getAssets()
		{
			return (object) [
				'stylesheets' => [
					$this->linkGenerator->getCssUrl(),
				],
				'scripts' => [
					"https://unpkg.com/prismjs@v1.x/components/prism-core.min.js",
					"https://unpkg.com/prismjs@v1.x/plugins/autoloader/prism-autoloader.min.js",
				],
			];
		}


		/**
		 * @return \stdClass
		 */
		private function getFiddleAssets(Page $page)
		{
			$installationAssets = $this->getInstallationAssets($page, $this->settings->getAssetsBaseUrl());
			$assets = (object) [
				'stylesheets' => array_merge([
						$this->linkGenerator->getFiddleCssUrl(),
					],
					$installationAssets->stylesheets
				),
				'scripts' => $installationAssets->scripts,
				'scriptsInHead' => $installationAssets->scriptsInHead,
			];

			return $assets;
		}


		/**
		 * @param  string $baseUrl
		 * @return \stdClass
		 */
		private function getInstallationAssets(Page $page, $baseUrl)
		{
			$assets = (object) [
				'stylesheets' => [
				],
				'scripts' => [
				],
				'scriptsInHead' => [
				],
			];

			if ($page->hasAnnotation('lumadoc-css')) {
				foreach ($page->getAnnotationValues('lumadoc-css') as $assetUrl) {
					if (!\Nette\Utils\Validators::isUrl($assetUrl)) {
						$assetUrl = $baseUrl . '/' . $assetUrl;
					}

					$assets->stylesheets[] = $assetUrl;
				}
			}

			if ($page->hasAnnotation('lumadoc-js')) {
				foreach ($page->getAnnotationValues('lumadoc-js') as $assetUrl) {
					if (!\Nette\Utils\Validators::isUrl($assetUrl)) {
						$assetUrl = $baseUrl . '/' . $assetUrl;
					}

					$assets->scripts[] = $assetUrl;
				}
			}

			if ($page->hasAnnotation('lumadoc-js-head')) {
				foreach ($page->getAnnotationValues('lumadoc-js-head') as $assetUrl) {
					if (!\Nette\Utils\Validators::isUrl($assetUrl)) {
						$assetUrl = $baseUrl . '/' . $assetUrl;
					}

					$assets->scriptsInHead[] = $assetUrl;
				}
			}

			return $assets;
		}


		/**
		 * @template T
		 * @param  T|FALSE $value
		 * @return T|NULL
		 */
		public static function falseToNull($value)
		{
			return $value !== FALSE ? $value : NULL;
		}


		/**
		 * @return self
		 */
		public static function create(
			Settings $settings,
			\Latte\Engine $latte
		)
		{
			$linkGenerator = new LinkGenerator;

			return new self(
				$settings,
				new DefaultContentProcessor($linkGenerator),
				$linkGenerator,
				Pages::createFromDirectory($settings->getDirectory()),
				$latte
			);
		}
	}
