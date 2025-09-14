# 📋 Guía de Implementación

Guía para usar contratos y mantener calidad en el desarrollo.

## 🎯 ¿Qué son los Contratos?

**Contratos = Reglas estrictas** que garantizan:
- Variables CSS (nunca píxeles hardcoded)
- Estructura consistente de componentes
- Diseño responsive uniforme
- Performance optimizado

## 🗂️ Contratos por Área

### 🎨 **CSS/Estilos** → `css-styles-contract.md`
**Cuándo usar:** Escribiendo CSS, colores, tamaños, espaciados
**Regla clave:** Variables CSS siempre, valores hardcoded nunca

### 🧩 **Componentes** → `components-structure-contract.md`
**Cuándo usar:** Creando/modificando componentes
**Regla clave:** Estructura `Views/[Feature]/Components/[Name]/`

### 📱 **Responsive** → `responsive-design-contract.md`
**Cuándo usar:** Interfaces multi-dispositivo
**Regla clave:** Mobile-first, medidas en rem

### ⚡ **Performance** → `performance-optimization-contract.md`
**Cuándo usar:** Animaciones, JavaScript pesado
**Regla clave:** Variables para transiciones

### 🏗️ **Creación Práctica** → `components-creation-guide.md`
**Cuándo usar:** Guía step-by-step para crear componentes
**Regla clave:** Flujo estructurado de desarrollo

## 📝 Cómo Crear Nuevos Contratos

### Cuándo crear:
1. **Área no cubierta:** Identificas patrones sin reglas claras
2. **Reglas complejas:** Un contrato actual se vuelve muy largo
3. **Problemas recurrentes:** Errores que se repiten sin reglas específicas

### Estructura obligatoria:
```markdown
# 🎯 Contrato de [Área]

## ✅ QUÉ HACER
[Ejemplos correctos]

## ❌ QUÉ NO HACER
[Ejemplos incorrectos]

## 📋 CHECKLIST
[Validación antes del commit]
```

### Proceso:
1. **Identificar problema** específico y recurrente
2. **Analizar código** existente para encontrar patrones
3. **Definir reglas** con ejemplos claros
4. **Crear checklist** práctico
5. **Actualizar este archivo** con referencia al nuevo contrato
6. **Actualizar `index.md`** con resumen del contrato

## 🔄 Mantenimiento

**⚠️ IMPORTANTE:** Cada vez que se crea o modifica un contrato:
1. **Actualizar `index.md`** con nuevo resumen
2. **Actualizar esta guía** con nueva referencia
3. **Documentar cambio** en bitácora correspondiente

---

**Recordatorio:** Los contratos existen para hacer el desarrollo más rápido y consistente, no más lento.