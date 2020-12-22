$(document).ready(function(){
    $("#referenceinput").on('change copy paste cut', function() {
        var autocompleteUrl = $(this).data('autocomplete-url');

        $.ajax({
            url: autocompleteUrl + '?query=' +$(this).val()
        }).then(function(data) {
            console.log(data);
            if(data.fullReference) {
                $("#add_ref_btn").removeAttr("disabled");
                $("#submit_param").val(data.fullReference);

            } else {
                //$("#add_ref_btn").addClass("disabled");
                $("#add_ref_btn").attr("disabled", "disabled");
            }
        });
    });

    $('#referenceinput').focusout(function(source) {
        var autocompleteUrl = $(this).data('autocomplete-url');

        $.ajax({
            url: autocompleteUrl + '?query=' +$(this).val()
        }).then(function(data) {
            if(data.fullReference) {
                $("#add_ref_btn").removeAttr("disabled");
                $("#submit_param").val(data.fullReference);
                console.log(data.fullReference);

            } else {
                $("#add_ref_btn").attr("disabled", "disabled");
            }
        });
    });


    $('#referenceinput').each(function(source) {

        var autocompleteUrl = $(this).data('autocomplete-url');
        $(this).autocomplete({hint: false}, [{

                source: function(query, cb) {

                    $.ajax({
                        url: autocompleteUrl + '?query=' + query
                    }).then(function(data) {

                        cb(data.books, data.fullReference, data.error);

                        if(data.error) {
                            $("#url_error").show();
                            $("#url_error .form-error-message").text(data.error);
                        } else {
                            $("#url_error").hide();
                        }
                        if(data.fullReference) {
                            // $("#add").prop('disabled', false);
                            $("#add_ref_btn").removeAttr("disabled");
                            $("#referenceinput").val(data.fullReference);
                            $("#submit_param").val(data.fullReference);

                        } else {
                            //$("#add").prop('disabled', true);
                            $("#add_ref_btn").attr("disabled", "disabled");
                        }
                    });
                },
                displayKey: 'name',
                debounce: 500 // only request every 1/2 second
            }
        ]);
    });

   /* $('#chapterinput').change(function(){

        console.log($(this).val());
        var versesUrl = $(this).data('chapter_verses_url');
        $.ajax({
            url: versesUrl + '?query=' + query
        }).then(function(data) {

            cb(data.books);
            if(data.books.length == 1) {

                $("#chapterinput").attr({
                    "max" : (data.books[0].chapters)
                });
                $("#chapterinput").show();
                $("#referenceinput").val(data.books[0].name);
            } else {
                $("#chapterinput").hide();
            }
        });

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
    });*/

    $('.custom-file-input').on('change', function (event) {

        var inputFile = event.currentTarget;
        readURL(inputFile);
        /*$(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);
        $(inputFile).parent().parent()
            .find('.show-article-img')
            .attr('src', inputFile.files[0].name);*/
    });

});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        console.log(input.files);
        reader.onload = function (e) {
            $('.show-article-img')
                .attr('src', e.target.result)
                .width(150)
                .height(200);
        };

        reader.readAsDataURL(input.files[0]);
    }
}
