
DATAPACKAGE: <?=strtoupper($package['title'])?>

===========================================================================

<?=$package['id']?>


by Open Power System Data: http://www.open-power-system-data.org/

Package Version: <?=$package['version']?>


<?=wordwrap($package['description'], 75)?>


<?=wordwrap($package['longDescription'], 75)?>


The data package covers the geographical region of <?=is_array($package['spatial'])&&$package['spatial']['location']?$package['spatial']['location']:'Undefined'?>.

We follow the Data Package standard by the Frictionless Data project, a
part of the Open Knowledge Foundation: http://frictionlessdata.io/


Documentation and script
===========================================================================

This README only contains the most basic information about the data package.
For the full documentation, please see the notebook script that was used to
generate the data package. You can find it at:

<?=str_replace('https://github.com/','https://nbviewer.jupyter.org/github/',$package['documentation'])?>


Or on GitHub at:

<?=$package['documentation']?>


License and attribution
===========================================================================
<?php if(is_array($package['licenses']) && count($package['licenses'])>0) { ?>

<?
if(is_array($package['licenses']) && count($package['licenses'])>0) foreach($package['licenses'] as $licenseNum => $license) {

    echo "Data license: \n";
    if($license['path']) {
        echo '    ['.($license['title']?$license['title']:$license['name']).']';
        echo'('.$license['path'].')';
    } else {
        echo $license['title']?$license['title']:$license['name'];
    }
}
?>


Script license:
    [MIT License](https://opensource.org/licenses/MIT)
<?php } ?>

Attribution:
    <?= wordwrap(Html2Text\Html2Text::convert($package['attribution']), 71, "\n    "); ?>



Version history
===========================================================================

<? $afterCurrentVersion=false; foreach($allVersions as $versionData) { if($package['version'] === $versionData['version'] || $afterCurrentVersion) { ?>
* <?=$versionData['version']?> <?=$versionData['lastChanges']?$versionData['lastChanges']:'Not documented'?>

<? $afterCurrentVersion=true;}} ?>


Resources
===========================================================================

* [Package description page](http://data.open-power-system-data.org/<?=$package['name']?>/<?=$package['version']?>/)
<?php if($package['zipFileName']) { ?>* [ZIP Package](http://data.open-power-system-data.org/<?=$package['name']?>/<?=$package['zipFileName']?>)
<?php } ?>
* [Script and documentation](<?=$package['documentation']?>)
<?php if($package['origDataPath']) { ?>* [Original input data](http://data.open-power-system-data.org/<?=$package['origDataPath']?>)
<?php } ?>


Sources
===========================================================================

<? foreach($package['sources'] as $sourceNum => $source) { ?>
<? if($source['path']) {?>
* [<?=$source['title']?>](<?=$source['path']?>)
<? } else { ?>
* <?=$source['title']."\n"?>
<? } ?>
<? } ?>

<? if(is_array(($package['resources']))){ ?>

Field documentation
===========================================================================
<?
foreach($package['resources'] as $resourceNum => $resource) { if(is_array($resource['schema']['fields']) && count($resource['schema']['fields']) > 0) { ?>

<?=$resource['path'] /*str_replace('_',' ',$resource['path'])*/?>

---------------------------------------------------------------------------

<? foreach($resource['schema']['fields'] as $fieldNum => $fieldConf) { ?>
* <?= $fieldConf['name'] ?>

    - Type: <?= $fieldConf['type'] ?>

<?= $fieldConf['format']?'    - Format: '.$fieldConf['format']."\n":'' ?>
    - Description: <?= $fieldConf['description'] ?>

<?
if(isset($fieldConf['source'])) {
    if(is_array($fieldConf['source'])) {
        if($fieldConf['source']['path'] && $fieldConf['source']['title']) {
			echo '    - Source: ['.$fieldConf['source']['title'].']('.$fieldConf['source']['path'].')';
        } elseif($fieldConf['source']['path']) {
			echo '    - Source: '.$fieldConf['source']['path'];
        } else {
			echo '    - Source: '.$fieldConf['source']['title'];
        }
    } else {
        echo $fieldConf['source'];
    }
    echo "\n";
}


?>
<? } } ?>

<? } ?>
<? } ?>
<?
/*
foreach($package['resources'] as $resourceNum => $resource) {
	if(is_array($resource['schema']['fields']) && count($resource['schema']['fields']) > 0) {

		echo '#### '.str_replace('_',' ',$resource['path'])."\n\n";

		$tableCols = array(
			'Field Name',
			'Type&nbsp;(Format)',
			'Description',
		);
		if($resource['__sourcesAreGivenAtFieldLevel']) {
			$tableCols[] = 'Source';
		}
		$tableRows = array();
		foreach($resource['schema']['fields'] as $fieldNum => $fieldConf) {
			$tableRow = array(
				$fieldConf['name'],
				$fieldConf['type'].($fieldConf['format']?' ('.$fieldConf['format'].')':''),
				$fieldConf['description']
			);

			$sourceFieldContent = '';
			if($resource['__sourcesAreGivenAtFieldLevel']) {
				if(is_array($fieldConf['source'])) {
					if($fieldConf['source']['web']) {
						$sourceFieldContent = $fieldConf['source']['name'].'('.$fieldConf['source']['web'].')';
					} else {
						$sourceFieldContent = $fieldConf['source']['name'];
					}
				} else {
					$sourceFieldContent = $fieldConf['source'];
				}
			}
			$tableRow[] = $sourceFieldContent;

			$tableRows[] = $tableRow;
		}
		
		echo $this->makeTextTable($tableCols, $tableRows)."\n\n\n";
	}
}
 */
?>

Feedback
===========================================================================

Thank you for using data provided by Open Power System Data. If you have
any question or feedback, please do not hesitate to contact us.

For this data package, contact:
<? if(is_array($package['contributors'])) foreach($package['contributors'] as $contributorNum => $contributor) { ?>
<?= ($contributorNum>0?"\n":'') ?><? if($contributor['email']) {?>
<?=$contributor['title']?> <<?=$contributor['email']?>>
<? } else { ?>
<?=$contributor['title']?>
<? } ?>
<? } ?>

For general issues, find our team contact details on our website:
http://www.open-power-system-data.org














