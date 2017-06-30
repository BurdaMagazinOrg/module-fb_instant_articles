# Facebook Instant Articles API

Exposes an RSS feed of Instant Articles via Views module. For use with the RSS 
feed method of delivering content to Facebook Instant Articles.

This module ships with a very simple way of using the Instant Article API 
related services exposed by the base module. It implements hook_node_insert, 
hook_node_update and hook_node_delete to mirror the operation on Facebook 
Instant Articles using the Instant Articles API.

If your particular use case requires more custom logic, implement a custom 
module following the pattern laid out in this module. If your needs are simple,
and you are publishing all content for enabled content types, this module should
suffice.

## Configuration

Enter your API credentials on the API Settings page at /admin/config/services/fb_instant_articles/api_settings.
Follow the descriptions on that form. Once you have connected your site, the
module will begin inserting, updating and deleting your content in Facebook as
you insert, update and delete content marked as enabled for Facebook Instant
Articles.
