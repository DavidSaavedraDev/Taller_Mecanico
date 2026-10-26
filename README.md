# Taller Mecánico

Aplicación web para la gestión operativa de un taller mecánico. Permite centralizar la información de clientes y vehículos, registrar órdenes de trabajo y controlar su avance mediante un flujo de estados, con acceso protegido por usuarios y roles.

## Funcionalidades

- Registro, inicio y cierre de sesión de usuarios.
- Recuperación de contraseña mediante código PIN enviado por correo.
- Gestión de clientes: creación, consulta, edición y eliminación.
- Registro de vehículos asociados a cada cliente.
- Creación y consulta de órdenes de trabajo.
- Seguimiento del estado de una orden:
  `Recepcionado`, `Diagnóstico`, `Esperando aprobación`, `En reparación`, `Pruebas`, `Listo para entregar` y `Entregado`.
- Carga y visualización de fotografías asociadas a una orden.
- Control de acceso según rol:
  - **Administrador:** usuarios, auditoría, clientes y órdenes.
  - **Recepción:** clientes y creación/consulta de órdenes.
  - **Mecánico:** consulta de órdenes y actualización de sus estados.
- Registro de eventos relevantes en el módulo de auditoría.
- Interfaz adaptable basada en Bootstrap 5.

## Tecnologías utilizadas

- **PHP 8.1+** con una arquitectura sencilla basada en controladores, modelos y vistas.
- **MySQL/MariaDB** para la persistencia de datos.
- **PDO** con consultas preparadas y `utf8mb4`.
- **Apache** para servir la aplicación.
- **Bootstrap 5.3.2** y **Font Awesome 6.4.2**, cargados desde CDN.
- SMTP mediante sockets TLS para el envío de correos de bienvenida y recuperación de contraseña.
- **Docker** para ejecutar la aplicación en un contenedor PHP con Apache.

## Requisitos

Para una instalación local:

- PHP 8.1 o superior.
- Extensión `pdo_mysql` habilitada.
- MySQL o MariaDB.
- Apache, o el servidor de desarrollo integrado de PHP.
- Un servidor SMTP únicamente si se desea habilitar el registro de usuarios con correo de bienvenida y la recuperación de contraseña.

Para la alternativa con Docker:

- Docker instalado y ejecutándose.
- Un servidor MySQL/MariaDB accesible desde el contenedor.

## Instalación local

### 1. Clonar el repositorio

```bash
git clone https://github.com/DavidSaavedraDev/Taller_Mecanico.git
cd Taller_Mecanico
```

### 2. Crear la base de datos

Ejecuta el script incluido en el repositorio:

```bash
mysql -u root -p < database.sql
```

El script crea la base de datos `taller_mecanico` y sus tablas para usuarios, clientes, vehículos, órdenes de trabajo, historial de estados, fotografías y auditoría.

### 3. Configurar las variables de entorno

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Edita `.env` con los datos de tu instalación:

```dotenv
DB_HOST=localhost
DB_NAME=taller_mecanico
DB_USER=root
DB_PASS=tu_contraseña

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-correo@gmail.com
SMTP_PASS=tu-contraseña-de-aplicacion
MAIL_FROM=tu-correo@gmail.com
SMTP_DEBUG=false
```

La configuración SMTP es opcional para utilizar las funciones principales, pero es necesaria para enviar correos. En Gmail debe utilizarse una **contraseña de aplicación**, no la contraseña habitual de la cuenta.

### 4. Iniciar la aplicación

Desde la raíz del proyecto puedes utilizar el servidor integrado de PHP:

```bash
php -S localhost:8000
```

Abre [http://localhost:8000](http://localhost:8000) en el navegador.

Si utilizas Apache, configura el `DocumentRoot` para que apunte a la raíz del proyecto y asegúrate de que el módulo PHP y la extensión `pdo_mysql` estén habilitados.

## Ejecución con Docker

### 1. Construir la imagen

```bash
docker build -t taller-mecanico .
```

### 2. Ejecutar el contenedor

```bash
docker run --name taller-mecanico \
  -p 8080:80 \
  --env-file .env \
  taller-mecanico
```

Abre [http://localhost:8080](http://localhost:8080).

El servidor MySQL debe ser accesible desde el contenedor. Si la base de datos se ejecuta en otro contenedor, utiliza una red Docker compartida y configura `DB_HOST` con el nombre del servicio de base de datos.

## Primer acceso

1. Abre la aplicación y selecciona **Regístrate aquí**.
2. Crea el primer usuario. Los registros públicos se crean con el rol `mecanico`.
3. Inicia sesión y registra clientes y órdenes de trabajo.
4. Para crear administradores o usuarios de recepción, actualiza el rol directamente en la tabla `usuarios` o utiliza la gestión de usuarios desde una cuenta administradora.

Ejemplo de actualización de rol:

```sql
UPDATE usuarios
SET rol = 'admin'
WHERE email = 'admin@ejemplo.com';
```

## Flujo recomendado de uso

1. Registrar el cliente.
2. Crear la orden de trabajo indicando el vehículo y los síntomas reportados.
3. Adjuntar fotografías del vehículo cuando sea necesario.
4. Actualizar el estado de la orden durante el proceso de diagnóstico y reparación.
5. Consultar el historial y marcar la orden como entregada al finalizar.

Las fotografías se almacenan en `uploads/vehiculos/`. El directorio está preparado para recibir archivos JPG, PNG y WEBP de hasta 5 MB por imagen.

## Estructura del proyecto

```text
.
├── config/          # Configuración general y carga del entorno
├── controllers/     # Controladores de autenticación y operaciones del negocio
├── core/            # Sesiones, autorización, conexión a BD, correo y helpers
├── models/          # Acceso a datos de usuarios, clientes, órdenes y auditoría
├── views/           # Vistas PHP y layouts de la interfaz
├── uploads/         # Archivos subidos por los usuarios
├── database.sql     # Esquema inicial de la base de datos
├── Dockerfile       # Imagen PHP con Apache
├── render.yaml      # Variables de entorno para despliegue en Render
└── index.php        # Punto de entrada y enrutador principal
```

## Seguridad y configuración

- No subas el archivo `.env` al repositorio.
- Utiliza contraseñas de aplicación para proveedores SMTP.
- Cambia `SMTP_DEBUG` a `false` en producción.
- Utiliza HTTPS en entornos públicos.
- Realiza copias de seguridad periódicas de la base de datos y de `uploads/vehiculos/`.
- Revisa los permisos del directorio de cargas cuando despliegues en un servidor Linux.

## Despliegue

El repositorio incluye `Dockerfile` y `render.yaml` como base para desplegar la aplicación en Render u otra plataforma compatible con contenedores. Antes de publicar, configura las variables de conexión de la base de datos y evita incluir credenciales reales en archivos versionados.

## Licencia

No se ha definido una licencia de distribución para este proyecto.
