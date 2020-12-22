function refreshEvent(a)
{
    var eventUrl = $('#info').data('event_info-url') + '?query=' +a
    console.log("refresh event");
    $.ajax({
        type: "GET",
        url: eventUrl,
        data: '',
        cache: false,
        success: function(data){
            console.log(data);
            $('#info').html(data);
            $('#infoContainer').show();
        },
        error: function(){},
        complete: function(){}
    });
}




