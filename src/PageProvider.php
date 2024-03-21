<?php

	namespace Lumadoc;


	interface PageProvider
	{
		/**
		 * @return Page|NULL
		 */
		function findPage(PageId $pageId);
	}
