$(function () {

    var WAIT_AREA_ID = 'powertag_waitarea';
    var MSG_AREA_ID = 'powertag_dialog_area';
    var WAIT_IMG_ID = 'powertag_wait_img';

    $.powertag = {};

    $("input[component=datepicker]").each(function () {
        $(this).datepicker({
            showAnim:'fadeIn',
            dateFormat:'yy-mm-dd'
        });
        if ($(this).attr("default")) {
            var day = parseInt($(this).attr("default"));
            var newDate = new Date().getTime() + day * 24 * 3600 * 1000;
            var that = new Date(newDate);
            that = formatDate(that);
            $(this).val(that);
        }
    });

    $("input[component=autocomplete]").each(function () {
        var api = $(this).attr("api");
        $(this).autocomplete({
            source:api
        });
    });

    function formatDate(date) {
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        if (("" + month).length == 1) {
            month = "0" + month;
        }
        var day = date.getDate();
        if (("" + day).length == 1) {
            day = "0" + day;
        }
        return year + '-' + month + "-" + day;
    }

    function wordWrap(txt, textlength) {
        var strText = txt;
        var tem = "";
        while (strText.length > textlength) {
            tem += strText.substr(0, textlength) + "<br/>";
            strText = strText.substr(textlength, strText.length);
        }
        tem += strText;
        return tem;
    }

    $.powertag.msg = function (opt) {
        if ($.type(opt) === 'string') {
            opt = {msg:opt};
        }
        powertagMsgHelper(opt, 'message', '');
    };

    $.powertag.alert = function (opt) {

        powertagMsgHelper(opt, 'alert', 'ALERT!');
    };

    function powertagMsgHelper(opt, type, title) {
        $("#" + MSG_AREA_ID).remove();
        var width = opt.width;
        var height = opt.height;
        var msg = opt.msg;
        var cut = opt.cut;
        width = width ? width : 300;
        height = height ? height : 150;
        $("<div id='" + MSG_AREA_ID + "'></div>").appendTo('body');
        if (cut) {
            var txt = wordWrap(msg, cut);
            $('#' + MSG_AREA_ID).html(txt);
        }
        $("#" + MSG_AREA_ID).html(msg).dialog(
            {
                width:width,
                height:height,
                title:title,
                dialogClass:'powertag_' + type,
                close:function () {
                    $("#" + MSG_AREA_ID).remove();
                }
            }
        );

    }


    $.powertag.showWaitStatus = function () {
        $.powertag.clearWaitStatus();
        var src = $("#" + WAIT_IMG_ID).attr('src');
        if (src) {
            var sWidth = screen.width;
            var sHeight = screen.height;
            $("<div id='" + WAIT_AREA_ID + "' ><img/></div>")
                .appendTo('body')
                .height(sHeight)
                .width(sWidth)
                .css({'position':'absolute', 'left':0, 'top':0, 'background-color':'#000', 'opacity':0.7})
                .find("img")
                .attr('src', src)
                .css("margin-left", sWidth / 2 - 30)
                .css("margin-top", 200);
        }
    };

    $.powertag.clearWaitStatus = function () {
        $("#" + WAIT_AREA_ID).remove();
    };

    $.powertag.api = function (name, cat, data, successFunc, exFunc, allFunc) {
        if (!exFunc) {
            exFunc = function (res) {
                $.powertag.alert({msg:res});
            }
        }

        if (!allFunc) {
            allFunc = function () {
            }
        }

        for (var i in data) {
            if ($.type(data[i]) == 'object' || $.type(data[i]) == 'array') {
                data[i] = $.toJSON(data[i]);
            }
        }

        var responsefunc = function (ret) {
            try {
                ret = $.trim(ret);
                ret = eval("(" + ret + ")");
            } catch (e) {
                exFunc("Broken response.");
                allFunc();
            }
            var code = ret.code;
            var data = ret.data;
            if (code !== '1') {
                exFunc(data);
            }
            else {
                successFunc(data);
            }
            allFunc(data);
        };
        $.post('api/?api=' + name + '&cat=' + cat, data, responsefunc);
    };
});