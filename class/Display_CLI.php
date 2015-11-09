<?php

class Display_CLI {

	public function promptSearch() {
		echo "============\n";
		echo "LINE Webtoon\n";
		echo "============\n";
		echo "\n";
		echo "Search: ";
		$search = trim(fgets(STDIN));
		echo "\n";

		return $search;
	}

	public function promptChoice($result) {
		echo "Search Result:\n";
		echo "--------------\n";
		foreach ($result as $i => $page) {
			if ($page->isAvailable()) {
				echo "$i > {$page->getTitle()}\n";
			} else {
				unset($result[$i]);
			}
		}
		if (count($result) < 1) {
			echo "Sorry, can't access them\n";
			exit;
		}

		echo "Choice [".implode(',', array_keys($result))."]: ";
		$choice = trim(fgets(STDIN));
		echo "\n";

		if (!isset($result[$choice])) {
			echo "Wrong choice\n";
			exit;
		}

		return $result[$choice];
	}

	public function promptChapters($text, $chapterMax) {
		echo "$text\n";
		echo str_repeat('-', strlen($text))."\n";
		echo "Chapters [1-$chapterMax]: ";
		$chapterInput = trim(fgets(STDIN));
		echo "\n";

		if (preg_match('#^(\d+)-(\d+)$#', $chapterInput, $matches)) {
			$chapterStart = $matches[1];
			$chapterEnd = $matches[2];
		} else {
			$chapterStart = $chapterEnd = $chapterInput;
		}

		if ($chapterStart < 1 || $chapterStart > $chapterEnd || $chapterEnd > $chapterMax) {
			echo "That chapter doesn't exist\n";
			exit;
		}

		return array($chapterStart, $chapterEnd);
	}

	public function displayChapter($chapter, $total) {
		echo "Total Image in chapter $chapter: $total\n";
		echo "Downloading: ";
	}

	public function updateChapter($i) {
		echo ($i+1).".";
	}

	public function finishChapter() {
		echo "Done!\n";
	}
}