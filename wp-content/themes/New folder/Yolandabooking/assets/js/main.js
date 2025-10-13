// Header Menu Fixed
$(function () {
  var header = $(".header_rw");

  $(window).scroll(function () {
      var scroll = $(window).scrollTop();
      if (scroll >= 0.5) {
          header.addClass("sticky");
      } else {
          header.removeClass("sticky");
      }
  });

}); 