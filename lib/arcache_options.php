<?php

/* Tab ArvanCloud */

$pageStart = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MY WEBSITE PAGE</title>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<style>
.tabs-menu {height: 30px;clear: both;float: right;width: 100%;}
.tabs-menu li {height: 30px;line-height: 30px;float: right;margin-right: 10px;background-color: #ccc;border-top: 1px solid #d4d4d1;border-right: 1px solid #d4d4d1;
border-left: 1px solid #d4d4d1;}
.tabs-menu li.current {position: relative;background-color: #fff;border-bottom: 1px solid #fff;z-index: 5;}
.tabs-menu li a {padding: 10px;text-transform: uppercase;color: #000;text-decoration: none; }
.tabs-menu .current a {color: #2e7da3;}
.tabs-menu li a:hover {padding: 10px;text-transform: uppercase;color: #2e7da3;text-decoration: none; }
.tab {border: 1px solid #d4d4d1;background-color: #fff;float: right;margin-top: -12px;margin-right: 10px;width: auto;}
#tab-1 {display: block;   }
</style>
<script type="text/javascript">
$(document).ready(function() {
    $(".tabs-menu a").click(function(event) {
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
<body>
</body>
</html>';
print $pageStart;
?>

<?php
$arcache = new arcache_API;
$arcache_options_post = isset($_POST['arcache_options']) ? $_POST['arcache_options'] : false;
if ($arcache_options_post) {
    update_option('arcache_options', $arcache_options_post);
}
$arcache_options = get_option('arcache_options');
$arcache_email = isset($arcache_options['email']) ? $arcache_options['email'] : '';
$arcache_token = isset($arcache_options['token']) ? $arcache_options['token'] : '';
$arcache_account = isset($arcache_options['account']) ? $arcache_options['account'] : '';
$arcache_site = $arcache_options['site'] != "" ? $arcache_options['site'] : $arcache->get_wordpress_domain();;
$arcache_auto_purge = isset($arcache_options['auto_purge']) ? $arcache_options['auto_purge'] : "0";;
$turn_off_menubar = isset($arcache_options['turn_off_menubar']) ? $arcache_options['turn_off_menubar'] : "0";;
$arcache_url = isset($arcache_options['arcache_url']) ? $arcache_options['arcache_url'] : get_home_url();
?>
<div class="wrap">
    <div class="icon32" id="icon-options-general"><br></div>
    <h2 style="font-family:tahoma;">افزونه تنظیمات ابر آروان</h2>
    <div id="arcache-options-form">
        <?php if ($arcache_token == ''): ?>
            <div class="updated" id="message"><p><strong>توجه !</strong> شما در ابتدا باید کلید API را از وبسایت ابر
                    آروان دریافت کنید<br/>اگر شما می خواهید کش ها خود را به صورت اتوماتیک حذف کنید ، هم اکنون اقدام کنید
                    <a target="_blank" href="https://www.arvancloud.com/register">ثبت نام در ابر آروان</a></p></div>
        <?php elseif ($arcache_account == ''): ?>
            <div class="updated" id="message"><p><strong>توجه !</strong> شما باید دامنه ای که در ابر آروان وارد کرده اید
                    شناساسیی شود</p></div>
        <?php endif; ?>
        <?php if ($arcache_token): ?>
            <div id="tabs-container">
                <ul class="tabs-menu">
                    <li><a href="#tab-1">تنظیمات</a></li>
                    <li class="current"><a href="#tab-2">عملیات حذف کش</a></li>
                </ul>
            </div>
        <?php endif; ?>
        <div class="tab">
            <form action="" id="arcache-form" method="post">
                <div id="tab-1" class="tab-content" <?php if ($arcache_token) {
                    print('style="display:none";');
                } ?> >
                    <table class="arcache-table">
                        <tbody>
                        <tr>
                            <th><label for="category_base">نام کاربری</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" class="regular-text code" value="<?php echo $arcache_email; ?>"
                                       id="arcache-email" name="arcache_options[email]">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tag_base">کلید API</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" autocomplete="off" class="regular-text code"
                                       value="<?php echo $arcache_token; ?>" id="arcache-token"
                                       name="arcache_options[token]">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tag_base">کلید دامنه</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" autocomplete="off" class="regular-text code"
                                       value="<?php echo $arcache_account; ?>" id="arcache-account"
                                       name="arcache_options[account]">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tag_base">دامنه</label></th>
                            <td class="col1"></td>
                            <td class="col2">
                                <input type="text" autocomplete="off" class="regular-text code"
                                       value="<?php echo $arcache_site; ?>" id="arcache-site"
                                       name="arcache_options[site]">
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
                    <table class="arcache-table">
                        <tbody>
                        <?php if ($arcache_token): ?>
                            <tr>
                                <th><label for="category_base">مخفی سازی منو بار وردپرس</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type=checkbox name="arcache_options[turn_off_menubar]" id="menubar_off"
                                           value="1" <?php checked("1", $turn_off_menubar); ?>>مخفی سازی منو بار
                                    وردپرس<br/>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category_base">حذف اتوماتیک</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type=checkbox name="arcache_options[auto_purge]" id="checkboxcache"
                                           value="1" <?php checked("1", $arcache_auto_purge); ?>>حذف کش زمانیکه یک پست
                                    بروزرسانی شد<br/>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category_base">حذف کش های آدرس</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type="text" class="regular-text code" value="<?php echo $arcache_url; ?>"
                                           name="arcache_options[arcache_url]" id="arcache-url">
                                    <input type="button" value="حذف کش های آدرس" id="arcache-purge-url"
                                           class="button-primary"/>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="category_base">حذف تمامی کش ها</label></th>
                                <td class="col1"></td>
                                <td class="col2">
                                    <input type="button" value="حذف تمامی کش ها" id="arcache-entire-cache"
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