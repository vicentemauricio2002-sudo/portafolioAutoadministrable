# Documento de Uso de Inteligencia Artificial (IA)

Este documento detalla de manera transparente el uso de herramientas de Inteligencia Artificial como asistentes de desarrollo de software durante el diseño, codificación e implementación del portafolio web dinámico.

---

## 1. Herramientas IA Utilizadas
* **Gemini (Google)**

---

## 2. Registro de Prompts y Respuestas

A continuación se presentan en orden los hitos de consulta, las respuestas obtenidas y las soluciones generadas, reflejando el proceso iterativo donde se descartaron los bloques de código masivos para priorizar la extracción selectiva de fragmentos.

### Hito 1: Optimización del Layout Responsivo (Bootstrap 5)
* **Prompt del Estudiante:** *"Al poner gx-3, section y aside se agrandan hacia los lados y se desalinean del nav y footer. g-3 no da separacion y solo agranda los lados de section y aside desalineadolos de los otros."*
* **Respuesta de la IA:** Explicación técnica sobre cómo las clases de espaciado horizontal (`gx-*`) de Bootstrap añaden paddings negativos a las filas (`.row`) y cómo el uso incorrecto de márgenes rompe el contenedor `.container`. Se sugirió reestructurar el layout usando la cuadrícula nativa con `.row` y `.col-md-*` manteniendo las utilidades de padding (`p-*`) internas para no desalinear el Navbar ni el Footer.
* **Resultado:** Un diseño responsivo limpio que no genera scroll horizontal en pantallas de celulares.

### Hito 2: Conceptualización del Flujo de Datos
* **Prompt del Estudiante:** *"Ahora que veo otra vez el formulario de contacto al campo de correo seria de quien lo manda o a quien esta dirijido? creo que debería ser el correo de quien lo mande y todos estan diseñados para llegar a un correo que determinado, o no?"*
* **Respuesta de la IA:** Confirmación del flujo de la arquitectura web. Se explicó la diferencia entre el correo del visitante (remitente conceptual) y el correo del administrador (destinatario real), introduciendo el concepto técnico de cabeceras de respuesta para automatizar la comunicación.
* **Resultado:** Claridad absoluta sobre cómo estructurar el procesamiento de las variables globales de PHP.

### Hito 3: Migración a Estándares de Seguridad (PHPMailer)
* **Prompt del Estudiante:** *"anteriormente me diste dos opciones, una con mail() que puede terminar en spam, y otro que era más segura era con PHPMailer"*
* **Respuesta de la IA:** *"¡Exacto! El nombre de esa herramienta es PHPMailer. Es la librería estándar de la industria... soluciona esto porque se conecta a un servidor de correo real mediante un protocolo llamado SMTP... Al poner tu usuario y contraseña en el código, el correo sale 100% auténtico"*.
* **Resultado:** Decisión de escalar el portafolio a una arquitectura segura utilizando Programación Orientada a Objetos (POO) para agregar la funcionalidad del formulario de contacto.

### Hito 4: Destacado - Adaptación y Extracción Selectiva del Formulario HTML
* **Prompt del Estudiante:** *"primero ademas de lo de las validaciones le falta algo a mi formulario en el index? <section ...> ... </section> y como otra pregunta el correo que me va a llegar va a ser enviado desde el correo que la persona ingrese como suyo en el campo correo?"*
* **Respuesta de la IA:** Identificación de omisiones críticas en el código entregado por el estudiante (falta del atributo `name="btn_contacto"` en el botón de envío y ajuste de rutas). Además, se entregó un script de backend estructurado por completo.
* **Resultado e Intervención:** En lugar de copiar el script entero propuesto (lo cual habría alterado drásticamente la estructura visual ya construida), analicé el bloque y extraje únicamente los fragmentos necesarios: la cabecera `addReplyTo` y los métodos SMTP obligatorios, adaptándolos minuciosamente al diseño Bootstrap original del portafolio.

### Hito 5: Gestión de Dependencias en la Terminal
* **Prompt del Estudiante:** *"no habia algo que ponía en la terminal para que la libreria se instalara  y organizara sola para no realizar el proceso manualmente?"*
* **Respuesta de la IA:** Introducción a **Composer** como gestor de dependencias de PHP. Explicación del comando `composer require phpmailer/phpmailer` y cómo este genera la carpeta `vendor/` junto con el cargador automático `autoload.php`.
* **Resultado:** Adopción de flujos de trabajo modernos de desarrollo de software e instalación exitosa de la librería en el entorno local.

### Hito 6: Control de Desbordamiento en Tablas Responsivas
* **Prompt del Estudiante:** *"Las barras de progreso de las tecnologías se ven muy aplastadas en las pantallas de celulares."*
* **Respuesta de la IA:** Sugerencia de envolver las estructuras de datos en contenedores con la clase `.table-responsive` de Bootstrap 5 y ajustar las propiedades de ancho mínimo (`min-width`) mediante clases de utilidad.
* **Resultado e Intervención:** La respuesta me daba un ejemplo con una tabla completamente nueva. En vez de reemplazar mi diseño, busqué únicamente la propiedad del contenedor div responsivo y la inyecté con cuidado alrededor de mi tabla dinámica generada con PHP, solucionando el problema visual en móviles de inmediato.

### Hito 7: Migración de URL de Imagen a Subida de Archivos Locales (Uploads)
* **Prompt del Estudiante:** *"El campo de imagen en dashboard_proyectos.php quiero cambiarlo a que en vez ser por url sea por adjuntación de una imagen. asi como en el dashboard_admin.php que tiene para poder subir una imagen de perfil. esto es posible o tendria que cambiar las configuraciones de mi base de datos?"*
* **Respuesta de la IA:** Confirmación de que la base de datos no requería modificaciones (manteniendo el campo `VARCHAR`). Se entregó un script completo que integraba el atributo `enctype="multipart/form-data"`, la validación de la variable global `$_FILES`, la función `move_uploaded_file()` con nombres únicos basados en `time()`, y el uso de las clases `.ratio-16x9` con `.object-fit-cover` de Bootstrap para forzar la visualización rectangular.
* **Resultado e Intervención:** La IA me devolvió un archivo completamente nuevo que reemplazaba todo el documento. Fiel a mi metodología de trabajo, rechacé el reemplazo automático para no perder mis estilos personalizados. En su lugar, abrí el código y realicé una **extracción selectiva**: identifiqué el bloque exacto del procesamiento de archivos, la condicional que controlaba si el usuario subía o no una nueva foto al editar, y las clases de proporción de Bootstrap. Incorporé estos fragmentos quirúrgicamente en mi estructura existente, logrando la carga de archivos rectangular sin alterar un solo píxel de mi diseño original.

---

## 3. Ajustes Realizados (Intervención del Estudiante)

El código propuesto por la IA nunca fue incorporado al proyecto de manera automatizada, pasiva o a ciegas. Cada sugerencia pasó por un filtro riguroso de análisis crítico, auditoría de líneas y adaptaciones manuales en mi entorno local. Mi intervención como desarrollador se centró en resolver conflictos de compatibilidad visual y estructurar la comunicación lógica entre el frontend y el backend mediante las siguientes acciones clave:

* **Sincronización Manual de Atributos e Inputs:** Al auditar las propuestas conceptuales de la IA para el formulario de contacto en `index.php`, detecté que el código genérico omitía la correspondencia exacta con las variables que yo ya había definido en mi arquitectura. Modifiqué manualmente las etiquetas HTML para asegurar la concordancia estricta del atributo `name="correo"` con la variable global `$_POST['correo']` en PHP. Asimismo, inyecté manualmente el atributo de control `name="btn_contacto"` en el botón de submit, permitiendo que la condicional `isset()` del servidor gatillara el procesamiento de forma correcta.

* **Ruteo de Navegación e Integración de Scripts Embebidos:** La IA sugirió scripts de procesamiento independientes que recargaban la página web perdiendo el foco de la interacción del usuario. Para mitigar esto, modifiqué el atributo `action` de mi formulario original redirigiendo el flujo de datos hacia el archivo especializado `enviar_contacto.php`. En este último, adapté las respuestas del servidor para que, en lugar de mostrar textos planos en blanco, devolvieran al visitante a la sección exacta de la interfaz mediante la inyección manual de scripts de JavaScript (`window.location.href='index.php#contacto'`) acompañados de alertas dinámicas en el navegador.

* **Ensamblaje Quirúrgico del Sistema de Archivos para Proyectos:** El hito de migración de URL a subida de archivos binarios en `dashboard_proyectos.php` representó el mayor esfuerzo de intervención manual. El asistente generó un archivo completo que destruía mis vistas modulares y las configuraciones específicas del Navbar de administración. Rechacé dicho bloque y procedí a una reestructuración quirúrgica: aislé el atributo `enctype="multipart/form-data"` en mi formulario existente, extraje manualmente la condicional lógica encargada de verificar si se estaba editando un registro con o sin reemplazo de imagen, y reorganicé la ruta de destino utilizando el método de renombrado único basado en `time()`. Esto garantizó la carga local en la carpeta `uploads/` protegiendo mi diseño intacto.

* **Adaptación de Proporciones y Contenedores Responsivos en Bootstrap:** En múltiples ocasiones, las soluciones de maquetación de la IA consistían en cambiar componentes enteros por tablas o grillas estándar. Para solucionar el desbordamiento de las barras de progreso y la distorsión de las imágenes de portada de los proyectos, ignoré las estructuras masivas propuestas y me dediqué a extraer selectivamente utilidades de Bootstrap 5. Envolví manualmente las tablas dinámicas dentro de contenedores personalizados con la clase `.table-responsive` e inyecté las clases de proporción `.ratio-16x9` y `.object-fit-cover` dentro de mis bucles dinámicos de PHP, forzando la simetría visual y la adaptabilidad móvil sin alterar mi maquetación base.

---

## 5. Reflexión Crítica

Trabajar con Inteligencia Artificial en este proyecto me demostró que estas herramientas son de gran ayuda, pero solo si uno mantiene el control de lo que hace. La principal ventaja de la IA fue funcionar como un tutor disponible a toda hora; me ayudó mucho a acelerar el aprendizaje y a entender más rápido cosas nuevas como el uso de Composer, el funcionamiento de los correos por SMTP, cómo arreglar detalles visuales con Bootstrap 5 y entender el código PHP para enlazar mi página web con una base de datos. Sin embargo, me di cuenta de una gran limitación: la IA casi siempre entrega códigos con falta de congruencia, muy genéricos y con cambios innecesarios. Si yo hubiera copiado y pegado esas respuestas completas, habría arruinado todo el diseño personalizado que tanto trabajo me costó armar en mi portafolio y, lo peor de todo, no habría aprendido nada. Para solucionar esto, decidí trabajar usando una "extracción selectiva": en lugar de usar los archivos enteros que me daba la IA, me dediqué a revisar las respuestas con lupa para sacar únicamente la línea exacta o la función específica que me servía, como una condicional en PHP o una clase de proporción en Bootstrap. Gracias a esto, el aprendizaje fue real; logré entender cómo viajan los datos en un formulario, cómo conectar una librería de forma segura y cómo programar en PHP de una manera mucho más limpia. Al final, me queda claro que la IA es un excelente copiloto para avanzar más rápido, pero el verdadero éxito del proyecto depende de que uno mismo sea capaz de revisar, entender y adaptar cada línea de código a lo que realmente necesita.