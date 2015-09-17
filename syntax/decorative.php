<?php
/**
 * DokuWiki plugin Pagetitle Decorative; Syntax component
 * Show page title decorative, with setting plain title in metadata of the page
 * 
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_decorative extends DokuWiki_Syntax_Plugin {

    protected $entry_pattern = '~~TITLE:(?=.*?~~)';
    protected $exit_pattern  = '~~';

    protected $pluginMode, $name;
    protected $store, $capture;

    function __construct() {
        $this->pluginMode = substr(get_class($this), 7); // drop 'syntax_' from class name
        $this->name = substr(get_class($this), 14);
    }

    function getType() { return 'baseonly';}
    function getPType() { return 'block';}
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }
    function getSort() { return 49; }

    // Connect pattern to lexer
    function connectTo($mode) {
        $this->Lexer->addEntryPattern($this->entry_pattern, $mode, $this->pluginMode);
    }
    function postConnect() {
        $this->Lexer->addExitPattern($this->exit_pattern, $this->pluginMode);
    }


    /*
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $handler->addPluginCall($this->name,array($state),$state,$pos,$match);
                return false;
            case DOKU_LEXER_UNMATCHED :
                $handler->_addCall('cdata', array($match), $pos);
                return false;
            case DOKU_LEXER_EXIT :
                $handler->addPluginCall($this->name,array($state),$state,$pos,$match);
                return false;
        }
        return false;
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        list($state) = $data;
        switch ($state) {
            case DOKU_LEXER_ENTER :
                // preserve variables
                $this->store = $renderer->doc;
                $this->capture = $renderer->capture;

                // set doc blank to store parsed "UNMATHCED" content
                $renderer->doc = '';
                // metadata renderer should always parse "UNMATCHED" content
                if ($format == 'metadata') $renderer->capture = true;

                return true;
                break;
            case DOKU_LEXER_EXIT :
                // retrieve parsed "UNMATCHED" content
                $decorative_title = trim($renderer->doc);

                // restore variable
                $renderer->doc = $this->store;
                if ($format == 'metadata') $renderer->capture = $this->capture;
                break; // do not return here
            default:
                return false; // this should never happen
        }

        // get plain title
        $title = htmlspecialchars_decode(strip_tags($decorative_title), ENT_QUOTES);
        if (empty($title)) return false;

        // output title
        $method = '_' . $format . '_render';
        if (method_exists($this, $method)) {
            return $this->$method($decorative_title, $title, $renderer);
        }
        else return false;
    }

    /**
     * Revised procedures for renderers
     */
    protected function _xhtml_render($decorative_title, $title, &$renderer) {
        // even title in <h1>, it never shown up in the table of contents (TOC)
        $renderer->doc .= '<h1 class="pagetitle">';
        $renderer->doc .= $decorative_title;
        $renderer->doc .= '</h1>'.DOKU_LF;
        return true;
    }

    protected function _metadata_render($decorative_title, $title, &$renderer) {
        $renderer->meta['title'] = $title;
        return true;
    }

}
