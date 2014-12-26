<?php

/**
 * Very basic streaming API example. In production you would store the
 * received tweets in a queue or database for later processing.
 *
 * Instructions:
 * 1) If you don't have one already, create a Twitter application on
 *      https://dev.twitter.com/apps
 * 2) From the application details page copy the consumer key and consumer
 *      secret into the place in this code marked with (YOUR_CONSUMER_KEY
 *      and YOUR_CONSUMER_SECRET)
 * 3) From the application details page copy the access token and access token
 *      secret into the place in this code marked with (A_USER_TOKEN
 *      and A_USER_SECRET)
 * 4) In a terminal or server type:
 *      php /path/to/here/streaming.php
 * 5) To stop the Streaming API either press CTRL-C or, in the folder the
 *      script is running from type:
 *      touch STOP
 *
 * @author themattharris
 */

function my_streaming_callback($data, $length, $metrics) {
	//echo $data .PHP_EOL;
	global $fp;
	global $collection;
	//echo "my streaming";
	$dataArray = json_decode($data, true);
	//print_r($dataArray);
	if(!empty($dataArray)){
			print_r((string)$dataArray["text"]);
			fputcsv($fp, array($dataArray["created_at"],$dataArray["id"],$dataArray["text"],$dataArray["user"]["screen_name"],$dataArray["user"]["followers_count"]));
			$collection->insert($dataArray);
			echo "Document inserted successfully";
	}  
	return file_exists(dirname(__FILE__) . '/STOP');
}

require 'tmhOAuth.php';
require 'tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
		'consumer_key'    => '0LRwCUJKeY7PRmwLsrPYRh6Cm',
		'consumer_secret' => 'AMrh3UX7C5voB1rXHbsAIm1VtbUi5CDDvgS4jTUCvlDwS4cMEi',
		'user_token'      => '2578760185-31Fqd6x9DeXfR63n6HsrAAR2Bkrho86ROSZp76V',
		'user_secret'     => '9n2upQxbaZ5gRhKkOoJFghBdqJVW2KHfIKyZdqIviRLQZ',
));

$method = 'https://stream.twitter.com/1.1/statuses/filter.json';
$word=  rawurlencode('ปฏิวัติซ้อน,เลือกตั้ง,ศรีรัศมิ์,ฟ้าชาย,โลกมืด6วัน,บุษบา,ในหลวง,สำนักพระราชวัง,รัฐธรรมนูญ,พงษ์พัฒน์,ทักษิณ');
$params = array(
	'track'     => $word,
	
);

$dbhost = 'localhost';  
$dbname = 'mydb';  
// connect to mongodb 
$m = new Mongo("mongodb://$dbhost");  
$db = $m->$dbname;  
// select the collection  
$collection = $db->streamCase2;
echo "Collection selected succsessfully\n";

$fp = fopen('output.csv', 'w');

fputcsv($fp,array('created_at','id','text','screen_name','number of follower'));

$tmhOAuth->streaming_request('POST', $method, $params, 'my_streaming_callback');

// output any response we get back AFTER the Stream has stopped -- or it errors
tmhUtilities::pr($tmhOAuth);
fclose($fp);

?>
