// entrada_medicamento.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEntradaMedicamento');
    const itemsTableBody = document.querySelector('#itemsEntradaTable tbody');
    const addItemBtn = document.getElementById('addItemEntrada');

    // Función para obtener y guardar el inventario en localStorage
    function getInventario() {
        return JSON.parse(localStorage.getItem('inventarioFarmacia')) || [];
    }

    function saveInventario(inventario) {
        localStorage.setItem('inventarioFarmacia', JSON.stringify(inventario));
    }

    // Función para añadir una fila de ítem al formulario
    function addMedicamentoItemRow(item = {}) {
        const newRow = itemsTableBody.insertRow();
        newRow.innerHTML = `
            <td><input type="text" class="nombre_medicamento_item" value="${item.nombre_medicamento || ''}" required></td>
            <td>
                <select class="presentacion_item">
                    <option value="">Seleccione</option>
                    <option value="GOTAS" ${item.presentacion === 'GOTAS' ? 'selected' : ''}>GOTAS</option>
                    <option value="TABLETAS" ${item.presentacion === 'TABLETAS' ? 'selected' : ''}>TABLETAS</option>
                    <option value="COMPRIMIDO" ${item.presentacion === 'COMPRIMIDO' ? 'selected' : ''}>COMPRIMIDO</option>
                    <option value="SUSPENSION" ${item.presentacion === 'SUSPENSION' ? 'selected' : ''}>SUSPENSION</option>
                    <option value="CREMA" ${item.presentacion === 'CREMA' ? 'selected' : ''}>CREMA</option>
                    <option value="INYECTABLE" ${item.presentacion === 'INYECTABLE' ? 'selected' : ''}>INYECTABLE</option>
                    <option value="CAPSULAS" ${item.presentacion === 'CAPSULAS' ? 'selected' : ''}>CAPSULAS</option>
                    <option value="JBE. PEDIATRICO" ${item.presentacion === 'JBE. PEDIATRICO' ? 'selected' : ''}>JBE. PEDIATRICO</option>
                    <option value="JBE. ADULTO" ${item.presentacion === 'JBE. ADULTO' ? 'selected' : ''}>JBE. ADULTO</option>
                    <option value="SOLUCION ORAL" ${item.presentacion === 'SOLUCION ORAL' ? 'selected' : ''}>SOLUCION ORAL</option>
                    <option value="BLANDAS" ${item.presentacion === 'BLANDAS' ? 'selected' : ''}>BLANDAS</option>
                    <option value="GRAGEAS" ${item.presentacion === 'GRAGEAS' ? 'selected' : ''}>GRAGEAS</option>
                </select>
            </td>
            <td><input type="text" class="unidad_medida_item" value="${item.unidad_medida || ''}"></td>
            <td><input type="text" class="lote_item" value="${item.numero_lote || ''}"></td>
            <td><input type="date" class="vencimiento_item" value="${item.fecha_vencimiento || ''}"></td>
            <td><input type="number" class="cantidad_item" value="${item.cantidad || ''}" min="1" required></td>
            <td><input type="number" step="0.01" class="costo_unitario_item" value="${item.costo_unitario || ''}"></td>
            <td><input type="text" class="costo_total_item" value="${item.costo_total || ''}" readonly></td>
            <td><button type="button" class="delete-item-btn action-buttons" style="background-color: #dc3545;">X</button></td>
        `;

        const cantidadInput = newRow.querySelector('.cantidad_item');
        const costoUnitarioInput = newRow.querySelector('.costo_unitario_item');
        const costoTotalInput = newRow.querySelector('.costo_total_item');
        const deleteBtn = newRow.querySelector('.delete-item-btn');

        const calculateRowTotal = () => {
            const cantidad = parseFloat(cantidadInput.value) || 0;
            const costoUnitario = parseFloat(costoUnitarioInput.value) || 0;
            costoTotalInput.value = (cantidad * costoUnitario).toFixed(2);
        };

        cantidadInput.addEventListener('input', calculateRowTotal);
        costoUnitarioInput.addEventListener('input', calculateRowTotal);

        deleteBtn.addEventListener('click', () => {
            newRow.remove();
        });

        calculateRowTotal(); // Calcular el total inicial si hay datos
    }

    // Añadir la primera fila al cargar la página
    addMedicamentoItemRow();

    // Evento para añadir nuevas filas de ítems
    addItemBtn.addEventListener('click', () => addMedicamentoItemRow());

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
        element.closest('.form-group').appendChild(errorDiv);
    }

    // Manejo del envío del formulario de entrada
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        limpiarEstilosError(); // Limpiar errores antes de revalidar

        let isValid = true;

        // Validar campos principales de la operación
        const fechaEntradaInput = document.getElementById('fecha_entrada');
        const responsableEntradaInput = document.getElementById('responsable_entrada');
        const proveedorInput = document.getElementById('proveedor');

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0); // Normalizar a inicio del día

        if (!fechaEntradaInput.value) {
            mostrarError(fechaEntradaInput, 'Fecha de Entrada es obligatoria.');
            isValid = false;
        } else {
            const fechaEntrada = new Date(fechaEntradaInput.value);
            fechaEntrada.setHours(0, 0, 0, 0);
            if (fechaEntrada > hoy) {
                mostrarError(fechaEntradaInput, 'La Fecha de Entrada no puede ser futura.');
                isValid = false;
            }
        }

        if (!responsableEntradaInput.value.trim()) {
            mostrarError(responsableEntradaInput, 'Responsable es obligatorio.');
            isValid = false;
        }

        if (!proveedorInput.value.trim()) {
            mostrarError(proveedorInput, 'Proveedor es obligatorio.');
            isValid = false;
        }

        // Validar ítems de medicamentos
        const items = [];
        const itemRows = itemsTableBody.querySelectorAll('tr');
        if (itemRows.length === 0) {
            alert('Debe añadir al menos un medicamento a la entrada.');
            isValid = false;
        }

        itemRows.forEach((row, index) => {
            const nombreMedicamentoInput = row.querySelector('.nombre_medicamento_item');
            const presentacionSelect = row.querySelector('.presentacion_item');
            const unidadMedidaInput = row.querySelector('.unidad_medida_item');
            const loteInput = row.querySelector('.lote_item');
            const vencimientoInput = row.querySelector('.vencimiento_item');
            const cantidadInput = row.querySelector('.cantidad_item');
            const costoUnitarioInput = row.querySelector('.costo_unitario_item');

            if (!nombreMedicamentoInput.value.trim()) {
                mostrarError(nombreMedicamentoInput, `Ítem ${index + 1}: Nombre de medicamento es obligatorio.`);
                isValid = false;
            }
            if (!cantidadInput.value || parseFloat(cantidadInput.value) <= 0) {
                mostrarError(cantidadInput, `Ítem ${index + 1}: Cantidad debe ser un número positivo.`);
                isValid = false;
            }
            if (vencimientoInput.value) {
                const fechaVencimiento = new Date(vencimientoInput.value);
                fechaVencimiento.setHours(0, 0, 0, 0);
                if (fechaVencimiento < hoy) {
                    mostrarError(vencimientoInput, `Ítem ${index + 1}: Fecha de vencimiento no puede ser pasada.`);
                    isValid = false;
                }
            }

            items.push({
                nombre_medicamento: nombreMedicamentoInput.value.trim(),
                presentacion: presentacionSelect.value,
                unidad_medida: unidadMedidaInput.value.trim(),
                numero_lote: loteInput.value.trim(),
                fecha_vencimiento: vencimientoInput.value,
                cantidad: parseFloat(cantidadInput.value),
                costo_unitario: parseFloat(costoUnitarioInput.value) || 0,
                costo_total: parseFloat(row.querySelector('.costo_total_item').value) || 0
            });
        });

        if (!isValid) {
            alert('Por favor, corrija los errores en el formulario.');
            return;
        }

        // Guardar la operación de entrada
        const nuevaEntrada = {
            id: Date.now(), // ID único para la operación de entrada
            tipo_operacion: 'entrada',
            fecha_operacion: fechaEntradaInput.value,
            responsable: responsableEntradaInput.value.trim(),
            proveedor: proveedorInput.value.trim(),
            num_factura: document.getElementById('num_factura').value.trim(),
            observaciones: document.getElementById('observaciones_entrada').value.trim(),
            items: items // Guardar los detalles de los medicamentos
        };

        // Guardar en el historial de movimientos (CRUD general)
        let movimientosHistorico = JSON.parse(localStorage.getItem('movimientosFarmaciaHistorico')) || [];
        movimientosHistorico.push(nuevaEntrada);
        localStorage.setItem('movimientosFarmaciaHistorico', JSON.stringify(movimientosHistorico));

        // Actualizar el inventario general de medicamentos
        let inventario = getInventario();
        items.forEach(item => {
            const existingItemIndex = inventario.findIndex(inv =>
                inv.nombre_medicamento === item.nombre_medicamento &&
                inv.presentacion === item.presentacion &&
                inv.numero_lote === item.numero_lote
            );

            if (existingItemIndex > -1) {
                // Actualizar cantidad si ya existe el medicamento con el mismo lote
                inventario[existingItemIndex].cantidad_disponible += item.cantidad;
            } else {
                // Añadir como nuevo ítem si no existe o tiene lote diferente
                inventario.push({
                    id: Date.now() + Math.random(), // ID único para el item de inventario
                    nombre_medicamento: item.nombre_medicamento,
                    presentacion: item.presentacion,
                    unidad_medida: item.unidad_medida,
                    numero_lote: item.numero_lote,
                    fecha_vencimiento: item.fecha_vencimiento,
                    cantidad_disponible: item.cantidad,
                    costo_unitario: item.costo_unitario
                });
            }
        });
        saveInventario(inventario);

        alert('Entrada de medicamento registrada y inventario actualizado exitosamente.');
        form.reset();
        // Limpiar filas de ítems y añadir una vacía
        itemsTableBody.innerHTML = '';
        addMedicamentoItemRow();
    });

    // Resetear el formulario también limpia las filas de ítems
    form.addEventListener('reset', function() {
        setTimeout(() => { // Pequeño delay para que el reset del form ocurra primero
            itemsTableBody.innerHTML = '';
            addMedicamentoItemRow();
            limpiarEstilosError();
        }, 50);
    });
});