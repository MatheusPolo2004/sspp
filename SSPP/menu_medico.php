<?php
session_start();
include "conexao.php";

if (!isset($_SESSION['crm_cpf'])) {
    header("Location: login.html");
    exit();
}

if (strlen($_SESSION['crm_cpf']) == 11) {
    header("Location: menu_paciente.php");
    exit();
}

function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);

    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ex_descricao = $_POST["ex_descricao"];
    $ex_data = $_POST["ex_data"];
    $ex_area = $_POST["ex_area"];
    $ex_pac_cpf = $_POST["ex_pac_cpf"];
    $ex_med_crm = $_SESSION["crm_cpf"];
    $ex_sistema = $_POST["ex_sistema"];
    $ex_obs = $_POST["ex_obs"];

    if (validaCPF($ex_pac_cpf)) {
        if (!empty($_FILES['pdf_file']['tmp_name'])) {
            $fileName = $_FILES['pdf_file']['name'];
            $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
            $fileType = $_FILES['pdf_file']['type'];

            if ($fileType == "application/pdf") {
                $fileContent = file_get_contents($fileTmpPath);

                $insertDados = "INSERT INTO exames (ex_descricao, ex_data, ex_area, ex_pac_cpf, ex_med_crm, ex_sistema, ex_obs, ex_arquivo, ex_nome_arquivo)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $connection->prepare($insertDados);
                $stmt->bind_param("sssssssss", $ex_descricao, $ex_data, $ex_area, $ex_pac_cpf, $ex_med_crm, $ex_sistema, $ex_obs, $fileContent, $fileName);

                if ($stmt->execute()) {
                    echo '<script>alert("Exame salvo com sucesso!"); window.location.href = "menu_medico.php";</script>';
                } else {
                    echo '<script>alert("Erro ao salvar o exame: ' . $stmt->error . '");</script>';
                }
            } else {
                echo '<script>alert("Por favor, envie um arquivo PDF válido.");</script>';
            }
        } else {
            echo '<script>alert("É obrigatório enviar um arquivo PDF!");</script>';
        }
    } else {
        echo '<script>alert("CPF inválido!");</script>';
    }

    $connection->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSPP - Sistema de Saúde Pública e Particular</title>
    <link rel="stylesheet" href="menu_medico.css">
    <link rel="icon" type="image/x-icon" href="img/icon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <header>
        <h1 id="titulo"><strong id="sigla">SSPP - </strong>SISTEMA DE SAÚDE PÚBLICA E PARTICULAR</h1>
    </header>
    <form action="menu_medico.php" method="POST" enctype="multipart/form-data">
    <div class="container">
        <section class="exam-details">
            <h2>Exames Realizados</h2>
            <form>
                <div class="exam-item">
                    <label for="tipo-exame">Tipo de Exame:</label>
                    <input type="text" id="tipo-exame" name="ex_descricao">

                    <label for="data">Data do Exame:</label>
                    <input type="date" id="data" name="ex_data">

                    <label for="Tipo">Área/Especialidade:</label>
                    <input type="text" id="descricao" name="ex_area">

<label for="ex_pac_cpf">CPF do Paciente:</label>
<input type="text" id="ex_pac_cpf" name="ex_pac_cpf" onblur="verificarCPF()">
<span id="cpfFeedback" style="color: red;"></span>

<script>
function verificarCPF() {
    var cpf = document.getElementById("ex_pac_cpf").value;
    var cpfFeedback = document.getElementById("cpfFeedback");

    if (cpf.length === 11) { 
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "verifica_cpf.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.responseText === "nao_existe") {
                    cpfFeedback.innerHTML = 'Paciente não cadastrado com este CPF. <a href="cadastropaciente2.html">Cadastrar?</a><br><br>';
                } else {
                    cpfFeedback.innerHTML = '';
                }
            }
        };
        xhr.send("cpf=" + cpf);
    } else {
        cpfFeedback.innerHTML = 'CPF do paciente inválido. <br><br>';
    }
}
</script>

                    <label>Sistema de Saúde</label>
                    <select name="ex_sistema" id="sistema" required>
                        <option value=""></option>
                        <option value="Público">Público</option>
                        <option value="Particular">Particular</option>
                    </select>
                    <br><br><br>
                    <label for="obs">Observações:</label>
                    <textarea id="obs" name="ex_obs" rows="4" maxlength="150"></textarea>
                </div>
                

                <button type="submit" class="save-btn">Salvar Exame</button>

                <button type="reset" class="save-btn">Limpar Dados</button>

                <div class="upload-container">
                    <span class="upload-instruction">Clique no botão ao lado para anexar o PDF do exame:</span>
                    <label for="file-upload" class="custom-file-upload">Escolher Arquivo</label>
                    <input type="file" id="file-upload" name="pdf_file" accept=".pdf">
                    <span id="file-name" style="margin-left: 10px;"></span>
                </div>

                <div class="conclude-container">
                    <button type="button" class="conclude-btn" onclick="window.location.href= 'menu_medico2.php' ">Visualizar Exames</button>
                    <button type="button" class="conclude-btn" onclick="window.location.href= 'logout.php' ">Sair</button>
                </div>
            </form>
        </section>
        
    </div>
    </form>

    <script>
        document.getElementById('file-upload').addEventListener('change', function() {
            var fileName = this.files[0].name;
            document.getElementById('file-name').textContent = "Arquivo selecionado: " + fileName;
        });
    </script>
    
    <script>

        function saveFormData() {
            const inputs = document.querySelectorAll('input, textarea, select');
    
            inputs.forEach(input => {
                localStorage.setItem(input.id, input.value);
            });
        }
    
        function restoreFormData() {
            const inputs = document.querySelectorAll('input, textarea, select');
    
            inputs.forEach(input => {
                if (localStorage.getItem(input.id)) {
                    input.value = localStorage.getItem(input.id);
                }
            });
        }
  
        function clearFormData() {
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                localStorage.removeItem(input.id);
            });
        }
    
        document.addEventListener('DOMContentLoaded', restoreFormData);
    
        window.addEventListener('beforeunload', saveFormData);
    
        document.querySelector('form').addEventListener('submit', clearFormData);
    </script>
    
</body>
</html>

