<?PHP
if(isset($_GET['id']))
{
    $id=$_GET['id'];
    $pageCacheFileName = sys_get_temp_dir()."/$id.txt";
    
    if(file_exists($pageCacheFileName))
    {
        $hCache = fopen($pageCacheFileName,"r");
        $cache = fgets($hCache);
        fclose($hCache);
        
        $cacheData = json_decode($cache, true);
    }
    else
    {
        header("Location: https://mail.bigkeer.cn/rss/jump.php?url=".urlencode("http://news.gdut.edu.cn/ViewArticle.aspx?articleid=$id"));
    }
}
else
{
    die("Invalid Params.");
}
?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>大科二缓存服务</title>
    <style>
        .container {
            max-width: 780px;
            width: 80%;
            margin: auto;
        }
        header {
            border-bottom: 1px solid gray;
            margin-bottom: 30px;
            padding-bottom: 15px;
        }
    </style>
</head>

<body>

    <header>
        <div class="container">
            <h3>大科二缓存服务</h3>
            <small style="color:gray;">此页面为广工新闻网的缓存页面，<a href="<?PHP echo "https://mail.bigkeer.cn/rss/jump.php?url=".urlencode("http://news.gdut.edu.cn/ViewArticle.aspx?articleid=$id"); ?>">点击此处</a>查看最新页面。本网站与此页面的作者无关，不对其内容负责。</small>
        </div>
    </header>
    <div class="article">
        <div class="container">
            <div class="docs">
                <?PHP echo $cacheData['content']; ?>
            </div>
        </div>
    </div>

</body>
</html>
