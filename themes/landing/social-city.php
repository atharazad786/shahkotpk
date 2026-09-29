<?php
lt_topbar($sections);lt_header($sections,'social');lt_slider($slides,'social');lt_hero($sections,$landingStats,'social');
echo '<div class="lt-shell lt-social-layout"><main>';
lt_featured($sections,$featured,'feed');lt_directory($sections,$categories,'social');lt_spotlights($sections,'social');
echo '</main><aside>';
lt_info($sections,'social');lt_services($sections,'feed');
echo '</aside></div>';
lt_gallery($sections,'social');lt_cta($sections,'social');lt_content($sections,'social');lt_custom_modules($sections);lt_links($sections,'social');lt_footer($sections);
?>