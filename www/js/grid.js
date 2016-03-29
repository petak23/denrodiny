$(function(){
    $.ajaxSetup({
        success: function(data){
            if(data.redirect){
                $.get(data.redirect);
            }
            if(data.snippets){
                for (var snippet in data.snippets){
                    $("#"+snippet).html(data.snippets[snippet]);
                }
            }
        }
    });

    //$(".grid-flash-hide").delegate("click", function(){ - povodne
    $(document).on("click", ".grid-flash-hide", function(){
        $(this).parent().parent().fadeOut(300);
    });

//    $(".grid-select-all").delegate("click", function(){ - povodne
    $(document).on("click", ".grid-select-all", function(){
        var checkboxes =  $(this).parents("thead").siblings("tbody").children("tr:not(.grid-subgrid-row)").find("td input:checkbox.grid-action-checkbox");
        if($(this).is(":checked")){
            $(checkboxes).attr("checked", "checked");
        }else{
            $(checkboxes).removeAttr("checked");
        }
    });

//    $('.grid a.grid-ajax:not(.grid-confirm)').delegate('click', function (event) { - povodne
    $(document).on("click", '.grid a.grid-ajax:not(.grid-confirm)', function (event) {
        event.preventDefault();
        $.get(this.href);
    });

//    $('.grid a.grid-confirm:not(.grid-ajax)').delegate('click', function (event) { - povodne
    $(document).on("click", '.grid a.grid-confirm:not(.grid-ajax)', function (event) {
        var answer = confirm($(this).data("grid-confirm"));
        return answer;
    });

//    $('.grid a.grid-confirm.grid-ajax').delegate('click', function (event) { - povodne
    $(document).on("click", '.grid a.grid-confirm.grid-ajax', function (event) {
        event.preventDefault();
        var answer = confirm($(this).data("grid-confirm"));
        if(answer){
            $.get(this.href);
        }
    });

//    $(".grid-gridForm").find("input[type=submit]").delegate("click", function(){ - povodne
    var pomoc = $(".grid-gridForm").find("input[type=submit]");
    $(document).on("click", pomoc,  function(){
        $(this).addClass("grid-gridForm-clickedSubmit");
    });

//    $(".grid-gridForm").delegate("submit", function(event){ - povodne
    $(document).on("submit", ".grid-gridForm", function(event){
        event.preventDefault();
        var button = $(".grid-gridForm-clickedSubmit");
        var selectName = $(button).data("select");
        var selected = $("select[name=\""+selectName+"\"] option:selected").data('grid-confirm');
        if(selected){
            var answer = confirm(selected);
            if(answer){
                $.post(this.action, $(this).serialize()+"&"+$(button).attr("name")+"="+$(button).val());
                $(button).removeClass("grid-gridForm-clickedSubmit");
            }
        }else{
            $.post(this.action, $(this).serialize()+"&"+$(button).attr("name")+"="+$(button).val());
            $(button).removeClass("grid-gridForm-clickedSubmit");
        }
    });

//    $(".grid-autocomplete").delegate('keydown.autocomplete', function(){ - povodne
    $(document).on('keydown.autocomplete', ".grid-autocomplete", function(){
        var gridName = $(this).data("gridname");
        var column = $(this).data("column");
        var link = $(this).data("link");
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: link,
                    data: gridName+'-term='+request.term+'&'+gridName+'-column='+column,
                    dataType: "json",
                    method: "post",
                    success: function(data) {
                        response(data.payload);
                    }
                });
            },
            delay: 100,
            open: function() { $('.ui-menu').width($(this).width()) }
        });
    });

//    $(".grid-changeperpage").delegate("change", function(){ - povodne
    $(document).on("change", ".grid-changeperpage", function(){
        $.get($(this).data("link"), $(this).data("gridname")+"-perPage="+$(this).val());
    });

    function hidePerPageSubmit()
    {
        $(".grid-perpagesubmit").hide();
    }
    hidePerPageSubmit();

    function setDatepicker()
    {
        $.datepicker.regional['sk'] = {
          closeText: 'Zavrieť',
          prevText: '&#x3c;Predchádzajúci',
          nextText: 'Nasledujúci&#x3e;',
          currentText: 'Dnes',
          monthNames: ['Január','Február','Marec','Apríl','Máj','Jún',
          'Júl','August','September','Október','November','December'],
          monthNamesShort: ['Jan','Feb','Mar','Apr','Máj','Jún',
          'Júl','Aug','Sep','Okt','Nov','Dec'],
          dayNames: ['Nedeľa','Pondelok','Utorok','Streda','Štvrtok','Piatok','Sobota'],
          dayNamesShort: ['Ned','Pon','Uto','Str','Štv','Pia','Sob'],
          dayNamesMin: ['Ne','Po','Ut','St','Št','Pia','So'],
          weekHeader: 'Ty',
          dateFormat: 'dd.mm.yy',
          firstDay: 1,
          isRTL: false,
          showMonthAfterYear: false,
          yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['sk']);


        $(".grid-datepicker").each(function(){
            if(($(this).val() != "")){
                var date = $.datepicker.formatDate('yy-mm-dd', new Date($(this).val()));
            }
            $(this).datepicker();
            $(this).datepicker({ constrainInput: false});
        });
    }
    setDatepicker();

    $(this).ajaxStop(function(){
        setDatepicker();
        hidePerPageSubmit();
    });

//    $("input.grid-editable").delegate("keypress", function(e) { - povodne
    $(document).on("keypress", "input.grid-editable", function(e) {
        if (e.keyCode == '13') {
            e.preventDefault();
            $("input[type=submit].grid-editable").click();
        }
    });

//    $("table.grid tbody tr:not(.grid-subgrid-row) td.grid-data-cell").delegate("dblclick", function(e) { - povodne
    $(document).on("dblclick", "table.grid tbody tr:not(.grid-subgrid-row) td.grid-data-cell", function(e) {
        $(this).parent().find("a.grid-editable:first").click();
    });
});