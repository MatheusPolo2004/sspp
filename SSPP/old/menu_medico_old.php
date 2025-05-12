<?php
include "conexao.php";
$destino = 'menu_medico.html';
session_start();

function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);

    if (strlen($cpf) != 11) {
        return false;
    }

    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

// Adicionar as informações do médico no banco de dados, incluindo o arquivo PDF
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ex_descricao = $_POST["ex_descricao"];
    $ex_data = $_POST["ex_data"];
    $ex_area = $_POST["ex_area"];
    $ex_pac_cpf = $_POST["ex_pac_cpf"];
    $ex_med_crm = $_SESSION["crm_cpf"];
    $ex_sistema = $_POST["ex_sistema"];
    $ex_obs = $_POST["ex_obs"];

    // Verificar a validade do CPF
    $vcpf = validaCPF($ex_pac_cpf);

    if ($vcpf == true) {
        // Verifica se o campo de arquivo foi enviado e não está vazio
        if (!empty($_FILES['pdf_file']['tmp_name'])) {
            $fileName = $_FILES['pdf_file']['name']; // Nome original do arquivo
            $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
            $fileType = $_FILES['pdf_file']['type'];

            // Verifica se o arquivo é um PDF
            if ($fileType == "application/pdf") {
                // Lê o conteúdo do arquivo
                $fileContent = file_get_contents($fileTmpPath);

                // Prepara o SQL para inserir os dados, incluindo o arquivo PDF e o nome original
                $insertDados = "INSERT INTO exames (ex_descricao, ex_data, ex_area, ex_pac_cpf, ex_med_crm, ex_sistema, ex_obs, ex_arquivo, ex_nome_arquivo)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                // Prepara a query
                $stmt = $connection->prepare($insertDados);
                $stmt->bind_param(
                    "sssssssss",  // Agora temos 9 campos, então a string precisa ter 9 's'
                    $ex_descricao,
                    $ex_data,
                    $ex_area,
                    $ex_pac_cpf,
                    $ex_med_crm,
                    $ex_sistema,
                    $ex_obs,
                    $fileContent,
                    $fileName  // Nome do arquivo original
                );

                if ($stmt->execute()) {
                    echo '<script type="text/javascript">';
                    echo 'alert("Exame salvo com sucesso!");';
                    echo 'window.location.href = "' . $destino . '";';
                    echo '</script>';
                } else {
                    echo "Erro ao salvar o exame: " . $stmt->error;
                }
            } else {
                echo '<script type="text/javascript">';
                echo 'alert("Por favor, envie um arquivo PDF válido.");';
                echo 'window.location.href = "' . $destino . '";';
                echo '</script>';
            }
        } else {
            // Exibe um alerta caso o arquivo PDF não tenha sido anexado
            echo '<script type="text/javascript">';
            echo 'alert("É obrigatório enviar um arquivo PDF!");';
            echo 'window.location.href = "' . $destino . '";';
            echo '</script>';
        }
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("CPF inválido!");';
        echo 'window.location.href = "' . $destino . '";';
        echo '</script>';
    }

    // Fecha a conexão
    $connection->close();
}
?>
