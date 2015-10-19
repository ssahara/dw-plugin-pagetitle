<?php
/**
 * DokuWiki plugin PageTitle Breadcrums; Syntax component
 * Macro to set the short title of the page in metadata
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_breadcrumb extends DokuWiki_Syntax_Plugin {

    protected $special_pattern = '~~ShortTitle:.*?~~';
    protected $check = array(); // ensure first matched pattern only effective

    function __construct() {
        $this->pluginMode = substr(get_class($this), 7); // drop 'syntax_' from class name
    }

    public function getType() { return 'substition'; }
    public function getPType(){ return 'normal'; }
    public function getSort() { return 990; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->special_pattern, $mode, $this->pluginMode);
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    public function render($format, Doku_Renderer $renderer, $data) {

        if ($this->check[$format]++) return false;
        list($state, $match) = $data;
        list($key, $value) = explode(':', substr($match, 2, -2), 2);
        $key = strtolower($key);

        if ($format == 'metadata') {
             $renderer->meta[$key] = trim($value);
        }
        return true;
    }

}
