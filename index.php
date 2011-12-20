<?php
require 'facebook.php';
include 'config.php';

$facebook = new Facebook(array(
  			'appId'  => $appid,
  			'secret' => $secret,
  			'cookie' => true
			));


$user = $facebook->getUser();


if ($user) {
	try {
		$user_profile = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		var_dump($e);
		$user = null;
	}
}

$param=array();
$param['scope']=array('offline_access','publish_stream','read_stream');


if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl($param);
}



if($user)
{
	$access_token=$facebook->getAccessToken();
    
    // Use 'since' parameter to get posts from a particular date
    // Use 'limit' and 'offset' to page
    // More documetation at https://developers.facebook.com/docs/reference/api/
    try {	
		$stream = $facebook->api(
			"/$source_ids/feed?limit=500",
			'GET',
			array(
				'access_token' => $access_token
			)
		);
	} catch  (FacebookApiException $e) {
		var_dump($e);
	}
	
	$posters = $textsize = $numbers = array();
	
	foreach($stream['data'] as $post) {
	
		if(isset($post['message'])) {				
			$post_size=strlen($post['message']);
		}
		
		$posters[$post['from']['id']]['name']	= $post['from']['name'];
		
		if(!isset($posters[$post['from']['id']]['textsize'])){
			$posters[$post['from']['id']]['textsize']	= $post_size;

		} else {
			$posters[$post['from']['id']]['textsize']	+= $post_size;
		}

		$textsize[$post['from']['id']] = $posters[$post['from']['id']]['textsize'];
		
    	$posters[$post['from']['id']]['id'] = $post['from']['id'];

		if(!isset($posters[$post['from']['id']]['number'])){
			$posters[$post['from']['id']]['number'] = 1;
		} else {	
			$posters[$post['from']['id']]['number']++;
		}
		
		$numbers[$post['from']['id']] = $posters[$post['from']['id']]['number'];
	}

	array_multisort($numbers, SORT_DESC, $textsize, SORT_DESC, $posters);
	
}

?>
    <?php if ($user): ?>

  <center><h2>Top users of <?php echo $source_name; ?></h2>
<table>
<?php
$i=1;
foreach($posters as $poster)
{
    echo "<tr><td><h2>$i</h2></td><td><img src='https://graph.facebook.com/$poster[id]/picture'></td><td>$poster[name]</td><td>made $poster[number] posts using $poster[textsize] Characters</td></tr>";
  $i++;
  if($i==11){break;}  
}
?>

</table>
  </center>
    <?php else: ?>
    <!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
<title>Top users of <?php echo $source_name; ?></title>
</head>
<body>

      <script language="javascript">
      document.location="<?php echo $loginUrl; ?>";
      </script>
      </body>
      </html>
    <?php endif ?>
