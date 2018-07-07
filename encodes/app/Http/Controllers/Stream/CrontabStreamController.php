<?php

namespace App\Http\Controllers\Stream;

use Illuminate\Routing\Controller as BaseController;
use App\Models\PushChannle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CrontabStreamController extends BaseController
{

    public function __construct()
    {

    }

    public function get9158Rooms()
    {
        $page = 1;
        $totalPage = 1;
        $rooms = [];
        do {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://live.9158.com/Room/GetHotLive_v2?isNewapp=1&page=$page&type=1");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36");
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            $response = curl_exec($ch);
            if ($error = curl_error($ch)) {
                die($error);
            }
            curl_close($ch);
//        dump($response);
            $json = json_decode($response, true);
//        dump($json);
            if (!empty($json['data']) && !empty($json['data']['list'])) {
                $totalPage = $json['data']['totalPage'];
                $rooms = array_merge($rooms, $json['data']['list']);
            }
            $page++;
        } while ($page < $totalPage);
//        dump($rooms);
        foreach ($rooms as $room) {
//            dump($room);
            $flv = $room['flv'];
            $roomid = $room['roomid'];
            $push_rtmp = str_replace('http://hdl', 'rtmp://push', $flv);
            $push_rtmp = str_replace('.flv', '', $push_rtmp);
            $m3u8 = str_replace('http://hdl', 'http://hls', $flv);
            $m3u8 = str_replace('.flv', '/playlist.m3u8', $m3u8);
            $pc = PushChannle::query()->where(['platform' => '9158', 'channel' => $roomid])->first();
            if (empty($pc)) {
                $pc = new PushChannle();
                $pc->channel = $roomid;
                $pc->platform = '9158';
            }
            $pc->name = $room['useridx'];
            $pc->push_rtmp = $push_rtmp;
            $pc->live_lines = $flv . "\n" . $m3u8;
            $pc->status = -1;
            $pc->updated_at = date_create();
            $pc->save();
        }
    }

    public function test9158Rooms()
    {
        $pcs = PushChannle::query()
            ->where(['platform' => '9158'])
            ->where('status', -1)
            ->orderBy('updated_at', 'asc')
            ->take(20)
            ->get();
        foreach ($pcs as $pc) {
            list($flv, $m3u8) = explode("\n", $pc->live_lines);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $flv);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // connect timeout
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // curl timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // curl timeout

            $status = 0;
            if (FALSE === curl_exec($ch)) {
                dump('open ' . $flv . ' failed' . "\n");
            } else {
                $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                dump('HTTP return code=' . $retcode . "\n");
                if ($retcode == 200) {
                    $status = -1;
                }
            }
            curl_close($ch);
            $pc->status = $status;
            $pc->updated_at = date_create();
            $pc->save();
        }
    }

    public function getChushouRooms()
    {
        $tokens = [
            '5b223be8f779744eg51e1174e',
            '212df6b646f2b7c6g52077dc6',
            'fe09f076ba486f02g520695fe',
            '74789c318d0cf918g52067018',
//            '',
//            '',
//            '',
//            '',
        ];
        foreach ($tokens as $token) {
            $pushUrl = '';
            $count = 0;
            $m3u8 = '';
            do {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.chushou.tv/api/live-room/get-rookie-push-url.htm?token=$token");
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36");
                curl_setopt($ch, CURLOPT_COOKIESESSION, true);
                $response = curl_exec($ch);
                if ($error = curl_error($ch)) {
                    die($error);
                }
                curl_close($ch);
//        dump($response);
                $json = json_decode($response, true);
//        dump($json);
                if (isset($json['data']['pushUrl']) && str_contains($json['data']['pushUrl'], 'up6.kascend.com')) {
                    $pushUrl = $json['data']['pushUrl'];
                    $m3u8 = explode('?', $pushUrl)[0];
                    $m3u8 = str_replace('rtmp://up6', 'http://hls6', $m3u8);
                    $m3u8 = $m3u8 . '.m3u8';
                    dump($pushUrl);
                }
                $count++;
            } while (empty($pushUrl) && $count < 10);

            if (!empty($pushUrl)) {
                $pc = PushChannle::query()->where(['platform' => 'chushou', 'channel' => $token])->first();
                if (empty($pc)) {
                    $pc = new PushChannle();
                    $pc->channel = $token;
                    $pc->platform = 'chushou';
                    $pc->name = $token;
                    $pc->status = 0;
                }
                $pc->push_rtmp = $pushUrl;
                $pc->live_lines = $m3u8;
                $pc->updated_at = date_create();
                $pc->save();
            }
        }
    }

    public function getChangbaRooms()
    {
        $rooms = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.changbalive.com/api_room.php?ac=gethotrank&curuserid=3310830&channelsrc=appstore&version=2.1.0&token=T286eb57e154dffd&bless=0&macaddress=4044A747-5BF0-4465-A894-99E2FEBAC4C1&ismember=0&openudid=69a214bdb8e3628de54a8ac70a773a87943377ce&systemversion=11.4&device=iPhone8,1&broken=0&build=2.1.0.1&gender=2&secret=9b1d35c3ae");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36");
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_close($ch);
//        dump($response);
        $json = json_decode($response, true);
//        dump($json);
        if (!empty($json['result']) && !empty($json['result']['sessioninfos'])) {
            $rooms = $json['result']['sessioninfos'];
        }
//        dump($rooms);
        foreach ($rooms as $room) {
//            dump($room);
            $roomid = $room['anchorid'];
            $flv = explode('?', $room['rtmp_url']['subscribe_url'])[0];
            $push_rtmp = $room['rtmp_url']['publish_url'];
            if (str_contains($push_rtmp, 'rtmp://wspush')) {
                $m3u8 = str_replace('http://wspull', 'http://hwspull', $flv);
                $m3u8 = str_replace('.flv', '/playlist.m3u8', $m3u8);
                $pc = PushChannle::query()->where(['platform' => 'changba', 'channel' => $roomid])->first();
                if (empty($pc)) {
                    $pc = new PushChannle();
                    $pc->channel = $roomid;
                    $pc->platform = 'changba';
                }
                $pc->name = $roomid;
                $pc->push_rtmp = $push_rtmp;
                $pc->live_lines = $flv . "\n" . $m3u8;
                $pc->status = -1;
//                $pc->updated_at = date_create();
                $pc->save();

            }
        }
    }

    public function testChangbaRooms()
    {
        $pcs = PushChannle::query()
            ->where(['platform' => 'changba'])
            ->where('status', -1)
            ->orderBy('updated_at', 'asc')
            ->take(20)
            ->get();
        foreach ($pcs as $pc) {
            list($flv, $m3u8) = explode("\n", $pc->live_lines);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $flv);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // connect timeout
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // curl timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // curl timeout

            $status = 0;
            if (FALSE === curl_exec($ch)) {
                dump('open ' . $flv . ' failed' . "\n");
            } else {
                $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                dump('HTTP return code=' . $retcode . "\n");
                if ($retcode == 200) {
                    $status = -1;
                }
            }
            curl_close($ch);
            $pc->status = $status;
            $pc->updated_at = date_create();
            $pc->save();
        }
    }

    public function hotsoon()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://hotsoon.snssdk.com/hotsoon/room/?app_name=live_stream");
        curl_setopt($ch, CURLOPT_COOKIE, "sid_tt=2f05186a3234d4da3be36d2589e19136;");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "cover_uri=&title=");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_USERAGENT, "LiveStreaming/4.1.3 (iPhone; iOS 11.4; Scale/2.00)");
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
//        curl_exec($ch);
        curl_close($ch);
//        dump($response);
        $json = json_decode($response, true);
//        dump($json);
        if (!empty($json['data']) && !empty($json['data']['stream_url']['rtmp_push_url'])) {
            $rtmp_push_url = $json['data']['stream_url']['rtmp_push_url'];
            $rtmp_pull_url = $json['data']['stream_url']['rtmp_pull_url'];
            $urls = explode('/', $rtmp_push_url);
            $stream_name = array_pop($urls);
            $stream_url = join('/', $urls);
            dump('URL：' . $stream_url);
            dump('流名称：' . $stream_name);
            dump('==================================================================');
            dump('PC播放地址：' . $rtmp_pull_url);
            $m3u8 = str_replace('flv-l6', 'hls-l6', $rtmp_pull_url);
            $m3u8 = str_replace('.flv', '/index.m3u8', $m3u8);
            $m3u8 = str_replace('flv-l1', 'hls-l1', $m3u8);
            $m3u8 = str_replace('.flv', '/playlist.m3u8', $m3u8);
            dump('M3U8播放地址：' . $m3u8);
        }
    }

    public function qianfan()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://mbl.56.com/play/v2/applyShow.ios?roomId=592203354");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_USERAGENT, "zhibo/5.8.1 (iPhone; iOS 11.4; Scale/2.00)");
        curl_setopt($ch, CURLOPT_COOKIE, "member_id=shunm_56109343822%4056.com; pass_hex=00475fd070d99d888bd362bc271563a123148be3; qfInfo=%7B%22typePatriarch%22%3A%22%22%2C%22qfLogin%22%3A1%7D");
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $response = curl_exec($ch);
        dump($response);
//        curl_setopt($ch, CURLOPT_URL, "https://mbl.56.com/play/v1/stopShow.ios?roomId=592044434");
        curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_close($ch);
        $json = json_decode($response, true);
        if (isset($json['message']['pushUrl'])) {
            //https://hls-v-ngb.qf.56.com/live/592044434_1528710076092/playlist.m3u8
            //https://v-ngb.qf.56.com/live/592044434_1528710076092.flv
            $rtmp_push_url = $json['message']['pushUrl'];
            $urls = explode('/', $rtmp_push_url);
            $stream_name = array_pop($urls);
            $stream_url = join('/', $urls);
            dump('URL：' . $stream_url);
            dump('流名称：' . $stream_name);
            dump('==========================================================================');
            $m3u8Url = str_replace('rtmp://up-ngb', 'https://hls-v-ngb', explode('?', $rtmp_push_url)[0]) . '/playlist.m3u8';
            dump('M3U8播放地址：' . $m3u8Url);
            $flvUrl = str_replace('rtmp://up-ngb', 'https://v-ngb', explode('?', $rtmp_push_url)[0]) . '.flv';
            dump('PC播放地址：' . $flvUrl);
//            return $json['message']['pushUrl'];
        } else {
            return null;
        }
    }

    public function weibo()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ing.weibo.com/api/protoweb/getcreateparams");
        curl_setopt($ch, CURLOPT_COOKIE, "LIVE-G0=5353d40b740d677d1f8edf8408955b65; WBStorage=201807040031|undefined; login_sid_t=f317719f69ba49ae46c45b6baa998c87; cross_origin_proto=SSL; _s_tentry=-; Apache=121035831212.49706.1530635556751; SINAGLOBAL=121035831212.49706.1530635556751; ULV=1530635556761:1:1:1:121035831212.49706.1530635556751:; SCF=AmKW-d8Zq4uyOZp1wsNOR7nge-alqfJygwBg6ckboxkVJ1W3Os8dsA08OOpFSyLDDtVxPI2E6CoSjbuoNiRwZVk.; SUB=_2A252P9VpDeRhGedH61UZ8CzNwjmIHXVVTUGhrDV8PUNbmtAKLXT6kW9NUPbxf1krouhfKDQOAkDPvTvsJbw81rOh; SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WWmajPEkfJqO7g_ws.3yQ685JpX5KzhUgL.Fo24ehMRehzp1K-2dJLoIpzLxKqL122L122LxK-L1-zL1-zt; SUHB=079ilJbrM8Iobh; ALF=1562171577; SSOLoginState=1530635578; wvr=6");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "is_premium=0");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ing.weibo.com/p/proto/admin?page=create');
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36");
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
//        curl_exec($ch);
        curl_close($ch);
//        dump($response);
        $json = json_decode($response, true);
//        dump($json);
        if (!empty($json['data']) && !empty($json['data']['push_url'])) {
            $rtmp_push_url = $json['data']['push_url'];
            $flv_pull_url = $json['data']['live_flv_hd'];
//            $rtmp_pull_hd = $json['data']['rtmp_hd'];
            $hls_pull_url = $json['data']['live_hd'];
            $urls = explode('/', $rtmp_push_url);
            $stream_name = array_pop($urls);
            $stream_url = join('/', $urls);
            dump('URL：' . $stream_url);
            dump('流名称：' . $stream_name);
            dump('==================================================================');
            dump('PC播放地址：' . $flv_pull_url);
            dump('M3U8播放地址：' . $hls_pull_url);
        }
    }

    public function inke()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://openact.busi.inke.cn/stream/get_pull_stream");
        curl_setopt($ch, CURLOPT_COOKIE, "Hm_lvt_11f833fcd634f41462d9c452077f1776=1530763998; INKEUSERINFO=%7B%22name%22%3A%22NBA%5Cu89e3%5Cu8bf4%22%2C%22pic%22%3A%22http%3A%5C%2F%5C%2Fimage.scale.inke.cn%5C%2Fimageproxy2%5C%2Fdimgm%5C%2FscaleImage%3Furl%3Dhttp%253A%252F%252Fimg2.inke.cn%252FMTUxNTY0OTg0ODQ2MSM0NDAjanBn.jpg%26w%3D100%26h%3D100%26s%3D80%26c%3D0%26o%3D0%22%2C%22view_id%22%3A104099414%2C%22level%22%3A1%2C%22gender%22%3A1%7D; INKEUSERLOGININFO=f7fba40b1ddb13feca4246910d541881bb8989c5d9d4f84b3ff01cb177b0fc9fdf4e929a2f8a26cbec288b389bcad74c2c05cd7c6c4d9c2f2740d4cb1e0e63becaaecd185e7ffd1655bec160a4ed2a481441e27a5f33e34b5de0b2058d63a7838b84253f7de9cd2cc00210e43001b5704a6d6d24676382b267eb27f095f9f01f4a99ff6d86d31b7635a168efbaca6abc892aba5bd7828be03805e2f5935597610a5a18f850d4e2408506bf2928f8cface14a655c2b91d9218ce7b6dcd2294483ea5605ef66e983ae902ad232c14e30d2eef77533faab278924872a7f7e1f9f9da389defa7fa790a46c35683cbcd3348dbc6fcb215401a1502e170d95bab7719ca0f3ec439d93e5e1e82bac6b03443e711ff6240f161dbc3a8a0358ca1928cd5b41ca1428ab1abbf45b20fa3ed80dfb610567ed26ec90e1ce278991b8acf57e6cf9431fdbad611be43fa96321138eee698b60cb3938e9b8c4c434769c9c263838adbacfcdaa853c4ad65aef72979674a8; INKEAPISESSION=a6h13CVqKrbuXGSkmFlmiGaN%2B2SJuVYqgNIPy3kxwdNt%2FF3yJxeNbU6phYss9E4jI%2FLaK9HOp8Oo75SMOziZqwIThCBz5xIwWc1plVOw8Di%2B04vbpJi9GG0DzSxii15wlvThlSSfXBrFVbKTFbNZnrNuymfF5%2Fl0Y62xRtLGaaZFjP%2Bl3PrBhiQOcbv%2F5R1B%2FHWggqg2pkyZsI%2F4y7qfO298VoEQVVx%2BmRJ1qRHTBdiHzwlXKGPvPowxtATkrLQ4ezSLMWniohcpGc82DFHCjs1oAYqD3miOYHgFDPWKtm8saKkWvvuwfhoNGXX%2FZqtDpaZagC7KR1zZvULQpFj6j64K%2FaEddDXXQwvlB37LMMntPsJmuFlbVc0VaR2MBQGZYEN7F275MZP%2FV4CfO2lHZIpazjiqFdBfgSI4iS9snn%2B51lcsYTTb2tUyr%2B8fR4%2B8qYLLAYIsV6R2fOdpX2lBL%2BIDmMopvh9iD7KXHZ%2BCZBs%3D; PHPSESSID=f3572526f955a498cecef6218d35c792; Hm_lpvt_11f833fcd634f41462d9c452077f1776=1530764373");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "id=0&screen_orientation=portrait&name=&gps_position=");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.inke.cn/live_flow.html');
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36");
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_setopt($ch, CURLOPT_URL, "http://openact.busi.inke.cn/stream/live_stop");
        curl_exec($ch);
        curl_close($ch);
//        dump($response);
        $json = json_decode($response, true);
//        dump($json);
        if (!empty($json['data']) && !empty($json['data']['pull_stream_url'])) {
            $rtmp_push_url = $json['data']['pull_stream_url'];
//            $flv_pull_url = $json['data']['live_flv_hd'];
//            $rtmp_pull_hd = $json['data']['rtmp_hd'];
//            $hls_pull_url = $json['data']['live_hd'];
//            rtmp://istream.inke.cn/live/1530764334486657?sign=364c99d464fbf258MjAwLWlzdHJlYW0uaW5rZS5jbi0xMDQwOTk0MTQ=&ver=2&uid=104099414&ikAppState=0
            $urls = explode('/', $rtmp_push_url);
            $stream_name = array_pop($urls);
            $stream_url = join('/', $urls);
            dump('URL：' . $stream_url);
            dump('流名称：' . $stream_name);
            dump('==================================================================');
            list($stream_id) = explode('?', $stream_name);
            $flv_pull_url = 'rtmp://wssource.pull.inke.cn/live/' . $stream_id;
            $hls_pull_url = 'http://wssource.hls.inke.cn/live/' . $stream_id . '/playlist.m3u8';
            dump('PC播放地址：' . $flv_pull_url);
            dump('M3U8播放地址：' . $hls_pull_url);
        }
    }

    public function test()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://mbl.56.com/play/v2/applyShow.ios?roomId=592044434");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_USERAGENT, "zhibo/5.8.1 (iPhone; iOS 11.4; Scale/2.00)");
        curl_setopt($ch, CURLOPT_COOKIE, "member_id=qq-109084804%4056.com; pass_hex=004073e6e98812e82cb024e8699a23038dc0ec29; qfInfo=%7B%22typePatriarch%22%3A%22%22%2C%22qfLogin%22%3A1%7D");
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $response = curl_exec($ch);
//        dump($response);
//        curl_setopt($ch, CURLOPT_URL, "https://mbl.56.com/play/v1/stopShow.ios?roomId=592044434");
        curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_close($ch);
        $json = json_decode($response, true);
        if (isset($json['message']['pushUrl'])) {
            //https://hls-v-ngb.qf.56.com/live/592044434_1528710076092/playlist.m3u8
            //https://v-ngb.qf.56.com/live/592044434_1528710076092.flv
            $pushUrl = $json['message']['pushUrl'];
            dump($pushUrl);
            $m3u8Url = str_replace('rtmp://up-ngb', 'https://hls-v-ngb', explode('?', $pushUrl)[0]) . '/playlist.m3u8';
            dump($m3u8Url);
            $flvUrl = str_replace('rtmp://up-ngb', 'https://v-ngb', explode('?', $pushUrl)[0]) . '.flv';
            dump($flvUrl);
//            return $json['message']['pushUrl'];
        } else {
            return null;
        }
    }
}