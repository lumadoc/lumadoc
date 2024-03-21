<?php

	namespace Lumadoc;

	use Nette\Utils\Strings;


	class PageAnnotations
	{
		/** @var array<string, string[]|string> */
		private $annotations;


		/**
		 * @param array<string, string[]|string> $annotations
		 */
		public function __construct(array $annotations)
		{
			$this->annotations = $annotations;
		}


		/**
		 * @param  string $name
		 * @return bool
		 */
		public function has($name)
		{
			return isset($this->annotations[$name]);
		}


		/**
		 * @param  string $name
		 * @return string
		 */
		public function get($name)
		{
			if (!isset($this->annotations[$name])) {
				throw new InvalidArgumentException("Annotation @{$name} not found for this page.");
			}

			if (is_array($this->annotations[$name])) {
				return isset($this->annotations[$name][0]) ? $this->annotations[$name][0] : '';
			}

			return $this->annotations[$name];
		}


		/**
		 * @param  string $name
		 * @return string[]
		 */
		public function getAll($name)
		{
			if (!isset($this->annotations[$name])) {
				throw new InvalidArgumentException("Annotation @{$name} not found for this page.");
			}

			if (!is_array($this->annotations[$name])) {
				return [$this->annotations[$name]];
			}

			return $this->annotations[$name];
		}


		/**
		 * @param  string $content
		 * @return self
		 */
		public static function createFromContent($content)
		{
			$filedoc = Strings::match($content, '~{\*.+\*}~sU');
			$annotations = [];

			if (isset($filedoc[0])) {
				$rawAnnotations = Strings::matchAll(Strings::substring($filedoc[0], 2, -2), "~@([a-zA-Z0-9-]+)\\s+([^@\\n\\r]*)~");

				foreach ($rawAnnotations as $rawAnnotation) {
					if (!isset($rawAnnotation[1])) {
						continue;
					}

					$name = (string) $rawAnnotation[1];
					$value = isset($rawAnnotation[2]) ? Strings::trim($rawAnnotation[2]) : '';

					if (isset($annotations[$name])) {
						if (!is_array($annotations[$name])) {
							$annotations[$name] = [$annotations[$name]];
						}

						$annotations[$name][] = $value;

					} else {
						$annotations[$name] = $value;
					}
				}
			}

			return new self($annotations);
		}
	}
