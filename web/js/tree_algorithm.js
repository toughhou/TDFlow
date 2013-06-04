$(function () {

    g.tree = {};

    var rootX = 450;
    var rootY = 600;
    var offsetY = 150;
    var nodeWidth = 300;
    var nodesPos = [];


    g.tree.getTreePoints = function (root, data, nodeBetweenX, nodeBetweenY) {
        if (!g.empty(nodeBetweenX)) nodeWidth = nodeBetweenX;
        if (!g.empty(nodeBetweenY)) offsetY = nodeBetweenY;

        var pts = setAndGetNodesPos(data, root);
        if (pts.length <= 0) {
            return false;
        }
        return pts;
    }


    function getChildren(fatherID, edges) {
        var c = [];
        var i, j;
        for (i = 0; i < edges.length; i++) {
            if (edges[i].target == fatherID)// find his children
            {

                for (j = 0; j < nodesPos.length; j++)// find children's node in
                {

                    if (nodesPos[j].id == edges[i].source) {
                        c.push(nodesPos[j]);
                        break;
                    }
                }
            }
        }
        return c;
    }

    function getLevelNodes(level) {
        var n = [];
        for (var i = 0; i < nodesPos.length; i++) {
            if (nodesPos[i].level == level)
                n.push(nodesPos[i]);
        }
        return n;
    }

    function setNodesLevel(data, rootID) {
        nodesPos = [];


        var rootObj = {
            id:rootID,
            posX:rootX,
            posY:rootY,
            level:1,
            IsPos:0
        };
        nodesPos.push(rootObj);
        for (var i = 0; i < data.nodes.length; i++) {
            if (data.nodes[i].id != rootID) {
                nodesPos.push({
                    id:data.nodes[i].id,
                    posX:rootX,
                    posY:rootY,
                    level:0,
                    IsPos:0
                });
            }
        }
        var level = 1;
        var j, m, n;
        while (getLevelNodes(level).length > 0) {
            var sameLevelNodes = getLevelNodes(level);
            for (j = 0; j < sameLevelNodes.length; j++) {
                var children = getChildren(sameLevelNodes[j].id, data.edges);
                for (n = 0; n < children.length; n++) {
                    for (m = 0; m < nodesPos.length; m++) {
                        if (nodesPos[m].id == children[n].id) {
                            nodesPos[m].level = (level + 1);
                            break;
                        }
                    }

                }
            }
            level++;
        }
    }

    function setPos(Obj, flag, data) {
        var children = getChildren(Obj.id, data.edges);
        for (var y = 0; y < children.length; y++) {
            if ((children[y].level != Obj.level + 1) || (children[y].IsPos == 1)) {
                children.splice(y, 1);
                y--;
            }
        }
        if (children.length <= 0)
            return;
        else {
        }
        var totalWidth = nodeWidth * children.length;
        var left = Obj.posX - (children.length * 0.5 - 0.5) * nodeWidth;
        var right = left + totalWidth;
        var sameLevelNodes = getLevelNodes(Obj.level + 1);

        // adjust the left sides
        if (flag == 1) {
            for (var i = 0; i < sameLevelNodes.length; i++) {
                if (sameLevelNodes[i].IsPos != 0 && left < (sameLevelNodes[i].posX + nodeWidth)) {
                    left = sameLevelNodes[i].posX + nodeWidth;
                    right = left + totalWidth;
                }
            }
        }
        // adjust the right sides
        if (flag == 2) {
            for (var i = 0; i < sameLevelNodes.length; i++) {
                if (sameLevelNodes[i].IsPos != 0 && sameLevelNodes[i].posX < right) {
                    right = sameLevelNodes[i].posX;
                    left = right - totalWidth;
                }
            }
        }
        // set this level node pos
        for (var q = 0; q < children.length; q++) {
            for (var i = 0; i < nodesPos.length; i++) {
                if (children[q].id == nodesPos[i].id && nodesPos[i].IsPos == 0) {
                    nodesPos[i].posX = left + q * nodeWidth;
                    nodesPos[i].posY = Obj.posY - offsetY;
                    nodesPos[i].IsPos = 1;
                }
            }
        }

    }

    function setAndGetNodesPos(data, rootID) {
        var points = [];
        var isSucc = setNodesLevel(data, rootID);
        // set nodes level and put them into
        // nodesPos
        if (isSucc == false)
            return points;

        var level = 1;
        var needToPos = getLevelNodes(level);
        while (needToPos.length > 0) {
            needToPos = bubbleSort(needToPos);
            // sort the nodes
            var midNodes = needToPos[Math.floor(needToPos.length / 2)];
            setPos(midNodes, 0, data);

            for (var j = Math.floor(needToPos.length / 2) - 1; j >= 0; j--) {
                setPos(needToPos[j], 2, data);
            }
            for (var k = Math.floor(needToPos.length / 2) + 1; k < needToPos.length; k++) {
                setPos(needToPos[k], 1, data);
            }
            level++;
            needToPos = getLevelNodes(level);
        }

        for (var i = 0; i < nodesPos.length; i++) {
            points.push({
                id:nodesPos[i].id,
                x:nodesPos[i].posX,
                y:nodesPos[i].posY
            });
        }
        return points;
    }

    function bubbleSort(nodes) {
        for (var t = 0; t < nodes.length - 1; t++) {
            var flag = false;
            for (var n = 1; n < nodes.length - t; n++) {
                if (nodes[n].posX < nodes[n - 1].posX) {
                    var temp = nodes[n - 1];
                    nodes[n - 1] = nodes[n];
                    nodes[n] = temp;
                    flag = true;
                }
            }
            if (flag == false)
                break;
        }
        return nodes;
    }


})
