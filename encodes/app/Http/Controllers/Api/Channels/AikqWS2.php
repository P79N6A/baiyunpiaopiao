<?php

namespace App\Http\Controllers\Api\Channels;

use App\Http\Controllers\Api\Channel;

class AikqWS2 extends Channel
{
    public $id = 996;//平台ID
    public $name = '网宿(平均)';//平台名称
    public $level = 1;//平台级别，1:野鸡，2:一般，3:大平台
    public $expiration = -1;//过期时间，单位秒，-1表示不过期

    private $push_host = "";
    private $hls_host = "";
    private $hdl_host = "";
    private $ws_key = "";

    private $streamURL = '';
    private $streamKey = '';

    private $playFlv = '';
    private $playRTMP = '';
    private $playM3U8 = '';

    private $playFlvSD = '';
    private $playRTMPSD = '';
    private $playM3U8SD = '';

    public function __construct($uid = 0)
    {
        $this->expiration = time() + 21600;
        $this->push_host = env('WS2_PUSH_HOST', '');
        $this->hls_host = env('WS2_HLS_HOST', '');
        $this->hdl_host = env('WS2_HDL_HOST', '');
        $this->ws_key = env('WS2_PUSH_KEY', '');
        if ($uid == 0) {
            $key = 'stream-' . $uid . '-' . time() . '-' . random_int(111111, 999999);
        } else {
            $key = 'stream-' . $uid;
        }
        $timestamp = $this->expiration;

        $sstring = $this->ws_key . '/live/' . $key . "$timestamp";
        $auth_key = md5($sstring);
        $this->streamURL = "rtmp://$this->push_host/live";//流地址
        $this->streamKey = $key . '?wsSecret=' . $auth_key . '&wsABSTime=' . $timestamp;//流名称

        $sstring = $this->ws_key . '/live/' . $key . ".flv$timestamp";
        $auth_key = md5($sstring);
        $this->playFlv = 'https://' . $this->hdl_host . '/live/' . $key . '.flv?wsSecret=' . $auth_key . '&wsABSTime=' . $timestamp;//flv播放地址

        $sstring = $this->ws_key . '/live/' . $key . "-sd.flv$timestamp";
        $auth_key = md5($sstring);
        $this->playFlvSD = 'https://' . $this->hdl_host . '/live/' . $key . '-sd.flv?wsSecret=' . $auth_key . '&wsABSTime=' . $timestamp;//flv播放地址

        $sstring = $this->ws_key . '/live/' . $key . "/playlist.m3u8$timestamp";
        $auth_key = md5($sstring);
        $this->playM3U8 = 'https://' . $this->hls_host . '/live/' . $key . '/playlist.m3u8?wsSecret=' . $auth_key . '&wsABSTime=' . $timestamp;//播放m3u8地址

        $sstring = $this->ws_key . '/live/' . $key . "-sd/playlist.m3u8$timestamp";
        $auth_key = md5($sstring);
        $this->playM3U8SD = 'https://' . $this->hls_host . '/live/' . $key . '-sd/playlist.m3u8?wsSecret=' . $auth_key . '&wsABSTime=' . $timestamp;//播放m3u8地址
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
        return $this->playFlvSD;
    }

    public function playFLVHD()
    {
        return $this->playFlv;
    }

    public function playRTMP()
    {
        return $this->playRTMPSD;
    }

    public function playRTMPHD()
    {
        return $this->playRTMP;
    }

    public function playM3U8()
    {
        return $this->playM3U8SD;
    }

    public function playM3U8HD(){
        return $this->playM3U8;
    }
}