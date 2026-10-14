# Pendientes del dueño

Cosas que dependen de ti. Se actualiza en cada fase. Cuando resuelvas algo, dímelo y lo marco.

## Acciones tuyas

| # | Desde | Pendiente | Por qué importa |
|---|---|---|---|
| U1 | Fase 1 | Desbloquear la facturación de GitHub Actions en tu cuenta. | Sin eso el CI no corre y los tests en Windows y macOS siguen sin verificar. |
| U2 | Fase 2 | Cambiar la contraseña del admin del mini PC: `docker exec lego2-app-1 php lego user:password admin@lego.local`. | La contraseña generada quedó en el log del contenedor y en el chat. |
| U3 | Limpieza | Decidir qué hacer con `cookies.txt`, que estuvo versionado en el historial de Lego 1. | Contiene una cookie de sesión antigua. Si el repo es público, conviene invalidarla y, si quieres, reescribir el historial. |
| U4 | Fase 1 | Decidir si apagamos Lego 1 en el mini PC (puerto 8080). | Ocupa memoria y ya no se usa para revisar. |
| U5 | Fase 5 | Decidir si `lego-2` pasa a ser la rama principal (fusionar en `main` o cambiar la rama por defecto en GitHub). | `main` sigue teniendo Lego 1; quien entre al repo ve la versión vieja. |
| U6 | Fase 5 | Decidir el presupuesto de tamaño del núcleo. El plan pedía 3.500 líneas de PHP; hoy son unas 5.100 sin comentarios ni líneas vacías. El test de métricas fija 5.300 como techo provisional. | Bajar a 3.500 implica recortar funcionalidad (por ejemplo, la CLI o la pantalla sin JavaScript); subir la meta es aceptar el tamaño actual. |
| U7 | Identidad | Decidir si el nombre "Lego" se mantiene en un proyecto público, por ser marca registrada de The LEGO Group. | Si cambia, conviene antes de publicar la identidad. |
| U8 | Identidad | Generar la portada del README (1280 × 640) y la imagen para redes con la nueva marca. | La entrega de diseño la muestra, pero no hay archivo exportado. |

## Decisiones tomadas por defecto, a confirmar

Las apliqué con mi recomendación para no frenar el avance. Si alguna no te convence, se cambia.

| # | Fase | Decisión aplicada | Alternativa |
|---|---|---|---|
| C1 | 4 | Las pantallas viven en la raíz (`/clients/5`), con una lista de nombres reservados verificada por test. | Prefijo `/app/clients/5`. |
| C2 | 4 | Una sola pantalla "nuevo" abierta por módulo. | Varios borradores a la vez. |
| C3 | 4 | Máximo 10 pantallas abiertas; al pasar el límite se cierra la usada hace más tiempo que no tenga cambios sin guardar. | Otro número o sin límite. |
| C4 | 4 | Si la sesión caduca con un formulario a medio escribir, se redirige al login y se pierden esos datos. | Avisar con un diálogo y guardar un borrador temporal. |
| C5 | 4 | "Atrás" hacia una pantalla ya cerrada la vuelve a abrir. | Saltar esa entrada del historial. |
| C6 | 4 | Los tests de navegador corren con Playwright para Python dentro de un contenedor de test. No entra Node al proyecto ni a la imagen. | Playwright con Node solo en CI. |
| C7 | 4 | Las pantallas abiertas se muestran como pestañas en la cabecera; el menú lateral queda igual. | Mostrarlas bajo cada módulo del menú. |
| C8 | 4 | Cambiar filtros, orden o página no crea entradas en el historial del navegador. | Que sí las cree. |
| C9 | 4 | La tabla y el formulario se activan por atributo (`data-lego="table"`), así un módulo CRUD no escribe JavaScript. | Importación explícita en cada módulo. |
| C10 | 6 | Los asistentes de IA están apagados por defecto (`LEGO_AGENT_ACCESS=off`); en el mini PC también. | Activar `read` en el mini PC para probar. |
| C11 | 6 | Usuarios y Roles no aceptan escrituras de agentes (sin `agentWrites`): un asistente nunca crea usuarios ni cambia permisos. | Permitirlo con confirmación. |
| C12 | 6 | Las descripciones de acciones, que lee la IA, van en español como la interfaz. | Escribirlas en inglés, como el código. |
| C13 | 6 | `Cross-Origin-Opener-Policy` y HSTS solo se envían en HTTPS: en HTTP el navegador las ignora y Chrome registra un error. | Enviarlas siempre. |
| C14 | Identidad | El foco del teclado es un contorno de 2 px en color de acento; los campos además muestran el anillo suave de 3 px del diseño. | Solo el anillo suave de `--accent-soft`, que en claro casi no contrasta. |
| C15 | Identidad | Los booleanos de las tablas se muestran como badge: "Sí" en verde, "No" neutro. | Texto plano. |
| C16 | Identidad | Los iconos del sistema (y Lucide para ampliarlos) quedan para después; el menú sigue sin iconos. | Integrarlos ya como sprite SVG. |
| C17 | Identidad | No se usó el lema de la portada de la entrega ("inspirado en Flutter y Angular"), que describe a Lego 1. | Escribir un lema nuevo para la portada del README. |
