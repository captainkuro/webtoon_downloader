<?php

use Simplon\Request\Request;
use Symfony\Component\DomCrawler\Crawler;

class Page_Search {
	private $search;
	private $opts;
	private $request;
	private $result;

	public function __construct($search, $opts = array(), $request = null) {
		$this->search = $search;
		$this->opts = $opts;
		if (is_null($request)) {
			$request = new Request();
		}
		$this->request = $request;
	}

	public function getResultPages() {
		$searchUrl = "http://www.webtoons.com/search?keyword={$this->search}";
		$searchResp = $this->request->get($searchUrl, array(), $this->opts);
		$searchDom = new Crawler($searchResp->getContent());

		$found = ($searchDom->filter(".card_nodata")->count() <= 0);
		if (!$found) {
			echo "Sorry, not found\n";
			exit;
		}

		$this->result = array();
		$searchDom->filter(".search .card_lst li a")->each(function ($a) {
			$text = $a->filter('.subj')->text();
			$url = "http://www.webtoons.com".$a->attr('href');
			$this->result[] = new Page_Comic($text, $url, $this->opts, $this->request);
		});
		$searchDom->filter(".search .challenge_lst li a")->each(function ($a) {
			$text = $a->filter('.subj')->text();
			$url = "http://www.webtoons.com".$a->attr('href');
			$this->result[] = new Page_Comic($text, $url, $this->opts, $this->request);
		});
		return $this->result;
	}
}