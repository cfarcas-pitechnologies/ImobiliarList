$(document).ready(function () {
    bindSearch();
    bindPriceSort();
    bindPager();
    var counties = ["Alba", "Arad", "Arges", "Bacau", "Bihor", "Bistrita-Nasaud", "Botosani", "Braila", "Brasov", "Bucuresti", "Buzau", "Calarasi", "Caras-Severin", "Cluj", "Constanta", "Covasna", "Dimbovita", "Dolj", "Galati", "Gorj", "Giurgiu", "Harghita", "Hunedoara", "Ialomita", "Iasi", "Ilfov", "Maramures", "Mehedinti", "Mures", "Neamt", "Olt", "Prahova", "Salaj", "Satu Mare", "Sibiu", "Suceava", "Teleorman", "Timis", "Tulcea", "Vaslui", "Vilcea", "Vrancea"];

    $('#imobiparser_county').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    },
    {
        name: 'states',
        source: substringMatcher(counties)
    });
});

function changeSearchType(data) {
    $('.search-type-input').val(data);
}

var bindPager = function () {
    var $form = $("#search-form");
    $('.pager-click').on('click', function () {

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                if (response.success) {
                    $('#results').html(response.template);
                    $('html, body').animate({
                        scrollTop: $('#results').offset().top
                    }, 2000);
                    bindPager();
                }
                return false;
            }
        });
        return false;
    })
}

var bindPriceSort = function () {
    $('#price-sort').on('click', function () {
        var $button = $(this),
                direction = $(this).data('price');

        $.ajax({
            url: priceSortURL,
            type: 'POST',
            data: JSON.stringify(direction),
            success: function (response) {
                if (response.success) {
                    $('#results').html(response.template);
                    $button.data('price', response.priceSort);
                    $button.removeClass();
                    $button.addClass('fa fa-sort-' + response.priceSort.toLowerCase());

                    $('html, body').animate({
                        scrollTop: $('#results').offset().top
                    }, 2000);
                }
                return false;
            }
        });
    });

    return false;
}

var substringMatcher = function (strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;

        // an array that will be populated with substring matches
        matches = [];

        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');

        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function (i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });

        cb(matches);
    };
};

var bindSearch = function () {
    var $form = $("#search-form");
    $form.find("#submit").on('click', function () {

        $.ajax({
            url: searchUrl,
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                if (response.success) {
                    $('#results').html(response.template);
                    $('html, body').animate({
                        scrollTop: $('#results').offset().top
                    }, 2000);
                    bindPager();
                }
            }
        });

        return false;
    });
}