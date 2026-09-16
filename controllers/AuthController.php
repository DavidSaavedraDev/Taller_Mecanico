<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Authorization.php';

class AuthController
{
    private Usuario $usuarioModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->auditLog = new AuditLog();
    }

    public function login(): void
    {
        Session::start();

        if (Session::isAuthenticated()) {
            redirect('index.php?action=' . (Authorization::role() === Authorization::MECANICO ? 'ordenes' : 'clientes'));
        }

        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $warning = Session::getFlash('warning');

        require_once __DIR__ . '/../views/clientes/auth/login.php';
    }

    public function autenticar(): void
    {
        Session::start();

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            redirect('index.php?action=login');
        }

        $email = sanitizeInput($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (!isValidEmail($email) || $password === '') {
            $this->logAudit(null, 'login.failure', null, null, ['email' => $email, 'reason' => 'invalid_input']);
            Session::setFlash('error', 'Correo o contraseña inválidos.');
            redirect('index.php?action=login');
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $this->logAudit($usuario ? (int) $usuario['id'] : null, 'login.failure', 'usuario', $usuario ? (int) $usuario['id'] : null, ['email' => $email, 'reason' => 'invalid_credentials']);
            Session::setFlash('error', 'Correo o contraseña incorrectos.');
            redirect('index.php?action=login');
        }

        if ((int) $usuario['estado'] !== 1) {
            $this->logAudit((int) $usuario['id'], 'login.failure', 'usuario', (int) $usuario['id'], ['email' => $email, 'reason' => 'inactive']);
            Session::setFlash('error', 'Este usuario no está activo.');
            redirect('index.php?action=login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol'] = $usuario['rol'];
        $this->logAudit((int) $usuario['id'], 'login.success', 'usuario', (int) $usuario['id'], ['rol' => $usuario['rol']]);

        redirect('index.php?action=' . ($usuario['rol'] === 'mecanico' ? 'ordenes' : 'clientes'));
    }

    public function registro(): void
    {
        Session::start();

        if (Session::isAuthenticated()) {
            redirect('index.php?action=' . (Authorization::role() === Authorization::MECANICO ? 'ordenes' : 'clientes'));
        }

        $error = Session::getFlash('error');
        require_once __DIR__ . '/../views/clientes/auth/registro.php';
    }

    public function registrar(): void
    {
        Session::start();               

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            redirect('index.php?action=registro');
        }

        $nombre = sanitizeInput($_POST['nombre'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        // El rol no debe ser controlable desde el registro público.
        $rol = 'mecanico';

        if ($nombre === '' || !isValidEmail($email) || strlen($password) < 6) {
            Session::setFlash('error', 'Completa todos los campos con datos válidos. La contraseña debe tener al menos 6 caracteres.');
            redirect('index.php?action=registro');
        }

        if ($this->usuarioModel->existsEmail($email)) {
            Session::setFlash('error', 'El correo electrónico ya está registrado.');
            redirect('index.php?action=registro');
        }

        $guardado = $this->usuarioModel->create($nombre, $email, $password, $rol);

        if (!$guardado) {
            Session::setFlash('error', 'No se pudo registrar el usuario. Inténtalo de nuevo.');
            redirect('index.php?action=registro');
        }

        if (!$this->sendWelcomeEmail($nombre, $email)) {
            Session::setFlash('warning', 'Cuenta creada correctamente, pero no pudimos enviar el correo de bienvenida.');
        }

        redirect('index.php?action=login&msg=registrado');
    }

    public function logout(): void
    {
        Session::start();
        session_unset();
        session_destroy();
        redirect('index.php?action=login');
    }

    public function mostrarRecuperacion(): void
    {
        Session::start();
        $error = Session::getFlash('error');
        require_once __DIR__ . '/../views/clientes/auth/olvide_password.php';
    }

    public function enviarPin(): void
    {
        Session::start();
        $this->verifyPostCsrf('olvide_password');

        $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
        if (!isValidEmail($email)) {
            Session::setFlash('error', 'Ingresa un correo electrónico válido.');
            redirect('index.php?action=olvide_password');
        }

        // La respuesta es la misma exista o no el correo, evitando enumeración de cuentas.
        $usuario = $this->usuarioModel->findByEmail($email);
        $this->smtpLog(sprintf(
            'Solicitud de recuperación: cuenta_encontrada=%s, estado=%s',
            $usuario ? 'sí' : 'no',
            $usuario['estado'] ?? 'no disponible'
        ));
        if ($usuario && (int) $usuario['estado'] === 1) {
            $code = (string) random_int(100000, 999999);
            $expiresAt = date('Y-m-d H:i:s', time() + 300);
            $codeHash = password_hash($code, PASSWORD_DEFAULT);

            $saved = $this->usuarioModel->saveResetCode($email, $codeHash, $expiresAt);
            $this->smtpLog('Código guardado en la base de datos: ' . ($saved ? 'sí' : 'no'));
            if (!$saved) {
                error_log('No se pudo guardar el código de recuperación para el usuario solicitado.');
                Session::setFlash('error', 'No se pudo preparar la recuperación. Inténtalo de nuevo.');
                redirect('index.php?action=olvide_password');
            }

            if (!$this->sendResetEmail($email, $code)) {
                Session::setFlash('error', 'El código se generó, pero no pudo enviarse. Revisa la configuración SMTP.');
                redirect('index.php?action=olvide_password');
            }
        }

        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_attempts'] = 0;
        Session::setFlash('success', 'Si el correo está registrado, recibirás un código en unos minutos.');
        redirect('index.php?action=validar_pin');
    }

    public function validarPin(): void
    {
        Session::start();
        $email = $_SESSION['reset_email'] ?? '';
        if (!isValidEmail((string) $email)) {
            redirect('index.php?action=olvide_password');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $error = Session::getFlash('error');
            require_once __DIR__ . '/../views/clientes/auth/validar_pin.php';
            return;
        }

        $this->verifyPostCsrf('validar_pin');
        $attempts = (int) ($_SESSION['reset_attempts'] ?? 0);
        if ($attempts >= 5) {
            Session::setFlash('error', 'Has superado el número máximo de intentos. Solicita otro código.');
            redirect('index.php?action=olvide_password');
        }

        $code = trim((string) ($_POST['pin'] ?? ''));
        $usuario = $this->usuarioModel->findValidReset($email);
        $validDate = $usuario && !empty($usuario['pin_expiracion'])
            && strtotime($usuario['pin_expiracion']) >= time();

        if (!$usuario || !$validDate || !password_verify($code, (string) $usuario['reset_pin'])) {
            $_SESSION['reset_attempts'] = $attempts + 1;
            $error = $_SESSION['reset_attempts'] >= 5
                ? 'Has superado el número máximo de intentos. Solicita otro código.'
                : 'El código es incorrecto o ya expiró.';
            require_once __DIR__ . '/../views/clientes/auth/validar_pin.php';
            return;
        }

        $_SESSION['reset_user_id'] = (int) $usuario['id'];
        $_SESSION['reset_verified_at'] = time();
        redirect('index.php?action=nueva_password');
    }

    public function reenviarPin(): void
    {
        Session::start();
        $email = strtolower((string) ($_SESSION['reset_email'] ?? ''));

        if (!isValidEmail($email)) {
            redirect('index.php?action=olvide_password');
        }

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $exception) {
            Session::setFlash('error', $exception->getMessage());
            redirect('index.php?action=validar_pin');
        }

        $usuario = $this->usuarioModel->findByEmail($email);
        if (!$usuario || (int) $usuario['estado'] !== 1) {
            Session::setFlash('success', 'Si el correo está registrado, recibirás un código en unos minutos.');
            redirect('index.php?action=validar_pin');
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 300);
        $codeHash = password_hash($code, PASSWORD_DEFAULT);

        if (!$this->usuarioModel->saveResetCode($email, $codeHash, $expiresAt)
            || !$this->sendResetEmail($email, $code)) {
            Session::setFlash('error', 'No se pudo reenviar el código. Revisa la configuración del correo e inténtalo de nuevo.');
            redirect('index.php?action=validar_pin');
        }

        $_SESSION['reset_attempts'] = 0;
        Session::setFlash('success', 'Te enviamos un código nuevo. Revisa tu bandeja de entrada y spam.');
        redirect('index.php?action=validar_pin');
    }

    public function nuevaPassword(): void
    {
        Session::start();
        $this->requireVerifiedReset();
        $error = Session::getFlash('error');
        require_once __DIR__ . '/../views/clientes/auth/nueva_password.php';
    }

    public function guardarPassword(): void
    {
        Session::start();
        $this->requireVerifiedReset();
        $this->verifyPostCsrf('nueva_password');

        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['confirm_password'] ?? '');
        if (strlen($password) < 8 || $password !== $confirmation) {
            Session::setFlash('error', 'Las contraseñas deben coincidir y tener al menos 8 caracteres.');
            redirect('index.php?action=nueva_password');
        }

        $updated = $this->usuarioModel->updatePasswordAndClearReset(
            (int) $_SESSION['reset_user_id'],
            password_hash($password, PASSWORD_DEFAULT)
        );
        if (!$updated) {
            Session::setFlash('error', 'No se pudo actualizar la contraseña.');
            redirect('index.php?action=nueva_password');
        }

        unset($_SESSION['reset_email'], $_SESSION['reset_attempts'], $_SESSION['reset_user_id'], $_SESSION['reset_verified_at']);
        redirect('index.php?action=login&msg=password_actualizada');
    }

    private function verifyPostCsrf(string $action): void
    {
        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            redirect('index.php?action=' . $action);
        }
    }

    private function logAudit(?int $usuarioId, string $accion, ?string $entidad, ?int $entidadId, array $detalles): void
    {
        try {
            $this->auditLog->registrar($usuarioId, $accion, $entidad, $entidadId, $detalles);
        } catch (Throwable $exception) {
            // Auditing must never make authentication unavailable if a legacy
            // installation has not yet run the audit_logs migration.
            error_log('No se pudo registrar auditoría: ' . $exception->getMessage());
        }
    }

    private function requireVerifiedReset(): void
    {
        $verifiedAt = (int) ($_SESSION['reset_verified_at'] ?? 0);
        if (empty($_SESSION['reset_user_id']) || $verifiedAt < time() - 600) {
            unset($_SESSION['reset_user_id'], $_SESSION['reset_verified_at']);
            redirect('index.php?action=olvide_password');
        }
    }

    private function sendResetEmail(string $email, string $code): bool
    {
        $host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $port = (int) (getenv('SMTP_PORT') ?: 587);
        $username = trim((string) getenv('SMTP_USER'));
        $password = str_replace(' ', '', (string) getenv('SMTP_PASS'));
        $from = getenv('MAIL_FROM') ?: $username;
        $this->smtpLog("Configuración: host={$host}, puerto={$port}, usuario={$username}, remitente={$from}");

        if ($username === '' || $password === '' || $from === '') {
            $this->smtpLog('SMTP no configurado: define SMTP_USER, SMTP_PASS y MAIL_FROM.');
            return false;
        }

        $subject = 'Código de recuperación - Taller Mecánico';
        $plainMessage = "Tu código de recuperación es: {$code}\n\nEste código vence en 5 minutos.\n\nSi no solicitaste este cambio, puedes ignorar este correo.";
        $htmlMessage = $this->buildResetEmailHtml($code);
        return $this->sendSmtpEmail($email, $subject, $plainMessage, $htmlMessage);
    }

    private function sendWelcomeEmail(string $name, string $email): bool
    {
        $safeName = e($name);
        $plainMessage = "¡Bienvenido a Taller Mecánico, {$name}!\n\nTu cuenta fue creada correctamente. Ya puedes iniciar sesión y utilizar el sistema.\n\nSi no realizaste este registro, contacta al administrador.";
        $htmlMessage = '<!doctype html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Bienvenido</title></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<div style="padding:32px 12px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 18px rgba(15,23,42,.10);">
<tr><td style="background:#f59e0b;padding:28px 32px;text-align:center;"><div style="font-size:30px;font-weight:700;color:#111827;">Taller Mecánico</div><div style="margin-top:6px;color:#451a03;font-size:14px;">Tu espacio de trabajo</div></td></tr>
<tr><td style="padding:36px 32px;text-align:center;"><div style="font-size:42px;">🎉</div><h1 style="margin:20px 0 10px;font-size:25px;color:#111827;">¡Bienvenido, ' . $safeName . '!</h1><p style="margin:0 auto;max-width:450px;font-size:16px;line-height:1.6;color:#6b7280;">Tu cuenta fue creada correctamente. Ya puedes iniciar sesión y comenzar a utilizar el sistema del taller.</p><div style="margin-top:28px;padding:14px 20px;border-radius:10px;background:#fffbeb;color:#92400e;font-size:14px;">Gracias por confiar en Taller Mecánico.</div></td></tr>
<tr><td style="padding:20px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;text-align:center;"><p style="margin:0;font-size:13px;line-height:1.6;color:#6b7280;">Si no realizaste este registro, contacta al administrador.</p></td></tr>
</table><p style="margin:18px auto 0;text-align:center;font-size:12px;color:#9ca3af;">Este es un mensaje automático. Por favor, no respondas.</p>
</div></body></html>';

        return $this->sendSmtpEmail($email, '¡Bienvenido a Taller Mecánico!', $plainMessage, $htmlMessage);
    }

    private function sendSmtpEmail(string $email, string $subject, string $plainMessage, string $htmlMessage): bool
    {
        $host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $port = (int) (getenv('SMTP_PORT') ?: 587);
        $username = trim((string) getenv('SMTP_USER'));
        $password = str_replace(' ', '', (string) getenv('SMTP_PASS'));
        $from = getenv('MAIL_FROM') ?: $username;
        $this->smtpLog("Configuración: host={$host}, puerto={$port}, usuario={$username}, remitente={$from}");

        if ($username === '' || $password === '' || $from === '') {
            $this->smtpLog('SMTP no configurado: define SMTP_USER, SMTP_PASS y MAIL_FROM.');
            return false;
        }

        $socket = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errorNumber,
            $errorMessage,
            15,
            STREAM_CLIENT_CONNECT
        );

        if ($socket === false) {
            $this->smtpLog("No se pudo conectar al SMTP: {$errorNumber} {$errorMessage}");
            return false;
        }

        stream_set_timeout($socket, 15);

        try {
            $this->smtpExpect($socket, 220);
            $this->smtpCommand($socket, 'EHLO localhost', 250);
            $this->smtpCommand($socket, 'STARTTLS', 220);

            $encrypted = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($encrypted !== true) {
                throw new RuntimeException('No se pudo activar TLS en SMTP.');
            }

            $this->smtpCommand($socket, 'EHLO localhost', 250);
            $this->smtpCommand($socket, 'AUTH LOGIN', 334);
            $this->smtpCommand($socket, base64_encode($username), 334);
            $this->smtpCommand($socket, base64_encode($password), 235);
            $this->smtpCommand($socket, 'MAIL FROM:<' . $from . '>', 250);
            $this->smtpCommand($socket, 'RCPT TO:<' . $email . '>', 250);
            $this->smtpCommand($socket, 'DATA', 354);

            $boundary = '=_TallerMecanico_' . bin2hex(random_bytes(12));
            $headers = [
                'From: Taller Mecánico <' . $from . '>',
                'To: <' . $email . '>',
                'Subject: ' . $subject,
                'MIME-Version: 1.0',
                'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            ];
            $body = implode("\r\n", $headers) . "\r\n\r\n";
            $body .= '--' . $boundary . "\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $plainMessage . "\r\n\r\n";
            $body .= '--' . $boundary . "\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $htmlMessage . "\r\n\r\n";
            $body .= '--' . $boundary . "--\r\n";
            $body = preg_replace('/^\./m', '..', $body) ?? $body;
            fwrite($socket, $body . "\r\n.\r\n");
            $this->smtpExpect($socket, 250);
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            return true;
        } catch (Throwable $exception) {
            $this->smtpLog('Error SMTP de correo: ' . $exception->getMessage());
            fclose($socket);
            return false;
        }
    }

    private function buildResetEmailHtml(string $code): string
    {
        $safeCode = e($code);

        return '<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de recuperación</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="padding:32px 12px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 18px rgba(15,23,42,.10);">
            <tr>
                <td style="background:#f59e0b;padding:28px 32px;text-align:center;">
                    <div style="font-size:30px;font-weight:700;color:#111827;">Taller Mecánico</div>
                    <div style="margin-top:6px;color:#451a03;font-size:14px;">Recuperación de contraseña</div>
                </td>
            </tr>
            <tr>
                <td style="padding:36px 32px;text-align:center;">
                    <div style="font-size:42px;line-height:1;">🔐</div>
                    <h1 style="margin:20px 0 10px;font-size:24px;color:#111827;">Tu código de verificación</h1>
                    <p style="margin:0 auto 24px;max-width:430px;font-size:16px;line-height:1.6;color:#6b7280;">
                        Usa el siguiente código para continuar con la recuperación de tu contraseña:
                    </p>
                    <div style="display:inline-block;padding:16px 28px;border:2px dashed #f59e0b;border-radius:12px;background:#fffbeb;color:#92400e;font-size:36px;letter-spacing:8px;font-weight:700;">
                        ' . $safeCode . '
                    </div>
                    <p style="margin:24px 0 0;font-size:14px;color:#6b7280;">
                        Este código es válido durante <strong style="color:#374151;">5 minutos</strong>.
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding:20px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;text-align:center;">
                    <p style="margin:0;font-size:13px;line-height:1.6;color:#6b7280;">
                        Si no solicitaste este cambio, puedes ignorar este correo.
                        <br>Por seguridad, nunca compartas este código con otras personas.
                    </p>
                </td>
            </tr>
        </table>
        <p style="margin:18px auto 0;max-width:600px;text-align:center;font-size:12px;color:#9ca3af;">
            Este es un mensaje automático. Por favor, no respondas a este correo.
        </p>
    </div>
</body>
</html>';
    }

    private function smtpCommand($socket, string $command, int $expectedCode): void
    {
        fwrite($socket, $command . "\r\n");
        $this->smtpExpect($socket, $expectedCode);
    }

    private function smtpExpect($socket, int $expectedCode): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('El servidor SMTP cerró la conexión sin responder.');
        }

        $this->smtpLog('Respuesta SMTP: ' . trim($response));
        $actualCode = (int) substr($response, 0, 3);
        if ($actualCode !== $expectedCode) {
            throw new RuntimeException("SMTP respondió {$actualCode}; se esperaba {$expectedCode}. Detalle: " . trim($response));
        }
    }

    private function smtpLog(string $message): void
    {
        if (filter_var(getenv('SMTP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN)) {
            $line = '[' . date('Y-m-d H:i:s') . '] [SMTP DEBUG] ' . $message . PHP_EOL;
            error_log(trim($line));
            file_put_contents(__DIR__ . '/../smtp_debug.log', $line, FILE_APPEND | LOCK_EX);
        }
    }
}
