<?php

namespace App\Http\Controllers\Stream;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OBSPushStreamController extends BaseController
{
    private $channels = [];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('filter')->except([]);
        if (env('APP_NAME') == 'good') {

        } elseif (env('APP_NAME') == 'aikq') {
            $this->channels['zhibo1'] = 'meme-ali##40290555';
            $this->channels['zhibo2'] = 'meme-ali##40290666';
            $this->channels['zhibo3'] = 'meme-ali##40290777';
            $this->channels['zhibo4'] = 'meme-ali##40290888';
            $this->channels['zhibo5'] = 'meme-ali##40290999';

            $this->channels['zhibo6'] = 'maobo-ali##300222111';
            $this->channels['zhibo7'] = 'maobo-ali##300222222';
            $this->channels['zhibo8'] = 'maobo-ali##300222333';
            $this->channels['zhibo9'] = 'maobo-ali##300222444';
            $this->channels['zhibo10'] = 'maobo-ali##300222555';

            $this->channels['zhibo11'] = 'qt-ali##30022211';
            $this->channels['zhibo12'] = 'qt-ali##30022222';
            $this->channels['zhibo13'] = 'qt-ali##30022233';
            $this->channels['zhibo14'] = 'qt-ali##30022244';
            $this->channels['zhibo15'] = 'qt-ali##30022255';

            $this->channels['zhibo16'] = 'huoxing-ks##29589911';
            $this->channels['zhibo17'] = 'huoxing-ks##29589922';
            $this->channels['zhibo18'] = 'huoxing-ks##29589933';
            $this->channels['zhibo19'] = 'huoxing-ks##29589944';
            $this->channels['zhibo20'] = 'huoxing-ks##29589955';

        } elseif (env('APP_NAME') == 'aikq1') {

        } elseif (env('APP_NAME') == 'leqiuba') {

        }
    }

    public function index(Request $request, $name)
    {
        $channel = $this->channels[$name];
        list($type, $key) = explode('##', $channel);

        $params = [];
        switch ($type) {
            case 'meme-ali': {
                $params['push_rtmp'] = 'rtmp://video-center.alivecdn.com/memeyule';//流地址
                $params['push_key'] = $key . '?vhost=aliyun.memeyule.com';//流名称
                $params['live_flv'] = 'http://aliyun.memeyule.com/memeyule/' . $key . '.flv';//flv播放地址
                $params['live_m3u8'] = 'http://aliyun.memeyule.com/memeyule/' . $key . '.m3u8';//m3u8播放地址
                $params['live_rtmp'] = '-';//rtmp播放地址
                break;
            }
            case 'maobo-ali': {
                $params['push_rtmp'] = 'rtmp://push.maobotv.com/maozhua/';//流地址
                $params['push_key'] = $key;//流名称
                $params['live_flv'] = 'http://flv.maobotv.com/maozhua/' . $key . '.flv';//flv播放地址
                $params['live_m3u8'] = 'http://hls.maobotv.com/maozhua/' . $key . '/index.m3u8';//播放m3u8地址
                $params['live_rtmp'] = '-';//rtmp播放地址
                break;
            }
            case 'qt-ali': {
                $params['push_rtmp'] = 'rtmp://video-center.alivecdn.com/sei/';//流地址
                $params['push_key'] = 'stream' . $key . '?vhost=qt1.alivecdn.com';//流名称
                $params['live_flv'] = 'http://qt1.alivecdn.com/sei/stream' . $key . '.flv';//flv播放地址
                $params['live_m3u8'] = 'http://qt1.alivecdn.com/sei/stream' . $key . '.m3u8';//播放m3u8地址
                $params['live_rtmp'] = '-';//rtmp播放地址
                break;
            }
            case 'huoxing-ks': {
                $params['push_rtmp'] = 'rtmp://kspush01.live.changbalive.com/live';//流地址
                $params['push_key'] = $key;//流名称
                $params['live_flv'] = 'http://hdlkspull01.live.changbalive.com/live/' . $key . '.flv';//flv播放地址
                $params['live_m3u8'] = 'http://hkspull01.live.changbalive.com/live/' . $key . '/index.m3u8';//播放m3u8地址
                $params['live_rtmp'] = '-';//rtmp播放地址
                break;
            }
        }
        if (!empty($params['push_rtmp'])) {
            dump('#############################   推流   ################################');
            dump('URL: ' . $params['push_rtmp']);
            dump('流名称: ' . $params['push_key']);
            dump('#############################   播放   ################################');
            dump('m3u8: ' . $params['live_m3u8']);
            dump('flv: ' . $params['live_flv']);
            dump('rtmp: ' . $params['live_rtmp']);
        }
//        return view('manager.obs.stream', ['params' => $params]);
    }

    public function test()
    {
//        list($roomName, $roomId, $token) = explode('##', '老铁扣波666##10061563##3c4068b47d194772');
//        $rtmp_json = $this->getRtmp($token);
//        $fms_val = $rtmp_json['fms_val'];
//        $rtmp_id = array_first(array_keys($rtmp_json['list']));
//        $rtmp_url = array_first(array_values($rtmp_json['list']));
//        if ($this->startLive($token, $fms_val, $rtmp_id)) {//开播成功
//            $flvUrl = $this->getFlv($roomId);
//            $m3u8Url = $this->getM3u8($roomId);
//            dump($rtmp_url);
//            dump($flvUrl);
//            dump($m3u8Url);
//        }
    }
}
