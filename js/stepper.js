// stepper.js
// Reemplaza las flechas nativas de los <input type="number" data-stepper>
// por botones "−" y "+" acordes al resto del sitio.
// Mejora progresiva: sin JavaScript el input sigue funcionando con sus flechas nativas.
// El teclado sigue igual: flechas ↑ / ↓ dentro del input, y se puede escribir el valor.

(function () {
    'use strict';

    var REPETICION_INICIAL_MS = 400;
    var REPETICION_MS = 70;

    function nombreCampo(input) {
        var etiqueta = input.id ? document.querySelector('label[for="' + input.id + '"]') : null;
        var texto = etiqueta ? etiqueta.textContent.replace(/\s*\(.*?\)\s*/g, '').trim() : '';
        return texto.toLowerCase();
    }

    function crearBoton(simbolo, accion, nombre) {
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn-secundario btn-stepper';
        boton.textContent = simbolo;
        boton.setAttribute('aria-label', accion + (nombre ? ' ' + nombre : ''));
        return boton;
    }

    function mejorar(input) {
        if (input.dataset.stepperListo) {
            return;
        }
        input.dataset.stepperListo = 'true';

        var nombre = nombreCampo(input);
        var envoltorio = document.createElement('div');
        envoltorio.className = 'number-stepper';

        var botones = document.createElement('div');
        botones.className = 'stepper-botones';
        var menos = crearBoton('−', 'Disminuir', nombre);
        var mas = crearBoton('+', 'Aumentar', nombre);
        botones.appendChild(menos);
        botones.appendChild(mas);

        input.parentNode.insertBefore(envoltorio, input);
        envoltorio.appendChild(input);
        envoltorio.appendChild(botones);

        function actualizarLimites() {
            var valor = parseFloat(input.value);
            var min = parseFloat(input.min);
            var max = parseFloat(input.max);
            menos.disabled = input.disabled || (!isNaN(valor) && !isNaN(min) && valor <= min);
            mas.disabled = input.disabled || (!isNaN(valor) && !isNaN(max) && valor >= max);
        }

        function paso(direccion) {
            try {
                if (direccion > 0) {
                    input.stepUp();
                } else {
                    input.stepDown();
                }
            } catch (error) {
                return;
            }
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            actualizarLimites();
        }

        // Mantener presionado repite el paso, como las flechas nativas
        function enlazar(boton, direccion) {
            var espera = null;
            var repeticion = null;

            function detener() {
                clearTimeout(espera);
                clearInterval(repeticion);
            }

            boton.addEventListener('pointerdown', function (evento) {
                if (boton.disabled || evento.button > 0) {
                    return;
                }
                paso(direccion);
                espera = setTimeout(function () {
                    repeticion = setInterval(function () {
                        if (boton.disabled) {
                            detener();
                            return;
                        }
                        paso(direccion);
                    }, REPETICION_MS);
                }, REPETICION_INICIAL_MS);
            });
            ['pointerup', 'pointerleave', 'pointercancel', 'blur'].forEach(function (nombreEvento) {
                boton.addEventListener(nombreEvento, detener);
            });

            // Activación con teclado (Enter / Espacio): el navegador dispara "click" sin pointerdown
            boton.addEventListener('click', function (evento) {
                if (evento.detail === 0) {
                    paso(direccion);
                }
            });
        }

        enlazar(menos, -1);
        enlazar(mas, 1);
        input.addEventListener('input', actualizarLimites);
        input.addEventListener('change', actualizarLimites);
        actualizarLimites();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="number"][data-stepper]').forEach(mejorar);
    });
})();
