<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class helper_plugin_miniblog_entry extends DokuWiki_Plugin {
    public function entry_list($ns) {
        global $conf;

        // pages to display
        search($pages, $conf['datadir'], 'search_pagename', array('query'=>'.txt'), $ns);

        // sort
        $entries = array();
        foreach ($pages as $page) {
            $date = p_get_metadata($page['id'], 'date created', METADATA_DONT_RENDER);
            $user = p_get_metadata($page['id'], 'user', METADATA_DONT_RENDER),

            $entries[$date] = array(
                'id' => $page['id'],
                'date' => $date,
                'user' => $user,
            );
        }
        krsort($entries);

        return $entries;
    }

    public function entry_content($id) {
        $ins = p_cached_instructions(wikiFN($id), false, $id);

        // delete heading, resolve internal links and remove comment
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
                    resolve_pageid(getNS($id), $ins[$i][1][0], $exists);
                    break;
                case 'internalmedia':
                    resolve_mediaid(getNS($id), $ins[$i][1][0], $exists);
                    break;
                case 'plugin':
                    if ($ins[$i][1][0] == 'miniblog_comment') unset($ins[$i]);
                    break;
            }
        }	

        return array($head, p_render('xhtml', $ins, $info));
    }
}
