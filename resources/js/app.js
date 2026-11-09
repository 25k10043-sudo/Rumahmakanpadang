const formatRupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;

const checkoutForm = document.querySelector('#checkout-form');

if (checkoutForm) {
    const cart = new Map();
    const cartItems = document.querySelector('#cart-items');
    const cartEmpty = document.querySelector('#cart-empty');
    const cartCount = document.querySelector('#cart-count');
    const cartTotal = document.querySelector('#cart-total');
    const checkoutButton = document.querySelector('#checkout-button');
    const orderType = document.querySelector('#order-type');
    const deliveryAddressField = document.querySelector('#delivery-address-field');
    const deliveryAddress = deliveryAddressField.querySelector('input');

    const updateDeliveryAddress = () => {
        const isDelivery = orderType.value === 'delivery';
        deliveryAddressField.hidden = !isDelivery;
        deliveryAddress.disabled = !isDelivery;
        deliveryAddress.required = isDelivery;
    };

    orderType.addEventListener('change', updateDeliveryAddress);
    updateDeliveryAddress();

    const renderCart = () => {
        cartItems.replaceChildren();
        let totalQuantity = 0;
        let totalPrice = 0;

        for (const [id, item] of cart) {
            totalQuantity += item.quantity;
            totalPrice += item.price * item.quantity;

            const row = document.createElement('div');
            row.className = 'cart-row';
            const description = document.createElement('div');
            const name = document.createElement('span');
            name.className = 'cart-row-name';
            name.textContent = item.name;
            const price = document.createElement('small');
            price.className = 'cart-row-price';
            price.textContent = formatRupiah(item.price * item.quantity);
            description.append(name, price);

            const quantity = document.createElement('div');
            quantity.className = 'quantity-control';
            const decrement = document.createElement('button');
            decrement.type = 'button';
            decrement.setAttribute('aria-label', `Kurangi ${item.name}`);
            decrement.textContent = '−';
            decrement.addEventListener('click', () => changeQuantity(id, -1));
            const count = document.createElement('span');
            count.textContent = item.quantity;
            const increment = document.createElement('button');
            increment.type = 'button';
            increment.setAttribute('aria-label', `Tambah ${item.name}`);
            increment.textContent = '+';
            increment.addEventListener('click', () => changeQuantity(id, 1));
            quantity.append(decrement, count, increment);
            row.append(description, quantity);
            cartItems.append(row);
        }

        cartEmpty.hidden = cart.size > 0;
        cartCount.textContent = totalQuantity;
        cartTotal.textContent = formatRupiah(totalPrice);
        checkoutButton.disabled = cart.size === 0;
    };

    const changeQuantity = (id, delta) => {
        const item = cart.get(id);
        if (!item) return;

        item.quantity += delta;
        if (item.quantity <= 0) cart.delete(id);
        renderCart();
    };

    document.querySelectorAll('.add-button').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const current = cart.get(id);

            if (current) {
                current.quantity += 1;
            } else {
                cart.set(id, {
                    name: button.dataset.name,
                    price: Number(button.dataset.price),
                    quantity: 1,
                });
            }

            renderCart();
        });
    });

    document.querySelectorAll('.category-chip').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('.category-chip.active')?.classList.remove('active');
            button.classList.add('active');

            document.querySelectorAll('.dish-card').forEach((card) => {
                card.hidden = button.dataset.category !== 'Semua' && card.dataset.category !== button.dataset.category;
            });
        });
    });

    checkoutForm.addEventListener('submit', (event) => {
        checkoutForm.querySelectorAll('[data-cart-input]').forEach((input) => input.remove());

        [...cart.values()].forEach((item, index) => {
            const dishId = [...cart.keys()][index];
            for (const [field, value] of Object.entries({ id: dishId, quantity: item.quantity })) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${field}]`;
                input.value = value;
                input.dataset.cartInput = 'true';
                checkoutForm.append(input);
            }
        });

        if (cart.size === 0) event.preventDefault();
    });
}
