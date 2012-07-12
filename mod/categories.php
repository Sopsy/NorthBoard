<?php

/**
 * Board management
 * @author Kehet
 */
require_once '../inc/include.php';

if ($cfg['user_class'] != 1) {
    header("Location: " . $cfg['htmldir'] . "/mod/login/");
    die();
}

$mod_pages = true;
$title = T_("Board administration") . $cfg['site_title'];
require $cfg['srvdir'] . '/inc/header.php';

echo '<div id="padded">' . PHP_EOL;

if (empty($_GET['a'])) {
    echo '<h2>Category management</h2>' . PHP_EOL .
    '<table class="table">' . PHP_EOL .
    '    <tr>' . PHP_EOL .
    '        <th>ID</th>' . PHP_EOL .
    '        <th>Name</th>' . PHP_EOL .
    '        <th>Order</th>' . PHP_EOL .
    '        <th>&nbsp;</th>' . PHP_EOL .
    '    </tr>' . PHP_EOL;

    $result = mysql_query('SELECT `id`, `name`, `order` FROM `categories` ORDER BY `categories`.`order`');
    if (mysql_num_rows($result) == 0) {
        echo '<tr><td colspan="4">No ccategories!</td></tr>' . PHP_EOL;
    } else {
        while ($row = mysql_fetch_assoc($result)) {
            echo '    <tr>' . PHP_EOL .
            '        <td>' . $row['id'] . '</td>' . PHP_EOL .
            '        <td>' . $row['name'] . '</td>' . PHP_EOL .
            '        <td>' . $row['order'] . '</td>' . PHP_EOL .
            '        <td><a href="edit/' . $row['id'] . '">Edit</a> <a href="delete/' . $row['id'] . '">Delete</a></td>' . PHP_EOL .
            '    </tr>' . PHP_EOL;
        }
    }
    echo '</table>';
} elseif ($_GET['a'] == "add") {
    if (empty($_POST)) {
        echo '<h2>Create category</h2>' . PHP_EOL .
        '    <form action="' . $cfg['htmldir'] . '/mod/categories/add" method="post" id="adminform">' . PHP_EOL .
        '    <fieldset>' . PHP_EOL .
        '        <legend>Board</legend>' . PHP_EOL .
        '        <label for="name">Name</label>' . PHP_EOL .
        '        <input type="text" name="name" id="name" /><br/>' . PHP_EOL .
        PHP_EOL .
        '        <label for="order">Order</label>' . PHP_EOL .
        '        <input type="text" name="order" id="order" /><br/>' . PHP_EOL .
        PHP_EOL .
        '        <input type="submit" value="' . T_("Create") . '" name="add" id="add" />' . PHP_EOL .
        '    </fieldset>' . PHP_EOL .
        '    </form>';
    } else {
        $args = array(
            'name' => mysql_real_escape_string($_POST['name']),
            'order' => mysql_real_escape_string($_POST['order']),
        );

        /*
          echo '<pre>';
          var_dump($_POST);
          var_dump($args);
          echo '</pre>';
          // */

        $errors = array();

        if (strlen($args['name']) == 0) {
            $errors[] = 'Name length must larger than 1';
        }

        if (!is_numeric($args['order']) OR strlen($args['description']) > 11) {
            $errors[] = 'Order must be numeric and length smaller than 11';
        }

        if (count($errors) != 0) {
            echo '<h2>Error!</h2>' . implode('<br/>', $errors);
        } else {
            $sql = 'INSERT INTO `categories` (`name`, `order`) ' .
                    'VALUES ("' . $args['name'] . '", ' .
                    '' . $args['order'] . ')';

            $result = mysql_query($sql);
            if ($result == false) {
                echo '<pre>';
                echo mysql_error() . PHP_EOL;
                echo $sql;
            }

            if (mysql_affected_rows() == 1) {
                echo 'success! <a href="' . $cfg['htmldir'] . '/mod/categories">continue</a>';
            } else {
                echo 'error! ';
            }
        }
    }
} elseif ($_GET['a'] == "edit") {
    if (empty($_POST)) {
        $id = mysql_real_escape_string($_GET['b']);

        if (!is_numeric($id)) {
            echo 'Board not exist!';
        } else {
            $result = mysql_query('SELECT `name`, `order` FROM `categories` WHERE `id` = ' . $id . ' LIMIT 0,1');

            if (mysql_num_rows($result) != 1) {
                echo 'Category ID not found!';
            } else {
                $category_val = mysql_fetch_assoc($result);
                echo '<h2>Edit category</h2>' . PHP_EOL .
                '    <form action="' . $cfg['htmldir'] . '/mod/categories/edit/' . $id . '" method="post" id="adminform">' . PHP_EOL .
                '    <fieldset>' . PHP_EOL .
                '        <legend>Board</legend>' . PHP_EOL .
                '        <label for="name">Name</label>' . PHP_EOL .
                '        <input type="text" name="name" id="name" value="' . $category_val['name'] . '" /><br/>' . PHP_EOL .
                PHP_EOL .
                '        <label for="order">Order</label>' . PHP_EOL .
                '        <input type="text" name="order" id="order" value="' . $category_val['order'] . '" /><br/>' . PHP_EOL .
                PHP_EOL .
                '        <input type="submit" value="' . T_("Create") . '" name="add" id="add" value="' . $category_val['order'] . '" />' . PHP_EOL .
                '    </fieldset>' . PHP_EOL .
                '    </form>';
            }
        }
    } else {
        $id = mysql_real_escape_string($_GET['b']);
        $args = array(
            'name' => mysql_real_escape_string($_POST['name']),
            'order' => mysql_real_escape_string($_POST['order']),
        );

        /*
          echo '<pre>';
          var_dump($_POST);
          var_dump($args);
          echo '</pre>';
          // */

        $errors = array();

        if (strlen($args['name']) == 0) {
            $errors[] = 'Name length must larger than 1';
        }

        if (!is_numeric($args['order']) OR strlen($args['description']) > 11) {
            $errors[] = 'Order must be numeric and length smaller than 11';
        }

        if (count($errors) != 0) {
            echo '<h2>Error!</h2>' . implode('<br/>', $errors);
        } else {
            $sql = 'UPDATE `categories` SET
`name` = "' . $args['name'] . '", 
`order` = ' . $args['order'] . ' 
WHERE `id` = ' . $id;

            $result = mysql_query($sql);
            if ($result == false) {
                echo '<pre>';
                echo mysql_error() . PHP_EOL;
                echo $sql;
            }

            if (mysql_affected_rows() == 1) {
                echo 'success! <a href="' . $cfg['htmldir'] . '/mod/categories">continue</a>';
            } else {
                echo 'error! ';
            }
        }
    }
} else {
    echo 'empty';
}


echo '</div>';
require $cfg['srvdir'] . '/inc/footer.php';