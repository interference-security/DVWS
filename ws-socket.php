<?php
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require 'class/CommandExecution.php';
require 'class/ReflectedXSS.php';
require 'class/ShowComments.php';
require 'class/PostComments.php';
require 'class/AuthenticateUser.php';
require 'class/AuthenticateUserPrepared.php';
require 'class/AuthenticateUserBlind.php';
require 'class/FileInclusion.php';
require 'class/AuthenticateUserPreparedSession.php';
require 'class/ChangePassword.php';

$collection = new RouteCollection;
$collection->add('command-execution', new Route('/command-execution', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new CommandExecution()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('reflected-xss', new Route('/reflected-xss', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new ReflectedXSS()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('show-comments', new Route('/show-comments', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new ShowComments()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('post-comments', new Route('/post-comments', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new PostComments()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user', new Route('/authenticate-user', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new AuthenticateUser()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user-prepared', new Route('/authenticate-user-prepared', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new AuthenticateUserPrepared()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user-blind', new Route('/authenticate-user-blind', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new AuthenticateUserBlind()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('file-inclusion', new Route('/file-inclusion', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new FileInclusion()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user-prepared-session', new Route('/authenticate-user-prepared-session', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new AuthenticateUserPreparedSession()
                            ),
        'allowedOrigins' => '*'
    )));
$collection->add('change-password', new Route('/change-password', array(
        '_controller' => new Ratchet\WebSocket\WsServer(
                                    new ChangePassword()
                            ),
        'allowedOrigins' => '*'
    )));

$router = new Ratchet\Http\Router(
                    new UrlMatcher($collection, 
                                   new RequestContext()
                    )
              );
$server = IoServer::factory(
    new HttpServer(
        $router
    ),
    8080
);
$server->run();
