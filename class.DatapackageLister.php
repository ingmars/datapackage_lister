<?php
/**
 * This is the main class file of the data platform.
 *
 * Author: Ingmar Schlecht
 * License: GNU General Public License v3.0
 */

class DatapackageLister {
	/**
	 * @var string Absolute filesystem path to data directory
	 */
	protected $rootPath;

	/**
	 * @var TemplateEngine
	 */
	protected $view;

	/**
	 * datapackageLister constructor.
	 */
	function __construct() {
		$this->rootPath = dirname(__FILE__).'/data/';
	}

	/**
	 * Main entry method of this script. Selects what to do based on URL parameters.
	 * Note that Apache mod_rewrite is used to transform paths into actionable URLs for this script.
	 * More details on the rewrite rules can be found in the .htaccess file in this folder.
	 */
	public function controller() {
		$this->view = new TemplateEngine();
		$this->view->rootPath = $this->rootPath;
		if($_GET['package'] && $_GET['version'] && $_GET['action'] === 'customDownload') {
			$this->customDownload($_GET['package'], $_GET['version'], $_GET['resource'], $_GET['filter']);
		} elseif($_GET['package'] && $_GET['version'] && $_GET['action'] === 'showReadme') {
			$this->showReadme($_GET['package'], $_GET['version']);
		} elseif($_GET['package'] && $_GET['version'] && $_GET['action'] === 'redirectToLatestFile') {
			$this->redirectToLatestFile($_GET['package'], $_GET['version'], $_GET['requestedFile']);
		} elseif($_GET['package']) {
			if($_GET['version']) {
				$this->detailView($_GET['package'], $_GET['version']);
			} else {
				$this->detailView($_GET['package']);
			}
		} elseif(isset($_GET['zipCreator'])) {
			$this->zipCreator();
		} else {
			$this->listView();
		}
	}

	/**
	 * Action: List view
	 *
	 * This is the default action and displays the initial view of the data platform displaying the
	 * overview list of all data packages.
	 */
	public function listView() {
		$availabilityData = $this->getDataAvailabilityData();

		$this->view->availabilityCountryList = $availabilityData['countryList'];
		$this->view->dataAvailabilities = $availabilityData['dataAvailabilities'];
		$this->view->dataPackages = $this->getAllDataPackages();

        $this->view->iframeMode = false;
        $mode = 'html';
        if($_GET['iframeMode']) {
            $this->view->iframeMode = true;
            $mode = 'iframe';
        }



		$this->view->render('listView.php', $mode);
	}

	/**
	 * Action: Detail view
	 *
	 * This is the action showing the details for an individual data package.
	 *
	 * @param $packageName
	 * @param bool $selectedVersionName
	 */
	public function detailView($packageName, $selectedVersionName=FALSE) {
		$versions = $this->getVersionListForDataPackage($packageName);
		reset($versions);
		$latestVersionName = key($versions); // get first array key (first, i.e. latest, version in array)
		if($selectedVersionName === FALSE) {
			$selectedVersionName = $latestVersionName;
		}
		$selectedVersion = $this->getSpecificVersion($packageName, $selectedVersionName);
		if($selectedVersionName === 'latest') {
			$selectedVersion['versionLinkPath'] = $selectedVersion['packagePath'].'latest/';
			if(substr($selectedVersion['id'],0,25) == 'https://doi.org/10.25832/') {
                $selectedVersion['id'] = 'https://doi.org/10.25832/'.$selectedVersion['name'];
            }
		}
		if(!$selectedVersion) {
			$this->throwError('Selected version "'.htmlspecialchars($selectedVersionName).'" of "'.htmlspecialchars($packageName).'" not found.');
		}
		$this->view->allVersions = $versions;
        $this->view->package = $selectedVersion;
        $this->view->metadataJsonLD = $this->getSchemaOrgJsonLD($selectedVersion);
		$this->view->dataPackages = $this->getAllDataPackages();
		$this->view->selectedVersionName = $selectedVersionName;

		$this->view->render('detailView.php');
	}

	/**
	 * Action: Show readme
	 *
	 * This action generates and shows the plain text readme file.
	 *
	 * @param $packageName
	 * @param $versionName
	 * @param bool $returnOutput
	 * @return string
	 */
	protected function showReadme($packageName, $versionName, $returnOutput=false) {
		$package = $this->getSpecificVersion($packageName, $versionName);
		$allVersions = $this->getVersionListForDataPackage($packageName);

		$this->view->package = $package;
		$this->view->allVersions = $allVersions;
		$output = $this->view->render('readme.php', 'textOnly', $returnOutput);
		if($returnOutput) {
			return $output;
		}
	}

	/**
	 * Action: Custom download
	 *
	 * This is the action behind the CSV / sqlite filter functionality. It is called when a user clicks the
	 * download button for a filtered CSV file. It can technically output both CSV and XLSX files, although
	 * XLSX output is currently commented out for performance reasons.
	 *
	 * @param $packageName
	 * @param $selectedVersion
	 * @param $resourceNum
	 * @param $selectedFilterValues
	 */
	public function customDownload($packageName, $selectedVersion, $resourceNum, $selectedFilterValues) {
		$package = $this->getSpecificVersion($packageName, $selectedVersion);
		if(!is_array($package)) {
			$this->throwError('Selected version "'.htmlspecialchars($selectedVersion).'" of "'.htmlspecialchars($packageName).'" not found.');
		}

		$resource = $package['resources'][$resourceNum];
		$baseName = basename($resource['path'], ".csv");

		if(!$package['sqliteFile']) {
			$this->throwError('No database file found for package "'.$packageName.'".');
		}

		$escapedFieldNames = array();

		foreach($resource['schema']['fields'] as $fieldConf) {
			if(isset($fieldConf['opsdProperties'])) {
				foreach($fieldConf['opsdProperties'] as $propertyKey => $propertyVal) {
					if(!is_array($selectedFilterValues[$propertyKey])) {
						$this->throwError('No value selected for field "'.htmlspecialchars($propertyKey).'".');
					}
					if(!in_array($propertyVal, $selectedFilterValues[$propertyKey])) {
						continue 2;
					}
				}
			}
			$escapedFieldNames[] = '`'.addslashes($fieldConf['name']).'`';
		}

		$fileBaseName = $baseName.'_filtered';

		if(count($escapedFieldNames) < 1) {
			$this->throwError('No matching fields');
		}

		$contentFilterWhereParts = array();

		foreach($resource['__opsd-filters'] as $filterKey => $filterConfig) {
			if($filterConfig['applyTo'] === 'fieldContent') {
				//$quotedFieldName = '\''.SQLite3::escapeString($filterConfig['fieldName']).'\'';
				$quotedFieldName = $filterConfig['fieldName'];
				if($filterConfig['type'] === 'date' && isset($selectedFilterValues[$filterKey]) && isset($selectedFilterValues[$filterKey]['from'])) {
					// validate dates
					$oneDay = 60*60*24;
					$fromDate = date('Y-m-d', strtotime($selectedFilterValues[$filterKey]['from']));
					$toDate = date('Y-m-d', strtotime($selectedFilterValues[$filterKey]['to'])+$oneDay);

					$contentFilterWhereParts[] = '
						AND '.$quotedFieldName.' >= date("'.$fromDate.'")
						AND '.$quotedFieldName.' <= date("'.$toDate.'")
					';
				} elseif($filterConfig['type'] === 'select' && isset($selectedFilterValues[$filterKey]) && is_array($selectedFilterValues[$filterKey])) {
					$escapedSelectedItems = array();
					$nullValueAllowed = false;
					foreach($selectedFilterValues[$filterKey] as $itemKey => $itemValue) {
						if($itemValue === '[empty]') {
							$nullValueAllowed = true;
						} else {
							$escapedSelectedItems[$itemKey] = "'".SQLite3::escapeString($itemValue)."'";
						}
					}
					$contentFilterWhereParts[] = '
						AND (
							'.$quotedFieldName.' IN ('.implode(',', $escapedSelectedItems).')
							'.($nullValueAllowed?' OR '.$quotedFieldName.' IS NULL ':'').'
						)
					';
				}
			}
		}

		$query = 'SELECT '.implode(', ', $escapedFieldNames).' FROM "'.addslashes($baseName).'" WHERE 1=1 '.implode(' ', $contentFilterWhereParts);

		$db = new SQLite3($this->rootPath.$package['versionPath'].$package['sqliteFile']);
		$statement = $db->prepare($query);
		//$statement->bindValue(':id', $id);
		$results = $statement->execute();

		$numCols = $results->numColumns();
		$colNames = array();
		for($i=0;$i<$numCols;$i++) {
			$colNames[] = $results->columnName($i);
		}

		//if($_GET['downloadXLSX']) {
		//	$this->outputAsXLSX($results, $fileBaseName, $colNames,$package['name']);
		//} else {
		$this->outputAsCSV($results, $fileBaseName, $colNames,$package['name']);
		//}

	}

	/**
	 * Action: Redirect to latest file
	 *
	 * When a users looks at a detail page of a data package using the latest/ URL (instead of a specific version date)
	 * then all links on that page are pointing to individual resource files also using that "latest/" bit in the URL
	 * to the resource. This method right here resolves the latest/ part to the actual latest version path and redirects.
	 *
	 * E.g this request: https://data.open-power-system-data.org/time_series/latest/time_series.xlsx
	 * is redirected to: https://data.open-power-system-data.org/time_series/2017-07-09/time_series.xlsx
	 * (given that 2017-07-09 is the latest version.)
	 *
	 * @param $packageName
	 * @param $versionName
	 * @param $requestedFile
	 */
	protected function redirectToLatestFile($packageName, $versionName, $requestedFile) {
		$requestedFile = preg_replace('/[^a-zA-Z0-9_.-]/','', $requestedFile); // Sanitize requested filename using whitelist
		$package = $this->getSpecificVersion($packageName, $versionName);
		$redirectPath = '/'.$package['versionPath'].$requestedFile;

		header('Location: '.$redirectPath);
	}

	/**
	 * Action: zipCreator
	 *
	 * @param $directory
	 * @param int $sortingOrder
	 * @return array|bool
	 */
	public function zipCreator() {
		$dataPackages = $this->getAllDataPackages(true, true);

		$version = $_GET['zipVersion'];
        $packageName = $_GET['zipPackage'];
        if($_GET['subAction']=='createZip') {
            $subAction = 'createZip';
        }
		$statusMessage = '';

		if($packageName && $version && $dataPackages[$packageName] && $dataPackages[$packageName][$version] && $subAction==='createZip') {
			$versionData = $dataPackages[$packageName][$version];
			$statusMessage = $this->createZip($packageName, $version, $versionData);
			$dataPackages = $this->getAllDataPackages(true, true); // refresh information on datapackages so potentially new ZIPs get displayed
		}

		$this->view->dataPackages = $dataPackages;
		$this->view->statusMessage = $statusMessage;
		$this->view->render('zipCreator.php');
	}

	/**
	 * Internal method to get meta data for all data packages
	 *
	 * @param bool $allVersions
	 * @param bool $includeHidden
	 * @return array
	 */
	protected function getAllDataPackages($allVersions = false, $includeHidden=false) {
		$dataPackages = array();
		$dataPackageDirectories = $this->listDirectoryContents($this->rootPath);
		foreach($dataPackageDirectories as $dataPackageName) {
			if(@is_dir($this->rootPath.$dataPackageName)) {
				if($allVersions) {
					$dataPackages[$dataPackageName] = $this->getVersionListForDataPackage($dataPackageName, FALSE, $includeHidden);
				} else {
					$versionList = $this->getVersionListForDataPackage($dataPackageName, TRUE);
					if(count($versionList)>0) {
						$dataPackages[$dataPackageName] = array_shift($versionList); // get first version from versionList
					}
				}
			}
		}

		return $dataPackages;
	}

	/**
	 * Get metadata for specific version of a data package
	 *
	 * @TODO: implement faster function that doesn't call getVersionListForDataPackage() to get ALL versions first
	 *
	 * @param $dataPackageName
	 * @param $versionName
	 * @return bool|mixed
	 */
	protected function getSpecificVersion($dataPackageName, $versionName) {
		if($versionName==='latest') {
			$versions = $this->getVersionListForDataPackage($dataPackageName, TRUE);
			return array_shift($versions);
		}
		$versions = $this->getVersionListForDataPackage($dataPackageName, $onlySearchFirst=FALSE, $includeHidden=TRUE);
		if(isset($versions[$versionName])) {
			return $versions[$versionName];
		} else {
			return FALSE;
		}
	}

	/**
	 * Get version list for a specific data package
	 *
	 * @param $dataPackageName
	 * @param bool $onlySearchFirst
	 * @param bool $includeHidden
	 * @return array
	 */
	protected function getVersionListForDataPackage($dataPackageName, $onlySearchFirst=FALSE, $includeHidden=FALSE) {
		$versionList = array();
		$packagePath = $dataPackageName.'/';
		$dataPackageVersions = $this->listDirectoryContents($this->rootPath.$packagePath, SCANDIR_SORT_DESCENDING);
		$linkTarget = FALSE;
		foreach($dataPackageVersions as $versionName) {
			if(is_dir($this->rootPath.$packagePath.$versionName) && file_exists($this->rootPath.$packagePath.$versionName.'/datapackage.json')) {
				// Check if the JSON is alright:
				$packageData = $this->readPackageMetadata($dataPackageName, $versionName);
				if($packageData) {
					if(!$packageData['hide'] || $includeHidden) {
						$versionList[$versionName] = $packageData;
						if($onlySearchFirst) {
							break;
						}
					}
				}
			}
		}

		return $versionList;
	}

	/**
	 * Gets the meta data for a specific version of a data package.
	 *
	 * This is basically a cache wrapper around readPackageMetadata_worker().
	 *
	 * The method implements a caching mechanism, so that the actual worker method (readPackageMetadata_worker)
	 * is called only once when the first user hits the page after something changed, and all subsequent
	 * users get the data from the cache file instead.
	 *
	 * @param $dataPackageName
	 * @param $versionName
	 * @return array|mixed
	 */
	protected function readPackageMetadata($dataPackageName, $versionName) {

		// If you temporarily want to disable the caching mechanism altogether, un-comment the following line:
		// return $this->readPackageMetadata_worker($dataPackageName, $versionName);

		$versionAbsPath = $this->rootPath.$dataPackageName.'/'.$versionName.'/';
		$cachePath = dirname($this->rootPath).'/cache/';
		$jsonFilePath = $versionAbsPath.'datapackage.json';
		$sqliteFilePath = $versionAbsPath.$dataPackageName.'.sqlite';
		$potentialZipFilePath = $this->rootPath.$dataPackageName.'/'.'opsd-'.$dataPackageName.'-'.$versionName.'.zip';

		$hashBase = filemtime($jsonFilePath).filesize($jsonFilePath);
		if(file_exists($sqliteFilePath)) {
			$hashBase .= filemtime($sqliteFilePath).filesize($sqliteFilePath);
		}
		if(file_exists($potentialZipFilePath)) {
			$hashBase .= 'ZIP file exists';
		}
		if(file_exists($versionAbsPath.'original_data/')) {
			$hashBase .= 'orig_data directory exists';
		}

		$cacheFilePath = $cachePath.$dataPackageName.'_'.$versionName.'_'.md5($hashBase).'.cache';

		if(file_exists($cacheFilePath) AND !(isset($_GET['skip_cache']) && $_GET['skip_cache'] === $dataPackageName)) { // note: the skip_cache _GET variable will not get through ModRewrite/RealURL. Therefore to make it work, the page needs to be called like: /index.php?package=...&version=...
			$serializedContent = file_get_contents($cacheFilePath);
			$result = unserialize($serializedContent);
		} else {
			$result = $this->readPackageMetadata_worker($dataPackageName, $versionName);
			$serializedContent = serialize($result);
			file_put_contents($cacheFilePath, $serializedContent);
		}

		return $result;
	}

	/**
	 * This method reads and streamlines the package meta-data from a datapackage.json file.
	 * You should never call this method directly but always through its cache-wrapper readPackageMetadata().
	 *
	 * This is probably the most "magic-doing" method of all in this class. It enriches the meta-data
	 * coming from the datapackage.json file of an individual data package with dynamically generated data
	 * such as further information regarding the content based filter.
	 *
	 * It is IMPORTANT to note that what comes out of this function is not just the datapackage.json content
	 * but contains further stuff dynamically generated in this method. The reason is that we want to have a single
	 * place where such additiional stuff is read and "streamlined", so that it can easily be used in many places
	 * subsequently.
	 *
	 * One example is: This method accesses the SQLite database of a data package to extract the unique values of a
	 * column for which a content filter is configured. This is a very resource-consuming task since it is accessing
	 * the large .sqlite files of data packages and executes heavy SQL queries. This should only be done once and then
	 * delivered from the cache. Since this method is indeed cached, those resource intensive things should be done
	 * right here.
	 *
	 * @param $dataPackageName
	 * @param $versionName
	 * @return array
	 */
	protected function readPackageMetadata_worker($dataPackageName, $versionName) {
		$packagePath = $dataPackageName.'/';
		$package = array();
		$package['name'] = $dataPackageName;
		$package['packagePath'] = $packagePath;
		$package['versionPath'] = $packagePath.$versionName.'/';
		$package['versionLinkPath'] = $package['versionPath']; // this is normally the same as versionPath, but functions using this data can modify it to differ (e.g. for the latest/ link in the detail view)
		$package['detailLink'] = '/'.urlencode($dataPackageName).'/'.urlencode($versionName);
		$package['origDataPath'] = $package['versionPath'].'original_data/';
		if(!@file_exists($this->rootPath.$package['origDataPath'])) {
			unset($package['origDataPath']);
		}
		$package['sqliteFile'] = $dataPackageName.'.sqlite';
		if(!@file_exists($this->rootPath.$package['versionPath'].$package['sqliteFile'])) {
			$package['sqliteFile'] = false;
		}
		$package['zipFileName'] = '';
		$potentialZipFileName = $targetName = 'opsd-'.$dataPackageName.'-'.$versionName.'.zip';
		if(@file_exists($this->rootPath.$packagePath.'/'.$potentialZipFileName)) {
			$package['zipFileName'] = $potentialZipFileName;
		}
		$pathToJSON = $this->rootPath.$package['versionPath'].'/datapackage.json';
		if(!@is_dir($this->rootPath.$packagePath) || !@file_exists($pathToJSON)) {
			return FALSE;
		}
		$versionMetaData = json_decode(file_get_contents($pathToJSON), true);
		if(!is_array($versionMetaData)) {
			return FALSE;
		}
		$versionMetaData['version_from_json'] = $versionMetaData['version'];
		$versionMetaData['version'] = $versionName;	// the version name that comes from the directory name of the version should be the definitive one. The version info from JSON is just for information.

        // Mapping old keys to new keys (for legacy OPSD json)
        $legacy_keys = array(
            'longDescription' => array('long_description'),
            '_alternativeFormats' => array('alternative_formats'),
            'lastChanges' => array('last_changes', 'opsd-changes-to-last-version'),
            'geographicalScope' => array('geographical-scope'),
        );
        $versionMetaData = $this->streamlineLegacyKeys($legacy_keys, $versionMetaData);

        if(!$versionMetaData['spatial'] && $versionMetaData['geographicalScope']) {
            $versionMetaData['spatial'] = array(
                'location' => $versionMetaData['geographicalScope'],
            );
            unset($versionMetaData['geographicalScope']);
        }

        foreach($versionMetaData['contributors'] as $contributorKey => $contributor) {
            // Mapping old keys to new keys (for legacy OPSD json)
            $legacy_keys = array(
                'title' => array('name'),
                'path' => array('web'),
            );
            $versionMetaData['contributors'][$contributorKey] = $this->streamlineLegacyKeys($legacy_keys, $contributor);
        }

        foreach($versionMetaData['sources'] as $sourceKey => $source) {
            // Mapping old keys to new keys (for legacy OPSD json)
            $legacy_keys = array(
                'title' => array('name'),
                'path' => array('web'),
            );
            $versionMetaData['sources'][$sourceKey] = $this->streamlineLegacyKeys($legacy_keys, $source);
        }

		if(!isset($versionMetaData['documentation']) && isset($versionMetaData['opsd-jupyter-notebook-url'])) {
			$versionMetaData['documentation'] = $versionMetaData['opsd-jupyter-notebook-url'];
		}

		if(isset($versionMetaData['hide']) && $versionMetaData['hide'] !== FALSE && $versionMetaData['hide'] !== "no") {
			$versionMetaData['hide'] = true;
		} else {
			$versionMetaData['hide'] = false;
		}

		if(isset($versionMetaData['external']) && $versionMetaData['external'] !== FALSE && $versionMetaData['external'] !== "no") {
			$versionMetaData['external'] = true;
		} else {
			$versionMetaData['external'] = false;
		}

		if(!isset($versionMetaData['attribution'])) {

		    $packageUrl = 'https://data.open-power-system-data.org/'.$dataPackageName.'/'.$versionMetaData['version'].'/';

		    if(isset($versionMetaData['id']) && substr($versionMetaData['id'], 0, 4 )=='http') {
                $packageUrl = $versionMetaData['id'];
            }

			$versionMetaData['attribution'] = 'Attribution in Chicago author-date style should be given as follows: "Open Power System Data. '.substr($versionMetaData['version'], 0, 4).'. <span style="font-style: italic">Data Package '.$versionMetaData['title'].'.</span> Version '.$versionMetaData['version'].'. '.$packageUrl.'. (Primary data from various sources, for a complete list see URL)."';
		}

		if(@is_array($versionMetaData['resources'])) foreach($versionMetaData['resources'] as $resourceKey => &$resource) {
			$filters = array();
			$sourcesAreGivenAtFieldLevel = false;
			$sqLiteConnection = FALSE;

			// Resolve schema reference as detailed in OPSD data package spec: http://specs.frictionlessdata.io/json-table-schema/#schema
			if(isset($resource['schema']) && !is_array($resource['schema'])) {
				$schemaNameString = $resource['schema']; // Theoretically this could be a URL as well but we only implement the in-file schemas

				if(
					isset($versionMetaData['schemas']) &&
					is_array($versionMetaData['schemas']) &&
					isset($versionMetaData['schemas'][$schemaNameString]) &&
					is_array($versionMetaData['schemas'][$schemaNameString])
				) {
					$resource['schema'] = $versionMetaData['schemas'][$schemaNameString];
				}
			}

			if(@is_array($resource['schema']['fields'])) foreach($resource['schema']['fields'] as $fieldKey => &$fieldConf) {

                // Mapping old keys to new keys (for legacy OPSD json)
                $legacy_keys = array(
                    'opsdContentfilter' => array('opsd-contentfilter'),
                    'opsdProperties' => array('opsd-properties'),
                );
                $fieldConf = $this->streamlineLegacyKeys($legacy_keys, $fieldConf);

				if($fieldConf['source']) {
					$sourcesAreGivenAtFieldLevel = true;
				}
				if($package['sqliteFile'] && @is_array($fieldConf['opsdProperties'])) foreach($fieldConf['opsdProperties'] as $propertyKey => $propertyValue) {
					if(!isset($filters[$propertyKey])) {
						$filters[$propertyKey] = array(
							'type' => 'select',
							'applyTo' => 'fieldNames',
							'optionItems' => array()
						);

						if($propertyKey == 'Variable' && $dataPackageName == 'time_series') {
                            $filters[$propertyKey]['message'] = 'Note: Not all variables exist for all countries. Missing fields to be expected.';
                        }
					}
					if(!isset($filters[$propertyKey][$propertyValue])) {
						$filters[$propertyKey]['optionItems'][$propertyValue] = $propertyValue;
					}
					ksort($filters[$propertyKey]['optionItems']);
				}
				if($package['sqliteFile'] && $fieldConf['opsdContentfilter'])  {
					$filter = null;
					if(!$sqLiteConnection) {
						$sqLiteConnection = new SQLite3($this->rootPath.$package['versionPath'].$package['sqliteFile']);
						$tableName = basename($resource['path'], ".csv");
					}
					if($sqLiteConnection) {
						if($fieldConf['type'] === 'datetime' OR $fieldConf['type'] === 'date') {
							$filter = array(
								'type' => 'date',
								'fieldName' => $fieldConf['name'],
								'applyTo' => 'fieldContent',
								'label' => $fieldConf['description'],
								'minDate' => '2000-01-01',
								'maxDate' => date('Y-m-d'),
							);

							// now: dynamically search first and last time period of date field for use in date selector
							$query = 'SELECT MIN('.addslashes($fieldConf['name']).'), MAX('.addslashes($fieldConf['name']).') FROM "'.addslashes($tableName).'"';
							$result = $sqLiteConnection->query($query);
							if($result) {
								while ($row = $result->fetchArray(SQLITE3_NUM)) {
									$filter['minDate'] = substr($row[0], 0, 10); // get only the date from date-and-time timestamps
									$filter['maxDate'] = substr($row[1], 0, 10);
								}
								if(!isset($versionMetaData['temporalCoverage'])) {
                                    $versionMetaData['temporalCoverage'] = $filter['minDate'].'/'.$filter['maxDate'];
                                }
							}
						} else {
							$query = 'SELECT DISTINCT ' . addslashes($fieldConf['name']) . ' FROM "' . addslashes($tableName) . '" WHERE 1=1 ';
							$result = $sqLiteConnection->query($query);
							if($result) {
								$resultCounter = 0;
								while ($row = $result->fetchArray(SQLITE3_NUM)) {
									$resultCounter++;
									if ($resultCounter == 1) { // basically this is just initialization stuff to do when there is at least one result. There is no proper sqlite_num_rows() function in SQLite3, so we have to do this slightly akward stuff.
										$filter = array(
											'type' => 'select',
											'fieldName' => $fieldConf['name'],
											'applyTo' => 'fieldContent',
											'label' => $fieldConf['description'],
											'optionItems' => array(),
										);
									}

									$itemValue = $row[0];

									if (!$itemValue && $itemValue !== 0) {
										$itemValue = '[empty]';
									}

									$filter['optionItems'][$itemValue] = $itemValue;

									if ($resultCounter > 100) {
										$filter['optionItems']['ERROR_ITEM'] = 'ERROR_TOO_MANY_OPTIONS';
										break;
									}
								}
							}
						}
					}
					if($filter) {
						$filterKey = '_contentfilter_'.$fieldConf['name'];
						$filters[$filterKey] = $filter;
					}
				}
			}
			if(@is_array($resource['_alternativeFormats'])) {
				$alternativeFormatsByType = array();
				$alternativeFormatsByStacking = array();
				foreach($resource['_alternativeFormats'] as $altFormat) {
					if(!is_array($alternativeFormatsByType[$altFormat['format']])) $alternativeFormatsByType[$altFormat['format']] = array();
					if(!is_array($alternativeFormatsByStacking[$altFormat['stacking']])) $alternativeFormatsByStacking[$altFormat['stacking']] = array();
					$alternativeFormatsByType[$altFormat['format']][] = $altFormat;
					$alternativeFormatsByStacking[$altFormat['stacking']][$altFormat['format']] = $altFormat;
					ksort($alternativeFormatsByStacking[$altFormat['stacking']]);
				}
				$versionMetaData['resources'][$resourceKey]['__alternativeFormatsByType'] = $alternativeFormatsByType;
				$versionMetaData['resources'][$resourceKey]['__alternativeFormatsByStacking'] = $alternativeFormatsByStacking;
			}
			$versionMetaData['resources'][$resourceKey]['__opsd-filters'] = $filters;
			$versionMetaData['resources'][$resourceKey]['__sourcesAreGivenAtFieldLevel'] = $sourcesAreGivenAtFieldLevel;
		}

		return array_merge($versionMetaData, $package);
	}

	public function getSchemaOrgJsonLD($package) {
	    $authors = array();

        if(is_array($package['contributors'])){
            foreach($package['contributors'] as $contributor) {
                $nameSplit = explode(' ',$contributor['title']);
                $authors[] = array (
                    '@type' => 'Person',
                    'name' => $contributor['title'],
                    'givenName' => $nameSplit[0],
                    'familyName' => $nameSplit[1],
                );
            }
        }

        $packageUrl = "https://data.open-power-system-data.org/".$package['name']."/".$package['version'];

        $metadata = array (
            '@context' => 'http://schema.org',
            '@type' => 'Dataset',
//            'additionalType' => 'CreativeWork',
            'name' => $package['title'],
            'author' => $authors,
            'description' => $package['description'].'. '.$package['longDescription'],
            'datePublished' => $package['version'],
            'schemaVersion' => 'https://schema.datacite.org/meta/kernel-4',
            'publisher' => array (
                    '@type' => 'Organization',
                    'name' => 'Open Power System Data',
            ),
            'provider' => array (
                    '@type' => 'Organization',
                    'name' => 'Open Power System Data',
            ),
            //"citation" => $package['attribution'], // This is causing problems with DOI Fabrica's DOI creation process, so we leave this out.

            // For some reason the DOI Fabrica workflow does not work any more if we switch on spatialCoverage here... Therefore, disabled for now.
           // "spatialCoverage" => is_array($package['spatial'])&&$package['spatial']['location']?$package['spatial']['location']:'',


              "version" => $package['version'],
              "url" => $packageUrl,
              "includedInDataCatalog" => array(
                  "@type" => "DataCatalog",
                  "name" => "Open Power System Data"
              ),
              "isBasedOn" => [
                  array(
                       "@type" => "Dataset",
                         "name" => "BNETZA Kraftwerksliste (DE)",
                         "url" => "http://www.bundesnetzagentur.de/DE/Sachgebiete/ElektrizitaetundGas/Unternehmen_Institutionen/Versorgungssicherheit/Erzeugungskapazitaeten/Kraftwerksliste/kraftwerksliste-node.html"
                  ),
                 array(
                       "@type" => "Dataset",
                         "name" => "Umweltbundesamt Datenbank Kraftwerke in Deutschland (DE)",
                         "url" => "http://www.umweltbundesamt.de/dokument/datenbank-kraftwerke-in-deutschland"
                 )
              ],
              "keywords" => $package['keywords'],
              "award" => array(
                     "Open Science Award Schleswig-Holstein 2016",
                     "Deutschland - Land der Ideen Ausgezeichneter Ort 2017"
              ),/* */
        );


        $metadata['distribution'] = array(
            array(
                "@type" => "DataDownload",
                "fileFormat" => "SQLITE",
                "contentUrl" => $packageUrl."/".$package['name'].".sqlite"
            )
        );
        foreach($package['resources'] as $resourceNum => $resource) {
            $metadata['distribution'][] = array(
                "@type" => "DataDownload",
                "fileFormat" => strtoupper($resource['format']),
                "contentUrl" => $packageUrl."/".$resource['path'],
            );
        }

        $metadata['variableMeasured'] = array();

        $resourceNumForVariableMeasured = FALSE;
        foreach($package['resources'] as $resourceNum => $resource){
            if(isset($resource['schema']['fields'])){
                $resourceNumForVariableMeasured = $resourceNum;
            }
        }

        if($resourceNumForVariableMeasured !== FALSE) {
            foreach($package['resources'][$resourceNumForVariableMeasured]['schema']['fields'] as $field) {
                $metadata['variableMeasured'][] = array(
                    "@type" => "PropertyValue",
                    "name" => $field['name'],
                    "description" => $field['description'],
                    // "unitCode" => "MAW",
                );
            }
        }

        if(isset($package['temporalCoverage'])) {
            $metadata['temporalCoverage'] = $package['temporalCoverage'];
        }

        if(isset($package['id']) && substr($package['id'], 0, 15) === "https://doi.org") {
            $metadata['@id'] = $package['id'];
            $metadata['identifier'] = array (
                '@type' => 'PropertyValue',
                'propertyID' => 'doi',
                'value' => $package['id'],
            );
        }

	    return $metadata;
    }


    public function streamlineLegacyKeys($new_key_to_old_keys_mapping, $arrayToWorkOn) {
        foreach($new_key_to_old_keys_mapping as $newKey => $oldKeys) {
            foreach($oldKeys as $oldKey) {
                if(isset($arrayToWorkOn[$oldKey]) && !isset($arrayToWorkOn[$newKey])) {
                    $arrayToWorkOn[$newKey] = $arrayToWorkOn[$oldKey];
                    unset($arrayToWorkOn[$oldKey]);
                }
            }
        }
	    return $arrayToWorkOn;
    }

	/**
	 * Throws an error message and renders the errorView
	 *
	 * @param $message
	 */
	public function throwError($message) {
		$this->view->errorMessage = 'Error: '.$message;
		$this->view->render('errorView.php');
		exit;
	}

	/**
	 * Outputs an SQL result as CSV
	 *
	 * Used by the filtered download functionality.
	 *
	 * @param $sqlResult
	 * @param $fileBaseName
	 * @param $colNames
	 * @param $dataPackageName
	 */
	public function outputAsCSV($sqlResult,$fileBaseName,$colNames,$dataPackageName) {
		header('Content-type: text/csv');
		header('Content-disposition: attachment;filename='.$fileBaseName.'.csv');
		$handle = fopen('php://output', 'w');

		fputcsv($handle, $colNames);
		while($row = $sqlResult->fetchArray(SQLITE3_NUM)) {
			fputcsv($handle, $row);
		}
		fclose($handle);
	}

	/**
	 * Outputs an SQL result as XLSX
	 *
	 * Used by the filtered download functionality.
	 *
	 * @param $sqlResult
	 * @param $fileBaseName
	 * @param $colNames
	 * @param $dataPackageName
	 */
	public function outputAsXLSX($sqlResult,$fileBaseName,$colNames,$dataPackageName) {
		include_once("libs/xlsxwriter/xlsxwriter.class.php");

		$header = array(); // colName => datatype matching
		foreach($colNames as $colName) {
			switch($colName) { // @TODO: This should not rely on the colName but on the meta data defined in the datapackage.json
				case 'timestamp':
					$header[$colName] = 'datetime';
					break;
				default:
					$header[$colName] = 'string';
					break;
			}
		}
		$data = array();
		while($row = $sqlResult->fetchArray(SQLITE3_NUM)) {
			$data[] = $row;
		}
		$writer = new XLSXWriter();
		$writer->setAuthor('Open Power System Data (open-power-system-data.org)');
		$writer->writeSheet($data,$dataPackageName,$header);
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-disposition: attachment;filename='.$fileBaseName.'.xlsx');
		$writer->writeToStdOut();
	}

	/**
	 * Helper function: List directory contents
	 *
	 * @param $directory
	 * @param int $sortingOrder
	 * @return array|bool
	 */
	protected function listDirectoryContents($directory, $sortingOrder = SCANDIR_SORT_ASCENDING) {
		if(!is_dir($directory)) return false;
		return array_diff(scandir($directory, $sortingOrder), array('..', '.'));
	}

	/**
	 * For OPSD use only. Get data for the availability table.
	 *
	 * @param $directory
	 * @param int $sortingOrder
	 * @return array|bool
	 */
	public function getDataAvailabilityData() {
		$dataAvailabilities = json_decode(file_get_contents(dirname(__FILE__).'/availabilities.json'),TRUE);

		$availabilityCountryList = array();
		foreach($dataAvailabilities as $packageSeries) {
			if(is_array($packageSeries)) foreach($packageSeries as $seriesData) {
				if(is_array($seriesData)) foreach($seriesData as $countryName => $countryVal) {
					if($countryName === '_url') {continue;}
					$availabilityCountryList[$countryName] = $countryName;
				}
			}
		}
		ksort($availabilityCountryList);
		// hacky: moving "20+ more" key to the end:
		$v = $availabilityCountryList['20+ more'];
		unset($availabilityCountryList['20+ more']);
		$availabilityCountryList['20+ more'] = $v;

		return array('countryList'=> $availabilityCountryList, 'dataAvailabilities' => $dataAvailabilities);
	}

	/**
	 * Creates a ZIP file for a data package
	 *
	 * @param $packageName
	 * @param $version
	 * @param $versionData
	 * @return string
	 */
	protected function createZip($packageName, $version, $versionData) {

		$filesToZip = array();
		$filesToZip[] = 'data/'.$packageName.'/'.$version.'/datapackage.json';

		if(is_array($versionData['resources'])) {
			foreach($versionData['resources'] as $resource) {
				$filesToZip[] = 'data/'.$packageName.'/'.$version.'/'.$resource['path'];
			}
		}

		$readMeContents = $this->showReadme($packageName, $version, true);

		$targetName = 'opsd-'.$packageName.'-'.$version;
		$targetPath = 'data/'.$packageName.'/'.$targetName.'.zip';

		$stringFiles = array(
			$targetName.'/README.md' => $readMeContents,
		);

		$status = $this->libraryfunc_create_zip(
			$filesToZip,
			$targetPath,
			$targetName,
			$stringFiles
		);

		if($status) {
			$statusMessage = 'ZIP (re)created successfully: '.$targetPath;
		} else {
			$statusMessage = 'ZIP creation failed.';
		}

		return $statusMessage;
	}

	/**
	 * Helper function that creates a compressed ZIP file
	 *
	 * @author David Walsh
	 * @license MIT license
	 * @source https://davidwalsh.name/create-zip-php
	 *
	 * @param array $files
	 * @param string $destination
	 * @param string $targetPath
	 * @param array $stringFiles
	 * @param bool $overwrite
	 * @return bool
	 */
	function libraryfunc_create_zip($files = array(),$destination = '',$targetPath='', $stringFiles=array(), $overwrite = true) {
		$overwriteFlag = ZIPARCHIVE::CREATE;
		if(file_exists($destination)) {
			if($overwrite) {
				$overwriteFlag =  ZIPARCHIVE::OVERWRITE;
			} else {
				return false;
			}
		}
		$valid_files = array();
		if(is_array($files)) {
			foreach($files as $file) {
				if(file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}
		if(count($valid_files)) {
			$zip = new ZipArchive();
			if($zip->open($destination,$overwriteFlag) !== true) {
				return false;
			}
			foreach($valid_files as $file) {
				if($targetPath) {
					$targetPath = rtrim($targetPath, '/').'/'; // ensure $targetPath has a trailing slash
					$localName = $targetPath.basename($file);
				} else {
					$localName = $file;
				}
				$zip->addFile($file,$localName);
			}

			foreach($stringFiles as $localName => $fileContents) {
				$zip->addFromString ($localName, $fileContents);
			}

			$zip->close();
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

			return file_exists($destination);
		} else {
			return false;
		}
	}

}

