var g = {
};

$(function () {


    g.queryStrings = function () {//get url querystring
        var params = document.location.search, reg = /(?:^\?|&)(.*?)=(.*?)(?=&|$)/g, temp, args = {};
        while ((temp = reg.exec(params)) != null) args[temp[1]] = decodeURIComponent(temp[2]);
        return args;
    };


    g.empty = function (mixed_var) {
        var undef, key, i, len;
        var emptyValues = [undef, null, false, 0, "", "0"];

        for (i = 0, len = emptyValues.length; i < len; i++) {
            if (mixed_var === emptyValues[i]) {
                return true;
            }
        }

        if (typeof mixed_var === "object") {
            for (key in mixed_var) {
                return false;
            }
            return true;
        }

        return false;
    };


    g.buildURL = function (site, params) {
        var url = g.dirURL() + site + '?';
        for (var i in params) {
            var v = params[i];
            url += i + "=" + v + '&';
        }
        return url;
    };

    g.dirURL = function () {
        var locHref = location.href;
        var locArray = locHref.split("/");
        delete locArray[locArray.length - 1];
        var dirTxt = locArray.join("/");
        return dirTxt;
    };


});
