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
 *        wiki source:    <title> H<sub>2</sub>O </title>
 *        page (html):    <h1 class="pagetitle">H<sub>2</sub>O</h1>
 *        title metadata: H2O
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_decorative extends DokuWiki_Syntax_Plugin {

    protected $mode;
    protected $pattern = [];

    function __construct() {
        // syntax mode,  drop 'syntax_' from class name
        $this->mode = substr(get_class($this), 7);

        // syntax patterns
        $this->pattern[1] = '<title\b[^\n>]*>(?=.*?</title>)'; // entry
        $this->pattern[4] = '</title>';                        // exit
        $this->pattern[5] = '~~Title:[^\n~]*~~';               // special
    }

    function getType() { return 'baseonly';}
    function getPType() { return 'block';}
    function getSort() { return 49; }
    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->pattern[5], $mode, $this->mode);
        $this->Lexer->addEntryPattern($this->pattern[1], $mode, $this->mode);
    }
    function postConnect() {
        $this->Lexer->addExitPattern($this->pattern[4], $this->mode);
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;
        static $params; // store title tag parameters

        $plugin = substr(get_class($this), 14);

        switch ($state) {
            case DOKU_LEXER_SPECIAL : // ~~Title:*~~ macro syntax
                $title = trim(substr($match, 8, -2));
                return $data = [$state, $ID, $title];

            case DOKU_LEXER_ENTER :
                // store title tag parameters
                $params = strtolower(trim(substr($match, 6, -1)));
                $data = [$state, $ID, ''];
                $handler->addPluginCall($plugin, $data, $state,$pos,$match);
                return false;

            case DOKU_LEXER_UNMATCHED :
                $data = [$match];
                $handler->_addCall('cdata', $data, $pos);
                return false;

            case DOKU_LEXER_EXIT :
                $data = [$state, $ID, $params];
                $handler->addPluginCall($plugin, $data, $state,$pos,$match);
                return false;
        }
        return false;
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        global $ID;
        static $doc, $capture; // store properties of $renderer
        static $counter = [];

        list ($state, $id, $param) = $data;

        switch ($state) {
            case DOKU_LEXER_SPECIAL : // ~~Title:*~~ macro syntax
                // $decorative_title = $param;
                // convert to curly quote characters depending on $conf['typography']
                $decorative_title = $this->render_text($param);
                break;

            case DOKU_LEXER_ENTER :
                // preserve variables
                $doc     = $renderer->doc;
                $capture = $renderer->capture;

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
                $renderer->doc = $doc;
                if ($format == 'metadata') $renderer->capture = $capture;
                break; // do not return here
            default:
                return false; // this should never happen
        }
        // follow up only for DOKU_LEXER_EXIT

        // skip calls that belong to different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        // ensure first instruction only effective
        if ($counter[$format]++ > 0) return false;

        // get plain title
        $title = trim(htmlspecialchars_decode(strip_tags($decorative_title), ENT_QUOTES));
        if (empty($title)) return false;

        // output title
        switch ($format) {
            case 'xhtml':
                if ($state == DOKU_LEXER_SPECIAL) return false;
                if (($wrap = $this->loadHelper('wrap')) != NULL) {
                    $attr = $wrap->buildAttributes($param, 'pagetitle');
                } else {
                    $attr = ' class="pagetitle"';
                }

                // even title in <h1>, it never shown up in the table of contents (TOC)
                $renderer->doc .= '<h1'.$attr.'>';
                $renderer->doc .= $decorative_title;
                $renderer->doc .= '</h1>'.DOKU_LF;
                return true;

            case 'text':
                $renderer->doc .= $title . DOKU_LF;
                return true;

            case 'metadata':
                // set metadata for metadata indexer
                $renderer->meta['plugin']['pagetitle'] = $ID;

                if ($this->getConf('usePersistent')) {
                    // metadata persistence
                    $renderer->persistent['title'] = $title;
                    $renderer->meta['title'] = $title;
                } else {
                    // erase persistent title metadata if defined
                    unset($renderer->persistent['title']);
                    $renderer->meta['title'] = $title;
                }
                return true;
        }
        return false;
    }

}
