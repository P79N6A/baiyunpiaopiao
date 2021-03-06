<?php
namespace App\Http\Controllers\Mobile\Match;

use App\Http\Controllers\FileTool;
use App\Http\Controllers\Mobile\AppCommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Created by PhpStorm.
 * User: ricky
 * Date: 2018/1/3
 * Time: 17:30
 */
class MatchesController
{
    /**
     * 调用match项目下的静态文件
     */
    public function index(Request $request, $sport, $tab) {
        $type = $request->input('type', 'all');

        $defaultDate = date('Y-m-d');
        switch ($tab) {
            case "result":
                $defaultDate = date('Y-m-d', strtotime('-1 days'));
                break;
            case "schedule":
                $defaultDate = date('Y-m-d', strtotime('+1 days'));
                break;
            case 'matchesByIds': //这个需要单独处理
                return $this->convert($request, $sport, $tab);
        }
        $date = $request->input('date', $defaultDate);

        //赛事id筛选
        $lids = null;
        if ($request->exists('id')) {
            $lids = explode(',', $request->input('id'));
        }

        //先从本地获取文件
        $formatDate = date('Ymd', strtotime($date));
        $result = json_decode(FileTool::getMatchesData($sport, $type, $formatDate), true);
        //如果获取不到，则从match项目请求数据
        if (!isset($result)) {
            $ch = curl_init();
            $url = env('MATCH_URL') . "/app/matches/" . $formatDate . "/" . $sport . "/" . $type . ".json";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $json = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($json, true);
        }
        if (is_null($result)) {
            return Response::json(AppCommonResponse::createAppCommonResponse(500, '参数错误'));
        }

        $tempMatches = $result['matches'];

        $matches = array();
        foreach ($tempMatches as $match) {
            $lid = $match['lid'];

            if (is_null($lids) || count($lids) <= 0 || in_array($lid, $lids)) {
                $matches[] = $match;
            }
        }
        if (is_null($lids) || count($lids) <= 0) {
            $data['filter'] = $result['filter'];
        }
        $data['matches'] = $matches;
        return Response::json(AppCommonResponse::createAppCommonResponse(0, '', $data, false));
    }

    public function convert($request, $sport, $tab){
        $ch = curl_init();
        $url = 'https://shop.liaogou168.com/api/v140/app/matches/'.$sport.'/matchesByIds?ids='.$request->input('ids',0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $json = curl_exec ($ch);
        curl_close ($ch);
        $json = json_decode($json, true);
        return $json;
    }
}