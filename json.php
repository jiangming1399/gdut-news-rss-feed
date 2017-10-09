<?PHP 
header("Access-Control-Allow-Origin: *");
define("CACHE_TIME",600);
//define("BASEURL","http://222.200.98.32");
define("BASEURL","http://news.gdut.edu.cn");

//获取上一次通知的缓存
$configFile = fopen(sys_get_temp_dir()."/config.txt","r");

if(isset($_GET['count']))$count=$_GET['count'];
else $count = 5;

if(isset($_GET['flush']))$flush=true;
else $flush=false;

if($configFile){
    $lastCache = fgets($configFile);
    fclose($configFile);
}else{
    $lastCache = "";
}

//判断缓存是否有效
if( (time() - CACHE_TIME < $lastCache)&&(!$flush) )
{
    $cacheFile = fopen(sys_get_temp_dir()."/cache.txt","r");
    //echo '<description>Last update: '.date('r',$lastCache).'</description>';
    echo json_encode(Array("status" => 200, "data" => parseContent(fread($cacheFile,filesize(sys_get_temp_dir()."/cache.txt")),$count)));
    fclose($cacheFile);
}
else 
{
    //获取全部通知
    $getResult = curl_get(BASEURL."/ArticleList.aspx?category=4");

    //判断SESSION是否有效
    if(strripos($getResult[0]["url"], "UserLogin.aspx")>0){
        //正则匹配隐藏字段
        preg_match_all('<input type="hidden" name="([A-Z_]*)" ["a-zA-Z0-9_= ]* value="([a-zA-Z0-9/+=]*)" />', $getResult[1],$params);
        //构建POST参数
        $postArgs = array('ctl00$ContentPlaceHolder1$userEmail' => 'gdutnews', 'ctl00$ContentPlaceHolder1$userPassWord' => 'newsgdut', 'ctl00$ContentPlaceHolder1$CheckBox1' => 'on', 'ctl00$ContentPlaceHolder1$Button1' => '登录', $params[1][0] => $params[2][0], $params[1][1] => $params[2][1]);
        //重新获取SESSION
        $getResult = curl_get(BASEURL."/UserLogin.aspx",$postArgs);

        //重新获取页面内容
        $getResult = curl_get(BASEURL."/ArticleList.aspx?category=4");
    }
    $jsonData = Array();

    if($getResult[0]["http_code"] == 200)
    {
        $jsonData = Array("status" => 200, "data" => parseContent($getResult[1], $count));
    
        $lastCache = time();
        $configFile = fopen(sys_get_temp_dir()."/config.txt","w");
        fwrite($configFile, $lastCache);
        fclose($configFile);
        
        $cacheFile = fopen(sys_get_temp_dir()."/cache.txt","w");
        fwrite($cacheFile, $getResult[1]);
        fclose($cacheFile);
    }
    else 
    {
        $cacheFile = fopen(sys_get_temp_dir()."/cache.txt","r");
        $jsonData = Array("status" => -1, "msg" => '无法连接到校内新闻网，此为 '.date('m-d H:i',$lastCache).' 的数据', "data" => parseContent(fread($cacheFile,filesize(sys_get_temp_dir()."/cache.txt")),$count));
        fclose($cacheFile);
    }
    echo json_encode($jsonData);
}

function parseContent($content,$count){   
    $jsonOrg = Array();
    $i=0;  
    //正则表达式匹配
    preg_match_all('#<p[ a-z="]*>\s*<a href=".([/a-z?=0-9\.]*)"\s*title="(.*)">\s*.*\s*<span title="(.*)">.*<span>(.*)</span>#', $content, $out, PREG_SET_ORDER);
    foreach($out as $item)
    {
        $i++;
        
        $id = explode("=",$item[1])[1];
        $pageCacheFileName = sys_get_temp_dir()."/$id.txt";
        if(file_exists($pageCacheFileName))
        {
            $hCache = fopen($pageCacheFileName,"r");
            $cache = fgets($hCache);
            fclose($hCache);
            
            $cacheData = json_decode($cache, true);
            $publishDate = $cacheData['time'];
            $content = $cacheData['content'];
        }
        else
        {
            $getResult = curl_get(BASEURL."$item[1]");
            
            if($getResult[1] != null)
            {
                preg_match('#<div.*id="articleBody".*>([\s\S]*?)</div>\s*<div.*class="articleinfos".*>([\s\S]*?)</div>#', $getResult[1], $subItem);
                preg_match('#\[发布日期:(.*?)\]#', $subItem[2], $time);
                $publishDate = $time[1];
                $content = $subItem[1];
                $jsonData = array("time" => $publishDate, "content" => $content);
                
                $hCache = fopen($pageCacheFileName,"w");
                $cache = fwrite($hCache, json_encode($jsonData));
                fclose($hCache);
            }
        }
                    
        $jsonItem = Array('title' =>  $item[2],
                'url' => "https://mail.bigkeer.cn/rss/page.php?id=$id",
                'author' =>  $item[3],
                'date' => date_format(date_create($publishDate), "Y-m-d H:i:s"));
        $jsonOrg[] = $jsonItem;
        
        if($i >= $count) break;
    }
    return $jsonOrg;
}    

function curl_get($url, $postData=''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
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
