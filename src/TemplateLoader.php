<?php

	namespace Lumadoc;

	use Latte;
	use Latte\CompileException;
	use Nette\Http\Url;
	use Nette\Utils\Strings;


	class TemplateLoader implements Latte\ILoader
	{
		/** @var string */
		private $fiddleLayoutFile;

		/** @var LinkGenerator */
		private $linkGenerator;

		/** @var PageProvider */
		private $pageProvider;

		/** @var Latte\Loaders\FileLoader */
		private $fileLoader;


		/**
		 * @param string $fiddleLayoutFile
		 */
		public function __construct(
			$fiddleLayoutFile,
			LinkGenerator $linkGenerator,
			PageProvider $pageProvider
		)
		{
			$this->fiddleLayoutFile = $fiddleLayoutFile;
			$this->linkGenerator = $linkGenerator;
			$this->pageProvider = $pageProvider;
			$this->fileLoader = new Latte\Loaders\FileLoader;
		}


		/**
		 * @throws FiddleNotFoundException
		 * @throws PageNotFoundException
		 */
		public function getContent($entry)
		{
			$uri = $this->tryParseUri($entry);
			$page = NULL;
			$fiddle = NULL;

			if ($uri === NULL) {
				return $this->fileLoader->getContent($entry);

			} elseif ($uri->path === Page::UriPath) {
				$page = $this->loadPage($uri->getQueryParameter('page'));

			} elseif ($uri->path === PageFiddle::UriPath) {
				$page = $this->loadPage($uri->getQueryParameter('page'));
				$fiddle = $this->loadFiddleId($uri->getQueryParameter('fiddle'));

			} else {
				throw new InvalidArgumentException('Invalid entry type.');
			}

			$content = $this->fileLoader->getContent($page->getFile());
			$tokens = Strings::split($content, '~({\/?example\s*[^}]*})~um', PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$result = '';
			/** @var array<int|string, string> $fiddles */
			$fiddles = [];
			$exampleCode = NULL;
			$exampleCodeLang = NULL;

			foreach ($tokens as $token) {
				if (Strings::startsWith($token, '{example')) {
					if ($exampleCode === NULL) {
						$exampleCode = '';
						$exampleCodeLang = Strings::trim(Strings::substring($token, 9, -1));

						if ($exampleCodeLang === '') {
							$exampleCodeLang = 'latte';
						}

					} else {
						throw new ParseException("Unexpected start macro {example}. Examples cannot be nested.");
					}

				} elseif (Strings::startsWith($token, '{/example')) {
					if ($exampleCode !== NULL) { // in example
						if ($exampleCode === '') {
							throw new CompileException("Examples cannot be empty.");
						}

						if (preg_match('#^([\t ]*)\S#m', $exampleCode, $m)) { // remove & restore indentation
							$exampleCode = str_replace(["\r", "\n" . $m[1]], ['', "\n"], $exampleCode);
						}

						$exampleCode = ltrim(rtrim($exampleCode), "\n\r");
						$fiddleId = count($fiddles) + 1;
						$fiddles[$fiddleId] = $exampleCode;
						$result .= '<div class="fiddleEmbed">'
							. '<iframe class="fiddleEmbed__preview" src="' . $this->linkGenerator->getFiddleUrl($page->getId(), (string) $fiddleId) . '" loading="lazy" sandbox="allow-same-origin"></iframe>'
							. '<details class="fiddleEmbed__codePreview">'
							. '<summary class="fiddleEmbed__codePreviewButton">Show code</summary>'
							. '<pre class="fiddleEmbed__code">'
							. '<code class="language-' . \Latte\Runtime\Filters::escapeHtmlAttr($exampleCodeLang) . '">'
							. '{syntax off}' . \Latte\Runtime\Filters::escapeHtml($exampleCode) . '{/syntax}'
							. '</code>'
							. '</pre>'
							. '</details>'
							. '</div>';
						$exampleCode = NULL;
						$exampleCodeLang = NULL;

					} else {
						throw new ParseException("Unexpected end macro {/example} in template {$page->getFile()}");
					}

				} elseif ($exampleCode !== NULL) { // in example
					$exampleCode .= $token;

				} else {
					$result .= $token;
				}
			}

			if ($fiddle !== NULL) {
				if (!isset($fiddles[$fiddle])) {
					throw new FiddleNotFoundException("Unknow fiddle $fiddle");
				}

				return "{extends " . $this->fiddleLayoutFile . "}\n"
					. "{block content}\n"
					. $fiddles[$fiddle]
					. "\n{/block}\n";
			}

			return $result;
		}


		public function isExpired($entry, $time)
		{
			$uri = $this->tryParseUri($entry);

			if ($uri !== NULL) {
				try {
					$page = $this->loadPage($uri->getQueryParameter('page'));
					$entry = $page->getFile();

				} catch (PageNotFoundException $e) {
					// nothing
				}
			}

			return $this->fileLoader->isExpired($entry, $time);
		}


		public function getReferredName($entry, $referringFile)
		{
			$uri = $this->tryParseUri($entry);

			if ($uri !== NULL) {
				try {
					$page = $this->loadPage($uri->getQueryParameter('page'));
					$entry = $page->getFile();

				} catch (PageNotFoundException $e) {
					// nothing
				}
			}

			return $this->fileLoader->getReferredName($entry, $referringFile);
		}


		public function getUniqueId($entry)
		{
			$uri = $this->tryParseUri($entry);

			if ($uri !== NULL) {
				try {
					$page = $this->loadPage($uri->getQueryParameter('page'));
					return $page->getFile();

				} catch (PageNotFoundException $e) {
					// nothing
				}
			}

			return $this->fileLoader->getUniqueId($entry);
		}


		/**
		 * @param  string $entry
		 * @return Url|NULL
		 */
		private function tryParseUri($entry)
		{
			if (Strings::startsWith($entry, Lumadoc::UriScheme)) {
				$uri = new Url($entry);

				if ($uri->scheme === Lumadoc::UriScheme) {
					return $uri;
				}
			}

			return NULL;
		}


		/**
		 * @param  mixed $pageId
		 * @return Page
		 * @throws PageNotFoundException
		 */
		private function loadPage($pageId)
		{
			if (!is_string($pageId)) {
				throw new PageNotFoundException("Page ID must be string.");
			}

			if ($pageId === '') {
				throw new PageNotFoundException("Page ID must be non-empty-string.");
			}

			$page = $this->pageProvider->findPage(new PageId($pageId));

			if ($page === NULL) {
				throw new PageNotFoundException("Page {$pageId} not found.");
			}

			return $page;
		}


		/**
		 * @param  mixed $fiddleId
		 * @return string
		 * @throws FiddleNotFoundException
		 */
		private function loadFiddleId($fiddleId)
		{
			if (!is_string($fiddleId)) {
				throw new FiddleNotFoundException("Fiddle ID must be string.");
			}

			return $fiddleId;
		}
	}
