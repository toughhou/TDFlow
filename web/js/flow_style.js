$(function () {

    g.cy.buildNetwork = function (data) {
        var network = {
            dataSchema:{
                nodes:[
                    {
                        name:"label",
                        type:"string"
                    },
                    {
                        name:"cpu",
                        type:"float"
                    },
                    {
                        name:"starttime",
                        type:"string"
                    },
                    {
                        name:"lastresptime",
                        type:"string"
                    },
                    {
                        name:"tables",
                        type:"string"
                    },

                    {
                        name:"username",
                        type:"string"
                    }
                ]
            },
            data:data
        };
        return network;
    };

    g.cy.drawNetwork = function (network, points) {
        var drawStyle = {
            global:{
                backgroundColor:"#000"
            },
            nodes:{
                shape:"ROUNDRECT",
                width:170,
                height:85,
                opacity:0.7,
                labelFontSize:10,
                labelFontColor:"#ffffff",
                color:"#6666ee"
            },
            edges:{
                targetArrowShape:"DELTA",
                width:5,
                color:"#dddd00",
                style:"SOLID"
            }
        };

        g.cy.drawStyle = drawStyle;

        var draw_options = {

            network:network,
            panZoomControlVisible:true,
            layout:{
                name:"Preset",
                options:{
                    fitToScreen:true,
                    points:points
                }
            },
            visualStyle:drawStyle
        };

        g.cy.vis.draw(draw_options);
    };

    g.cy.theme = {
        hightlightNeighborEdgesColor:'#ff0000',
        detailBoxWidth:800,
        detailBoxHeight:300
    };

});
