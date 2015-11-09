<?php

use Simplon\Request\Request;
use Symfony\Component\DomCrawler\Crawler;

class Page_Chapter {
	private $url;
	private $opts;
	private $request;
	private $total = null;
	private $imageUrls = null;

	public function __construct($url, $opts = array(), $request = null) {
		$this->url = $url;
		$this->opts = $opts;
		if (is_null($request)) {
			$request = new Request();
		}
		$this->request = $request;
	}

	private function fetchInformation() {
		if (is_null($this->total)) {
			$resp = $this->request->get($this->url, array(), $this->opts);
			$dom = new Crawler($resp->getContent());

			$images = $dom->filter('#_imageList img');
			$this->total = $images->count();
			$images->each(function ($img) {
				$this->imageUrls[] = $img->attr('data-url');
			});
		}
	}

	public function getTotal() {
		$this->fetchInformation();
		return $this->total;
	}

	public function getImageUrls() {
		$this->fetchInformation();
		return $this->imageUrls;
	}
}