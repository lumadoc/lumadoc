<?php

	namespace Lumadoc;

	use Nette\Utils\Validators;


	class Lumadoc
	{
		/** @var string */
		private $docName;

		/** @var Section[] */
		private $sections;

		/** @var string */
		private $directory;

		/** @var string */
		private $assetsBaseUrl;

		/** @var string */
		private $installationBaseUrl;

		/** @var \Latte\Engine */
		private $latte;

		/** @var LinkGenerator */
		private $linkGenerator;

		/** @var TemplateLoader */
		private $templateLoader;


		/**
		 * @param string $docName
		 * @param Section[] $sections
		 * @param string $directory
		 * @param string $assetsBaseUrl
		 * @param string $installationBaseUrl
		 */
		public function __construct(
			$docName,
			array $sections,
			$directory,
			$assetsBaseUrl,
			$installationBaseUrl,
			\Latte\Engine $latte
		)
		{
			$this->docName = $docName;
			$this->sections = $sections;
			$this->directory = $directory;
			$this->assetsBaseUrl = rtrim($assetsBaseUrl, '/');
			$this->installationBaseUrl = rtrim($installationBaseUrl, '/');
			$this->linkGenerator = new LinkGenerator;
			$this->templateLoader = new TemplateLoader(
				__DIR__ . '/templates/@layout-fiddle.latte',
				$this->linkGenerator
			);
			$this->latte = clone $latte;
			$this->latte->setLoader($this->templateLoader);
			$this->latte->addProvider('coreParentFinder', function (\Latte\Runtime\Template $template) {
				if (!$template->getReferenceType()) {
					$layoutFile = $this->directory . '/@layout.latte';

					if (is_dir($layoutFile)) {
						return $layoutFile;
					}

					return __DIR__ . '/templates/@layout.latte';
				}
			});
		}


		/**
		 * @return void
		 * @throws PageNotFoundException
		 */
		public function renderPage(PageId $pageId)
		{
			$page = $this->loadPage($pageId);

			if ($page === NULL) {
				throw new PageNotFoundException("Missing page '$pageId'");
			}

			$this->latte->render($page, [
				'docName' => $this->docName,
				'linkGenerator' => $this->linkGenerator,
				'assets' => $this->getAssets(),
				'sections' => $this->sections,
				'pages' => Pages::createFromDirectory($this->directory),
				'currentPage' => $page,
				'installation' => $this->getInstallationAssets($page, $this->installationBaseUrl),
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
			$page = $this->loadPage($pageId);

			if ($page === NULL) {
				throw new PageNotFoundException("Missing page '$pageId'");
			}

			$fiddle = new PageFiddle($page, $fiddleId);
			$this->latte->render($fiddle, [
				'docName' => $this->docName,
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
			$stylesFile = $this->directory . '/styles.css';

			if (is_file($stylesFile)) {
				$res[] = $stylesFile;

			} else {
				$res[] = __DIR__ . '/templates/styles.css';
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
			$stylesFile = $this->directory . '/fiddles.css';

			if (is_file($stylesFile)) {
				$res[] = $stylesFile;
			}

			return $res;
		}


		/**
		 * @return Page|NULL
		 */
		private function loadPage(PageId $pageId)
		{
			$file = $this->directory . '/' . $pageId . '.latte';

			if (!is_file($file)) {
				return NULL;
			}

			return Page::createFromFile(
				$pageId,
				$file
			);
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
			$installationAssets = $this->getInstallationAssets($page, $this->assetsBaseUrl);
			$assets = (object) [
				'stylesheets' => array_merge([
						$this->linkGenerator->getFiddleCssUrl(),
					],
					$installationAssets->stylesheets
				),
				'scripts' => $installationAssets->scripts,
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
	}
