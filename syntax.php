<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';
require_once DOKU_INC.'inc/search.php';

class syntax_plugin_miniblog extends DokuWiki_Syntax_Plugin {
    public function getType() {
        return 'protected';
    }

    public function getSort() {
        return 380;
    }

    public function getPType() {
        return 'block';
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<miniblog>', $mode, 'plugin_miniblog');
    }

    public function handle($match, $state, $pos, &$handler){
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

        return array(5,$result); // dispaly 5 entries per page
    }

    public function render($mode, &$renderer, $data) {
        global $ID;
        global $INPUT;
        global $INFO;

        if ($mode != 'xhtml') return false;

        list($num, $entries) = $data;

        // disable cache and toc
        $renderer->info['cache'] = false;
        $INFO['prependTOC'] = false;

        // slice
        $page = $INPUT->int('page', 0); // current page
        $last = $page+$num;
        $more = ((count($entries) > $last) ? true : false);
        $entries = array_slice($entries, $page, $num);

        // blog entries
        foreach ($entries as $key => $entry) {
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

            $renderer->doc .= '<h1><a id="'.$head.'" href="'.wl($entry).'" name="'.$head.'">'.$head.'</a></h1>'.p_render('xhtml', $ins, $info);
        }

        // paganition
        $link = '<p class="centeralign">';

        if ($page > 0) {
            $page = max(0, $page-$num);
            $link .= '<a href="'.wl($ID, 'page='.$page).'" class="wikilink1">较新的文章</a>';
            if ($more) $link .= ' | '; else $link .= '</p>';
        }

        if ($more) {
            $link .= '<a href="'.wl($ID, 'page='.$last).'" class="wikilink1">较早的文章</a></p>';
        }

        $renderer->doc .= $link;
            
        return true;
    }
}
