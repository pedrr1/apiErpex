<?php
require_once __DIR__ . '/base-repository.php';
class CreateUserRepository extends BaseRepository
{
    private CreateUserCache $cacheUser;
     
    public function __construct(mysqli $db, CreateUserCache $cache) {
        parent::__construct($db);
        $this->cacheUser = $cache;
    }
   

    public function addFoto(string $uidRequest): string
    {
        $extensao = strtolower(pathinfo($_FILES['UserFoto']['name'], PATHINFO_EXTENSION));
        $nomeArquivo = 'user' . $uidRequest . '.' . $extensao;
        $pasta = __DIR__ . '/fotos/user/';
        $caminhoFinal = $pasta . $nomeArquivo;

        if (!move_uploaded_file($_FILES['UserFoto']['tmp_name'], $caminhoFinal)) {
            throw new ApiException("Falha ao salvar arquivo", 500);
        }

        return $nomeArquivo;
    }



    public function getAllInfos(
        string $name,
        string $email,
        string $cpf,
        ?string $telefone = null,
        string $uidRequest,
        ?string $googleUid = null
    ): void {


        $stmt = $this->db->prepare("
    SELECT id, uuid_request, google_uid, nome, email, telefone, cpf
    FROM usuarios
    WHERE (uuid_request = ?
       OR email = ?
       OR cpf = ?
       OR nome = ?
       OR (telefone IS NOT NULL AND telefone = ?)
       OR (google_uid IS NOT NULL AND google_uid = ?)
       )
       AND status_id != 3
    LIMIT 1
");

        $stmt->bind_param(
            "ssssss",
            $uidRequest,
            $email,
            $cpf,
            $name,
            $telefone,
            $googleUid
        );
        $start = microtime(true);

        $stmt->execute();
        $result = $stmt->get_result();

        $duration = (int)((microtime(true) - $start) * 1000);
        if ($row = $result->fetch_assoc()) {
            if ($row['uuid_request'] === $uidRequest) {
                $erro = 'Este dispositivo já está vinculado a uma conta';
            } elseif ($row['email'] === $email) {
                $erro = 'Email já cadastrado';
            } elseif ($row['cpf'] === $cpf) {
                $erro = 'CPF já cadastrado';
            } elseif ($row['telefone'] === $telefone && $telefone !== null) {
                $erro = 'Telefone já cadastrado';
            } elseif ($row['google_uid'] === $googleUid && $googleUid !== null) {
                $erro = 'Conta Google já vinculada';
            } elseif ($row['nome'] === $name) {
                $erro = 'Nome já cadastrado';
            }

            $this->logRepository(
                endpoint: __DIR__,
                metodo: __METHOD__,
                duration: $duration,
                rows: $result->num_rows,
                action: 'SELECT',
                entidade: 'usuarios',
                entidadeId: isset($row['id']) ? (int)$row['id'] : null
            );

            throw new ApiException($erro, 409);
        }
    }

    public function insertAcount(
        string $name,
        string $email,
        string $cpf,
        ?string $telefone = null,
        string $uidRequest,
        ?string $googleUid = null,
        string $senhaHash,
        ?string $pathFoto = null
    ): void {
       
        $sql = "
        INSERT INTO usuarios (
            role_id,
            status_id,
            nome,
            email,
            cpf,
            telefone,
            foto_perfil,
            senha_hash,
            uuid_request,
            google_uid
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

        $stmt = $this->db->prepare($sql);

        $roleId   = 2; // USER
        $statusId = 1; // ATIVO

        $stmt->bind_param(
            "iissssssss",
            $roleId,
            $statusId,
            $name,
            $email,
            $cpf,
            $telefone,
            $pathFoto,     // pode ser null
            $senhaHash,
            $uidRequest,
            $googleUid     // pode ser null
        );

        $start = microtime(true);

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {

            // duplicidade (UNIQUE)
            if ($e->getCode() === 1062) {
                throw new ApiException('Já existe um usuário com esses dados', 409);
            }

            throw $e;
        }

        $duration = (int)((microtime(true) - $start) * 1000);
        $userId = (int)$this->db->insert_id;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $stmt->affected_rows,
            action: 'INSERT',
            entidade: 'usuarios',
            entidadeId: $userId,
            before: null,
            after: [
                'id' => $userId,
                'nome' => $name,
                'email' => $email,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'uuid_request' => $uidRequest,
                'foto_perfil' => $pathFoto,
                'google_uid' => $googleUid,
                'role_id' => $roleId,
                'status_id' => $statusId
            ]
        );
    }

    private function getEmailRepository(string $email): void
    {
        $start = microtime(true);

        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ? AND status_id != 3 LIMIT 1");
        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $duration = (int)((microtime(true) - $start) * 1000);
        $user = $result->fetch_assoc();


        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $result->num_rows,
            action: 'SELECT',
            entidade: 'usuarios',
            entidadeId: isset($user['id']) ? (int)$user['id'] : null
        );


        if ($user) {
            throw new ApiException("Email ja Existente", 409);
        }
    }

    public function verifyEmailCode(string $email, string $code): void
    {
        //esse codigo  do redis deixa em outra funçao de ver
        $this->getEmailRepository($email);
        $this->cacheUser->getSetEmailCode($email, $code);
    }

    public function insertCodeEmail(string $email, string $code): void
    {
        $savedCode = $this->cacheUser->compareEmailCode($email);

        if (!hash_equals($savedCode, $code)) {
            throw new ApiException(
                'Código não exite ou Expirou',
                422
            ); // código inválido
        }
        $this->cacheUser->delEmailCode($email);
    }
}
