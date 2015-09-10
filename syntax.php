<?php
/**
 * DokuWiki plugin Pagetitle; Syntax component
 * Macro to set the title of the page in metadata
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_pagetitle extends DokuWiki_Syntax_Plugin {

    protected $special_pattern = '~~(?:Title|ShortTitle):.*?~~';

    public function getType() { return 'substition'; }
    public function getPType(){ return 'block'; }
    public function getSort() { return 990; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->special_pattern, $mode,
            implode('_', array('plugin',$this->getPluginName(),))
        );
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    public function render($format, Doku_Renderer $renderer, $data) {

       list($state, $match) = $data;
       list($key, $value) = explode(':', substr($match, 2, -2), 2);
       $key = strtolower($key);

        if ($format == 'metadata') {
             $renderer->meta[$key] = trim($value);
        }
        elseif ($format == 'xhtml') {
        	if ($this->getConf('render_title') && $key == 'title' && trim($value) != false) {
        		$renderer->header(trim($value), 1, 0);
        	}
        }
        
        return true;
    }

}

