<?php
class ConexionDB {
    public $conex;
    private $host = "127.0.0.1";
    private $usr = "root";
    private $pass = "";
    private $bdatos = "minizoo";
    private $port = 3306;
    private $error;

    public function conectar() {
        $this->conex = new mysqli($this->host, $this->usr, $this->pass, $this->bdatos, $this->port);
        if ($this->conex->connect_error) {
            $this->error = $this->conex->connect_error;
        }
    }

    public function getError() {
        return $this->error;
    }
}
?>
