<?php
require 'vendor/autoload.php';

class WebtoonScraper {
	public function __construct() {
		$this->opts = array(
			CURLOPT_HTTPHEADER => array(
				'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
				'Accept-Language: en-US,en;q=0.8'
			),
			CURLOPT_FOLLOWLOCATION => 1,
		);
		$this->cli = new Display_CLI();
		$this->downloader = new Image_Downloader($this->opts);
	}

	public function run() {
		$search = $this->cli->promptSearch();

		$searchPage = new Page_Search($search, $this->opts);
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