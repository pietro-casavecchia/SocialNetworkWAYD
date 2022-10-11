<?php
function pdo(PDO $pdo, string $sql, array $arguments = null) {
    if (!$arguments) { 
        return $pdo->query($sql);
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($arguments);
    return $statement;
}