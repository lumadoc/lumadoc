<?php

	namespace Lumadoc;

	use Nette\Utils\FileSystem;
	use Nette\Utils\Strings;
	use Nette\Utils\Validators;


	class Page
	{
		/** @var PageId */
		private $id;

		/** @var non-empty-string */
		private $file;

		/** @var non-empty-string */
		private $title;

		/** @var non-empty-string|NULL */
		private $section;

		/** @var PageAnnotations */
		private $annotations;


		/**
		 * @param non-empty-string $file
		 * @param non-empty-string $title
		 * @param non-empty-string|NULL $section
		 */
		public function __construct(
			PageId $id,
			$file,
			$title,
			$section,
			PageAnnotations $annotations
		)
		{
			$this->id = $id;
			$this->file = $file;
			$this->title = $title;
			$this->section = $section;
			$this->annotations = $annotations;
		}


		/**
		 * @return PageId
		 */
		public function getId()
		{
			return $this->id;
		}


		/**
		 * @return non-empty-string
		 */
		public function getFile()
		{
			return $this->file;
		}


		/**
		 * @return non-empty-string
		 */
		public function getTitle()
		{
			return $this->title;
		}


		/**
		 * @param  non-empty-string $sectionId
		 * @return bool
		 */
		public function isInSection($sectionId)
		{
			return $this->section === $sectionId;
		}


		/**
		 * @return bool
		 */
		public function isGlobal()
		{
			return $this->section === NULL;
		}


		/**
		 * @param  string $name
		 * @return bool
		 */
		public function hasAnnotation($name)
		{
			return $this->annotations->has($name);
		}


		/**
		 * @param  string $name
		 * @return string
		 */
		public function getAnnotation($name)
		{
			return $this->annotations->get($name);
		}


		/**
		 * @param  string $name
		 * @return string[]
		 */
		public function getAnnotationValues($name)
		{
			return $this->annotations->getAll($name);
		}


		/**
		 * @return bool
		 */
		public function isActive(self $page)
		{
			return $this->id->equals($page->id);
		}


		public function __toString()
		{
			return $this->file;
		}


		/**
		 * @param  non-empty-string $file
		 * @return self
		 */
		public static function createFromFile(
			PageId $pageId,
			$file
		)
		{
			$section = Lumadoc::falseToNull(Strings::before((string) $pageId, '/'));
			$title = Strings::firstUpper(basename($pageId));

			$content = FileSystem::read($file);
			$annotations = PageAnnotations::createFromContent($content);

			if ($annotations->has('lumadoc-section')) {
				$section = $annotations->get('lumadoc-section');
			}

			if ($annotations->has('lumadoc-title')) {
				$title = $annotations->get('lumadoc-title');
			}

			return new self(
				$pageId,
				$file,
				$title !== '' ? $title : $file,
				$section !== '' ? $section : NULL,
				$annotations
			);
		}


		/**
		 * @param  string $url
		 * @return bool
		 */
		public static function isUrlValid($url)
		{
			if ($url === '') {
				return FALSE;
			}

			return Validators::is($url, 'pattern:[a-zA-Z0-9][a-zA-Z0-9-]*(\/[a-zA-Z0-9][a-zA-Z0-9-]*)*');
		}
	}
