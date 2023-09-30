<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('/', 'MusicController::index');
$routes->post('/createPlaylist', 'MusicController::createPlaylist');
$routes->post('/upload', 'MusicController::uploadMusic');
$routes->post('music/getPlaylistMusic', 'MusicController::getPlaylistMusic');
$routes->post('music/addToPlaylist', 'MusicController::addToPlaylist');
$routes->get('playlist/(:num)', 'MusicController::playlists/$1');
