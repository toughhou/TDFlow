<?php

if (empty($_GET['type']) || empty($_GET['date']))
    exit();
?>
<?php require './header.part.php' ?>
<div id="content">
    <div id="cytoweb_container"></div>
</div>
<?php require './script.part.php'
?>

<style>
    #container {
        width: 100%;
        margin-top: 3px;
    }

    #cytoweb_container {
        clear: both;
        width: 100%;
        background: #111;
    }

</style>

<script type="text/javascript">
    $(function () {
        $('#cytoweb_container').css('height', screen.height);
        var req = g.queryStrings();
        var table = $.trim(req['table']);
        var session_id = $.trim(req['sessionid']);
        var date = $.trim(req['date']);
        var type = $.trim(req['type']);

        if (!g.empty(table)) {
            g.cy.flow.showFlow(table, date, type);
        } else if (!g.empty(session_id)) {
            g.cy.flow.showFlowById(session_id, date, type);
        }
    })
</script>
<?php require './footer.part.php'
?>