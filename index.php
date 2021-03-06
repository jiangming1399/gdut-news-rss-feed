<?PHP header("Content-type: text/xml; charset=utf-8"); ?>
<?PHP
echo '<?xml version="1.0" encoding="utf-8" ?>';
?>
<rss version="2.0">
    <channel>
        <title>校内通知</title>
        <link>https://www.bigkeer.cn</link>
        <language>zh-cn</language>
        <generator><![CDATA[]]></generator>
        <webmaster>admin@bigkeer.cn</webmaster>
<?PHP
    define("CACHE_TIME",600);
    //获取上一次通知的缓存
    $configFile = fopen(sys_get_temp_dir()."/config.txt","r");
    
    if(isset($_GET['flush']))$flush=true;
    else $flush=false;
    
    if($configFile){
        $lastCache = fgets($configFile);
        fclose($configFile);
    }else{
        $lastCache = "";
    }
    
    //判断缓存是否有效
    if( (time() - CACHE_TIME < $lastCache)&&(!$flush) ){
        $cacheFile = fopen(sys_get_temp_dir()."/cache.txt","r");
        echo '<description>Last update: '.date('r',$lastCache).'</description>';
        printRss(fread($cacheFile,filesize(sys_get_temp_dir()."/cache.txt")),$flush);
        fclose($cacheFile);
    }
    else {
        //获取全部通知
        $getResult = curl_get("http://news.gdut.edu.cn/ArticleList.aspx?category=4");

        //判断SESSION是否有效
        if(strripos($getResult[0]["url"], "UserLogin.aspx")>0){
            //正则匹配隐藏字段
            preg_match_all('<input type="hidden" name="([A-Z_]*)" ["a-zA-Z0-9_= ]* value="([a-zA-Z0-9/+=]*)" />', $getResult[1],$params);
            //构建POST参数
            $postArgs = array('ctl00$ContentPlaceHolder1$userEmail' => 'gdutnews', 'ctl00$ContentPlaceHolder1$userPassWord' => 'newsgdut', 'ctl00$ContentPlaceHolder1$CheckBox1' => 'on', 'ctl00$ContentPlaceHolder1$Button1' => '登录', $params[1][0] => $params[2][0], $params[1][1] => $params[2][1]);
            //重新获取SESSION
            $getResult = curl_get("http://news.gdut.edu.cn/UserLogin.aspx",$postArgs);

            //重新获取页面内容
            $getResult = curl_get("http://news.gdut.edu.cn/ArticleList.aspx?category=4");
        }
        echo '<description>Last update: '.date('r',time()).'</description>';
        printRss($getResult[1],$flush);
        
        $lastCache = time();
        $configFile = fopen(sys_get_temp_dir()."/config.txt","w");
        fwrite($configFile, $lastCache);
        fclose($configFile);
        
        $cacheFile = fopen(sys_get_temp_dir()."/cache.txt","w");
        fwrite($cacheFile, $getResult[1]);
        fclose($cacheFile);
    } 
    
function printRss($content,$forceReflush){
    //正则表达式匹配
    preg_match_all('#<p[ a-z="]*>\s*<a href=".([/a-z?=0-9\.]*)"\s*title="(.*)">\s*.*\s*<span title="(.*)">.*<span>(.*)</span>#', $content, $out, PREG_SET_ORDER);
    foreach($out as $item){
        $id = explode("=",$item[1])[1];
        $pageCacheFileName = sys_get_temp_dir()."/$id.txt";
        if(file_exists($pageCacheFileName) && !$forceReflush)
        {
            $hCache = fopen($pageCacheFileName,"r");
            $cache = fgets($hCache);
            fclose($hCache);
            
            $cacheData = json_decode($cache, true);
            $publishDate = $cacheData['time'];
            $content = $cacheData['content'];
            
            PrintOutRssItem($item[2],"http://news.gdut.edu.cn$item[1]",$content,date_create($publishDate),$item[3]);
        }
        else
        {
            $getResult = curl_get("http://news.gdut.edu.cn$item[1]");
            preg_match('#<div.*id="articleBody".*>([\s\S]*?)</div>\s*<div.*class="articleinfos".*>([\s\S]*?)</div>#', $getResult[1], $subItem);
            preg_match('#\[发布日期:(.*?)\]#', $subItem[2], $time);
            $publishDate = $time[1];
            $content = $subItem[1];
            $jsonData = array("time" => $publishDate, "content" => $content);
            
            $hCache = fopen($pageCacheFileName,"w");
            $cache = fwrite($hCache, json_encode($jsonData));
            fclose($hCache);
            
            PrintOutRssItem($item[2],"http://news.gdut.edu.cn$item[1]",$content,date_create($publishDate),$item[3]);
            
        }
    }
}    

function PrintOutRssItem($title, $url, $desc, $pubDate, $author)
{
    echo '<item>';
    echo "    <title><![CDATA[$title]]></title>";
    echo "    <link>http://mail.bigkeer.cn/rss/jump.php?url=".urlencode($url)."</link>";
    echo "    <description><![CDATA[$desc]]></description>";
    echo '    <pubDate>'.date_format($pubDate, "Y-m-d H:i:s").'</pubDate>';
    echo "    <category>校内通知</category>";
    echo "    <author>$author</author>";
    echo "    <comments>我们大科二</comments>";
    echo "</item>";
}

function curl_get($url, $postData=''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    if (is_array($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    $content = curl_exec($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);
    return array($header,$content);
}


?>
    </channel>
</rss>