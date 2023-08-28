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
class syntax_plugin_pagetitle_decorative extends DokuWiki_Syntax_Plugin
{
    /** syntax type */
    public function getType()
    {
        return 'baseonly';
    }

    /** paragraph type */
    public function getPType()
    {
        return 'block';
    }

    /** allowed mode types */
    public function getAllowedTypes()
    {
        return ['formatting', 'substition', 'disabled'];
    }

    /**
     * Connect pattern to lexer
     */
    protected $mode, $pattern;

    /** sort number used to determine priority of this mode */
    public function getSort()
    {
        return 49;
    }

    public function preConnect()
    {
        // syntax mode, drop 'syntax_' from class name
        $this->mode = substr(__CLASS__, 7);

        // syntax patterns
        $this->pattern[1] = '<title\b[^\n>]*>(?=.*?</title>)'; // entry
        $this->pattern[4] = '</title>';                        // exit
        $this->pattern[5] = '~~Title:[^\n~]*~~';               // special
    }

    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern($this->pattern[5], $mode, $this->mode);
        $this->Lexer->addEntryPattern($this->pattern[1], $mode, $this->mode);
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern($this->pattern[4], $this->mode);
    }


    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        global $ID;
        static $params; // store title tag parameters

        switch ($state) {
            case DOKU_LEXER_SPECIAL : // ~~Title:*~~ macro syntax
                $title = trim(substr($match, 8, -2));
                return $data = [$state, $ID, $title];

            case DOKU_LEXER_ENTER :
                // store title tag parameters
                $params = strtolower(trim(substr($match, 6, -1)));
                return $data = [$state, $ID, ''];

            case DOKU_LEXER_UNMATCHED :
                $handler->base($match, $state, $pos);
                return false;

            case DOKU_LEXER_EXIT :
                // hand over title tag parameters to render stage
                return $data = [$state, $ID, $params];
        }
        return false;
    }

    /** @var string temporary $doc store used in render() */
    protected $store = '';

    /**
     * Create output
     */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $ID;
        static $counter = [];

        list ($state, $id, $param) = $data;

        switch ($state) {
            case DOKU_LEXER_ENTER :
                // disable capturing
                if ($renderer->getFormat() == 'metadata') $renderer->capturing = false;
                // preserve rendered data
                $this->store = $renderer->doc;
                // set doc blank prior to store "UNMATHCED" content
                $renderer->doc = '';
                return true;
                break;
            case DOKU_LEXER_EXIT :
                // re-enable capturing
                if ($renderer->getFormat() == 'metadata') $renderer->capturing = true;
                // retrieve "UNMATCHED" content
                $decorative_title = trim($renderer->doc);
                // restore rendered data
                $renderer->doc = $this->store;
                $this->store = '';
                break; // do not return here

            case DOKU_LEXER_SPECIAL : // ~~Title:*~~ macro syntax
                // $decorative_title = $param;
                // convert to curly quote characters depending on $conf['typography']
                $decorative_title = $this->render_text($param);
                break;
        }
        // follow up only for DOKU_LEXER_EXIT and DOKU_LEXER_SPECIAL

        // skip calls that belong to different pages (eg. title of included page)
        if ($id !== $ID) return false;

        // ensure first instruction only effective
        if (!isset($counter[$ID][$format])) $counter[$ID][$format] = 0;
        if ($counter[$ID][$format]++ > 0) return false;

        // get plain title
        $title = trim(htmlspecialchars_decode(strip_tags($decorative_title), ENT_QUOTES));
        if (empty($title)) return false;

        // output title
        switch ($format) {
            case 'metadata':
                $renderer->cdata(DOKU_LF. $title .DOKU_LF);

                // set metadata for metadata indexer
                $renderer->meta['plugin']['pagetitle']['title'] = $ID;

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

            case 'xhtml':
                if ($state == DOKU_LEXER_SPECIAL) return false;
                if ($param && ($wrap = $this->loadHelper('wrap')) !== null) {
                    $attr = $wrap->buildAttributes($param, 'pagetitle');
                } else {
                    $attr = ' class="pagetitle"';
                }

                // even title in <h1>, it never shown up in the table of contents (TOC)
                $renderer->doc .= DOKU_LF;
                $renderer->doc .= '<h1'.$attr.'>'.$decorative_title.'</h1>'.DOKU_LF;
                return true;

            case 'text':
                $renderer->doc .= DOKU_LF. $title .DOKU_LF;
                return true;
        }
        return false;
    }

}
