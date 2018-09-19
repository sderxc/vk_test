<?php

require_once 'lib/rout.php';
require_once 'lib/db.php';

route('/', function () {

    $mc = memcache_connect('127.0.0.1', 11211);
    $conection = connect();

    $stmt = mysqli_prepare($conection, "SELECT `id` FROM `goods` LIMIT 20");

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ids = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $items = $missedItems = $fetchedRecords = [];
    if ($ids) {
        $mKeys = [];
        foreach ($ids as $id) {
            $mKeys[$id['id']] = 'good:' . $id['id'];
        }

        $fetchedRecords = memcache_get($mc, $mKeys);

        $missedKeys = array_diff($mKeys, array_keys($fetchedRecords));



        if ($missedKeys) {

            $inArr = array_keys($missedKeys);
            $clause = implode(', ', array_fill(0, count($inArr), '?'));
            $types = str_repeat('i', count($inArr));

            $stmt2 = mysqli_prepare($conection, "SELECT * FROM `goods` WHERE `id` IN ($clause)");

            mysqli_stmt_bind_param($stmt2, $types, ...$inArr);

            mysqli_stmt_execute($stmt2);
            $result = mysqli_stmt_get_result($stmt2);
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if ($rows) {
                foreach ($rows as $row) {
                    $missedItems[$row['id']] = $row;
                    memcache_set($mc, 'good:' . $row['id'], $row, 0, 300);
                }
            }
        }

        foreach ($ids as $id) {
            $items[] = $fetchedRecords['good:' . $id['id']] ?? $missedItems[$id['id']];
        }
    }



//    $items = [ // simple stub
//        ['id' => 1, 'name' => '123', 'price' => 321],
//        ['id' => 2, 'name' => 'qweqwe', 'price' => 321],
//        ['id' => 3, 'name' => '123123', 'price' => 321],
//    ];

    include 'tpl/index.phtml';
});

route('/getList/(\d+)', function ($lastId) {



    $mc = memcache_connect('127.0.0.1', 11211);
    $conection = connect();

    $stmt = mysqli_prepare($conection, "SELECT `id` FROM `goods` WHERE `id` > ? LIMIT 20");

    mysqli_stmt_bind_param($stmt, 'i', $lastId);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ids = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $items = $missedItems = $fetchedRecords = [];
    if ($ids) {
        $mKeys = [];
        foreach ($ids as $id) {
            $mKeys[$id['id']] = 'good:' . $id['id'];
        }

        $fetchedRecords = memcache_get($mc, $mKeys);

        $missedKeys = array_diff($mKeys, array_keys($fetchedRecords));



        if ($missedKeys) {

            $inArr = array_keys($missedKeys);
            $clause = implode(', ', array_fill(0, count($inArr), '?'));
            $types = str_repeat('i', count($inArr));

            $stmt2 = mysqli_prepare($conection, "SELECT * FROM `goods` WHERE `id` IN ($clause)");

            mysqli_stmt_bind_param($stmt2, $types, ...$inArr);

            mysqli_stmt_execute($stmt2);
            $result = mysqli_stmt_get_result($stmt2);
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if ($rows) {
                foreach ($rows as $row) {
                    $missedItems[$row['id']] = $row;
                    memcache_set($mc, 'good:' . $row['id'], $row, 0, 300);
                }
            }
        }

        foreach ($ids as $id) {
            $items[] = $fetchedRecords['good:' . $id['id']] ?? $missedItems[$id['id']];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($items);
});

route('/getItem/(\d+)', function ($itemId) {

    $mc = memcache_connect('127.0.0.1', 11211);

    $item = memcache_get($mc, 'good:' . $itemId);

    if (!$item) {
        $conection = connect();
        $stmt = mysqli_prepare($conection, "SELECT `id` FROM `goods` WHERE `id` = ?");

        mysqli_stmt_bind_param($stmt, 'i', $itemId);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_row($result);

        memcache_set($mc, 'good:' . $itemId, $item, 0, 300);
    }

    header('Content-Type: application/json');
    echo json_encode($item);
});

route('/addItem', function () {

    $conection = connect();

    $stmt = mysqli_prepare($conection, "INSERT INTO `goods` (name, description, price) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssi', $_POST['name'], $_POST['descr'], $_POST['price']);
    mysqli_stmt_execute($stmt);

    $result = 0;

    if (mysqli_stmt_affected_rows($stmt)) {
        $result = 1;
    }

    header('Content-Type: application/json');
    echo json_encode(['result' => $result]);
//    $all = mysqli_fetch_all($result);

//    var_dump($all);
}, 'POST');

route('/updateItem/(\d+)', function ($itemId) {


    $conection = connect();

    $stmt = mysqli_prepare($conection, "UPDATE `goods` SET name = ?, description = ?, price = ? WHERE `id` = ?");
    mysqli_stmt_bind_param($stmt, 'ssii', $_POST['name'], $_POST['descr'], $_POST['price'], $itemId);
    mysqli_stmt_execute($stmt);

    $result = 0;

    if (mysqli_stmt_affected_rows($stmt)) {
        $result = 1;
        $mc = memcache_connect('127.0.0.1', 11211);
        memcache_delete($mc, 'good:' . $itemId);
    }

    header('Content-Type: application/json');
    echo json_encode(['result' => $result]);
});

route('/removeItem/(\d+)', function ($itemId) {
    $conection = connect();

    $stmt = mysqli_prepare($conection, "DELETE FROM `goods` WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $itemId);
    mysqli_stmt_execute($stmt);

    $result = 0;

    if (mysqli_stmt_affected_rows($stmt)) {
        $mc = memcache_connect('127.0.0.1', 11211);
        memcache_delete($mc, 'good:' . $itemId);

        $result = 1;
    }

    header('Content-Type: application/json');
    echo json_encode(['result' => $result]);
});



error(404, function () {
    echo 'Page Not Found';
});

route(getenv('REQUEST_URI'));

