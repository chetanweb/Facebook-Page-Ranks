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
		error_log($e);
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
  
	$stream = $facebook->api(
		"/$source_ids/feed?limit=50",
		'GET',
		array(
			'access_token' => $access_token
		)
	);
	
	$posters=array();

	foreach($stream['data'] as $post) {
	
		if(isset($post['message'])) {				
			$post_size=strlen($post['message']);
		}
		
		$posters[$post['from']['name']]['name']	= $post['from']['name'];
		
		if(!isset($posters[$post['from']['name']]['textsize'])){
			$posters[$post['from']['name']]['textsize']	= $post_size;			
		} else {
			$posters[$post['from']['name']]['textsize']	+= $post_size;
		}

    	$posters[$post['from']['name']]['id'] = $post['from']['id'];

		if(!isset($posters[$post['from']['name']]['number'])){
			$posters[$post['from']['name']]['number'] = 1;
		} else {	
			$posters[$post['from']['name']]['number']++;
		}
	}
	
   include("sort.php");
   $posters=array_sort($posters,'textsize',SORT_ASC);
   $posters=array_sort($posters,'number',SORT_DESC);

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
<title><?php echo $title; ?></title>
</head>
<body>

      <script language="javascript">
      document.location="<?php echo $loginUrl; ?>";
      </script>
      </body>
      </html>
    <?php endif ?>
