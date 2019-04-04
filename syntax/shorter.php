<?php
/**
 * DokuWiki plugin PageTitle Shorter; Syntax component
 * Macro to set the short title of the page in metadata
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_shorter extends DokuWiki_Syntax_Plugin
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
        $this->pattern[5] = '~~ShortTitle:[^\n~]*~~';
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
        static $counter = [];

        // ensure first matched pattern only effective
        if ($counter[$ID]++ > 0) return false;

        // get short title
        $short_title = trim(substr($match, 13, -2));
        return $data = [$state, $short_title, $ID];
    }

    /**
     * Create output
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $ID;

        [$state, $short_title, $id] = $data;

        // skip calls that belong to different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        switch ($format) {
            case 'metadata':
                $renderer->meta['shorttitle'] = $short_title;
                return true;
            default:
                return false;
        }
    }

}
