<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Interfaces;

/**
 * A check that runs before every request (see HubletoOverrides/GuardedRenderer).
 * It returns the route to render: the requested one when allowed, otherwise a replacement.
 */
interface RouteGuard
{
  public function getRouteToRender(string $route, string $controllerClass): string;
}
