// validacion.js
// Valida que DNI y contraseña no estén vacíos.
// Recién ahí se habilita el botón de envío del formulario.

document.addEventListener('DOMContentLoaded', function () {

    const inputDni = document.getElementById('dni');
    const inputContrasena = document.getElementById('contrasena');
    const btnIngresar = document.getElementById('btnIngresar');
    const msgValidacion = document.getElementById('msgValidacion');

    function validarCampos() {
        const dniCompleto = inputDni.value.trim() !== '';
        const contrasenaCompleta = inputContrasena.value !== '';

        if (dniCompleto && contrasenaCompleta) {
            btnIngresar.disabled = false;
            msgValidacion.textContent = '';
        } else {
            btnIngresar.disabled = true;
            msgValidacion.textContent = 'Completá DNI y contraseña para continuar.';
        }
    }

    // Se valida en cada tecla presionada en ambos campos
    inputDni.addEventListener('input', validarCampos);
    inputContrasena.addEventListener('input', validarCampos);

    // Estado inicial (por si el navegador autocompleta los campos)
    validarCampos();
});
