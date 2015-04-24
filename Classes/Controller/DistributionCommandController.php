<?php
namespace MaxServ\Distributionmanagement\Controller;

/**
 *  Copyright notice
 *
 *  ⓒ 2015 Michiel Roos <michiel@maxserv.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is free
 *  software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class DistributionCommandController
 *
 * @author Michiel Roos <michiel@maxserv.com>
 */
class DistributionCommandController extends CommandController {

	/**
	 * Configuration utillity
	 *
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility
	 * @inject
	 */
	protected $configurationUtility;

	/**
	 * Distribution repository
	 *
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $distributionRepository;

	/**
	 * Extension download utillity
	 *
	 * @var \TYPO3\CMS\Extensionmanager\Utility\DownloadUtility
	 * @inject
	 */
	protected $downloadUtility;

	/**
	 * Extension management service
	 *
	 * @var \TYPO3\CMS\Extensionmanager\Service\ExtensionManagementService
	 * @inject
	 */
	protected $managementService;

	/**
	 * Repository Helper
	 *
	 * @var \TYPO3\CMS\Extensionmanager\Utility\Repository\Helper
	 * @inject
	 */
	protected $repositoryHelper;

	/**
	 * List distributions
	 *
	 * @return void
	 */
	public function listCommand() {
		if ($this->distributionRepository->countAll() === 0) {
			$this->updateExtensionList();
		}
		$distributions = $this->distributionRepository->findAllOfficialDistributions();
		$this->outputLine('Found ' . count($distributions) . ' official distributions');
		/** @var Extension $distribution */
		foreach ($distributions as $distribution) {
			$this->outputLine($distribution->getExtensionKey() . ' - ' . $distribution->getTitle());
		}
		$this->outputLine();
		$distributions = $this->distributionRepository->findAllCommunityDistributions();
		$this->outputLine('Found ' . count($distributions) . ' community distributions');
		/** @var Extension $distribution */
		foreach ($distributions as $distribution) {
			$this->outputLine($distribution->getExtensionKey() . ' - ' . $distribution->getTitle());
		}
	}

	/**
	 * Install command
	 *
	 * @param string $distributionKey The distribution key
	 *
	 * @return void
	 */
	public function installCommand($distributionKey = '') {
		if ($this->distributionRepository->countAll() === 0) {
			$this->updateExtensionList();
		}
		$distributionToInstall = NULL;
		if ($distributionKey !== '') {
			$distributions = $this->distributionRepository->findAllOfficialDistributions();
			/** @var Extension $distribution */
			foreach ($distributions as $distribution) {
				if ($distributionKey == $distribution->getExtensionKey()) {
					$distributionToInstall = $distribution;
					break;
				}
			}
			$this->outputLine();
			$distributions = $this->distributionRepository->findAllCommunityDistributions();
			/** @var Extension $distribution */
			foreach ($distributions as $distribution) {
				if ($distributionKey == $distribution->getExtensionKey()) {
					$distributionToInstall = $distribution;
					break;
				}
			}
			if ($distributionToInstall === NULL) {
				$this->outputLine('The specified distribution wat not found. Please select one of the distributions shown by the distribution:list action.');
			}
			$this->installDistribution($distributionToInstall);
		} else {
			$this->outputLine('Please specify an distribution key using the --distribution-key option.');
		}
	}

	/**
	 * Action for installing a distribution -
	 * redirects directly to configuration after installing
	 *
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Model\Extension $extension The
	 *    extension to install
	 *
	 * @return void
	 */
	private function installDistribution(\TYPO3\CMS\Extensionmanager\Domain\Model\Extension $extension) {
		list($result, $errorMessages) = $this->installFromTer($extension);
		if ($errorMessages) {
			foreach ($errorMessages as $extensionKey => $messages) {
				foreach ($messages as $message) {
					$this->outputLine($message['message']);
				}
			}
		} else {
			$this->outputLine(
				\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('distribution.welcome.message', 'extensionmanager') .
				' ' .
				$extension->getExtensionKey()
			);
		}
	}

	/**
	 * Install an action from TER
	 * Downloads the extension, resolves dependencies and installs it
	 *
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Model\Extension $extension The
	 *    extension to install
	 * @param string $downloadPath The downloadpath
	 *
	 * @return array
	 */
	protected function installFromTer(\TYPO3\CMS\Extensionmanager\Domain\Model\Extension $extension, $downloadPath = 'Local') {
		$result = FALSE;
		$errorMessages = array();
		try {
			$this->downloadUtility->setDownloadPath($downloadPath);
//			$this->managementService->setAutomaticInstallationEnabled($this->configurationUtility->getCurrentConfiguration('extensionmanager')['automaticInstallation']['value']);
			if (($result = $this->managementService->installExtension($extension)) === FALSE) {
				$errorMessages = $this->managementService->getDependencyErrors();
			}
		} catch (\TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException $e) {
			$errorMessages = array(
				$extension->getExtensionKey() => array(
					array(
						'code' => $e->getCode(),
						'message' => $e->getMessage(),
					)
				),
			);
		}

		return array($result, $errorMessages);
	}

	/**
	 * Update extension list.
	 *
	 * @return void
	 */
	protected function updateExtensionList() {
		$this->outputLine('Updating extensionlist.');
		try {
			$this->repositoryHelper->updateExtList();
		} catch (\TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException $e) {
			$errorMessage = $e->getMessage();
			$this->outputLine($errorMessage);
		}
	}
}
