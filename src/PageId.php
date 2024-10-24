<?php

	namespace Lumadoc;

	use Nette\Utils\Strings;
	use Nette\Utils\Validators;


	class PageId
	{
		/** @var non-empty-string */
		private $id;


		/**
		 * @param non-empty-string $id
		 */
		public function __construct($id)
		{
			if (!self::isValid($id)) {
				throw new InvalidIdException("Page ID '$id' is invalid.");
			}

			$this->id = $id;
		}


		/**
		 * @return bool
		 */
		public function equals(self $b)
		{
			return $this->id === $b->id;
		}


		/**
		 * @return non-empty-string
		 */
		public function getId()
		{
			return $this->id;
		}


		/**
		 * @return bool
		 */
		public function isGlobal()
		{
			return !Strings::contains($this->id, '/');
		}


		/**
		 * @return self|NULL
		 */
		public function getParentId()
		{
			if ($this->isGlobal()) {
				return NULL;
			}

			$parentId = Lumadoc::falseToNull(Strings::before($this->id, '/', -1));
			return ($parentId !== NULL && $parentId !== '') ? new self($parentId) : NULL;
		}


		/**
		 * @return string
		 */
		public function getBaseName()
		{
			$a = Lumadoc::falseToNull(Strings::after($this->id, '/', -1));

			if ($a === NULL) {
				return $this->id;
			}

			return $a;
		}


		/**
		 * @return non-empty-string
		 */
		public function __toString()
		{
			return $this->id;
		}


		/**
		 * @param  string $id
		 * @return bool
		 */
		public static function isValid($id)
		{
			if ($id === '') {
				return FALSE;
			}

			return Validators::is($id, 'pattern:[a-zA-Z0-9][a-zA-Z0-9-]*(\/[a-zA-Z0-9][a-zA-Z0-9-]*)*');
		}
	}
