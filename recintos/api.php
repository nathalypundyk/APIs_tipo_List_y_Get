<?php
header("Content-Type: application/json");

$recintos = [
    ["id" => 1, "nombre" => "Zona Selvática", "capacidad" => 5],
    ["id" => 2, "nombre" => "Zona Desértica", "capacidad" => 3],
    ["id" => 3, "nombre" => "Zona Acuática", "capacidad" => 4]
];

echo json_encode($recintos);
?>
