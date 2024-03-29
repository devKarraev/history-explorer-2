$(document).ready(function(){
    var i=1;
    $('#add').click(function(){
        i++;
        $('#dynamic_field').append(
            '<tr id="row'+i+'">' +
            '<td><input type="text" name="name[]" placeholder="Enter your Name" class="form-control name_list" />' +
            '</td>' +
            '<td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button>' +
            '</td>' +
            '</tr>');
    });

    $('.name_list').each(function() {


        var autocompleteUrl = $(this).data('autocomplete-url');
        alert(autocompleteUrl);
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

    $(document).on('click', '.btn_remove', function(){
        var button_id = $(this).attr("id");
        $('#row'+button_id+'').remove();
    });
    $('#submit').click(function(){
        $.ajax({
            url:"name.php",
            method:"POST",
            data:$('#add_name').serialize(),
            success:function(data)
            {
                alert(data);
                $('#add_name')[0].reset();
            }
        });
    });
});
