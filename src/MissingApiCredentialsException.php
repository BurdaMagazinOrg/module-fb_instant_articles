<?php

namespace Drupal\fb_instant_articles;

/**
 * API client is attempted to be used before it's been configured.
 */
class MissingApiCredentialsException extends \Exception {
}
