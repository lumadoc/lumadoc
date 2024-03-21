<?php

	namespace Lumadoc;


	class LinkGenerator
	{
		/**
		 * @return string
		 */
		public function getHomepageUrl()
		{
			return '?page=index';
		}


		/**
		 * @return string
		 */
		public function getPageUrl(PageId $pageId)
		{
			return '?page=' . rawurlencode((string) $pageId);
		}


		/**
		 * @param  string $fiddleId
		 * @return string
		 */
		public function getFiddleUrl(PageId $pageId, $fiddleId)
		{
			return '?cmd=fiddle&page=' . rawurlencode((string) $pageId) . '&fiddle=' . rawurlencode($fiddleId);
		}


		/**
		 * @return string
		 */
		public function getCssUrl()
		{
			return '?cmd=css';
		}


		/**
		 * @return string
		 */
		public function getFiddleCssUrl()
		{
			return '?cmd=fiddlecss';
		}
	}
