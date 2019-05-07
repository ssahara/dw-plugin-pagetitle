<?php
/**
 * DokuWiki plugin PageTitle YouAreHere; Syntax component
 * Hierarchical breadcrumbs
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_youarehere extends DokuWiki_Syntax_Plugin
{
    public function getType()
    {   // Syntax Type
        return 'substition';
    }

    public function getPType()
    {   // Paragraph Type
        return 'block';
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
        $this->pattern[5] = '<!-- ?YOU_ARE_HERE ?-->';
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
        global $ID;
        return $data = [$state, $match, $ID];
    }

    /**
     * Create output
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $ID;
        static $helper;

        list($state, $match, $id) = $data;

        // skip calls that belong to different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        if ($format == 'metadata') {
            $renderer->meta['plugin']['pagetitle']['youarehere'] =+ 1;

        } elseif ($format == 'xhtml') {
            // load helper object
            isset($helper) || $helper = $this->loadHelper($this->getPluginName());

            $renderer->doc .= DOKU_LF.$match.DOKU_LF; // html comment
            $renderer->doc .= '<div class="youarehere">';
            $renderer->doc .= $helper->html_youarehere(1); // start_depth = 1
            $renderer->doc .= '</div>'.DOKU_LF;
        }
        return true;
    }

}
