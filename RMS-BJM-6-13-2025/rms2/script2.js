document.addEventListener('DOMContentLoaded', () => {
    fetchMenus();

    // Calculator functionality
    const display = document.getElementById('display');
    document.querySelectorAll('.buttons button').forEach(btn => {
        btn.addEventListener('click', () => {
            const value = btn.textContent;

            if (value === 'C') {
                display.value = '';
            } else if (value === '=') {
                try {
                    display.value = eval(display.value);
                } catch {
                    display.value = 'Error';
                }
            } else {
                display.value += value;
            }
        });
    });
    const clearBtn = document.getElementById('clearSelectedBtn');
    clearBtn.addEventListener('click', () => {
        document.getElementById('selectedItemsBody').innerHTML = '';
        for (const key in selectedItems) {
            delete selectedItems[key];
        }
        updateTotal();
    });
    document.getElementById('proceedOrderBtn').addEventListener('click', () => {
    const orders = Object.values(selectedItems); // Convert selectedItems to an array

    if (orders.length === 0) {
        alert("Please select at least one product.");
        return;
    }

    fetch('proceed_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ orders })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Order processed successfully!");

            // Clear selected items and update UI
            document.getElementById('selectedItemsBody').innerHTML = '';
            for (const key in selectedItems) {
                delete selectedItems[key];
            }
            updateTotal();
            fetchMenus(); // Refresh to disable any out-of-stock items
        } else {
            alert("Order failed: " + data.message);
        }
    })
    .catch(err => {
        console.error('Order failed:', err);
        alert("An error occurred while processing the order.");
    });
});

});


function fetchMenus() {
    fetch('get_menus.php')
        .then(res => res.json())
        .then(menus => {
            const menuContainer = document.querySelector('.menuButtons');
            menuContainer.innerHTML = ''; // Clear before repopulating

            menus.forEach(menu => {
                const btn = document.createElement('button');
                btn.textContent = `${menu.name} - ₱${menu.price}`;
                
                if (menu.out_of_stock) {
                    btn.disabled = true;
                    btn.style.opacity = 0.5;
                    btn.title = "Out of Stock";
                } else {
                    btn.addEventListener('click', () => addToSelected(menu));
                }

                menuContainer.appendChild(btn);
            });
        })
        .catch(err => {
            console.error('Failed to load menus:', err);
        });
}

const selectedItems = {};

function addToSelected(menu) {
    const tbody = document.getElementById('selectedItemsBody');

    if (selectedItems[menu.id]) {
        selectedItems[menu.id].quantity += 1;
        const row = document.getElementById(`row-${menu.id}`);
        row.querySelector('.qty').textContent = selectedItems[menu.id].quantity;
    } else {
        selectedItems[menu.id] = {
            id: menu.id,
            name: menu.name,
            price: parseFloat(menu.price),
            quantity: 1
        };

        const row = document.createElement('tr');
        row.id = `row-${menu.id}`;
        row.innerHTML = `
            <td>${menu.id}</td>
            <td class="qty">1</td>
            <td>${menu.name}</td>
            <td>₱${menu.price}</td>
        `;
        tbody.appendChild(row);
    }

    updateTotal();
}

function updateTotal() {
    let total = 0;
    for (const key in selectedItems) {
        total += selectedItems[key].price * selectedItems[key].quantity;
    }
    document.getElementById('totalPrice').textContent = `₱${total.toFixed(2)}`;
}


