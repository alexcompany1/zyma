<!-- 
    ===============================================================================
    GUÍA DE INSTALACIÓN RÁPIDA - SISTEMA DE VALORACIONES ZYMA
    ===============================================================================
    
    Este archivo documenta paso a paso cómo instalar el sistema de valoraciones
    en tu aplicación Zyma. Sigue los pasos en orden.
-->

# 🚀 INSTALACIÓN RÁPIDA - Sistema de Valoraciones Zyma

## ✅ Verificación Previa

Asegúrate de tener:
- [ ] Acceso a phpMyAdmin o terminal MySQL
- [ ] Base de datos "zyma" funcionando
- [ ] Conexión PDO en `config.php` correcta
- [ ] Usuarios crear en BD (tabla "usuarios")

---

## 📋 Paso 1: Crear la Tabla en Base de Datos

### Opción A: Desde phpMyAdmin (Recomendado)

1. Abre tu navegador → http://localhost/phpmyadmin
2. Selecciona la BD "zyma" en el panel izquierdo
3. Click en pestaña "SQL"
4. Copia todo el contenido de: `database_valoraciones.sql`
5. Pega en el editor SQL
6. Click en botón "Continuar" (abajo)
7. Verás: "✓ La consulta se ejecutó exitosamente"

### Opción B: Desde Terminal

```bash
cd c:\xampp\htdocs\zyma-main
mysql -u root -p zyma < database_valoraciones.sql

# Te pedirá la contraseña (presiona Enter si es vacía)
# Verás: Query OK, 0 rows affected
```

### Opción C: Verificar que se creó

En phpMyAdmin:
1. Haz click en la BD "zyma"
2. Deberías ver tabla "valoraciones" en la lista
3. Haz click en ella para ver la estructura

---

## 📁 Paso 2: Verificar Archivos

Confirma que estos archivos existen en `c:\xampp\htdocs\zyma-main\`:

```
✓ valoraciones.php          (página principal)
✓ guardar_valoracion.php    (procesa datos)
✓ database_valoraciones.sql (script de BD)
✓ styles.css                (con nuevos estilos)
✓ header.php                (header existente)
✓ config.php                (configuración BD)
```

---

## 🔗 Paso 3: Agregar Link de Navegación

### En usuario.php (para usuarios logueados)

Busca la sección "quick-dropdown" y agrega dentro:

```html
<div class="dropdown quick-dropdown" id="quickDropdown">
    <a href="usuario.php">Inicio</a>
    <a href="carta.php">Ver carta</a>
    <a href="valoraciones.php">Valoraciones</a>  <!-- ← AGREGAR ESTA LÍNEA -->
</div>
```

### En header.php (opcional, para usuarios en cualquier página)

Busca `<a href="carta.php">Ver carta</a>` y después agrega:

```html
<a href="valoraciones.php">Valoraciones</a>  <!-- ← AGREGAR -->
```

### En index.php (página de inicio sin login)

En el botón-row del landing page, agrega:

```html
<a href="valoraciones.php?guest=1" class="btn-secondary">Ver Valoraciones</a>
```

---

## 🧪 Paso 4: Prueba la Instalación

### Para Usuarios Logueados:

1. Inicia sesión en la app
2. Ve a http://localhost/zyma-main/valoraciones.php
3. Deberías ver:
   - ✓ Título "Valoraciones y Opiniones"
   - ✓ Estadísticas (promedio 0, sin opiniones aún)
   - ✓ Formulario para agregar valoración
   - ✓ Selector de estrellas interactivo

4. Completa con:
   - Selecciona 5 estrellas
   - Escribe: "Excelente restaurante"
   - Click "Enviar valoración"

5. Verifica:
   - ✓ Se muestra mensaje "Valoración guardada"
   - ✓ Página se recarga
   - ✓ Aparece tu opinión en listado

### Para Usuarios No Logueados:

1. Logout o abre incógnito
2. Ve a http://localhost/zyma-main/valoraciones.php
3. Deberías ver:
   - ✓ Botones "Iniciar sesión" y "Crear cuenta"
   - ✓ Puedes ver valoraciones de otros
   - ✓ No puedes agregar (bloqueado)

---

## 🔧 Paso 5: Configuración Avanzada (Opcional)

### Mostrar Estadísticas en Dashboard

En `usuario.php`, después del título, agrega:

```php
<?php
// Cargar estadísticas de valoraciones
try {
    $stmt = $pdo->prepare("
        SELECT AVG(puntuacion) as promedio, COUNT(*) as total
        FROM valoraciones
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    $promedio = $stats['promedio'] ? round($stats['promedio'], 1) : 0;
    $total = $stats['total'] ?? 0;
} catch (Exception $e) {
    $promedio = 0;
    $total = 0;
}
?>

<!-- Mostrar en la página -->
<?php if ($total > 0): ?>
    <p class="muted">
        ⭐ Zyma tiene <?= $promedio ?>/5 estrellas 
        (<?= $total ?> opiniones)
        <a href="valoraciones.php">Ver más →</a>
    </p>
<?php endif; ?>
```

---

## 🎨 Paso 6: Personalización de Estilos

Si quieres cambiar colores, edita en `styles.css`:

```css
/* Cambiar color de estrellas */
.star-icon.filled { 
    color: #EECF6D;  /* Color oro - cambiar aquí */
}

/* Cambiar color de card */
.ratings-stats-card {
    border-left: 4px solid #720E07;  /* Color rojo - cambiar aquí */
}

/* Cambiar texto feedback */
.star-selector-feedback.feedback-5 { 
    background: #e6ffe6;  /* Verde claro */
    color: #059669;       /* Verde oscuro */
}
```

---

## ⚠️ Solución de Problemas

### ❌ Error: "Error de conexión a la base de datos"

**Causa:** `config.php` no conecta correctamente

**Solución:**
1. Verifica que MySQL está corriendo (XAMPP)
2. Verifica credenciales en `config.php`
3. Verifica que la BD "zyma" existe

### ❌ Error: "Error de conexión. Intenta de nuevo."

**Causa:** `guardar_valoracion.php` falla

**Solución:**
1. Abre Consola del Navegador (F12 → Network)
2. Click en "Enviar valoración"
3. Busca request a "guardar_valoracion.php"
4. Ve la respuesta (Response tab)
5. Revisa qué error retorna

### ❌ Las estrellas no se ven

**Causa:** Fuentes o estilos no cargados

**Solución:**
```bash
# Limpia caché del navegador
Ctrl + F5 (Windows/Linux)
Cmd + Shift + R (Mac)
```

### ❌ No puedo guardar valoración (400 Bad Request)

**Causa:** Datos inválidos

**Solución:**
- Puntuación debe estar entre 1-5
- Comentario máximo 500 caracteres
- Debes estar logueado

### ❌ Tabla no existe en BD

**Causa:** No ejecutaste `database_valoraciones.sql`

**Solución:** Vuelve a Paso 1

---

## 📊 Consultas Útiles para Testing

Ejecuta en phpMyAdmin → SQL:

### Ver todas las valoraciones
```sql
SELECT v.*, u.nombre, u.email FROM valoraciones v 
JOIN usuarios u ON v.id_usuario = u.id 
ORDER BY v.fecha_creacion DESC;
```

### Ver promedio y total
```sql
SELECT 
    AVG(puntuacion) as promedio,
    COUNT(*) as total
FROM valoraciones;
```

### Ver distribución
```sql
SELECT 
    puntuacion,
    COUNT(*) as cantidad
FROM valoraciones
GROUP BY puntuacion
ORDER BY puntuacion DESC;
```

### Ver valoración de un usuario (ID 1)
```sql
SELECT * FROM valoraciones WHERE id_usuario = 1;
```

### Borrar todas las valoraciones (para resetear)
```sql
TRUNCATE TABLE valoraciones;
```

---

## ✨ Características Implementadas

✅ Sistema completo de valoraciones  
✅ Selector visual de estrellas (1-5)  
✅ Comentarios opcionales (máx 500 caracteres)  
✅ Actualización en tiempo real  
✅ Estadísticas automáticas  
✅ Editar valoración propia  
✅ Responsive design (móvil/tablet/desktop)  
✅ Validación completa (cliente + servidor)  
✅ Diseño consistente con Zyma  
✅ Documentación completa  

---

## 📞 Soporte

Si algo no funciona:

1. **Revisa la Consola:**
   - F12 → Console
   - ¿Hay errores en rojo?

2. **Revisa phpMyAdmin:**
   - ¿Tabla "valoraciones" existe?
   - ¿Hay registros en ella?

3. **Revisa Logs:**
   - `c:\xampp\apache\logs\error.log`
   - `c:\xampp\mysql\data\`

4. **Compara con ejemplos:**
   - Los datos en BD deben tener: id, id_usuario, puntuacion, comentario
   - Las fechas son automáticas

---

## 🎓 Referencias

- PHP Prepared Statements: https://www.php.net/manual/es/pdo.prepared-statements.php
- JSON en JavaScript: https://developer.mozilla.org/es/docs/Web/JavaScript/Reference/Global_Objects/JSON
- SQL Joins: https://www.w3schools.com/sql/sql_join.asp
- CSS Grid: https://developer.mozilla.org/es/docs/Web/CSS/CSS_Grid_Layout

---

**Versión:** 1.0  
**Última actualización:** 2025-02-17  
**Estado:** ✅ Listo para Producción
