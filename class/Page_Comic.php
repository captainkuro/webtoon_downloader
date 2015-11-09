<?php

use Simplon\Request\Request;
use Symfony\Component\DomCrawler\Crawler;

class Page_Comic {
	private $text;
	private $url;
	private $opts;
	private $request;
	private $chapterMax = null;
	private $chapterUrlPattern = null;

	public function __construct($text, $url, $opts = array(), $request = null) {
		$this->text = $text;
		$this->url = $url;
		$this->opts = $opts;
		if (is_null($request)) {
			$request = new Request();
		}
		$this->request = $request;
	}

	public function getTitle() {
		return $this->text;
	}

	public function isAvailable() {
		$head = $this->request->get($this->url, array(), array(CURLOPT_CUSTOMREQUEST => 'HEAD', CURLOPT_NOBODY => 1, CURLOPT_FOLLOWLOCATION => 1));
		return($head->getHttpCode() == 200);
	}

	private function fetchInformation() {
		if (is_null($this->chapterMax)) {
			$resp = $this->request->get($this->url, array(), $this->opts);
			$dom = new Crawler($resp->getContent());

			$lastChapter = $dom->filter('#_listUl a')->first();
			$this->chapterMax = str_replace('#', '', $lastChapter->filter('.tx')->text());
			$this->chapterUrlPattern = str_replace("episode_no={$this->chapterMax}", "episode_no=:CHAPTER:", $lastChapter->attr('href'));
		}
	}

	public function getChapterMax() {
		$this->fetchInformation();
		return $this->chapterMax;
	}

	public function getChapterPage($chapter) {
		$this->fetchInformation();
		$chapterUrl = str_replace(':CHAPTER:', $chapter, $this->chapterUrlPattern);
		return new Page_Chapter($chapterUrl, $this->opts, $this->request);
	}
}