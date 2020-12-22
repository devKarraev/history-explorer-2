var prevScrollpos = window.pageYOffset;

window.onscroll = function() {
    var currentScrollPos = window.pageYOffset;

    //console.log(currentScrollPos - prevScrollpos);
    if (prevScrollpos > currentScrollPos) {
       $(".navbar").css('top', '0px');
       //$(".footer").css('bottom', '-82px');

    } else {
        $(".navbar").css('top', '-82px');
        //$(".footer").css('bottom', '0px');
    }
    prevScrollpos = currentScrollPos;
}