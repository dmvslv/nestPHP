<?php
namespace Nest;

Class Api {

    const betaUrl = 'https://developer-api.nest.com/';
    const mainUrl = 'https://api.nest.com/';

    const loginUrl = 'https://home.nest.com/login/oath2';
    const oauthUrl = 'https://api.home.nest.com/oauth2/access_token';

    private $_url = '';
    private $_main = false;


    /**
     * check client_Id & client_Secret
     * @return [type] [description]
     */
    public static function init()
    {
        if(!defined('NEST_CLIEND_ID') || !defined('NEST_CLIEND_SECRET') ) {
            throw new Exception("set client_id & secret", 1);
        }
    }

    /**
     *
     * @param integer $site  [description]
     * @param boolean $debug [description]
     */
    public function __construct($site = 0, $debug = false)
    {
        $this->_url  = $site? self::mainUrl : self::betaUrl;
        $this->_main = $site? true : false;

        self::init();
    }

    /**
     * [SetAuth description]
     * @param [type] $access_token [description]
     */
    public function setAuth($access_token)
    {
        $this->access_token = $access_token;
        return $this;
    }

    /**
     * [oauth description]
     * @param  boolean $header [description]
     * @return [type]          [description]
     */
    public static function oauth($header = true)
    {
        self::init();
        $url = self::loginUrl . '?' . http_build_query(['client_id' => NEST_CLIEND_ID, 'status' => 'STATAE']);

        if ($header) {
            header('Location: "'.$url.'"');
        } else {
            echo '<html><META http-equiv="refresh" content="0;URL='.urlencode($url).'"></html>';
        }
        die();
    }

    /**
     * [getAuh description]
     *
     * @return string access_token
     */
    public static function getAccessCode()
    {
        self::init();
        $code = $_GET['code'];
        if(empty($code)) {
            throw new Exception("Error Processing Request", 1);
        }

        $url = self::oauthUrl;
        $param['client_id']     = NEST_CLIEND_ID;
        $param['client_secret'] = NEST_CLIEND_SECRET;
        $param['code']          = $code;
        $param['grant_type']    = 'authorization_code';

        $url .= '?' . http_build_query($param);

        try{
            $http = new GuzzleHttp\Client();
            $res = $http->post($url, [], ['timeout' => 2, 'connect_timeout' => 2]);
            $data = $res->JSON();
            $access_token = $data->access_token;
        } catch (Exception $e) {
            restore_exception_handler();
            return false;
        }

        return $access_token;
    }

    /**
     * httpClient
     *
     * @param  [type]  $url    [description]
     * @param  boolean $steam  [description]
     * @param  string  $method [description]
     * @param  string  $body   [description]
     * @return [type]          [description]
     */
    private function httpClient($url, $steam = false, $method = 'get', $body = false)
    {

        $http = new \GuzzleHttp\Client();
        try{

            $header = ['Accept' => ($steam? 'text/event-stream' : 'application/json') ];
            $option = ['headers' => $header, 'verify' => $this->_main, 'stream' => $steam];

            if($body) {
                $option['body'] = $body;
            }

            $url = $this->_url . $url . '?auth='. $this->access_token;

            $res = $http->$method($url, $option, []);

        }  catch (Exception $e) {
            echo $url;
            restore_exception_handler();
            die();
            //throw new Exception("$url error\n", 1);
        }

        return $res;
    }

    /**
     * [getAll description]
     * @return [type] [description]
     */
    public function getAll()
    {
        $res = $this->httpClient();
        return $res->JSON();
    }

    /**
     * [getStucture description]
     * @return [type] [description]
     */
    public function getStuctureOnce()
    {
        $res = $this->httpClient('structure/');
        return $res->JSON();
    }

    /**
     * [getDevice description]
     * @return [type] [description]
     */
    public function getDeviceOnce()
    {
        $res = $this->httpClient('device/');
        return $res->JSON();
    }

    /**
     * [listener description]
     * @param  string $func  func;
     * @return [type] [description]
     */
    public function listener($func = '')
    {

        $base = false;
        try{
            $respond = $this->httpClient('devices/', true);
            // $body = $respond->getBody();
        } catch (Exception $e) {
            echo 'aaa';
            restore_exception_handler();
        }


        $str = '';
        $index = 1;
        while (!$respond->feof()) {
            echo "\n$index \n";
            $str .= $respond->readLine();

            if(strpos($str, 'event: put') !== false) {
                $cmd = substr($str, strpos($str, 'data:') + 5);
                $str = "";

                $data = json_decode($cmd, true);
                $change = [];

                if(!$base) {
                    $change['devices']   = $data['data']['devices'];
                    $change['structures'] = $data['data']['structures'];
                } else {
                    // diff;
                    $tmp = [];
                    $tmp['devices']   = $data['data']['devices'];
                    $tmp['structures'] = $data['data']['structuress'];

                    $change['devices']   = array_diff($change['devices'], $tmp['devices']);
                    $change['structures'] = array_diff($change['structures'], $tmp['structures']);
                }

                if(is_callable($func)) {
                    echo "run func";
                    call_user_func_array($func, [$data, $change, $index]);
                }

                $index++;
            } else {
                echo $str;
                continue;
            }
        }

    }

    /**
     * [set description]
     * @param [type] $path [description]
     * @param [type] $var  [description]
     */
    public function set($path, $var)
    {
        $respond = $this->httpClient($path, false, 'put', $var);
        return $respond->getHeader();
    }


}

?>