$(document).ready(function(){

    var chips = '';

    // Setup Elasticsearch
    var client = new $.es.Client({
        hosts: 'http://localhost:9200'
    });

    document.querySelector('#search-results').style.width = document.querySelector('#search-field').offsetWidth+'px';

    // Find all ticked checkboxes for producing chips
    if($('input:checked').length > 0) chips += 'Filtered by: ';
    $('input:checked').each(function(){
        if($(this).attr('data-type')){
            chips +=
                '<div class="chip" style="text-transform:capitalize;">'+
                    'Type: '+$(this).attr('data-type')+
                    '<i class="close material-icons" data-checkbox-id="'+$(this).attr('id')+'">close</i>'+
                '</div> ';
        }
        else if ($(this).attr('data-breed')) {
            chips +=
                '<div class="chip" style="text-transform:capitalize;">'+
                    'Breed: '+$(this).attr('data-breed')+
                    '<i class="close material-icons" data-checkbox-id="'+$(this).attr('id')+'">close</i>'+
                '</div> ';
        }

    });

    // Append chip to #chip-container
    $('#chip-container').append(chips);

    // For Filter Container Pushpin
    $('#filter-container #collapsible-container').pushpin({
        top: $('#filter-container').offset().top,
        offset: 135
    });

    $("input#search").keydown(function(e){
        // Perform GET request upon pressing the Enter key
        // or fetch suggestions from Elastic search
        // and output it on search results
        if(e.which == 13) {
            e.preventDefault();
            filter.apply();
        }
        else{
            setTimeout(function(){
                searchPhrase = document.querySelector('input#search').value;

                // Execute of searchPhrase is not empty
                if(searchPhrase){

                    // Query on Elasticsearch search engine
                    client.search({
                        index: 'swinecart',
                        type: 'products',
                        body:{
                            "_source": "output",
                            "suggest": {
                                "productSuggest": {
                                    "prefix": searchPhrase,
                                    "completion": {
                                        "field": "suggest",
                                        "fuzzy": {
                                        	"fuzziness": 2
                                        }
                                    }
                                }
                            }
                        }
                    }).then(function(response){
                        var options = response.suggest.productSuggest[0].options;
                        var searchResultsTop = '';
                        var searchResultsBot = '';

                        if(options.length > 0){

                            for (var i = 0; i < 3; i++) {
                                searchResultsTop += '<li class="search-item">' +
                                    options[i]._source.output[1] +
                                    '</li>';

                                searchResultsBot += '<li class="search-item">' +
                                    options[i]._source.output[0] +
                                    '</li>';
                            }

                            document.querySelector("#search-results ul").innerHTML = searchResultsTop + searchResultsBot;

                            $("#search-results").show();
                        }

                    }, function(error){
                        console.trace(error.message);
                    });
                }

                else $("#search-results").hide();

            }, 0);

        }
    });

    // Redirect to designated link upon checkbox and select value change
    $("#filter-container input[type=checkbox], select").change(function(){
        filter.apply();
    });

    // Redirect to designated link upon removing chips
    $(".chip i.material-icons").click(function(e){
        e.preventDefault();
        $("#"+$(this).attr('data-checkbox-id')).prop('checked', false);
        filter.apply();
    });

    $('body').on('click', 'li.search-item', function(e){
        e.preventDefault();

        var searchInput = document.querySelector('input#search');
        searchInput.value = $(this).html();

        $("#search-results").hide();
        filter.apply();
    });

});
