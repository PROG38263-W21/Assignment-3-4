<?php
$host = '127.0.0.1';
$db   = 'test';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname;user=$pg_dbuser;password=$pg_dbpassword";
$options = [
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
	 $pdo = new PDO($dsn, $pg_dbuser, $pg_dbpassword, $options);
	 if ($debug && $pdo) {
		echo 'Connection status ok';
	 }
} catch (\PDOException $e) {
	if ($debug) {
		echo 'Connection status bad';
	}
	throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function run_query($query, $args = []) {
	global $pdo;
	if ($debug) {
		echo "$query<br>";
	}

	$stmt = $pdo->prepare($query);
	$result = $stmt->execute($args);

	if ($result == False and $debug) {
		echo "Query failed<br>";
	}
	return $result;
}

//database functions
function get_article_list(){
	$query=
		"SELECT
		articles.created_on as date,
		articles.aid as aid,
		articles.title as title,
		authors.username as author,
		articles.stub as stub
		FROM
		articles
		INNER JOIN
		authors ON articles.author=authors.id
		ORDER BY
		date DESC";
	return run_query($query);
}

function get_article($aid) {
	$query=
		"SELECT
		articles.created_on as date,
		articles.aid as aid,
		articles.title as title,
		authors.username as author,
		articles.stub as stub,
		articles.content as content
		FROM
		articles
		INNER JOIN
		authors ON articles.author=authors.id
		WHERE
		aid=?
		LIMIT 1";
	return run_query($query, $aid);
}

function delete_article($aid) {
	$query= "DELETE FROM articles WHERE aid=?";
	return run_query($query, [$aid]);
}

function add_article($title, $content, $author) {
	$stub = substr($content, 0, 30);
	$aid = str_replace(" ", "-", strtolower($title));
	$query="
		INSERT INTO
		articles
		(aid, title, author, stub, content)
		VALUES
		(?,?,?,?,?)";
	return run_query($query, [$aid, $title, $author, $stub, $content]);
}

function update_article($title, $content, $aid) {
	$query=
		"UPDATE articles
		SET
		title=?,
		content=?
		WHERE
		aid=?";
	return run_query($query, [$title, $content, $aid]);
}

function authenticate_user($username, $password) {
	$query=
		"SELECT
		authors.id as id,
		authors.username as username,
		authors.password as password,
		authors.role as role
		FROM
		authors
		WHERE
		username=?
		AND
		password=?
		LIMIT 1";
	return run_query($query, [$username, $password]);
}
?>
