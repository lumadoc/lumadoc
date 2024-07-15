<?php

	namespace Lumadoc;

	use Nette\Utils\Strings;


	class DefaultContentProcessor implements ContentProcessor
	{
		/** @var LinkGenerator */
		private $linkGenerator;


		public function __construct(LinkGenerator $linkGenerator)
		{
			$this->linkGenerator = $linkGenerator;
		}


		/**
		 * @throws ParseException
		 */
		public function processContent(Page $page, $content)
		{
			$tokens = Strings::split($content, '~({\/?example\s*[^}]*})~um', PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$result = '';
			/** @var array<non-empty-string, string> $fiddles */
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
							throw new ParseException("Examples cannot be empty.");
						}

						if (preg_match('#^([\t ]*)\S#m', $exampleCode, $m)) { // remove & restore indentation
							$exampleCode = str_replace(["\r", "\n" . $m[1]], ['', "\n"], $exampleCode);
						}

						$exampleCode = ltrim(rtrim($exampleCode), "\n\r");
						$fiddleId = 'f' . (count($fiddles) + 1);
						$fiddles[$fiddleId] = $exampleCode;
						$result .= '<div class="fiddleEmbed">'
							. '<iframe class="fiddleEmbed__preview" src="' . $this->linkGenerator->getFiddleUrl($page->getId(), $fiddleId) . '" style="height: ' . ($this->calculateFiddleHeight($exampleCode)) . 'rem" loading="lazy" sandbox="allow-same-origin"></iframe>'
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
						throw new ParseException("Unexpected end macro {/example}");
					}

				} elseif ($exampleCode !== NULL) { // in example
					$exampleCode .= $token;

				} else {
					$result .= $token;
				}
			}

			return new PageContent(
				$result,
				$fiddles
			);
		}


		/**
		 * @param  string $s
		 * @return int
		 */
		private function calculateFiddleHeight($s)
		{
			$s = Strings::normalizeNewLines($s);
			$s = str_replace("\n\n", "\n", $s);
			$s = trim($s);

			return substr_count($s, "\n") + 2;
		}
	}
