
<?php

return [
    [
        'key'   => 'google',
        'name'  => 'google::app.title',
        'route' => 'admin.google.index',
        'sort'  => 2,
    ], [
        'key'   => 'google.view',
        'name'  => 'google::app.view',
        'route' => 'admin.google.index',
        'sort'  => 1,
    ], [
        'key'   => 'google.sync',
        'name'  => 'google::app.sync',
        'route' => 'admin.google.calendar.sync',
        'sort'  => 2,
    ], [
        'key'   => 'google.gmail',
        'name'  => 'google::app.gmail.title',
        'route' => 'admin.google.gmail.index',
        'sort'  => 3,
    ], [
        'key'   => 'google.gmail.view',
        'name'  => 'google::app.gmail.view',
        'route' => 'admin.google.gmail.index',
        'sort'  => 1,
    ], [
        'key'   => 'google.gmail.compose',
        'name'  => 'google::app.gmail.compose',
        'route' => 'admin.google.gmail.compose',
        'sort'  => 2,
    ], [
        'key'   => 'google.gmail.send',
        'name'  => 'google::app.gmail.send',
        'route' => 'admin.google.gmail.send',
        'sort'  => 3,
    ], [
        'key'   => 'google.gmail.sync',
        'name'  => 'google::app.gmail.sync',
        'route' => 'admin.google.gmail.sync',
        'sort'  => 4,
    ],
];
