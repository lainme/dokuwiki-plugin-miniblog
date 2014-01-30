<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwik
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_miniblog extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('FEED_MODE_UNKNOWN', 'BEFORE', $this, 'handle_mode_unknown');
        $controller->register_hook('FEED_ITEM_ADD', 'BEFORE', $this, 'handle_item_add');
    }

    function handle_mode_unknown(&$event, $param) {
        if ($event->data['opt']['feed_mode'] != 'miniblog') return;

        $event->preventDefault();
        $event->data['data'] = array();

        $entries = array_slice($this->loadHelper('miniblog')->get_entries(), 0, $event->data['opt']['items']);

        foreach ($entries as $entry) {
            $event->data['data'][] = array(
                'id' => $entry,
                'date' => p_get_metadata($entry, 'date created', METADATA_DONT_RENDER),
                'user' => p_get_metadata($entry, 'user', METADATA_DONT_RENDER),
            );
        }
    }

    function handle_item_add(&$event, $param) {
        if ($event->data['opt']['feed_mode'] != 'miniblog') return;

        // strip first heading 
        $entry = $event->data['ditem']['id'];
        list($head, $content) = $this->loadHelper('miniblog')->get_contents($entry);

        $event->data['item']->description = $content;
    }
}
