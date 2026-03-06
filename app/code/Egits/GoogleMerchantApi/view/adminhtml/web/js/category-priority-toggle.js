define(['jquery'], function ($) {
    'use strict';

    function toggleChildren(categoryId, show) {
        $('.priority').filter(function () {
            return $(this).find('input[type="hidden"]').data('parent-hidden') == categoryId;
        }).each(function () {
            const $child = $(this);
            const childId = $child.find('input[type="hidden"]').data('id-hidden');
            const $childToggle = $child.find('.toggle');

            if (show) {
                $child.show().removeClass('collapsed').addClass('expanded');
                if ($childToggle.length && $childToggle.hasClass('open')) {
                    toggleChildren(childId, true); // Only recurse if toggle is open
                }
            } else {
                toggleChildren(childId, false); // Recursively hide all descendants
                $child.hide().removeClass('expanded').addClass('collapsed');
                $childToggle.removeClass('open').addClass('closed'); // Ensure toggle reflects closed state
            }
        });
    }

    function init() {
        $('.priority .toggle').on('click', function () {
            const $toggle = $(this);
            const categoryId = $toggle.data('category-id');
            const isOpen = $toggle.hasClass('open');

            toggleChildren(categoryId, !isOpen);
            $toggle.toggleClass('open closed');
        });

        // Ensure toggles reflect correct state on page load
        $('.priority.collapsed').each(function () {
            const parentId = $(this).find('input[type="hidden"]').data('parent-hidden');
            const $parentToggle = $(`.toggle[data-category-id="${parentId}"]`);
            if ($parentToggle.length) {
                $parentToggle.removeClass('open').addClass('closed');
            }
        });
    }

    return { init };
});
