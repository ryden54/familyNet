<?php
class Context_Resource
{
	const JS = 'js';
	const CSS = 'CSS';
	const HTML = 'HTML';

	const INLINE = 'inline';
	const FILE = 'file';

	protected $nature, $type, $content;
	public function __construct($n, $t, $content) {
		$this->nature = $n;
		$this->type = $t;
		$this->content = $content;

		if ($this->type === self::FILE && substr($this->content, 0, 1) === '/') {
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $this->content) === false) {
				displayError("Missing resource", "Couldn't find the ressource file '" . $this->content . "'");
			}
		}
	}

	public function getNature() {
		return $this->nature;
	}

	public function getType() {
		return $this->type;
	}

	public function getContent() {
		return $this->content;
	}

	public function getHash() {
		return md5($this->content);
	}
}
