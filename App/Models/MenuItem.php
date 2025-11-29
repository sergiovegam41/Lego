<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MenuItem Model
 *
 * FILOSOFÍA LEGO:
 * Modelo para gestión de menú jerárquico con niveles ilimitados.
 *
 * ESTRUCTURA:
 * - Relación padre-hijo con parent_id
 * - Niveles ilimitados de profundidad
 * - Ordenamiento con display_order
 * - index_label para cuando un item tiene hijos
 *
 * CAMPOS:
 * - id: Identificador único (string, ej: 'flores', 'flores-create')
 * - parent_id: ID del padre (NULL = raíz)
 * - label: Nombre del item (ej: 'Flores')
 * - index_label: Nombre cuando tiene hijos (ej: 'Ver')
 * - route: Ruta del componente (ej: '/component/flores')
 * - icon: Icono de Ionicons (ej: 'flower-outline')
 * - display_order: Orden de aparición
 * - level: Nivel en jerarquía (0 = raíz, 1 = hijo, etc.)
 * - is_visible: Si se muestra en el menú lateral por defecto
 * - is_dynamic: Si es una opción dinámica/fantasma (requiere contexto)
 *
 * TIPOS DE ITEMS:
 * 1. NORMAL (visible=true, dynamic=false): En menú + buscable
 * 2. OCULTO (visible=false, dynamic=false): Solo buscable
 * 3. DINÁMICO (visible=false, dynamic=true): Solo por activación con contexto
 */
class MenuItem extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'menu_items';

    /**
     * La clave primaria (no es auto-increment)
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'id',
        'parent_id',
        'label',
        'index_label',
        'route',
        'icon',
        'display_order',
        'level',
        'is_visible',
        'is_dynamic'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'display_order' => 'integer',
        'level' => 'integer',
        'is_visible' => 'boolean',
        'is_dynamic' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Valores por defecto para atributos
     */
    protected $attributes = [
        'display_order' => 0,
        'level' => 0,
        'is_visible' => true,
        'is_dynamic' => false,
        'icon' => 'ellipse-outline'
    ];

    /**
     * Relación: Hijos de este item
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id', 'id')
                    ->orderBy('display_order');
    }

    /**
     * Relación: Padre de este item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id', 'id');
    }

    /**
     * Scope: Solo items raíz (sin padre)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')
                     ->orderBy('display_order');
    }

    /**
     * Scope: Solo items visibles en el menú lateral
     * Excluye items con is_visible=false Y items dinámicos
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)
                     ->where(function($q) {
                         // Incluir items donde is_dynamic es false o no existe (compatibilidad)
                         $q->where('is_dynamic', false)
                           ->orWhereNull('is_dynamic');
                     });
    }

    /**
     * Scope: Items buscables (no dinámicos)
     * Los items dinámicos no aparecen en búsquedas porque requieren contexto
     */
    public function scopeSearchable($query)
    {
        return $query->where(function($q) {
            $q->where('is_dynamic', false)
              ->orWhereNull('is_dynamic');
        });
    }

    /**
     * Scope: Solo items dinámicos (requieren contexto para activarse)
     */
    public function scopeDynamic($query)
    {
        return $query->where('is_dynamic', true);
    }

    /**
     * Scope: Todos los items independientemente de visibilidad
     * Útil para búsquedas que incluyen items ocultos pero no dinámicos
     */
    public function scopeForSearch($query)
    {
        return $query->searchable(); // Excluye dinámicos, incluye todo lo demás
    }

    /**
     * Scope: Por nivel
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Obtener árbol completo del menú (con hijos anidados)
     */
    public static function getTree(): array
    {
        return self::root()
                   ->visible()
                   ->with(['children' => function($query) {
                       $query->visible()->with('children');
                   }])
                   ->get()
                   ->toArray();
    }

    /**
     * Obtener árbol completo recursivamente (todos los niveles)
     */
    public static function getFullTree(): array
    {
        $rootItems = self::root()->visible()->get();
        
        return $rootItems->map(function($item) {
            return self::buildTreeNode($item);
        })->toArray();
    }

    /**
     * Construir nodo del árbol con todos sus descendientes
     */
    private static function buildTreeNode(MenuItem $item): array
    {
        $node = $item->toArray();
        
        $children = $item->children()->visible()->get();
        if ($children->isNotEmpty()) {
            $node['children'] = $children->map(function($child) {
                return self::buildTreeNode($child);
            })->toArray();
        }
        
        return $node;
    }

    /**
     * Obtener siguiente orden disponible para un padre
     */
    public static function getNextOrder(?string $parentId = null): int
    {
        $maxOrder = self::where('parent_id', $parentId)->max('display_order');
        return ($maxOrder ?? -1) + 1;
    }

    /**
     * Calcular nivel basado en el padre
     */
    public static function calculateLevel(?string $parentId = null): int
    {
        if ($parentId === null) {
            return 0;
        }
        
        $parent = self::find($parentId);
        return $parent ? $parent->level + 1 : 0;
    }

    /**
     * Verificar si este item tiene hijos
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Obtener label efectivo (usa index_label si tiene hijos)
     */
    public function getEffectiveLabelAttribute(): string
    {
        if ($this->hasChildren() && $this->index_label) {
            return $this->index_label;
        }
        return $this->label;
    }

    /**
     * Obtener todos los ancestros (padres, abuelos, etc.)
     */
    public function getAncestors(): array
    {
        $ancestors = [];
        $current = $this->parent;
        
        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }

    /**
     * Obtener breadcrumb (ruta de ancestros)
     */
    public function getBreadcrumb(): array
    {
        $breadcrumb = $this->getAncestors();
        $breadcrumb[] = $this;
        
        return array_map(function($item) {
            return [
                'id' => $item->id,
                'label' => $item->label,
                'route' => $item->route
            ];
        }, $breadcrumb);
    }
}
