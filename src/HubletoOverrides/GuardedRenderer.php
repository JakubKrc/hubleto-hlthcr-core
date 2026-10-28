<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\HubletoOverrides;

use Hubleto\App\External\WaiBlue\HlthcrCore\Interfaces\RouteGuard;
use Hubleto\Framework\Services\Router;

/**
 * Hubleto's renderer with route guards in front of it. Hlthcr apps register guards
 * (for example the record write rules) instead of each replacing the renderer.
 */
class GuardedRenderer extends \Hubleto\Erp\Renderer
{
  /** @var string[] RouteGuard classes, checked in the order they were added */
  private static array $routeGuardClasses = [];

  private static bool $isRegistered = false;

  public static function register(): void
  {
    if (self::$isRegistered) return;
    self::$isRegistered = true;

    \Hubleto\Framework\DependencyInjection::setServiceProvider(\Hubleto\Framework\Services\Renderer::class, self::class);
    // Hubleto starts its renderer before the apps are built, so the replacement needs the same start.
    \Hubleto\Erp\Loader::getGlobalApp()->renderer()->init();
  }

  public static function addRouteGuard(string $routeGuardClass): void
  {
    if (in_array($routeGuardClass, self::$routeGuardClasses)) return;
    self::$routeGuardClasses[] = $routeGuardClass;
  }

  public function render(string $route = '', array $params = []): string
  {
    if (empty($route)) $route = $this->router()->extractRouteFromRequest();

    $routeData = $this->router()->parseRoute(Router::HTTP_GET, $route);
    $controllerClass = (string) ($routeData['controller'] ?? '');

    // Hubleto signs in a "remember me" user only in parent::render(), so the guards would see no user.
    $authProvider = $this->authProvider();
    if (!$authProvider->isUserInSession()) $authProvider->authenticateRememberedUser();

    foreach (self::$routeGuardClasses as $routeGuardClass) {
      /** @var RouteGuard */
      $routeGuard = $this->getService($routeGuardClass);
      $routeToRender = $routeGuard->getRouteToRender($route, $controllerClass);
      if ($routeToRender != $route) return parent::render($routeToRender, $params);
    }

    return parent::render($route, $params);
  }
}
