# Constancia de Residencia

Aplicación web para solicitar constancias de residencia del Consejo Comunal **Las Garras del León**.

## Stack
- PHP 8.3
- MySQL
- HTML + JavaScript (vanilla)

## Variables de entorno requeridas (Railway)
| Variable | Descripción |
|---|---|
| `MYSQLHOST` | Host de la base de datos |
| `MYSQLUSER` | Usuario MySQL |
| `MYSQLPASSWORD` | Contraseña MySQL |
| `MYSQLDATABASE` | Nombre de la base de datos |
| `MYSQLPORT` | Puerto MySQL (3306) |

## Despliegue
1. Conectar repositorio en [railway.app](https://railway.app)
2. Agregar servicio MySQL desde Railway
3. Las variables de entorno se configuran automáticamente
4. Importar `DOCSQL.sql` desde Railway → MySQL → Data
