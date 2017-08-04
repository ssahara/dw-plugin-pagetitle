<?php
/**
 * DokuWiki plugin PageTitle Breadcrumb; Syntax component
 * Macro to set the short title of the page in metadata
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_breadcrumb extends DokuWiki_Syntax_Plugin {

    protected $mode;
    protected $pattern = array();

    protected $check = array(); // ensure first matched pattern only effective

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name

        //syntax patterns
        $this->pattern[5] = '~~ShortTitle:.*?~~';
    }

    function getType() { return 'substition'; }
    function getPType(){ return 'normal'; }
    function getSort() { return 990; }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->pattern[5], $mode, $this->mode);
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;

        if ($this->check[$ID]++) {
            return false; // ignore match after once handled
        }

        $short_title = trim(substr($match, 13, -2));
        return array($state, $short_title, $ID);
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        global $ID;

        list($state, $short_title, $id) = $data;

        // skip calls that belong to different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        switch ($format) {
            case 'metadata':
                $renderer->meta['shorttitle'] = $short_title;
                return true;
            default:
                return false;
        }
    }

}
