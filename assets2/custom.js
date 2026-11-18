document.addEventListener("DOMContentLoaded", function () {
  function addToCart(productElement) {
    const productName = productElement
      .querySelector(".product_name")
      .textContent.trim()
      .replace(/\s+/g, " ")
      .replace(/\n/g, "");

    const productPrice = parseFloat(
      productElement
        .querySelector(".current_price")
        .textContent.replace("₹", "")
        .trim()
    );
    const productImage = productElement.querySelector(".primary_img img").src;
    const productPath = productElement.querySelector(".primary_img").href;
    const quantity = 1; // You can modify this based on how many products are added.

    // Prepare data to store in localStorage
    const productData = {
      name: productName,
      price: productPrice,
      image: productImage,
      path: productPath,
      quantity: quantity,
    };

    // Retrieve cartData from localStorage (or initialize as empty array if not present)
    let cartData = JSON.parse(localStorage.getItem("cartData")) || [];

    // Check if the product already exists in the cart
    const productIndex = cartData.findIndex(
      (item) => item.name === productName
    );

    if (productIndex === -1) {
      // Add new product to localStorage
      cartData.push(productData);
    } else {
      // Update the quantity if the product already exists in the cart
      cartData[productIndex].quantity += quantity;
    }

    // Save updated cart data back to localStorage
    localStorage.setItem("cartData", JSON.stringify(cartData));

    // Send the data to track_cart.php
    const formData = new FormData();
    formData.append("name", productName);
    formData.append("price", productPrice);
    formData.append("image", productImage);
    formData.append("path", productPath);
    formData.append("quantity", quantity);

    fetch("track_cart.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        console.log(data); // You can display success message to the user
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  }

  const addToCartButtons = document.querySelectorAll(".add_to_cart a");

  addToCartButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const productElement = button.closest(".single_product");
      addToCart(productElement);
    });
  });
});

function removeFromCart(productName) {
  let cartData = JSON.parse(localStorage.getItem("cartData")) || [];

  cartData = cartData.filter((item) => item.name !== productName);

  localStorage.setItem("cartData", JSON.stringify(cartData));
}

window.onload = function () {};

function toggleOverflow() {
  const miniCart = document.querySelector(".mini_cart");

  if (miniCart.classList.contains("active")) {
    document.body.classList.add("overflow-hidden");
  } else {
    document.body.classList.remove("overflow-hidden");
  }
}

function getFilteredPrice(event) {
  event.preventDefault();

  const priceRange = document.getElementById("amount").value;
  const cleanedPriceRange = priceRange.replace(/[^\d.-]/g, "");

  const [minPrice, maxPrice] = cleanedPriceRange
    .split("-")
    .map((value) => parseFloat(value.trim()));

  const products = document.querySelectorAll(".single_product");
  let visibleCount = 0;

  products.forEach((product) => {
    const productPrice = parseFloat(
      product.closest(".col-lg-4").getAttribute("data-price")
    );

    if (productPrice >= minPrice && productPrice <= maxPrice) {
      product.closest(".col-lg-4").style.display = "block";
      visibleCount++;
    } else {
      product.closest(".col-lg-4").style.display = "none";
    }
  });

  const resultText = `Showing ${visibleCount} results`;
  document.querySelector(".page_amount p").textContent = resultText;
}

document
  .getElementById("productForm")
  ?.addEventListener("submit", function (event) {
    event.preventDefault();

    const productName = document
      .getElementById("productName")
      .textContent.trim();

    const productPrice = parseFloat(
      document
        .getElementById("productPrice")
        .textContent.replace("₹", "")
        .trim()
    );
    const quantity = parseInt(document.getElementById("quantity").value);
    const productImage = document.querySelector("#img-1 img").src;

    const product = {
      name: productName,
      price: productPrice,
      quantity: quantity,
      image: productImage,
      path: window.location.href,
    };

    let cartData = JSON.parse(localStorage.getItem("cartData")) || [];

    const existingProductIndex = cartData.findIndex(
      (item) => item.name === product.name
    );

    if (existingProductIndex !== -1) {
      cartData[existingProductIndex].quantity += quantity;
    } else {
      cartData.push(product);
    }

    localStorage.setItem("cartData", JSON.stringify(cartData));
  });
