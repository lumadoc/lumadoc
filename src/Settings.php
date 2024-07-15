<?php

	namespace Lumadoc;


	class Settings
	{
		/** @var non-empty-string */
		private $docName;

		/** @var Section[] */
		private $sections;

		/** @var string */
		private $directory;

		/** @var string */
		private $assetsBaseUrl;

		/** @var string */
		private $installationBaseUrl;


		/**
		 * @param non-empty-string $docName
		 * @param Section[] $sections
		 * @param string $directory
		 * @param string $assetsBaseUrl
		 * @param string $installationBaseUrl
		 */
		public function __construct(
			$docName,
			$sections,
			$directory,
			$assetsBaseUrl,
			$installationBaseUrl
		)
		{
			$this->docName = $docName;
			$this->sections = $sections;
			$this->directory = $directory;
			$this->assetsBaseUrl = rtrim($assetsBaseUrl, '/');
			$this->installationBaseUrl = rtrim($installationBaseUrl, '/');
		}


		/**
		 * @return non-empty-string
		 */
		public function getDocName()
		{
			return $this->docName;
		}


		/**
		 * @return Section[]
		 */
		public function getSections()
		{
			return $this->sections;
		}


		/**
		 * @return string
		 */
		public function getDirectory()
		{
			return $this->directory;
		}


		/**
		 * @return string
		 */
		public function getAssetsBaseUrl()
		{
			return $this->assetsBaseUrl;
		}


		/**
		 * @return string
		 */
		public function getInstallationBaseUrl()
		{
			return $this->installationBaseUrl;
		}
	}
