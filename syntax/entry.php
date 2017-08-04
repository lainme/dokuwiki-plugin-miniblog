<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Esther Brunner <wikidesign@gmail.com> (upstream)
 * @author  Robert Rackl <wiki@doogie.de> (upstream)
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
        $this->Lexer->addSpecialPattern('<miniblog.*>', $mode, 'plugin_miniblog_entry');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler){
        return trim(mb_substr($match, 10, -1));
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        global $INPUT;
        global $INFO;

        if ($mode != 'xhtml') return false;

        // disable cache and toc
        $renderer->info['cache'] = false;
        $INFO['prependTOC'] = false;

        $entries = plugin_load('helper', 'miniblog_entry')->entry_list('blog');
        $num = 5; // display 5 entries per page

        // slice
        $page = $num*$INPUT->int('page', 0); // index of first entry in current page
        $less = (($page > 0) ? max(0, ($page-$num)/$num) : -1); // previous page
        $more = ((count($entries) > $page+$num) ? ($page+$num)/$num : -1); // next page
        $entries = array_slice($entries, $page, $num);

        // comment count
        $source = 'count.js';
        $option = array('disqus_shortname' => $this->getConf('shortname'));
        $renderer->doc .= plugin_load('helper', 'miniblog_comment')->comment_script($source, $option);

        // contents
        if ($data == 'twentyfifteen') {
            $this->render_twentyfifteen($renderer, $entries, $less, $more);
        } else {
            $this->render_default($renderer, $entries, $less, $more);
        }
        return true;
    }

    function render_default($renderer, $entries, $less, $more) {
        global $ID;

        // show entries
        foreach ($entries as $entry) {
            list($head, $content) = plugin_load('helper', 'miniblog_entry')->entry_content($entry['id']);

            $renderer->doc .= '<h1><a href="'.wl($entry['id']).'">'.$head.'</a></h1>';
            $renderer->doc .= '<p class="miniblog_info">';
            $renderer->doc .= dformat($entry['date']).' · '.$entry['user'].' · <a href="'.wl($entry['id'],'',true).'#disqus_thread"></a>';
            $renderer->doc .= '</p>';
            $renderer->doc .= html_secedit($content, false); // no section edit button
        }

        // pagination
        if ($less !== -1) {
            $renderer->doc .= '<p class="medialeft"><a href="'.wl($ID, 'page='.$less).'" class="wikilink1">'.$this->getLang('newer').'</a></p>';
        }
        if ($more !== -1) {
            $renderer->doc .= '<p class="mediaright"><a href="'.wl($ID, 'page='.$more).'" class="wikilink1">'.$this->getLang('older').'</a></p>';
        }
    }

    function render_twentyfifteen($renderer, $entries, $less, $more) {
        global $ID;

        // show entries
        foreach ($entries as $entry) {
            list($head, $content) = plugin_load('helper', 'miniblog_entry')->entry_content($entry['id']);

            $renderer->doc .= '<article class="entry">';
            $renderer->doc .= '<div class="entry-content">';
            $renderer->doc .= '<h1><a href="'.wl($entry['id']).'">'.$head.'</a></h1>';
            $renderer->doc .= html_secedit($content, false); // no section edit button
            $renderer->doc .= '</div>';
            $renderer->doc .= '<footer class="entry-footer">';
            $renderer->doc .= '<span class="entry-date">'.dformat($entry['date']).'</span>';
            $renderer->doc .= '<span class="entry-author">'.$entry['user'].'</span>';
            $renderer->doc .= '<span class="entry-comment"><a href="'.wl($entry['id'],'',true).'#disqus_thread"></a></span>';
            $renderer->doc .= '</footer>';
            $renderer->doc .= '</article>';
        }

        // pagination
        $renderer->doc .= '<article class="entry entry-navigation"><div class="entry-content">';
        if ($less !== -1) {
            $renderer->doc .= '<p class="medialeft"><a href="'.wl($ID, 'page='.$less).'" class="wikilink1">'.$this->getLang('newer').'</a></p>';
        }
        if ($more !== -1) {
            $renderer->doc .= '<p class="mediaright"><a href="'.wl($ID, 'page='.$more).'" class="wikilink1">'.$this->getLang('older').'</a></p>';
        }
        $renderer->doc .= '</div></article>';
    }
}
