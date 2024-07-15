<?php

	namespace Lumadoc;


	class PageContent
	{
		/** @var string */
		private $content;

		/** @var array<non-empty-string, string> */
		private $fiddles;


		/**
		 * @param string $content
		 * @param array<non-empty-string, string> $fiddles
		 */
		public function __construct(
			$content,
			array $fiddles
		)
		{
			$this->content = $content;
			$this->fiddles = $fiddles;
		}


		/**
		 * @return string
		 */
		public function getContent()
		{
			return $this->content;
		}


		/**
		 * @param  non-empty-string $fiddleId
		 * @return string
		 * @throws FiddleNotFoundException
		 */
		public function getFiddle($fiddleId)
		{
			if (isset($this->fiddles[$fiddleId])) {
				return $this->fiddles[$fiddleId];
			}

			throw new FiddleNotFoundException("Unknow fiddle $fiddleId");
		}
	}
