# 📑 Índice de Contratos

Resumen rápido de todos los contratos disponibles para navegación eficiente.

## 🎨 CSS/Estilos
**Archivo:** `css-styles-contract.md`
**Propósito:** Reglas para colores, tamaños y espaciados
**Regla principal:** Variables CSS obligatorias, cero hardcoding
**Métricas:** 100% variables CSS, medidas en rem únicamente

## 🧩 Componentes
**Archivo:** `components-structure-contract.md`
**Propósito:** Organización y estructura de componentes
**Regla principal:** Estructura `components/[Core|App]/[ComponentName]/`
**Archivos:** `ComponentNameComponent.php`, `component-name.css`, `component-name.js`
**Filosofía:** Named arguments, tipado fuerte, composición declarativa estilo Flutter

## 📱 Responsive Design
**Archivo:** `responsive-design-contract.md`
**Propósito:** Diseño adaptativo multi-dispositivo
**Regla principal:** Mobile-first approach
**Breakpoints:** Estándar definido, medidas en rem

## ⚡ Performance
**Archivo:** `performance-optimization-contract.md`
**Propósito:** Optimización de rendimiento
**Regla principal:** Variables para transiciones
**Focus:** Hardware acceleration, métricas medibles

## 🏗️ Creación de Componentes
**Archivo:** `components-creation-guide.md`
**Propósito:** Guía práctica paso a paso para crear componentes Lego
**Regla principal:** Named arguments con tipos específicos
**Filosofía:** Componentes declarativos tipo-safe estilo Flutter
**Incluye:** Plantillas, ejemplos, validación tipada con Collections

---

## 📂 Estructura Actual

```
AI/
├── README.md                        # Punto de entrada
├── implementation-guide.md          # Guía principal
├── index.md                         # Este índice
├── Contracts/                       # Contratos específicos
│   ├── css-styles-contract.md
│   ├── components-structure-contract.md
│   ├── responsive-design-contract.md
│   ├── performance-optimization-contract.md
│   └── components-creation-guide.md
└── Bitacora/                        # Historial
    ├── 2025-08-28.md
    ├── 2025-09-04.md
    └── 2025-09-07.md
```