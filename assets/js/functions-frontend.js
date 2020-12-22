jQuery(function ($) {
    "use strict";

    $('.ewa-dropdown-widget').on('change', function () {
        var href = $(this).find(":selected").val();
        location.href = href;
    });

    if (typeof $.fn.slick === 'function') {

        $('.ewa-carousel').slick({
            slide: '.ewa-slick-slide',
            infinite: true,
            draggable: false,
            prevArrow: '<div class="slick-prev"><span>' + ewa_ajax_object.carousel_prev + '</span></div>',
            nextArrow: '<div class="slick-next"><span>' + ewa_ajax_object.carousel_next + '</span></div>',
            speed: 300,
            lazyLoad: 'progressive',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 4,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 3,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 2,
                        draggable: true,
                        arrows: false
                    }
                }
            ]
        });

        $('.ewa-product-carousel').slick({
            slide: '.ewa-slick-slide',
            infinite: true,
            draggable: false,
            prevArrow: '<div class="slick-prev"><span>' + ewa_ajax_object.carousel_prev + '</span></div>',
            nextArrow: '<div class="slick-next"><span>' + ewa_ajax_object.carousel_next + '</span></div>',
            speed: 300,
            lazyLoad: 'progressive',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        draggable: true,
                        arrows: false
                    }
                }
            ]
        });

    }

    /* ··························· Filter by author widget ··························· */

    var EWAFilterByAuthor = function () {

        var baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
        var currentUrl = window.location.href;

        var marcas = [];
        $('.ewa-filter-products input[type="checkbox"]').each(function (index) {
            if ($(this).prop('checked')) marcas.push($(this).val());
        });
        marcas = marcas.join();

        if (marcas) {

            //removes previous "ewa-author" from url
            currentUrl = currentUrl.replace(/&?ewa-author-filter=([^&]$|[^&]*)/i, "");

            //removes pagination
            currentUrl = currentUrl.replace(/\/page\/\d*\//i, "");

            if (currentUrl.indexOf("?") === -1) {
                currentUrl = currentUrl + '?ewa-author-filter=' + marcas;
            } else {
                currentUrl = currentUrl + '&ewa-author-filter=' + marcas;
            }

        } else {
            currentUrl = baseUrl;
        }

        location.href = currentUrl;

    }

    $('.ewa-filter-products button').on('click', function () { EWAFilterByAuthor(); });
    $('.ewa-filter-products.ewa-hide-submit-btn input').on('change', function () { EWAFilterByAuthor(); });

    var authors = EWAgetUrlParameter('ewa-author-filter');

    if (authors != null) {
        var authors_array = authors.split(',');
        $('.ewa-filter-products input[type="checkbox"]').prop('checked', false);
        for (var i = 0, l = authors_array.length; i < l; i++) {
            $('.ewa-filter-products input[type="checkbox"]').each(function (index) {
                if ($(this).val()) {
                    if (authors_array[i] == $(this).val()) {
                        $(this).prop('checked', true);
                    }
                }
            });
        }
    } else {
        $('.ewa-filter-products input[type="checkbox"]').prop('checked', false);
    }

    /* ··························· /Filter by author widget ··························· */

});

var EWAgetUrlParameter = function EWAgetUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
