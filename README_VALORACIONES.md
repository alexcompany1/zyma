# Sistema de Valoraciones y Opiniones - Zyma

## 📋 Descripción General

El sistema de valoraciones permite a los clientes del restaurante **Zyma** compartir sus opiniones sobre la experiencia gastronómica. Los usuarios logueados pueden:

- **Valorar** el restaurante con puntuaciones de 1 a 5 estrellas
- **Escribir comentarios** sobre su experiencia (opcional)
- **Editar su valoración** en cualquier momento
- **Ver valoraciones** de otros clientes
- **Visualizar estadísticas** sobre la experiencia general

## 🗄️ Estructura de Base de Datos

### Tabla: `valoraciones`

```sql
CREATE TABLE IF NOT EXISTS valoraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
    comentario TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_puntuacion (puntuacion),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Campos:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único de la valoración (clave primaria) |
| `id_usuario` | INT | Referencia al usuario que realiza la valoración (FK) |
| `puntuacion` | INT | Puntuación de 1 a 5 estrellas |
| `comentario` | TEXT | Texto opcional con la opinión del usuario (máx 500 caracteres) |
| `fecha_creacion` | DATETIME | Timestamp de creación (automático) |
| `fecha_actualizacion` | DATETIME | Timestamp de última edición (automático) |

**Restricciones:**
- La puntuación debe estar entre 1 y 5
- Cada usuario solo puede tener una valoración (se actualiza si modifica la suya)
- Las valoraciones de un usuario se eliminan si el usuario es eliminado

## 📁 Archivos del Sistema

### 1. **database_valoraciones.sql**
Script SQL para crear la tabla `valoraciones` en la base de datos.

**Uso:** Ejecutar este script una sola vez en phpMyAdmin o consola MySQL:
```bash
mysql -u root zyma < database_valoraciones.sql
```

### 2. **valoraciones.php**
Página principal del sistema. Muestra:
- Estadísticas generales (promedio de valoración, distribución de estrellas)
- Formulario para agregar/editar valoraciones (solo usuarios logueados)
- Lista de todas las valoraciones ordenadas por fecha

**Características:**
- Header unificado (solo si usuario está logueado)
- Selector visual de estrellas (1-5)
- Campo de texto para comentarios
- Contador de caracteres en tiempo real
- Validación en cliente y servidor
- Invitación para iniciar sesión (si no está logueado)

**URL:** `valoraciones.php`

### 3. **guardar_valoracion.php**
Endpoint API que procesa las valoraciones. Realiza:
- Validación de sesión
- Validación de puntuación (1-5)
- Validación de comentario (máx 500 caracteres)
- Inserción o actualización en BD
- Retorna JSON

**Método:** POST
**Entrada (JSON):**
```json
{
    "puntuacion": 4,
    "comentario": "Excelente servicio y comida deliciosa"
}
```

**Salida (JSON):**
```json
{
    "success": true,
    "mensaje": "Valoración guardada correctamente."
}
```

**Errores posibles:**
- 401: Usuario no autenticado
- 400: Validación fallida (puntuación fuera de rango, comentario muy largo)
- 500: Error de base de datos

### 4. **Estilos CSS** (en `styles.css`)
Sección especializada con clases para:
- `.ratings-stats-card` - Tarjeta de estadísticas
- `.ratings-stats-header` - Contenedor de estadísticas
- `.ratings-score` - Puntuación promedio
- `.ratings-distribution` - Distribución de estrellas
- `.add-rating-card` - Tarjeta del formulario
- `.star-selector` - Selector interactivo de estrellas
- `.rating-item` - Elemento individual de opinión
- Y más... (ver archivo CSS para referencia completa)

## 🎨 Flujo de Uso

### Para Usuarios Logueados:

1. **Acceder a Valoraciones:**
   - Click en "Valoraciones" desde el menú
   - URL: `valoraciones.php`

2. **Ver Estadísticas:**
   - Se muestran automáticamente en la parte superior
   - Promedio de puntuación
   - Distribución de estrellas (1-5)
   - Total de valoraciones

3. **Agregar/Editar Valoración:**
   - Si es la primera vez: forma vacía para agregar nueva
   - Si ya tiene valoración: forma pre-rellena con sus datos
   - Seleccionar puntuación (1-5 estrellas)
   - Escribir comentario opcional
   - Click en "Enviar valoración" o "Guardar cambios"

4. **Ver otras Opiniones:**
   - Scroll hacia abajo
   - Consulta "Opiniones recientes"
   - Ver nombre, fecha, estrellas y comentario

### Para Usuarios No Logueados:

- Se muestra invitación para iniciar sesión
- Pueden ver todas las valoraciones
- No pueden agregar opiniones

## 🔄 Flujo Técnico

### Crear/Actualizar Valoración

```
Usuario llena formulario
         ↓
JavaScript valida campos
         ↓
Envía JSON a guardar_valoracion.php
         ↓
PHP valida sesión y datos
         ↓
Busca si usuario ya tiene valoración
         ↓
INSERT (nueva) o UPDATE (existente)
         ↓
Retorna JSON de éxito
         ↓
JavaScript recarga página (opcional)
```

### Obtener Valoraciones

```
Página valoraciones.php carga
         ↓
PHP consulta tabla valoraciones
         ↓
Calcula estadísticas:
  - Promedio
  - Distribución (1-5 estrellas)
         ↓
Obtiene valoración del usuario actual (si existe)
         ↓
Renderiza HTML con datos
         ↓
JavaScript maneja interactividad
```

## 🎯 Funcionalidades Principales

### ⭐ Selector de Estrellas
- 5 opciones visuales (1-5 estrellas)
- Feedback textual al seleccionar
- Hover effects
- Almacena valor en input radio

### 📝 Campo de Comentario
- Máximo 500 caracteres
- Contador en tiempo real
- Placeholder descriptivo
- Validación en servidor

### 📊 Estadísticas
- Promedio de puntuación (ej: 4.3)
- Gráfica de distribución
- Total de valoraciones
- Cada puntuación muestra cantidad

### 📱 Responsive Design
- Desktop: layout de 2 columnas
- Tablet: ajusta a 1 columna
- Móvil: botones y elementos redimensionados

## 🔐 Seguridad

- **Autenticación:** Solo usuarios sesión válida pueden enviar
- **Validación:** Todas las entradas se validan (cliente y servidor)
- **SQL Injection:** Prepared statements con parámetros
- **XSS:** Todos los datos se escapan con `htmlspecialchars()`
- **CSRF:** Sesión y POST requeridos

## ✅ Validaciones

### Cliente (JavaScript):
- Puntuación seleccionada (requerida)
- Comentario ≤ 500 caracteres

### Servidor (PHP):
- Sesión activa
- Puntuación entre 1-5
- Comentario ≤ 500 caracteres
- Usuario existe

## 🎨 Paleta de Colores

Se utiliza el esquema de colores de Zyma:

| Color | Variable CSS | Uso |
|-------|-------------|-----|
| Oro | `--gold` | Estrellas llenas, botones primarios |
| Rojo oscuro | `--dark-red` | Títulos, botones |
| Blanco | `--white` | Fondos, texto contraste |
| Gris claro | `--light-gray` | Bordes, backgrounds secundarios |

## 🚀 Instalación

### Paso 1: Crear Tabla en BD

```bash
1. Abrir phpMyAdmin
2. Seleccionar BD: "zyma"
3. Click en "SQL"
4. Copiar/pegar contenido de "database_valoraciones.sql"
5. Click "Continuar"
```

O desde terminal:
```bash
mysql -u root -p zyma < database_valoraciones.sql
```

### Paso 2: Verificar Archivos

Asegurarse que existen en la raíz:
- ✅ `valoraciones.php`
- ✅ `guardar_valoracion.php`
- ✅ `database_valoraciones.sql`
- ✅ `styles.css` (con estilos nuevos)

### Paso 3: Agregar Link en Navegación

En `usuario.php` o `header.php`, agregar:
```html
<a href="valoraciones.php">Valoraciones</a>
```

## 📊 Ejemplos de Uso

### Consultas SQL Útiles

Obtener todas las valoraciones:
```sql
SELECT v.*, u.nombre, u.email 
FROM valoraciones v 
JOIN usuarios u ON v.id_usuario = u.id
ORDER BY v.fecha_creacion DESC;
```

Promedio y distribución:
```sql
SELECT 
    AVG(puntuacion) as promedio,
    COUNT(*) as total,
    SUM(CASE WHEN puntuacion = 5 THEN 1 ELSE 0 END) as estrellas_5,
    SUM(CASE WHEN puntuacion = 4 THEN 1 ELSE 0 END) as estrellas_4
FROM valoraciones;
```

Valoración de usuario específico:
```sql
SELECT * FROM valoraciones WHERE id_usuario = 123;
```

## 🐛 Troubleshooting

### Problema: "Debes iniciar sesión para valorar"
**Solución:** Asegúrate de estar logueado correctamente

### Problema: Tabla no existe
**Solución:** Ejecutar `database_valoraciones.sql` en la BD

### Problema: Comentarios no guardados
**Solución:** Revisar que `maxlength="500"` esté en textarea

### Problema: Estrellas no se actualizan visualmente
**Solución:** Limpiar caché del navegador (Ctrl+Shift+R)

## 📝 Notas de Desarrollo

- El sistema usa **JSON para comunicación** entre cliente-servidor
- Cada usuario solo puede tener **una valoración**
- Las puntuaciones se **redondean a 1 decimal**
- Los comentarios aparecen en **orden cronológico descendente**
- La BD usa **utf8mb4** para caracteres unicode

## 🔮 Mejoras Futuras (Opcionales)

- Enviar email de confiración después de valorar
- Sistema de "útil/no útil" para cada opinión
- Respuestas de administrador a opiniones
- Filtrar opiniones por puntuación
- Reportar opiniones inapropiadas
- Ranking de usuarios más útiles

---

**Versión:** 1.0  
**Fecha:** 2025-02-17  
**Estado:** Producción  
**Autor:** Sistema Zyma
