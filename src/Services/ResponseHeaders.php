<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

/**
 * Hubleto (dev-main) re-opens the session on every session read, and with Apache mod_php each re-open repeats
 * the Set-Cookie header. Hundreds of them exceed browser limits (ERR_RESPONSE_HEADERS_TOO_BIG), e.g. on the calendar.
 */
class ResponseHeaders
{
  // Registered with header_register_callback(): runs just before PHP sends the headers.
  public static function keepEachCookieOnce(): void
  {
    $lastSetCookieByName = [];
    foreach (headers_list() as $header) {
      if (stripos($header, 'Set-Cookie:') !== 0) continue;

      $cookie = trim(substr($header, strlen('Set-Cookie:')));
      $cookieName = strstr($cookie, '=', true);
      // The browser applies the last value of a cookie, so the last one is kept.
      $lastSetCookieByName[$cookieName] = $cookie;
    }

    if (count($lastSetCookieByName) == 0) return;

    header_remove('Set-Cookie');
    foreach ($lastSetCookieByName as $cookie) {
      header('Set-Cookie: ' . $cookie, false);
    }
  }
}
