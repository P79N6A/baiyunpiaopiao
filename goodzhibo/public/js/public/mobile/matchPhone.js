window.oddIndexGet = false;
window.sameOddGet = false;
window.teamStyleGet = false;
window.cornerGet = false;
function setPage(){
    var Tab = $('#Tab input');
    Tab.change(function(){
        $('.content').css('display','none');
        $('#' + this.value).css('display','');

        if ((document.documentElement.scrollTop || document.body.scrollTop) > 252) {
            document.documentElement.scrollTop = 252;
        }
    })


    var BottomTab = $('.bottom input');
    BottomTab.change(function(){
        $(this).parents('.content').children('.childNode').css('display','none');
        $('#' + this.value).css('display','');
    })

    var BtnClose = $('button.close');
    BtnClose.click(function(){
        if ($(this).parents('.default').attr('close')) {
            $(this).parents('.default').removeAttr('close');
        }else{
            $(this).parents('.default').attr('close','close');
        }
    })

    var Sel = $('select');
    Sel.change(function(){
        $(this).parents('.default').children('table').css('display','none');
        $('#' + $(this).children('option:selected').val()).css('display','');
    })

    var Hale = $('.content input[value=ha],.content input[value=le]');
    Hale.change(function(){
        $(this).parents('.default').attr(this.value,this.checked?1:0)
    })
}

function setHead () {
    var Scroll = getPageScroll()[1];
    $('#Navigation .team').css('opacity',Scroll / 252)
    if (Scroll >= 252) {
        $('#Info').css('margin-bottom','88px');
        $('#Tab').css('position','fixed');
        $('#Tab').css('top','88px');
    }else{
        $('#Info').css('margin-bottom','');
        $('#Tab').css('position','');
        $('#Tab').css('top','');
    }
}

function setCanvas () {
    var Canvas = $('canvas');

    function Circle (value,color,obj) {
        if (value != 0) {
            var ctx = obj.getContext("2d");
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.arc(70, 70, 80, - 0.5 * Math.PI, (2 * value - 0.5) * Math.PI, false);
            ctx.lineTo(70, 70);
            ctx.fillStyle = color;
            ctx.fill();
            ctx.strokeStyle = "white";
            ctx.closePath();
        }
    }

    for (var i = 0; i < Canvas.length; i++) {
        Circle(parseFloat(Canvas[i].getAttribute('value')),Canvas[i].getAttribute('color'),Canvas[i]);
    }
}

function getSameOdd(so_id) {
    Alert('loading', '加载中');
    var sameOddUrl = getCdnUrl('/m/football/detail/same_odd/' + window.startTime + '/' + window.mid + '.html');
    $.ajax({
        'url': sameOddUrl,
        'type': 'get',
        'dataType': 'html',
        'success': function (html) {
            window.sameOddGet = true;
            $("#" + so_id).html(html);
            var BottomTab = $('#' + so_id + ' .bottom input');
            BottomTab.change(function(){
                $(this).parents('.content').children('.childNode').css('display','none');
                $('#' + this.value).css('display','');
            });
            closeLoading();
        },
        "error": function () {
            closeLoading();
        }
    });
}

function getOddIndex(id) {
    Alert('loading', '加载中');
    var oddIndexUrl = getCdnUrl('/m/football/detail/odd_index/' + window.startTime + '/' + window.mid + '.html');
    $.ajax({
        'url': oddIndexUrl,
        'type': 'get',
        'dataType': 'html',
        'success': function (html) {
            window.oddIndexGet = true;
            $("#" + id).html(html);
            var BottomTab = $('#' + id + ' .bottom input');
            BottomTab.change(function(){
                $(this).parents('.content').children('.childNode').css('display','none');
                $('#' + this.value).css('display','');
            });
            closeLoading();
        },
        "error": function () {
            closeLoading();
        }
    });
}

function getTeamStyle(id) {
    Alert('loading', '加载中');
    var teamStyle = getCdnUrl('/m/football/detail/style/' + window.startTime + '/' + window.mid + '.html');
    $.ajax({
        'url': teamStyle,
        'type': 'get',
        'dataType': 'html',
        'success': function (html) {
            window.teamStyleGet = true;
            $("#" + id).find("#Trait").append(html);

            var BtnClose = $("#" + id).find("#Trait").find('button.close');
            BtnClose.click(function(){
                if ($(this).parents('.default').attr('close')) {
                    $(this).parents('.default').removeAttr('close');
                }else{
                    $(this).parents('.default').attr('close','close');
                }
            });
            closeLoading();
        },
        "error": function () {
            closeLoading();
        }
    });
}

function getCorner(id) {
    Alert('loading', '加载中');
    var cornerUrl = getCdnUrl('/m/football/detail/corner/' + window.startTime + '/' + window.mid + '.html');
    $.ajax({
        'url': cornerUrl,
        'type': 'get',
        'dataType': 'html',
        'success': function (html) {
            window.cornerGet = true;
            $("#Team").find("#" + id).html(html);
            setCanvas();

            var Hale = $('.content input[value=ha],.content input[value=le]');
            Hale.change(function(){
                $(this).parents('.default').attr(this.value,this.checked?1:0)
            });

            var BtnClose = $("#Team").find("#" + id).find('button.close');
            BtnClose.click(function(){
                if ($(this).parents('.default').attr('close')) {
                    $(this).parents('.default').removeAttr('close');
                }else{
                    $(this).parents('.default').attr('close','close');
                }
            });
            closeLoading();
        },
        "error": function () {
            closeLoading();
        }
    });
}
function refreshMatch(mid) {
    //刷新比赛信息
    var url = getCdnUrl("/football/change/live.json");
    $.ajax({
        "url": url + "?rd=" + (new Date()).getTime(),
        "dataType": "json",
        "success": function (json) {
            var dataItem = json[mid];
            if (!dataItem) {
                return;
            }
            var scoreItem = $("#Info p.score");
            if (scoreItem) {
                var currentScore = dataItem.score;
                if (currentScore.indexOf('-')) {
                    var scores = currentScore.split('-');
                    if (scores.length == 2) {
                        var hScore = scores[0];
                        var aScore = scores[1];
                        scoreItem.find('span.host').html($.trim(hScore));
                        scoreItem.find('span.away').html($.trim(aScore));
                    }
                }
            }
        },
        "error": function () {

        }
    });
}
function hasLive(mid) {
    //直播刷新
    var url = getCdnUrl('/football/has_live/' + mid + '.json');
    $.ajax({
        "url": url,
        "dataType": "json",
        "success": function (json) {
            if ($('#Info a.live').length > 0) {
                $('#Info a.live')[0].style.display = json['live'] == 0 ? 'none' : '';
            }
        },
        "error": function () {

        }
    });
}

function getCdnUrl(url) {
    if (!window.cdn_url) {
        window.cdn_url = "//static.cdn.vcgood.net";
    }
    var http = location.href.indexOf('https://') != -1 ? 'https:' : 'http:';
    var url = http + window.cdn_url + url;
    return url;
}