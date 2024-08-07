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


		/**
		 * @return Page[]
		 */
		public function findGlobals()
		{
			$result = [];

			foreach ($this->pages as $page) {
				if ($page->isGlobal()) {
					$result[] = $page;
				}
			}

			return $result;
		}


		/**
		 * @return Page[]
		 */
		public function findBySection(Section $section)
		{
			$sectionId = $section->getId();
			$result = [];

			foreach ($this->pages as $page) {
				if ($page->isInSection($sectionId)) {
					$result[] = $page;
				}
			}

			return $result;
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
