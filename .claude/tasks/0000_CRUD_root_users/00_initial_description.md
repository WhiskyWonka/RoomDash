Se necesita en CRUD de los usuarios root.
Estos usuarios son los unicos que pueden acceder al crud de tenants

Al crearlos deben tener los siguientes campos:
- username required
- first_name required
- last_name required
- email required
- avatar

Al registrarlos deben recibir un mail para confirmar su email y registro. sino no estaran habilitados a iniciar sesion
Una vez confirmado el registro (email) deberan habilitar el 2FA

Estos usuarios pueden administrar tanto tenants como el CRUD de usuarios root

todas las acciones de estos usuarios deben guardarse en unta tabla de logs la cual debe poder ser consultada por los mismos usuarios root, pero nunca modificada
