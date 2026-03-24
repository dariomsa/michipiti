# Estado del proyecto

Fecha: 2026-03-23

## Punto actual

El foco principal del último bloque fue el módulo `videografia`.
Se avanzó en:

- listado de audiovisuales alineado visualmente al módulo de productos
- pantalla de edición propia para audiovisuales
- separación del dominio audiovisual respecto de `productos`
- estructura relacional nueva por tipo audiovisual
- trazabilidad básica con mensajes y movimientos
- planificador audiovisual conectado a rutas y controlador propios

## Qué quedó implementado

- Listado de audiovisuales:
  - archivo: `resources/views/videografia/audiovisuales/index.blade.php`
  - mantiene estructura visual parecida a los listados de productos
  - ya abre edición real por audiovisual
  - usa filtros por búsqueda, sección, responsable, videógrafo, estado y fecha

- Edición de audiovisuales:
  - archivo: `resources/views/videografia/audiovisuales/edit.blade.php`
  - usa el layout general del sistema
  - el lateral muestra:
    - resumen del audiovisual
    - movimientos
    - mensajes
  - ya carga datos reales desde DB
  - ya guarda cambios sobre `audiovisuales` y tablas hijas por tipo

- Planificador audiovisual:
  - archivo: `app/Http/Controllers/Videografia/PlanificadorController.php`
  - ya no depende de `tipo_producto_id`
  - registra movimientos al:
    - crear o editar
    - mover
    - aprobar
    - enviar a pauta
    - eliminar

## Rutas existentes del módulo

Grupo:

- prefijo: `/videografia`
- name: `videografia.*`
- middleware: `auth` + `role:videografia,editor,director`

Rutas de listado/edición:

- `GET /videografia/listado`
- `GET /videografia/listado/{audiovisual}/edit`
- `PUT /videografia/listado/{audiovisual}`
- `POST /videografia/listado/{audiovisual}/mensajes`

Rutas de planificación:

- `GET /videografia/planificacion`
- `GET /videografia/planificacion/week`
- `GET /videografia/planificacion/responsables`
- `POST /videografia/planificacion/store`
- `POST /videografia/planificacion/move`
- `POST /videografia/planificacion/aprobar`
- `POST /videografia/planificacion/to-pauta`
- `DELETE /videografia/planificacion/{audiovisual}`

## Controladores principales

- `app/Http/Controllers/Videografia/AudiovisualController.php`
  - carga tipos audiovisuales desde catálogo
  - guarda `tipo_audiovisual_id` en la tabla principal `audiovisuales`
  - sincroniza detalle específico por tipo en tablas hijas
  - registra movimientos al editar
  - permite registrar mensajes

- `app/Http/Controllers/Videografia/PlanificadorController.php`
  - maneja semana, responsables, store, move, approve, toPauta y destroy
  - serializa audiovisuales para el frontend del planificador

## Decisiones técnicas tomadas

- `productos` y `videografia` son módulos diferentes
- cada módulo debe mantener independencia funcional y de datos
- la relación entre ambos dominios no debe resolverse con tablas de negocio compartidas
- el único punto de relación aceptado por ahora es `empresa`
- `audiovisuales` ya no debe depender de `productos`
- se descartó usar JSON/meta para el detalle variable por tipo
- se optó por modelo relacional con:
  - catálogo `tipo_audiovisuales`
  - tabla principal `audiovisuales`
  - tablas hijas por tipo
  - tablas de soporte para mensajes, movimientos, requerimientos y redes

## Estructura nueva para audiovisuales

- Catálogo:
  - `tipo_audiovisuales`

- Tabla principal:
  - `audiovisuales`
  - usa `tipo_audiovisual_id`

- Tablas hijas:
  - `audiovisual_ediciones`
  - `audiovisual_grabaciones`
  - `audiovisual_grabacion_ediciones`

- Tablas auxiliares:
  - `audiovisual_requerimientos`
  - `audiovisual_redes_sociales`
  - `audiovisual_mensajes`
  - `audiovisual_movimientos`

## Modelos ya presentes

- `app/Models/Audiovisual.php`
- `app/Models/TipoAudiovisual.php`
- `app/Models/AudiovisualEdicion.php`
- `app/Models/AudiovisualGrabacion.php`
- `app/Models/AudiovisualGrabacionEdicion.php`
- `app/Models/AudiovisualRequerimiento.php`
- `app/Models/AudiovisualRedSocial.php`
- `app/Models/AudiovisualMensaje.php`
- `app/Models/AudiovisualMovimiento.php`

## Vistas presentes

- `resources/views/videografia/audiovisuales/index.blade.php`
- `resources/views/videografia/audiovisuales/edit.blade.php`
- `resources/views/videografia/audiovisuales/planificacion.blade.php`
- `resources/views/videografia/audiovisuales/planificador.blade.php`

Nota:

- mañana conviene revisar cuál de las dos vistas de planificación es la vigente para no mantener duplicidad innecesaria

## Seeders creados o ajustados

- Nuevo:
  - `database/seeders/TipoAudiovisualSeeder.php`
  - tipos cargados:
    - `edicion`
    - `grabacion`
    - `grabacion_edicion`

- Ajustados:
  - `database/seeders/DatabaseSeeder.php`
  - `database/seeders/CatalogoBaseSeeder.php`

## Migraciones nuevas relacionadas con videografía

- `2026_03_23_221632_create_audiovisuales_table.php`
- `2026_03_23_230100_create_tipo_audiovisuales_table.php`
- `2026_03_23_230200_add_tipo_audiovisual_id_to_audiovisuales_table.php`
- `2026_03_23_230300_create_audiovisual_ediciones_table.php`
- `2026_03_23_230400_create_audiovisual_grabaciones_table.php`
- `2026_03_23_230500_create_audiovisual_grabacion_ediciones_table.php`
- `2026_03_23_230600_create_audiovisual_requerimientos_table.php`
- `2026_03_23_230700_create_audiovisual_redes_sociales_table.php`
- `2026_03_23_230800_drop_tipo_producto_id_from_audiovisuales_table.php`
- `2026_03_23_230900_create_audiovisual_mensajes_table.php`
- `2026_03_23_231000_create_audiovisual_movimientos_table.php`

## Advertencias reales al cierre

- El módulo depende de migraciones nuevas; sin migrar fallarán relaciones y formularios.
- Si la base actual todavía tiene `audiovisuales.tipo_producto_id`, la migración nueva debe eliminarlo.
- El formulario ya usa `tipo_audiovisual_id` desde DB.
- No todo está 100% desacoplado de valores fijos:
  - en `AudiovisualController.php` todavía hay catálogos hardcodeados para requerimientos, redes y productos digitales
- El módulo videografía todavía está en working tree y no todo está consolidado en commit.
- No se volvió a correr una suite amplia de tests; cualquier verificación pendiente sigue siendo manual/funcional.

## Archivos clave para retomar mañana

- `app/Http/Controllers/Videografia/AudiovisualController.php`
- `app/Http/Controllers/Videografia/PlanificadorController.php`
- `app/Models/Audiovisual.php`
- `app/Models/TipoAudiovisual.php`
- `resources/views/videografia/audiovisuales/edit.blade.php`
- `resources/views/videografia/audiovisuales/index.blade.php`
- `resources/views/videografia/audiovisuales/planificacion.blade.php`
- `resources/views/videografia/audiovisuales/planificador.blade.php`
- `routes/web.php`
- `database/seeders/TipoAudiovisualSeeder.php`

## Qué falta revisar mañana

- validar funcionalmente el formulario por tipo:
  - `edicion`
  - `grabacion`
  - `grabacion_edicion`
- endurecer reglas de obligatorios según tipo audiovisual
- revisar si `Responsable`, `Videógrafo` y `Editor` deben usar exactamente los mismos usuarios o catálogos distintos
- revisar si el planificador audiovisual debe obligar tipo audiovisual al crear
- verificar visualmente mensajes y movimientos en el lateral
- revisar coherencia de estados audiovisuales con el flujo real del negocio
- decidir si `planificacion.blade.php` y `planificador.blade.php` deben coexistir

## Cómo retomar mañana

Pedir:

`retomemos videografia, revisa docs/estado.md`
