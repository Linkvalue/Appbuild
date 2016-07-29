(function($) {
    'use strict';
    $(function() {
        var $navigationToggler = $('.navigation-toggle');
        var $container = $('#container');
        var $navigation = $('#navigation');

        $navigation.on('click', '[role="toggle-sub-menu"]', toggleSubMenu);
        $navigationToggler.on('click', toggleSidebar);

        function toggleSidebar() {
            $container.toggleClass('navigation-closed');
        }
        function toggleSubMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).parents('.menu-item').toggleClass('is-open');
        }

        // Collapsible panels
        $('.panel .collapsible .fa-chevron-up, .panel .collapsible .fa-chevron-down').click(function (e) {
            e.preventDefault();
            $(this).toggleClass("fa-chevron-up fa-chevron-down");
            $(this).closest(".panel").children(".panel-body").slideToggle(300);
        });
    });
})($);
