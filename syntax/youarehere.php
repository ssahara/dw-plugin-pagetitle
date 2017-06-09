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

    protected $special_pattern = '<!-- ?YOU_ARE_HERE ?-->';
    protected $mode;

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name
    }

    public function getType() { return 'substition'; }
    public function getPType(){ return 'block'; }
    public function getSort() { return 990; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->special_pattern, $mode, $this->mode);
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    public function render($format, Doku_Renderer $renderer, $data) {

        list($state, $match) = $data;
        $template = plugin_load('helper','pagetitle');

        if ($format == 'xhtml') {
            $renderer->doc .= DOKU_LF.$match.DOKU_LF; // html comment
            $renderer->doc .= '<div class="youarehere">';
            $renderer->doc .= $template->html_youarehere(1); // start_depth = 1
            $renderer->doc .= '</div>'.DOKU_LF;
        }
        return true;
    }

}
