<?php

/**
 * @file
 * Hooks provided by Facebook Instant Articles RSS module.
 */

/**
 * Allows modules to alter if an entity is a Facebook Instant Article.
 *
 * @param bool $is_type
 *   By reference. Whether or not the entity is a Facebook Instant
 *   Article.
 * @param int $entity_id
 *   The entity ID.
 *
 * @see fb_instant_articles_rss_is_article()
 */
function hook_fb_instant_articles_rss_is_article_alter(&$is_type, $entity_id) {
}

/**
 * Allows modules to perform actions after setting an entity as a Facebook
 * Instant Article.
 *
 * @param int $id
 * @param bool $enabled
 *
 * @see fb_instant_articles_rss_set_entity()
 */
function hook_fb_instant_articles_rss_set_entity($id, $enabled) {
}

/**
 * Allows modules to perform actions after unsetting an entity as a Facebook
 * Instant Article
 *
 * @param bool $id
 *   The entity ID that has been unset.
 *
 * @see fb_instant_articles_rss_delete_entity()
 */
function hook_fb_instant_articles_rss_delete_entity($id) {
}
