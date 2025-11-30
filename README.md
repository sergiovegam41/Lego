# 🧱 Lego Framework

> **"Todo encaja perfectamente"**
> 
> Construye aplicaciones web modulares como si ensamblaras bloques LEGO. Cada componente es una pieza autocontenida que se conecta naturalmente con otras para crear experiencias completas.

---

## 🎯 La Filosofía LEGO

### 🧩 **Cada pieza tiene su lugar**
Cada componente es **autocontenido** - incluye su lógica PHP, estilos CSS y JavaScript en una sola carpeta. Como un bloque LEGO real, funciona independientemente pero se conecta perfectamente con otros.

### 🔄 **Reutilización infinita**  
Crea una vez, usa en cualquier lugar. Los componentes se comportan como piezas LEGO - puedes combinarlos de formas infinitas para crear desde interfaces simples hasta aplicaciones complejas.

### ⚡ **Simplicidad poderosa**
Sin plantillas complicadas, sin configuraciones infinitas. Solo PHP puro con una arquitectura que hace que todo "simplemente funcione".

### 🎨 **Consistencia visual natural**
Sistema unificado de variables CSS que garantiza que todos tus componentes mantengan el mismo lenguaje visual, como las piezas LEGO mantienen su estilo característico.

---

## 🚀 En palabras simples

**LEGO Framework es:**

### 🎯 **Como Flutter** en el modo de uso
- ✅ **Componentes = Clases PHP** (como widgets en Dart)
- ✅ **Renderizado declarativo** - describes qué quieres, no cómo hacerlo
- ✅ **Composición natural** - combinas componentes para crear interfaces

### 📐 **Como Angular** en organización y escalabilidad  
- ✅ **Estructura modular** perfectamente organizada
- ✅ **Convenciones claras** que todos pueden seguir
- ✅ **Escalable** desde proyectos pequeños hasta aplicaciones enterprise

### 🧱 **Pero en PHP puro**
```php
// ✅ Así de simple es crear un componente
class DashboardCard extends CoreComponent {
    protected $CSS_PATHS = ["./card.css"];

    public function __construct(
        public string $title,
        public string $value
    ) {}

    protected function component(): string {
        return <<<HTML
        <div class="dashboard-card">
            <h3>{$this->title}</h3>
            <p>{$this->value}</p>
        </div>
        HTML;
    }
}

// Úsalo donde quieras - como un Widget de Flutter
$card = new DashboardCard(title: 'Usuarios', value: '1,250');
echo $card->render();
```

**🎯 Resultado:** La simplicidad conceptual de Flutter + La organización de Angular + La familiaridad de PHP

---

## 🔄 Comparación familiar

### 🎯 **Si vienes de Flutter:**
```dart
// Flutter Widget
class UserCard extends StatelessWidget {
  final String name;
  UserCard({required this.name});
  
  @override
  Widget build(BuildContext context) {
    return Card(child: Text(name));
  }
}
```

```php
// LEGO Component - ¡Misma lógica!
class UserCard extends CoreComponent {
    public function __construct(public string $name) {}
    
    protected function component(): string {
        return "<div class='card'>{$this->name}</div>";
    }
}
```

### 📐 **Si vienes de Angular:**
```
angular-app/
├── src/app/
│   ├── components/
│   │   └── user-card/
│   │       ├── user-card.component.ts
│   │       ├── user-card.component.html  
│   │       └── user-card.component.css
```

```
lego/components/App/
└── UserCard/
    ├── UserCardComponent.php  ← Lógica + HTML
    ├── user-card.css         ← Estilos
    └── user-card.js          ← Comportamiento
```

**¡Misma organización, misma escalabilidad!**

### 🎯 **Ventajas vs otros frameworks:**

| Otros Frameworks | 🧱 Lego Framework |
|-----------------|-------------------|
| Templates separados | ✅ Todo en una clase PHP |
| CSS global caótico | ✅ Estilos por componente |
| JS esparcido | ✅ Lógica encapsulada |
| Configuración compleja | ✅ Convenciones simples |
| "Magic" oculta | ✅ Transparencia total |

---

## 📚 Ejemplo rápido

```php
class ButtonComponent extends CoreComponent {
    protected $CSS_PATHS = ["./button.css"];
    protected $JS_PATHS = ["./button.js"];

    public function __construct(
        public string $label = 'Click',
        public string $variant = 'primary'
    ) {}

    protected function component(): string {
        return "<button class='btn btn--{$this->variant}'>{$this->label}</button>";
    }
}

$button = new ButtonComponent(label: 'Guardar', variant: 'primary');
echo $button->render();
```

---

## 🏗️ Empezar a construir

### **Instalación en 30 segundos:**

```bash
git clone https://github.com/tuusuario/legophp.git
cd lego-framework
cp .env.example .env
docker-compose up -d
```

**¡Ya tienes tu entorno corriendo!** 🎉
- 🌐 App: [http://localhost:8080](http://localhost:8080)
- 🛢️ PgAdmin: [http://localhost:8081](http://localhost:8081)

---

## 🗂️ Arquitectura LEGO

Como los sets de LEGO reales, todo está **perfectamente organizado**:

```
lego/
├── components/
│   ├── Core/        🧱 Piezas base del framework
│   │   ├── Login/
│   │   ├── Home/
│   │   └── Automation/
│   └── App/         🎨 Tus componentes específicos
│       ├── TestButton/
│       └── [TusComponentes]/
├── Routes/          🛤️ Conexiones entre componentes
│   ├── Web.php      → Rutas web principales
│   ├── Api.php      → Rutas API REST
│   └── Views.php    → Auto-discovery de componentes
├── Core/            ⚙️ Motor del framework
│   ├── Commands/    → CLI (make:component, migrate, etc)
│   ├── Components/  → CoreComponent base
│   └── Services/    → Servicios del framework
├── App/             💼 Lógica de negocio
│   ├── Controllers/ → Controladores
│   └── Models/      → Modelos Eloquent
├── assets/          🎨 Assets globales
│   ├── css/core/    → Variables CSS y estilos base
│   ├── js/          → JavaScript global
│   └── images/      → Imágenes
└── database/        🗄️ Migraciones
```

### **Cada componente = 1 carpeta completa:**
```
components/App/MiComponente/
├── MiComponenteComponent.php  ← Lógica y HTML
├── mi-componente.css          ← Estilos únicos
└── mi-componente.js           ← Comportamiento
```

### **Sistema de rutas relativas:**
Los componentes usan rutas relativas para sus assets:
```php
protected $CSS_PATHS = ["./mi-componente.css"];  // ✅ Se resuelve automáticamente
protected $JS_PATHS_WITH_ARG = [
    new ScriptCoreDTO("./mi-componente.js", [])
];
```

### 🔗 **Sistema de Enlaces Simbólicos (Symlinks)**

**¿Por qué existen `public/components/` y `public/assets/`?**

El framework usa **enlaces simbólicos** para servir archivos estáticos manteniendo la organización del código:

```
Estructura real:
├── components/          ← Código fuente de componentes (PHP, CSS, JS)
├── assets/              ← Assets globales compartidos
└── public/              ← DocumentRoot de Nginx/Apache
    ├── index.php        ← Entry point
    ├── components ->    ← SYMLINK → ../components/
    └── assets ->        ← SYMLINK → ../assets/
```

**¿Cómo funciona?**
1. **Nginx/Apache** sirve archivos desde `public/` (seguridad)
2. **Los symlinks** permiten acceso HTTP a CSS/JS de componentes
3. **Sin duplicación**: Los symlinks ocupan ~0 bytes

**Flujo de acceso:**
```
Browser: http://localhost/components/Core/Home/home.css
           ↓
Nginx:   /public/components/Core/Home/home.css
           ↓
Symlink: ../components/Core/Home/home.css
           ↓
Real:    /components/Core/Home/home.css ✅
```

**Ventajas:**
- ✅ Código organizado fuera del DocumentRoot público
- ✅ Assets accesibles vía HTTP sin duplicación
- ✅ Seguridad: solo `public/` expuesto al web server
- ✅ Performance: sin copias, referencias directas

**Los symlinks ya están incluidos en el repositorio.** Si por alguna razón necesitas recrearlos:

```bash
cd public/
ln -s ../components components
ln -s ../assets assets
```

---

## 🛤️ Sistema de Routing en 3 Capas

Lego Framework implementa un sistema de routing innovador que separa claramente las responsabilidades en **3 capas independientes**.

### 📐 Arquitectura del Router

```
Usuario → Nginx → public/index.php → Core/Router.php
                                            ↓
                        ┌───────────────────┴───────────────────┐
                        │   Analiza primer segmento de la URI   │
                        └───────────────────┬───────────────────┘
                                            ↓
            ┌──────────────┬────────────────┴────────────┬──────────────┐
            │              │                             │              │
         /api/*      /component/*                     otros            /
            │              │                             │              │
            ↓              ↓                             ↓              ↓
         Api.php      Component.php                 Web.php        Web.php
         (JSON)     (HTML parcial + Assets)       (HTML completo)
```

---

### 🔴 **Capa 1: API Backend** (`/api/*`)

**Propósito:** Endpoints REST para lógica de negocio

**Características:**
- ✅ Retorna JSON
- ✅ Autenticación modular (Admin, Api, extensible)
- ✅ Validación de requests
- ✅ Rutas dinámicas auto-mapeadas

**Ejemplos:**
```
POST /api/auth/admin/login
POST /api/auth/api/refresh_token
GET  /api/users/list
POST /api/products/create
```

**Archivo:** `Routes/Api.php`

---

### 🟢 **Capa 2: Component Routes** (`/component/*`)

**Propósito:** Componentes SPA + Assets estáticos

**Características:**
- ✅ Retorna HTML parcial (sin DOCTYPE/HEAD/BODY) para componentes
- ✅ Sirve assets estáticos (.css, .js) de componentes
- ✅ Auto-discovery con decorador `#[ApiComponent]`
- ✅ Se insertan en `#home-page` del layout SPA
- ✅ **Filosofía "Sin estado en frontend"**
- ✅ Consistencia total: `/component/` para todo lo relacionado a componentes

**¿Por qué sin estado?**
En lugar de mantener estado complejo en el frontend (Redux, Vuex, etc.),
los componentes siempre se refrescan desde el servidor. Esto elimina:
- ❌ Desfases de información
- ❌ Sincronización compleja
- ❌ Bugs de estado inconsistente

Y garantiza:
- ✅ Información siempre actualizada
- ✅ Backend como única fuente de verdad
- ✅ Desarrollo más simple

**Ejemplo de uso:**

1. **Crear componente con decorador:**
```php
#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent {
    protected function component(): string {
        return '<div>Dashboard actualizado</div>';
    }
}
```

2. **JavaScript lo refresca:**
```javascript
// Window Manager hace fetch automáticamente
fetch('/component/inicio')
    .then(html => {
        document.getElementById('home-page').innerHTML = html;
    });
```

3. **Assets se cargan automáticamente:**
```html
<link rel="stylesheet" href="/component/inicio/HomeComponent.css">
<script src="/component/inicio/HomeComponent.js"></script>
```

4. **Usuario ve información actualizada** sin recargar la página

**Ejemplos de rutas:**
```
GET /component/inicio              → HomeComponent (HTML)
GET /component/automation          → AutomationComponent (HTML)
GET /component/inicio/HomeComponent.css  → CSS del componente
GET /component/inicio/HomeComponent.js   → JS del componente
```

**Archivo:** `Routes/Component.php`

---

### 🔵 **Capa 3: Web Routes** (`/*`)

**Propósito:** Páginas completas (puntos de entrada)

**Características:**
- ✅ Retorna HTML completo (DOCTYPE, HEAD, BODY)
- ✅ MainComponent (layout SPA), LoginComponent
- ✅ Registro manual de rutas
- ✅ Entry points de la aplicación

**Ejemplos:**
```
GET /admin  → MainComponent (Layout con sidebar/header)
GET /login  → LoginComponent (Página de autenticación)
GET /       → Redirect a /admin
```

**Archivo:** `Routes/Web.php`

---

### 🎯 Flujo Completo en Acción

**Escenario:** Usuario navega en el dashboard

```
1. Usuario accede → /admin
   └→ Web.php → MainComponent
   └→ Renderiza HTML completo con sidebar, header, #home-page

2. Usuario hace click en "Inicio" del menú
   └→ JavaScript fetch → /component/inicio
   └→ Core/Router.php → Component.php → HomeComponent
   └→ Retorna HTML parcial

3. JavaScript inserta contenido en #home-page
   └→ Usuario ve dashboard actualizado
   └→ Sin recargar página, sin mantener estado

4. Assets del componente se cargan automáticamente
   └→ /component/inicio/HomeComponent.css
   └→ /component/inicio/HomeComponent.js
   └→ Servidos con caché eficiente desde PHP

5. Usuario hace click en "Automatización"
   └→ JavaScript fetch → /component/automation
   └→ Component.php → AutomationComponent
   └→ Información fresca del servidor
   └→ Siempre actualizada, sin desfases
```

---

### 💡 Ventajas del Sistema

**1. Separación clara de responsabilidades**
- Cada capa con propósito específico
- Código organizado y mantenible

**2. Desarrollo simple**
- Sin estado complejo en frontend
- Sin sincronización de datos
- Backend como única fuente de verdad

**3. Información siempre actualizada**
- Cada refresco trae datos frescos
- Elimina bugs de estado desincronizado

**4. Escalabilidad**
- Auto-discovery de componentes
- Fácil agregar nuevas funcionalidades
- Sistema modular extensible

---

## ⚡ Herramientas de construcción

### 🏗️ **Crear nuevas piezas**
```bash
php lego make:component UserCard
# Crea toda la estructura automáticamente
```

### 🔧 **Gestionar tu aplicación**
```bash
php lego migrate      # Configurar base de datos
php lego map:routes   # Mapear todas las conexiones
```

**👀 Guía completa:** [`doc/flows/crear-componente.md`](doc/flows/crear-componente.md)

---

## 📖 Documentación

**[Ver documentación completa →](doc/README.md)**

| Tema | Descripción |
|------|-------------|
| [Arquitectura](doc/01-arquitectura.md) | Flujo de ejecución, capas, routing |
| [Componentes](doc/02-componentes.md) | CoreComponent, CSS/JS, composición |
| [Screens](doc/03-screens.md) | ScreenInterface, identidad de ventanas |
| [Menú](doc/04-menu.md) | MenuStructure, items dinámicos |
| [Módulos](doc/05-modulos.md) | WindowManager, navegación |
| [API](doc/06-api.md) | Rutas, controladores |
| [Modelos](doc/07-modelos.md) | Eloquent, atributos |
| [Servicios JS](doc/08-servicios-js.md) | AlertService, ThemeManager |
| [Tablas](doc/09-tablas.md) | TableComponent, filtros |
| [Formularios](doc/10-formularios.md) | InputText, Select, FilePond |

### Guías Prácticas (Cómo hacer X)

| Flujo | Descripción |
|-------|-------------|
| [Crear componente](doc/flows/crear-componente.md) | Componente básico en 5 pasos |
| [Crear screen](doc/flows/crear-screen.md) | Pantalla con identidad + menú |
| [Crear CRUD](doc/flows/crear-crud.md) | Lista + Crear + Editar completo |
| [Crear botón](doc/flows/crear-boton.md) | Botones con acciones |
| [Crear migración](doc/flows/crear-migracion.md) | Tablas de base de datos |
| [Agregar menú](doc/flows/agregar-menu-item.md) | Items al menú lateral |
| [Agregar API](doc/flows/agregar-api-endpoint.md) | Endpoints REST |

---

## 🧱 ¿Qué incluye la caja LEGO?

Tu entorno viene con **todas las piezas esenciales**:

| Servicio | URL/Puerto | Uso |
|----------|------------|-----|
| 🌐 **Aplicación** | [localhost:8080](http://localhost:8080) | Tu framework corriendo |
| 🛢️ **PgAdmin** | [localhost:8081](http://localhost:8081) | Gestión de PostgreSQL |
| 🗄️ **PostgreSQL** | localhost:5432 | Base de datos principal |
| 📊 **MongoDB** | localhost:27017 | Datos no relacionales |
| ⚡ **Redis** | localhost:6379 | Cache y sesiones |

---

## 🎯 Próximas piezas especiales

- [ ] 🧩 **Slots y props** avanzados para componentes
- [ ] 📦 **Asset Registry** - sin duplicar CSS/JS
- [ ] ⚡ **Hydration opcional** - SPA cuando lo necesites  
- [ ] 📚 **Storybook integrado** - documenta tus piezas
- [ ] 🤖 **CLI generativo** - crea todo con comandos

---

## 🤝 Únete a la construcción

**¿Quieres ayudar a hacer LEGO Framework aún mejor?**

1. 🍴 Fork el repo
2. 🏗️ Crea tu feature: `git checkout -b feature/PiezaNueva`  
3. 🎯 Haz commit: `git commit -m 'Agregué pieza increíble'`
4. 🚀 Push: `git push origin feature/PiezaNueva`
5. 🎉 Abre un Pull Request

---

## 📜 Licencia

**MIT** - Usa las piezas LEGO como quieras, construye lo que sueñes.

---

> **"La creatividad es la moneda del futuro"** 🧱
> 
> Con LEGO Framework, cada componente es una inversión que se reutiliza infinitamente. **Construye una vez, úsalo siempre.**



### ⚠️ **Para Desarrolladores/IA**
Lee [`AI/README.md`](AI/README.md) - Contratos y reglas para desarrollo asistido.