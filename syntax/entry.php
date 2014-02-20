<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_miniblog_entry extends DokuWiki_Syntax_Plugin {
    public function getType() {
        return 'substition';
    }

    public function getSort() {
        return 380;
    }

    public function getPType() {
        return 'block';
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<miniblog>', $mode, 'plugin_miniblog_entry');
    }

    public function handle($match, $state, $pos, &$handler){
        return array();
    }

    public function render($mode, &$renderer, $data) {
        global $ID;
        global $INPUT;
        global $INFO;

        if ($mode != 'xhtml') return false;

        // disable cache and toc
        $renderer->info['cache'] = false;
        $INFO['prependTOC'] = false;

        $entries = $this->plugin_load('helper', 'miniblog_entry')->entry_list('blog');
        $num = 5; // display 5 entries per page

        // slice
        $page = 5*$INPUT->int('page', 0); // index of first entry in current page
        $less = (($page > 0) ? max(0, $page-$num) : 0)/5; // previous page
        $more = ((count($entries) > $page+$num) ? $page+$num : 0)/5; // next page
        $entries = array_slice($entries, $page, $num);

        // comment count
        $source = "count.js";
        $option = array('disqus_shortname' => $this->getConf('shortname'));
        $renderer->doc .= plugin_load('helper', 'miniblog_comment')->comment_script($source, $option);

        // show entries
        foreach ($entries as $entry) {
            list($head, $content) = $this->loadHelper('miniblog')->entry_content($entry['id']);

            $renderer->doc .= '<h1><a href="'.wl($entry['id']).'">'.$head.'</a></h1>';
            $renderer->doc .= '<p class="miniblog_info">';
            $renderer->doc .= dformat($entry['date']).' Â· <a href="'.$wl($entry['id'],'',true).'#disqus_thread"/>';
            $renderer->doc .= '</p>';
            $renderer->doc .= $content;
        }

        // paganition
        $renderer->doc .= '<div id="miniblog_paganition">';
        if ($less) {
            $renderer->doc .= '<p class="less"><a href="'.wl($ID, 'page='.$less).'" class="wikilink1">'.$this->getLang('newer').'</a></p>';
        }
        if ($more) {
            $renderer->doc .= '<p class="more"><a href="'.wl($ID, 'page='.$more).'" class="wikilink1">'.$this->getLang('older').'</a></p>';
        }
        $renderer->doc .= '</div>';

        return true;
    }
}
