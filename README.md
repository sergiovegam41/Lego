# Freamework Lego

Lego es un freamework de php basado en componentes visuales 

# requisitos para el proyecto

- tener instlado git
- tener instalado docker 


# pasos para la instalacion

- Clonar el repositorio
- configurar .env en base a .env.example 
- dockcer compose up -d --build
- docker-compose exec app composer install
- docker-compose exec app composer dump-autoload

