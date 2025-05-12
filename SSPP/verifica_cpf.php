<?php
include "conexao.php";

if (isset($_POST['cpf'])) {
    $cpf = $_POST['cpf'];

    $sql = "SELECT * FROM paciente WHERE pac_cpf = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    echo ($result->num_rows > 0) ? 'existe' : 'nao_existe';
    $stmt->close();
    $connection->close();
}
?>
