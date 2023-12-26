<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, DELETE');

$host = "localhost";
$db_name = "playlbrx_play";
$username = "playlbrx_play";
$password = "Mv@36343634";

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]);
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['_method'])) {
    try {
        // Coletando dados do formulário
        $title = $_POST['title'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $colors = json_decode($_POST['colors']);
        $sizes = json_decode($_POST['sizes']);
        $quantities = json_decode($_POST['quantities']);
        $prices = json_decode($_POST['prices']);
        $fabric = $_POST['fabric'];

        // Verificar se é um link ou um arquivo
        if (filter_var($_POST['image'], FILTER_VALIDATE_URL)) {
            // Se for um link, considera diretamente como o URL da imagem
            $image = $_POST['image'];
        } else {
            // Coletar dados do arquivo
            $image = $_FILES['image'];

            // Verificar se há erro no upload do arquivo
            if ($image['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo de imagem.']);
                exit();
            }

            // Verificar o tipo do arquivo (imagem)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($image['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não suportado. Apenas imagens JPEG, PNG ou GIF são permitidas.']);
                exit();
            }

            // Salvar a imagem
            $uploadDirectory = 'produtos/';
            $uploadedFileName = uniqid() . '-' . basename($image['name']);
            $targetPath = $uploadDirectory . $uploadedFileName;

            if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
                echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem.']);
                exit();
            }

            $image = $uploadedFileName; // Atualiza $image para o nome do arquivo salvo
        }

        // Imagem salva com sucesso, agora podemos inserir no banco de dados
        // Preparando a consulta SQL para inserção
        $sql = "INSERT INTO produtos (title, image, description, quantity, colors, sizes, quantities, prices, fabric) 
                VALUES (:title, :image, :description, :quantity, :colors, :sizes, :quantities, :prices, :fabric)";
        $stmt = $conn->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':colors', json_encode($colors));
        $stmt->bindParam(':sizes', json_encode($sizes));
        $stmt->bindParam(':quantities', json_encode($quantities));
        $stmt->bindParam(':prices', json_encode($prices));
        $stmt->bindParam(':fabric', $fabric);

        // Executando a consulta
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar o produto. Verifique os dados e tente novamente.']);
    }
} elseif (($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['_method'])) {
    try {
        // Obtendo o ID do produto a ser atualizado
        $id = $_POST['_method'];

        // Coletando dados do formulário
        $title = $_POST['title'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $colors = json_decode($_POST['colors']);
        $sizes = json_decode($_POST['sizes']);
        $quantities = json_decode($_POST['quantities']);
        $prices = json_decode($_POST['prices']);
        $fabric = $_POST['fabric'];

        // Verificar se é um link ou um arquivo
        if (filter_var($_POST['image'], FILTER_VALIDATE_URL)) {
            // Se for um link, considera diretamente como o URL da imagem
            $image = $_POST['image'];
        } else {
            // Coletar dados do arquivo
            $image = $_FILES['image'];

            // Verificar se há erro no upload do arquivo
            if ($image['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo de imagem.']);
                exit();
            }

            // Verificar o tipo do arquivo (imagem)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($image['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não suportado. Apenas imagens JPEG, PNG ou GIF são permitidas.']);
                exit();
            }

            // Salvar a imagem
            $uploadDirectory = 'produtos/';
            $uploadedFileName = uniqid() . '-' . basename($image['name']);
            $targetPath = $uploadDirectory . $uploadedFileName;

            if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
                echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem.']);
                exit();
            }

            $image = $uploadedFileName; // Atualiza $image para o nome do arquivo salvo
        }

        // Imagem atualizada com sucesso, agora podemos atualizar no banco de dados
        // Preparando a consulta SQL para atualização
        $sql = "UPDATE produtos SET 
                title = :title, 
                image = :image, 
                description = :description, 
                quantity = :quantity, 
                colors = :colors, 
                sizes = :sizes, 
                quantities = :quantities, 
                prices = :prices, 
                fabric = :fabric 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':colors', json_encode($colors));
        $stmt->bindParam(':sizes', json_encode($sizes));
        $stmt->bindParam(':quantities', json_encode($quantities));
        $stmt->bindParam(':prices', json_encode($prices));
        $stmt->bindParam(':fabric', $fabric);

        // Executando a consulta
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o produto. Verifique os dados e tente novamente.']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    try {
        // Obtendo o ID do produto a ser excluído
        $id = $_GET['id'];

        // Preparando a consulta SQL para exclusão
        $sql = "DELETE FROM produtos WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);

        // Executando a consulta
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o produto: ' . $e->getMessage()]);
    }
} else {
    try {
        // Consulta SQL para obter todos os produtos
        $sql = "SELECT * FROM produtos";
        $stmt = $conn->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($products);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao recuperar os produtos. Tente novamente mais tarde.']);
    }
}
?>
