<?php
/**
 * DokuWiki Plugin miniblog
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gina Haeussge <osd@foosel.net> (upstream)
 * @author  lainme <lainme993@gmail.com>
 */

// must be run within Dokuwik
if (!defined('DOKU_INC')) die();

class action_plugin_miniblog extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('FEED_MODE_UNKNOWN', 'BEFORE', $this, 'handle_mode_unknown');
        $controller->register_hook('FEED_ITEM_ADD', 'BEFORE', $this, 'handle_item_add');
    }

    public function handle_mode_unknown(&$event, $param) {
        if ($event->data['opt']['feed_mode'] != 'miniblog') return;

        $event->preventDefault();
        $event->data['data'] = array();

        $entries = $this->loadHelper('miniblog_entry')->entry_list('blog');
        $entries = array_slice($entries, 0, $event->data['opt']['items']);

        // add entries to feed
        foreach ($entries as $entry) {
            $event->data['data'][] = $entry;
        }
    }

    public function handle_item_add(&$event, $param) {
        if ($event->data['opt']['feed_mode'] != 'miniblog') return;

        // remove first heading from content
        list($head, $content) = $this->loadHelper('miniblog_entry')->entry_content($event->data['ditem']['id'], true);
        $event->data['item']->description = $content;
    }
}
