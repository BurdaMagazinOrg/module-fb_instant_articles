# Drupal Facebook Instant Articles

The Facebook Instant Articles modules allow administrators to add content from a 
Drupal site to Facebook, to be viewed on mobile devices as Instant Articles.

## About the Facebook Instant Articles service

Facebook has [introductory information for publishers](https://instantarticles.fb.com/)
as well as [in-depth documentation for developers](https://developers.facebook.com/docs/instant-articles).

## About Drupal integration

Before installing this module or packaged sub-modules, there are two methods to 
choose from for how to add your content to Facebook as Instant Articles (either 
via a feed or Facebook's API), and then simple or advanced options for each.

### 1. Feed method

On a basic Drupal site, with one or more article-like content types that you 
wish to post to Facebook as Instant Articles, the simplest way is to create a
feed from your Drupal site and then have your Facebook page admin configure
the Instant Articles options to point to your website to ingest the feed.

You'll need to configure which content types you want to be allowable as Instant 
Articles, then configure a view mode to specify which fields you want to map to
which region of the Instant Article format, then you're all ready to create the 
feed.

To create the actual feed, there are two main options: a simple RSS feed, or a 
more advanced feed using the [Views module](https://www.drupal.org/project/views). 

#### RSS feed (simple)

If you enable the fb_instant_articles_rss.module you will have a feed created 
for you automatically. You will then have the option to either add all published 
content of one or more types to the feed, or else select individual pieces of 
content to be included in the feed. 

#### Views feed (advanced)

Alternately, if you enable the fb_instant_articles_views.module you will be able
to choose more advanced filtering rules for which content is included in the 
feed. Currently only a rendered view mode (not a fields view) is supported.

### 2. API method
  
If you're a publisher who wants more control over exact timing of publishing - 
editing, modifying public access, or removing content from your Facebook Instant 
Articles library - the API approach allows for greater control.

#### API Rules integration

If you're using the API you will need to determine under what conditions the API
creates, edits or removes content from Facebook's Instant Articles library. This
submodule adds actions (both Drupal core actions and [Rules](https://drupal.org/project/rules) 
module actions) which can be triggered based on your chosen configurations.

#### API Report

Optionally enable this module to give a report of API interactions, so that for 
example any content that fails Facebook Instant Article format validation will
be easier to find and fix.

## Dependencies

The Drupal Facebook Instant Articles modules have different dependencies, see 
"About Drupal integration" above to help choose which modules are right for you.

- Base (fb_instant_articles.module)
    - Depends on:
        - [Facebook Instant Articles PHP SDK](https://github.com/facebook/facebook-instant-articles-sdk-php) 
provides necessary support for ensuring Drupal content is added to facebook in 
the required format for Instant Articles. 
    - Optionally (recommended) integrates with:
        - [Composer Manager](https://www.drupal.org/project/composer_manager) for 
autoloading classes needed for the Facebook Instant Articles SDK, and download 
the SDK for you automatically when you enable the base module. Alternately, if 
you do not wish to enable Composer Manager, you can choose to manually download 
and mange the dependencies via Composer yourself. In this case you will also 
need to include Composer's autoload file somewhere in your code. 
- Display (fb_instant_articles_display.module)
    - Depends on:
        - [CTools](https://drupal.org/project/ctools) for exporting 
        configurations.
- RSS feed (fb_instant_articles_rss.module)
    - No external dependencies.
- Views feed (fb_instant_articles_views.module)
    - Depends on:
        - [Views](https://drupal.org/project/views)
- API (fb_instant_articles_api.module)
    - No external dependencies.
- API Rules integration (fb_instant_articles_api_rules.module)
    - No external dependencies.
    - Optionally integrates with:
        - [Rules](https://drupal.org/project/rules)
- API Report (fb_instant_articles_api_report.module)
    - No external dependencies.


## Installation

Before installing the Drupal Facebook Instant Articles modules, see  "About 
Drupal integration" above to help choose which modules are right for you. Then
see "Dependencies" above. Then:

- Install the submodules you choose as you would normally install a contributed 
Drupal module. See [Installing modules (Drupal 7)](https://drupal.org/documentation/install/modules-themes/modules-7)
for further information.

## Configuration

See the README in your selected submodules for configuration help.

## Issues and Development

- Issues should be made in the project's issue queue on Drupal.org.
- All development is happening via PRs in [GitHub](https://github.com/BurdaMagazinOrg/module-fb_instant_articles).
