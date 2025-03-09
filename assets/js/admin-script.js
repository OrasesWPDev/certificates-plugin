/**
 * Certificates Plugin Admin Scripts
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // FAQ accordion functionality
        $('.certificates-plugin-faq-item h4').on('click', function() {
            const $answer = $(this).next('.certificates-plugin-faq-answer');
            $answer.slideToggle(200);
            $(this).toggleClass('active');
        });
    });
})(jQuery);