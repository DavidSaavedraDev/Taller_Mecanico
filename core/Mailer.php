<?php

declare(strict_types=1);

class Mailer
{
    public function send(string $to, string $subject, string $plainMessage, string $htmlMessage): bool
    {
        if (!isValidEmail($to)) {
            return false;
        }

        $host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $port = (int) (getenv('SMTP_PORT') ?: 587);
        $username = trim((string) getenv('SMTP_USER'));
        $password = str_replace(' ', '', (string) getenv('SMTP_PASS'));
        $from = trim((string) (getenv('MAIL_FROM') ?: $username));

        if ($username === '' || $password === '' || !isValidEmail($from)) {
            $this->log('SMTP no configurado o remitente inválido.');
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
            $this->log("No se pudo conectar al SMTP: {$errorNumber} {$errorMessage}");
            return false;
        }

        stream_set_timeout($socket, 15);

        try {
            $this->expect($socket, 220);
            $this->command($socket, 'EHLO localhost', 250);
            $this->command($socket, 'STARTTLS', 220);

            if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new RuntimeException('No se pudo activar TLS en SMTP.');
            }

            $this->command($socket, 'EHLO localhost', 250);
            $this->command($socket, 'AUTH LOGIN', 334);
            $this->command($socket, base64_encode($username), 334);
            $this->command($socket, base64_encode($password), 235);
            $this->command($socket, 'MAIL FROM:<' . $from . '>', 250);
            $this->command($socket, 'RCPT TO:<' . $to . '>', 250);
            $this->command($socket, 'DATA', 354);

            $boundary = '=_TallerMecanico_' . bin2hex(random_bytes(12));
            $headers = [
                'From: Taller Mecánico <' . $from . '>',
                'To: <' . $to . '>',
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
            $this->expect($socket, 250);
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            return true;
        } catch (Throwable $exception) {
            $this->log('Error SMTP: ' . $exception->getMessage());
            fclose($socket);
            return false;
        }
    }

    private function command($socket, string $command, int $expectedCode): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $expectedCode);
    }

    private function expect($socket, int $expectedCode): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $actualCode = (int) substr($response, 0, 3);
        if ($actualCode !== $expectedCode) {
            throw new RuntimeException("SMTP respondió {$actualCode}; se esperaba {$expectedCode}.");
        }
    }

    private function log(string $message): void
    {
        if (filter_var(getenv('SMTP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN)) {
            error_log('[SMTP DEBUG] ' . $message);
        }
    }
}
