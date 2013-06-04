$(function () {

        g.cy.flow = {
            date:'',
            type:'', //upstream or downstream
            table:'',
            by:'', //sessionid or table
            url:''
        };

        g.cy.init('cytoweb_container', {});


        var flow = g.cy.flow;
        var opts = g.cy.theme;
        var vis = g.cy.vis;


        vis.complete(function () {
            addMenu();
            addVisEvent();
        });

        function addMenu() {
            vis.addContextMenuItem("Show SQL", "nodes", function (evt) {
                var rootNode = evt.target;
                var url = g.buildURL('sql.php', {date:flow.date, sessionid:rootNode.data.id});
                window.open(url, "" + new Date().getTime());
            });
            vis.addContextMenuItem("Dive into", 'nodes', function (evt) {
                var rootNode = evt.target;
                var id = rootNode.data.id;
                var url = g.buildURL('single_flow.php', {sessionid:id, date:flow.date, type:flow.type});
                window.open(url, "" + new Date().getTime());
            });

            vis.addContextMenuItem("Show details", 'nodes', function (evt) {
                var rootNode = evt.target;
                showNodeDetails(rootNode.data);
            })
        }

        function addVisEvent() {
            vis.addListener("dblclick", 'nodes', function (evt) {
                var node = evt.target;
                showNodeDetails(node.data);
            });

            vis.addListener('select', 'nodes', function (evt) {
                var node = evt.target; //here node is array, very special
                var neibs = vis.firstNeighbors(node);
                var edges = neibs['edges'];
                g.cy.updateVisStylePass(edges, {color:opts.hightlightNeighborEdgesColor}, 'edges');
            });

            vis.addListener('deselect', 'nodes', function (evt) {
                var node = evt.target;    //here node is array too
                var neibs = vis.firstNeighbors(node);
                var edges = neibs['edges'];
                g.cy.updateVisStylePass(edges, { color:g.cy.drawStyle.edges.color}, 'edges');
            });
        }

        function setCyDataFlow(date, type, by, table, root) {
            $.extend(flow, {
                date:date,
                type:type,
                by:by,
                table:table,
                root:root
            });

            if (by == 'sessionid') {
                flow.url = g.buildURL('single_flow.php', {sessionid:root, date:date, type:type});
            }
            if (by == 'table') {
                flow.url = g.buildURL('single_flow.php', {table:table, date:date, type:type});
            }
            g.cy.vis.url = flow.url;
        }

        function showNodeDetails(data) {
            var tables = data.tables.replace(/\|/img, " , ");
            tables = tables.substr(0, tables.length - 2);
            if (data.starttime) {
                var html = addKeyValuePairs({'Tables':tables,
                    'Username':data.username,
                    'CPU':data.cpu,
                    'Start time':data.starttime,
                    'End Time':data.lastresptime
                });

                $.powertag.msg({msg:html, width:opts.detailBoxWidth, height:opts.detailBoxHeight});
            } else {
                var html = addKeyValuePairs({'Tables':tables});
                $.powertag.msg({msg:html, width:opts.detailBoxWidth, height:opts.detailBoxHeight});
            }

            function addKeyValuePairs(pairs) {
                var html = '';
                for (var key in pairs) {
                    var value = pairs[key];
                    html += "<p><span class='dialog_key'>" + key + " : </span><span class='dialog_value'>" + value + "</span></p>";
                }
                return  html;
            }
        }


        function showFlow(table, date, type) {
            $.powertag.showWaitStatus();
            $.powertag.api('flow', 'graph', {table:table, date:date, type:type}, function (data) {
                var graph = data["graph"];
                var root = data['root'];
                setCyDataFlow(date, type, 'table', table, root);
                drawWithData(root, graph);
            }, null, function () {
                $.powertag.clearWaitStatus();
            });
        }

        flow.showFlow = showFlow;

        function showFlowById(id, date, type) {
            $.powertag.showWaitStatus();
            $.powertag.api('flow', 'graph', {sessionid:id, date:date, type:type}, function (data) {
                var graph = data["graph"];
                var root = data['root'];
                setCyDataFlow(date, type, 'sessionid', null, root);
                drawWithData(root, graph);
            }, null, function () {
                $.powertag.clearWaitStatus();
            });
        }

        flow.showFlowById = showFlowById;


        function drawWithData(root, data) {

            for (var i in data.nodes) {
                data.nodes[i] = setNodeLabel(data.nodes[i]);
            }
            var points = g.tree.getTreePoints(root, data);
            if (flow.type == 'downstream') {
                data['edges'] = revertRelation(data['edges']);
            }
            var netw = g.cy.buildNetwork(data);

            g.cy.drawNetwork(netw, points);
        }

        function revertRelation(edges) {
            var new_edges = [];
            for (var i in edges) {
                new_edges.push({id:edges[i]['id'], target:edges[i]['source'], source:edges[i]['target']});
            }

            return new_edges;
        }

        function setNodeLabel(node) {
            node.label = node.tables.replace(/\|/img, "\n").replace(/\./img, "\n");
            if (node.starttime && node.lastresptime) {
                node.label += node.starttime + "---" + node.lastresptime;
            }
            if (node.username != undefined) {
                node.label += "\n";
                node.label += node.username;
                return node;

            }

            return node;
        }
    }
);
