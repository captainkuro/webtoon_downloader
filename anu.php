<?php
require 'vendor/autoload.php';

use Simplon\Request\Request;
use Symfony\Component\DomCrawler\Crawler;

$request = new Request();
$url = "http://www.webtoons.com/in/challenge/3-roomates-and-that-1-guy/list?title_no=6731";
$resp = $request->get($url, array(), array(CURLOPT_CUSTOMREQUEST => 'HEAD', CURLOPT_NOBODY=>1));
// $resp = $request->postVariant('HEAD', $url, array(), array(CURLOPT_FOLLOWLOCATION => 1), 'json');
print_r($resp->getHttpCode());
