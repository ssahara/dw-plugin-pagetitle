<?php
/**
 * DokuWiki plugin Pagetitle metaonly; Syntax component
 * Macro to set the itle of the page in metadata
 * deteriorated decorative syntax component
 * 
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 */

require_once(dirname(__FILE__).'/decorative.php');

class syntax_plugin_pagetitle_metaonly extends syntax_plugin_pagetitle_decorative {

    protected $entry_pattern = '~~Title:(?=.*?~~)';
    protected $exit_pattern  = '~~';


    function getType() { return 'baseonly';}
    function getPType() { return 'normal';}
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }
    function getSort() { return 49; }


    /**
     * Revised procedures for renderers
     */
    protected function _xhtml_render($decorative_title, $title, &$renderer) {
        return true;
    }

    protected function _metadata_render($decorative_title, $title, &$renderer) {
        $renderer->meta['title'] = $title;
        return true;
    }

}
