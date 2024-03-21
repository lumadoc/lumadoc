<?php

	namespace Lumadoc;

	use Latte;
	use Latte\CompileException;
	use Nette\Utils\Strings;


	class TemplateLoader implements Latte\ILoader
	{
		/** @var string */
		private $fiddleLayoutFile;

		/** @var LinkGenerator */
		private $linkGenerator;

		/** @var Latte\Loaders\FileLoader */
		private $fileLoader;


		/**
		 * @param string $fiddleLayoutFile
		 */
		public function __construct(
			$fiddleLayoutFile,
			LinkGenerator $linkGenerator
		)
		{
			$this->fiddleLayoutFile = $fiddleLayoutFile;
			$this->linkGenerator = $linkGenerator;
			$this->fileLoader = new Latte\Loaders\FileLoader;
		}


		/**
		 * @param  Page|PageFiddle|string $entry
		 * @throws FiddleNotFoundException
		 */
		public function getContent($entry)
		{
			if (is_string($entry)) {
				return $this->fileLoader->getContent($entry);
			}

			$pageUrl = NULL;

			if ($entry instanceof Page) {
				$pageUrl = $entry->getId();

			} elseif ($entry instanceof PageFiddle) {
				$pageUrl = $entry->getPageId();

			} else {
				throw new InvalidArgumentException('Invalid entry type.');
			}

			$content = $this->fileLoader->getContent($entry->getFile());
			$tokens = Strings::split($content, '~({\/?example\s*[^}]*})~um', PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$result = '';
			/** @var array<int|string, string> $fiddles */
			$fiddles = [];
			$exampleCode = NULL;
			$exampleCodeLang = NULL;

			foreach ($tokens as $token) {
				if (Strings::startsWith($token, '{example')) {
					if ($exampleCode === NULL) {
						$exampleCode = '';
						$exampleCodeLang = Strings::trim(Strings::substring($token, 9, -1));

						if ($exampleCodeLang === '') {
							$exampleCodeLang = 'latte';
						}

					} else {
						throw new ParseException("Unexpected start macro {example}. Examples cannot be nested.");
					}

				} elseif (Strings::startsWith($token, '{/example')) {
					if ($exampleCode !== NULL) { // in example
						if ($exampleCode === '') {
							throw new CompileException("Examples cannot be empty.");
						}

						if (preg_match('#^([\t ]*)\S#m', $exampleCode, $m)) { // remove & restore indentation
							$exampleCode = str_replace(["\r", "\n" . $m[1]], ['', "\n"], $exampleCode);
						}

						$exampleCode = ltrim(rtrim($exampleCode), "\n\r");
						$fiddleId = count($fiddles) + 1;
						$fiddles[$fiddleId] = $exampleCode;
						$result .= '<div class="fiddleEmbed">'
							. '<iframe class="fiddleEmbed__preview" src="' . $this->linkGenerator->getFiddleUrl($pageUrl, (string) $fiddleId) . '" loading="lazy" sandbox="allow-same-origin"></iframe>'
							. '<details class="fiddleEmbed__codePreview">'
							. '<summary class="fiddleEmbed__codePreviewButton">Show code</summary>'
							. '<pre class="fiddleEmbed__code">'
							. '<code class="language-' . \Latte\Runtime\Filters::escapeHtmlAttr($exampleCodeLang) . '">'
							. '{syntax off}' . \Latte\Runtime\Filters::escapeHtml($exampleCode) . '{/syntax}'
							. '</code>'
							. '</pre>'
							. '</details>'
							. '</div>';
						$exampleCode = NULL;
						$exampleCodeLang = NULL;

					} else {
						throw new ParseException("Unexpected end macro {/example} in template {$entry->getFile()}");
					}

				} elseif ($exampleCode !== NULL) { // in example
					$exampleCode .= $token;

				} else {
					$result .= $token;
				}
			}

			if ($entry instanceof PageFiddle) {
				$fiddleId = $entry->getFiddleId();

				if (!isset($fiddles[$fiddleId])) {
					throw new FiddleNotFoundException("Unknow fiddle $fiddleId");
				}

				return "{extends " . $this->fiddleLayoutFile . "}\n"
					. "{block content}\n"
					. $fiddles[$fiddleId]
					. "\n{/block}\n";
			}

			return $result;
		}


		/**
		 * @param  Page|PageFiddle|string $entry
		 * @param  int $time
		 * @return bool
		 */
		public function isExpired($entry, $time)
		{
			if (($entry instanceof Page) || ($entry instanceof PageFiddle)) {
				$entry = $entry->getFile();
			}

			return $this->fileLoader->isExpired($entry, $time);
		}


		/**
		 * @param  Page|PageFiddle|string $entry
		 * @param  string $referringFile
		 */
		public function getReferredName($entry, $referringFile)
		{
			if (($entry instanceof Page) || ($entry instanceof PageFiddle)) {
				$entry = $entry->getFile();
			}

			return $this->fileLoader->getReferredName($entry, $referringFile);
		}


		/**
		 * @param  Page|PageFiddle|string $entry
		 */
		public function getUniqueId($entry)
		{
			if (($entry instanceof Page) || ($entry instanceof PageFiddle)) {
				return (string) $entry;
			}

			return $this->fileLoader->getUniqueId($entry);
		}
	}
