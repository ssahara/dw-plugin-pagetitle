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
        $renderer->doc .= $this->tpl_youarehere();
        $renderer->doc .= DOKU_LF;
        return true;
    }

    /**
     * Hierarchical breadcrumbs for PageTitle plugin
     * the startpage does not always printed
     *
     * @param bool        $print
     * @return bool|string html, or false if no data, true if printed
     */
    function tpl_youarehere($print = false) {
        global $conf, $ID, $lang;

        $page = ':'.$ID;
        $parts = explode(':', $page);
        $depth = count($parts) -1;

        $out = '<div class="youarehere">';
        //$out.= '<span class="bchead">#'.$lang['youarehere'].' </span>';

        $ns = '';
        for ($i = 1; $i < count($parts); $i++) {
            $ns .= $parts[$i].':';
            $page = $ns;
            resolve_pageid('', $page, $exists);
            //$name = p_get_metadata($page, 'shorttitle') ?: $parts[$i];
            //$out.= $this->pagelink($page, $name);
            $out.= $this->pagelink($page);
            if ($i < $depth) $out.= ' ›&#x00A0;'; // separator
        }

        $out .= '</div>';
        if ($print) {
            echo $out;
            return (bool) $out;
        } 
        return $out;
    }

    /**
     * Prints a link to a WikiPage
     *
     * @param string      $id   page id
     * @param string|null $name the name of the link
     * @param bool        $print
     * @return bool|string html, or false if no data, true if printed
     */
    protected function pagelink($id, $name = null, $print = false) {
        global $conf;

        $title = p_get_metadata($id, 'title');
        if (empty($title)) $title = $id;

        if (empty($name)) {
            $short_title = p_get_metadata($id, 'shorttitle');
            if (empty($short_title)) {
               if (noNS($id) == $conf['start']) {
                   $short_title = p_get_metadata(getNS($id), 'shorttitle');
                   if (empty($short_title)) $short_title = noNS(getNS($id));
               } else {
                   $short_title = noNS($id);
               }
            }
        } else {
            $short_title = $name;
        }

        $out = '<a href="'.wl($id).'" title="'.hsc($title).'">'.hsc($short_title).'</a>';
        $out = '<bdi>'.$out.'</bdi>';
        if ($print) {
            echo $out;
            return (bool) $out;
        } 
        return $out;
    }

}
