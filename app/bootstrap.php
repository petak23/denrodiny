<?php
use Nette\Application\Routers\Route,
    Nette\Application\Routers\RouteList,
    Nette\Application\Routers\SimpleRouter;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode(TRUE);
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

// Setup router using mod_rewrite detection
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) // pro Apache
    || isset($_SERVER["NETTE_HTACCESS"])) {
	$router = $container->getService('router');
	$router[] = new Route('index.php', 'Front:Homepage:default', Route::ONE_WAY);
  $router[] = new Route('urllist.txt', 'Mapa:Mapa:urllist', Route::ONE_WAY);
  $router[] = new Route('sitemap.xml', 'Mapa:Mapa:sitemap', Route::ONE_WAY);
	$router[] = new Route('clanky/domov', 'Front:Homepage:default', Route::ONE_WAY);
  
	$router[] = $adminRouter = new RouteList('Admin');
	$adminRouter[] = new Route('admin/<presenter>/<action>', 'Homepage:default');

	$router[] = $frontRouter = new RouteList('Front');
  $frontRouter[] = new Route('clanky[/<id>]', array(
    'presenter' => 'Clanky',
    'action' => 'default',
    'id' => array(
              Route::FILTER_IN => function ($id) use ($container) {
                  if (is_numeric($id)) {
                    return $id;
                  } else {
                     $hlavnemenu = $container->getService("hlavnemenu");
                    $hh = $hlavnemenu->findOneBy(array('spec_nazov'=>$id));
                    return $hh ? $hh->id : 0;
                  }
              },
              Route::FILTER_OUT => function ($id) use ($container) {
                  if (!is_numeric($id)) {
                    return $id;
                  } else {
                    $hlavnemenu = $container->getService("hlavnemenu");
                    $hh = $hlavnemenu->find($id);
                    return $hh ? $hh->spec_nazov : "";
                  }
              }
          ),
  ));
  $frontRouter[] = new Route('forgottenPassword', 'User:forgottenPassword');
  $frontRouter[] = new Route('profile', 'UserLog:default');
  $frontRouter[] = new Route('registration', 'User:registracia');
  $frontRouter[] = new Route('user[/<action>]', 'User:default');
  $frontRouter[] = new Route('userlog[/<action>]/<id>', 'UserLog:default');
  $frontRouter[] = new Route('oznam[/<action>]', 'Oznam:default');
  $frontRouter[] = new Route('pokladnicka[/<action>]', 'Pokladnicka:default');
  $frontRouter[] = new Route('error[/<action>]', 'Error:default');
  $frontRouter[] = new Route('<presenter>/<action>[/cokolvek]', 'Homepage:default');
  $frontRouter[] = new Route('[<presenter>][/<action>][/<spec_nazov><? \.html?|\.php|>]', 'Homepage:default', Route::ONE_WAY);  
} else {
	$container->addService('router', new SimpleRouter('Front:Homepage:default'));
}

return $container;