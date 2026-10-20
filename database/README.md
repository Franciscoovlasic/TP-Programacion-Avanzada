# Database migrations

Run `database/migrations/001_add_rol_to_usuario.sql` against the application
database before deploying the role-aware login.

For databases created with the previous exercise schema, run
`database/migrations/003_drop_descripcion_from_ejercicio.sql` after deploying
the updated exercise catalog.

Run `database/migrations/004_create_rutina.sql` to create the routine catalog.
This is a reference migration; execute it manually and never from the application.

Public registration always creates `USUARIO` accounts. To manually promote an
existing account, run this SQL with the real DNI:

```sql
UPDATE usuario
SET rol = 'ADMIN'
WHERE dni = 'REEMPLAZAR_DNI';
```

Verify the result before logging in:

```sql
SELECT id_usuario, dni, rol
FROM usuario
WHERE dni = 'REEMPLAZAR_DNI';
```

## Constructor de rutinas (panel de administración)

Después de tener creadas las tablas `usuario`, `ejercicio` y `rutina`, ejecutar una sola vez:

```sql
SOURCE database/migrations/005_rutinas_dias_ejercicios.sql;
```

La migración agrega los días de cada rutina, los ejercicios asignados a cada día y el descanso en segundos. Las rutinas nuevas se guardan como registros nuevos; las anteriores no se eliminan y quedan disponibles en el historial. Editar una rutina existente actualiza esa rutina.


## Cuenta maestra de administrador (entorno local XAMPP)

La cuenta maestra se crea por separado del registro público. El formulario público asigna siempre el rol `USUARIO` y reserva el DNI/email de administración.

Desde XAMPP Shell, ejecutar en la carpeta del proyecto:

```bat
cd C:\xampp\htdocs\TP-Programacion-Avanzada
php database\crear_admin.php
```

Credenciales iniciales para la instalación local:

- DNI: `99999999`
- Contraseña: `ForjaAdmin2026!`
- Email: `admin@forjagym.local`

El script se ejecuta por consola (no desde el navegador), crea la cuenta si no existe y no convierte automáticamente una cuenta de usuario existente en administradora. Si el DNI/email reservado ya pertenece a una cuenta normal, hay que resolver ese conflicto manualmente antes de ejecutarlo. Una vez creada la cuenta, se puede eliminar `database/crear_admin.php` del servidor si ya no se necesita.
