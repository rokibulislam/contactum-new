<?php
    $page = sanitize_text_field($_GET['page']);
?>

        <div class="contactum_menu_header">

            <ul class="contactum_menu">
                <li class="<?php echo ($page == 'contactum') ? 'active' : '' ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=contactum')); ?>" class="contactum_menu_link">
                        <?php _e('Forms', 'contactum'); ?>
                    </a>
                </li>
                <li class="<?php echo ($page == 'contactum-entries') ? 'active' : '' ?>">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=contactum-entries'));?>" class="contactum_menu_link">
                            <?php _e('Entries', 'contactum'); ?>
                        </a>
                </li>

                <li class="<?php echo ($page == 'contactum-settings') ? 'active' : '' ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=contactum-settings')); ?>" class="contactum_menu_link">
                        <?php _e('Global Settings', 'contactum'); ?>
                    </a>
                </li>

                <li class="<?php echo ($page == 'contactum-tools') ? 'active' : '' ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=contactum-tools')); ?>" class="contactum_menu_link">
                        <?php _e('Tools', 'contactum'); ?>
                    </a>
                </li>

            </ul>

        </div>

    <style>

.contactum_menu_header {
    background: #fff;
    padding: 10px 24px;
}
.contactum_menu {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    margin-top: 22px
}


.contactum_menu_link {
    background-color: transparent;
    border-radius: 6px;
    color: #353537;
    display: inline-block;
    font-size: 15px;
    font-weight: 500;
    line-height: 1.6;
    overflow: hidden;
    padding: 4px 14px;
    text-decoration: none;
    text-overflow: ellipsis;
    transition: .2s;
    white-space: nowrap
}

     </style>

