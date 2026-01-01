document.addEventListener("DOMContentLoaded", () => {
  // Sidebar aktif
  const sidebarItems = document.querySelectorAll(".sidebar li");
  sidebarItems.forEach(item => {
    item.addEventListener("click", () => {
      sidebarItems.forEach(i => i.classList.remove("active"));
      item.classList.add("active");
    });
  });

  // Produk dan keranjang
  const productCards = document.querySelectorAll(".product-card");
  const cartBadge = document.querySelector(".cart-badge");
  const checkoutBtn = document.querySelector(".checkout");
  let cart = {};

  productCards.forEach(card => {
    const addBtn = card.querySelector(".plus");
    const minusBtn = card.querySelector(".minus");
    const qtyBadge = document.createElement("div");
    qtyBadge.classList.add("quantity-badge");
    let productName = card.querySelector("h3").innerText;
    let quantity = 0;

    qtyBadge.innerText = quantity;
    card.appendChild(qtyBadge);
    qtyBadge.style.display = "none";

    // Tambah produk
    addBtn.addEventListener("click", () => {
      quantity++;
      qtyBadge.innerText = quantity;
      qtyBadge.style.display = "block";
      card.classList.add("selected");
      cart[productName] = quantity;
      updateCartBadge();
    });

    // Kurangi produk
    minusBtn.addEventListener("click", () => {
      if (quantity > 0) {
        quantity--;
        qtyBadge.innerText = quantity;
        if (quantity === 0) {
          qtyBadge.style.display = "none";
          card.classList.remove("selected");
          delete cart[productName];
        } else {
          cart[productName] = quantity;
        }
        updateCartBadge();
      }
    });
  });

  // Update jumlah di ikon keranjang
  function updateCartBadge() {
    let total = Object.values(cart).reduce((a, b) => a + b, 0);
    cartBadge.innerText = total;
    cartBadge.style.display = total > 0 ? "block" : "none";
  }

  // Checkout
  checkoutBtn.addEventListener("click", () => {
    if (Object.keys(cart).length === 0) {
      alert("Keranjang kosong!");
      return;
    }
    let summary = "Pesanan Anda:\n";
    for (let [item, qty] of Object.entries(cart)) {
      summary += `- ${item}: ${qty}\n`;
    }
    alert(summary + "\nTerima kasih telah memesan!");
    cart = {};
    document.querySelectorAll(".quantity-badge").forEach(b => b.style.display = "none");
    document.querySelectorAll(".product-card").forEach(c => c.classList.remove("selected"));
    updateCartBadge();
  });
});

