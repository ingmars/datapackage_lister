

<script type="application/ld+json">
<?=json_encode($metadataJsonLD,  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)?>

</script>

<h3 style="margin:0; padding: 0; padding-top: 30px; width: 300px; float:left;"><a href="https://open-power-system-data.org/">Data Platform</a></h3>

<div style="float:right;padding: 0; padding-top: 20px; ">
    Jump to Data Package: <select onChange="if(this.value !== 'dummy') window.location.href=this.value" style="width: 250px; display: inline; margin: 0;">
		<?php
		$packageTypes = array('internal', 'external');
		foreach($packageTypes as $packageType) {
		?>
            <optgroup label="<?= $packageType=='internal'?'OPSD data packages':'Contributed data packages' ?>">
                <?
				foreach($dataPackages as $otherPackage) if($otherPackage['external'] == ($packageType === 'external')) {  ?>
                    <option
                            value="<?=$otherPackage['detailLink']?>"
						<?= ($otherPackage['name'] === $package['name']?'selected="selected"':'')  ?>
                    >
						<?=$otherPackage['title']?>
                    </option>
				<? } ?>
            </optgroup>
        <? } ?>
    </select>
</div>

<table style="">
    <tr>
        <td style="padding-left: 0;" colspan="2">
            <h4><strong><?=$package['title']?></strong></h4>
            <?= isset($package['id']) && substr($package['id'], 0, 4 )=='http'?'<a href="'.$package['id'].'">'.$package['id'].'</a>':'' ?>
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0; width: 120px;">Package&nbsp;version</td>
        <td>
            <select onChange="window.location.href=this.value" style="display:inline; width: 180px; margin: 0;">
				<? if($package['hide']) { /* this seems to be a hidden (beta version) package. Therefore it won't be contained in the list of all versions. Therefore we manually add it here and mark it as selected. */ ?>
                    <option value="<?=$package['detailLink']?>" selected="selected"><?=$package['version']?> (hidden)</option>
				<? } ?>
                <? foreach($allVersions as $versionName => $versionData) { $versionCounter++ ?>
                    <option
                        value="<?=$versionData['detailLink']?>"
                        <?= ($package['version'] === $versionName?'selected="selected"':'') ?>
                    >
                        <?=$versionName?>
                        <?= ($versionCounter === 1?'(latest)':'') ?>
                    </option>
                <? } ?>
            </select>
            <div style="float: right; padding-top: 8px;">
                <a
                    title="Permalink always pointing to the latest version of this Data Package. This can be used to always fetch the latest version of the data. All links on the &quot;latest&quot; page will again have links to the latest version of all individual files contained in this Data Package, enabling stable URLs to the latest version of data files."
                    href="<?=$package['packagePath']?>latest/"
                >"latest" URL</a>
            </div>
        </td>
    </tr>
    <? if($package['version'] !== $package['version_from_json']) { ?>
        <tr>
            <td style="padding-left: 0;"><strong style="color: red;">Error</strong></td>
            <td><strong style="color: red;">The directory name of the version ("<?=$package['version']?>") is not equal to the version name as defined in the datapackage.json file ("<?=$package['version_from_json']?>") but it should be.</strong></td>
        </tr>
    <? } ?>
    <tr>
        <td style="padding-left: 0;">Description</td>
        <td><?=$package['description']?></td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Notes</td>
        <td><?=$package['longDescription']?></td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Last&nbsp;changes</td>
        <td><?=$package['lastChanges']?$package['lastChanges']:'Not documented.'?></td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Geographical scope</td>
        <td><?=is_array($package['spatial'])&&$package['spatial']['location']?$package['spatial']['location']:'Undefined'?></td>
    </tr>
    <? if(is_array($package['spatial'])&&$package['spatial']['resolution']) { ?>
        <tr>
            <td style="padding-left: 0;">Geographical resolution</td>
            <td><?=$package['spatial']['resolution']?></td>
        </tr>
    <? } ?>
    <tr>
        <td style="padding-left: 0;">Documentation</td>
        <td>
            <a
                    href="<?=str_replace('https://github.com/','https://nbviewer.jupyter.org/github/',$package['documentation'])?>"
                    target="_blank"
            >Documentation and script</a> (<a
                    href="<?=$package['documentation']?>"
                    target="_blank"
            >view on GitHub</a>)
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Download</td>
        <td>
                Data package (zip)<br>
			    <? if($package['zipFileName']){ ?>
                    <a href="<?=$package['packagePath'].$package['zipFileName']?>" target="_blank"><?=$package['zipFileName']?></a>
                    (<?= $this->human_filesize(filesize($rootPath.$package['packagePath'].$package['zipFileName']))?>)
				<? } else{ ?>
                    <strong>Error: ZIP file not yet generated by package maintainer.</strong>
                <? } ?>
			<? if(is_array($package['resources']) && count($package['resources']) > 0){ ?>
                    <br><br>
                    Individual data files (csv, xlsx)<br>
                <? foreach($package['resources'] as $resourceNum => $resource) { ?>
                    <?= ($resourceNum>0?'<br />':'') ?>
                    <?= $this->getLinkedResourceWithFilesize($package['versionPath'], $resource['path'], FALSE, $package['versionLinkPath']) ?>
                    <? if(@is_array($resource['__alternativeFormatsByType']) && count($resource['__alternativeFormatsByType']) > 0) { ?>
                        <a href="javascript:0" onclick="jQuery('#alternativeFormats<?=$resourceNum?>').slideToggle(); jQuery(this).find('img').toggle(); return false;">
                            Alternative file formats
                            <img src="/assets/expand.png" style="position:relative; top: 3px;margin:0; padding:0; height: 1em;" /><img src="/assets/collapse.png" style="position:relative; top: 3px;margin:0; padding:0; height: 1em; display:none;" /></a>
                    <? } ?>
                    <? if(@is_array($resource['__opsd-filters']) && count($resource['__opsd-filters']) > 0) { ?>
                        <a href="javascript:0" onclick="jQuery('#filter<?=$resourceNum?>').slideToggle(); jQuery(this).find('img').toggle(); return false;">
                            Filter
                            <img src="/assets/expand.png"   style="position:relative; top: 3px;margin:0; padding:0; height: 1em;" />
                            <img src="/assets/collapse.png" style="position:relative; top: 3px;margin:0; padding:0; height: 1em; display:none;" />
                        </a>
                        <div id="filter<?=$resourceNum?>" style="display:none;padding: 5px; margin-top: 5px; border: 1px solid black; width: 500px;">
                            <p style="margin-left: 10px;"><strong>Filter for "<?=$resource['path']?>"</strong></p>
                            <form action="index.php" method="get">
                                <input type="hidden" name="package" value="<?=$package['name'] ?>" />
                                <input type="hidden" name="version" value="<?=$package['version'] ?>" />
                                <input type="hidden" name="action" value="customDownload" />
                                <input type="hidden" name="resource" value="<?=$resourceNum ?>" />
                                <table border="0">
                                    <? if(@is_array($resource['__opsd-filters'])) foreach($resource['__opsd-filters'] as $filterKey => $filterConfig) { ?>
                                        <tr>
                                            <td>
                                                <?=isset($filterConfig['label'])?$filterConfig['label']:$filterKey?> <? if($filterConfig['message']) { ?><br><span style="font-size: 0.8em;"><?=$filterConfig['message']?></span><?php } ?>
                                            </td>
                                            <td>
                                                <?php if($filterConfig['type'] == 'date') { ?>
                                                    <script type="application/javascript">
                                                        jQuery(function() {
                                                            datePickerConf = {
                                                                defaultDate: "2001-01-01",
                                                                changeMonth: true,
                                                                changeYear: true,
                                                                numberOfMonths: 1,
                                                                dateFormat: 'yy-mm-dd',
                                                                yearRange: "1970:<?=date("Y")?>",
                                                                minDate: "<?=$filterConfig['minDate']?>",
                                                                maxDate: "<?=$filterConfig['maxDate']?>"
                                                            };
                                                            datePickerConf_from = datePickerConf;
                                                            datePickerConf_from.onClose = function( selectedDate ) {
                                                                jQuery( "#<?=$filterKey?>_to<?=$resourceNum?>" ).datepicker( "option", "minDate", selectedDate );
                                                            };
                                                            datePickerConf_to = jQuery.extend({}, datePickerConf); // copy datePickerConf object. A simple "=" would have resulted in a reference.
                                                            datePickerConf_to.onClose = function( selectedDate ) {
                                                                jQuery( "#<?=$filterKey?>_from<?=$resourceNum?>" ).datepicker( "option", "maxDate", selectedDate );
                                                            };
                                                            jQuery( "#<?=$filterKey?>_from<?=$resourceNum?>" ).datepicker(datePickerConf_from);
                                                            jQuery( "#<?=$filterKey?>_to<?=$resourceNum?>" ).datepicker(datePickerConf_to);
                                                        });
                                                    </script>
                                                    <label for="<?=$filterKey?>_from<?=$resourceNum?>">From</label>
                                                    <input type="text" id="<?=$filterKey?>_from<?=$resourceNum?>" name="filter[<?=$filterKey?>][from]" value="<?=$filterConfig['minDate']?>">
                                                    <label for="<?=$filterKey?>_to<?=$resourceNum?>">To</label>
                                                    <input type="text" id="<?=$filterKey?>_to<?=$resourceNum?>" name="filter[<?=$filterKey?>][to]" value="<?=$filterConfig['maxDate']?>">
                                                <?php } else { ?>
                                                    <select title="Select items to include" multiple="multiple" name="filter[<?=$filterKey?>][]" style="width: 250px;">
                                                        <? foreach($filterConfig['optionItems'] as $itemKey => $itemValue) { ?>
                                                            <option value="<?=htmlspecialchars($itemKey) ?>" selected="selected"><?= htmlspecialchars($itemValue)?></option>
                                                        <? } ?>
                                                    </select><br>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td>
                                        </td>
                                        <td>
                                            <!--<input style="margin-top: 5px;"  type="submit" onclick="return confirm('Note: XLSX download will take significantly longer than CSV and sometimes result in corrupted files, especially for large datasets. We suggest CSV instead. Do you want to proceed anyway?')" name="downloadXLSX" value="Download XLSX" />-->
                                            <input style="margin-top: 5px;"  type="submit" name="downloadCSV" value="Download CSV" />
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    <? } ?>
                    <? if(@is_array($resource['__alternativeFormatsByType']) && count($resource['__alternativeFormatsByType']) > 0) { ?>
                        <div id="alternativeFormats<?=$resourceNum?>" style="display:none;padding: 5px; margin-top: 5px; border: 1px solid black; width: 400px;">
                            <p><strong>Alternative formats for "<?=$resource['path']?>"</strong></p>
                            We provide time series data in two file types (csv and xlsx), two granularities (15 min and 60 min), and three formats (singleindex, multiindex, stacked). They all have pros and cons. The three formats differ in the way data and variable names are organized:
                            <ul style="margin-left: 25px; margin-top: 10px;">
                                <?php if(isset($resource['__alternativeFormatsByStacking']['Singleindex'])) { ?>
                                    <li><strong>Singleindex</strong> (easy to read for humans, compatible with datapackage standard, small file size)
                                        <br><img src="assets/singleindex.png" />
                                    </li>
							    <?php } ?>
						        <?php if(isset($resource['__alternativeFormatsByStacking']['Multiindex'])) { ?>
                                    <li><strong>Multiindex</strong> (easy to read into GAMS, not compatible with datapackage standard, small file size)
                                        <br><img src="assets/multiindex.png" />
                                    </li>
					            <?php } ?>
								<?php if(isset($resource['__alternativeFormatsByStacking']['Stacked'])) { ?>
                                    <li><strong>Stacked</strong> (compatible with data package standard, large file size, many rows, too many for Excel)
                                        <br><img src="assets/stacked.png" />
                                    </li>
				                <?php } ?>
                            </ul>
                            <p><strong>Download alternatives to "<?=$resource['path']?>":</strong></p>
                            <table border="0" style="margin-top: -15px;">
                                <tr>
                                    <td></td>
                                    <? foreach($resource['__alternativeFormatsByType'] as $altFormatType => $altFormatsWithCurrentType) { ?>
                                        <td><strong><?=$altFormatType?></strong></td>
                                    <? } ?>
                                </tr>
                                <? foreach($resource['__alternativeFormatsByStacking'] as $altFormatStacking => $altFormatsWithCurrentStacking) { ?>
                                    <tr>
                                        <td><strong><?=$altFormatStacking?></strong></td>
										<? foreach($resource['__alternativeFormatsByType'] as $altFormatType => $altFormatsWithCurrentType) { ?>
                                            <td>
                                                <?php
                                                if(isset($altFormatsWithCurrentStacking[$altFormatType])) {
												$altFormatFile = $altFormatsWithCurrentStacking[$altFormatType];
                                                ?>
												    <?= $this->getLinkedResourceWithFilesize($package['versionPath'], $altFormatFile['path'], $altFormatType, $package['versionLinkPath']) ?>
												<? } ?>
                                            </td>
										<? } ?>
                                    </tr>
                                <? } ?>
                            </table>
                        </div>
                    <? } ?>
                <? } ?>
			<? } ?>
			<? if($package['sqliteFile']) { ?>
                <br><br>
                Database (SQLite)<br>
				<?= $this->getLinkedResourceWithFilesize($package['versionPath'], $package['sqliteFile'], FALSE, $package['versionLinkPath']) ?>
			<? } ?>
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Meta data</td>
        <td>
            <a href="<?=$package['versionLinkPath']?>README.md" target="_blank">README.md</a><br />
            <a href="<?=$package['versionLinkPath']?>datapackage.json" target="_blank">datapackage.json</a><!-- (<?= $this->human_filesize(filesize($rootPath.$package['versionPath'].'datapackage.json'))?>)-->
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Input&nbsp;data</td>
        <td>
            <?=(
            $package['origDataPath'] ?
                '<a href="'.$package['origDataPath'].'" target="_blank">View original input data</a>':
                'Not&nbsp;provided'
            )
            ?>
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Sources</td>
        <td>
			<?
			$allSourceNames = array();
			foreach($package['sources'] as $sourceNum => $source) {
				$allSourceNames[] = $source['title'];
				?>
				<?= ($sourceNum>0?'|':'') ?>
				<? if($source['path']) {?>
                    <a href="<?=$source['path']?>" target="_blank"><?=$source['title']?></a>
				<? } else { ?>
					<?=$source['title']?>
				<? } ?>
			<? } ?>
        </td>
    </tr>
	<? if(is_array($package['licenses']) && count($package['licenses'])>0) foreach($package['licenses'] as $licenseNum => $license)  if($license['path'] || ($license['title']?$license['title']:$license['name'])) { ?>
        <tr>
            <td style="padding-left: 0;">Data license</td>
            <td>

                    <? if($license['path']) { ?>
                        <a href="<?=$license['path']?>" target="_blank">
                            <?= $license['title']?$license['title']:$license['name'] ?>
                        </a>
                    <? } else { ?>
						<?= $license['title']?$license['title']:$license['name'] ?>
                    <? } ?>
            </td>
        </tr>
	<? } ?>
    <tr>
        <td style="padding-left: 0;">Report issue</td>
        <td>
            <a href="<?=dirname(dirname(dirname($package['documentation'])))?>/issues"
               target="_blank"
            >Report an issue on Github</a>
            </a>
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Contact</td>
        <td>
            <? if(is_array($package['contributors'])) foreach($package['contributors'] as $contributorNum => $contributor) { ?>
                <?= ($contributorNum>0?'|':'') ?>
                <? if($contributor['email']) {?>
                    <a href="mailto:<?=$contributor['email']?>" target="_blank"><?=$contributor['title']?></a>
                <? } else { ?>
                    <?=$contributor['title']?>
                <? } ?>
            <? } ?>
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;">Subscribe</td>
        <td>
            <a
                target="_blank"
                href="https://docs.google.com/forms/d/e/1FAIpQLSfisFPxcdvblbq8DTWRkbrPouWw3d1N2VN1ykd-fpfA81uf8A/viewform?entry.1001090486&entry.301086874=<?=urlencode($package['title'])?>"
            >
                Get notified by email for updates
            </a>
        </td>
    </tr>
    <tr>
        <td style="padding-left: 0;" colspan="2">
            <span style="font-size: smaller;">
                <?= $package['attribution'] ?>
            </span>
        </td>
    </tr>
</table>

<a href="/">&lt;- back to overview</a>


<? if(is_array($package['resources'])) { ?>
    <h3>Field documentation</h3>

    <? foreach($package['resources'] as $resourceNum => $resource) { if(is_array($resource['schema']['fields']) && count($resource['schema']['fields']) > 0) { ?>
        <div style="margin-bottom: 20px;">
            <h4 style="border-bottom: 1px solid black;"><?= str_replace('_',' ',$resource['path']) ?></h4>
            <table class="dataTable display">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Type&nbsp;(Format)</th>
                        <th>Description</th>
                        <? if($resource['__sourcesAreGivenAtFieldLevel']) { ?>
                            <th>Source</th>
                        <? } ?>
                    </tr>
                </thead>
                <tbody>
                    <? foreach($resource['schema']['fields'] as $fieldNum => $fieldConf) { ?>
                        <tr>
                            <td><?=$fieldConf['name']?></td>
                            <td><?=$fieldConf['type']?><?=($fieldConf['format']?' ('.$fieldConf['format'].')':'')?></td>
                            <td><?=$fieldConf['description']?></td>
                            <? if($resource['__sourcesAreGivenAtFieldLevel']) { ?>
                                <td>
                                    <? if(is_array($fieldConf['source'])) { ?>
                                        <? if($fieldConf['source']['web']) {?>
                                            <a href="<?=$fieldConf['source']['web']?>" target="_blank"><?=$fieldConf['source']['name']?></a>
                                        <? } else { ?>
                                            <?=$fieldConf['source']['name']?>
                                        <? } ?>
                                    <? } elseif(filter_var($fieldConf['source'], FILTER_VALIDATE_URL)) { ?>
                                        <a href="<?=$fieldConf['source']?>" target="_blank">Source</a>
                                    <? } else { ?>
                                        <?=$fieldConf['source']?>
                                    <? } ?>
                                </td>
                            <? } ?>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>
    <? }} ?>
<? } ?>

<script type="text/javascript">
    // Enable all multiselects
    jQuery(function(){
        jQuery("select[multiple='multiple']").multiselect({
            selectedList: 10             // 0-based index
        });
    });
</script>

<a href="/">&lt;- back to overview</a>