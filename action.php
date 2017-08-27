<?php
/**
 * DokuWiki Plugin PageTitle; Action component
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if(!defined('DOKU_INC')) die();

class action_plugin_pagetitle extends DokuWiki_Action_Plugin {

    /**
     * register the event handlers
     */
    function register(Doku_Event_Handler $controller) {
      //$controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'deleteObsoletedSingleClass');
        $controller->register_hook('INDEXER_VERSION_GET', 'BEFORE', $this, '_indexer_version');
        $controller->register_hook('INDEXER_PAGE_ADD', 'BEFORE', $this, '_indexer_pagetitle');
    }

    /**
     * Delete syntax.php which is obsoleted since multi-components syntax structure
     */
    function deleteObsoletedSingleClass(Doku_Event $event) {
        $legacyFile = dirname(__FILE__).'/syntax.php';
        if (file_exists($legacyFile)) { unlink($legacyFile); }
    }


    /**
     * INDEXER_VERSION_GET
     * Set a version string to the metadata index so that
     * the index will be re-created when the version increased
     */
    function _indexer_version(Doku_Event $event, $param) {
        $event->data['plgin_pagetitle'] = '1.0';
    }

    /**
     * INDEXER_PAGE_ADD
     * Add id of the page to metadata index, relevant pages should be found
     * in data/index/plugin_pagetitle_w.idx file
     */
    function _indexer_pagetitle(Doku_Event $event, $param) {
        $id = p_get_metadata($event->data['page'], 'plugin pagetitle');
        if ($id) {
            $event->data['metadata']['plugin_pagetitle'] = $id;
        }
    }

}
