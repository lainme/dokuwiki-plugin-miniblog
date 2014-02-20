<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_miniblog_comment extends DokuWiki_Syntax_Plugin {
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
        $this->Lexer->addSpecialPattern('~~DISQUS~~', $mode, 'plugin_miniblog_comment');
    }

    public function handle($match, $state, $pos, &$handler){
        return array();
    }

    public function render($mode, &$renderer, $data) {
        global $ID;
        global $INFO;

        if ($mode != 'xhtml') return false;

        $source = "embed.js";
        $option = array(
            'disqus_shortname' => $this->getConf('shortname'),
            'disqus_title' => addslashes($INFO['meta']['title']),
            'disqus_url' => wl($ID, '', true),
        );

        $renderer->doc .= plugin_load('helper', 'miniblog_comment')->comment_script($source, $option);

        return true;
    }
}
