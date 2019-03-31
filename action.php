<?php
/**
 * DokuWiki Plugin PageTitle; Action component
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if(!defined('DOKU_INC')) die();

class action_plugin_pagetitle extends DokuWiki_Action_Plugin
{
    /**
     * register the event handlers
     */
    public function register(Doku_Event_Handler $controller) {
      //$controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'deleteObsoletedSingleClass');
        $controller->register_hook('INDEXER_VERSION_GET', 'BEFORE', $this, '_indexer_version');
        $controller->register_hook('INDEXER_PAGE_ADD', 'BEFORE', $this, '_indexer_pagetitle');
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, '_parser_render');
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, '_prepare_cache');
    }

    /**
     * Delete syntax.php which is obsoleted since multi-components syntax structure
     */
    public function deleteObsoletedSingleClass(Doku_Event $event)
    {
        $legacyFile = dirname(__FILE__).'/syntax.php';
        if (file_exists($legacyFile)) { unlink($legacyFile); }
    }


    /**
     * INDEXER_VERSION_GET
     * Set a version string to the metadata index so that
     * the index will be re-created when the version increased
     */
    public function _indexer_version(Doku_Event $event, $param)
    {
        $event->data['plgin_pagetitle'] = '1.'.$this->getConf('usePersistent');
    }

    /**
     * INDEXER_PAGE_ADD
     * Add id of the page to metadata index, relevant pages should be found
     * in data/index/plugin_pagetitle_w.idx file
     */
    public function _indexer_pagetitle(Doku_Event $event, $param)
    {
        $metadata = p_get_metadata($event->data['page'], 'plugin pagetitle');
        $id = $metadata['title'] ?? null;
        if ($id) {
            $event->data['metadata']['plugin_pagetitle'] = $id;
        }
    }


    /**
     * PAESER_METADATA_RENDER
     * Use this event to update/reflesh metadata they may have set elseware.
     * The page metadata is passed including both the current and persistent arrays.
     */
    public function _parser_render(Doku_Event $event, $param)
    {
        global $ID;

        /*
         * The PageTitle plugin will overwrite "title" metadata of the page
         * with "pagetitle" specified in page source. The page must be rendered
         * first in xhtml mode to get pagetitle and to stote it on metadata
         * storage.
         
         * Each metadata storage (.meta file) may be expired or refleshed by
         * DokuWiki or any plugins at elsewhere in any stage. For example,
         * metadata will be expired when main config modified. DokuWiki will set
         * again title metadata through calls p_get_first_heading() depending on
         * $conf['useheading"] setting.
         *
         * Since page text is not changed, instruction and xhtml cache files are
         * used to set title metadata, there is no chance to handle/render page
         * source to get pagetitle and to overwite title metadata.
         * Therfore, the value of "title" metadata will remain wrong with that
         * pagetitle plugin intended.
         *
         * For the purpose to trigger PageTitle plugin's renderer, we tentatively
         * set $ID as "title" to tell DokuWiki caching mechanism so that old
         * cache need to be purged and metadata must be rebuild again.
         */

        $meta       =& $event->data['current'];
        $persistent =& $event->data['persistent'];

        // check metadata index whether pagetitle had used in the wiki page
        $pages = idx_get_indexer()->getPages('plugin_pagetitle');
        $pageTitled = in_array($ID, $pages);

        if (!$pageTitled) return;

        // check whether page has rendered by pagetitle plugin
        if (!isset($meta['plugin']['pagetitle'])) {
            // tentatively assign full id as page title, just to distinguish
            // with normal setting noNS($ID) and to purge .meta file later
            $meta['title'] = $ID;
        }

        // unnecessary persistent metadata should be removed in syntax component,
        // however it may be possible to remove it here
        if (!$this->getConf('usePersistent')) {
            unset($persistent['title']);
        }
    }

    /**
     * PARSER_CACHE_USE
     * prepare the cache object for default _useCache action
     */
    public function _prepare_cache(Doku_Event $event, $param)
    {
        $cache =& $event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) return;

        // check dependency for hierarchical breadcrumbs
        if ($cache->mode == 'xhtml') {
            $metadata = p_get_metadata($cache->page, 'plugin pagetitle');
            if (isset($metadata['youarehere'])) {
                isset($helper) || $helper = $this->loadHelper('pagetitle');
                $html = $helper->html_youarehere(1, $cache->page, $traces);
                array_pop($traces);
                $depends = [];
                foreach ($traces as $id) {
                    $depends[] = wikiFN($id, '', false);
                }
                $cache->depends['files'] = array_merge((array)$cache->depends['files'], $depends);
            }
        }

        // check metadata index whether pagetitle had used in the wiki page
        $pages = idx_get_indexer()->getPages('plugin_pagetitle');
        $pageTitled = in_array($cache->page, $pages);

        if (!$pageTitled) return;

        // check title metadata whether cache files should be purged
        $title = p_get_metadata($cache->page, 'title', METADATA_DONT_RENDER);
        switch ($cache->mode) {
            case 'i': // instruction cache
                $request = ($title == $cache->page) ? true : false;
                break;
            case 'metadata': // metadata cache?
                $request = ($title == $cache->page) ? true : false;
                break;
            case 'xhtml': // xhtml cache
                $request = ($title == $cache->page) ? true : false;
                break;
        }
        // request purge if necessary
        $cache->depends['purge'] = $request;
    }

}
