<?php
/**
 * DokuWiki plugin PageTitle Decorative; Syntax component
 * Show decorative title on the page, with setting plain title in metadata
 * 
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 * The title text can contain wiki formatting markups such as bold,
 * itlic, subscript and superscript, but title metadata remains simple
 * plain text without any markup.
 *   example
 *        wiki source:    <title> H<sub>s</sub>O </title>
 *        page (html):    <h1 class="pagetitle">H<sub>2</sub>O</h1>
 *        title metadata: H2O
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_decorative extends DokuWiki_Syntax_Plugin {

    protected $entry_pattern = '<title\b[^>\r\n]*?>(?=.*?</title>)';
    protected $exit_pattern  = '</title>';

    protected $mode, $name;
    protected $store, $capture;
    protected $params;          // store title tag parameters
    protected $check = array(); // ensure first title only effective, used in render()

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name
        $this->name = substr(get_class($this), 14);
    }

    function getType() { return 'baseonly';}
    function getPType() { return 'block';}
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }
    function getSort() { return 49; }

    // Connect pattern to lexer
    function connectTo($mode) {
        $this->Lexer->addEntryPattern($this->entry_pattern, $mode, $this->mode);
    }
    function postConnect() {
        $this->Lexer->addExitPattern($this->exit_pattern, $this->mode);
    }


    /*
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;

        if ($this->check[$ID] > 0) {
            return false; // ignore match after once handled
        }

        switch ($state) {
            case DOKU_LEXER_ENTER :
                $params = strtolower(trim(substr($match, strpos($match,' '), -1)));
                $data = array($state, $ID, $params);
                $handler->addPluginCall($this->name, $data, $state,$pos,$match);
                return false;
            case DOKU_LEXER_UNMATCHED :
                $handler->_addCall('cdata', array($match), $pos);
                return false;
            case DOKU_LEXER_EXIT :
                $data = array($state, $ID, '');
                $handler->addPluginCall($this->name, $data, $state,$pos,$match);
                $this->check[$ID]++;
                return false;
        }
        return false;
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        global $ID;

        list($state, $id, $params) = $data;
        switch ($state) {
            case DOKU_LEXER_ENTER :
                // store title tag parameters
                $this->params = $params;
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
        if (strcmp($id, $ID) !== 0) return false;

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
    protected function _xhtml_render($decorative_title, $title, $renderer) {
        if (($wrap = $this->loadHelper('wrap')) != NULL) {
            $attr = $wrap->buildAttributes($this->params, 'pagetitle');
        } else $attr = ' class="pagetitle"';

        // even title in <h1>, it never shown up in the table of contents (TOC)
        $renderer->doc .= '<h1'.$attr.'>';
        $renderer->doc .= $decorative_title;
        $renderer->doc .= '</h1>'.DOKU_LF;
        return true;
    }

    protected function _metadata_render($decorative_title, $title, $renderer) {
        $renderer->meta['title'] = $title;
        return true;
    }

}
