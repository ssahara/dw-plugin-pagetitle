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
        $renderer->doc .= DOKU_LF.$match.DOKU_LF; // html comment
        //$renderer->doc .= '<span id="pagetitle">%'.$INFO['meta']['shorttitle'].'</span>';
        $renderer->doc .= '<div class="youarehere">';
        $renderer->doc .= $this->tpl_youarehere(0);
        $renderer->doc .= '</div>'.DOKU_LF;
        return true;
    }

    /**
     * Hierarchical breadcrumbs for PageTitle plugin
     *
     * @param bool   $print if false return content
     * @return bool|string html, or false if no data, true if printed
     */
    function tpl_youarehere($print = true) {
        global $conf, $ID, $lang;

        $page = ':'.((noNS($ID) == $conf['start']) ? getNS($ID) : $ID);

        $parts = explode(':', $page);
        $depth = count($parts) -1;

        $out = '';
        //$out = '<span class="bchead">'.$lang['youarehere'].' </span>';

        $ns = '';
        for ($i = 1; $i < count($parts); $i++) {
            $ns.= $parts[$i];
            $id = $ns;
            resolve_pageid('', $id, $exists);
            if (!$exists) {
                $id = $ns.':';
                resolve_pageid('', $id, $exists);
            }
            $name = p_get_metadata($id, 'shorttitle') ?: $parts[$i];
            $out.= '<bdi>'.$this->tpl_pagelink(0, $id, $name, $exists).'</bdi>';
            if ($i < $depth) $out.= ' ›&#x00A0;'; // separator
            $ns.= ':';
        }

        if ($print) {
            echo $out; return (bool) $out;
        }
        return $out;
    }

    /**
     * Prints a link to a WikiPage
     *
     * @param bool   $print if false return content
     * @param string $id    page id
     * @param string $name  the name of the link
     * @param bool   $exists
     * @return bool|string html, or false if no data, true if printed
     */
    protected function tpl_pagelink($print = true, $id, $name = null, $exists = null) {
        global $conf;

        $title = p_get_metadata($id, 'title');
        if (empty($title)) $title = $id;

        if (is_null($exists)) {
            $class = (page_exists($id)) ? 'wikilink1' : 'wikilink2';
        } else {
            $class = ($exists) ? 'wikilink1' : 'wikilink2';
        }

        $short_title = $name;
        if (empty($name)) {
            $short_title = p_get_metadata($id, 'shorttitle') ?: noNS($id);
        }

        $out = '<a href="'.wl($id).'" class="'.$class.'" title="'.hsc($title).'">';
        $out.= hsc($short_title).'</a>';
        if ($print) {
            echo $out; return (bool) $out;
        } 
        return $out;
    }


    /**
     * Prints or returns the title of the given page (current one if none given)
     * modified from DokuWiki original function tpl_pagetitle() 
     * defined in inc/template.php
     * This variant function returns title from metadata, ignoring $conf['useheading']
     *
     * @param bool   $print if false return content
     * @param string $id    page id
     * @return bool|string html, or false if no data, true if printed
     */
    function tpl_pagetitle($print = true, $id = null) {
        global $ACT, $ID, $conf, $lang;

        if (is_null($id)) {
            $title = (p_get_metadata($ID, 'title')) ?: $ID;
        } else {
            $title = (p_get_metadata($id, 'title')) ?: $id;
        }

        // default page title is the page name, modify with the current action
        switch ($ACT) {
            // admin functions
            case 'admin' :
                $page_title = $lang['btn_admin'];
                // try to get the plugin name
                /** @var $plugin DokuWiki_Admin_Plugin */
                if ($plugin = plugin_getRequestAdminPlugin()){
                    $plugin_title = $plugin->getMenuText($conf['lang']);
                    $page_title = $plugin_title ? $plugin_title : $plugin->getPluginName();
                }
                break;

            // user functions
            case 'login' :
            case 'profile' :
            case 'register' :
            case 'resendpwd' :
                $page_title = $lang['btn_'.$ACT];
                break;

            // wiki functions
            case 'search' :
            case 'index' :
                $page_title = $lang['btn_'.$ACT];
                break;

            // page functions
            case 'edit' :
                $page_title = "✎ ".$title;
                break;

            case 'revisions' :
                $page_title = $title . ' - ' . $lang['btn_revs'];
                break;

            case 'backlink' :
            case 'recent' :
            case 'subscribe' :
                $page_title = $title . ' - ' . $lang['btn_'.$ACT];
                break;

            default : // SHOW and anything else not included
                $page_title = $title;
        }

        if ($print) {
            echo hsc($page_title); return (bool) $page_title;
        } 
        return hsc($page_title);
    }

}
