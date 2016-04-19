<?php
return array(
    'db' => array(
        'database' => 'ChainStore_ServiceChannel_Gateway',
        'host'     => '127.0.0.1',
        'user'     => 'dev',
        'pass'     => 'dev',
    ),
    'log' => array(
        'path' => __DIR__ . '/logs/common.log'
    ),
    'mapping' => array(
        'preferences'=> __DIR__ . '/mapping/preferences.json',
        'call'       => __DIR__ . '/mapping/data2sc.json',
        'callbacks'  => __DIR__ . '/mapping/callbacks.json',
        'workorder'  => __DIR__ . '/mapping/workorder.json',
        'checkinout' => __DIR__ . '/mapping/checkinout.json',
        'note'       => __DIR__ . '/mapping/note.json',
    )
);