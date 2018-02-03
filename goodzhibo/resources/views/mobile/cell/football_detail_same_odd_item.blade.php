@if(isset($sameOdd))
<?php
    $count = isset($sameOdd['matches']) ? min(count($sameOdd['matches']), 10) : 0;
?>
<div id="{{$id}}" class="{{$class}} default childNode" style="display: {{$ch == '' ? 'none' : ''}};" ha="0" le="0">
    <div class="title">历史同赔</div>
    <div class="proportion" ha="0" le="0">
        <p class="win" style="width: {{$sameOdd['win']}}%;"><b>{{$sameOdd['win']}}%</b></p>
        <p class="draw" style="width: {{$sameOdd['draw']}}%;"><b>{{$sameOdd['draw']}}%</b></p>
        <p class="lose" style="width: {{$sameOdd['lose']}}%;"><b>{{$sameOdd['lose']}}%</b></p>
    </div>
    <table ha="0" le="0">
        <thead>
        <tr>
            <th>赛事</th>
            <th>对阵</th>
            <th>比分</th>
            <th>初盘/终盘</th>
            <th>结果</th>
        </tr>
        </thead>
        <tbody>
        @for($index=0; $index < $count; $index++)
        <?php
            $match = $sameOdd['matches'][$index];
            $result = $sameOdd['result'][$index];
            $r_class = $result == 3 ? 'win' : ($result == 1 ? 'draw' : 'lose');
            $r_cn = '';
            if ($type == 1) {
                $r_cn = $result == 3 ? '赢' :($result == 1 ? '走' : '输');
            } else if ($type == 2) {
                $r_cn = $result == 3 ? '大球' :($result == 1 ? '走' : '小球');
            } else if ($type == 3) {
                $r_cn = $result == 3 ? '主胜' :($result == 1 ? '走' : '客胜');
            }
        ?>
        <tr>
            <td>{{isset($match['lname'])?$match['lname']:$match['win_lname']}}</td>
            <td>
                <p>{{$match['hname']}}</p>
                <p>{{$match['aname']}}</p>
            </td>
            <td>
                <p class="red">{{$match['hscore']}}</p>
                <p class="red">{{$match['ascore']}}</p>
            </td>
            <td>
                <p>
                    <span>{{$match['up1']}}</span>
                    <span>{{$type == 2 ? $match['middle1'] :\App\Models\Match\Odd::getOddMiddleString($match['middle1'])}}</span>
                    <span>{{$match['down1']}}</span>
                </p>
                <p>
                    <span>{{$match['up2']}}</span>
                    <span>{{$type == 2 ? $match['middle2'] :\App\Models\Match\Odd::getOddMiddleString($match['middle2'])}}</span>
                    <span>{{$match['down2']}}</span>
                </p>
            </td>
            <td>
                <p class="{{$r_class}}">{{$r_cn}}</p>
            </td>
        </tr>
        @endfor
        </tbody>
    </table>
</div>
@endif