<?php
/**
 * Created by PhpStorm.
 * User: 11247
 * Date: 2018/2/9
 * Time: 19:12
 */

namespace App\Http\Controllers\Mobile\Detail;


use App\Http\Controllers\CacheInterface\FootballDetailInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mobile\Match\MatchDetailTool;
use App\Models\Match\MatchLive;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    use MatchDetailTool;

    public function detailCell(Request $request, $type, $id) {
        $match = $this->footballDetailMatchData($id);
        $date = date('Ymd', $match['time']);
        switch ($type) {
            case 'team' :
                return $this->teamCell($request, $date, $id);
                break;
            case 'analyse':
                return $this->analyseCell($request, $date, $id);
                break;
            case 'base':
                return$this->baseCell($request, $date, $id, $match);
                break;
            default:
                return "";
        }
    }

    /**
     * 球队终端 球队 单元的html 内容
     * @param Request $request
     * @param $date
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function teamCell(Request $request, $date, $id) {
        $corner = $this->teamCornerData($date, $id);
        $style = $this->teamStyleData($date, $id);
        $result['corner'] = $corner;
        $result['style'] = $style;
        return view('mobile.football_detail_cell.team_cell', $result);
    }

    /**
     * 终端分析页面
     * @param Request $request
     * @param $date
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function analyseCell(Request $request, $date, $id) {
        $base = $this->analyseBaseData($date, $id);
        $odd = $this->analyseOddData($date, $id);

        $result['odd'] = $odd;
        $result['base'] = $base;
        return view('mobile.football_detail_cell.analyse_cell', $result);
    }

    /**
     * 足球终端 赛况页面
     * @param Request $request
     * @param $date
     * @param $id
     * @param $match
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function baseCell(Request $request, $date, $id, $match = []) {
        $event = $this->baseData($date, $id);
        $result = array_merge($event, $match);
        return view('mobile.football_detail_cell.base_cell', $result);
    }
    //====================================================================================//

    /**
     *  获取比赛信息。
     * @param $id
     * @return array|bool|mixed|null|string
     */
    public function getFootballMatchData($id) {
        $cacheInterface = new FootballDetailInterface();
        $json = $cacheInterface->getMatchDataFromCache($id);
        if (isset($json)) {
            return json_decode($json, true);
        }
        $json = $this->footballDetailMatchData($id);
        return $json;
    }

    /**
     * 分析页面 赔率数据
     * @param $date
     * @param $id
     * @return mixed
     */
    public function analyseOddData($date, $id) {
        $cacheInterface = new FootballDetailInterface();
        $json = $cacheInterface->getOddDataFromCache($date, $id);
        if (isset($json)) {
            return json_decode($json, true);
        }
        return $this->footballOddData($id, $date);
    }

    /**
     * 分析页面基础数据
     * @param $date
     * @param $id
     * @return array|mixed
     */
    public function analyseBaseData($date, $id) {
        $cacheInterface = new FootballDetailInterface();
        $json = $cacheInterface->getBaseDataFromCache($date, $id);
        if (isset($json)) {
            return json_decode($json, true);
        }
        return $this->footballDetailBaseData($id, $date);
    }

    /**
     * 球队 角球数据
     * @param $date
     * @param $mid
     * @return bool|mixed|null|string
     */
    public function teamCornerData($date, $mid) {
        $cacheInterface = new FootballDetailInterface();
        $json = $cacheInterface->getCornerDataFromCache($date, $mid);
        if (isset($json)) {//如果有文件内容则返回文件的内容。
            return json_decode($json, true);
        }
        $json = $this->footballCornerData($mid, $date);
        return $json;
    }

    /**
     * 球队 风格数据
     * @param $date
     * @param $mid
     * @return bool|mixed|null|string
     */
    public function teamStyleData($date, $mid) {
        $cacheInterface = new FootballDetailInterface();
        $json = $cacheInterface->getStyleDataFromCache($date, $mid);
        if (isset($json)) {//如果有文件内容则返回文件的内容。
            return json_decode($json, true);
        }
        $json = $this->footballDetailBaseData($mid, $date);
        return $json;
    }

    /**
     * 比赛终端 概况数据
     * @param $date
     * @param $mid
     * @return bool|mixed|null|string
     */
    public function baseData($date, $mid) {
        $cacheInterface = new FootballDetailInterface();
        $json = $cacheInterface->getEventFromCache($date, $mid);
        if (isset($json)) {//如果有文件内容则返回文件的内容。
            return json_decode($json, true);
        }
        $json = $this->footballEventData($mid, $date);
        return $json;
    }
}