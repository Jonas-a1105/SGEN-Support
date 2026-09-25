# Guía Oficial de Arquitectura, Patrones de Diseño y Estándares de Calidad
## SGEN-Support (Laravel 12 + Inertia.js + Vue 3)

> **Documento Normativo**: Esta guía establece las reglas técnicas mandatorias, convenciones de diseño y límites modulares del sistema. Todo desarrollador y agente debe adherirse a estos estándares para evitar la degradación de la base de código, violaciones de SRP y fugas de contexto.

---

## 1. Principios Arquitectónicos Fundamentales

SGEN-Support está estructurado bajo el paradigma de **Monolito Modular** aplicando **Clean Architecture (Puertos y Adaptadores / Arquitectura Hexagonal)**.

### Estructura de Capas por Módulo (`sgen-backend/src/Modules/{Modulo}/`)

```
Modules/{Modulo}/
├── Domain/                  # NÚCLEO PURO: Sin dependencias de frameworks ni BD
│   ├── Models/              # Entidades y Agregados con lógica e invariantes de negocio
│   ├── Enums/               # Tipos de estado, prioridades, roles
│   ├── ValueObjects/        # Sku, Money, Quantity, etc.
│   ├── Exceptions/          # Excepciones de negocio tipadas
│   ├── Ports/               # Interfaces de Repositorios y Servicios (Contratos)
│   └── Services/            # Políticas de dominio (e.g. SlaPolicy)
│
├── Application/             # CASOS DE USO Y ORQUESTACIÓN
│   ├── DTOs/                # Objetos de Transferencia de Datos inmutables (readonly)
│   ├── UseCases/            # Clases ejecutables atómicas (1 caso de uso = 1 clase)
│   ├── Mappers/             # Transformadores de filas BD -> DTOs de presentación
│   └── Ports/               # Contratos específicos de aplicación (e.g. Ensambladores de PDF)
│
└── Infrastructure/          # IMPLEMENTACIÓN TÉCNICA
    ├── Persistence/         # Repositorios Eloquent/DB que implementan los Domain Ports
    └── Presentation/        # Ensambladores específicos (e.g. PdfTicketDataAssembler)
```

### Capa de Adaptadores HTTP (`sgen-backend/app/Infrastructure/{Modulo}/`)
- **`Http/Controllers/`**: Controladores web/API delgados.
- **`Http/Requests/`**: FormRequests de validación y sanitización previa.
- **`Providers/`**: ServiceProviders encargados de registrar la inyección de dependencias (`$this->app->bind(...)`).

---

## 2. Reglas de Oro del Backend (Mandatorias)

### Regla 1: Delimitación de Contextos Acotados (Bounded Contexts)
- **NUNCA** realizar consultas de escritura (`insert`, `update`, `delete`) ni mutaciones sobre tablas pertenecientes a otro módulo.
- **Ejemplo Incorrecto (Anti-patrón)**:
  ```php
  // DENTRO de Modules/Inventory:
  DB::table('equipos')->where('id', $id)->update([...]); // VIOLACIÓN DE LÍMITES
  ```
- **Ejemplo Correcto**:
  - Si el módulo `Inventory` requiere alterar un equipo, debe delegar al `EquipmentRepositoryInterface` o disparar una ruta/evento del módulo `Equipment`.

### Regla 2: Pureza de los Casos de Uso (Application Layer)
- Un caso de uso (`UseCase`) **NUNCA** debe invocar la fachada `DB::table(...)` ni construir SQL directamente.
- Toda interacción con la base de datos debe residir detrás de una interfaz en `Domain/Ports/*Interface.php` e implementarse en `Infrastructure/Persistence/`.
- **Estructura Canónica de un Caso de Uso**:
  ```php
  final readonly class CompleteMaintenanceUseCase
  {
      public function __construct(
          private MaintenanceRepositoryInterface $repository
      ) {}

      public function execute(int $id, CompleteMaintenanceDTO $dto): bool
      {
          // Validar reglas o invariantes de negocio
          return $this->repository->completeMaintenance($id, $dto);
      }
  }
  ```

### Regla 3: Controladores Delgados (Thin Controllers)
- Los controladores solo deben:
  1. Recibir la solicitud y validar mediante `FormRequest`.
  2. Extraer los datos a un `DTO`.
  3. Ejecutar el `UseCase` correspondiente.
  4. Retornar la respuesta (`Inertia::render`, `RedirectResponse` o `JsonResponse`).
- **PROHIBIDO**: Inyectar repositorios directamente en los métodos del controlador para armar la vista (evitar `$repository->getFormOptions()`). Si la vista necesita datos complejos, se orquesta a través de un `UseCase`.

### Regla 4: Segregación de Interfaces en Repositorios (ISP)
- Si un repositorio supera las ~350 líneas o asume múltiples dominios de consulta (CRUD, KPIs, estadísticas complejas, carga de archivos, combos de formulario), debe segregarse:
  - **CRUD / Entidad principal**: `Eloquent{Modulo}Repository`
  - **Métricas y Estadísticas**: `{Modulo}KpiService` o `AnalyticsRepository`
  - **Lookups de Formulario / Catálogos**: `CatalogLookupService`

### Regla 5: Coherencia y Nomenclatura en DTOs
- Todo DTO debe declararse como `final readonly class`.
- Mantener una nomenclatura consistente en los campos de entrada y propiedades. Evitar duplicar flujos como `CreateEquipmentDTO` (inglés) y `RegisterEquipmentDTO` (español); unificar en un solo flujo canónico.

---

## 3. Reglas de Oro del Frontend (Vue 3 + TypeScript)

### Regla 6: Centralización de Tipos TypeScript (`resources/js/types/`)
- **PROHIBIDO** definir interfaces de modelos o entidades repetidas inline dentro de componentes `.vue`.
- Todo módulo debe poseer su archivo canónico en `resources/js/types/`:
  - `types/support.ts`
  - `types/equipment.ts`
  - `types/inventory.d.ts`
  - `types/maintenance.ts`
  - `types/department.ts`
  - `types/employee.ts`
  - `types/user.ts`
  - `types/audit.ts`
- Las propiedades (`props`) y eventos (`emits`) de componentes deben tiparse usando estas interfaces compartidas.

### Regla 7: Límite de Tamaño de Componentes y Descomposición
- Los componentes mayores a **300 líneas** deben evaluarse para descomposición:
  - Si más del **60% del archivo es CSS**, se deben reutilizar variables de diseño globales (`--bg-card`, `--stroke`, `--text`, etc.) y clases atómicas.
  - Componentes de página (`Show.vue`, `Index.vue`) deben actuar como **Contenedores de Orquestación**, delegando la presentación a componentes hijos puros.

### Regla 8: Flujo Unidireccional de Datos (Props Down, Events Up)
- Los componentes hijos no deben disparar mutaciones globales opacas.
- Deben emitir eventos tipados mediante `defineEmits<{ (e: 'evento', payload: T): void }>()`.
- El componente contenedor (`Show.vue`, etc.) es el responsable de invocar `router.put`, `router.post` o gestionar el estado reactivo.

---

## 4. Catálogo de Patrones de Diseño Aplicados

| Patrón | Capa | Propósito en SGEN-Support |
| :--- | :--- | :--- |
| **Ports & Adapters (Hexagonal)** | Dominio / Infraestructura | Desacopla la lógica de negocio de la base de datos (PostgreSQL), permitiendo pruebas unitarias con mocks sin tocar la BD. |
| **Data Transfer Object (DTO)** | Aplicación | Transporta información tipada e inmutable entre el controlador HTTP y los casos de uso, evitando el uso de arrays asociativos crudos. |
| **Data Mapper** | Aplicación / Mappers | Transforma las filas SQL devueltas por PostgreSQL a DTOs listos para el consumo de Inertia/Vue (`toDTO`, `fromRow`). |
| **Policy / Strategy** | Dominio / Services | Encapsula el cálculo dinámico de SLAs laborales y cálculo de tiempos en `SlaPolicy`. |
| **Atomic Movement Recording (Pessimistic Lock)** | Infraestructura | Ejecuta transacciones con `lockForUpdate` para descontar inventario y registrar movimientos de auditoría concurrentes sin condición de carrera. |
| **Container / Presentational Components** | Frontend (Vue 3) | Separa componentes que orquestan lógica y rutas de aquellos que solo renderizan interfaz y emiten interacciones del usuario. |

---

## 5. Matriz de Anti-Patrones: Comparativa Práctica

| Anti-patrón Detectado | Por qué es defectuoso | Solución Canónica SGEN |
| :--- | :--- | :--- |
| **Consultas SQL en UseCases** | Acopla la capa de aplicación al motor de BD; impide mockeo y testabilidad pura. | Inyectar `RepositoryInterface` y llamar a métodos semánticos. |
| **Fuga de Bounded Context** (Ej: Inventario modificando `equipos`) | Rompe la modularidad; duplica lógica de validación e integridad patrimonial. | Usar el caso de uso oficial del módulo dueño (`Equipment`). |
| **Controlador inyectando Repositorio directamente** | Viola la arquitectura en capas; salta validaciones y políticas de la aplicación. | Inyectar únicamente `UseCase` correspondiente. |
| **Tipos TypeScript inline en cada componente** | Conduce a divergencia de tipos, propiedades rotas en refactors y código repetido. | Centralizar contratos en `@/types/{modulo}.ts`. |
| **DTOs con nombres ambiguos y mezclados** | Dificulta el mantenimiento y crea múltiples endpoints para la misma acción. | Un solo DTO canónico por acción (`CreateEquipmentDTO`). |

---

*Esta guía rige el desarrollo y mantenimiento continuo de SGEN-Support.*
