let carrito = [];

function cambiarCantidad(btn, cambio) {
    const card = btn.closest('.producto-card');
    const input = card.querySelector('.cantidad-input');
    let nuevaCantidad = parseInt(input.value) + cambio;
    const maxStock = parseInt(card.dataset.stock);
    
    if (nuevaCantidad >= 0 && nuevaCantidad <= maxStock) {
        input.value = nuevaCantidad;
        actualizarCarritoDesdeInputs();
    }
}

function actualizarCarritoDesdeInputs() {
    carrito = [];
    const cards = document.querySelectorAll('.producto-card');
    
    cards.forEach(card => {
        const input = card.querySelector('.cantidad-input');
        const cantidad = parseInt(input.value);
        
        if (cantidad > 0) {
            const id = card.dataset.id;
            const nombre = card.querySelector('.producto-nombre').textContent;
            const tipoPorcion = card.querySelector('.tipo-porcion')?.value;
            
            let precio;
            if (tipoPorcion) {
                precio = tipoPorcion === 'chica' ? 
                    parseFloat(card.dataset.precioCh) : 
                    parseFloat(card.dataset.precioGr);
            } else {
                precio = parseFloat(card.dataset.precio);
            }
            
            carrito.push({
                id: id,
                nombre: nombre,
                precio: precio,
                cantidad: cantidad,
                tipo_porcion: tipoPorcion,
                stock: parseInt(card.dataset.stock)
            });
        }
    });
    
    actualizarCarrito();
}

function actualizarCarrito() {
    const carritoItems = document.getElementById('carrito-items');
    const subtotalSpan = document.getElementById('subtotal');
    const totalSpan = document.getElementById('total');
    const totalInput = document.getElementById('total-input');
    const carritoCount = document.getElementById('carrito-count');
    const btnConfirmar = document.getElementById('btn-confirmar');
    const stockMessages = document.getElementById('stock-messages');
    
    let subtotal = 0;
    let sinStock = [];
    
    if (carrito.length === 0) {
        carritoItems.innerHTML = `
            <div class="carrito-vacio">
                <i class="fas fa-shopping-basket"></i>
                <p>No hay productos seleccionados</p>
            </div>
        `;
        subtotalSpan.textContent = 'Bs. 0.00';
        totalSpan.textContent = 'Bs. 0.00';
        totalInput.value = '0';
        carritoCount.textContent = '0';
        btnConfirmar.disabled = true;
        stockMessages.style.display = 'none';
        return;
    }
    
    let html = '';
    let totalItems = 0;
    
    carrito.forEach((item, index) => {
        const itemSubtotal = item.precio * item.cantidad;
        subtotal += itemSubtotal;
        totalItems += item.cantidad;
        
        // Verificar stock
        if (item.cantidad > item.stock) {
            sinStock.push(item.nombre);
        }
        
        html += `
            <div class="carrito-item">
                <div class="carrito-item-info">
                    <h4>${item.nombre}</h4>
                    ${item.tipo_porcion ? `<small>${item.tipo_porcion === 'chica' ? 'CH' : 'GR'}</small>` : ''}
                    <small>x${item.cantidad}</small>
                </div>
                <div class="carrito-item-cantidad">
                    <button onclick="modificarCantidadCarrito(${index}, -1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span>${item.cantidad}</span>
                    <button onclick="modificarCantidadCarrito(${index}, 1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="carrito-item-precio">
                    Bs. ${(itemSubtotal).toFixed(2)}
                </div>
            </div>
        `;
    });
    
    carritoItems.innerHTML = html;
    subtotalSpan.textContent = `Bs. ${subtotal.toFixed(2)}`;
    totalSpan.textContent = `Bs. ${subtotal.toFixed(2)}`;
    totalInput.value = subtotal;
    carritoCount.textContent = totalItems;
    
    // Verificar stock
    if (sinStock.length > 0) {
        btnConfirmar.disabled = true;
        stockMessages.style.display = 'block';
        stockMessages.className = 'stock-message error';
        stockMessages.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            No hay suficiente stock de: ${sinStock.join(', ')}
        `;
    } else {
        btnConfirmar.disabled = carrito.length === 0;
        stockMessages.style.display = 'none';
    }
    
    // Actualizar JSON para el formulario
    document.getElementById('productos-json').value = JSON.stringify(carrito);
    
    // Sincronizar inputs de cantidad
    sincronizarInputsConCarrito();
}

function sincronizarInputsConCarrito() {
    // Resetear todos los inputs a 0
    document.querySelectorAll('.cantidad-input').forEach(input => {
        input.value = 0;
    });
    
    // Establecer valores según carrito
    carrito.forEach(item => {
        const cards = document.querySelectorAll('.producto-card');
        cards.forEach(card => {
            const nombre = card.querySelector('.producto-nombre').textContent;
            if (nombre === item.nombre) {
                const input = card.querySelector('.cantidad-input');
                if (input) {
                    input.value = item.cantidad;
                }
                
                // Si hay selector de porción, establecerlo
                const select = card.querySelector('.tipo-porcion');
                if (select && item.tipo_porcion) {
                    select.value = item.tipo_porcion;
                }
            }
        });
    });
}

function modificarCantidadCarrito(index, cambio) {
    const nuevaCantidad = carrito[index].cantidad + cambio;
    if (nuevaCantidad > 0 && nuevaCantidad <= carrito[index].stock) {
        carrito[index].cantidad = nuevaCantidad;
        actualizarCarrito();
    } else if (nuevaCantidad === 0) {
        carrito.splice(index, 1);
        actualizarCarrito();
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Sincronizar tipo de pedido
    document.querySelectorAll('input[name="tipo_pedido"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('tipo-pedido-input').value = this.value;
        });
    });
    
    // Validar formulario
    document.getElementById('form-pedido').addEventListener('submit', function(e) {
        if (carrito.length === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un producto');
            return;
        }
    });
    
    // Actualizar carrito cuando cambien los selects de porción
    document.querySelectorAll('.tipo-porcion').forEach(select => {
        select.addEventListener('change', function() {
            actualizarCarritoDesdeInputs();
        });
    });
});