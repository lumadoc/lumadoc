<?php

	namespace Lumadoc;


	class PageFiddle
	{
		/** @var Page */
		private $page;

		/** @var non-empty-string */
		private $fiddleId;


		/**
		 * @param non-empty-string $fiddleId
		 */
		public function __construct(
			Page $page,
			$fiddleId
		)
		{
			$this->page = $page;
			$this->fiddleId = $fiddleId;
		}


		/**
		 * @return string
		 */
		public function getPageUrl()
		{
			return $this->page->getUrl();
		}


		/**
		 * @return non-empty-string
		 */
		public function getFile()
		{
			return $this->page->getFile();
		}


		/**
		 * @return non-empty-string
		 */
		public function getFiddleId()
		{
			return $this->fiddleId;
		}


		public function __toString()
		{
			return $this->page->getFile() . '#fiddle:' . $this->fiddleId;
		}
	}
