<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../exceptions/apiException.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mail;
    private array $env;

    public function __construct(array $env)
    {
        $this->env = $env;
        $this->mail = new PHPMailer(true);

        // SMTP
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $this->env['EMAIL'];
        $this->mail->Password   = $this->env['EMAIL_PASS'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;

        $this->mail->setFrom($this->env['EMAIL'], 'Agente Pedro');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendCode(string $email, string $codigo, string $html): void
    {
        try{
            $this->mail->clearAddresses();
            $this->mail->addAddress($email);

            $this->mail->Subject = 'Código de Verificação Seye-ERP';
            $this->mail->Body    = $this->carregarTemplate($codigo, $html);

            $this->mail->send();
        }
        catch(Exception $e){
            throw new ApiException('Erro ao enviar email', 500);
        }    
    }

    private function carregarTemplate(string $codigo, string $html): string
    {
        $caminho = __DIR__ . '/' . $html. '.html';

        if (!file_exists($caminho)) {
            throw new Exception('Template de e-mail não encontrado');
        }

        $html = file_get_contents($caminho);

        return str_replace(
            ['{{CODIGO}}', '{{ANO}}'],
            [$codigo, date('Y')],
            $html
        );
    }
}


?>
