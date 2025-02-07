<?php

add_action('admin_menu', 'sarbatoare_azi_settings_menu');

function sarbatoare_azi_settings_menu() {
    add_options_page(
        'Sarbatoare Azi Settings',
        'Sarbatoare Azi',
        'manage_options',
        'sarbatoare-azi-settings',
        'sarbatoare_azi_settings_page'
    );
}

function sarbatoare_azi_settings_page() {
    ?>
    <div class="wrap">
        <h1>Sarbatoare Azi Settings</h1>
        <p>There are no configurable settings for this plugin at the moment.</p>
    </div>
    <?php
}

?>