<?php

require_once 'lib/rout.php';

route('/', function () {

    $items = [ // simple stub
        ['id' => 1, 'name' => '123', 'price' => 321],
        ['id' => 2, 'name' => 'qweqwe', 'price' => 321],
        ['id' => 3, 'name' => '123123', 'price' => 321],
    ];

    include 'tpl/index.phtml';
});

route('/getList/(\d+)', function ($lastId) {
    $items = [ // simple stub
        ['id' => 1, 'name' => '123', 'price' => 321],
        ['id' => 2, 'name' => 'qweqwe', 'price' => 321],
        ['id' => 3, 'name' => '123123', 'price' => 321],
    ];

    header('Content-Type: application/json');
    echo json_encode($items);
});

route('/getItem/(\d+)', function ($itemId) {
    $item= ['id' => 1, 'name' => '123', 'price' => 321];

    header('Content-Type: application/json');
    echo json_encode($item);
});

route('/addItem', function () {
    //TODO save new item
}, 'POST');

route('/updateItem/(\d+)', function ($itemId) {
    //TODO update item if exist
});

route('/removeItem/(\d+)', function ($itemId) {
    //TODO remove item if exist
});



error(404, function () {
    echo 'Page Not Found';
});

route(getenv('REQUEST_URI'));

