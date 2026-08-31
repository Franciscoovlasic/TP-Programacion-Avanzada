// validacion.js
// Valida que usuario y contraseña no estén vacíos.
// Recién ahí se habilita el botón de envío del formulario.

document.addEventListener('DOMContentLoaded', function () {

    const inputUsuario = document.getElementById('usuario');
    const inputContrasena = document.getElementById('contrasena');
    const btnIngresar = document.getElementById('btnIngresar');
    const msgValidacion = document.getElementById('msgValidacion');

    function validarCampos() {
        const usuarioCompleto = inputUsuario.value.trim() !== '';
        const contrasenaCompleta = inputContrasena.value.trim() !== '';

        if (usuarioCompleto && contrasenaCompleta) {
            btnIngresar.disabled = false;
            msgValidacion.textContent = '';
        } else {
            btnIngresar.disabled = true;
            msgValidacion.textContent = 'Complete usuario y contraseña para continuar.';
        }
    }

    // Se valida en cada tecla presionada en ambos campos
    inputUsuario.addEventListener('input', validarCampos);
    inputContrasena.addEventListener('input', validarCampos);

    // Estado inicial (por si el navegador autocompleta los campos)
    validarCampos();
});
