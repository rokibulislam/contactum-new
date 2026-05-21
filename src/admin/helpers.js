
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




export const handleSidebarSettingsActiveLink = ($anchorOrItem) => {
  // Accept either an <a> or its parent <li>; always work with the <a>
  const $anchor = $anchorOrItem.is('a')
    ? $anchorOrItem
    : $anchorOrItem.find('> a').first();

  if (!$anchor.length) return;

  // 1. Deactivate every link, then activate the target
  jQuery('.contactum-settings__menu a').removeClass('active');
  $anchor.addClass('active');

  // 2. Expand the active group; collapse all others
  const $group = $anchor.closest('.contactum-settings__menu-item--has-submenu');

  jQuery('.contactum-settings__menu-item--has-submenu').each(function () {
    const $g = jQuery(this);
    if ($group.length && $g.is($group)) {
      // Expand this group
      $g.removeClass('is-collapsed');
      const $label = $g.find('> .contactum-settings__group-label').first();
      const key = 'ctm_sidebar_' + $label.text().trim().replace(/\s+/g, '_').toLowerCase();
      try { sessionStorage.setItem(key, '0'); } catch (_) {}
    } else {
      // Collapse all other groups
      $g.addClass('is-collapsed');
      const $label = $g.find('> .contactum-settings__group-label').first();
      const key = 'ctm_sidebar_' + $label.text().trim().replace(/\s+/g, '_').toLowerCase();
      try { sessionStorage.setItem(key, '1'); } catch (_) {}
    }
  });
};
