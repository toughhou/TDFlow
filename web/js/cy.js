$(function () {


    g.cy = {
        vis:{
            zoomLevel:0,
            complete:function (func) {
                this.ready(function () {
                    this.zoomToFit();
                    this.panEnabled(true);
                    this.zoomLevel = this.zoom();
                    g.cy.addLinks();
                    g.cy.addSaveButtons(['svg', 'png']);
                    this.removeAllContextMenuItems();

                    $('#cytoweb_container').bind('mousewheel', function (event, delta, deltaX, deltaY) {
                        if (delta > 0) {
                            g.cy.vis.zoomIn();
                        }
                        if (delta < 0) {
                            g.cy.vis.zoomOut();
                        }
                    });

                    $("#action_url").click(function () {
                        $.powertag.msg({msg:g.cy.vis.url, width:600, height:200, cut:55});
                    });


                    $("#full_screen_url").click(function () {
                        window.open(g.cy.vis.url, "" + new Date().getTime());
                    });

                    func.call(this);
                });
            },
            zoomIn:function () {
                if (g.cy.vis.zoomLevel + 0.05 >= 1) {
                    g.cy.vis.zoomLevel = 1;
                } else {
                    g.cy.vis.zoomLevel += 0.05;
                }
                g.cy.vis.zoom(g.cy.vis.zoomLevel);
            },
            zoomOut:function () {
                if (g.cy.vis.zoomLevel - 0.05 <= 0.1) {
                    g.cy.vis.zoomLevel = 0.1
                } else {
                    g.cy.vis.zoomLevel -= 0.05;
                }
                g.cy.vis.zoom(g.cy.vis.zoomLevel);
            }

        },
        canvasId:0,
        defaultOptions:{
            panZoomControlVisible:true,
            nodeLabelsVisible:true,
            edgeLabelsVisible:true,
            nodeTooltipsEnabled:true,
            edgeTooltipsEnabled:true,
            swfPath:"swf/CytoscapeWeb",
            flashInstallerPath:"swf/playerProductInstall"
        },

        init:function (id, opt) {
            var opt = $.extend({}, this.defaultOptions, opt);
            var cytoscape = $("#" + id).cytoscapeweb(opt);
            this.vis = $.extend(cytoscape, this.vis);
            this.canvasId = id;
        },
        createSave:function (fn) {
            var vis = g.cy.vis;
            var binary = true;
            if (fn == "svg")
                binary = false;
            var options = {
                swfPath:"swf/Exporter",
                flashInstallerPath:"swf/playerProductInstall",
                base64:binary,
                data:function () {
                    return vis[fn]();
                },
                fileName:function () {
                    return defaultFileName(fn);
                },
                ready:function () {
                    $("#save_" + fn).trigger("available");
                }
            };

            new org.cytoscapeweb.demo.Exporter("save_" + fn, options);
            function defaultFileName(extension) {
                var d = new Date();
                return "network_" + d.toDateString() + "." + extension;
            }
        },
        addSaveButtons:function (fns) {
            for (var i in fns) {
                $("#save_" + fns[i]).remove();
                $("<div id='save_" + fns[i] + "'></div>").insertBefore("#" + g.cy.canvasId).show();
                g.cy.createSave(fns[i]);
            }
        },
        addLinks:function () {
            $("#action_url").remove();
            $("#full_screen_url").remove();
            $("<a href='#' class='action_link' id='action_url'>URL</a><a href='#' class='action_link' id='full_screen_url'>Full Screen</a>").insertBefore('#' + g.cy.canvasId);
        },
        updateVisStylePass:function (eles, style, type) {
            var bypass = g.cy.vis.visualStyleBypass();
            for (var i in eles) {
                var ele = eles[i];
                g.cy.extendStyle(bypass, type, ele.data.id, style);
            }
            g.cy.vis.visualStyleBypass(bypass);
        },
        extendStyle:function (bypass, type, id, style) {
            if (bypass[type][id] === undefined) {
                bypass[type][id] = {};
            }
            bypass[type][id] = $.extend({}, bypass[type][id], style);

        }
    }
});
;

