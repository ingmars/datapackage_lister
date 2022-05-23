<?php

require_once ('libs/class.TextTable.php');

class TemplateEngine {
	protected $template_dir = 'templates/';
	protected $vars = array();
	public function __construct($template_dir = null) {
		if ($template_dir !== null) {
			// Check here whether this directory really exists
			$this->template_dir = $template_dir;
		}
	}

	// $mode can be html or textOnly
	public function render($template_file, $mode='html', $returnOutput=false) {
		if (!file_exists($this->template_dir.$template_file)) {
			throw new Exception('no template file ' . $template_file . ' present in directory ' . $this->template_dir);
		}

		if($returnOutput || $mode==='iframe') {
			ob_start();
		}

		extract($this->vars);
		switch($mode) {
            case 'textOnly':
                if(!$returnOutput) {
                    header('Content-Type:text/plain');
                }
                include $this->template_dir.$template_file;
            break;
            case 'iframe':
                include $this->template_dir.'includes/01_header.html';
                $skeleton = ob_get_contents();
                ob_end_clean();

                $parts = explode("<body",$skeleton);
                echo $parts[0].'<body>';
                include $this->template_dir.$template_file;
                echo '</body>';
            break;
			default:
				include $this->template_dir.'includes/01_header.html';
				include $this->template_dir.$template_file;
				include $this->template_dir.'includes/02_footer.html';
		}
		if($returnOutput) {
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}

	public function __set($name, $value) {
		$this->vars[$name] = $value;
	}

	public function __get($name) {
		return $this->vars[$name];
	}

	public function getLinkedResourceWithFilesize($versionPath, $fileName, $linkTitle=FALSE, $versionLinkPath=FALSE) {
		if($linkTitle === FALSE) {
			$linkTitle = $fileName;
		}
		$filePathAndName = $this->vars['rootPath'].$versionPath.$fileName;
		if(@file_exists($filePathAndName)) {
			$content = '<a href="'.($versionLinkPath?$versionLinkPath:$versionPath).$fileName.'" target="_blank">'.$linkTitle.'</a> ('. $this->human_filesize(filesize($filePathAndName)).')';
		} else {
			$content = '<strong>Error:</strong> File "'.$fileName.'" does not exist.';
		}
		return $content;
	}

	// from http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
	public function human_filesize($bytes, $decimals = 0) {
		$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . '&nbsp;'.@$size[$factor];
	}

	public function makeTextTable($columns, $rows, $align=NULL) {
		$t = new TextTable($columns, $rows);
		if($align) {
			$t->setAlgin($align);
		}
		return $t->render();
	}
}
