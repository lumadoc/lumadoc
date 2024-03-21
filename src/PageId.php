<?php

	namespace Lumadoc;

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
