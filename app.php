<?php 

require_once "lib/router/router.php";
require_once "lib/parsedown/parsedownExt.php";

$db = new mysqli("localhost", "p.janiak", "99988877p", "karjes_all");
if ($db->connect_errno) {
    error(505);
}

function require_logged() {
    global $logged;
    if ($logged === true) {
        // ok
    } else {
        error(403);
    }
}

$logged = false;
if (isset($_SESSION['email'], $_SESSION['pass'])) {
    $query = $db->query('SELECT * FROM `users` WHERE `email` = \''.$_SESSION['email'].'\'');
    if ($row = $query->fetch_assoc()) {
        if ($row['pass'] == $_SESSION['pass']) {
            $logged = true;
        }
    }
}

route('/', function() {
    echo '<a href="/panel/">Panel</a>';
});

route('/panel/login/', function() {
    global $db;
    if (isset($_POST['email'], $_POST['pass'], $_POST['redir'])) {
        $query = $db->query('SELECT * FROM `users` WHERE `email` = \''.$_POST['email'].'\'');
        if ($row = $query->fetch_assoc()) {
            if ($row['pass'] == $_POST['pass']) {
                $_SESSION['email'] = $row['email'];
                $_SESSION['pass'] = $row['pass'];
            }
        }
        redirect($_POST['redir']);
    } else {
        redirect('/panel/');
    }
});

route('/panel/logout/', function() {
    require_logged();
    unset($_SESSION['email']);
    unset($_SESSION['pass']);
    redirect('/panel/');
});

route('/panel/', function() {
    require_logged();
    global $db;
    $queryB = $db->query('SELECT * FROM `storage` WHERE `type` = \'book\'');
    $books = array();
    while($row = $queryB->fetch_assoc()) {
        $books[] = $row;
    }
    $queryW = $db->query('SELECT * FROM `storage` WHERE `type` = \'work\'');
    $works = array();
    while($row = $queryW->fetch_assoc()) {
        $works[] = $row;
    }
    render('p-index.html', array(
        'books' => $books,
        'works' => $works
    ));
});

route('/panel/gallery/', function() {
    require_logged();
    render('p-gallery.html',array(
        'images' => array_slice(scandir('static/uploaded/'),2)
    ));
});

route('/panel/gallery/rename/:oldname', function($args) {
    require_logged();
    if (isset($_POST['newname']) and !empty(trim($_POST['newname']))) {
        $old = 'static/uploaded/'.urldecode($args['oldname']);
        $new = 'static/uploaded/'.trim(urldecode($_POST['newname']));
        if (is_file($old) and !is_file($new)) {
            rename($old, $new);
        }
    }
    redirect('/panel/gallery/');
});

route('/panel/gallery/delete/:name', function($args) {
    require_logged();
    if (isset($_POST['confirm'])) {
        $f = 'static/uploaded/'.urldecode($args['name']);
        if (is_file($f)) {
            unlink($f);
        }
        redirect('/panel/gallery/');
    } else {
        render('p-img-confirm.html', array('name'=>urldecode($args['name'])));
    }
});

route('/panel/add/:type', function($args) {
    require_logged();
    global $db;
    if (isset($_POST['name'],$_POST['image']) and !empty(trim($_POST['name'])) and !empty(trim($_POST['image']))) {
        $types = array('book','work');
        if (in_array($args['type'],$types)) {
            $db->query("INSERT INTO `storage`(`name`, `image`, `content`, `type`) VALUES ('".trim($_POST['name'])."','".trim($_POST['image'])."','','".$args['type']."')");
            redirect('/panel/edit/'.$db->insert_id);
        } else {
            redirect('/panel/');
        }
    } else {
        render('p-add.html',array(
            'type' => $args['type'],
            'type_c' => ucfirst($args['type'])
        ));
    }
});

route('/panel/update/:id',function($args) {
    require_logged();
    global $db;
    if (isset($_POST['name'],$_POST['image']) and !empty(trim($_POST['name'])) and !empty(trim($_POST['image']))) {
        $db->query('UPDATE `storage` SET `name` = \''.trim($_POST['name']).'\', `image` = \''.trim($_POST['image']).'\' WHERE `id` = '.$args['id']);
    } 
    redirect('/panel/');
});

route('/panel/edit/:id', function($args) {
    require_logged();
    global $db;
    $query = $db->query('SELECT * FROM `storage` WHERE `id` = '.$args['id']);
    if ($row = $query->fetch_assoc()) {
        $content = $row['content'];
        if (isset($_POST['content'])) {
            $content = $_POST['content'];
        }
        render('p-edit.html',array(
            'id' => $row['id'],
            'name' => $row['name'],
            'content' => $content
        ));
    } else {
        redirect('/panel/');
    }
});

route('/panel/preview', function() {
    require_logged();
    global $db;
    if (isset($_POST['id'], $_POST['content'])) {
        $parser = new ParsedownExtended();
        $md = $parser->textExtended($_POST['content']);
        $query = $db->query('SELECT * FROM `storage` WHERE `id` = '.$_POST['id']);
        if ($row = $query->fetch_assoc()) {
            render('p-preview.html', array(
                'id' => $row['id'],
                'title'=> $row['name'],
                'original'=> $_POST['content'],
                'markdown'=> $md
            ));
        } else {
            redirect('/panel/');
        }
    } else {
        redirect('/panel/');
    }
});

route('/panel/delete/:id', function($args) {
    require_logged();
    global $db;
    if (isset($_POST['confirm'])) {
        $db->query('DELETE FROM `storage` WHERE `id` = '.$args['id']);
        redirect('/panel/');
    } else {
        $query = $db->query('SELECT * FROM `storage` WHERE `id` = '.$args['id']);
        if ($row = $query->fetch_assoc()) {
            render('p-confirm.html', array('element'=>$row));
        } else {
            redirect('/panel/');
        }
    }
});

route('/panel/save/:id', function($args) {
    require_logged();
    global $db;
    if (isset($_POST['content'])) {
        $db->query('UPDATE `storage` SET `content` = \''.$_POST['content'].'\' WHERE `id` = '.$args['id']);
        redirect('/panel/');
    } else {
        error(500);
    }
});

route('/panel/upload', function() {
    require_logged();
    $imgs_types = array("image/jpg", "image/bmp", "image/jpeg", "image/gif", "image/png");
    if (isset($_FILES['file']) and $_FILES['file']['error'] == 0) {
        if (in_array($_FILES['file']['type'],$imgs_types)) {
            $fname = $_FILES['file']['name'];
            if (!is_file("static/uploaded/$fname")) {
                move_uploaded_file($_FILES['file']['tmp_name'], "static/uploaded/$fname");
            } else {
                error(502);
            }
        } else {
            error(501);
        }
    } else {
        error(500);
    }
});

?>