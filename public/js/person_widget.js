function refreshPerson(a)
{
    var personUrl = $('#info').data('person_info-url') + '?query=' +a
    console.log(personUrl);
    $.ajax({
        type: "GET",
        url: personUrl,
        data: '',
        cache: false,
        success: function(data){
            $('#info').html(data);
            $('#infoContainer').show();
        },
        error: function(){},
        complete: function(){}
    });
}



