(function ($) {
  'use strict';
  $(function () {
    var body = $('body');
    var contentWrapper = $('.content-wrapper');
    var scroller = $('.container-scroller');
    var footer = $('.footer');
    var sidebar = $('.sidebar');

    // Function to add active class based on the current URL
    // function addActiveClass(element) {
    //   var currentPath = location.pathname; // Get the current full path
    //   var href = element.attr('href');
      
    //   // Check if the current href matches exactly or contains the full path
    //   if (currentPath === href || currentPath.indexOf(href) !== -1) {
    //     element.parents('.nav-item').last().addClass('active');
    //     if (element.parents('.sub-menu').length) {
    //       element.closest('.collapse').addClass('show');
    //       element.addClass('active');
    //     }
    //   } else {
    //     // Remove the active class from non-matching elements
    //     // element.parents('.nav-item').last().removeClass('active');
    //   }
    // }
$(document).ready(function () {
  var currentPath = window.location.pathname.replace(/\/$/, '');

  $('.sidebar .nav-link').each(function () {
    var $this = $(this);
    var href = $this.attr('href');

    if (href) {
      var hrefPath = new URL(href, window.location.origin).pathname.replace(/\/$/, '');

      var isMatch = (currentPath === hrefPath);

      // Smart UX active route matching for sub-pages / actions
      if (!isMatch) {
        if (hrefPath === '/departmentview' && (currentPath === '/department' || currentPath.indexOf('/department/') === 0)) {
          isMatch = true;
        } else if (hrefPath === '/branches' && (currentPath === '/branches/create' || currentPath.indexOf('/branches/edit') === 0 || currentPath.indexOf('/branches/assign') === 0)) {
          isMatch = true;
        }
      }

      if (isMatch) {
        if ($this.closest('.sub-menu').length) {
          // Submenu item matched
          $this.addClass('active');
          $this.closest('.collapse').addClass('show');

          var $parentLink = $this.closest('.collapse').prev('.nav-link');
          $parentLink.addClass('active');

          // 🔶 Apply styles to parent menu link
          $parentLink.css({
            'color': '#E66136',
            'font-weight': '600',
            'background-color': '#ffffff', // white background
            'border-radius': '8px'         // optional: make it rounded
          });

          $parentLink.find('i.menu-icon').css('color', '#E66136');
        } else {
          // Dashboard or other main links
          $this.closest('.nav-item').addClass('active');
          $this.css({
            'color': '#E66136',
            'font-weight': '600',
            'background-color': '#ffffff', // white background
            'border-radius': '8px'
          });
          $this.find('i.menu-icon').css('color', '#E66136');
        }
      }
    }
  });
});





    // Get the current path of the URL
    var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');

    // Apply active class to sidebar and horizontal menu items
    // $('.nav li a', sidebar).each(function () {
    //   var $this = $(this);
    //   addActiveClass($this);
    // });

    // $('.horizontal-menu .nav li a').each(function () {
    //   var $this = $(this);
    //   addActiveClass($this);
    // });

    // Close other submenu in sidebar on opening any
    sidebar.on('show.bs.collapse', '.collapse', function () {
      sidebar.find('.collapse.show').collapse('hide');
    });

    // Change sidebar and content-wrapper height
    applyStyles();

    function applyStyles() {
      // Applying perfect scrollbar
      if (!body.hasClass("rtl")) {
        if ($('.settings-panel .tab-content .tab-pane.scroll-wrapper').length) {
          const settingsPanelScroll = new PerfectScrollbar('.settings-panel .tab-content .tab-pane.scroll-wrapper');
        }
        if ($('.chats').length) {
          const chatsScroll = new PerfectScrollbar('.chats');
        }
        if (body.hasClass("sidebar-fixed")) {
          if ($('#sidebar').length) {
            var fixedSidebarScroll = new PerfectScrollbar('#sidebar .nav');
          }
        }
      }
    }

    // Prevent scroll chaining from sidebar to window when reaching top/bottom
    sidebar.on('wheel', function (e) {
      var delta = e.originalEvent.deltaY;
      var scrollTop = this.scrollTop;
      var scrollHeight = this.scrollHeight;
      var height = $(this).outerHeight();

      if ((delta < 0 && scrollTop <= 0) || (delta > 0 && scrollTop + height >= scrollHeight - 1)) {
        e.preventDefault();
      }
    });

    // Minimize sidebar toggle
    $('[data-bs-toggle="minimize"]').on("click", function () {
      if ((body.hasClass('sidebar-toggle-display')) || (body.hasClass('sidebar-absolute'))) {
        body.toggleClass('sidebar-hidden');
      } else {
        body.toggleClass('sidebar-icon-only');
      }
    });

    // Checkbox and radio buttons styling
    $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');

    // Horizontal menu in mobile
    $('[data-toggle="horizontal-menu-toggle"]').on("click", function () {
      $(".horizontal-menu .bottom-navbar").toggleClass("header-toggled");
    });

    // Horizontal menu navigation in mobile
    var navItemClicked = $('.horizontal-menu .page-navigation >.nav-item');
    navItemClicked.on("click", function (event) {
      if (window.matchMedia('(max-width: 991px)').matches) {
        if (!($(this).hasClass('show-submenu'))) {
          navItemClicked.removeClass('show-submenu');
        }
        $(this).toggleClass('show-submenu');
      }
    });

    // Scroll effect for horizontal menu
    $(window).scroll(function () {
      if (window.matchMedia('(min-width: 992px)').matches) {
        var header = $('.horizontal-menu');
        if ($(window).scrollTop() >= 70) {
          $(header).addClass('fixed-on-scroll');
        } else {
          $(header).removeClass('fixed-on-scroll');
        }
      }
    });

    // Datepicker
    if ($("#datepicker-popup").length) {
      $('#datepicker-popup').datepicker({
        enableOnReadonly: true,
        todayHighlight: true,
      });
      $("#datepicker-popup").datepicker("setDate", "0");
    }

  });

  // Check all checkboxes in order status
  $("#check-all").click(function () {
    $(".form-check-input").prop('checked', $(this).prop('checked'));
  });

  // Focus input when clicking on search icon
  $('#navbar-search-icon').click(function () {
    $("#navbar-search-input").focus();
  });

  // Scroll effect for header (Desktop only: smooth auto-adjust on scroll)
  function updateHeaderOnScroll() {
    if (window.innerWidth < 992) {
      $(".fixed-top").removeClass("headerLight");
      $("body").removeClass("has-scrolled");
      return;
    }
    var scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || $(window).scrollTop() || 0;
    if (scroll >= 20) {
      $(".fixed-top").addClass("headerLight");
      $("body").addClass("has-scrolled");
    } else {
      $(".fixed-top").removeClass("headerLight");
      $("body").removeClass("has-scrolled");
    }
  }

  $(window).on('scroll resize touchmove', updateHeaderOnScroll);
  $(document).on('scroll', updateHeaderOnScroll);
  $(document).ready(updateHeaderOnScroll);
})(jQuery);
