<?php

/**
 * Upload (bank statement) configuration
 */
return array(
    'upload_banking' => array(
        'upload_dir' => 'import/',
        'maxFileSize' => '20kB',
        'fileExtension' => 'csv',
        'fileType' => 'text/plain',
        'maxParseLines' => 10, // Number of lines processed once
    ),
);
