<?php

// model includes
include_once "../model/user.php";
include_once "../model/file.php";

// Database connection
$host = "localhost";
$user = "doeb";
$password = "admin";
$dbname = "openLibShare";

$dbObj = new mysqli($host, $user, $password, $dbname);

if ($dbObj->connect_error) {
    die("<strong>DB-Connection failed</strong><br>Reason: " 
            . $dbObj->connect_error);
} else {
    // nice for debugging
    //echo "<pre>Connected successfully</pre>";
}

function login(string $email, string $password) {

    global $dbObj;

    $sql = "select uid, username, email, password, fk_pid from user where email = ?";

    //echo "<pre>SQL: " . $sql . "</pre>";

    $stmt = $dbObj->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $user = new User();
    $stmt->bind_result($user->id, $user->username, $user->email, $user->password, $user->permission);

    
    if ($stmt->fetch()) {
        //echo "<pre>got into fetch method!</pre>";
        //print_r($user);

        if (password_verify($password, $user->password)) {
            //echo "<pre>Password match!</pre>";
            return $user;
        } else {
            // passwords do not match
            echo "<pre>Password do not match!</pre>";
            return null;
        }
 
    } else {
        echo "<pre>empty user fetch failed!</pre>";
        return null;
    }
}

// registration
function registration(string $username, string $email, string $password) {
    global $dbObj;
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $dbObj->prepare("insert into user (username, email, password) values (?,?,?)");
    $stmt->bind_param("sss", $username, $email, $hash);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

// pdf upload
function uploadPdf(string $filename) {
    global $dbObj;

    $stmt = $dbObj->prepare("insert into file (fk_uid, filename) values (?,?)");

    $stmt->bind_param("is", $_SESSION["uid"], $filename);
    $success = $stmt->execute();
    
    if(!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

// Load Data out of DB

function loadAllUsers() {
    global $dbObj;

    $result = $dbObj->query("select * from user order by username");

    $users = [];
    while (($u = $result->fetch_assoc()) != null) {
        $user = new User();
        $user->id = intval($u["uid"]);
        $user->username = $u["username"];
        $user->email = $u["email"];
        $user->permission = $u["fk_pid"];
        $users[] = $user;
    }

    return $users;
}

// PDF Management

function loadAllFiles() {
    global $dbObj;

    $result = $dbObj->query("select * from file");

    while(($f = $result->fetch_assoc()) != null) {
        $file = new File();
        $file->id = intval($f['fid']);
        $file->fk_uid = intval($f['fk_uid']);
        $file->filename = $f['filename'];
        $files[] = $file;
    }

    return $files;
}

/*function loadAllPdfsAndTags() {
    global $dbObj;

    $result = $dbObj->query("select * from file left join tag on file.fid = tag.fk_fid");

    $files = [];

    while (($f = $result->fetch_assoc()) != null) {
        $file = new File();
        $file->id = intval($f["fid"]);
        $file->fk_uid = intval($f["fk_uid"]);
        $file->filename = $f["filename"];

        $files[] = $file;
    }
    return $files;
}*/

function loadUploadsByUser(int $uid) {
    global $dbObj;

    $stmt = $dbObj->prepare("select * from file where fk_uid = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    $result = $stmt->get_result();

    $files = [];
    while (($f = $result->fetch_assoc()) != null) {
        $file = new File();
        $file->id = intval($f["fid"]);
        $file->fk_uid = intval($f["fk_uid"]);
        $file->filename = $f["filename"];

        $files[] = $file;
    }

    return $files;
}

function deleteFileByName(string $filename) {
    global $dbObj;

    $stmt = $dbObj->prepare("delete from file where filename = ?");
    $stmt->bind_param("s", $filename);
    $stmt->execute();
}

function getFileIdByName(string $filename) {
    global $dbObj;

    $stmt = $dbObj->prepare("select fid from file where filename = ?");
    $stmt->bind_param("s", $filename);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        return intval($row["fid"]);
    }
    
    return null;
}

// Tag Management

function addTagToFile(int $fid, string $tagValue) {
    global $dbObj;

    $stmt = $dbObj->prepare("insert into tag (fk_fid, value) values (?, ?)");
    $stmt->bind_param("is", $fid, $tagValue);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

function loadTagsByFileWithId(int $fid) {
    global $dbObj;

    $stmt = $dbObj->prepare("select tid, fk_fid, attribut, value from tag where fk_fid = ?");
    $stmt->bind_param("i", $fid);
    $stmt->execute();

    $result = $stmt->get_result();

    $tags = [];
    while (($t = $result->fetch_assoc()) != null) {
        $tags[] = $t;
    }

    return $tags;
}

function deleteTagFromFile(int $tid) {
    global $dbObj;

    $stmt = $dbObj->prepare("delete from tag where tid = ?");
    $stmt->bind_param("i", $tid);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

// User Management

function deleteUser(int $uid) {
    global $dbObj;

    $stmt = $dbObj->prepare("delete from user where uid = ?");
    $stmt->bind_param("i", $uid);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

function deleteAllTagsFromFile(int $fid) {
    global $dbObj;

    $stmt = $dbObj->prepare("delete from tag where fk_fid = ?");
    $stmt->bind_param("i", $fid);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

function searchFilesByTag($tagValue) {
    global $dbObj;
    $stmt = $dbObj->prepare("SELECT DISTINCT f.* FROM file f 
                             INNER JOIN tag t ON f.fid = t.fk_fid 
                             WHERE LOWER(t.value) LIKE ?");
    $searchTerm = '%' . strtolower($tagValue) . '%';
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $files = [];
    while ($f = $result->fetch_assoc()) {
        $file = new File();
        $file->id = intval($f['fid']);
        $file->fk_uid = intval($f['fk_uid']);
        $file->filename = $f['filename'];
        $files[] = $file;
    }
    return $files;
}