<?php

	namespace Lumadoc;


	class Section
	{
		/** @var non-empty-string */
		private $id;

		/** @var non-empty-string */
		private $label;


		/**
		 * @param non-empty-string $id
		 * @param non-empty-string $label
		 */
		public function __construct(
			$id,
			$label
		)
		{
			$this->id = $id;
			$this->label = $label;
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
		public function getLabel()
		{
			return $this->label;
		}
	}
