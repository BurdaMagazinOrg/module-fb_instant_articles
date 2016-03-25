INTRODUCTION
------------

The Facebook Instant Articles module allows administrators to create an RSS feed
compatible with the Facebook Instant Articles specification. The module provides
a new View Mode for content type where administrators can map Drupal fields with
 FB Instant Article formats.

 * FB Instant Article doc: https://developers.facebook.com/docs/instant-articles

 * For a full description of the module, visit the project page:
   https://www.drupal.org/node/2614462

REQUIREMENTS
------------

This module requires the following modules:

 * CTools (https://drupal.org/project/ctools)

INSTALLATION
------------

This module uses Composer to install dependencies. You can either execute
`composer install` directly or use the composer_manager module.

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-7
   for further information.
 * Install Composer dependencies. See https://www.drupal.org/node/2404989 for
   more information on using Composer in a Drupal project.

CONFIGURATION
-------------

 * Enable a Content type to appear in the feed by editing your content type and
   enabling it on the Facebook Instant Articles vertical tab. Select the option:
   "Include Content Type in Facebook Instant Articles feed".

 * Once enabled go to Manage Display on your Content Type and on the view mode
 Facebook Instant Articles. There you can map your fields to FB Instant Article
  compatible formats and place them in the correct regions.

 * Access /fbinstant.rss of your site to view the feed.

 * Configure the feed settings in Administration » Web Services »
 Facebook Instant Articles Settings:

   - You can set the channel title, description and language.

MAINTAINERS
-----------

Current maintainers:
 * Vin Gardner (vgardner) - https://www.drupal.org/u/vgardner
