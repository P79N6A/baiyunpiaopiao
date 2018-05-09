<?php

namespace App\Http\Controllers\Push;

use App\Http\Controllers\Controller as BaseController;
use App\Models\EncodeTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HuajiaoEncodesController extends BaseController
{
    private $channels = [];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('filter')->except([]);
        if (env('APP_NAME') == 'good') {

        } elseif (env('APP_NAME') == 'aikq') {
            $this->channels[] = '花椒直播1##_LC_AL1_5832731615253162591492831';
            $this->channels[] = '花椒直播2##_LC_AL1_5832731615253162591492832';
            $this->channels[] = '花椒直播3##_LC_AL1_5832731615253162591492833';
            $this->channels[] = '花椒直播4##_LC_AL1_5832731615253162591492834';
            $this->channels[] = '花椒直播5##_LC_AL1_5832731615253162591492835';
            $this->channels[] = '花椒直播6##_LC_AL1_5832731615253162591492836';
            $this->channels[] = '花椒直播7##_LC_AL1_5832731615253162591492837';
            $this->channels[] = '花椒直播8##_LC_AL1_5832731615253162591492838';
        } elseif (env('APP_NAME') == 'aikq1') {
            $this->channels[] = '花椒直播1##_LC_AL1_5822731615253162591492831';
            $this->channels[] = '花椒直播2##_LC_AL1_5822731615253162591492832';
            $this->channels[] = '花椒直播3##_LC_AL1_5822731615253162591492833';
            $this->channels[] = '花椒直播4##_LC_AL1_5822731615253162591492834';
            $this->channels[] = '花椒直播5##_LC_AL1_5822731615253162591492835';
            $this->channels[] = '花椒直播6##_LC_AL1_5822731615253162591492836';
            $this->channels[] = '花椒直播7##_LC_AL1_5822731615253162591492837';
            $this->channels[] = '花椒直播8##_LC_AL1_5822731615253162591492838';
        } elseif (env('APP_NAME') == 'leqiuba') {
            $this->channels[] = '花椒直播1##_LC_AL1_5812731615253162591492831';
            $this->channels[] = '花椒直播2##_LC_AL1_5812731615253162591492832';
            $this->channels[] = '花椒直播3##_LC_AL1_5812731615253162591492833';
            $this->channels[] = '花椒直播4##_LC_AL1_5812731615253162591492834';
            $this->channels[] = '花椒直播5##_LC_AL1_5812731615253162591492835';
            $this->channels[] = '花椒直播6##_LC_AL1_5812731615253162591492836';
            $this->channels[] = '花椒直播7##_LC_AL1_5812731615253162591492837';
            $this->channels[] = '花椒直播8##_LC_AL1_5812731615253162591492838';
        }
    }

    public function index(Request $request)
    {
        $ets = EncodeTask::query()->where('from', env('APP_NAME'))->where('to', 'Huajiao')->where('created_at', '>', date_create('-3 hour'))->whereIn('status', [1, 2, -1])->get();
        return view('manager.push.huajiao', ['ets' => $ets, 'channels' => $this->channels]);
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
            $ets = EncodeTask::query()->where('from', env('APP_NAME'))->where('to', 'Huajiao')->where('created_at', '>', date_create('-3 hour'))->whereIn('status', [1, 2, -1])->inRandomOrder()->get();
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
            list($roomName, $roomId) = explode('##', $channel);
            $rtmp_url = 'rtmp://al1.live.huajiao.com/live_huajiao_v2/' . $roomId;//获取rtmp地址
            $live_flv_url = 'http://al1-flv.live.huajiao.com/live_huajiao_v2/' . explode('?', $roomId)[0] . '.flv';//flv地址
            $live_rtmp_url = '';//rtmp地址
            $live_m3u8_url = 'http://al1-hls.live.huajiao.com/live_huajiao_v2/' . explode('?', $roomId)[0] . '.m3u8';//m3u8地址

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
                $et->out = $live_flv_url . "\n" . $live_rtmp_url . "\n" . $live_m3u8_url;
                $et->from = env('APP_NAME');
                $et->to = 'Huajiao';
                $et->status = 1;
                $et->save();
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
