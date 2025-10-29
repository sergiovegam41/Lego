# Modular Blocks Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         LEGO FRAMEWORK APPLICATION                           │
└─────────────────────────────────────────────────────────────────────────────┘

                              ┌──────────────────────┐
                              │  HTML Components     │
                              │  (Existing LEGO)     │
                              │                      │
                              │ • TableComponent     │
                              │ • FormFields         │
                              │ • Buttons            │
                              └──────────────────────┘
                                        ▲
                                        │
                ┌───────────────────────┼───────────────────────┐
                │                       │                       │
                ▼                       ▼                       ▼
        ┌───────────────┐      ┌──────────────────┐      ┌──────────────┐
        │ TableManager  │      │  FormBuilder     │      │  ApiClient   │
        │   (298 lines) │      │  (389 lines)     │      │ (133 lines)  │
        │               │      │                  │      │              │
        │ • setData()   │      │ • getData()      │      │ • list()     │
        │ • setColumns()│      │ • setData()      │      │ • create()   │
        │ • updateCount()       │ • setErrors()    │      │ • update()   │
        │ • getSelected()       │ • clearErrors()  │      │ • delete()   │
        │ • exportCSV() │      │ • markValid()    │      │ • call()     │
        │ • on/emit()   │      │ • on/emit()      │      │ • timeout    │
        └───────────────┘      └──────────────────┘      └──────────────┘
                │                       │                       │
                └───────────────────────┼───────────────────────┘
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │    StateManager               │
                        │    (108 lines)                │
                        │                               │
                        │ Pub/Sub Event Bus             │
                        │                               │
                        │ • setState()                  │
                        │ • getState()                  │
                        │ • on() / off() / once()       │
                        │ • emit()                      │
                        └───────────────────────────────┘
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │  ValidationEngine             │
                        │  (181 lines)                  │
                        │                               │
                        │ • validate()                  │
                        │ • hasErrors()                 │
                        │ • addCustom()                 │
                        │ • Predefined patterns         │
                        └───────────────────────────────┘
                                        │
                                        ▼
                        ┌───────────────────────────────┐
                        │    Backend API                │
                        │    (REST Endpoints)           │
                        │                               │
                        │ • /api/products               │
                        │ • /api/clients                │
                        │ • /api/invoices               │
                        │ • ... any entity              │
                        └───────────────────────────────┘
```

---

## Data Flow: Create Operation

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER INTERACTION                             │
│              (Click "Create" button)                            │
└─────────────────────────────────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  window.createProduct()               │
        │  (JavaScript function)                │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  FormBuilder.getData()                │
        │  Extract form values                  │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  ValidationEngine.validate(data)      │
        │  Check against schema                 │
        └───────────────────────────────────────┘
                            │
                ┌───────────┴───────────┐
                │                       │
          Errors?                      No Errors
                │                       │
                ▼                       ▼
        ┌──────────────────┐    ┌──────────────────┐
        │ FormBuilder.     │    │ ApiClient.       │
        │ setErrors()      │    │ create(data)     │
        │                  │    │                  │
        │ Display errors   │    │ POST /api/...    │
        └──────────────────┘    └──────────────────┘
                                         │
                                         ▼
                                ┌──────────────────┐
                                │ Backend Response │
                                └──────────────────┘
                                         │
                                ┌────────┴────────┐
                                │                 │
                           Success           Failure
                                │                 │
                                ▼                 ▼
                        ┌──────────────┐  ┌──────────────┐
                        │ StateManager │  │ AlertService │
                        │ .emit()      │  │ .error()     │
                        │              │  │              │
                        │ Broadcast    │  │ Show error   │
                        │ 'created'    │  │              │
                        └──────────────┘  └──────────────┘
                                │
                                ▼
                        ┌──────────────────┐
                        │ ApiClient.       │
                        │ list()           │
                        │                  │
                        │ Reload list      │
                        └──────────────────┘
                                │
                                ▼
                        ┌──────────────────┐
                        │ TableManager.    │
                        │ setData()        │
                        │ updateRowCount() │
                        │                  │
                        │ Update UI        │
                        └──────────────────┘
                                │
                                ▼
                        ┌──────────────────┐
                        │ AlertService     │
                        │ .success()       │
                        │                  │
                        │ Show success     │
                        └──────────────────┘
```

---

## Block Composition Patterns

### Pattern 1: Simple Table
```
Component HTML
    ↓
TableManager ──→ AG Grid
    ↓
ApiClient ──→ Backend
```

### Pattern 2: CRUD with Validation
```
Component HTML
    ├── Table
    └── Form
        ↓
TableManager ──→ AG Grid
FormBuilder ──→ Form Fields
ValidationEngine ──→ Rules
    ↓
ApiClient ──→ Backend
    ↓
StateManager ──→ Events
```

### Pattern 3: Advanced with State
```
Component HTML
    ├── Table
    ├── Form
    └── Stats Widget
        ↓
TableManager ──────┐
FormBuilder ───────┤──→ ValidationEngine ──→ ApiClient
StateManager ◄─────┘                             ↓
    ↓                                        Backend
Global State Bus
(all components listen)
```

---

## Independence of Blocks

Each block can be used independently or composed:

```
┌────────────────────────────────────────────────────────┐
│                    MODULAR BLOCKS                      │
├────────────────────────────────────────────────────────┤
│                                                        │
│  ✓ ApiClient can work without TableManager            │
│  ✓ FormBuilder can work without ValidationEngine      │
│  ✓ ValidationEngine can work standalone               │
│  ✓ TableManager can work without FormBuilder          │
│  ✓ StateManager is optional (direct API calls OK)     │
│                                                        │
│  COMPOSITION:                                          │
│  Use all 5 blocks together OR just what you need     │
│                                                        │
└────────────────────────────────────────────────────────┘
```

---

## Agnóstic Design

The same blocks work for ANY entity:

```
Products CRUD:
    const api = new ApiClient('/api/products');
    const validator = new ValidationEngine({ /* products schema */ });

Clients CRUD:
    const api = new ApiClient('/api/clients');
    const validator = new ValidationEngine({ /* clients schema */ });

Invoices CRUD:
    const api = new ApiClient('/api/invoices');
    const validator = new ValidationEngine({ /* invoices schema */ });

Suppliers CRUD:
    const api = new ApiClient('/api/suppliers');
    const validator = new ValidationEngine({ /* suppliers schema */ });

→ ALL use EXACT same block code
→ ZERO hardcoding of entity types
→ Change ApiClient → fixes all 4 CRUDs
```

---

## Event Flow with StateManager

```
┌───────────────────────────────────────┐
│  Component A: Creates Product         │
│  api.create(data) ──success──→        │
│                                       │
│  state.emit('product:created', data)  │
└───────────────────────────────────────┘
                │
                │ Event published
                ▼
┌───────────────────────────────────────┐
│ StateManager Event Bus                │
│                                       │
│ on('product:created', callback)       │
│ once('product:updated', callback)     │
└───────────────────────────────────────┘
                │
         ┌──────┼──────┬──────┐
         │      │      │      │
         ▼      ▼      ▼      ▼
    ┌────────┬─────┬──────┬──────────┐
    │ Stats  │List │Cart  │ Analytics│
    │Widget  │Page │Count │ Tracker  │
    │        │     │      │          │
    │Listens │Listen Listen Listen   │
    │to      │to   to     to         │
    │event   │event event  event     │
    └────────┴─────┴──────┴──────────┘
         │      │      │      │
         └──────┴──────┴──────┘
              │
              ▼
    ┌──────────────────┐
    │ All components   │
    │ update instantly │
    │ (no polling)     │
    └──────────────────┘
```

---

## Migration Path: Old vs New

### OLD APPROACH (Template-based)
```
ProductsController
    ↓
ProductsComponent (PHP)
    ↓
products.js (300+ hardcoded lines)
    ├─ const API_BASE = '/api/products'
    ├─ function loadProducts() { fetch... }
    ├─ function createProduct() { lots of code... }
    ├─ function editProduct() { lots of code... }
    ├─ function deleteProduct() { lots of code... }
    ├─ Accesses window.legoTable_products_api directly
    ├─ Validation mixed with UI logic
    └─ All hardcoded to products entity

= 300+ lines
= Duplicated in every CRUD
= Hard to maintain
= No reusability
```

### NEW APPROACH (Modular blocks)
```
ProductsController (unchanged)
    ↓
ProductsComponent (unchanged)
    ↓
products.js (50-80 clean lines)
    ├─ const api = new ApiClient('/api/products')
    ├─ const tableManager = new TableManager('table-id')
    ├─ const validator = new ValidationEngine({ schema })
    │
    ├─ tableManager.onReady(async () => { loadData() })
    │
    ├─ window.create = async (data) => {
    │     const errors = validator.validate(data)
    │     if (!errors) await api.create(data)
    │  }
    │
    ├─ window.edit = async (id) => { ... }
    ├─ window.delete = async (id) => { ... }
    └─ Uses reusable blocks

= 50-80 lines per CRUD
= Same code pattern for every CRUD
= Easy to maintain (fix in block = fixes all)
= Highly reusable
```

---

## Performance Characteristics

```
Block              Initialization    Memory        API Calls
────────────────────────────────────────────────────────────
ApiClient          ~1ms              Minimal       On-demand
StateManager       ~0.5ms            Minimal       0 (pub/sub)
ValidationEngine   ~0.1ms            Minimal       0 (local)
TableManager       ~500ms            Medium        0 (uses ApiClient)
FormBuilder        ~50ms             Minimal       0 (reads DOM)

TOTAL:            ~500-550ms for full stack
                  (mostly AG Grid initialization)
```

---

## Error Handling Architecture

```
┌──────────────────┐
│  User Action     │
└──────────────────┘
         │
         ▼
┌──────────────────┐      Validation Errors
│ FormBuilder      │─────→ setFieldError() ──→ Update UI
│ ValidationEngine │
└──────────────────┘
         │
         ▼  Valid data
┌──────────────────┐      Network Errors
│  ApiClient       │─────→ AlertService.error() ──→ Show Modal
│  .create()       │
└──────────────────┘
         │
         ▼  Server Error
┌──────────────────┐
│ Response handler │─────→ AlertService.error() ──→ Show Message
└──────────────────┘
         │
         ▼  Success
┌──────────────────┐
│ StateManager     │─────→ emit('created') ──→ Update Components
│ .emit()          │
└──────────────────┘
         │
         ▼
┌──────────────────┐
│ AlertService     │─────→ AlertService.success() ──→ Toast
│ .success()       │
└──────────────────┘
```

---

## Scalability: From 1 CRUD to 50+

```
Time to Create 1st CRUD:   2-3 hours (building infrastructure)
                           + Learning how blocks work

Time to Create 2nd CRUD:   30 minutes
                           (reuse all blocks)

Time to Create 3rd-50th:   20-30 minutes each
                           (pattern is clear, blocks are proven)

TOTAL TIME FOR 50 CRUDs:
  Old approach:   ~100-150 hours
  New approach:   3 hours + (47 × 0.5 hours) = ~26.5 hours

SAVINGS:         ~75-80% time reduction
```

---

## Testing Strategy

```
Each block tests independently:

┌─────────────────────┐
│ ApiClient Tests     │  ✓ Mock API responses
│ • list()            │  ✓ Error handling
│ • create()          │  ✓ Timeout handling
│ • update()          │  ✓ Status codes
│ • delete()          │
└─────────────────────┘

┌─────────────────────┐
│ ValidationEngine    │  ✓ Schema validation
│ Tests               │  ✓ Custom validators
│ • validate()        │  ✓ Error messages
│ • patterns          │  ✓ Edge cases
└─────────────────────┘

┌─────────────────────┐
│ StateManager Tests  │  ✓ Event emission
│ • setState()        │  ✓ Event listening
│ • emit()            │  ✓ Multiple listeners
│ • on/off            │
└─────────────────────┘

┌─────────────────────┐
│ TableManager Tests  │  ✓ Data updates
│ • setData()         │  ✓ Column changes
│ • getSelected()     │  ✓ Export
│ • events            │
└─────────────────────┘

┌─────────────────────┐
│ FormBuilder Tests   │  ✓ Value management
│ • getData()         │  ✓ Error display
│ • setData()         │  ✓ Field focus
│ • validation        │
└─────────────────────┘

Integration Tests:
  ✓ Complete CRUD flow
  ✓ Error scenarios
  ✓ State synchronization
  ✓ Event propagation
```

---

## Summary

The modular blocks architecture transforms LEGO Framework from:
```
❌ Template-based (rigid, duplicative, hard to maintain)
→ ✅ Block-based (flexible, reusable, easy to maintain)
```

Key insight: **Composition Over Templates**
- Blocks are like LEGO bricks
- Mix and match as needed
- No template limitations
- Maximum flexibility
- Minimum code duplication
