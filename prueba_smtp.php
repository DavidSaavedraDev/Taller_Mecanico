<?php

declare(strict_types=1);

require __DIR__ . '/config/app.php';

echo "============================<br>";
echo "PRUEBA DE CONFIGURACIÓN SMTP<br>";
echo "============================<br><br>";

echo "Usuario SMTP: " . getenv('SMTP_USER') . "<br>";

$password = getenv('SMTP_PASS');

echo "Contraseña cargada: ";

if ($password === false || $password === '') {
    echo "NO<br>";
} else {
    echo "SÍ<br>";
    echo "Cantidad de caracteres: " . strlen($password) . "<br>";
}

echo "Servidor SMTP: " . getenv('SMTP_HOST') . "<br>";
echo "Puerto SMTP: " . getenv('SMTP_PORT') . "<br>";
echo "Remitente: " . getenv('MAIL_FROM') . "<br>";
