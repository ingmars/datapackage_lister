<p></p>
<h3>ZIP Creator</h3>

Internal: Use this tool to generate ZIP files for the datapackages. This must be done manually for each new version of a datapackage.
<br><br>Note: After clicking "Create ZIP", <strong>wait until the page finishes loading</strong>, otherwise you might create corrupted ZIP files.
<br><br>
<table style="width: 1000px; padding: 50px 50px 50px 50px;">
    <tr>
        <td><strong>Data package</strong></td>
        <td><strong>Version</strong></td>
        <td><strong>ZIP exists?</strong></td>
        <td><strong>ZIP last created</strong></td>
        <td><strong>Action</strong></td>
    </tr>

    <?php foreach($dataPackages as $package) { foreach($package as $version) {  ?>
        <tr>
            <td><?=$version['name']?></td>
            <td><a href="<?=$version['detailLink']?>"><?=$version['version']?></a><?php if($version['hide']) { ?> (hidden)<?php }?></td>
            <td>
                <?php if($version['zipFileName']) {?>
                    <a href="data/<?=$version['name'].'/'.$version['zipFileName']?>">ZIP</a> (<?= $this->human_filesize(filesize($rootPath.$version['packagePath'].$version['zipFileName']))?>)
                <?php } ?>
            </td>
            <td>
                <?php if($version['zipFileName']) {?>
                    <?=  date("Y-m-d H:i",filemtime($rootPath.$version['packagePath'].$version['zipFileName'])) ?>
                <?php } ?>
            </td>
            <td><a href="index.php?zipCreator&zipPackage=<?=$version['name']?>&zipVersion=<?=$version['version']?>&subAction=createZip">Create ZIP</a></td>
        </tr>
    <?php }} ?>
</table>

<? if($statusMessage) { ?>
    <div style="border: 1px solid black;">
        <strong>Message: <?=$statusMessage?></strong>
    </div>
<? } ?>