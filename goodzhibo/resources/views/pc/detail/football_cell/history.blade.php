<?php
$hLeagueName = $match['hLeagueName'];
$aLeagueName = $match['aLeagueName'];
$hLeagueRank = $match['hLeagueRank'];
$aLeagueRank = $match['aLeagueRank'];
?>
@if(isset($data['home']) || isset($data['away']))
    <div class="title">
        <p>近期战绩</p>
    </div>
    @if(isset($data['home']))
        @component("pc.detail.football_cell.history_item",
        [
        'data'=>$data['home'],
        'name'=>$match['hname'],
        'hid'=>$match['hid'],
        'key'=>'H',
        'league'=>$hLeagueName,
        'rank'=>$hLeagueRank])
        @endcomponent
    @endif
    @if(isset($data['away']))
        @component("pc.detail.football_cell.history_item",
        [
        'data'=>$data['away'],
        'name'=>$match['aname'],
        'hid'=>$match['aid'],
        'key'=>'A',
        'league'=>$aLeagueName,
        'rank'=>$aLeagueRank])
        @endcomponent
    @endif
@endif