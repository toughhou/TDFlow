<?php require './header.part.php'
?>
<h1>TdFlow</h1>

<div class="row-fluid">
    <div id="nav" class="span3">
        <form>
            <fieldset>
                <legend>Search</legend>
                <label class="big">Table: </label>
                <input id="table_input" type="text" placeholder="some table..." component="autocomplete"
                       api="api?api=flow&cat=lkp_tables&limit=20"/>
                <label class="big">Date</label>
                <input id="date_select" type="text" class="input-medium" component="datepicker" default="-1"/>

                <label class="radio">
                    <input type="radio" name="type_select" value="upstream" checked>
                    Upstream
                </label>
                <label class="radio">
                    <input type="radio" name="type_select" value="downstream">
                    Downstream
                </label>
                <a href="#" class="btn btn-primary" id="search_button">Search
                </a>
            </fieldset>
        </form>
    </div>
    <div id="content" class="span9">
        <div id="cytoweb_container"></div>
    </div>

</div>


<?php require './script.part.php'
?>
<script type="text/javascript" src="js/flow_page.js"></script>
<?php require './footer.part.php'
?> 