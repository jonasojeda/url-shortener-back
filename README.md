# URL-shortener

Una API que permite acortar URLs, gestionar enlaces y realizar redirecciones. Este proyecto tiene como objetivo facilitar el uso de enlaces cortos en diversas aplicaciones y mejorar la accesibilidad.

## Guía de Inicio Rápido

### Requisitos Previos

-   PHP (versión 8.0 o superior)
-   Composer (gestor de dependencias para PHP)
-   MySQL (o cualquier otro sistema de gestión de bases de datos compatible)
-   Node.js y npm (para el frontend si se utiliza React)
-   Laravel (versión 8.x o superior)

### Instalación

1. Clonar el repositorio:

    ```
    git clone https://github.com/jonasojeda/url-shortener.git
    ```

2. Navegar al directorio del proyecto:

    ```
    cd url-shortener-back

    ```

3. Instalar dependencias:
    ```
    composer install
    ```

### Configuración

1. Copiar el archivo de configuración de ejemplo:

    ```
    cp .env.example .env
    ```

2. Editar el archivo `.env` con tus configuraciones específicas.

3. Generar la clave de aplicación de Laravel:

    ```
      php artisan key:generate

    ```

4. Ejecutar las migraciones para crear las tablas necesarias:

    ```
      php artisan migrate

    ```

### Ejecución Local

1. Iniciar el servidor de desarrollo:

    ```
    php artisan serve
    ```

2. Abrir un navegador y visitar `http://localhost:8000`

## Testing

Este proyecto incluye un conjunto de pruebas automatizadas para garantizar la calidad y la funcionalidad del código. Las pruebas se han implementado utilizando PHPUnit y están diseñadas para verificar el comportamiento de los controladores y modelos de la aplicación.

### Requisitos Previos

-   Pruebas Unitarias: Verifican el comportamiento de métodos individuales y clases.
-   Pruebas de Integración: Aseguran que diferentes partes de la aplicación funcionen juntas correctamente.

### Estructura de Pruebas

-   tests/Unit: Contiene pruebas que verifican la funcionalidad de clases y métodos individuales.
-   tests/Feature: Contiene pruebas que validan el comportamiento de las rutas y la interacción entre diferentes componentes del sistema.

### Ejemplo de Prueba

Un ejemplo de prueba para el controlador UrlController puede verse así:

````php
    /** @test */
public function it_can_store_a_url()
{
    $response = $this->post('/api/url', [
        'url' => 'https://www.example.com',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'url', 'url_key', 'short_url']]);
}

```

### Notas adicionales
- Asegúrate de que tu base de datos de pruebas esté configurada correctamente y que todas las migraciones estén aplicadas antes de ejecutar las pruebas.
- Si utilizas SQLite en memoria para pruebas, tu base de datos se reiniciará automáticamente entre pruebas.


### Despliegue en la Nube

Instrucciones paso a paso para desplegar en la plataforma de nube elegida (por ejemplo, AWS, Google Cloud, Heroku, etc.)

## Decisiones de Diseño

### Arquitectura

La API está estructurada utilizando el patrón MVC (Modelo-Vista-Controlador) de Laravel, permitiendo una separación clara de la lógica de negocio, las interacciones de usuario y la gestión de datos.

### Tecnologías Utilizadas

-   Backend: Laravel - Elegido por su facilidad de uso y su potente funcionalidad de ORM.
-   Base de Datos: MySQL - Elegido por su robustez y su amplia adopción en aplicaciones web.

### Patrones de Diseño

Descripción de los patrones de diseño utilizados y cómo apoyan los requisitos del negocio.

### Seguridad

-   Protección CSRF: Implementada en las solicitudes de API para prevenir ataques CSRF.
-   Escapado de salida: Todas las salidas de datos son escapadas para prevenir ataques XSS.
-   Validación de datos: Se valida la entrada del usuario para prevenir la inyección SQL y otros ataques.

### Escalabilidad

-   Uso de caché: Implementación de caché para respuestas y consultas para mejorar el rendimiento.
    Estrategias implementadas para asegurar la escalabilidad del proyecto.

-   Desacoplamiento: La arquitectura desacoplada permite escalar el frontend y el backend de manera independiente.

<!-- ## Contribución

Instrucciones para contribuir al proyecto, si es aplicable. -->

<!-- ## Licencia

Este proyecto está licenciado bajo la MIT License. -->
````
