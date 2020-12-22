$(document).ready(function() {

    var $eventNextSelect = $('.js-event-next');
    var $eventPrevSelect = $('.js-event-prev');
    var $currentEvent = $('.js-event-current');
    var $eventId = $currentEvent.data('event-id');

    $eventNextSelect.on('change', function(e) {

        $.ajax({
            url: $eventNextSelect.data('event-prev-url'),
            data: {
                event: $eventNextSelect.val(),
                current: $eventId
            },
            success: function (id) {

                if (!id['id']) {
                    return;
                }
                $eventPrevSelect.val(id['id']).prop('selected', true);
            }
        });
    });

    $eventPrevSelect.on('change', function(e) {

        $.ajax({
            url: $eventPrevSelect.data('event-next-url'),
            data: {
                event: $eventPrevSelect.val(),
                current: $eventId
            },
            success: function (id) {

                if (!id['id']) {
                    return;
                }
                $eventNextSelect.val(id['id']).prop('selected', true);
            }
        });
    });
});