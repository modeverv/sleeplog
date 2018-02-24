<?php
error_reporting(E_ALL);
setlocale(LC_ALL, "ja_JP.utf8");
date_default_timezone_set("Asia/Tokyo");

/**
 * sleeplog
 */

require_once "config.php";

$DEBUG = false;

/**
 * debug print
 *
 * @param $obj object to debug print
 */
function dp($obj)
{
    global $DEBUG;
    if ($DEBUG) {
        echo "<pre>";
        var_dump($obj);
        echo "</pre>";
    }
}
/**
 * get DB connection(PDO)
 *
 * @return PDO db handle
 */
function getDB()
{
    global $DBFILE;
    $dsn = 'sqlite:' . $DBFILE;
    $user = '';
    $pass = '';
    $dbh;
    try {
        $dbh = new PDO($dsn, $user, $pass);
    } catch (PDOException $e) {
        echo ('Error:' . $e->getMessage());
        die();
    }
    return $dbh;
}
/**
 * executeSQL
 *
 * @param $sql String SQL string
 * @param $param Array SQL parameter to bind
 * @return result of sql execution result
 */
function executeSQL($sql, $param = null)
{
    $is_select = preg_match("/select/i", $sql) == 1;
    dp($is_select);
    dp($sql);
    $pdo = getDB();
    try {
        dp(1);
        $stmt = $pdo->prepare($sql);
        dp(2);
        if (null == $param) {
            $stmt->execute();
        } else {

            dp($param);
            dp($stmt);
            dp(3);
            $stmt->execute($param);
        }
        dp(4);
        if ($is_select) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return 0;
        }

    } catch (Exception $e) {
        echo ($e->getMessage());
        die();
    }
}

/**
 * PRG pattern
 */
function doPRG()
{
    global $DEBUG;
    if (!$DEBUG) {
        header("Location: " . $_SERVER['PHP_SELF']);
    }
}

/**
 * dispatcher
 */
function dispatch()
{
    // execute SQL task when receive submit request
    if (isset($_REQUEST["submit"])) {
        run();
        doPRG();
    }
}

function s2h($seconds)
{
    if (null == $seconds) {return "";}
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    $hms = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    return $hms;
}

function display_log()
{
    $sql = "SELECT
    datetime(sleep_at,'localtime') sleep_at,
    datetime(wakeup_at,'localtime') wakeup_at,
    strftime('%s', wakeup_at) - strftime('%s', sleep_at) sleepsec
    FROM log ORDER BY id DESC LIMIT 365 * 3;";
    $rows = executeSQL($sql);
    $html = "<table>";
    $html .= "<tr>
       <th>sleep</th>
       <th>wakeup</th>
       <th>sleep</th>
    </tr>";
    foreach ($rows as $row) {
        dp($row);
        $html .= "<tr>";
        $html .= "<td>";
        $html .= $row["sleep_at"];
        $html .= "</td>";
        $html .= "<td>";
        $html .= $row["wakeup_at"];
        $html .= "</td>";
        $html .= "<td>";
        $html .= s2h($row["sleepsec"]);
        $html .= "</td>";
        $html .= "</tr>";
    }
    $html .= "<table>";
    return $html;
}

/**
 * run
 */
function run()
{
    $check_stated_sql = "SELECT count(*) as started, id FROM log WHERE wakeup_at is null;";
    $started_result = executeSQL($check_stated_sql);
    $started = $started_result[0]["started"] == "1";
    if ($started) {
        $sql = "UPDATE log SET wakeup_at = CURRENT_TIMESTAMP WHERE id = '" . $started_result[0]["id"] . "';";
        executeSQL($sql);
    } else {
        global $USERNAME;
        var_dump($USERNAME);
        $sql = "INSERT INTO log(username, sleep_at) VALUES(:user_name, CURRENT_TIMESTAMP);";
        executeSQL($sql, [":user_name" => $USERNAME]);
    }
}

// main
dispatch();

?><!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
<title>Sleep Log</title>
<link rel="stylesheet" href="css/style.css?<?php echo time(); ?>">
</head>
<body>
<header>
  <h1>Sleep Log</h1>
</header>
<article>
  <section class="input">
    <form method="POST">
        <input type="submit" class="submit-btn" name="submit" value="Logging"/>
    </form>
  </section>
  <section class="table"><?php echo display_log(); ?></section>
</article>
<nav>
  <ul>
    <li><a href="#"></a></li>
    <li><a href="#"></a></li>
    <li><a href="#"></a></li>
  </ul>
</nav>
<footer>
  <p></p>
</footer>
</body>
</html>
