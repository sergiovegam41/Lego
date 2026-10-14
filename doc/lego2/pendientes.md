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
