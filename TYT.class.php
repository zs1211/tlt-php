<?php

require  dirname(__FILE__) . '/AES.class.php';
set_time_limit(0);

/**
 * Created by
 * User: zhangsheng
 * Date: 2018/1/2
 * Time: 15:41
 */
class TYT {


    private $header;

    private $baseReq;

    private $times;

    private $host = 'https://mp.weixin.qq.com/wxagame/';

    private $gameData;

    private $score;

    private $sessionId;

    private $version;

    private $playTimeSeconds;


    public function __construct() {
        $config = require  dirname(__FILE__) . '/config.php';
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }




        if (empty($config['version'])) {
            throw new Exception('config version is nesscessary');
        }

        if (empty($config['playTimeSeconds'])) {
            $this->playTimeSeconds = 1;
        }

    }

    public function set($score,$sessionId) {


        if (empty($sessionId)) {
            throw new Exception('sessionId is nesscessary');
        }

        if (empty($score)) {
            throw new Exception('score is nesscessary');
        }
        $this->score = $score;
        $this->sessionId = $sessionId;
        //
        $this->init();
        $this->getUserInfo();
        sleep(1);
        $this->getFriendsScore();
        sleep(1);
        $this->initGame();
        sleep(floor($this->playTimeSeconds * $this->score));
        $this->createGameData();
        $info = $this->settleMent();
        if($this->getErrorCode($info)===0){
            return 'success ! score:' . $score;
        }
        return 'failed';
    }


    /**
     *初始化
     */
    public function init() {
        $this->baseReq = array(
            'base_req'=>array(
                'session_id' =>$this->sessionId,
                'fast'=> 1
            )
        );

        $header[] ='Accept: */*';
        $header[] ='Accept-Language: zh-cn';
        $header[] ='User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114 MicroMessenger/6.6.1 NetType/WIFI Language/zh_CN';
        $header[] ='Content-Type: application/json';
        $header[] ='Referer: https://servicewechat.com/wx7c8d593b2c3a7703/5/page-frame.html';
        $this->header = $header;
    }
    /**
     * 发送https post请求
     * @param $url  url地址
     * @param $header 请求头
     * @param $data  请求数据
     * @return mixed
     */
    public function postRequest($url, $header, $data) {
        $data = json_encode($data,320);
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLINFO_HEADER_OUT,0);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 获取用户信息
     * @return $this
     * @throws Exception
     */
    public function  getUserInfo() {
        $path  ='wxagame_getuserinfo';
        $url = $this->host . $path;
        $info = $this->postRequest($url, $this->header,$this->baseReq);
        if( !$info ) {
            throw  new Exception('getUserInfo failed');
        }

        return $this;
    }


    /**
     * 获取朋友们的分数
     * @return $this
     * @throws Exception
     */
    public function  getFriendsScore() {
        $path  ='wxagame_getfriendsscore';
        $url = $this->host . $path;
        $userInfo = $this->postRequest($url, $this->header,$this->baseReq);
        if( !$userInfo ) {
            throw  new Exception('getFriendsScore failed');
        }
        $userInfo = json_decode($userInfo,true);
        //取得数据中自己一共的游戏次数
        $this->times = $userInfo['my_user_info']['times'] + 1;
        return $this;
    }


    /**
     * 获取朋友们的份数
     * @return $this
     * @throws Exception
     */
    public function  initGame() {
        $path  ='wxagame_init';
        $url = $this->host . $path;
        $this->baseReq['version'] = $this->version;
        $info = $this->postRequest($url, $this->header, $this->baseReq);
        if( !$info ) {
            throw  new Exception('game init failed');
        }
        //取得数据中自己一共的游戏次数
        return $this;
    }

    /**
     * 获取随机的浮点数
     * @param $min
     * @param $max
     * @return int|string
     */
    public function randFloat ($min, $max) {
        $min = explode('.',$min);
        $max = explode('.',$max);
        $f = rand($min[1],$max[1]);
        return ($min[0] . '.' .$f ) +0 ;
    }


    /**
     *  上传成绩
     */
    public function settleMent () {
        $data = array(
            'score'=>$this->score,
            'times'=>$this->times,
            'game_data'=>$this->gameData
        );
        $key = substr($this->sessionId,0,16);
        $iv = substr($this->sessionId,0,16);
        $data = json_encode($data,320);
        $actionData = AES::encrypt($data,$key,$iv);
        $this->baseReq['action_data'] = $actionData;
        $path = 'wxagame_init';
        $url = $this->host . $path;
        $info = $this->postRequest($url, $this->header,$this->baseReq);
        return $info;
    }


    public function createGameData (){
        $action = [];
        $music = [];
        $touch = [];
        for ($i=0 ; $i<$this->score; $i++) {
            array_push($action,[$this->randFloat(0.752, 0.852), $this->randFloat(1.31,1.36), false]);
            array_push($music,false);
            array_push($touch,[rand(180, 190), rand(441, 456)]);
        }

        $s = time() . rand(100,999);
        $game_data = array(
            'seed'=> $s + 0,
            'action'=>$action,
            'musicList'=>$music,
            'touchList'=>$touch,
            'version'=> 1
        );
        $this->gameData = json_encode($game_data,320);
        return $this;
    }

    public function getErrorCode($jsonStr){
        $result = json_decode($jsonStr,true);
        return $result['base_resp']['errcode'];
    }
}


















