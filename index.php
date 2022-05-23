<?php
/**
 * This is the entry point for starting the datapackage lister
 *
 * Author: Ingmar Schlecht
 * License: GNU General Public License v3.0
 */


header('Content-Type: text/html; charset=utf-8');
require_once('class.TemplateEngine.php');
require_once('class.DatapackageLister.php');
require_once(dirname(__FILE__).'/libs/class.Html2Text.php');

//error_reporting(E_ERROR | E_PARSE); // E_WARNING

$lister = new DatapackageLister();
$lister->controller();

