
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




export const handleSidebarSettingsActiveLink = (
  $link,
  init = false,
  firstLoad = false
) => {
  const ACTIVE = 'contactum-settings__menu-item--active';
  const HAS_SUB = 'contactum-settings__menu-item--has-submenu';
  const OPEN = 'contactum-settings__menu-item--open';
  const SUBMENU = '.contactum-settings__submenu';

  // 1. Activate current item, deactivate siblings
  $link
    .addClass(ACTIVE)
    .siblings()
    .removeClass(`${ACTIVE} ${OPEN}`);

  // 2. Toggle submenu if current item has submenu
  if ($link.hasClass(HAS_SUB)) {
    const $submenu = $link.children(SUBMENU);

    if (firstLoad) {
      $submenu.show();
      $link.addClass(OPEN);
    } else {
      $link.toggleClass(OPEN);
      $submenu.slideToggle();
    }
  }

  // 3. Activate first submenu item (if exists)
  const $firstSubItem = $link.find(`${SUBMENU} > li:first`);
  if ($firstSubItem.length) {
    $firstSubItem
      .addClass(ACTIVE)
      .siblings()
      .removeClass(ACTIVE);
  }

  // 4. Init mode: ensure parent submenu opens
  if (init) {
    const $parent = $link.closest(`.${HAS_SUB}`);

    if ($parent.length) {
      const $parentSubmenu = $parent.children(SUBMENU);

      if (firstLoad) {
        $parent.addClass(`${ACTIVE} ${OPEN}`);
        $parentSubmenu.show();
      } else {
        $parent.addClass(`${ACTIVE} ${OPEN}`);
        $parentSubmenu.slideToggle();
      }
    }
  }

  // 5. Close other open submenus
  $link
    .siblings(`.${HAS_SUB}`)
    .removeClass(OPEN)
    .children(SUBMENU)
    .slideUp();
};
