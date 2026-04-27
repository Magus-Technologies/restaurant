# 🍽️ RestaurantOS — Sistema de Gestión de Restaurante

Sistema completo PHP + MySQL para restaurantes con módulos de mozos (tablet), cocina KDS (TV), caja y panel de administración.

---

## Requisitos

- PHP 8.1+
- MySQL 8.0+ o MariaDB 10.5+
- Apache 2.4+ con `mod_rewrite`
- Extensión PDO + PDO_MySQL habilitada

---

## Instalación

### 1. Copiar archivos

Coloca la carpeta `restaurant/` dentro de tu directorio web:

```
/var/www/html/restaurant/     ← Apache estándar
/Applications/MAMP/htdocs/restaurant/   ← MAMP (Mac)
C:\xampp\htdocs\restaurant\   ← XAMPP (Windows)
```

### 2. Crear la base de datos

Importa el schema limpio (para instalación nueva):

```bash
mysql -u root -p < install/schema_final.sql
```

O desde phpMyAdmin: importa el archivo `install/schema_final.sql`.

> ⚠️ Si ya tienes una instalación anterior con `database.sql`, ejecuta `install/patch_v1.sql` para migrar.

### 3. Configurar conexión

Edita `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');
define('DB_NAME', 'restaurant_db');
define('BASE_URL', '/restaurant');  // Ajustar si usas otro path
```

### 4. Habilitar mod_rewrite (Apache)

Asegúrate que `AllowOverride All` está activo en tu `httpd.conf` o virtualhost.

### 5. Acceder al sistema

Abre: `http://localhost/restaurant/`

---

## Credenciales Demo

| Usuario    | Password   | Módulo       |
|------------|------------|--------------|
| `admin`    | `password` | Panel Admin  |
| `mozo1`    | `password` | Módulo Mozos |
| `cocina1`  | `password` | Cocina KDS   |
| `cajero1`  | `password` | Caja         |

---

## Módulos

| URL | Descripción | Dispositivo |
|-----|-------------|-------------|
| `/restaurant/` | Login principal | Cualquiera |
| `/restaurant/modules/mozos/` | Tomar pedidos | Tablet |
| `/restaurant/modules/cocina/` | Kitchen Display System | TV/Monitor |
| `/restaurant/modules/caja/` | Cobros y cierre | Desktop |
| `/restaurant/modules/admin/` | Administración | Desktop |

### Rutas cortas (con .htaccess)
- `/restaurant/mozos/`
- `/restaurant/cocina/`
- `/restaurant/caja/`
- `/restaurant/admin/`

---

## Estructura de Archivos

```
restaurant/
├── .htaccess                  ← Routing y seguridad
├── index.php                  ← Login
├── logout.php
├── config/
│   └── database.php           ← Configuración BD + clase DB (PDO)
├── includes/
│   └── functions.php          ← Auth, helpers, JSON response
├── api/                       ← REST API endpoints
│   ├── mesas.php
│   ├── categorias.php
│   ├── platos.php
│   ├── ordenes.php
│   ├── cocina.php
│   ├── caja.php
│   ├── notificaciones.php
│   ├── insumos.php
│   ├── proveedores.php
│   ├── compras.php
│   ├── usuarios.php
│   ├── reservas.php
│   ├── reportes.php
│   ├── delivery.php
│   ├── clientes.php
│   └── menu_dia.php
├── modules/
│   ├── mozos/index.php        ← UI Tablet (pedidos)
│   ├── cocina/index.php       ← KDS TV (producción)
│   ├── caja/index.php         ← Cobros
│   └── admin/index.php        ← Panel administrativo
└── install/
    ├── schema_final.sql       ← ✅ Usar para instalación nueva
    ├── database.sql           ← Schema original (legacy)
    └── patch_v1.sql           ← Migración si ya tienes database.sql
```

---

## API Reference

Todos los endpoints están en `/api/*.php` y responden JSON.

### Autenticación
La API usa sesiones PHP. El frontend debe estar en el mismo dominio.

### Ejemplos

**Listar mesas:**
```
GET /api/mesas.php
```

**Abrir mesa:**
```
POST /api/mesas.php
{"action":"abrir","id_mesa":1,"personas":4,"cliente_nombre":"Mesa Pérez"}
```

**Agregar items a orden:**
```
POST /api/ordenes.php
{"action":"agregar_items","id_orden":1,"items":[
  {"id_plato":6,"cantidad":2,"observacion":"sin cebolla","prioridad":"alta","opciones_texto":"Sin cebolla"}
]}
```

**Cambiar estado en cocina:**
```
POST /api/cocina.php
{"action":"cambiar_estado_item","id_item":15,"estado":"preparando"}
```

**Cobrar mesa:**
```
POST /api/caja.php
{"action":"cobrar","id_orden":1,"descuento":0,"propina":5,"tipo_comprobante":"boleta",
 "metodos_pago":[{"metodo":"efectivo","monto":120}]}
```

---

## Flujo Operativo

```
Cliente llega → Mozo abre mesa → Toma pedido (tablet) →
Envía a cocina → KDS muestra orden → Cocina prepara →
Marca LISTO → Mozo recibe notificación → Sirve →
Caja consolida → Cobra → Mesa pasa a "por limpiar"
```

---

## Notas Técnicas

- **Inventario automático**: El trigger `trg_descuento_inventario` descuenta insumos cuando un ítem se marca como "listo".
- **Polling**: Los módulos mozos y cocina hacen polling cada 5-10 segundos a las APIs.
- **IGV**: Configurado al 18% (Perú). Cambiar en `config/database.php`.
- **Zona horaria**: Lima UTC-5, configurado en BD y PHP.
- **Sesiones**: Timeout de 1 hora configurable en `SESSION_TIMEOUT`.

---

## Personalización

### Cambiar nombre del restaurante
En `config/database.php`:
```php
define('RESTAURANT_NAME', 'Tu Restaurante');
```

### Cambiar IGV
```php
define('RESTAURANT_IGV', 0.10); // 10%
```

### Agregar zona de mesas
En la BD: `INSERT INTO mesas (numero, zona, capacidad) VALUES ('11', 'jardin', 6);`
