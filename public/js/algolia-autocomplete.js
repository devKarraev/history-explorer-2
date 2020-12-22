$(document).ready(function() {

    $('.js-person-autocomplete').each(function() {

        var autocompleteUrl = $(this).data('autocomplete-url');

        $(this).autocomplete({hint: false}, [
            {
                source: function(query, cb) {

                    $.ajax({
                        url: autocompleteUrl + '?query=' + query
                    }).then(function(data) {
                        cb(data.persons);
                    });
                },

                displayKey: 'name',
                debounce: 500 // only request every 1/2 second
            }
        ])
    });

    $('.js-reference-autocomplete').each(function() {

        var autocompleteUrl = $(this).data('autocomplete-url');

        $(this).autocomplete({hint: false}, [
            {
                source: function(query, cb) {

                    $.ajax({
                        url: autocompleteUrl + '?query=' + query
                    }).then(function(data) {
                        cb(data.books);
                    });
                },
                displayKey: 'name',
                debounce: 500 // only request every 1/2 second
            }
        ])
    });
});