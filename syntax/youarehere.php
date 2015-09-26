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
    protected $pluginMode;

    function __construct() {
        $this->pluginMode = substr(get_class($this), 7); // drop 'syntax_' from class name
    }

    public function getType() { return 'substition'; }
    public function getPType(){ return 'block'; }
    public function getSort() { return 990; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->special_pattern, $mode, $this->pluginMode);
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    public function render($format, Doku_Renderer $renderer, $data) {
        global $INFO;
        list($state, $match) = $data;
        $template = plugin_load('helper','pagetitle');

        $renderer->doc .= DOKU_LF.$match.DOKU_LF; // html comment
        //$renderer->doc .= '<span id="pagetitle">%'.$INFO['meta']['shorttitle'].'</span>';
        $renderer->doc .= '<div class="youarehere">';
        $renderer->doc .= $template->tpl_youarehere(0, 1);
        $renderer->doc .= '</div>'.DOKU_LF;
        return true;
    }

}
