<?php

namespace Drupal\fb_instant_articles;

/**
 * Adds Drupal logging functionality to existing SDK logging.
 *
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
    \Drupal::logger($event->getLoggerName())->log(
      // Watchdog levels follow RFC 3164.
      $event->getLevel()->getSyslogEquivalent(),
      $event->getRenderedMessage()
    );
  }

}
