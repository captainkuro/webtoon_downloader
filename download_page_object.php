<?php
require 'vendor/autoload.php';

class WebtoonScraper {
	public function __construct() {
		$this->cli = new Display_CLI();
		$this->downloader = new Image_Downloader(Config::$opts);
	}

	public function run() {
		$search = $this->cli->promptSearch();

		$searchPage = new Page_Search($search, Config::$baseurl, Config::$opts);
		$result = $searchPage->getResultPages();

		$comicPage = $this->cli->promptChoice($result);
		$text = $comicPage->getTitle();
		$chapterMax = $comicPage->getChapterMax();
		
		list($chapterStart, $chapterEnd) = $this->cli->promptChapters($text, $chapterMax);
		
		for ($chapter = $chapterStart; $chapter <= $chapterEnd; $chapter++) {
			$chapterPage = $comicPage->getChapterPage($chapter);
			$this->cli->displayChapter($chapter, $chapterPage->getTotal());

			foreach ($chapterPage->getImageUrls() as $i => $imageUrl) {
				$this->cli->updateChapter($i);
				$this->downloader->download($imageUrl, $chapter, $text, $i);
			}
			$this->cli->finishChapter();
		}
	}
}

(new WebtoonScraper())->run();