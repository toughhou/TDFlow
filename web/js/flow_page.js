$(function () {


    $("#search_button").click(function () {
        var table = $.trim($("#table_input").val());
        if (g.empty(table)) {
            $.powertag.alert('Table cannot be empty!');
            return false;
        }
        var date = $.trim($("#date_select").val());
        var type = $.trim($("form input[name=type_select]:checked:radio").val());
        g.cy.flow.showFlow(table, date, type);
    });


});