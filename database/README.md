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
