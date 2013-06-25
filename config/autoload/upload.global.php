<?php

/**
 * Upload (bank statement) configuration
 */
return array(
    'uploadBanking' => array(
        'upload_dir' => 'import/',
        'maxFileSize' => '20kB',
        'fileExtension' => 'csv',
        'fileType' => 'text/plain',
        'maxParseLines' => 10, // Number of lines processed once
    ),
);
