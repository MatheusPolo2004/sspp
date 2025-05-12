<?php
// Configurações de conexão com o banco de dados
$host = "localhost";
$user = "root";
$password = "";
$dbname = "sspp";

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $dbname);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verifica se o ID do exame foi passado
if (isset($_GET['id'])) {
    $ex_id = intval($_GET['id']);

    // Consulta para buscar o arquivo PDF com base no ID do exame
    $sql = "SELECT ex_arquivo, ex_nome_arquivo FROM exames WHERE ex_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ex_id);
    $stmt->execute();
    $stmt->store_result();
    
    // Verifica se encontrou algum registro
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($fileContent, $fileName);
        $stmt->fetch();

        // Configura os cabeçalhos para a visualização do PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($fileContent));

        // Envia o conteúdo do arquivo PDF
        echo $fileContent;
    } else {
        echo "Exame não encontrado!";
    }
    
    // Fecha a consulta e a conexão
    $stmt->close();
} else {
    echo "ID do exame não fornecido!";
}

// Fecha a conexão
$conn->close();
?>
