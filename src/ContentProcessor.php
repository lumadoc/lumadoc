<?php

	namespace Lumadoc;


	interface ContentProcessor
	{
		/**
		 * @param  string $content
		 * @return PageContent
		 */
		function processContent(
			Page $page,
			$content
		);
	}
