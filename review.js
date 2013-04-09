jQuery(document).ready(function($) {
    $('.ratings_stars').hover(
        function() {  
            $(this).prevAll().andSelf().addClass('ratings_over');
        }, 
        function() {  
            $(this).prevAll().andSelf().removeClass('ratings_over'); 
        }  
    );

    $('.ratings_stars').on('click', function() {
        var stars = $(this).data('stars');
        $(this).prevAll().andSelf().removeClass('ratings_over');
        $(this).nextAll().removeClass('ratings_click');
        $(this).prevAll().andSelf().addClass('ratings_click');
        $("input#stars").attr('value', stars);
    })
});