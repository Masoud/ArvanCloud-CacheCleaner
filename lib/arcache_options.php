<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="https://lib.arvancloud.com/ar/jquery/1.7.1/jquery.min.js"></script>
    <style>
        .tabs-menu {
            height: 30px;
            clear: both;
            float: right;
            width: 100%;
        }

        .tabs-menu li {
            height: 30px;
            line-height: 30px;
            float: right;
            margin-right: 10px;
            background-color: #ccc;
            border-top: 1px solid #d4d4d1;
            border-right: 1px solid #d4d4d1;
            border-left: 1px solid #d4d4d1;
        }

        .tabs-menu li.current {
            position: relative;
            background-color: #fff;
            border-bottom: 1px solid #fff;
            z-index: 5;
        }

        .tabs-menu li a {
            padding: 10px;
            text-transform: uppercase;
            color: #000;
            text-decoration: none;
        }

        .tabs-menu .current a {
            color: #2e7da3;
        }

        .tabs-menu li a:hover {
            padding: 10px;
            text-transform: uppercase;
            color: #2e7da3;
            text-decoration: none;
        }

        .tab {
            border: 1px solid #d4d4d1;
            background-color: #fff;
            float: right;
            margin-top: -12px;
            margin-right: 10px;
            width: auto;
        }

        #tab-1 {
            display: block;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".tabs-menu a").click(function (event) {
                event.preventDefault();
                $(this).parent().addClass("current");
                $(this).parent().siblings().removeClass("current");
                var tab = $(this).attr("href");
                $(".tab-content").not(tab).css("display", "none");
                $(tab).fadeIn();
            });
        });
    </script>
</head>
</html>
<?php
$ar_cache = new ar_cache_API;
$ar_cache_options_post = isset($_POST['ar_cache_options']) ? $_POST['ar_cache_options'] : false;
if ($ar_cache_options_post) {
    update_option('ar_cache_options', $ar_cache_options_post);
}
$ar_cache_options = get_option('ar_cache_options');
$ar_cache_email = isset($ar_cache_options['email']) ? $ar_cache_options['email'] : '';
$ar_cache_token = isset($ar_cache_options['token']) ? $ar_cache_options['token'] : '';
$ar_cache_account = isset($ar_cache_options['account']) ? $ar_cache_options['account'] : '';
$ar_cache_site = $ar_cache_options['site'] != "" ? $ar_cache_options['site'] : $ar_cache->ar_get_wordpress_domain();;
$ar_cache_auto_purge = isset($ar_cache_options['auto_purge']) ? $ar_cache_options['auto_purge'] : "0";;
$turn_off_menubar = isset($ar_cache_options['turn_off_menubar']) ? $ar_cache_options['turn_off_menubar'] : "0";;
$ar_cache_url = isset($ar_cache_options['ar_cache_url']) ? $ar_cache_options['ar_cache_url'] : get_home_url();
?>
<div class="wrap">
    <div class="icon32" id="icon-options-general"><br></div>
    <h2 style="font-family:tahoma;">افزونه تنظیمات ابر آروان</h2>
    <div id="ar_cache-options-form">
        <?php if ($ar_cache_token == ''): ?>
            <div class="updated" id="message"><p><strong>توجه !</strong> شما در ابتدا باید کلید API را از وبسایت ابر
                    آروان دریافت کنید<br/>اگر شما می خواهید کش ها خود را به صورت اتوماتیک حذف کنید ، هم اکنون اقدام کنید
                    <a target="_blank" href="https://www.arvancloud.com/register">ثبت نام در ابر آروان</a></p></div>
        <?php elseif ($ar_cache_account == ''): ?>
            <div class="updated" id="message"><p><strong>توجه !</strong> شما باید دامنه ای که در ابر آروان وارد کرده اید
                    شناساسیی شود</p></div>
        <?php endif; ?>
        <?php if ($ar_cache_token): ?>
            <div id="tabs-container">
                <ul class="tabs-menu">
                    <li><a href="#tab-1">تنظیمات</a></li>
                    <li class="current"><a href="#tab-2">عملیات حذف کش</a></li>
                </ul>
            </div>
        <?php endif; ?>
        <div class="tab">
            <form action="" id="ar_cache-form" method="post">
                <div id="tab-1" class="tab-content" <?php if ($ar_cache_token) {
                    print('style="display:none";');
                } ?> >
                    <table class="ar_cache-table">
                        <tbody>
                        <tr>
                            <th><label for="category_base">نام کاربری</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" class="regular-text code" value="<?php echo $ar_cache_email; ?>"
                                       id="ar_cache-email" name="ar_cache_options[email]">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tag_base">کلید API</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" autocomplete="off" class="regular-text code"
                                       value="<?php echo $ar_cache_token; ?>" id="ar_cache-token"
                                       name="ar_cache_options[token]">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tag_base">کلید دامنه</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" autocomplete="off" class="regular-text code"
                                       value="<?php echo $ar_cache_account; ?>" id="ar_cache-account"
                                       name="ar_cache_options[account]">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tag_base">دامنه</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" autocomplete="off" class="regular-text code"
                                       value="<?php echo $ar_cache_site; ?>" id="ar_cache-site"
                                       name="ar_cache_options[site]">
                            </td>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="submit" value="بروز رسانی / ذخیره" class="button-secondary"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div id="tab-2" class="tab-content">
                    <table class="ar_cache-table">
                        <tbody>
                        <?php if ($ar_cache_token): ?>
                            <tr>
                                <th><label for="category_base">مخفی سازی منو بار وردپرس</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type=checkbox name="ar_cache_options[turn_off_menubar]" id="menubar_off"
                                           value="1" <?php checked("1", $turn_off_menubar); ?>>مخفی سازی منو بار
                                    وردپرس<br/>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category_base">حذف اتوماتیک</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type=checkbox name="ar_cache_options[auto_purge]" id="checkboxcache"
                                           value="1" <?php checked("1", $ar_cache_auto_purge); ?>>حذف کش زمانیکه یک پست
                                    بروزرسانی شد<br/>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category_base">حذف کش های آدرس</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type="text" class="regular-text code" value="<?php echo $ar_cache_url; ?>"
                                           name="ar_cache_options[ar_cache_url]" id="ar_cache-url">
                                    <input type="button" value="حذف کش های آدرس" id="ar_cache-purge-url"
                                           class="button-primary"/>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category_base">حذف تمامی کش ها</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type="button" value="حذف تمامی کش ها" id="ar_cache-entire-cache"
                                           class="button-primary"/>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                </div>
        </div>
        </table>
        </form>
        <div id="spinner"></div>
    </div>


