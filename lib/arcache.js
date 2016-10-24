(function ($) {
    window.arcache = {};
    var arcache = window.arcache;
    arcache.initialize = function () {
        arcache.setElements();
        arcache.purgeEntireCache();
        arcache.purgeUrl();
        jQuery(document).ajaxStart(function () {
            jQuery('#spinner').show();
        })
        jQuery(document).ajaxStop(function () {
            jQuery('#spinner').hide();
        })
    };
    arcache.setElements = function () {
        arcache.elems = {};
        arcache.elems.form = {};
        arcache.elems.form.form = jQuery('#arcache-form');
        arcache.elems.form.username = arcache.elems.form.form.find('#arcache-email');
        arcache.elems.form.account = arcache.elems.form.form.find('#arcache-account');
        arcache.elems.form.token = arcache.elems.form.form.find('#arcache-token');
        arcache.elems.form.url = arcache.elems.form.form.find('#arcache-url');
        arcache.elems.entire_cache_btn = jQuery('#arcache-entire-cache');
        arcache.elems.purge_url_btn = jQuery('#arcache-purge-url');
        arcache.properties = {};
    };
    arcache.handleJsonResponse = function (response, status) {
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
                arcache.handleJsonResponse(response, 'success');

            },
            'error': function (response) {
                if (countPurgeReq <= 10) {//try request
                    countPurgeReq++;
                    purgeRequest(action,url);

                }else{//display error message
                    arcache.handleJsonResponse(response, 'error');
                }
            }
        });
    }

    arcache.purgeEntireCache = function () {
        arcache.elems.entire_cache_btn.bind('click', function (e) {

            e.preventDefault();
            if (confirm('پاکسازی فایل های کش شده میتواند به طور موقت عملکرد وبسایت شما کاهش دهد. آیا مایل به انجام عملیات هستید ؟')) {
                countPurgeReq = 1;

                purgeRequest('arcache_entire_cache','');

            }
        });
    }
    arcache.purgeUrl = function () {
        arcache.elems.purge_url_btn.bind('click', function (e) {
            e.preventDefault();
            purgeRequest('arcache_purge_url',arcache.elems.form.url.val());
        });
    }
    jQuery(document).ready(function () {
        arcache.initialize();
    });

    $(document).on('change', '#checkboxcache , #menubar_off', function () {
        $('#arcache-form').submit();
    });
})(jQuery);