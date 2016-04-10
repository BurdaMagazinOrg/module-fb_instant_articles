<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles\DrupalLoggerAppender.
 */

namespace Drupal\fb_instant_articles;

/**
 * Adds Drupal logging functionality to existing SDK logging.
 *
 * Class DrupalLoggerAppender
 * @package Drupal\fb_instant_articles
 *
 * @see fb_instant_articles_display_init()
 */
class DrupalLoggerAppender extends \LoggerAppender {

  /**
   * {@inheritdoc}
   *
   * Additionally sends logging event to Drupal watchdog.
   */
  public function append(\LoggerLoggingEvent $event) {
    watchdog(
      $event->getLoggerName(),
      $event->getRenderedMessage(),
      null,
      // Watchdog levels follow RFC 3164.
      $event->getLevel()->getSyslogEquivalent()
    );
  }

}
