<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>我们大科二缓存服务</title>
    <style>
        .container {
            max-width: 780px;
            margin: auto;
        }
        header {
            height: 60px;
            border-bottom: 1px solid gray;
            margin-bottom: 30px;
            padding-bottom: 15px;
        }
    
    </style>
</head>

<body>

    <header>
        <div class="container">
            <p>我们大科二缓存服务</p>
            <small>此页面为广工新闻网的缓存页面，不代表网站的即时页面。本网站和广工新闻网该页面的作者无关，不对其内容负责。</small>
        </div>
    </header>
    <div class="article">
        <div class="container">

            <div class="docs">
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
                    echo $cacheData['content'];
                }
                else
                {
                    echo "无法找到此页面的缓存，点击查看原文以获取更多信息。";
                }
            }
            else
            {
                echo "ID错误";
            }
            ?>
            </div>
            <div class="footer">
                <a href="<?PHP echo "https://mail.bigkeer.cn/rss/jump.php?url=".urlencode("http://news.gdut.edu.cn/ViewArticle.aspx?articleid=$id"); ?>">查看原文</a>
            </div>

        </div>
    </div>

</body>
</html>
