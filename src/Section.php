<?php

	namespace Lumadoc;


	class Section
	{
		/** @var non-empty-string|NULL */
		private $label;

		/** @var PageId|NULL */
		private $link;

		/** @var Page[] */
		private $pages;


		/**
		 * @param non-empty-string|NULL $label
		 * @param Page[] $pages
		 */
		public function __construct(
			$label,
			PageId $link = NULL,
			array $pages
		)
		{
			$this->label = $label;
			$this->link = $link;
			$this->pages = $pages;
		}


		/**
		 * @return non-empty-string|NULL
		 */
		public function getLabel()
		{
			return $this->label;
		}


		/**
		 * @return PageId|NULL
		 */
		public function getLink()
		{
			return $this->link;
		}


		/**
		 * @return Page[]
		 */
		public function getPages()
		{
			return $this->pages;
		}
	}
