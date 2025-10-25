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
    protected $CSS_PATHS = ["./card.css"]; // Ruta relativa al componente

    public function component(): string {
        return <<<HTML
        <div class="dashboard-card">
            <h3>{$this->config['title']}</h3>
            <p>{$this->config['value']}</p>
        </div>
        HTML;
    }
}

// Úsalo donde quieras - como un Widget de Flutter
$card = new DashboardCard(['title' => 'Usuarios', 'value' => '1,250']);
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
    public function component(): string {
        $name = $this->config['name'];
        return "<div class='card'>{$name}</div>";
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

**👀 Mira la guía completa:** [`docs/COMO_CREAR_COMPONENTES.md`](docs/COMO_CREAR_COMPONENTES.md)

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



### ⚠️ **OBLIGATORIO**
Si eres desarrollador/IA, lee [`AI/README.md`](AI/README.md) - Sistema de contratos para calidad y consistencia. 