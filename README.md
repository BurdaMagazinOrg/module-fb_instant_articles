# Facebook Instant Articles

A custom module to produce an RSS field that is compatible with the
Facebook Instant Articles program.

## To use

First install the module into your application using the standard approach: https://www.drupal.org/documentation/install/modules-themes/modules-8

Then make sure to configure the module as described below.

## to configure

There is an administration page located at /admin/config/fb_instant_articles/adminconfig

In this page you can set

- pages id: A facebook provide id, that is used to insert a <meta> tag
into all Drupal pages that will look like <meta property="fb:pages" content="{id goes here}" />
