<?php

$heartbeat_interval = 30;
$arg_options = getopt("", ["heartbeat-interval:","help"]);
$arg_opt_heartbeat = isset($arg_options["heartbeat-interval"]);
$arg_opt_help = isset($arg_options["help"]);

if($arg_opt_heartbeat)
{
        $heartbeat_interval= intval($arg_options["heartbeat-interval"]);
        if($heartbeat_interval <= 0)
        {
                $heartbeat_interval = 30;
        }
        print "WebSocket Heatbeat Interval: " . $heartbeat_interval . " seconds\n";
}
else
{
        print "Syntax: php ws-socket.php --heartbeat-interval <seconds>\n";
        print "Example: php ws-socket.php --heartbeat-interval 10\n";
        die();
}

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

$wsServer_CommandExecution = new Ratchet\WebSocket\WsServer(new CommandExecution());
$wsServer_ReflectedXSS = new Ratchet\WebSocket\WsServer(new ReflectedXSS());
$wsServer_ShowComments = new Ratchet\WebSocket\WsServer(new ShowComments());
$wsServer_PostComments = new Ratchet\WebSocket\WsServer(new PostComments());
$wsServer_AuthenticateUser = new Ratchet\WebSocket\WsServer(new AuthenticateUser());
$wsServer_AuthenticateUserPrepared = new Ratchet\WebSocket\WsServer(new AuthenticateUserPrepared());
$wsServer_AuthenticateUserBlind = new Ratchet\WebSocket\WsServer(new AuthenticateUserBlind());
$wsServer_FileInclusion = new Ratchet\WebSocket\WsServer(new FileInclusion());
$wsServer_AuthenticateUserPreparedSession = new Ratchet\WebSocket\WsServer(new AuthenticateUserPreparedSession());
$wsServer_ChangePassword = new Ratchet\WebSocket\WsServer(new ChangePassword());

$collection->add('command-execution', new Route('/command-execution', array(
        '_controller' => $wsServer_CommandExecution,'allowedOrigins' => '*'
    )));

$collection->add('reflected-xss', new Route('/reflected-xss', array(
        '_controller' => $wsServer_ReflectedXSS, 'allowedOrigins' => '*'
    )));
$collection->add('show-comments', new Route('/show-comments', array(
        '_controller' => $wsServer_ShowComments, 'allowedOrigins' => '*'
    )));
$collection->add('post-comments', new Route('/post-comments', array(
        '_controller' => $wsServer_PostComments, 'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user', new Route('/authenticate-user', array(
        '_controller' => $wsServer_AuthenticateUser, 'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user-prepared', new Route('/authenticate-user-prepared', array(
        '_controller' => $wsServer_AuthenticateUserPrepared, 'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user-blind', new Route('/authenticate-user-blind', array(
        '_controller' => $wsServer_AuthenticateUserBlind, 'allowedOrigins' => '*'
    )));
$collection->add('file-inclusion', new Route('/file-inclusion', array(
        '_controller' => $wsServer_FileInclusion, 'allowedOrigins' => '*'
    )));
$collection->add('authenticate-user-prepared-session', new Route('/authenticate-user-prepared-session', array(
        '_controller' => $wsServer_AuthenticateUserPreparedSession, 'allowedOrigins' => '*'
    )));
$collection->add('change-password', new Route('/change-password', array(
        '_controller' => $wsServer_ChangePassword, 'allowedOrigins' => '*'
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


$wsServer_CommandExecution->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_ReflectedXSS->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_ShowComments->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_PostComments->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_AuthenticateUser->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_AuthenticateUserPrepared->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_AuthenticateUserBlind->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_FileInclusion->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_AuthenticateUserPreparedSession->enableKeepAlive($server->loop, $heartbeat_interval);
$wsServer_ChangePassword->enableKeepAlive($server->loop, $heartbeat_interval);

$server->run();
