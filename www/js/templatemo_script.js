var top_menu_height = 0;
jQuery(function($) {
  $(window).load( function() {
    $('.external-link').unbind('click');	
  });
		
  $(document).ready( function() {
    top_menu_height = $('.templatemo-top-menu').height();
    // scroll spy to auto active the nav item
    $('body').scrollspy({ target: '#templatemo-nav-bar', offset: top_menu_height + 10 });
    $('.external-link').unbind('click');

    // scroll to top
    $('.btn-back-to-top').click(function(e){
        e.preventDefault();
        $('html,body').animate({ scrollTop: 0 }, 'slow');
        return false;
    });

    // scroll to specific id when click on menu
    $('.templatemo-top-menu .navbar-nav a.nav').click(function(e){
        e.preventDefault(); 
        var linkId = $(this).attr('href');
        scrollTo(linkId);
        if($('.navbar-toggle').is(":visible") == true){
            $('.navbar-collapse').collapse('toggle');
        }
        $(this).blur();
        return false;
    });

    $('.templatemo-btn-read-more').click(function(e){
        e.preventDefault();
        var linkId = $(this).attr('href');
        scrollTo(linkId);
        return false;
    });

    // to stick navbar on top
    $('.templatemo-top-menu ').stickUp();

    // gallery category
    $('.templatemo-gallery-category a').click(function(e){
        e.preventDefault(); 
        $(this).parent().children('a').removeClass('active');
        $(this).addClass('active');
        var linkClass = $(this).attr('href');
        $('.gallery').each(function(){
            if($(this).is(":visible") == true){
               $(this).hide();
            };
        });
        $(linkClass).fadeIn();  
    });

    //gallery light box setup
    $('a.colorbox').colorbox({
      rel: function(){
          return $(this).data('group');
      }
    });
  });
});

function initialize() {
//    var mapOptions = {
//      zoom: 17,
//      center: new google.maps.LatLng(49.0452392,20.2976089)
//    };

    //var map = new google.maps.Map(document.getElementById('map-canvas'),  mapOptions);
}

// scroll animation 
function scrollTo(selectors) {
  if(!$(selectors).size()) return;
  var selector_top = $(selectors).offset().top - top_menu_height + 30 ;
  console.log(selector_top);
  $('html,body').animate({ scrollTop: selector_top }, 'slow');
}