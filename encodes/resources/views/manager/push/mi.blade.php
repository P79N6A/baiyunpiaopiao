@extends('layouts.push')
@section('content')
    <form action="/manager/mi/created/" method="post">
        {{ csrf_field() }}
        <div class="form-inline form-group">
            <label for="label-title">名称</label>
            <input name="name" type="text" class="form-control" id="label-title" size="50">
        </div>
        <div class="form-inline form-group">
            <label for="label-size">分辨率</label>
            <select name="size" class="form-control" id="label-size">
                @foreach($sizes as $key=>$size)
                    <option value="{{ $key }}">{{ $size['name'] }}</option>
                @endforeach
            </select>

            <label class="checkbox-logo">
                <input name="logo" type="checkbox" id="checkbox-logo" value="1" checked> 是否显示Logo挡板
            </label>

            <label for="label-logo-text">Logo文案</label>
            <input name="logo_text" type="text" value="{{ $logo_text }}" class="form-control" id="label-logo-text"
                   size="20">

            <label for="label-position">Logo位置</label>
            <select name="logo_position" class="form-control" id="label-position">
                @foreach($logo_position as $key=>$position)
                    <option value="{{ $key }}">{{ $position['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-inline form-group">
            <label for="label-watermark">水印内容</label>
            <input name="watermark" type="text" value="{{ $watermark }}"
                   class="form-control" id="label-watermark" size="70">
            <label for="label-watermark-location">水印位置</label>
            <select id="label-watermark-location" name="location" class="form-control">
                <option value="bottom">下面</option>
                <option value="top">上面</option>
            </select>
            {{--<label for="label-fontsize">字体大小</label>--}}
            {{--<input name="fontsize" type="text" value="{{ $fontsize }}" class="form-control" id="label-fontsize" size="4">--}}
        </div>
        <div class="form-inline form-group">
            <label for="label-resource">源地址</label>
            <input name="input" type="text" class="form-control" id="label-resource" size="120">
        </div>
        <div class="form-inline form-group">
            <label for="label-referer">Referer(Http源)</label>
            <input name="referer" type="text" class="form-control" id="label-referer" size="40">
            <label for="label-header1">Header1(Http源)</label>
            <input name="header1" type="text" class="form-control" id="label-header1"size="40">
        </div>
        <div class="form-inline form-group">
            <label for="label-header2">Header2(Http源)</label>
            <input name="header2" type="text" class="form-control" id="label-header2" size="40">
            <label for="label-header3">Header3(Http源)</label>
            <input name="header3" type="text" class="form-control" id="label-header3" size="40">
        </div>
        <div class="form-inline form-group">
            <label for="label-channel">推流地址</label>
            <select name="channel" class="form-control" id="label-channel">
                @foreach($channels as $channel)
                    @if(!$ets->contains('channel',$channel))
                        <option value="{{ $channel }}">{{ explode('?',$channel)[0] }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">新建转码</button>
    </form>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <td>名称</td>
                <td>直播间</td>
                <td>地址</td>
                <td>开始时间</td>
                <td>状态</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            @foreach($ets as $et)
                <tr>
                    <td width="15%">{{ $et->name }}</td>
                    <td>{{ $et->channel }}</td>
                    <td width="50%">
                        <textarea rows="6" style="width:100%;" readonly>{{ $et->out }}</textarea>
                    </td>
                    <td>
                        {{ substr($et->created_at,5,11) }}
                    </td>
                    <td>
                        <label class="label label-{{ $et->status >= 1?'success':'danger' }}">{{ $et->status >= 1?'正常':'停止' }}</label>
                    </td>
                    <td>
                        <a class="btn btn-xs btn-danger" href="javascript:if(confirm('确认删除')) location.href='/manager/mi/stop/{{ $et->id }}'">停止</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection