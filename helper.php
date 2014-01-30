<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

require_once DOKU_INC.'inc/search.php';

class helper_plugin_miniblog extends DokuWiki_Plugin {
    function get_entries() {
        global $conf;

        // pages to display
        search($pages, $conf['datadir'], 'search_pagename', array('query'=>'.txt'), 'blog');

        // sort
        $result = array();
        foreach ($pages as $page) {
            $key = p_get_metadata($page['id'], 'date created', METADATA_DONT_RENDER).'_'.$page['id'];
            $result[$key] = $page['id'];
        }
        krsort($result);

        return $result;
    }

    function get_contents($entry) {
        $ins = p_cached_instructions(wikiFN($entry), false, $entry);

        // delete heading, resolve internal links and remove comment plugin
        $head = false;
        for ($i=0; $i<count($ins); $i++) {
            switch ($ins[$i][0]) {
                case 'header':
                    if ($head === false) {
                        $head = $ins[$i][1][0];
                        unset($ins[$i]);
                    }
                    break;
                case 'internallink':
                    resolve_pageid(getNS($entry), $ins[$i][1][0], $exists);
                    break;
                case 'internalmedia':
                    resolve_mediaid(getNS($entry), $ins[$i][1][0], $exists);
                    break;
                case 'plugin':
                    if ($ins[$i][1][0] == "disqus") unset($ins[$i]);
                    break;
            }
        }	

        return array($head, p_render('xhtml', $ins, $info));
    }
}
