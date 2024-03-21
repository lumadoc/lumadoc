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
		 * @param  string $pageId
		 * @return string
		 */
		public function getPageUrl($pageId)
		{
			return '?page=' . rawurlencode($pageId);
		}


		/**
		 * @param  string $pageId
		 * @param  string $fiddleId
		 * @return string
		 */
		public function getFiddleUrl($pageId, $fiddleId)
		{
			return '?cmd=fiddle&page=' . rawurlencode($pageId) . '&fiddle=' . rawurlencode($fiddleId);
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
