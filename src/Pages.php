<?php

	namespace Lumadoc;

	use Nette\Utils\Finder;
	use Nette\Utils\Strings;


	class Pages implements PageProvider
	{
		/** @var Page[] */
		private $pages;


		/**
		 * @param Page[] $pages
		 */
		public function __construct(
			array $pages
		)
		{
			$this->pages = $pages;
		}


		public function findPage(PageId $pageId)
		{
			foreach ($this->pages as $page) {
				if ($page->getId()->equals($pageId)) {
					return $page;
				}
			}

			return NULL;
		}


		public function getParentPage(Page $page)
		{
			$parentId = $page->getId()->getParentId();

			if ($parentId === NULL) {
				return NULL;
			}

			return $this->findPage($parentId);
		}


		public function getChildrenPages(Page $page = NULL)
		{
			$children = [];

			if ($page !== NULL) {
				$parentPageId = (string) $page->getId() . '/';
				$parentPageLevel = substr_count($parentPageId, '/');

				foreach ($this->pages as $page) {
					$pageId = (string) $page->getId();

					if (Strings::startsWith($pageId, $parentPageId) && (substr_count($pageId, '/') === $parentPageLevel)) {
						$children[] = $page;
					}
				}

			} else {
				foreach ($this->pages as $page) {
					if ($page->getId()->isGlobal()) {
						$children[] = $page;
					}
				}
			}

			return $children;
		}


		public function getNavigation(Page $page)
		{
			$pages = [];
			$sectionLabel = NULL;
			$sectionLink = NULL;

			$parentPage = $this->getParentPage($page);

			if ($parentPage !== NULL) {
				$sectionLabel = $parentPage->getTitle();
				$sectionLink = $parentPage->getId();
			}

			$pages = $this->getChildrenPages($parentPage);

			usort($pages, function (Page $a, Page $b) {
				$aBase = $a->getId()->getBaseName();
				$bBase = $b->getId()->getBaseName();

				if ($aBase === $bBase) {
					return 0;
				}

				if ($aBase === 'index') {
					return -1;
				}

				if ($bBase === 'index') {
					return 1;
				}

				return strcmp($aBase, $bBase);
			});

			return [
				new Section(
					$sectionLabel,
					$sectionLink,
					$pages
				),
			];
		}


		/**
		 * @param  string $directory
		 * @return self
		 */
		public static function createFromDirectory($directory)
		{
			$directory = rtrim($directory, '/') . '/';
			$directoryLength = Strings::length($directory);
			$pages = [];
			$finder = Finder::findFiles('*.latte')
				->from($directory);

			foreach ($finder as $path => $file) {
				if (!($file instanceof \SplFileInfo)) {
					throw new InvalidStateException("File must be instance of " . \SplFileInfo::class);
				}

				if (!is_string($path)) {
					throw new InvalidStateException("File path must be string");
				}

				$basename = $file->getBasename();

				if (!is_string($basename)) {
					throw new InvalidStateException("File basename must be string");
				}

				if (Strings::startsWith($basename, '.') || Strings::startsWith($basename, '@')) {
					continue;
				}

				if (!Strings::startsWith($path, $directory)) {
					continue;
				}

				$url = ltrim(Strings::substring(dirname($path), $directoryLength) . '/' . $file->getBasename('.' . $file->getExtension()), '/');

				if ($url === '' || !PageId::isValid($url)) {
					continue;
				}

				$url = new PageId($url);
				$file = (string) $file;

				if ($file === '') {
					continue;
				}

				$pages[] = Page::createFromFile($url, $file);
			}

			usort($pages, function (Page $a, Page $b) {
				return strcmp((string) $a->getId(), (string) $b->getId());
			});

			return new self($pages);
		}
	}
