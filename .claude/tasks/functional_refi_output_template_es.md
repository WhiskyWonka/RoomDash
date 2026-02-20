<!-- El agente genera esto automáticamente -->

# [TASK-XXX] Nombre refinado de la tarea

## 📋 Resumen Ejecutivo
> Una línea explicando qué hace esta tarea

**Estado:** En refinamiento / Refinada / Bloqueada
**Estimación:** X puntos
**Prioridad:** Alta / Media / Baja
**Módulos afectados:** [lista]

---

## 🎯 Objetivo
Descripción clara y sin ambigüedades del objetivo.

---

## 👤 Actores
- **Actor 1**: Rol y acción principal
- **Actor 2**: Rol y acción principal

---

## ❓ Preguntas Abiertas
<!-- El agente las genera, vos las respondés -->
| # | Pregunta | Categoría | Impacto | Estado |
|---|----------|-----------|---------|--------|
| 1 | ¿...? | Funcional | Alto | Pendiente |
| 2 | ¿...? | Negocio | Medio | Respondida: ... |

---

## ✅ Criterios de Aceptación

### Escenario 1: [Nombre del escenario principal]
```gherkin
Given [contexto inicial]
When [el actor realiza la acción]
Then [resultado esperado]
And [resultado adicional]
```

### Escenario 2: [Caso de error]
```gherkin
Given [contexto]
When [acción inválida]
Then [mensaje de error esperado]
```

---

## 🔌 Especificación Técnica

### Endpoints

#### POST /api/v1/recurso
**Descripción:** Crear nuevo recurso

**Request:**
```json
{
  "campo": "tipo - descripción - requerido/opcional",
  "otro_campo": "tipo - descripción"
}
```

**Response 201:**
```json
{
  "data": {
    "id": "integer",
    "campo": "string"
  }
}
```

**Errores:**
| Código | Causa |
|--------|-------|
| 422 | Validación fallida |
| 409 | Conflicto (duplicado) |
| 403 | Sin permisos |

---

### Base de Datos

#### Nueva tabla: nombre_tabla
| Columna | Tipo | Nullable | Descripción |
|---------|------|----------|-------------|
| id | bigint PK | No | Auto-increment |
| campo | varchar(255) | No | Descripción |
| tenant_id | bigint FK | No | Multi-tenant |
| created_at | timestamp | No | |

**Índices:**
- `idx_tabla_campo` en (tenant_id, campo)

#### Modificaciones a tabla existente:
- Agregar columna `campo` tipo `varchar(100)` nullable

---

### Reglas de Negocio
1. **RN-001**: [Nombre] - Descripción clara de la regla
2. **RN-002**: [Nombre] - Descripción clara de la regla

---

### Casos Borde
- ¿Qué pasa si [situación extrema]?
- ¿Qué pasa si [condición inesperada]?

---

## 🧪 Escenarios de Testing
- [ ] Happy path: [descripción]
- [ ] Error de validación: [descripción]
- [ ] Sin permisos: [descripción]
- [ ] [Caso borde]: [descripción]

---

## 📦 Subtareas Técnicas
<!-- El agente propone cómo dividir el trabajo -->

| # | Descripción | Capa | Estimación |
|---|-------------|------|------------|
| 1 | Crear migración tabla X | Infrastructure | 1pt |
| 2 | Crear entidad de dominio | Domain | 1pt |
| 3 | Implementar caso de uso | Application | 2pt |
| 4 | Crear controller y endpoint | Infrastructure | 1pt |
| 5 | Tests unitarios | - | 2pt |

---

## ⚠️ Riesgos e Impacto
- **Impacto en módulo X**: Describir qué puede romperse
- **Riesgo técnico**: Describir incertidumbre
- **Dependencias bloqueantes**: Qué debe estar listo primero
