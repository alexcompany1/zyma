# Guía de Solución de Problemas - Sistema de Valoraciones

## Error: "Error al procesar la valoración. Intenta de nuevo"

Si ves este mensaje al intentar guardar una valoración, sigue estos pasos:

### Paso 1: Ejecutar Inicialización
1. Inicia sesión como trabajador
2. En el Panel de Control, haz clic en **"Inicializar Valoraciones"**
3. Debería mostrar un mensaje de éxito

### Paso 2: Verificar Estado (Diagnóstico)
1. En el Panel de Control, haz clic en **"Diagnóstico de Valoraciones"**
2. Revisa los siguientes puntos:
   - ✓ Archivo SQL existe
   - ✓ Tabla valoraciones existe en BD
   - ✓ Tabla productos existe
   - ✓ Tabla usuarios existe
   - ✓ Archivos de imagen existen (estrella)

### Paso 3: Probar Manualmente
1. En el Panel de Control, haz clic en **"Probar Valoración (Test)"**
2. Selecciona un producto, una puntuación y opcionalmente un comentario
3. Haz clic en "Enviar Prueba"
4. Si ves un error, el mensaje indicará el problema exacto

## Posibles Soluciones

### Problema: "Archivo database_valoraciones.sql no encontrado"
**Solución:** El archivo SQL debe estar en la raíz de `c:\xampp\htdocs\zyma-main\`
- Verifica que exista: `database_valoraciones.sql`
- Si falta, contacta al desarrollador

### Problema: "Table 'zyma.valoraciones' doesn't exist"
**Solución:** Haz clic en "Inicializar Valoraciones" nuevamente
- Esto eliminará y recreará la tabla
- Todas las valoraciones anteriores se perderán

### Problema: La valoración se guarda pero no aparece
1. Recarga la página de valoraciones (F5)
2. Verifica que otros usuarios ven tu valoración
3. En Diagnóstico, verifica "Valoraciones Existentes"

### Problema: No puedo valorar siendo cliente logueado
1. Asegúrate de estar logueado (deberías ver tu nombre en la esquina)
2. Intenta el Test de Valoración como trabajador
3. Si el test funciona, el problema está en el formulario de cliente

## Información Técnica

**Archivos del sistema:**
- `valoraciones.php` - Página principal de valoraciones
- `guardar_valoracion.php` - Backend que procesa el guardado
- `database_valoraciones.sql` - Schema de la tabla
- `init_valoraciones.php` - Inicializador manually
- `diagnostico_valoraciones.php` - Diagnóstico del sistema
- `test_valoracion.php` - Test manual para trabajadores

**Base de datos:**
- Tabla: `valoraciones`
- Relaciones: 
  - `id_usuario` → `usuarios.id`
  - `id_producto` → `productos.id`
- Única restricción: Un usuario solo puede valorar cada producto UNA VEZ

**Campos:**
- `id` (INT, PK)
- `id_usuario` (INT, FK)
- `id_producto` (INT, FK)
- `puntuacion` (INT 1-5)
- `comentario` (TEXT)
- `fecha_creacion` (DATETIME)
- `fecha_actualizacion` (DATETIME)

## Contacto del Desarrollador
Si después de estos pasos sigues con problemas, prepara:
1. Screenshot del error
2. Log del servidor (si puedes acceder)
3. Resultado del Diagnóstico

---
**Última actualización:** 17/02/2026
