<?php
/**
 * DokuWiki plugin PageTitle YouAreHere; Syntax component
 * Hierarchical breadcrumbs
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_youarehere extends DokuWiki_Syntax_Plugin {

    function getType() { return 'substition'; }
    function getPType(){ return $PType; }
    function getSort() { return 990; }

    /**
     * Connect pattern to lexer
     */
    protected $mode, $pattern, $PType = 'block';

    function preConnect() {
        // syntax mode, drop 'syntax_' from class name
        $this->mode = substr(get_class($this), 7);

        //syntax patterns
        $this->pattern[5] = '<!-- ?YOU_ARE_HERE ?-->';
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->pattern[5], $mode, $this->mode);
        $this->Lexer->addSpecialPattern('<!-- ?YOU_ARE_HERE -inline?-->', $mode, $this->mode);
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;
        return array($state, $match, $ID);
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        global $ID;

        list($state, $match, $id) = $data;

        // change html-tag and PType when inline keyword is found
        if(preg_match('/\-inline/', $match)) {
           $html_tag = 'span';
           $PType = 'normal';
        }
        else {
            $html_tag = 'div';
        }

        // skip calls that belong to different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        $template = $this->loadHelper('pagetitle');

        if ($format == 'xhtml') {
            $renderer->doc .= DOKU_LF.$match.DOKU_LF; // html comment
            $renderer->doc .= '<'.$html_tag.' class="youarehere">';
            $renderer->doc .= $template->html_youarehere(1); // start_depth = 1
            $renderer->doc .= '</'.$html_tag.'>'.DOKU_LF;
        }
        return true;
    }

}
