<?php

class Config {
	public static $opts = array(
		CURLOPT_HTTPHEADER => array(
			'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
			'Accept-Language: en-US,en;q=0.8'
		),
		// CURLOPT_COOKIE => 'contentLanguage=id;locale=in', // For english comic, comment this line
		CURLOPT_FOLLOWLOCATION => 1,
	);
}