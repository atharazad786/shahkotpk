<?php
lt_topbar($sections);lt_header($sections,'civic');lt_hero($sections,$landingStats,'civic');lt_info($sections,'civic');
echo '<div class="lt-shell lt-civic-layout"><main>';
lt_directory($sections,$categories,'civic');lt_content($sections,'civic');lt_featured($sections,$featured,'civic');
echo '</main><aside>';
lt_links($sections,'civic');lt_services($sections,'civic');
echo '</aside></div>';
lt_slider($slides,'civic');lt_cta($sections,'civic');lt_gallery($sections,'civic');lt_custom_modules($sections);lt_footer($sections);
?>