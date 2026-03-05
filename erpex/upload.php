<?php

header('Content-Type: application/json');

// Pasta onde as imagens ficam
$diretorio = __DIR__ . "/fotos/produtos/";

// URL pública (ajuste para seu domínio)
$baseUrl = "https://sophia-me13/erpex/fotos/produtos/";

// Garante pasta
if (!is_dir($diretorio)) {
    mkdir($diretorio, 0777, true);
}

// Valida arquivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] != UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Arquivo não enviado"
    ]);
    exit;
}

// Código da foto
$codigo = $_POST['codigo'] ?? '';
if (empty($codigo)) {
    http_response_code(400);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Código da foto não informado"
    ]);
    exit;
}

// Extensão
$ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);

// Nome do arquivo
$nomeArquivo = $codigo . "." . $ext;

// Caminho completo
$caminho = $diretorio . $nomeArquivo;

// Salva
if (!move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
    http_response_code(500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao salvar arquivo"
    ]);
    exit;
}

// URL para carregar no PictureBox
$url = $baseUrl . $nomeArquivo;

// Retorno
echo json_encode([
    "sucesso" => true,
    "url" => $url
]);

?>