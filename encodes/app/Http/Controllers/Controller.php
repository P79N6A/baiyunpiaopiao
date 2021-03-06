<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Pull\XBetEncodesController;
use App\Models\EncodeTask;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{

    protected $sizes = [//视频输出分辨率
        'ssd' => ['name' => '320p', 'w' => 600, 'h' => 320, 'factor' => 0.75],
        'sd' => ['name' => '480p', 'w' => 800, 'h' => 480, 'factor' => 1],
        'md' => ['name' => '540p', 'w' => 900, 'h' => 540, 'factor' => 1.125],
        'hd' => ['name' => '720p', 'w' => 1200, 'h' => 720, 'factor' => 1.5],
        'hhd' => ['name' => '1080p', 'w' => 1800, 'h' => 1080, 'factor' => 2.25],
    ];

    protected $logo_position = [//水印位置
        'right' => [
            'name' => '右上',
            'x' => 620,
            'y' => 20,
            'w' => 170,
            'h' => 30
        ],
        'left' => [
            'name' => '左上',
            'x' => 20,
            'y' => 20,
            'w' => 170,
            'h' => 30
        ]
    ];

    protected $fontsize = 18;//水印字体大小

    protected $watermark_alpha = 0.7;//水印透明度

    protected $random_logo = '';

    public function __construct()
    {
        View::share('banner_text', env('banner_text', '爱看球'));
        View::share('banner_color', env('banner_color', ''));
//        $count = EncodeTask::query()->where('from', env('APP_NAME'))->where('status', '>=', 1)->count();
        $count = EncodeTask::query()
            ->where('created_at', '>', date_create('-48 hour'))
            ->whereIn('status', [1, 2, -1])
            ->count();
        View::share('banner_count', $count);
        if (env('APP_NAME') == 'good') {
            View::share('watermark', '足球专家微信：bet6879，篮球专家微信：bet8679a');
            View::share('logo_text', '加微信：bet6879');
        } elseif (env('APP_NAME') == 'aikq' || env('APP_NAME') == 'aikq1') {
            $watermark = Redis::get('watermark');
            $logo_text = Redis::get('logo_text');
//            View::share('watermark', '加主播微信【kanqiu818】进群聊球，每日抢红包，会员抽iPhone X');
//            View::share('logo_text', '加微信：kanqiu818');
            if (isset($logo_text)) {
                View::share('logo_text', $logo_text);
            } else {
                View::share('logo_text', '爱看球直播');
            }
            if (isset($watermark)) {
                View::share('watermark', $watermark);
            } else {
                View::share('watermark', '专业赛事推荐，今日重心已发布！微信搜索关注《足彩边角料》公众号免费获取');
            }
            $this->random_logo = '爱看球直播：aikanqiu.com';
        } elseif (env('APP_NAME') == 'leqiuba') {
            View::share('watermark', '看球 聊球 微信群，进群加微信：zhibo556 红包福利天天有！');
            View::share('logo_text', '加微信：zhibo556');
        } else {
            View::share('watermark', '');
            View::share('logo_text', '');
        }
        $fontsize = Redis::get('fontsize');
        if (isset($fontsize)) {
            View::share('fontsize', $fontsize);
        } else {
            View::share('fontsize', $this->fontsize);
        }

        $size = Redis::get('size');
        View::share('default_size', $size);//默认
        View::share('sizes', $this->sizes);

        $logo_position = Redis::get('logo_position');
        View::share('default_logo_position', $logo_position);
        View::share('logo_position', $this->logo_position);

        $has_logo = Redis::get('has_logo');
        View::share('default_has_logo', $has_logo);

        $location = Redis::get('location');
        View::share('default_location', $location);
    }

    /**
     * 生成转码推流命令
     * @param string $input_uri 源地址
     * @param string $rtmp_url 推流地址
     * @param string $watermark 水印文案
     * @param int $fontsize 水印字体
     * @param string $location 水印位置
     * @param bool|string $has_logo 是否有logo
     * @param string $size 分辨率
     * @param string $referer 源Referer
     * @param string $header1 源Http Header 1
     * @param string $header2 源Http Header 2
     * @param string $header3 源Http Header 3
     * @param string $logo_position
     * @param string $logo_text
     * @return string 返回转码推流命令
     */
    protected function generateFfmpegCmd($input_uri = '',
                                         $rtmp_url = '',
                                         $watermark = '',
                                         $fontsize = 18,
                                         $location = 'top',
                                         $has_logo = '1',
                                         $size = 'md',
                                         $referer = '',
                                         $header1 = '',
                                         $header2 = '',
                                         $header3 = '',
                                         $logo_position = 'right',
                                         $logo_text = '')
    {
        if (empty($input_uri) || empty($rtmp_url)) {
            return '';
        }
        $size = $this->sizes[$size];
        if (empty($size)) {
            $size = $this->sizes['md'];
        }
        $fontsize *= $size['factor'];
        $lp = $this->logo_position[$logo_position];
        $lp = [
            'x' => $lp['x'] * $size['factor'],
            'y' => $lp['y'] * $size['factor'],
            'w' => $lp['w'] * $size['factor'],
            'h' => $lp['h'] * $size['factor']
        ];
        $execs = ['nohup /usr/bin/ffmpeg'];
        if (starts_with($input_uri, 'http')) {
            if (str_contains($input_uri, '5club.cctv.cn')) {
                $execs[] = '-user_agent "cctv_app_phone_cctv5"';
                $execs[] = '-headers "Referer: api.cctv.cn"';
                $execs[] = '-headers "UID: 269482797625189"';
            } elseif (str_contains($input_uri, 'http://cctv5')) {
                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
                $execs[] = '-headers "Referer: http://tv.cctv.com/live/cctv5/"';
                $execs[] = '-headers "X-Requested-With:ShockwaveFlash/28.0.0.126"';
            } elseif (str_contains($input_uri, 'zijian.hls.video.qq.com')) {
                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
                $execs[] = '-headers "Referer: http://sports.qq.com/kbsweb/"';
                $execs[] = '-headers "X-Requested-With:ShockwaveFlash/28.0.0.126"';
            } elseif (str_contains($input_uri, 'qietv.douyucdn.cn')) {
//                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
//                $execs[] = '-headers "Referer: http://live.qq.com/10003848"';
//                $execs[] = '-headers "X-Requested-With: ShockwaveFlash/29.0.0.140"';
            } elseif (starts_with($input_uri, 'http://gmcllc.de')) {
                $execs[] = '-user_agent "BLUEIOS"';
                $execs[] = '-headers "Range: bytes=0-"';
                $execs[] = '-headers "Icy-MetaData: 1"';
            } elseif (str_contains($input_uri, 'aliez-stream.gcdn.co')) {
                $execs[] = '-user_agent "AppleCoreMedia/1.0.0.15G77 (iPhone; U; CPU OS 11_4_1 like Mac OS X; zh_cn)"';
                $execs[] = '-headers "referer: http://emb.aliez.me/"';
            } elseif (str_contains($input_uri, '.live.sjmhw.com')) {
                $execs[] = '-user_agent "AppleCoreMedia/1.0.0.15G77 (iPhone; U; CPU OS 11_4_1 like Mac OS X; zh_cn)"';
                $execs[] = '-headers "referer: https://www.lehuzhibo.com/"';
            } elseif (starts_with($input_uri, 'https://m3u8.zhibo1.cc/')) {
                $execs[] = '-user_agent "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36"';
                $execs[] = '-headers "origin: https://www.ballbar.cc"';
                $execs[] = '-headers "referer: https://www.ballbar.cc/live/17240"';
            } elseif (str_contains($input_uri, 'livecdn.tk')) {
                $execs[] = '-user_agent "' . XBetEncodesController::K_X_BET_USER_AGENT . '"';
//                $execs[] = '-headers "origin: https://www.ballbar.cc"';
//                $execs[] = '-headers "referer: https://www.ballbar.cc/live/17240"';
            } else {
                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
                if (!empty($referer)) {
                    $execs[] = '-headers "Referer:' . $referer . '"';
                }
                if (!empty($header1)) {
                    $execs[] = '-headers "' . $header1 . '"';
                }
                if (!empty($header2)) {
                    $execs[] = '-headers "' . $header2 . '"';
                }
                if (!empty($header3)) {
                    $execs[] = '-headers "' . $header3 . '"';
                }
            }
        }
        $execs[] = '-c:v h264_cuvid -re -i "' . $input_uri . '"';
        $execs[] = '-vcodec h264_nvenc -acodec aac';

        if (!empty($watermark)) {
            $logo_code = '';
            if (!empty($has_logo)) {
                $logo_code = 'drawbox=color=black:x=' . $lp['x'] . ':y=' . $lp['y'] . ':width=' . $lp['w'] . ':height=' . $lp['h'] . ':t=fill,';
                if (!empty($logo_text)) {
                    $logo_code .= 'drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $logo_text . '\':fontcolor=0xf7f14e:fontsize=' . $fontsize . ':x=(' . $lp['x'] . '+(' . $lp['w'] . '-tw)/2):y=(' . $lp['y'] . '+(' . $lp['h'] . '-' . $fontsize . ')/2),';
                }
            }

            //随机水印
            $random_logo = '';
            if (!empty($this->random_logo)) {
                $random_logo = 'drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $this->random_logo . '\':fontcolor=0xffffff:fontsize=' . $fontsize . ':x=mod(3*n\,31*w)-16*w:y=h/(1.2+mod(3*n/(31*w)\,6)/1.5),';
            }

            if ($location == 'top') {
                $vf = '-vf "scale=' . $size['w'] . ':' . $size['h'] . ',format=pix_fmts=yuv420p,' . $logo_code . $random_logo . 'drawbox=y=0:color=black@' . $this->watermark_alpha . ':width=iw:height=' . ($fontsize * 2) . ':t=fill,drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $watermark . '\':fontcolor=white:fontsize=' . $fontsize . ':x=(w-tw)/2:y=' . ($fontsize / 2) . '"';
            } elseif ($location = 'bottom') {
                $vf = '-vf "scale=' . $size['w'] . ':' . $size['h'] . ',format=pix_fmts=yuv420p,' . $logo_code . $random_logo . 'drawbox=y=(ih-' . ($fontsize * 2) . '):color=black@' . $this->watermark_alpha . ':width=iw:height=' . ($fontsize * 2) . ':t=fill,drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $watermark . '\':fontcolor=white:fontsize=' . $fontsize . ':x=(w-tw)/2:y=(h-' . ($fontsize + $fontsize / 2) . ')"';
            } else {
                $vf = '-vf "scale=' . $size['w'] . ':' . $size['h'] . ',format=pix_fmts=yuv420p,' . $logo_code . $random_logo . 'drawbox=y=0:color=black@' . $this->watermark_alpha . ':width=iw:height=' . ($fontsize * 2) . ':t=fill,drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $watermark . '\':fontcolor=white:fontsize=' . $fontsize . ':x=(w-tw)/2:y=' . ($fontsize / 2) . '"';
            }
            $execs[] = $vf;
        }

        $execs[] = '-r 24 -keyint_min 36 -g 36 -sc_threshold 0 -b:v:0 ' . (1200 * $size['factor']) . 'k -pixel_format yuv420p -s ' . $size['w'] . 'x' . $size['h'] . ' -f flv "' . $rtmp_url . '"';
//        $execs[] = '-r 24 -keyint_min 36 -g 36 -sc_threshold 0 -maxrate ' . (1200 * $size['factor']) . 'k -minrate ' . (1200 * $size['factor']) . 'k -bf 1 -b_strategy 0 -pixel_format yuv420p -s ' . $size['w'] . 'x' . $size['h'] . ' -f flv "' . $rtmp_url . '"';

        $date = date('YmdHis');
        $execs[] = ">> /tmp/ffmpeg-$date.log &";
        $exec = join($execs, ' ');
        return $exec;
    }

    protected function generateLehuFfmpegCmd($input_uri = '',
                                             $rtmp_url = '',
                                             $watermark = '',
                                             $fontsize = 18,
                                             $location = 'top',
                                             $has_logo = '1',
                                             $size = 'hd',
                                             $logo_position = 'right',
                                             $logo_text = '')
    {
        if (empty($input_uri) || empty($rtmp_url)) {
            return '';
        }
        $size = $this->sizes[$size];
        if (empty($size)) {
            $size = $this->sizes['hd'];
        }
        $fontsize *= $size['factor'];
        $lp = $this->logo_position[$logo_position];
        $lp = [
            'x' => $lp['x'] * $size['factor'],
            'y' => $lp['y'] * $size['factor'],
            'w' => $lp['w'] * $size['factor'],
            'h' => $lp['h'] * $size['factor']
        ];
        $execs = ['nohup /usr/bin/ffmpeg'];
        if (starts_with($input_uri, 'http')) {
            if (str_contains($input_uri, '5club.cctv.cn')) {
                $execs[] = '-user_agent "cctv_app_phone_cctv5"';
                $execs[] = '-headers "Referer: api.cctv.cn"';
                $execs[] = '-headers "UID: 269482797625189"';
            } elseif (str_contains($input_uri, 'http://cctv5')) {
                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
                $execs[] = '-headers "Referer: http://tv.cctv.com/live/cctv5/"';
                $execs[] = '-headers "X-Requested-With:ShockwaveFlash/28.0.0.126"';
            } elseif (str_contains($input_uri, 'zijian.hls.video.qq.com')) {
                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
                $execs[] = '-headers "Referer: http://sports.qq.com/kbsweb/"';
                $execs[] = '-headers "X-Requested-With:ShockwaveFlash/28.0.0.126"';
            } elseif (str_contains($input_uri, 'qietv.douyucdn.cn')) {
//                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
//                $execs[] = '-headers "Referer: http://live.qq.com/10003848"';
//                $execs[] = '-headers "X-Requested-With: ShockwaveFlash/29.0.0.140"';
            } elseif (str_contains($input_uri, 'aliez-stream.gcdn.co')) {
                $execs[] = '-user_agent "AppleCoreMedia/1.0.0.15G77 (iPhone; U; CPU OS 11_4_1 like Mac OS X; zh_cn)"';
                $execs[] = '-headers "referer: http://emb.aliez.me/"';
            } elseif (str_contains($input_uri, 'live.sjmhw.com')) {
                $execs[] = '-user_agent "AppleCoreMedia/1.0.0.15G77 (iPhone; U; CPU OS 11_4_1 like Mac OS X; zh_cn)"';
                $execs[] = '-headers "referer: https://www.lehuzhibo.com/"';
            } elseif (str_contains($input_uri, 'live.dlfyb.com')) {
                $execs[] = '-user_agent "AppleCoreMedia/1.0.0.15G77 (iPhone; U; CPU OS 11_4_1 like Mac OS X; zh_cn)"';
                $execs[] = '-headers "referer: https://www.lehuzhibo.com/"';
            } elseif (starts_with($input_uri, 'https://m3u8.zhibo1.cc/')) {
                $execs[] = '-user_agent "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36"';
                $execs[] = '-headers "origin: https://www.ballbar.cc"';
                $execs[] = '-headers "referer: https://www.ballbar.cc/live/17240"';
            } elseif (str_contains($input_uri, 'livecdn.tk')) {
                $execs[] = '-user_agent "' . XBetEncodesController::K_X_BET_USER_AGENT . '"';
//                $execs[] = '-headers "origin: https://www.ballbar.cc"';
//                $execs[] = '-headers "referer: https://www.ballbar.cc/live/17240"';
            } else {
                $execs[] = '-user_agent "Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 63.0.3239.84 Safari / 537.36"';
            }
        }
        $execs[] = '-c:v h264_cuvid -re -i "' . $input_uri . '"';
        $execs[] = '-vcodec h264_nvenc -acodec aac';

        if (!empty($watermark) || !empty($has_logo)) {
            $logo_code = '';//logo
            if (!empty($has_logo)) {
                $logo_code = ',drawbox=color=black:x=' . $lp['x'] . ':y=' . $lp['y'] . ':width=' . $lp['w'] . ':height=' . $lp['h'] . ':t=fill';
                if (!empty($logo_text)) {
                    $logo_code .= ',drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $logo_text . '\':fontcolor=0xf7f14e:fontsize=' . $fontsize . ':x=(' . $lp['x'] . '+(' . $lp['w'] . '-tw)/2):y=(' . $lp['y'] . '+(' . $lp['h'] . '-' . $fontsize . ')/2)';
                }
            }
            $watermark_code = '';//mark
            if (!empty($watermark)) {
                $watermark_code = ',drawbox=y=0:color=black@' . $this->watermark_alpha . ':width=iw:height=' . ($fontsize * 2) . ':t=fill,drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $watermark . '\':fontcolor=white:fontsize=' . $fontsize . ':x=(w-tw)/2:y=' . ($fontsize / 2);
                if ($location = 'bottom') {
                    $watermark_code = ',drawbox=y=(ih-' . ($fontsize * 2) . '):color=black@' . $this->watermark_alpha . ':width=iw:height=' . ($fontsize * 2) . ':t=fill,drawtext=font=\'WenQuanYi Zen Hei\':text=\'' . $watermark . '\':fontcolor=white:fontsize=' . $fontsize . ':x=(w-tw)/2:y=(h-' . ($fontsize + $fontsize / 2) . ')';
                }
            }
            $vf = '-vf "scale=' . $size['w'] . ':' . $size['h'] . ',format=pix_fmts=yuv420p' . $logo_code . $watermark_code . '"';
            $execs[] = $vf;
        }

        $execs[] = '-r 24 -keyint_min 36 -g 36 -sc_threshold 0 -b:v:0 ' . (1200 * $size['factor']) . 'k -pixel_format yuv420p -s ' . $size['w'] . 'x' . $size['h'] . ' -f flv "' . $rtmp_url . '"';

        $date = date('YmdHis');
        $execs[] = ">> /tmp/ffmpeg-$date.log &";
        $exec = join($execs, ' ');
        return $exec;
    }

    protected function stopPush($id)
    {
        $et = EncodeTask::query()->find($id);
        if (isset($et)) {
            $pid = exec('pgrep -f "' . explode('?', $et->rtmp)[0] . '"');
            if (!empty($pid) && $et->pid == $pid) {
                exec('kill -9 ' . $et->pid, $output_array, $return_var);
                if ($return_var == 0) {
                    $et->status = 0;
                    $et->stop_at = date_create();
                    $et->save();
                }
            } else {
                $et->status = 0;
                $et->stop_at = date_create();
                $et->save();
            }
        }
    }

    protected function repeatPush($id)
    {
        $et = EncodeTask::query()->find($id);
        if (isset($et)) {
            $pid = exec('pgrep -f "' . explode('?', $et->rtmp)[0] . '"');
            if (!empty($pid)) {
                if ($et->pid == $pid) {
                    exec('kill -9 ' . $et->pid, $output_array, $return_var);
                    if ($return_var == 0) {
                        $et->status = 0;
                        $et->stop_at = date_create();
                        $et->save();
                        sleep(1);
                        shell_exec($et->exec);
                        $pid = exec('pgrep -f "' . explode('?', $et->rtmp)[0] . '"');
                        if (!empty($pid) && is_numeric($pid) && $pid > 0) {
                            $et->status = 1;
                            $et->pid = $pid;
                            $et->save();
                        }
                    }
                }
            } else {
                shell_exec($et->exec);
                $pid = exec('pgrep -f "' . explode('?', $et->rtmp)[0] . '"');
                if (!empty($pid) && is_numeric($pid) && $pid > 0) {
                    $et->status = 1;
                    $et->pid = $pid;
                    $et->save();
                }
            }
        }
    }

    //抓取关键帧
    protected function spiderKeyFrame($stream, $out)
    {
        exec('ffmpeg -i "' . $stream . '" -y -vframes 1 -f image2 ' . $out);
    }

    //验证流是否正常
    protected function streamCheck($stream)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $stream);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // connect timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // curl timeout
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // curl timeout

        $status = false;
        if (TRUE === curl_exec($ch)) {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode == 200) {
                $status = true;
            }
        }
        return $status;
    }
}
