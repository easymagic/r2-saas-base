<?php 

$uri = $_SERVER['REQUEST_URI'];

$uri = trim($uri, '/');

$page = 'ssr_pages/'.$uri.'.php';

if (file_exists($page)) {
    include $page;
} else {
    include 'ssr_pages/404.php';
}
