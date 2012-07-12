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
    echo '<h2>' . T_("Board management") . '</h2>' . PHP_EOL .
    '<table class="table">' . PHP_EOL .
    '    <tr>' . PHP_EOL .
    '        <th>ID</th>' . PHP_EOL .
    '        <th>URL</th>' . PHP_EOL .
    '        <th>Name</th>' . PHP_EOL .
    '        <th>Description</th>' . PHP_EOL .
    '        <th>Order</th>' . PHP_EOL .
    '        <th>Category</th>' . PHP_EOL .
    '        <th>International</th>' . PHP_EOL .
    '        <th>Pages</th>' . PHP_EOL .
    '        <th>Locked</th>' . PHP_EOL .
    '        <th>Worksafe</th>' . PHP_EOL .
    '        <th>Ads</th>' . PHP_EOL .
    '        <th>Namefield</th>' . PHP_EOL .
    '        <th>Default name</th>' . PHP_EOL .
    '        <th>Show empty names</th>' . PHP_EOL .
    '        <th>Default style</th>' . PHP_EOL .
    '        <th>Hide sidebar</th>' . PHP_EOL .
    '        <th>&nbsp;</th>' . PHP_EOL .
    '    </tr>' . PHP_EOL;

    $result = mysql_query('SELECT boards.id, boards.url, boards.name, boards.description, boards.order, categories.name as category, boards.international, boards.pages, boards.locked, boards.worksafe, boards.ad_category, boards.namefield, boards.default_name, boards.show_empty_names, boards.default_style, boards.hide_sidebar FROM boards LEFT OUTER JOIN categories ON categories.id = boards.category');
    if (mysql_num_rows($result) == 0) {
        echo '<tr><td colspan="17">No Boards!</td></tr>' . PHP_EOL;
    } else {
        while ($row = mysql_fetch_assoc($result)) {
            echo '    <tr>' . PHP_EOL .
            '        <td>' . $row['id'] . '</td>' . PHP_EOL .
            '        <td>' . $row['url'] . '</td>' . PHP_EOL .
            '        <td>' . $row['name'] . '</td>' . PHP_EOL .
            '        <td>' . $row['description'] . '</td>' . PHP_EOL .
            '        <td>' . $row['order'] . '</td>' . PHP_EOL .
            '        <td>' . $row['category'] . '</td>' . PHP_EOL .
            '        <td>' . ($row['international'] == 1 ? '<b style="color: #009900;">true</b>' : '<b style="color: #990000;">false</b>') . '</td>' . PHP_EOL .
            '        <td>' . $row['pages'] . '</td>' . PHP_EOL .
            '        <td>' . ($row['locked'] == 1 ? '<b style="color: #009900;">true</b>' : '<b style="color: #990000;">false</b>') . '</td>' . PHP_EOL .
            '        <td>' . ($row['worksafe'] == 1 ? '<b style="color: #009900;">true</b>' : '<b style="color: #990000;">false</b>') . '</td>' . PHP_EOL .
            '        <td>' . ($row['ad_category'] == 0 ? 'no' : 'cat ' . $row['ad_category']) . '</td>' . PHP_EOL .
            '        <td>' . ($row['namefield'] == 1 ? 'tripcode' : ($row['namefield'] == 2 ? 'OP' : 'none')) . '</td>' . PHP_EOL .
            '        <td>' . $row['default_name'] . '</td>' . PHP_EOL .
            '        <td>' . ($row['show_empty_names'] == 1 ? '<b style="color: #009900;">true</b>' : '<b style="color: #990000;">false</b>') . '</td>' . PHP_EOL .
            '        <td>' . $row['default_style'] . '</td>' . PHP_EOL .
            '        <td>' . ($row['hide_sidebar'] == 1 ? '<b style="color: #009900;">true</b>' : '<b style="color: #990000;">false</b>') . '</td>' . PHP_EOL .
            '        <td><a href="edit/' . $row['id'] . '">Edit</a> <a href="delete/' . $row['id'] . '">Delete</a></td>' . PHP_EOL .
            '    </tr>' . PHP_EOL;
        }
    }
    echo '</table>';
} elseif ($_GET['a'] == "add") {
    if (empty($_POST)) {
        $result = mysql_query('SELECT * FROM categories ORDER BY categories.order, categories.id ASC');
        if (mysql_num_rows($result) == 0) {
            echo 'Add some categories first!';
        } else {
            echo '<h2>Create board</h2>' . PHP_EOL .
            '    <form action="' . $cfg['htmldir'] . '/mod/boards/add" method="post" id="adminform">' . PHP_EOL .
            '    <fieldset>' . PHP_EOL .
            '        <legend>Board</legend>' . PHP_EOL .
            '        <label for="url">URL</label>' . PHP_EOL .
            '        <input type="text" name="url" id="url" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="name">Name</label>' . PHP_EOL .
            '        <input type="text" name="name" id="name" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="description">Description</label>' . PHP_EOL .
            '        <textarea name="description" id="description"></textarea><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="order">Order</label>' . PHP_EOL .
            '        <input type="text" name="order" id="order" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="category">Category</label>' . PHP_EOL .
            '        <select name="category" id="category">' . PHP_EOL;
            while ($row = mysql_fetch_assoc($result)) {
                echo '            <option value="' . $row['id'] . '">' . $row['name'] . '</option>' . PHP_EOL;
            }
            echo '        </select><br/><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="international">International</label>' . PHP_EOL .
            '        <input type="checkbox" name="international" id="international" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="pages_t">Pages</label>' . PHP_EOL .
            '        <input type="text" name="pages" id="pages_t" value="15" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="locked">Locked</label>' . PHP_EOL .
            '        <input type="checkbox" name="locked" id="locked" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="worksafe">Worksafe</label>' . PHP_EOL .
            '        <input type="checkbox" name="worksafe" id="worksafe" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="ad_category">Ad category</label>' . PHP_EOL .
            '        <input type="text" name="ad_category" id="ad_category" value="0" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="namefield">Namefield</label>' . PHP_EOL .
            '        <select name="namefield" id="namefield">' . PHP_EOL .
            '            <option value="0">None</option>' . PHP_EOL .
            '            <option value="1">Tripcode</option>' . PHP_EOL .
            '            <option value="2">OP</option>' . PHP_EOL .
            '        </select><br/><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="default_name">Default name</label>' . PHP_EOL .
            '        <input type="text" name="default_name" id="default_name" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="show_empty_names">Show empty names</label>' . PHP_EOL .
            '        <input type="checkbox" name="show_empty_names" id="show_empty_names" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="default_style">Default style</label>' . PHP_EOL .
            '        <select name="default_style" id="default_style">' . PHP_EOL .
            '            <option value="">None</option>' . PHP_EOL;

            foreach ($cfg['themes'] as $key => $val) {
                echo '            <option value="' . $val . '">' . $key . '</option>' . PHP_EOL;
            }

            echo '        </select><br/><br/>' . PHP_EOL . PHP_EOL .
            '        <label for="hide_sidebar_cb">Hide sidebar</label>' . PHP_EOL .
            '        <input type="checkbox" name="hide_sidebar" id="hide_sidebar_cb" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <input type="submit" value="' . T_("Create") . '" name="add" id="add" />' . PHP_EOL .
            '    </fieldset>' . PHP_EOL .
            '    </form>';
        }
    } else {
        $args = array(
            'url' => mysql_real_escape_string($_POST['url']),
            'name' => mysql_real_escape_string($_POST['name']),
            'description' => mysql_real_escape_string($_POST['description']),
            'order' => mysql_real_escape_string($_POST['order']),
            'category' => mysql_real_escape_string($_POST['category']),
            'international' => mysql_real_escape_string($_POST['international']),
            'pages' => mysql_real_escape_string($_POST['pages']),
            'locked' => mysql_real_escape_string($_POST['locked']),
            'worksafe' => mysql_real_escape_string($_POST['worksafe']),
            'ad_category' => mysql_real_escape_string($_POST['ad_category']),
            'namefield' => mysql_real_escape_string($_POST['namefield']),
            'default_name' => mysql_real_escape_string($_POST['default_name']),
            'show_empty_names' => mysql_real_escape_string($_POST['show_empty_names']),
            'default_style' => mysql_real_escape_string($_POST['default_style']),
            'hide_sidebar' => mysql_real_escape_string($_POST['hide_sidebar']),
        );

        /*
          echo '<pre>';
          var_dump($_POST);
          var_dump($args);
          echo '</pre>';
          // */

        $errors = array();

        if (strlen($args['url']) == 0 OR strlen($args['url']) > 20) {
            $errors[] = 'URL length must be between 1 - 20';
        }

        if (strlen($args['name']) == 0) {
            $errors[] = 'Name length must larger than 1';
        }

        if (!is_numeric($args['order']) OR strlen($args['description']) > 11) {
            $errors[] = 'Order must be numeric and length smaller than 11';
        }

        if (is_numeric($args['category'])) {
            $result = mysql_query('SELECT * FROM categories WHERE id = ' . $args['category']);
            if (mysql_num_rows($result) == 0) {
                $errors[] = 'Category not exist';
            }
        } else {
            $errors[] = 'Category not exist';
        }

        if ($args['international'] == 'on') {
            $args['international'] = 1;
        } else {
            $args['international'] = 0;
        }

        if (!is_numeric($args['pages']) OR strlen($args['pages']) > 11 OR $args['pages'] <= 0) {
            $errors[] = 'Page number must be numeric and larger than 0';
        }

        if ($args['locked'] == 'on') {
            $args['locked'] = 1;
        } else {
            $args['locked'] = 0;
        }

        if ($args['worksafe'] == 'on') {
            $args['worksafe'] = 1;
        } else {
            $args['worksafe'] = 0;
        }

        if (!is_numeric($args['ad_category']) OR $args['ad_category'] < 0 OR $args['ad_category'] > 9) {
            $errors[] = 'Ad category number must be numeric and between 0 - 9';
        }

        if (!is_numeric($args['namefield']) OR $args['namefield'] < 0 OR $args['namefield'] > 2) {
            $errors[] = 'Invalid namefield!';
        }

        if ($args['show_empty_names'] == 'on') {
            $args['show_empty_names'] = 1;
        } else {
            $args['show_empty_names'] = 0;
        }

        if (!empty($args['default_style']) AND !in_array($args['default_style'], $cfg['themes'])) {
            $errors[] = 'Invalid default theme';
        }

        if ($args['hide_sidebar'] == 'on') {
            $args['hide_sidebar'] = 1;
        } else {
            $args['hide_sidebar'] = 0;
        }

        if (count($errors) != 0) {
            echo '<h2>Error!</h2>' . implode('<br/>', $errors);
        } else {
            $sql = 'INSERT INTO `boards` (`url`, `name`, `description`, `order`, `category`, ' .
                    '`international`, `pages`, `locked`, `worksafe`, `ad_category`, `namefield`, ' .
                    '`default_name`, `show_empty_names`, `default_style`, `hide_sidebar`) ' .
                    'VALUES ("' . $args['url'] . '", ' .
                    '"' . $args['name'] . '", ' .
                    '"' . $args['description'] . '", ' .
                    '' . $args['order'] . ', ' .
                    '' . $args['category'] . ', ' .
                    '' . $args['international'] . ', ' .
                    '' . $args['pages'] . ', ' .
                    '' . $args['locked'] . ', ' .
                    '' . $args['worksafe'] . ', ' .
                    '' . $args['ad_category'] . ', ' .
                    '' . $args['namefield'] . ', ' .
                    '"' . $args['default_name'] . '", ' .
                    '' . $args['show_empty_names'] . ', ' .
                    '"' . $args['default_style'] . '", ' .
                    '' . $args['hide_sidebar'] . ')';

            $result = mysql_query($sql);
            if ($result == false) {
                echo '<pre>';
                echo mysql_error() . PHP_EOL;
            }

            if (mysql_affected_rows() == 1) {
                echo 'success!';
            } else {
                echo 'error! ';
            }
        }
    }
} elseif ($_GET['a'] == "edit") {
    $id = mysql_real_escape_string($_GET['b']);

    if (!is_numeric($id)) {
        echo 'Board not exist!';
    } elseif (empty($_POST)) {
        $categories = mysql_query('SELECT * FROM categories ORDER BY categories.order, categories.id ASC');
        $board = mysql_query('SELECT * FROM boards WHERE id = ' . $id . ' LIMIT 0, 1');
        if (mysql_num_rows($categories) == 0) {
            echo 'Add some categories first!';
        } elseif (mysql_num_rows($board) != 1) {
            echo 'Board ID not found!';
        } else {
            $board_val = mysql_fetch_assoc($board);

            echo '<h2>Create board</h2>' . PHP_EOL .
            '    <form action="' . $cfg['htmldir'] . '/mod/boards/edit/' . $id . '" method="post" id="adminform">' . PHP_EOL .
            '    <fieldset>' . PHP_EOL .
            '        <legend>Board</legend>' . PHP_EOL .
            '        <label for="url">URL</label>' . PHP_EOL .
            '        <input type="text" name="url" id="url" value="' . $board_val['url'] . '" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="name">Name</label>' . PHP_EOL .
            '        <input type="text" name="name" id="name" value="' . $board_val['name'] . '" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="description">Description</label>' . PHP_EOL .
            '        <textarea name="description" id="description">' . $board_val['description'] . '</textarea><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="order">Order</label>' . PHP_EOL .
            '        <input type="text" name="order" id="order" value="' . $board_val['order'] . '" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="category">Category</label>' . PHP_EOL .
            '        <select name="category" id="category">' . PHP_EOL;
            while ($row = mysql_fetch_assoc($categories)) {
                echo '            <option value="' . $row['id'] . '"' . ($board_val['category'] == $row['id'] ? ' selected="selected"' : '') . '>' . $row['name'] . '</option>' . PHP_EOL;
            }
            echo '        </select><br/><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="international">International</label>' . PHP_EOL .
            '        <input type="checkbox" name="international" id="international"' . ($board_val['international'] == 1 ? ' checked="checked"' : '') . ' /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="pages_t">Pages</label>' . PHP_EOL .
            '        <input type="text" name="pages" id="pages_t" value="' . $board_val['pages'] . '" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="locked">Locked</label>' . PHP_EOL .
            '        <input type="checkbox" name="locked" id="locked"' . ($board_val['locked'] == 1 ? ' checked="checked"' : '') . ' /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="worksafe">Worksafe</label>' . PHP_EOL .
            '        <input type="checkbox" name="worksafe" id="worksafe"' . ($board_val['worksafe'] == 1 ? ' checked="checked"' : '') . ' /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="ad_category">Ad category</label>' . PHP_EOL .
            '        <input type="text" name="ad_category" id="ad_category" value="' . $board_val['ad_category'] . '" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="namefield">Namefield</label>' . PHP_EOL .
            '        <select name="namefield" id="namefield">' . PHP_EOL .
            '            <option value="0"' . ($board_val['namefield'] == 0 ? ' selected="selected"' : '') . '>None</option>' . PHP_EOL .
            '            <option value="1"' . ($board_val['namefield'] == 1 ? ' selected="selected"' : '') . '>Tripcode</option>' . PHP_EOL .
            '            <option value="2"' . ($board_val['namefield'] == 2 ? ' selected="selected"' : '') . '>OP</option>' . PHP_EOL .
            '        </select><br/><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="default_name">Default name</label>' . PHP_EOL .
            '        <input type="text" name="default_name" id="default_name" value="' . $board_val['default_name'] . '" /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="show_empty_names">Show empty names</label>' . PHP_EOL .
            '        <input type="checkbox" name="show_empty_names" id="show_empty_names"' . ($board_val['show_empty_names'] == 1 ? ' checked="checked' : '') . ' /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <label for="default_style">Default style</label>' . PHP_EOL .
            '        <select name="default_style" id="default_style">' . PHP_EOL .
            '            <option value=""' . ($board_val['category'] == '' ? ' selected="selected"' : '') . '>None</option>' . PHP_EOL;

            foreach ($cfg['themes'] as $key => $val) {
                echo '            <option value="' . $val . '"' . ($board_val['category'] == $key ? ' selected="selected"' : '') . '>' . $key . '</option>' . PHP_EOL;
            }

            echo '        </select><br/><br/>' . PHP_EOL . PHP_EOL .
            '        <label for="hide_sidebar_cb">Hide sidebar</label>' . PHP_EOL .
            '        <input type="checkbox" name="hide_sidebar" id="hide_sidebar_cb"' . ($board_val['hide_sidebar'] == 1 ? ' checked="checked' : '') . ' /><br/>' . PHP_EOL .
            PHP_EOL .
            '        <input type="submit" value="' . T_("Create") . '" name="add" id="add" />' . PHP_EOL .
            '    </fieldset>' . PHP_EOL .
            '    </form>';
        }
    } else {
        $args = array(
            'url' => mysql_real_escape_string($_POST['url']),
            'name' => mysql_real_escape_string($_POST['name']),
            'description' => mysql_real_escape_string($_POST['description']),
            'order' => mysql_real_escape_string($_POST['order']),
            'category' => mysql_real_escape_string($_POST['category']),
            'international' => mysql_real_escape_string($_POST['international']),
            'pages' => mysql_real_escape_string($_POST['pages']),
            'locked' => mysql_real_escape_string($_POST['locked']),
            'worksafe' => mysql_real_escape_string($_POST['worksafe']),
            'ad_category' => mysql_real_escape_string($_POST['ad_category']),
            'namefield' => mysql_real_escape_string($_POST['namefield']),
            'default_name' => mysql_real_escape_string($_POST['default_name']),
            'show_empty_names' => mysql_real_escape_string($_POST['show_empty_names']),
            'default_style' => mysql_real_escape_string($_POST['default_style']),
            'hide_sidebar' => mysql_real_escape_string($_POST['hide_sidebar']),
        );

        /*
          echo '<pre>';
          var_dump($_POST);
          var_dump($args);
          echo '</pre>';
          // */

        $errors = array();

        if (strlen($args['url']) == 0 OR strlen($args['url']) > 20) {
            $errors[] = 'URL length must be between 1 - 20';
        }

        if (strlen($args['name']) == 0) {
            $errors[] = 'Name length must larger than 1';
        }

        if (!is_numeric($args['order']) OR strlen($args['description']) > 11) {
            $errors[] = 'Order must be numeric and length smaller than 11';
        }

        if (is_numeric($args['category'])) {
            $result = mysql_query('SELECT * FROM categories WHERE id = ' . $args['category']);
            if (mysql_num_rows($result) == 0) {
                $errors[] = 'Category not exist';
            }
        } else {
            $errors[] = 'Category not exist';
        }

        if ($args['international'] == 'on') {
            $args['international'] = 1;
        } else {
            $args['international'] = 0;
        }

        if (!is_numeric($args['pages']) OR strlen($args['pages']) > 11 OR $args['pages'] <= 0) {
            $errors[] = 'Page number must be numeric and larger than 0';
        }

        if ($args['locked'] == 'on') {
            $args['locked'] = 1;
        } else {
            $args['locked'] = 0;
        }

        if ($args['worksafe'] == 'on') {
            $args['worksafe'] = 1;
        } else {
            $args['worksafe'] = 0;
        }

        if (!is_numeric($args['ad_category']) OR $args['ad_category'] < 0 OR $args['ad_category'] > 9) {
            $errors[] = 'Ad category number must be numeric and between 0 - 9';
        }

        if (!is_numeric($args['namefield']) OR $args['namefield'] < 0 OR $args['namefield'] > 2) {
            $errors[] = 'Invalid namefield!';
        }

        if ($args['show_empty_names'] == 'on') {
            $args['show_empty_names'] = 1;
        } else {
            $args['show_empty_names'] = 0;
        }

        if (!empty($args['default_style']) AND !in_array($args['default_style'], $cfg['themes'])) {
            $errors[] = 'Invalid default theme';
        }

        if ($args['hide_sidebar'] == 'on') {
            $args['hide_sidebar'] = 1;
        } else {
            $args['hide_sidebar'] = 0;
        }

        if (count($errors) != 0) {
            echo '<h2>Error!</h2>' . implode('<br/>', $errors);
        } else {
            $sql = 'UPDATE `boards` SET 
`url` = "' . $args['url'] . '", 
`name` = "' . $args['name'] . '", 
`description` = "' . $args['description'] . '", 
`order` = ' . $args['order'] . ', 
`category` = ' . $args['category'] . ', 
`international` = ' . $args['international'] . ', 
`pages` = ' . $args['pages'] . ', 
`locked` = ' . $args['locked'] . ', 
`worksafe` = ' . $args['worksafe'] . ', 
`ad_category` = ' . $args['ad_category'] . ', 
`namefield` = ' . $args['namefield'] . ',  
`default_name` = "' . $args['default_name'] . '", 
`show_empty_names` = ' . $args['show_empty_names'] . ', 
`default_style` = "' . $args['default_style'] . '", 
`hide_sidebar` = ' . $args['hide_sidebar'] . '
WHERE id = ' . $id . '';

            $result = mysql_query($sql);
            if ($result == false) {
                echo '<pre>';
                echo mysql_error() . PHP_EOL;
            }

            if (mysql_affected_rows() == 1) {
                echo 'success!';
            } else {
                echo 'error! ';
            }
        }
    }
} elseif ($_GET['a'] == "delete") {
    $id = mysql_real_escape_string($_GET['b']);

    if (!is_numeric($id)) {
        echo 'Board not exist!';
    } elseif (empty($_POST)) {
        $board = mysql_query('SELECT * FROM `boards` WHERE id = ' . $id . ' LIMIT 0, 1');
        if (mysql_num_rows($board) == 0) {
            echo 'Board not exist!';
        } else {
            $board_val = mysql_fetch_assoc($board);
            echo '<h2>Are you sure you want to delete board "' . $board_val['name'] . '" ?</h2>';

            echo '<form method="post" action="' . $cfg['htmldir'] . '/mod/boards/delete/' . $id . '">' . PHP_EOL .
            '<a href="' . $cfg['htmldir'] . '/mod/boards/">Cancel</a>' . PHP_EOL .
            '<input type="submit" name="delete" value="Delete" />' . PHP_EOL .
            '<input type="submit" name="delete_all" value="Delete and also delete all posts and files related (propably timeouts if deleting large boards)" />' . PHP_EOL .
            '</form>';
        }
    } else {
        $board = mysql_query('SELECT * FROM `boards` WHERE id = ' . $id . ' LIMIT 0, 1');
        $board_val = mysql_fetch_assoc($board);

        $continue = true;
        if (isset($_POST['delete']) OR isset($_POST['delete_all'])) {
            $sql = 'DELETE FROM `boards` WHERE `id` = ' . $id;
            $result = mysql_query($sql);
            if (mysql_affected_rows() == 1) {
                echo 'Board deleted! ';
            } else {
                echo 'Error deleting board!';
                $continue = false;
            }

            if(isset($_POST['delete_all'])) {
                $sql = 'SELECT `id` FROM `posts` WHERE `board` = ' . $id;
                $result = mysql_query($sql);
                if (mysql_num_rows($result) != 0) {
                    while ($row = mysql_fetch_assoc($result)) {
                        $sql2 = 'SELECT `fileid` FROM `post_files` WHERE `postid ` = ' . $id;
                        $result2 = mysql_query($sql);

                        if (mysql_num_rows($result2) != 0) {
                            while ($row2 = mysql_fetch_assoc($result2)) {
                                $sql3 = 'DELETE FROM `files` WHERE `id` = ' . $row2['fileid'];
                                $result3 = mysql_query($sql3);
                                if ($result3 == false) {
                                    echo mysql_error();
                                    break 2;
                                }
                                $row3 = mysql_fetch_assoc($result3);
                                if ($result3 == false) {
                                    echo 'error deleting file ' . $row2['fileid'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                $sql = 'DELETE FROM `posts` WHERE `board` = ' . $id;
                $result = mysql_query($sql);

                rrmdir($cfg['srvdir'] . "/files/" . $board_val['url']);

                echo 'success!';
            }
        }
    }
} else {
    echo 'empty';
}


echo '</div>';
require $cfg['srvdir'] . '/inc/footer.php';

function rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    //rmdir($dir);
}