<?php

	namespace Lumadoc;

	use Latte;
	use Nette\Http\Url;
	use Nette\Utils\Strings;


	class TemplateLoader implements Latte\ILoader
	{
		/** @var string */
		private $fiddleLayoutFile;

		/** @var ContentProcessor */
		private $contentProcessor;

		/** @var PageProvider */
		private $pageProvider;

		/** @var Latte\Loaders\FileLoader */
		private $fileLoader;


		/**
		 * @param string $fiddleLayoutFile
		 */
		public function __construct(
			$fiddleLayoutFile,
			ContentProcessor $contentProcessor,
			PageProvider $pageProvider
		)
		{
			$this->fiddleLayoutFile = $fiddleLayoutFile;
			$this->contentProcessor = $contentProcessor;
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

			try {
				$pageContent = $this->contentProcessor->processContent($page, $content);

			} catch (ParseException $e) {
				throw new ParseException("Parsing of file {$page->getFile()} failed.", 0, $e);
			}

			if ($fiddle !== NULL) {
				$fiddleContent = $pageContent->getFiddle($fiddle);

				return "{extends " . $this->fiddleLayoutFile . "}\n"
					. "{block content}\n"
					. $fiddleContent
					. "\n{/block}\n";
			}

			return $pageContent->getContent();
		}


		public function isExpired($entry, $time)
		{
			$entry = $this->getPageFile($entry);
			return $this->fileLoader->isExpired($entry, $time);
		}


		public function getReferredName($entry, $referringFile)
		{
			$entry = $this->getPageFile($entry);
			$referringFile = $this->getPageFile($referringFile);
			return $this->fileLoader->getReferredName($entry, $referringFile);
		}


		public function getUniqueId($entry)
		{
			$uri = $this->tryParseUri($entry);

			if ($uri !== NULL) {
				try {
					$this->loadPage($uri->getQueryParameter('page'));
					return (string) $uri;

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
		 * @param  string $entry
		 * @return string
		 */
		private function getPageFile($entry)
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

			return $entry;
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
		 * @return non-empty-string
		 * @throws FiddleNotFoundException
		 */
		private function loadFiddleId($fiddleId)
		{
			if (!is_string($fiddleId)) {
				throw new FiddleNotFoundException("Fiddle ID must be string.");
			}

			if ($fiddleId === '') {
				throw new PageNotFoundException("Fiddle ID must be non-empty-string.");
			}

			return $fiddleId;
		}
	}
