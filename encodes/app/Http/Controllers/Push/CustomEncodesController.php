<?php

namespace App\Http\Controllers\Push;

use App\Http\Controllers\Controller as BaseController;
use App\Models\EncodeTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CustomEncodesController extends BaseController
{
    private $channels = [];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('filter')->except([]);
        if (env('APP_NAME') == 'good') {

        } elseif (env('APP_NAME') == 'aikq') {
            $rens[] = 'renren##201804241141222057111';
            $rens[] = 'renren##201804241141222057222';
            $rens[] = 'renren##201804241141222057333';
            $rens[] = 'renren##201804241141222057444';
            $this->channels['人人-金山'] = $rens;

            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008dvaa';
            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008dvbb';
            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008dvcc';
            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008dvdd';
            $this->channels['我乐-帝联'] = $dfms;

            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dvaa';
            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dvbb';
            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dvcc';
            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dvdd';
            $this->channels['我乐-网宿'] = $wfms;

            $memes[] = 'meme-ali##40290811';
            $memes[] = 'meme-ali##40290822';
            $memes[] = 'meme-ali##40290833';
            $memes[] = 'meme-ali##40290844';
            $this->channels['么么-阿里'] = $memes;

            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184f8aa';
            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184f8bb';
            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184f8cc';
            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184f8dd';
            $this->channels['云图-阿里'] = $nagezanalis;

            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184f7aa';
            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184f7bb';
            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184f7cc';
            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184f7dd';
            $this->channels['云图-网宿'] = $nagezanwss;

            $maobos[] = 'maobo-ali##300223331';
            $maobos[] = 'maobo-ali##300223332';
            $maobos[] = 'maobo-ali##300223333';
            $maobos[] = 'maobo-ali##300223334';
            $this->channels['猫播-阿里'] = $maobos;

            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432113_111';
            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432113_222';
            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432113_333';
            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432113_444';
            $this->channels['看东方-网宿'] = $kdfs;

        } elseif (env('APP_NAME') == 'aikq1') {
            $rens[] = 'renren##201804241141222056111';
            $rens[] = 'renren##201804241141222056222';
            $rens[] = 'renren##201804241141222056333';
            $rens[] = 'renren##201804241141222056444';
            $this->channels['人人-金山'] = $rens;

            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008ddaa';
            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008ddbb';
            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008ddcc';
            $dfms[] = 'wole-dl##5ad02f57e85eb9cf5975eb7008dddd';
            $this->channels['我乐-帝联'] = $dfms;

            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dcaa';
            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dcbb';
            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dccc';
            $wfms[] = 'wole-ws##5ad02f57e85eb9cf5975eb7008dcdd';
            $this->channels['我乐-网宿'] = $wfms;

            $memes[] = 'meme-ali##40290611';
            $memes[] = 'meme-ali##40290622';
            $memes[] = 'meme-ali##40290633';
            $memes[] = 'meme-ali##40290644';
            $this->channels['么么-阿里'] = $memes;

            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184d7aa';
            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184d7bb';
            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184d7cc';
            $nagezanalis[] = 'yuntu-ali##9bfd6634-9ea6-4081-95e1-2ccee184d7dd';
            $this->channels['云图-阿里'] = $nagezanalis;

            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184c7aa';
            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184c7bb';
            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184c7cc';
            $nagezanwss[] = 'yuntu-ws##9bfd6634-9ea6-4081-95e1-2ccee184c7dd';
            $this->channels['云图-网宿'] = $nagezanwss;

            $maobos[] = 'maobo-ali##300213331';
            $maobos[] = 'maobo-ali##300213332';
            $maobos[] = 'maobo-ali##300213333';
            $maobos[] = 'maobo-ali##300213334';
            $this->channels['猫播-阿里'] = $maobos;

            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432112_111';
            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432112_222';
            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432112_333';
            $kdfs[] = 'kdf-ws##wCOXzowoOsmGe_11125_1486629432112_444';
            $this->channels['看东方-网宿'] = $kdfs;
        } elseif (env('APP_NAME') == 'leqiuba') {

        }
    }

    public function index(Request $request)
    {
        $ets = EncodeTask::query()->where('from', env('APP_NAME'))->where('to', 'Custom')->where('created_at', '>', date_create('-24 hour'))->whereIn('status', [1, 2, -1])->get();
        return view('manager.push.custom', ['ets' => $ets, 'channels' => $this->channels]);
    }

    public function created(Request $request)
    {
        if ($request->isMethod('post')
            && $request->has('input')
            && $request->has('channel')
            && $request->has('name')
        ) {
            $name = str_replace(' ', '-', $request->input('name'));
            $input = $request->input('input');

//            $channel = $request->input('channel');
            $channel = $request->input('channel');
            $ets = EncodeTask::query()->where('from', env('APP_NAME'))->where('to', 'Custom')->where('created_at', '>', date_create('-24 hour'))->whereIn('status', [1, 2, -1])->inRandomOrder()->get();
            if ($ets->contains('channel', $channel)) {
                foreach ($this->channels as $ch) {
                    if (!$ets->contains('channel', $ch)) {
                        $channel = $ch;
                    }
                }
            }
            if (empty($channel)) {
                return back()->with(['error' => '没有可用的直播间咯']);
            }
            list($type, $roomId) = explode('##', $channel);
            $rtmp_url = '';
            $live_rtmp_url = '';
            $live_m3u8_url = '';
            switch ($type) {
                case 'renren': {
                    $rtmp_url = 'rtmp://ksy-uplive.renren.com/live/' . $roomId;//获取rtmp地址
                    $live_rtmp_url = 'http://ksy-hls.renren.com/live/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://ksy-hls.renren.com/live/' . $roomId . '/index.m3u8';//播放m3u8地址
                    break;
                }
                case 'wole-dl': {
                    $rtmp_url = 'rtmp://dfms.xiuimg.com/vshow/' . $roomId;//获取rtmp地址
                    $live_rtmp_url = 'http://dplay.xiuimg.com/vshow/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://dhls.xiuimg.com/vshow/' . $roomId . '/index.m3u8';//播放m3u8地址
                    break;
                }
                case 'wole-ws': {
                    $rtmp_url = 'rtmp://wfms.xiuimg.com/vshow/' . $roomId;//获取rtmp地址
                    $live_rtmp_url = 'http://wplay.xiuimg.com/vshow/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://whls.xiuimg.com/vshow/' . $roomId . '/playlist.m3u8';//播放m3u8地址
                    break;
                }
                case 'meme-ali': {
                    $rtmp_url = 'rtmp://video-center.alivecdn.com/memeyule/' . $roomId . '?vhost=aliyun.memeyule.com';//获取rtmp地址
                    $live_rtmp_url = 'http://aliyun.memeyule.com/memeyule/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://aliyun.memeyule.com/memeyule/' . $roomId . '.m3u8';//播放m3u8地址
                    break;
                }
                case 'yuntu-ali': {
                    $rtmp_url = 'rtmp://video-center.alivecdn.com/nagezan/' . $roomId . '?vhost=aliyun.nagezan.net';//获取rtmp地址
                    $live_rtmp_url = 'http://aliyun.nagezan.net/nagezan/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://aliyun.nagezan.net/nagezan/' . $roomId . '.m3u8';//播放m3u8地址
                    break;
                }
                case 'yuntu-ws': {
                    $rtmp_url = 'rtmp://push.live.nagezan.net/vod/' . $roomId;//获取rtmp地址
                    $live_rtmp_url = 'http://pull.live.nagezan.net/vod/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://pull-hls.live.nagezan.net/vod/' . $roomId . '/playlist.m3u8';//播放m3u8地址
                    break;
                }
                case 'maobo-ali': {
                    $rtmp_url = 'rtmp://push.maobotv.com/maozhua/' . $roomId;//获取rtmp地址
                    $live_rtmp_url = 'http://flv.maobotv.com/maozhua/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://hls.maobotv.com/maozhua/' . $roomId . '/index.m3u8';//播放m3u8地址
                    break;
                }
                case 'kdf-ws': {
                    $rtmp_url = 'rtmp://kdf.wslive.cibnlive.com/live/' . $roomId;//获取rtmp地址
                    $live_rtmp_url = 'http://kdf.wsflv.cibnlive.com/live/' . $roomId . '.flv';//播放rtmp地址
                    $live_m3u8_url = 'http://kdf.wshls.cibnlive.com/live/' . $roomId . '/playlist.m3u8';//播放m3u8地址
                    break;
                }
            }

            if (!empty($rtmp_url)) {
                $fontsize = $request->input('fontsize', 18);
                $watermark = $request->input('watermark', '');
                $location = $request->input('location', 'top');
                $has_logo = $request->input('logo');
                $logo_position = $request->input('logo_position', '');
                $logo_text = $request->input('logo_text', '');
                $referer = $request->input('referer', '');
                $header1 = $request->input('header1', '');
                $header2 = $request->input('header2', '');
                $header3 = $request->input('header3', '');
                $size = $request->input('size', 'md');
                $exec = $this->generateFfmpegCmd($input, $rtmp_url, $watermark, $fontsize, $location, $has_logo, $size, $referer, $header1, $header2, $header3, $logo_position, $logo_text);
                Log::info($exec);
                shell_exec($exec);
                $pid = exec('pgrep -f "' . explode('?', $rtmp_url)[0] . '"');
                if (!empty($pid) && is_numeric($pid) && $pid > 0) {
                    $et = new EncodeTask();
                    $et->name = $name;
                    $et->channel = $channel;
                    $et->input = $input;
                    $et->rtmp = $rtmp_url;
                    $et->exec = $exec;
                    $et->pid = $pid;
                    $et->out = $live_rtmp_url . "\n" . $live_m3u8_url;
                    $et->from = env('APP_NAME');
                    $et->to = 'Custom';
                    $et->status = 1;
                    $et->save();
                }
            }

        }
        return back();
    }

    public function stop(Request $request, $id)
    {
        $this->stopPush($id);
        return back();
    }

    public function repeat(Request $request, $id)
    {
        $this->repeatPush($id);
        return back();
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
