<?php
require 'vendor/autoload.php';

use Simplon\Request\Request;
use Symfony\Component\DomCrawler\Crawler;

// Opening
echo "============\n";
echo "LINE Webtoon\n";
echo "============\n";
echo "\n";
echo "Search: ";
$search = trim(fgets(STDIN));
echo "\n";

// Global
$request = new Request();
$opts = Config::$opts;

// Parse search
$search_url = Config::$baseurl."/search?keyword=$search";
$search_resp = $request->get($search_url, array(), $opts);
$search_dom = new Crawler($search_resp->getContent());

$found = ($search_dom->filter(".card_nodata")->count() <= 0);
if (!$found) {
	echo "Sorry, not found\n";
	exit;
}

$result = array();
$search_dom->filter(".search .card_lst li a")->each(function ($a) {
	global $result;
	$text = $a->filter('.subj')->text();
	$url = Config::$baseurl.$a->attr('href');
	$result[] = array($text, $url);
});
$search_dom->filter(".search .challenge_lst li a")->each(function ($a) {
	global $result;
	$text = $a->filter('.subj')->text();
	$url = Config::$baseurl.$a->attr('href');
	$result[] = array($text, $url);
});

echo "Search Result:\n";
echo "--------------\n";
foreach ($result as $i => list($text, $url)) {
	$head = $request->get($url, array(), array(CURLOPT_CUSTOMREQUEST => 'HEAD', CURLOPT_NOBODY => 1, CURLOPT_FOLLOWLOCATION => 1));
	if ($head->getHttpCode() == 200) {
		echo "$i > $text\n";
	} else if ($head->getHeader()->getLocation()) {
		echo "$i > $text\n";
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

// Parse comic
if (!isset($result[$choice])) {
	echo "Wrong choice\n";
	exit;
}
list($text, $url) = $result[$choice];
$comic_resp = $request->get($url, array(), $opts);
$comic_dom = new Crawler($comic_resp->getContent());

$last_chapter = $comic_dom->filter('#_listUl a')->first();
$chapter_max = str_replace('#', '', $last_chapter->filter('.tx')->text());
$chapter_url_pattern = str_replace("episode_no=$chapter_max", "episode_no=:CHAPTER:", $last_chapter->attr('href'));
$chapter_url_pattern = str_replace(" ", "%20", $chapter_url_pattern);

echo "$text\n";
echo str_repeat('-', strlen($text))."\n";
echo "Chapters [1-$chapter_max]: ";
$chapter_input = trim(fgets(STDIN));
echo "\n";

// Parse chapter
function download_image($url, $save_to) {
	global $opts;

	$ch = curl_init();
	$fp = fopen($save_to, 'w');

	$download_opts = $opts;
	$download_opts[CURLOPT_URL] = $url;
	$download_opts[CURLOPT_FILE] = $fp;
	$download_opts[CURLOPT_HEADER] = 0;
	$download_opts[CURLOPT_REFERER] = $url;
	curl_setopt_array($ch, $download_opts);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}

if (preg_match('#^(\d+)-(\d+)$#', $chapter_input, $matches)) {
	$chapter_start = $matches[1];
	$chapter_end = $matches[2];
} else {
	$chapter_start = $chapter_end = $chapter_input;
}

for ($chapter = $chapter_start; $chapter <= $chapter_end; $chapter++) {
	if ($chapter < 1 || $chapter > $chapter_max) {
		echo "That chapter doesn't exist\n";
		exit;
	}
	$chapter_url = str_replace(':CHAPTER:', $chapter, $chapter_url_pattern);
	$chapter_resp = $request->get($chapter_url, array(), $opts);
	$chapter_dom = new Crawler($chapter_resp->getContent());

	$images = $chapter_dom->filter('#_imageList img');
	$total = $images->count();
	echo "Total Image in chapter $chapter: $total\n";
	echo "Downloading: ";
	$images->each(function ($img, $i) use ($text, $chapter) {
		$id = $i+1;
		echo "$id.";
		$image_url = $img->attr('data-url');
		$chapter_dir = str_pad($chapter, 3, 0, STR_PAD_LEFT);
		$download_dir = "download/$text/$chapter_dir/";
		if (!is_dir($download_dir)) mkdir($download_dir, 0777, true);
		
		if (preg_match('#\.(\w{3})\?#', $image_url, $matches)) {
			$ext = $matches[1];
		} else {
			$ext = 'jpg';
		}
		$filename = str_pad($id, 3, 0, STR_PAD_LEFT).'.'.$ext;
		download_image($image_url, $download_dir.$filename);
	});
	echo "Done!\n";
}