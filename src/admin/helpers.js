
export const scrollTop = (scrollTop = 0, milliSecond = 300, selector = 'html, body') => (
    jQuery(selector).animate({ scrollTop }, milliSecond).promise()
);

export const handleSidebarActiveLink = ($link, init = false, firstLoad = false) => {
    //make current link active and others deactivate
    $link.addClass('active').siblings().removeClass('active');

    // toggle sub-links if curren link has sub-links
    if ($link.hasClass('has_sub_menu')) {

        if (firstLoad) {
            $link.find('.contactum_list_submenu').show();
        } else {
            $link.toggleClass('is-submenu'); // toggle sub-link icon
            $link.find('.contactum_list_submenu').slideToggle();
        }
    }

    // make first sub-link active if it has submenu
    const $subMenuFirstItem = $link.find('ul.contactum_list_submenu li:first');
    if ($subMenuFirstItem.length) {
        $subMenuFirstItem.addClass('active').siblings().removeClass('active');
    }

    if (init) {
        const $parentLink = $link.closest('li.contactum_list_button_item.has_sub_menu');
        if ($parentLink.length) {
            if (firstLoad) {
                $parentLink.addClass('active'); // toggle sub-link icon
                $parentLink.find('.contactum_list_submenu').show();

            } else {
                $parentLink.find('.contactum_list_submenu').slideToggle();
                $parentLink.addClass('is-submenu active'); // toggle sub-link icon
            }
        }
    }

    // close all others sub-links if it has
    if ($link.siblings().hasClass('has_sub_menu')) {
        $link.siblings().removeClass('is-submenu'); // sub-link icon close
        $link.siblings().find('.contactum_list_submenu').slideUp();
    }
}