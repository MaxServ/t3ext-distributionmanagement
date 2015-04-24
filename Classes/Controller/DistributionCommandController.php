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
	 * Distribution repository
	 *
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $distributionRepository;

	/**
	 * List backend users
	 *
	 * @return void
	 */
	public function listCommand() {
		$distributions = $this->distributionRepository->findAllOfficialDistributions();
		$this->outputLine('Found ' . count($distributions) . ' official distributions');
		/** @var Extension $distribution */
		foreach ($distributions as $distribution) {
			$this->outputLine($distribution->getExtensionKey());
		}
		$distributions = $this->distributionRepository->findAllCommunityDistributions();
		$this->outputLine('Found ' . count($distributions) . ' community distributions');
		/** @var Extension $distribution */
		foreach ($distributions as $distribution) {
			$this->outputLine($distribution->getExtensionKey());
		}
	}
}
