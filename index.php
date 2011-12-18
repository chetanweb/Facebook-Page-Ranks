<?php


require 'facebook.php';
include 'config.php';

$facebook = new Facebook(array(
  'appId'  => $appid,
  'secret' => $secret,
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

  include("class.XMLHttpRequest.php");
  include("sort.php");
   $req=new XMLHttpRequest();
   $req->open("GET","https://api.facebook.com/method/stream.get?access_token=$access_token&source_ids=$source_ids&format=json");
   $req->send();
   $responseText=$req->responseText;
   $req->close();
   $data=json_decode($responseText);
   $posters=array();
   
   foreach($data->posts as $post)
   {
    $actor=number_format($post->actor_id,0,'','');
    $post_size=strlen($post->message);
  
  
    $actor_data=json_decode(file_get_contents("http://graph.facebook.com/$actor?format=json"));
    $posters[$actor_data->name][textsize]+=$post_size;
    $posters[$actor_data->name][number]++;
    $posters[$actor_data->name][name]=$actor_data->name;
    $posters[$actor_data->name][id]=$actor_data->id;
    
    
   }
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
    echo "<tr><td><h2>$i</h2></td><td><img src='https://graph.facebook.com/$poster[id]/picture'></td><td>$poster[name]</td><td>$poster[name] made $poster[number] posts using $poster[textsize] Characters</td></tr>";
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
