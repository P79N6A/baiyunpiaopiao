<?php

namespace App\Http\Controllers\Api\Channels;

use App\Http\Controllers\Api\Channel;

class Xiaomi extends Channel
{
    public $id = 311;//平台ID
    public $name = 'mi.com';//平台名称
    public $level = 3;//平台级别，1:野鸡，2:一般，3:大平台
    public $expiration = -1;//过期时间，单位秒，-1表示不过期

    private $streamURL;
    private $streamKey;

    private $playFlv;
    private $playRTMP;
    private $playM3U8;

    public function __construct($uid = 0)
    {
        $key = "cid".date('YmdHi').(random_int(pow(10, 8), pow(10, 9) - 1));
//        $this->streamURL = 'rtmp://r2.ks.zb.mi.com/live';
//        $this->streamKey = $key;
//        $this->playFlv = 'http://v2.ks.zb.mi.com/live/' . $key . '.flv';
//        $this->playM3U8 = 'http://hls.ksy.zb.mi.com/live/' . $key . '/index.m3u8';
//
         $this->streamURL = 'rtmp://r2.pandora.zb.mi.com/live';
        $this->streamKey = $key;
        $this->playFlv = 'http://v2.pandora.zb.mi.com/live/' . $key . '.flv';
        $this->playM3U8 = 'http://hls.pandora.zb.mi.com/live/' . $key . '/playlist.m3u8';
    }

    public function pushURL()
    {
        return $this->streamURL;
    }

    public function pushKey()
    {
        return $this->streamKey;
    }

    public function playFLV()
    {
        return $this->playFlv;
    }

    public function playRTMP()
    {
        return $this->playRTMP;
    }

    public function playM3U8()
    {
        return $this->playM3U8;
    }
}