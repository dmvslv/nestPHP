<?php
namespace Nest;

Class Api {

    const betaUrl = 'https://developer-api.nest.com/';
    const mainUrl = 'https://api.nest.com/';

    const loginUrl = 'https://home.nest.com/login/oath2';
    const oauthUrl = 'https://api.home.nest.com/oauth2/access_token';
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

    public static function oauth($header = true)
    {
        self::init();
        $url = self::loginUrl . '?' . http_build_query(['client_id' => NEST_CLIEND_ID, 'status' => 'STATAE']);

        if ($header) {
            header('Location: "'.$url.'"');
        } else {
            echo '<html><META http-equiv="refresh" content="0;URL='.urlencode($url).'"></html>'
        }
        die();
    }

    public static function getAuh()
    {
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
        }

        return $access_token;
    }

    public function getAll()
    {

    }

    public function getStucture()
    {

    }

    public function getDevice()
    {

    }



}

?>