<?php
/**
 * DokuWiki plugin PageTitle; Helper component counter
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class helper_plugin_pagetitle_counter extends DokuWiki_Plugin {

    /**
     * tally counter utility - incrementally counts items
     *
     * Note: assign this closure function to variables to use counter
     *   $obj = $this->loadHelper('pagetitle_counter');
     *   $counter = $obj->create_counter($item);
     *   echo $counter($something);
     *
     * if a closure function assigned to class properties, make sure that 
     * PHP parses "$MyClass->counter" as fetch the property named "counter".
     *
     *   $MyClass->counter = $obj->create_counter($item);
     *   echo ($MyClass->counter)($something);  // PHP >= 7.0
     *
     * or call it using call_user_func:
     *   echo call_user_func($MyClass->counter, $something);
     *
     */
    function create_counter($item='') {
        $counter = array();
        return function ($item='') use (&$counter) {
            return @$counter[$item]++ ?: 0; // restrain notice of Undefined index
        };
    }

}
