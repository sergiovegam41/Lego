# LegoPHP Framework

> Construye aplicaciones web como si ensamblaras piezas de Lego.
> Componentes autocontenidos en PHP, renderizados desde el backend, con soporte modular para JS y CSS.

---

## 🔧 Características clave

* ✨ **Componentes autocontenidos:** Cada componente incluye su HTML (render), JS y CSS
* 🛠️ **Renderizado declarativo en PHP puro** (inspirado en Flutter y React)
* 🛦 **Módulos reutilizables** y composables (soporte para recursividad y props)
* 🦬 **Sistema de rutas automático**
* 🚜 **CLI para scaffolding, migraciones y mapeo**
* ⛽ **Entorno completo con Docker**: PostgreSQL, MongoDB, Redis, PgAdmin
* 🔒 **JWT para autenticación**, Carbon para fechas, validaciones integradas

---

## ✨ Filosofía

* ❌ **Sin plantillas** (Blade, Twig...): los componentes son clases PHP.
* ✅ **Sin dependencias externas**: toda la lógica vive en PHP puro.
* 🧳 **Backend-driven UI**: el HTML completo se construye del lado servidor.
* ⚖️ **Encapsulamiento real**: JS y CSS por componente, sin fugas.

---

## 📚 Ejemplo rápido

```php
class ButtonComponent extends CoreComponent {
    protected $CSS_PATHS = ['/assets/css/button.css'];
    protected $JS_PATHS = ['/assets/js/button.js'];

    protected function component(): string {
        $label = $this->config['label'] ?? 'Click';
        return "<button class='btn'>{$label}</button>";
    }
}

$button = new ButtonComponent(['label' => 'Guardar']);
echo $button->render();
```

---

## 🚀 Instalación

```bash
git clone https://github.com/tuusuario/legophp.git
cd legophp
cp .env.example .env
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php lego migrate
docker-compose exec app php lego map:routes
```

---

## 🗂️ Estructura del proyecto

```
lego/
├── App/             # Lógica de la aplicación
├── Core/            # Núcleo del framework (render, components, servicios)
├── Views/           # Componentes visuales
│   └── Home/
│       └── Components/
│           └── ButtonComponent/
│               ├── ButtonComponent.php
│               ├── style.css
│               └── script.js
├── Routes/          # Rutas definidas por la aplicación
├── database/        # SQL, migraciones
├── public/          # Entrada web y assets compilados
├── assets/          # JS/CSS global
└── vendor/          # Composer
```

---

## 🔢 CLI Disponible

### 💡 Crear componente

```bash
php lego make:component ButtonComponent
```

### 📄 Migraciones

```bash
php lego migrate
```

### 📘 Mapeo de rutas

```bash
php lego map:routes
```

---

## 💪 Servicios incluidos

* Aplicación PHP: [http://localhost:8080](http://localhost:8080)
* PgAdmin: [http://localhost:8081](http://localhost:8081)
* PostgreSQL: localhost:5432
* MongoDB: localhost:27017
* Redis: localhost:6379

---

## ⚖️ Licencia

MIT © Tu Nombre o Compañía

---

## 🚀 Roadmap

* [ ] AssetRegistry global para evitar duplicaciones de scripts/estilos
* [ ] Slots y props para personalizar componentes
* [ ] Hydration progresiva (frontend reactivo opcional)
* [ ] Generador de documentación estilo Storybook
* [ ] CLI para crear rutas, servicios, migraciones, seeds

---

## 🤝 Contribuciones

1. Haz fork del repo
2. Crea una rama `feature/LoQueSea`
3. Haz commit y push
4. Abre un PR

Construyamos una nueva forma de hacer backend con componentes.
