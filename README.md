# Freamework Lego

Lego es un freamework de php basado en componentes 

# requisitos para el proyecto

- tener instlado git
- tener instalado docker 


# pasos para la instalacion

- Clonar el repositorio
- configurar .env basado en .env.example 
- correr `dockcer compose up -d --build`
- correr `docker-compose exec app composer install`
- correr `docker-compose exec app composer dump-autoload`

