//pang id ng menu
let menuCounter = 1;


document.addEventListener("DOMContentLoaded", function() {
    limitIngredients();
    addIngredients();
});

function limitIngredients() {
    document.getElementById("ingredientsNum").addEventListener("input", function() {
        let value = parseInt(this.value);
        if (value < 1) this.value = 1;
        if (value > 9) this.value = 9;
    });
}

function addIngredients() {
    document.getElementById("ingredientsNum").addEventListener("input", function() {
        let container = document.getElementById("inputContainer");
        let num = parseInt(this.value);
        
        if (num < 1) num = 0;
        container.innerHTML = "";

        for (let i = 0; i < num; i++) {
            let newInput = document.createElement("input");
            newInput.type = "text";
            newInput.id="Ingredient" + i;
            newInput.placeholder = `Ingredient ${i + 1}`;
            newInput.className = "ingredientInput";
            container.appendChild(newInput);
            container.appendChild(document.createElement("br"));
        }
    });
}

function createMenuItem() {
    let menuName = document.getElementById("menuName").value.trim();
    let menuPrice = document.getElementById("menuPrice").value.trim();
    let ingredientsElems = document.querySelectorAll(".ingredientInput");
    let ingredients = [];

    ingredientsElems.forEach(input => {
        if (input.value.trim() !== "") {
            ingredients.push(input.value.trim());
        }
    });

    if (!menuName || !menuPrice || ingredients.length === 0) {
        alert("Please fill in all fields!");
        return;
    }

    fetch("insert_menu.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            name: menuName,
            price: menuPrice,
            ingredients: ingredients
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Menu item added successfully!");
            // Optionally clear inputs or update UI
            document.getElementById("menuName").value = "";
            document.getElementById("menuPrice").value = "";
            document.getElementById("ingredientsNum").value = "";
            document.getElementById("inputContainer").innerHTML = "";
        } else if (data.error) {
            alert("Error: " + data.error);
        }
    })
    .catch(err => {
        alert("Request failed: " + err.message);
    });
}
function displayMenuLocally(menuName, menuPrice, ingredients) {
    let menuId = `Menu${menuCounter++}`;
    let menuDiv = document.createElement("div");
    menuDiv.id = menuId;
    menuDiv.className = "menuItem";

    let nameElement = document.createElement("h2");
    nameElement.textContent = `Name: ${menuName}`;
    menuDiv.appendChild(nameElement);

    let priceElement = document.createElement("p");
    priceElement.textContent = `Price: P${menuPrice}`;
    menuDiv.appendChild(priceElement);

    let ingredientTitle = document.createElement("h4");
    ingredientTitle.textContent = "Ingredient List";
    menuDiv.appendChild(ingredientTitle);

    let ingredientsList = document.createElement("ul");
    ingredients.forEach((ing, i) => {
        let listItem = document.createElement("li");
        listItem.textContent = ing;
        ingredientsList.appendChild(listItem);
    });
    menuDiv.appendChild(ingredientsList);

    document.getElementById("menuContainer").appendChild(menuDiv);
}
