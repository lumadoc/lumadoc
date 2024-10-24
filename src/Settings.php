<?php

	namespace Lumadoc;


	class Settings
	{
		/** @var non-empty-string */
		private $docName;

		/** @var string */
		private $directory;

		/** @var string */
		private $assetsBaseUrl;

		/** @var string */
		private $installationBaseUrl;


		/**
		 * @param non-empty-string $docName
		 * @param string $directory
		 * @param string $assetsBaseUrl
		 * @param string $installationBaseUrl
		 */
		public function __construct(
			$docName,
			$directory,
			$assetsBaseUrl,
			$installationBaseUrl
		)
		{
			$this->docName = $docName;
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
