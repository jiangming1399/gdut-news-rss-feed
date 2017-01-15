<?PHP
    if(isset($_COOKIE['JSESSIONID']))
        $cookie = $_COOKIE['JSESSIONID'];
    else $cookie = "";
    
    if(isset($_SERVER['HTTP_ORIGIN']))
        $http_origin = $_SERVER['HTTP_ORIGIN'];
    else $http_origin = "*";

    if(isset($_GET['type']))
        $type=$_GET['type'];
    else die('invalid params');
    
    switch($type) {
        case "captcha":
            header('Content-type: image/jpeg;charset=UTF-8');
            $result = curl_get("http://222.200.98.147/yzm?d=".time(), $cookie, true);
            echo $result[1];
            break;
            
        case "auth":
            header("Access-Control-Allow-Origin: $http_origin");
            header('Access-Control-Allow-Credentials:true');
            $result = curl_get("http://222.200.98.147/login!doLogin.action", $cookie, false ,
                        array('account'=> $_POST['username'],
                            'pwd'=> $_POST['password'],
                            'verifycode'=> $_POST['captcha'])
                        );
            echo $result[1];            
            break;
            
        case "getDate":
            header("Access-Control-Allow-Origin: $http_origin");
            header('Access-Control-Allow-Credentials:true');
            if(isset($_POST['xnxqdm'])) $xnxqdm=$_POST['xnxqdm'];
            else die('invalid params'); 
            if(isset($_POST['zc'])) $zc=$_POST['zc'];
            else die('invalid params');
            $result = curl_get('http://222.200.98.147/xsbjkbcx!getKbRq.action?xnxqdm='.$xnxqdm.'&zc='.$zc, $cookie, false);
            echo $result[1];
            break;

        case "getData":
            header("Access-Control-Allow-Origin: $http_origin");
            header('Access-Control-Allow-Credentials:true');
            $result = curl_get("http://222.200.98.147/xsgrkbcx!getDataList.action", $cookie, false, $_POST);
            echo $result[1];
            break;
        
        default:
            die("unknown type ".$type);
            break;
    }

    
function curl_get($url, $cookie,$isRaw=false, $postData=''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,$isRaw);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($ch, CURLOPT_HEADER, 1); 
    curl_setopt($ch, CURLOPT_COOKIE, 'JSESSIONID='.$cookie); 
    
    if (is_array($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    $content = curl_exec($ch);
    $curlInfo = curl_getinfo($ch);
    
    $headerSize =  $curlInfo['header_size'];
    $header = substr($content, 0, $headerSize);
    
    $body = substr($content, $headerSize);
    
    curl_close($ch);
    preg_match_all('/Set-Cookie: JSESSIONID=(.*); Path=/', $header, $results);
   
    if(count($results[0])>0){
        $cookie = $results[1][0];
        setcookie("JSESSIONID",$cookie);
    }
        
    return array($curlInfo,$body,$header);
}


?>