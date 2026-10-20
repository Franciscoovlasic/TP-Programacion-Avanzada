// confirmar.js
// Pide confirmación antes de enviar un formulario marcado con data-confirmar="mensaje".
// Previene borrados accidentales. Si el usuario cancela, el formulario no se envía.

document.addEventListener('submit', function (evento) {
    'use strict';

    var formulario = evento.target;
    if (!(formulario instanceof HTMLFormElement) || !formulario.hasAttribute('data-confirmar')) {
        return;
    }

    if (!window.confirm(formulario.getAttribute('data-confirmar'))) {
        evento.preventDefault();
    }
});
