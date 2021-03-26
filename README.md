**Please note that development of this module has moved back to drupal.org.**

Please file issues in the drupal.org issue queue and submit merge requests
there via drupal.org's Gitlab integration.

# Drupal Facebook Instant Articles

The Facebook Instant Articles modules allow administrators to add content from a
Drupal site to Facebook, to be viewed on mobile devices as Instant Articles.

## About the Facebook Instant Articles service

Facebook has [introductory information for publishers](https://instantarticles.fb.com/)
as well as [in-depth documentation for developers](https://developers.facebook.com/docs/instant-articles).

## About Drupal integration

Before installing this module or packaged sub-modules, there are two methods to
choose from for how to add your content to Facebook as Instant Articles (either
via a RSS feed or Facebook's API).

### 1. RSS Feed method

On a basic Drupal site, with one or more article-like content types that you
wish to post to Facebook as Instant Articles, the simplest way is to create a
feed from your Drupal site and then have your Facebook page admin configure
the Instant Articles options to point to your website to ingest the feed.

You'll need to configure which content types you want to be allowable as Instant
Articles, then configure a view mode to specify which fields you want to map to
which region of the Instant Article format, then you're all ready to create the
feed.

To create the actual feed, simple enable the Facebook Instant Articles Views
module. This module ships with a default view which creates an RSS feed of your
content accessible at /instant-articles.rss. If you require more customized
filtering, simply edit the view.

### 2. API method

If you're a publisher who wants more control over exact timing of publishing -
editing, modifying public access, or removing content from your Facebook Instant
Articles library - the API approach allows for greater control.

Simply enable the Facebook Instant Articles API module. After enabling,
configure your API settings on the configuration page at
/admin/config/services/fb_instant_articles/api_settings. After that, the module
will begin creating/editing/deleting content in Instant Articles as you
create/edit/delete your Facebook Instant Articles enabled content types.

## Dependencies

[Facebook Instant Articles PHP SDK](https://github.com/facebook/facebook-instant-articles-sdk-php)
Provides necessary support for ensuring Drupal content is added to Facebook in
the required format for Instant Articles. The module ships with a composer.json
file covering this dependency.

## Installation

Before installing the Drupal Facebook Instant Articles modules, see  "About
Drupal integration" above to help choose which modules are right for you. Then
see "Dependencies" above. Then:

- Install Composer dependencies. See [Installing modules' composer dependencies](https://www.drupal.org/docs/8/extending-drupal-8/installing-modules-composer-dependencies).
- Install the submodules you choose as you would normally install a contributed
  Drupal module. See [Installing modules](https://www.drupal.org/docs/8/extending-drupal-8/installing-modules)
  for further information.

## Configuration

All configuration for the module is made available by the base module, including
configuration for delivery via the Facebook API. API configuration is only
necessary when using the Facebook Instant Articles API module, or another custom
module that uses the fb_instant_articles.drupal_client service. Thus it can be
safely ignored if you are using the RSS feed method.

Configuration can be found at /admin/config/services/fb_instant_articles. There
are a number of options there with ample documentation, please follow the
descriptions on the form to guide you.

## Behat tests

This module ships with a bunch of Behat tests, found in `tests/src/Behat`. Here
are the steps to set up your local environment to run them:

1. Ensure you've installed the composer dev dependencies of the module.
2. In the module, copy `tests/src/Behat/example.behat.local.yml` to
`tests/src/Behat/behat.local.yml` and edit as needed. At the least, IP addresses
and URL's need to be changed.
3. Download Selenium 3 and run it (changing the path as needed). The jar is available in Homebrew for OS X:
```
$ java -jar /usr/local/Cellar/selenium-server-standalone/3.4.0/libexec/selenium-server-standalone-3.4.0.jar
```
4. Run tests
```
$ cd <path to web root>/modules/contrib/fb_instant_articles
$ ../../../vendor/bin/behat -c tests/src/Behat/behat.local.yml tests/src/Behat/features
```

## Issues and Development

- Issues should be made in the project's issue queue on Drupal.org.
- All development is happening in the project's issue queue on Drupal.org.

## Maintainers

This module is maintained by the [Drupal community](https://www.drupal.org/node/2676800/committers).
