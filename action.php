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
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, '_parser_render');
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, '_prepare_cache');
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
        $event->data['plgin_pagetitle'] = '1.'.$this->getConf('usePersistent');
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


    /**
     * PAESER_METADATA_RENDER
     * Use this event to update/reflesh metadata they may have set elseware.
     * The page metadata is passed including both the current and persistent arrays.
     */
    function _parser_render(Doku_Event $event, $param) {
        global $ID;

        /*
         * The PageTitle plugin will overwrite "title" metadata of the page as
         * specific "pagetitle" when wiki text has rendered in xhtml format mode
         * and are kept in each .meta files as metadata storage.
         * Each metadata storage (.meta file) may expired or refleshed by DokuWiki
         * or any plugins in any stage especially by calls p_get_first_heading()
         * at least when main config modified depending on $conf[useheading].
         *
         * Then the value of "title" metadata that had overwritten as "pagetitle"
         * will be lost or set wrong until relevant pages are rendered again in
         * xhtm mode by PageTitle syntax plugin component.
         *
         * Here, we tentatively set $ID as "title" metadata to identify so that
         * it should be set again during caching mechanism.
         */

        $meta       =& $event->data['current'];
        $persistent =& $event->data['persistent'];

        // check whether pagetitle syntax had used in the wiki
        $pages = idx_get_indexer()->getPages('plugin_pagetitle');
        $pageTitled = in_array($ID, $pages);


        if ($pageTitled && !isset($meta['plugin']['pagetitle'])) {
            // for pages found in metadata index but xhtml has not rendered yet
            if ($this->getConf('useTentativeTitle')) {
                // tentatively assign full id as page title, just to distinguish
                // with normal setting noNS($ID) and to expire .meta file later
                $meta['title'] = $ID;
            }

            // remove unnecessary persistent metadata
            if (!$this->getConf('usePersistent')) {
                unset($persistent['title']);
            }
        }
    }

    /**
     * PARSER_CACHE_USE
     * prepare the cache object for default _useCache action
     */
    function _prepare_cache(Doku_Event $event, $param) {
        $cache =& $event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) return;

        // check whether pagetitle had used in the wiki page
        $pages = idx_get_indexer()->getPages('plugin_pagetitle');
        $pageTitled = in_array($cache->page, $pages);

        if (!$pageTitled) return;

        if ($this->getConf('useTentativeTitle')) {

            $title = p_get_metadata($cache->page, 'title', METADATA_DONT_RENDER);
 
            switch ($cache->mode) {
                case 'metadata': // metadata cache?
                    $cache->depends['purge'] = ($title == $cache->page) ? true : false;
                    break;
                case 'i': // instruction cache
                    $cache->depends['purge'] = ($title == $cache->page) ? true : false;
                    break;
                case 'xhtml': // xhtml cache
                    $cache->depends['purge'] = ($title == $cache->page) ? true : false;
                    break;
            }
        }
    }

}
