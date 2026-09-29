<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
fwrite(STDOUT, "ShahkotPK emergency recovery 9.0.2: v8 worker temporarily PAUSED.\n");
exit(0);
