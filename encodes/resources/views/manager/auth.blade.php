<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <title>登陆</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- Bootstrap -->
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap-theme.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/toastr.js/latest/css/toastr.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/bootstrap/3.3.7/fonts/glyphicons-halflings-regular.svg" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">登陆</h1>
            <div class="row placeholders"></div>

            <div id="myAlert" class="alert alert-danger" @if(!isset($err_msg)) style="display: none;" @endif>
                <a id="close" class="close">&times;</a>
                <strong id="alertMsg">{{$err_msg or ''}}</strong>
            </div>

            <div style="width: 500px;margin:0 auto">
                <form class="form-horizontal" role="form" method="POST" onsubmit="return verifyForm(this);">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon "><span class="glyphicon glyphicon-user"></span></div>
                            <input class="form-control" type="text" value="{{old("account")}}" name="account"
                                   placeholder="账号">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon "><span class="glyphicon glyphicon-lock"></span></div>
                            <input type="password" class="form-control" name="password" placeholder="密码">
                        </div>
                    </div>

                    {{--<div class="form-group" style="text-align: left;">--}}
                    {{--<div class="checkbox">--}}
                    {{--<label><input name="remember" value="on"--}}
                    {{--@if(isset($remember) && $remember == 'on') checked @endif type="checkbox">记住登录状态</label>--}}
                    {{--</div>--}}
                    {{--</div>--}}

                    <div class="form-group">
                        <div style="text-align: center;">
                            <button type="submit" class="btn btn-default">登录</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
<script src="//cdn.bootcss.com/jquery/2.1.4/jquery.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.js"></script>
<script src="//cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
<script src="//cdn.bootcss.com/js-sha1/0.6.0/sha1.js"></script>
<script>

    function verifyForm(form) {
        if (form.account.value === '' || form.password.value === '') {
            $('#alertMsg').html('用户名或者密码不能为空');
            $("#myAlert").show();
            return false;
        }
        form.password.value = sha1(form.password.value);
        return true;
    }

    @if(!empty(session('error')))
        $('#alertMsg').html('{{session('error')}}');
    $("#myAlert").show();
    @endif
</script>
<script>
    $(function () {
        $("#close").bind('click', function () {
            $(this).parent().hide()
        });
    });
</script>
</html>
