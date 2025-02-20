<?php

  $modified_files = [
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "    Header unset Last-Modified" . PHP_EOL,
      'replace' => "",
    ],
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "  <FilesMatch \"\\.(gif|ico|jpg|jpeg|js|pdf|png|ttf)$\">" . PHP_EOL
                 . "    Header set Cache-Control \"max-age=86400, public, must-revalidate\"",
      'replace' => "  <FilesMatch \"\\.(gif|ico|jpg|jpeg|js|pdf|png|ttf)$\">" . PHP_EOL
                 . "    Header set Cache-Control \"max-age=604800, public, must-revalidate\"",
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  $deleted_files = [
    FS_DIR_APP . 'ext/jquery/jquery-1.12.0.min.js',
    FS_DIR_APP . 'ext/jquery/jquery-1.12.0.min.map',
    FS_DIR_APP . 'ext/trumbowyg/plugins/colors/ui/images/',
    FS_DIR_APP . 'ext/trumbowyg/ui/images/',
  ];

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
