<?php

	namespace Lumadoc;


	class HttpController
	{
		/** @var Lumadoc */
		private $lumadoc;


		public function __construct(
			Lumadoc $lumadoc
		)
		{
			$this->lumadoc = $lumadoc;
		}


		/**
		 * @return void
		 */
		public function run(
			\Nette\Http\Request $request,
			\Nette\Http\Response $response
		)
		{
			$command = $request->getQuery('cmd');

			if ($command === NULL) { // show page
				$this->processShowPage($request, $response);

			} elseif ($command === 'fiddle') { // show CSS styles
				$this->processShowFiddle($request, $response);

			} elseif ($command === 'css') { // show CSS styles
				$this->processShowCss($response);

			} elseif ($command === 'fiddlecss') { // show CSS styles for fiddles
				$this->processShowFiddleCss($response);

			} else {
				$this->error($response, 'Page not found.', 404);
			}
		}


		/**
		 * @return void
		 */
		private function processShowPage(
			\Nette\Http\Request $request,
			\Nette\Http\Response $response
		)
		{
			$page = $request->getQuery('page');

			if (!is_string($page)) {
				$this->error($response, 'Page not found.', 404);
				return;
			}

			try {
				$response->setCode(200);
				$response->setContentType('text/html', 'utf-8');
				$this->lumadoc->showPage($page);

			} catch (PageNotFoundException $e) {
				$this->error($response, 'Page not found.', 404);
			}
		}


		/**
		 * @return void
		 */
		private function processShowFiddle(
			\Nette\Http\Request $request,
			\Nette\Http\Response $response
		)
		{
			$page = $request->getQuery('page');
			$fiddle = $request->getQuery('fiddle');

			if (!is_string($page)) {
				$this->error($response, 'Page not found.', 404);
				return;
			}

			if (!is_string($fiddle) || $fiddle === '') {
				$this->error($response, 'Fiddle not found.', 404);
				return;
			}

			try {
				$response->setCode(200);
				$response->setContentType('text/html', 'utf-8');
				$this->lumadoc->showFiddle($page, $fiddle);

			} catch (PageNotFoundException $e) {
				$this->error($response, 'Page not found.', 404);

			} catch (FiddleNotFoundException $e) {
				$this->error($response, 'Fiddle not found.', 404);
			}
		}


		/**
		 * @return void
		 */
		private function processShowCss(
			\Nette\Http\Response $response
		)
		{
			$response->setCode(200);
			$response->setContentType('text/css', 'utf-8');

			foreach ($this->lumadoc->getThemeCssFiles() as $cssFile) {
				readfile($cssFile);
			}
		}


		/**
		 * @return void
		 */
		private function processShowFiddleCss(
			\Nette\Http\Response $response
		)
		{
			$response->setCode(200);
			$response->setContentType('text/css', 'utf-8');

			foreach ($this->lumadoc->getFiddleCssFiles() as $cssFile) {
				readfile($cssFile);
			}
		}


		/**
		 * @param  string $message
		 * @param  int $code
		 * @return void
		 */
		private function error(
			\Nette\Http\Response $response,
			$message,
			$code
		)
		{
			$response->setCode($code);
			$response->setContentType('text/plain', 'utf-8');
			echo $message, "\n";
		}
	}
