/**
 * Certificates Plugin Scripts
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Smooth scroll for sticky menu links only
        $('.certificates-plugin-sticky-menu a').on('click', function(e) {
            if (this.hash !== '') {
                e.preventDefault();
                const hash = this.hash;

                $('html, body').animate({
                    scrollTop: $(hash).offset().top - 250 // Adjust this offset value as needed
                }, 800);
            }
        });

        // Handle "Back to Top" links separately - scroll to page top
        $('.certificates-plugin-back-to-top a').on('click', function(e) {
            e.preventDefault();

            // Scroll to the top of the page
            $('html, body').animate({
                scrollTop: 0
            }, 800);
        });

        // Highlight sticky menu items on scroll
        $(window).on('scroll', function() {
            const scrollPosition = $(window).scrollTop();

            // Check each section
            $('.row[id]').each(function() {
                const currentSection = $(this);
                const sectionTop = currentSection.offset().top - 300; // Increased offset
                const sectionBottom = sectionTop + currentSection.outerHeight();

                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    $('.certificates-plugin-sticky-menu a').removeClass('active');
                    $('.certificates-plugin-sticky-menu a[href="#' + currentSection.attr('id') + '"]').addClass('active');
                }
            });
        });
    });
})(jQuery);