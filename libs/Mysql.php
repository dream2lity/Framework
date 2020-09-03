<?php



class Mysql
{
    private $conn;

    function __construct(string $host = '127.0.0.1', string $port = '3306',
                         string $database = 'test',
                         string $user = 'root', string $password = '')
    {
        try {
            $dsn = sprintf('mysql:dbname=%s;host=%s;port=%s', $database, $host, $port);
            $options = [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];
            $this->conn = new PDO($dsn, $user, $password, $options);
            $this->conn->exec('set names utf8');
        } catch (PDOException $e) {
            echo 'MySQL connect failed:' . $e->getMessage();
            die;
        }
    }

    public function exec(string $sql)
    {
        return $this->conn->exec($sql);
    }

    public function query(string $sql)
    {
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function prepare(string $sql, array $params)
    {
        $stm = $this->conn->prepare($sql);
        $execRet = $stm->execute($params);
        try {
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $execRet;
        }
    }
}