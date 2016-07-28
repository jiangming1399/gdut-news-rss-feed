<?PHP
if (array_key_exists('url',$_GET))$url = urldecode($_GET["url"]);
else $url = 'https://www.bigkeer.cn';

echo '<meta http-equiv="refresh" content="0; URL='.$url.'">';
?>