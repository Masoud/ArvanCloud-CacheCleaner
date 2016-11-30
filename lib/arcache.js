(function ($) {
    window.ar_cache = {};
    var ar_cache = window.ar_cache;
    ar_cache.initialize = function () {
        ar_cache.setElements();
        ar_cache.purgeEntireCache();
        ar_cache.purgeUrl();
        jQuery(document).ajaxStart(function () {
            jQuery('#spinner').show();
        })
        jQuery(document).ajaxStop(function () {
            jQuery('#spinner').hide();
        })
    };
    ar_cache.setElements = function () {
        ar_cache.elems = {};
        ar_cache.elems.form = {};
        ar_cache.elems.form.form = jQuery('#ar_cache-form');
        ar_cache.elems.form.username = ar_cache.elems.form.form.find('#ar_cache-email');
        ar_cache.elems.form.account = ar_cache.elems.form.form.find('#ar_cache-account');
        ar_cache.elems.form.token = ar_cache.elems.form.form.find('#ar_cache-token');
        ar_cache.elems.form.url = ar_cache.elems.form.form.find('#ar_cache-url');
        ar_cache.elems.entire_cache_btn = jQuery('#ar_cache-entire-cache');
        ar_cache.elems.ar_purge_url_btn = jQuery('#ar_cache-purge-url');
        ar_cache.properties = {};
    };
    ar_cache.handleJsonResponse = function (response, status) {
        if (status === undefined) {
            status = 'success';
        }
        if (status == 'success') {
            alert('درخواست حذف کش با موفقیت اجرا شد\n\n');
        }
        else {
            alert('درخوسات حذف کش با خطا مواجه شد\n\n' + response);
        }
    }

    var countPurgeReq = 1;
    function purgeRequest(action,url){

        jQuery.ajax({
            'type': 'post',
            'url': ajaxurl,
            'timeout': 60000,
            'data': {
                'action': action,
                'url': url
            },
            'success': function (response) {
                ar_cache.handleJsonResponse(response, 'success');

            },
            'error': function (response) {
                if (countPurgeReq <= 10) {//try request
                    countPurgeReq++;
                    purgeRequest(action,url);

                }else{//display error message
                    ar_cache.handleJsonResponse(response, 'error');
                }
            }
        });
    }

    ar_cache.purgeEntireCache = function () {
        ar_cache.elems.entire_cache_btn.bind('click', function (e) {

            e.preventDefault();
            if (confirm('پاکسازی فایل های کش شده میتواند به طور موقت عملکرد وبسایت شما کاهش دهد. آیا مایل به انجام عملیات هستید ؟')) {
                countPurgeReq = 1;

                purgeRequest('ar_cache_entire_cache','');

            }
        });
    }
    ar_cache.purgeUrl = function () {
        ar_cache.elems.ar_purge_url_btn.bind('click', function (e) {
            e.preventDefault();
            purgeRequest('ar_cache_ar_purge_url',ar_cache.elems.form.url.val());
        });
    }
    jQuery(document).ready(function () {
        ar_cache.initialize();
    });

    $(document).on('change', '#checkboxcache , #menubar_off', function () {
        $('#ar_cache-form').submit();
    });
})(jQuery);

jQuery('#arvan_add').click(function () {
    jQuery('#field_arvan_text').clone().appendTo('#field_arvan_text');
});
