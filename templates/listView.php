<?php if(!$iframeMode) { ?>
    <h3>Data Platform</h3>
    <p>This is the Open Power System Data platform. We provide European power system data in five packages:</p>
<?php } ?>

<table style="border:none; margin:0; padding:0;">

    <?php
    $tableNames = array('internal', 'external');
    foreach($tableNames as $tableName) {
        if($iframeMode) {
            $fieldStyle = 'border:none; background:none; padding-left:0;';
        } else {
            $fieldStyle = "background-color: ".($tableName=='internal'?'#d9edf7':'#F2DAF7').";";
        }
        ?>
        <tr>
            <td colspan="4" style="border: none; background:none; margin:0; padding:<?=($iframeMode?"0 0 10px 0":"0 0 10px 0")?>;"><h4 style="border: none; margin:30px 0 0 0; padding:0;"><?= $tableName=='internal'?'OPSD data packages':'Contributed data packages' ?></h4></td>
        </tr>
            <? if(!$iframeMode) { ?>
                <tr>
                    <td style="<?=$fieldStyle?>"><strong>Data package</strong></td>
                    <td style="<?=$fieldStyle?>"><strong>Description</strong></td>
                    <td style="<?=$fieldStyle?>"><strong>Download</strong>&nbsp;&nbsp;&nbsp;</td>
                    <td style="<?=$fieldStyle?>"><strong>Docs</strong></td>
                </tr>
            <?php } ?>

            <?php foreach($dataPackages as $package) if($package['external'] == ($tableName === 'external')) { ?>
                <tr>
                    <td style="width: 220px; <?=$fieldStyle?>"><strong><a title="Go to detail page" target="_top" href="<?=$package['detailLink']?>"><?=$package['title']?></a></strong></td>
                    <td style="<?=$fieldStyle?>"><?=$package['description']?></td>
                    <td style="<?=$fieldStyle?>">
                        <?php if($package['zipFileName']) { ?>
                            <a title="Download this version" target="_top" href="<?=$package['packagePath'].$package['zipFileName']?>" target="_blank"><?=$package['version']?></a><!--(<?= $this->human_filesize(filesize($rootPath.$package['packagePath'].$package['zipFileName']))?>)-->
                        <? } else { ?>
                            Error: ZIP file not yet generated.
                        <?php } ?>
                    </td>
                    <td style="<?=$fieldStyle?>"><a title="View documentation" target="_blank" href="<?=str_replace('https://github.com/','https://nbviewer.jupyter.org/github/',$package['documentation'])?>">Docs</a></td>
                </tr>
            <?php } ?>
    <?php } ?>
</table>

<? if(!$iframeMode) { ?>
    <link rel='stylesheet' id='tablepress-default-css'  href='https://open-power-system-data.org/wp-content/tablepress-combined.min.css?ver=36' type='text/css' media='all' />

    <br>
    <h4 id="data_availability">Data availability overview</h4>

    <table id="tablepress-data-availability" class="tablepress tablepress-id-data-availability">
        <tbody>
            <tr class="row-1">
                <td rowspan="2" class="column-1"><!--Version: latest--></td>
                <?php foreach($dataAvailabilities as $packageName => $packageSeries) { ?>
                    <td colspan="<?=count($packageSeries)-(isset($packageSeries['_url'])?1:0)?>" class="column-2" <?=(isset($packageSeries['_all'])?'rowspan="2"':'')?>>
                        <?php if(isset($packageSeries['_url'])) { ?>
                            <a target="_top" href="<?=$packageSeries['_url']?>" target="_blank">
                                <?=$packageName?>
                            </a>
                        <?php } else { ?>
                            <?=$packageName?>
                        <?php } ?>
                    </td>
                <?php } ?>
            </tr>
            <tr class="row-2">
                <?php foreach($dataAvailabilities as $packageName => $packageSeries) { ?>
                    <?php foreach($packageSeries as $seriesName => $seriesData) { ?>
                        <?php if($seriesName === '_url' || $seriesName === '_all') {continue;} ?>
                        <td class="column-2">
                            <strong>
                                <?php if(isset($seriesData['_url'])) { ?>
                                    <a target="_top" href="<?=$seriesData['_url']?>" target="_blank">
                                        <?=$seriesName?>
                                    </a>
                                <?php } else { ?>
                                    <?=$seriesName?>
                                <?php } ?>
                            </strong>
                        </td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <?php foreach($availabilityCountryList as $countryName) { ?>
                <tr class="row-3">
                    <td class="column-1"><?=$countryName?></td>
                    <?php foreach($dataAvailabilities as $packageName => $packageSeries) { ?>
                        <?php if(is_array($packageSeries)) foreach($packageSeries as $seriesName => $seriesData) { ?>
                            <?php if($seriesName === '_url') {continue;} ?>
                            <?php
                            if(isset($packageSeries['_url']))
                                $linkJS = ' onclick="window.location.href = \''.$packageSeries['_url'].'\';"';
                            elseif(isset($seriesData['_url']))
                                $linkJS = ' onclick="window.location.href = \''.$seriesData['_url'].'\';"';
                            else
                                $linkJS = '';
                            ?>
                            <?php  ?>
                            <?php if(is_array($seriesData) && isset($seriesData[$countryName])) { ?>
                                <td style="background-color: #d9edf7;" class="column-3" <?=$linkJS?>><?=$seriesData[$countryName]===true?'':(is_int($seriesData[$countryName])?$seriesData[$countryName].'+':$seriesData[$countryName])?></td>
                            <?php } elseif($seriesName === '_all' && $seriesData === true) { ?>
                                <td style="background-color: #d9edf7;" class="column-3" <?=$linkJS?>></td>
                            <?php } else { ?>
                                <td class="column-2"></td>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <br>
    <h4>Need help? Want more information?</h4>
    <ul style="margin-left: 35px;">
        <li><a target="_top" href="https://open-power-system-data.org/step-by-step" target="_blank">Step-by-step manual: how to use the data platform</a></li>
        <li><a target="_top" href="https://open-power-system-data.org/contribute">Your data here? Contribute</a></li>
        <li><a target="_top" href="https://open-power-system-data.org/it">IT concept and glossary</a></li>
        <li>Data processing scripts, documentation, list of data sources, data in different file formats, original (raw) data: click on data package title above</li>
        <li><a target="_top" href="mailto: muehlenpfordt@neon-energie.de" target="_blank">Email us</a> or give us a <a target="_top" href="tel:+4915755199715" target="_blank">call</a></li>
    </ul>

    <p style="font-size: smaller">
        Attribution in Chicago author-date style should be given as follows: "<span style="font-style: italic">Open Power System Data. [Year]. Data Package [Data package Title]. Version [version]. [URL]. [(remark on primary sources)].</span>"
    </p>

    <p style="font-size: smaller">Disclaimer: Data might be subject to copyright or related rights. Please consult the primary data owner.</p>
<?php } ?>