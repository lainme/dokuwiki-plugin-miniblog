<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class helper_plugin_miniblog_comment extends DokuWiki_Plugin {
    public function comment_script($source, $option) {
        $doc = '<script type="text/javascript">';

        foreach ($option as $key => $value) {
            $doc .= 'var '.$key.'="'.$value.'";';
        }

        $doc .= '(function () { var s = document.createElement("script");';
        $doc .= 's.async = true; s.type = "text/javascript";';
        $doc .= 's.src = "//" + disqus_shortname + ".disqus.com/'.$source.'";';
        $doc .= '(document.getElementsByTagName("HEAD")[0] || document.getElementsByTagName("BODY")[0]).appendChild(s);';
        $doc .= '}()); </script>';

        return $doc;
    }
}
