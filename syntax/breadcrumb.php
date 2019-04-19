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
    public function getType()
    {   // Syntax Type
        return 'substition';
    }

    public function getPType()
    {   // Paragraph Type
        return 'normal';
    }

    /**
     * Connect pattern to lexer, implement Doku_Parser_Mode_Interface
     */
    protected $mode, $pattern;

    public function getSort()
    {
        // sort number used to determine priority of this mode
        return 990;
    }

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
        static $helper;

        $id = cleanID($data[0]) ?: $ID;

        switch ($format) {
            case 'xhtml':
                // load helper object
                isset($helper) || $helper = $this->loadHelper($this->getPluginName());

                $renderer->doc .= $helper->html_youarehere(1, $id);
                return true;
            default:
                return false;
        }
    }

}
