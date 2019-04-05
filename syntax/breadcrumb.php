<?php
/**
 * DokuWiki plugin PageTitle Breadcrumb; Syntax component
 * Render hierarchical breadcrumbs in the page using "Short Title"
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_breadcrumb extends DokuWiki_Syntax_Plugin
{
    function getType() { return 'substition'; }
    function getPType(){ return 'normal'; }
    function getSort() { return 990; }

    /**
     * Connect pattern to lexer
     */
    protected $mode, $pattern;

    public function preConnect()
    {
        // syntax mode, drop 'syntax_' from class name
        $this->mode = substr(get_class($this), 7);

        //syntax patterns
        $this->pattern[5] = '~~\$Breadcrumb\([^\n~]*\)~~';
    }

    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern($this->pattern[5], $mode, $this->mode);
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $id = trim(substr($match, 13, -3));
        return $data = [$id];
    }

    /**
     * Create output
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $ID;

        $id = cleanID($data[0]) ?: $ID;

        // load helper object
        isset($helper) || $helper = $this->loadHelper($this->getPluginName());

        switch ($format) {
            case 'xhtml':
                $renderer->doc .= $helper->html_youarehere(1, $id);
                return true;
            default:
                return false;
        }
    }

}
