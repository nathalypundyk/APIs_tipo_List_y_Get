<?php
header("Content-Type: application/json");

$especies = [
    ["id" => 1, "nombre" => "Leon", "alimentacion" => "Carnivoro"],
    ["id" => 2, "nombre" => "Tigre", "alimentacion" => "Carnivoro"],
    ["id" => 3, "nombre" => "Elefante", "alimentacion" => "Herbivoro"]
];

echo json_encode($especies);
?>
