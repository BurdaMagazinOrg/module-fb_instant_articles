Introduction
------------

This optional module adds Entity support to Facebook Instant Articles, allowing
 administrators to configure which Entity types and Bundles can be Instant
 Articles, and to map the fields of those Bundles to the format expected by the
 Facebook service. If enabled, this module will include the any such field
 mappings in an InstantArticle object attached to relevant Entities, which can
 be sent to Facebook by either a Feed or the API (see the Base module README for
 details on which packaged modules to use for each of these methods).

Requirements
------------

This module requires the following modules:

- [CTools](https://drupal.org/project/ctools)

Recommended modules
-------------------

- [Markdown filter](https://www.drupal.org/project/markdown):
  When enabled, display of the project's README.md help will be rendered
  with markdown.
- Field UI (optional Drupal core module)
  When enabled, administrators can configure which Entity types and Bundles can
  be Instant Articles, and set the fields mappings. On production sites, it is
  standard practice to store these configurations in code, and disable field_ui.

Installation
------------

Install as you would normally install a contributed Drupal module. See
 [Installing modules (Drupal 7)](https://drupal.org/documentation/install/modules-themes/modules-7)
 for further information.

Configuration
-------------

- Configure user permissions in Administration » People » Permissions

  - Administer Facebook Instant Articles Display

    Users in roles with the "Administer Facebook Instant Articles Display"
    permission will be able to configure which Entity types and Bundles can be
    Instant Articles. Supported Entity types have a UI for this setting on their
    Bundle form (example: /admin/structure/types/manage/TYPE). To enable other
    Entity types and Bundles, see fb_instant_articles_display.api.php.

- Configure field mappings on each "Manage Display" UI tab for each configured
  Bundle, (example: admin/structure/types/manage/BUNDLE/display).

Troubleshooting
---------------

- When implementing `hook_fb_instant_articles_article_alter()` when this
  optional Display module is enabled, it is necessary to check that the Entity
  type and ID of the Entity you plan to load is not the same as the current
  Entity (this module stores that in the `$context` param of that hook).
  This check is required in order to prevent an `entity_load()` recursion,
  because this module's implementation of `hook_entity_load()` creates an
  `InstantArticle` object, which invokes this hook again. Code example:

        function hook_fb_instant_articles_article_alter($instantArticle, $context) {
          $target_type = 'user';
          $target_id = 9999;
          list($id) = entity_extract_ids($context['entity_type'], $context['entity']);
          if ($target_type != $context['entity_type'] && $target_id != $id) {
             $entities = entity_load($target_type, array($target_id));
            // Now do whatever you want with your Entity without worrying about recursion.
          }
        }
