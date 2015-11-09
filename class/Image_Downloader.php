<?php

class Image_Downloader {
	private $opts;

	public function __construct($opts) {
		$this->opts = $opts;
	}

	public function download($imageUrl, $chapter, $text, $i) {
		$chapterDir = str_pad($chapter, 3, 0, STR_PAD_LEFT);
		$downloadDir = "download/$text/$chapterDir/";
		if (!is_dir($downloadDir)) mkdir($downloadDir, 0777, true);
		
		if (preg_match('#\.(\w{3})\?#', $imageUrl, $matches)) {
			$ext = $matches[1];
		} else {
			$ext = 'jpg';
		}
		
		$id = $i+1;
		$filename = str_pad($id, 3, 0, STR_PAD_LEFT).'.'.$ext;
		$this->transfer($imageUrl, $downloadDir.$filename);
	}

	private function transfer($url, $saveTo) {
		$ch = curl_init();
		$fp = fopen($saveTo, 'w');

		$downloadOpts = $this->opts;
		$downloadOpts[CURLOPT_URL] = $url;
		$downloadOpts[CURLOPT_FILE] = $fp;
		$downloadOpts[CURLOPT_HEADER] = 0;
		$downloadOpts[CURLOPT_REFERER] = $url;
		curl_setopt_array($ch, $downloadOpts);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
}