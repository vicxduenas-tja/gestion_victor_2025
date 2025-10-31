# 🚀 GUÍA DE IMPLEMENTACIÓN PASO A PASO

## 📌 IMPORTANTE
Esta guía te ayudará a implementar TODO el sistema correctamente, verificando cada paso.

---

## FASE 1: BASE DE DATOS (6 Pasos)

### ✅ PASO 1: Verificar Tablas Existentes

**Comando:**
```bash
mysql -u root gestion_archivos < sql_paso_1_verificar_tablas.sql
```

**¿Qué hace?**
- Solo VERIFICA qué tablas ya tienes
- NO modifica nada

**Resultado esperado:**
Deberías ver una lista de tablas existentes:
- usuarios
- oficinas
- hojas_ruta
- documentos_oficiales
- carpetas
- archivos

**¿Qué buscar?**
- ✅ Si ves `documentos_respondidos` → Ya existe (salta paso 2)
- ❌ Si NO aparece → Necesitas ejecutar paso 2

---

### ✅ PASO 2: Crear Tabla documentos_respondidos

**Comando:**
```bash
mysql -u root gestion_archivos < sql_paso_2_tabla_documentos_respondidos.sql
```

**¿Qué hace?**
- Crea la tabla `documentos_respondidos`
- Esta tabla es CRÍTICA para que funcionen los reportes

**Resultado esperado:**
```
Tabla documentos_respondidos creada exitosamente
```

**Verificación:**
```sql
DESCRIBE documentos_respondidos;
```

Deberías ver los campos:
- id
- id_documento
- archivo_respuesta
- id_oficina_respondio
- id_usuario_respondio
- fecha_respuesta
- observaciones
- estado

---

### ✅ PASO 3: Modificar Tabla hojas_ruta

**Comando:**
```bash
mysql -u root gestion_archivos < sql_paso_3_modificar_hojas_ruta.sql
```

**¿Qué hace?**
- Permite NULL en `id_oficina_destino` (para documentos P.C.)
- Agrega estado 'conocimiento' al ENUM
- Agrega campo `sin_limite`

**Resultado esperado:**
```
Tabla hojas_ruta modificada exitosamente
```

**Posibles errores:**
- Si dice "Duplicate column name 'sin_limite'" → Ya existe, está bien
- Si dice error en ENUM → La columna ya tiene los valores correctos

---

### ✅ PASO 4: Modificar Tabla documentos_oficiales

**Comando:**
```bash
mysql -u root gestion_archivos < sql_paso_4_modificar_documentos_oficiales.sql
```

**¿Qué hace?**
- Agrega campo `sin_limite`
- Agrega prioridad 'urgente'
- Agrega estado 'respondido_retraso' (MUY IMPORTANTE)
- Permite NULL en `id_oficina_destino`

**Resultado esperado:**
```
Tabla documentos_oficiales modificada exitosamente
```

**Verificación importante:**
El campo `estado` debe tener estos valores:
- delegado
- en_progreso
- respondiendo
- completado
- archivado
- **respondido_retraso** ← NUEVO

---

### ✅ PASO 5: Modificar Oficinas y Carpetas

**Comando:**
```bash
mysql -u root gestion_archivos < sql_paso_5_oficinas_carpetas.sql
```

**¿Qué hace?**
- Agrega campo `abreviatura` a tabla `oficinas`
- Agrega campo `es_fija` a tabla `carpetas`

**Resultado esperado:**
```
Tablas oficinas y carpetas modificadas exitosamente
```

**ACCIÓN MANUAL REQUERIDA:**
Después de este paso, debes actualizar las abreviaturas de tus oficinas:

```sql
UPDATE oficinas SET abreviatura = 'DIRE' WHERE id = 1;
UPDATE oficinas SET abreviatura = 'CAPA' WHERE id = 3;
-- Repite para todas tus oficinas
```

**Formato de abreviatura:** Máximo 4 caracteres (ej: CAPA, DIRE, ADMI)

---

### ✅ PASO 6: Verificación Final

**Comando:**
```bash
mysql -u root gestion_archivos < sql_paso_6_verificacion_final.sql
```

**¿Qué hace?**
- Verifica que TODO esté correcto
- Muestra un resumen completo

**Resultado esperado:**
Deberías ver:
- ✅ documentos_respondidos existe
- ✅ hojas_ruta tiene estado 'conocimiento'
- ✅ documentos_oficiales tiene estado 'respondido_retraso'
- ✅ Campo sin_limite existe en ambas tablas
- ✅ Oficinas tienen abreviaturas

---

## FASE 2: VERIFICAR DIRECTORIOS

### ✅ PASO 7: Verificar Estructura de Directorios

**Comando:**
```bash
ls -la Assets/documentos_oficiales/
ls -la Assets/archivos/
ls -la Assets/documentos_para_conocimiento/
```

**Resultado esperado:**
```
Assets/
├── documentos_oficiales/
│   ├── en_proceso/
│   ├── completados/
│   └── archivado/
├── documentos_para_conocimiento/
└── archivos/
```

**Si falta algún directorio:**
Ya están creados en el commit anterior, pero si no aparecen, créalos:

```bash
mkdir -p Assets/documentos_oficiales/en_proceso
mkdir -p Assets/documentos_oficiales/completados
mkdir -p Assets/documentos_oficiales/archivado
mkdir -p Assets/documentos_para_conocimiento
mkdir -p Assets/archivos
chmod -R 755 Assets/documentos_oficiales
chmod -R 755 Assets/documentos_para_conocimiento
chmod -R 755 Assets/archivos
```

---

## FASE 3: PROBAR MÓDULOS

### ✅ PASO 8: Probar Hoja de Ruta

**URL:** `http://localhost/gestion/hojaruta`

**Pruebas:**
1. ✅ La página carga correctamente
2. ✅ Click en "Nueva Hoja de Ruta" → Modal se abre
3. ✅ Se genera número correlativo automático
4. ✅ Puede seleccionar oficina destino
5. ✅ Puede seleccionar "Para Conocimiento"
6. ✅ Puede subir PDF
7. ✅ Click "Guardar y Delegar" → Guarda correctamente

**Si hay error:**
- Verifica que `Assets/documentos_oficiales/en_proceso/` exista
- Verifica que tenga permisos de escritura (755)

---

### ✅ PASO 9: Probar Calendario

**URL:** `http://localhost/gestion/calendario`

**Pruebas:**
1. ✅ La página carga correctamente
2. ✅ El **contador verde del sidebar** muestra número correcto
3. ✅ Se ven las tareas delegadas
4. ✅ Click en una tarea → Se abre modal
5. ✅ Click "Responder" → Puede subir PDF
6. ✅ Click "Completar" → Tarea se completa

**Verificaciones importantes:**
- Si la tarea tiene fecha límite vencida → Debe marcar como 'respondido_retraso'
- Si se marca "Archivar" → Debe actualizar hojas_ruta a 'archivado'

---

### ✅ PASO 10: Probar Reportes

**URL:** `http://localhost/gestion/reportes`

**Pruebas:**
1. ✅ Widgets muestran números correctos
2. ✅ Puede generar reporte con filtros
3. ✅ Click en "Productividad" → Carga página

**URL:** `http://localhost/gestion/reportes/productividad`

**Pruebas:**
1. ✅ **TABLA 1:** Muestra usuarios con métricas
2. ✅ **TABLA 2:** Muestra inventario de documentos activos
3. ✅ **TABLA 3:** Muestra histórico de documentos respondidos
4. ✅ Click en botón verde de "Respondidos" → Modal se abre
5. ✅ Modal carga datos del usuario

---

## FASE 4: FLUJO COMPLETO

### ✅ PASO 11: Probar Flujo Completo

**Flujo de prueba:**

1. **ADMIN → Hoja de Ruta**
   - Crear nueva hoja de ruta
   - Delegar a una oficina
   - Subir PDF

2. **USUARIO → Calendario**
   - Verificar que aparece el documento
   - Contador del sidebar debe aumentar
   - Abrir documento
   - Subir respuesta PDF
   - Completar tarea

3. **ADMIN → Reportes**
   - Ver que el contador de "Completados" aumentó
   - Ir a Productividad
   - Ver que aparece en Tabla 1 (con número de respondidos)
   - Ver que aparece en Tabla 3 (historial)

**Si todo funciona:**
✅ Sistema 100% operativo

---

## CHECKLIST FINAL

### Base de Datos
- [ ] Tabla documentos_respondidos creada
- [ ] Campo sin_limite agregado
- [ ] Estado 'conocimiento' en hojas_ruta
- [ ] Estado 'respondido_retraso' en documentos_oficiales
- [ ] Oficinas tienen abreviaturas

### Directorios
- [ ] Assets/documentos_oficiales/en_proceso/
- [ ] Assets/documentos_oficiales/completados/
- [ ] Assets/documentos_oficiales/archivado/
- [ ] Assets/documentos_para_conocimiento/
- [ ] Assets/archivos/

### Módulos
- [ ] Hoja de Ruta funciona
- [ ] Calendario funciona
- [ ] Contador del sidebar funciona
- [ ] Reportes funcionan
- [ ] 3 tablas en productividad funcionan

### Flujo Completo
- [ ] Delegar documento funciona
- [ ] Responder documento funciona
- [ ] Detecta retrasos correctamente
- [ ] Genera nombres de archivo correctos
- [ ] Registra en documentos_respondidos
- [ ] Aparece en reportes

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### Error: "Table doesn't exist"
**Solución:** Ejecuta los scripts SQL en orden

### Error: "Permission denied"
**Solución:**
```bash
chmod -R 755 Assets/
```

### Error: "Column not found"
**Solución:** Ejecuta el script SQL correspondiente

### Contador del sidebar en 0
**Solución:**
1. Verificar que contarPendientes() esté corregido
2. Verificar que haya documentos en estado 'delegado'

### Reportes vacíos
**Solución:**
1. Verificar que exista tabla documentos_respondidos
2. Completar al menos una tarea para que se registre
3. Verificar que completarTarea() esté corregido

---

## 📞 PRÓXIMOS PASOS

Una vez completados TODOS los pasos:
1. Commit de las abreviaturas de oficinas
2. Probar con datos reales
3. ¡Sistema listo para producción!

---

**¿En qué paso estás? Comparte el resultado de cada paso para ayudarte a continuar.**
