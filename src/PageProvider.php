<?php

	namespace Lumadoc;


	interface PageProvider
	{
		/**
		 * @return Page|NULL
		 */
		function findPage(PageId $pageId);


		/**
		 * @return Page|NULL
		 */
		function getParentPage(Page $page);


		/**
		 * @return Page[]
		 */
		function getChildrenPages(Page $page = NULL);


		/**
		 * @return Section[]
		 */
		function getNavigation(Page $page);
	}
