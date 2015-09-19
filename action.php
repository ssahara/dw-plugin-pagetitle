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
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'deleteObsoletedSingleClass');
    }

    /**
     * Delete syntax.php which is obsoleted since multi-components syntax structure
     */
    public function deleteObsoletedSingleClass(Doku_Event $event) {
        $legacyFile = dirname(__FILE__).'/syntax.php';
        if (file_exists($legacyFile)) { unlink($legacyFile); }
    }

}
