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
    protected $pattern = array();

    private   $counter = null;  // helper component "pagetitle_counter"
    private   $renderedOnce = null;

    protected $doc, $capture;   // store properties of $renderer
    protected $params;          // store title tag parameters

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name

        // syntax patterns
        $this->pattern[1] = '<title\b[^>\r\n]*?>(?=.*?</title>)'; // entry
        $this->pattern[4] = '</title>';                           // exit
        $this->pattern[5] = '~~Title:[^\r\n]*?~~';                // special

        // assign helper component to dedicated class property
        $this->counter = $this->loadHelper('pagetitle_counter', true);
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

        $plugin = substr(get_class($this), 14);

        switch ($state) {
            case DOKU_LEXER_SPECIAL : // ~~Title:*~~ macro syntax
                $title = trim(substr($match, 8, -2));
                return array($state, $ID, $title);

            case DOKU_LEXER_ENTER :
                // store title tag parameters
                if (($n = strpos($match, ' ')) !== false) {
                    $this->params = strtolower(trim(substr($match, $n, -1)));
                } else {
                    $this->params = '';
                }
                $data = array($state, $ID, '');
                $handler->addPluginCall($plugin, $data, $state,$pos,$match);
                return false;

            case DOKU_LEXER_UNMATCHED :
                $handler->_addCall('cdata', array($match), $pos);
                return false;

            case DOKU_LEXER_EXIT :
                $data = array($state, $ID, $this->params);
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

        list($state, $id, $param) = $data;

        switch ($state) {
            case DOKU_LEXER_SPECIAL : // ~~Title:*~~ macro syntax
                // $decorative_title = $param;
                // convert to curly quote characters depending on $conf['typography']
                $decorative_title = $this->render_text($param);
                break;

            case DOKU_LEXER_ENTER :
                // preserve variables
                $this->doc     = $renderer->doc;
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
                $renderer->doc = $this->doc;
                if ($format == 'metadata') $renderer->capture = $this->capture;
                break; // do not return here
            default:
                return false; // this should never happen
        }
        // follow up only for DOKU_LEXER_EXIT

        // skip calls of different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        // assign a closure function to the class property
        if ($this->renderedOnce === null) {
            $this->renderedOnce = $this->counter->create_counter($item);
        }

        // ensure first instruction only effective
        //if (($n = call_user_func($this->renderedOnce, $format)) > 0) { // PHP < 7.0
        if (($n = ($this->renderedOnce)($format)) > 0) {
            error_log($this->mode.' Render ignore '.$n.' '.$format.' '.$short_title.' '.$ID);
            return false;
        }

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
                $renderer->meta['title'] = $title;
                return true;
        }
        return false;
    }

}
