<?php

$inputHtml = file_get_contents('https://open-power-system-data.org/data-beta-frame/');
$inputHtml = str_replace('http://open-power-system-data.org/','https://open-power-system-data.org/', $inputHtml);
header('Content-Type: text/html; charset=utf-8');
list($beforeContent, $afterContent) = explode('<p>DO NOT DELETE THIS PAGE NOR CHANGE ITS URL AWAY FROM data-beta-frame/ (IT IS NEEDED BY DATA PLATFORM)</p>', $inputHtml);
list($beforeContent_head, $beforeContent_body) = explode('</head>', $beforeContent);

$newHeadContent = '<!-- NEW FOR JQUERY MULTISELECTOR -->
    <link rel="stylesheet" type="text/css" href="/libs/jquery-ui-multiselect-widget-gitrepo/jquery.multiselect.css" />
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/flick/jquery-ui.css">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/libs/jquery-ui-multiselect-widget-gitrepo/src/jquery.multiselect.js"></script>
    <link rel="stylesheet" type="text/css" href="/libs/DataTables/datatables.min.css"/>
	<script type="text/javascript" src="/libs/DataTables/datatables.min.js"></script>
    <style>
    	h1,h2,h3,h4,h5 {
    		font-family: "Arial","Helvetica",sans-serif;
    	}
        .ui-widget {
            font-size:80%;
        }
        .ui-multiselect-checkboxes li label span {
            font-size: 80%;
        }
        .ui-multiselect-checkboxes li label input {
            margin-right: 5px;
        }
        .ui-datepicker-group tr {
            font-size: 80%;
        }
        table td, table th {
        	text-align: left;
        }
    </style>
    <!-- END NEW FOR JQUERY MULTISELECTOR -->
';

$newStuffForBeginningOfHeadTag = '
    <!-- BASE URL HERE: -->
    <base href="//data.open-power-system-data.org/" />
    <!-- END BASE URL -->';

list($docTypeDefinition, $htmlHeadPart) = explode('<head>', $beforeContent_head);

$beforeContent =
	$docTypeDefinition.'<head>'.$newStuffForBeginningOfHeadTag.$htmlHeadPart
	.$newHeadContent
	.'</head>'
	.$beforeContent_body
;

$filePathHeader = '../01_header_new.html';
$filePathFooter = '../02_footer_new.html';

file_put_contents($filePathHeader, $beforeContent);
chmod($filePathHeader, 0777);

file_put_contents($filePathFooter, $afterContent);
chmod($filePathFooter, 0777);

$totalContent =
	$beforeContent
	.'OK, header and footer were generated, but not put live yet. If this very page looks fine (with OPSD header and footer), you can proceed to putting the new header and footer live by clicking <a href="templates/includes/generator/put_new_templates_live.php">here</a>.'
	.$afterContent
;

echo $totalContent;