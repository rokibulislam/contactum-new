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

                <li class="<?php echo ($page == 'contactum-analytics') ? 'active' : '' ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=contactum-analytics')); ?>" class="contactum_menu_link">
                        <?php _e('Analytics', 'contactum'); ?>
                    </a>
                </li>

            </ul>

        </div>

    <style>

.contactum_menu_header {
    background: var(--card); /* white card background */
    padding-left: 10px;
    padding-top: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* subtle shadow */
    border-radius: 8px;
}

.contactum_menu {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 22px;
    gap: 10px;
}

.contactum_menu li {
    margin-bottom: 0px !important;
}

.contactum_menu li.active a.contactum_menu_link {
    color: var(--primary);
}

.contactum_menu li.active {
    border-bottom: 2px solid var(--primary);
}

.contactum_menu_link {
    background-color: transparent;
    border-radius: 6px;
    color: var(--muted-foreground);
    display: inline-block;
    font-size: 15px;
    font-weight: 500;
    line-height: 1.6;
    padding: 4px 14px;
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: all 0.2s ease;

    &:hover {
        background-color: var(--secondary);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
}


     </style>

