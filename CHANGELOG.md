# Changelog

## 2.1.2

- **Se audita con `larapack-generator` 9.** La dependencia de desarrollo
  pedía `^7.0` y pasa a `^9.0`, la línea base del ecosistema. Para quien
  instala el paquete no cambia nada: `require-dev` no viaja a la aplicación.
  Aquí el generador sólo lo usa la CI para `larapack:audit`, que ahora compara
  contra la línea base de hoy y no contra la que traía la 7.10.3.

  El paquete no usa ninguna clase del generador, y el `composer.json` del
  generador es el mismo de la 7.10.3 a la 9.1.0: el resto de lo que se resuelve
  no cambia. Se fija `^9.0` en vez de ampliar a `^7.0 || ^8.0 || ^9.0` porque en
  desarrollo no hay consumidor al que dejarle la 7, y el rango ancho sólo
  serviría para que la CI resolviera un generador viejo.

## 2.1.1

- **Font Awesome y Vue se cargan con `integrity`**, como Bootstrap y jQuery. La
  interfaz edita los secretos de la aplicación: un archivo alterado en el CDN no
  se ejecuta. Los hashes se calcularon sobre los archivos que sirven hoy cdnjs y
  jsDelivr.

## 2.1.0

Seguridad y correcciones para activar la interfaz en una aplicación Laravel 13
nueva.

- **La interfaz exige autenticación.** El valor por defecto de
  `env-editor.route.middleware` pasa de `['web']` a `['web', 'auth']`: con
  `env-editor.route.enable` activado, cualquiera podía leer y reescribir el
  `.env`. La aplicación debe sumar su propia comprobación de administrador, por
  ejemplo `['web', 'auth', 'admin']`.
- **Opción nueva `env-editor.route.gate`.** Si nombra una habilidad, el
  controlador la exige en cada acción: un invitado recibe 401 (o la redirección
  al login) y un usuario sin la habilidad, 403.
- **Los nombres de copia que salen del directorio de copias se rechazan.**
  `restore-backup`, `destroy-backup` y `download` aceptaban `..\archivo` en
  Windows, y la fachada `../archivo`. Mensaje nuevo `exceptions.invalidFileName`.
- **`restoreBackup` y `destroyBackup` sin nombre** responden 400 con mensaje en
  lugar de un 500: el parámetro `{filename?}` es opcional y ahora vale `''`.
- **Los errores del editor llegan como JSON.** En peticiones JSON, `EnvException`
  se renderiza como 400 con `message` y `success: false`, que es lo que muestra
  la interfaz.
- **`optimize` solo se programa al guardar si la configuración está cacheada.**
  Antes se programaba siempre y, sin caché, la activaba: los cambios siguientes
  al `.env` dejaban de verse. Con la cola `sync` ocurría en la misma petición.
- **La regla de subida pierde la coma final** (`mimes:txt,text`).
- **La interfaz carga sus recursos.** Font Awesome Free 5.15.4 desde cdnjs en
  lugar de Font Awesome Pro 5.10, que solo sirve a dominios con kit, y la build
  de producción de Vue 2.7.16 en lugar de la de desarrollo de 2.5.17. Esos dos
  recursos no llevan `integrity`.
- **`minimum-stability` pasa a `stable`.**
- **Tests.** `tests/Feature` y `tests/Unit` entran en `phpunit.xml.dist`. Los de
  la interfaz trabajan sobre una copia temporal del `.env`.

### Contrato

- Cambia el valor por defecto de `env-editor.route.middleware`. Una configuración
  publicada con una versión anterior conserva `['web']` y hay que añadir `auth` a
  mano. Quien dependiera del acceso sin autenticar tiene que declararlo.
- Se añade `env-editor.route.gate`, `null` por defecto.
- No cambian las URIs, los nombres de ruta, el resto de claves de configuración,
  los tags de publicación ni la fachada.
