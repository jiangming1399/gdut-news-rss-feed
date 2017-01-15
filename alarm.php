<?PHP
    header("Access-Control-Allow-Origin: *");
    if(isset($_GET['areaid']))
        $areaid=$_GET['areaid'];
    else die('invalid params');
    $url = 'http://product.weather.com.cn/alarm/stationalarm.php?count=-1&areaid='.$areaid;

    echo curl_get($url);
    
function curl_get($url,$isRaw=false, $postData=''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,$isRaw);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    
    if (is_array($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    $content = curl_exec($ch);
    
    curl_close($ch);
        
    return $content;
}


?>