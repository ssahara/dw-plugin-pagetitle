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

    protected $special_pattern = '~~(?:YouAreHere|YOU_ARE_HERE)~~';
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
        $renderer->doc .= DOKU_LF.'<!-- YOU_ARE_HERE -->'.DOKU_LF;
        //$renderer->doc .= '<span id="pagetitle">%'.$INFO['meta']['shorttitle'].'</span>';
        $renderer->doc .= $this->tpl_youarehere(' ›&#x00A0;');
        $renderer->doc .= DOKU_LF;
        return true;
    }

    /**
     * Hierarchical breadcrumbs for PageTitle plugin
     * the startpage does not always printed
     * @param string $sep Separator between entries
     * @return bool
     */
    function tpl_youarehere($sep = ' » ') {
        global $conf, $ID, $lang;

        $page = ':'.$ID;
        $parts = explode(':', $page);
        $depth = count($parts) -1;

        $out = '<div class="youarehere">';
        //$out.= '<span class="bchead">#'.$lang['youarehere'].' </span>';

        $ns = '';
        $page = '';
        for ($i = 1; $i < count($parts); $i++) {
            $ns .= $parts[$i].':';
            $page = $ns;
            resolve_pageid('', $page, $exists);
            $out.= $this->pagelink($page, $parts[$i]);
            if ($i < $depth) $out.= $sep;
        }

        $out .= '</div>';
        return $out;
    }

    protected function pagelink($id, $name = null) {

        $href = wl($id);
        $title = p_get_metadata($id, 'title');
        if (empty($title)) $title = $href;
        $short_title = p_get_metadata($id, 'shorttitle');
        if (empty($short_title)) $short_title = $name;
        if (empty($short_title)) $short_title = $id;

        $out = '<a href="'.$href.'" title="'.hsc($title).'">'.hsc($short_title).'</a>';
        $out = '<bdi>'.$out.'</bdi>';
        return $out;
    }


}
