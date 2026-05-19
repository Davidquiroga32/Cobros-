# 📋 GUÍA DE IMPLEMENTACIÓN — SmartPay

## Orden de archivos a copiar/reemplazar

### 1. MIGRACIONES (ejecutar en este orden)
```
php artisan migrate --path=database/migrations/2026_05_18_000000_create_sectores_table.php
php artisan migrate --path=database/migrations/2026_05_18_000001_add_cn_sector_to_users_table.php
php artisan migrate --path=database/migrations/2026_05_18_000002_create_cajas_table.php
php artisan migrate --path=database/migrations/2026_05_18_000003_create_rutas_table.php
```

### 2. MODELOS — reemplazar/crear en `app/Models/`
- `User.php`           → reemplazar
- `Sector.php`         → nuevo
- `Caja.php`           → nuevo
- `Ruta.php`           → nuevo
- `RutaParada.php`     → nuevo
- `CobradorEstado.php` → sin cambios (ya existía)

### 3. SERVICIOS — reemplazar en `app/Services/`
- `CobradorEstadoService.php` → reemplazar (ahora cruza datos reales)

### 4. CONTROLADORES — reemplazar/crear

**Admin (`app/Http/Controllers/Admin/`)**
- `CobradorEstadoController.php` → reemplazar (BUG: retornaba JSON en ruta web)
- `UserController.php`           → reemplazar (agrega CN y Sector)
- `SectorController.php`         → nuevo
- `CajaController.php`           → nuevo (admin puede abrir cajas a cobradores)

**Cobrador (`app/Http/Controllers/Cobrador/`)**
- `RutaController.php` → nuevo
- `CajaController.php` → nuevo

**API (`app/Http/Controllers/Api/`)**
- `CobradorEstadoApiController.php` → reemplazar (corregido: validación + seguridad auth)

### 5. RUTAS
- `routes/web.php` → reemplazar completo

### 6. VISTAS — crear en `resources/views/`

```
admin/cobradores/estado.blade.php   ← Vista real (antes mostraba datos ficticios)
admin/sectores/index.blade.php
admin/sectores/create.blade.php
admin/cajas/index.blade.php         ← Crear manualmente (ver estructura abajo)
admin/cajas/create.blade.php        ← Crear manualmente
admin/cajas/show.blade.php          ← Crear manualmente
cobrador/ruta/index.blade.php
cobrador/caja/index.blade.php
```

---

## ⚠️ BUGS CORREGIDOS

### 1. CobradorEstadoController retornaba JSON en ruta web
**Antes:**
```php
return response()->json($service->obtenerDashboard());
```
**Ahora:**
```php
return view('admin.cobradores.estado', compact('cobradores'));
```

### 2. Ruta /cobradores/sync sin autenticación (VULNERABILIDAD)
**Antes:**
```php
Route::post('/cobradores/sync', [...]);  // Sin middleware
```
**Ahora:**
```php
Route::post('/cobradores/sync', [...])->middleware('auth');
```

### 3. CobradorEstadoService usaba datos ficticios
El servicio ahora cruza:
- Pagos reales del día agrupados por cobrador (1 query)
- Cuotas pendientes del día como meta (1 query)
- Porcentaje calculado correctamente

### 4. `progreso_ruta` en `cobradores_estado` era un campo manual
Ahora se calcula desde `rutas.paradas_completadas / rutas.total_paradas`
y se sincroniza automáticamente al actualizar cada parada.

---

## 🗂️ ESTRUCTURA DE NAVEGACIÓN A AGREGAR

### Admin sidebar (`layouts/admin.blade.php`)
Agregar links en la sección Administración:
```blade
<a href="{{ route('admin.sectores.index') }}" class="nav-item {{ request()->routeIs('admin.sectores.*') ? 'active' : '' }}">
    <i class="fas fa-map"></i> Sectores
</a>
<a href="{{ route('admin.cajas.index') }}" class="nav-item {{ request()->routeIs('admin.cajas.*') ? 'active' : '' }}">
    <i class="fas fa-cash-register"></i> Cajas
</a>
```

### Cobrador sidebar (`layouts/cobrador.blade.php`)
```blade
<a href="{{ route('cobrador.ruta.index') }}" class="nav-item {{ request()->routeIs('cobrador.ruta.*') ? 'active' : '' }}">
    <i class="fas fa-route"></i> Mi ruta
</a>
<a href="{{ route('cobrador.caja.index') }}" class="nav-item {{ request()->routeIs('cobrador.caja.*') ? 'active' : '' }}">
    <i class="fas fa-cash-register"></i> Mi caja
</a>
```

---

## 📐 ESTRUCTURA BD RESULTANTE

```
users
  + cn          VARCHAR(20) UNIQUE NULL
  + sector_id   FK → sectores.id NULL

sectores
  id, nombre, codigo, descripcion, ciudad, activo

cajas
  id, codigo, cobrador_id, sector_id, abierta_por, cerrada_por
  monto_inicial, monto_cobrado, monto_gastos, monto_final
  estado (abierta|cerrada|cuadrada)
  fecha_apertura, fecha_cierre, fecha_jornada

rutas
  id, cobrador_id, caja_id, fecha
  total_paradas, paradas_completadas, progreso (columna calculada), estado

ruta_paradas
  id, ruta_id, cliente_id, cuota_id, orden
  estado (pendiente|visitado|no_encontrado|reagendado)
  monto_esperado, monto_cobrado, hora_visita, observaciones
```

---

## ✅ FUNCIONALIDADES COMPLETADAS

| Módulo                   | Estado |
|--------------------------|--------|
| Estado operativo (real)  | ✅ |
| Sectores CRUD            | ✅ |
| CN en usuarios           | ✅ |
| Cajas (admin)            | ✅ |
| Cajas (cobrador)         | ✅ |
| Rutas del cobrador       | ✅ |
| Progreso de ruta real    | ✅ |
| Seguridad /sync          | ✅ |
| CobradorEstado service   | ✅ |

## 📌 PENDIENTE (siguiente iteración)
- Vista `admin/cajas/show.blade.php` detallada
- Vista `admin/cajas/index.blade.php` con filtros
- Vincular `caja_id` automáticamente al generar ruta del cobrador
- Reportes de cierre de caja en PDF
- Integración de ubicación GPS en tiempo real (WebSocket o polling)
