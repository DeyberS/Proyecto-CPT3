// salida_medicamento.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSalidaMedicamento');
    const itemsTableBody = document.querySelector('#itemsSalidaTable tbody');
    const addItemBtn = document.getElementById('addItemSalida');

    // Función para obtener el inventario del localStorage
    function getInventario() {
        return JSON.parse(localStorage.getItem('inventarioFarmacia')) || [];
    }

    // Función para guardar el inventario actualizado en localStorage
    function saveInventario(inventario) {
        localStorage.setItem('inventarioFarmacia', JSON.stringify(inventario));
    }

    // Función para obtener el historial de movimientos
    function getMovimientosHistorico() {
        return JSON.parse(localStorage.getItem('movimientosFarmaciaHistorico')) || [];
    }

    function saveMovimientosHistorico(historico) {
        localStorage.setItem('movimientosFarmaciaHistorico', JSON.stringify(historico));
    }

    // Función para crear las opciones del select de medicamentos disponibles
    function createMedicamentoOptions(inventario) {
        let options = '<option value="">Seleccione un medicamento</option>';
        inventario.forEach(item => {
            if (item.cantidad_disponible > 0) {
                options += `<option value="${item.id}">${item.nombre_medicamento} - ${item.presentacion} (Lote: ${item.numero_lote}) - Disponible: ${item.cantidad_disponible}</option>`;
            }
        });
        return options;
    }

    // Función para añadir una fila de ítem de salida
    function addSalidaItemRow() {
        const inventario = getInventario();
        const options = createMedicamentoOptions(inventario);

        const newRow = itemsTableBody.insertRow();
        newRow.innerHTML = `
            <td>
                <select class="medicamento_salida_select" required>
                    ${options}
                </select>
            </td>
            <td><span class="cantidad_disponible_span">0</span></td>
            <td><input type="number" class="cantidad_solicitada_item" min="1" required></td>
            <td><button type="button" class="delete-item-btn action-buttons" style="background-color: #dc3545;">X</button></td>
        `;

        const medicamentoSelect = newRow.querySelector('.medicamento_salida_select');
        const cantidadDisponibleSpan = newRow.querySelector('.cantidad_disponible_span');
        const cantidadSolicitadaInput = newRow.querySelector('.cantidad_solicitada_item');
        const deleteBtn = newRow.querySelector('.delete-item-btn');

        medicamentoSelect.addEventListener('change', function() {
            const selectedItemId = this.value;
            const selectedItem = inventario.find(item => item.id == selectedItemId); // Usar == para comparar number con string
            if (selectedItem) {
                cantidadDisponibleSpan.textContent = selectedItem.cantidad_disponible;
                cantidadSolicitadaInput.max = selectedItem.cantidad_disponible; // Establecer max para validación HTML
            } else {
                cantidadDisponibleSpan.textContent = '0';
                cantidadSolicitadaInput.max = '0';
            }
        });

        deleteBtn.addEventListener('click', () => {
            newRow.remove();
        });
    }

    // Añadir la primera fila al cargar la página
    addSalidaItemRow();

    // Evento para añadir nuevas filas de ítems
    addItemBtn.addEventListener('click', () => addSalidaItemRow());

    // Función para limpiar estilos de error
    function limpiarEstilosError() {
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }

    // Función para mostrar mensaje de error
    function mostrarError(element, message) {
        element.classList.add('input-error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        // Insertar el mensaje de error después del input o select
        if (element.nextSibling) {
            element.parentNode.insertBefore(errorDiv, element.nextSibling);
        } else {
            element.parentNode.appendChild(errorDiv);
        }
    }


    // Manejo del envío del formulario de salida
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        limpiarEstilosError(); // Limpiar errores antes de revalidar

        let isValid = true;

        // Validar campos principales de la operación
        const fechaSalidaInput = document.getElementById('fecha_salida');
        const responsableSalidaInput = document.getElementById('responsable_salida');
        const beneficiarioInput = document.getElementById('beneficiario');

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0); // Normalizar a inicio del día

        if (!fechaSalidaInput.value) {
            mostrarError(fechaSalidaInput, 'Fecha de Salida es obligatoria.');
            isValid = false;
        } else {
            const fechaSalida = new Date(fechaSalidaInput.value);
            fechaSalida.setHours(0, 0, 0, 0);
            if (fechaSalida > hoy) {
                mostrarError(fechaSalidaInput, 'La Fecha de Salida no puede ser futura.');
                isValid = false;
            }
        }

        if (!responsableSalidaInput.value.trim()) {
            mostrarError(responsableSalidaInput, 'Responsable es obligatorio.');
            isValid = false;
        }

        if (!beneficiarioInput.value.trim()) {
            mostrarError(beneficiarioInput, 'Beneficiario/Paciente es obligatorio.');
            isValid = false;
        }

        // Validar ítems de medicamentos
        const itemsDispensados = [];
        const itemRows = itemsTableBody.querySelectorAll('tr');
        if (itemRows.length === 0) {
            alert('Debe añadir al menos un medicamento a la salida.');
            isValid = false;
        }

        let inventarioActual = getInventario(); // Obtener una copia mutable del inventario

        itemRows.forEach((row, index) => {
            const medicamentoSelect = row.querySelector('.medicamento_salida_select');
            const cantidadSolicitadaInput = row.querySelector('.cantidad_solicitada_item');

            if (!medicamentoSelect.value) {
                mostrarError(medicamentoSelect, `Ítem ${index + 1}: Seleccione un medicamento.`);
                isValid = false;
                return; // Saltar a la siguiente iteración
            }

            const selectedItemId = parseInt(medicamentoSelect.value);
            const cantidadSolicitada = parseFloat(cantidadSolicitadaInput.value);

            if (isNaN(cantidadSolicitada) || cantidadSolicitada <= 0) {
                mostrarError(cantidadSolicitadaInput, `Ítem ${index + 1}: Cantidad solicitada debe ser un número positivo.`);
                isValid = false;
                return;
            }

            const inventarioItemIndex = inventarioActual.findIndex(item => item.id === selectedItemId);

            if (inventarioItemIndex === -1) {
                mostrarError(medicamentoSelect, `Ítem ${index + 1}: Medicamento seleccionado no encontrado en inventario.`);
                isValid = false;
                return;
            }

            const itemEnInventario = inventarioActual[inventarioItemIndex];

            if (cantidadSolicitada > itemEnInventario.cantidad_disponible) {
                mostrarError(cantidadSolicitadaInput, `Ítem ${index + 1}: Cantidad solicitada (${cantidadSolicitada}) excede la disponible (${itemEnInventario.cantidad_disponible}).`);
                isValid = false;
                return;
            }

            // Si todo es válido para este ítem, preparar para la actualización
            itemsDispensados.push({
                id_inventario: itemEnInventario.id, // Para referenciar el ítem de inventario
                nombre_medicamento: itemEnInventario.nombre_medicamento,
                presentacion: itemEnInventario.presentacion,
                unidad_medida: itemEnInventario.unidad_medida,
                numero_lote: itemEnInventario.numero_lote,
                fecha_vencimiento: itemEnInventario.fecha_vencimiento,
                cantidad_solicitada: cantidadSolicitada
            });
        });

        if (!isValid) {
            alert('Por favor, corrija los errores en el formulario.');
            return;
        }

        // Si todas las validaciones pasan, proceder a guardar y actualizar inventario
        itemsDispensados.forEach(itemDisp => {
            const invIndex = inventarioActual.findIndex(inv => inv.id === itemDisp.id_inventario);
            if (invIndex > -1) {
                inventarioActual[invIndex].cantidad_disponible -= itemDisp.cantidad_solicitada;
            }
        });

        saveInventario(inventarioActual);

        // Guardar la operación de salida en el historial
        const nuevaSalida = {
            id: Date.now(), // ID único para la operación de salida
            tipo_operacion: 'salida',
            fecha_operacion: fechaSalidaInput.value,
            responsable: responsableSalidaInput.value.trim(),
            beneficiario: beneficiarioInput.value.trim(),
            num_receta_orden: document.getElementById('num_receta_orden').value.trim(),
            motivo_salida: document.getElementById('motivo_salida').value.trim(),
            items: itemsDispensados
        };

        let movimientosHistorico = getMovimientosHistorico();
        movimientosHistorico.push(nuevaSalida);
        saveMovimientosHistorico(movimientosHistorico);

        alert('Salida de medicamento registrada y inventario actualizado exitosamente.');
        form.reset();
        // Limpiar filas de ítems y añadir una vacía (con opciones actualizadas)
        itemsTableBody.innerHTML = '';
        addSalidaItemRow(); // Esto recargará las opciones con las nuevas cantidades
    });

    // Resetear el formulario también limpia las filas de ítems
    form.addEventListener('reset', function() {
        setTimeout(() => {
            itemsTableBody.innerHTML = '';
            addSalidaItemRow();
            limpiarEstilosError();
        }, 50);
    });
});