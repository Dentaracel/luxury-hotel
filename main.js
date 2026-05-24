// Mobile menu toggle
const menuBtn = document.getElementById("menu-btn");
const navLinks = document.getElementById("nav-links");

if (menuBtn && navLinks) {
  const menuBtnIcon = menuBtn.querySelector("i");

  menuBtn.addEventListener("click", () => {
    navLinks.classList.toggle("open");
    const isOpen = navLinks.classList.contains("open");
    menuBtnIcon.setAttribute("class", isOpen ? "ri-close-line" : "ri-menu-line");
  });

  navLinks.addEventListener("click", () => {
    navLinks.classList.remove("open");
    menuBtnIcon.setAttribute("class", "ri-menu-line");
  });
}

// ScrollReveal animations (only on pages that have ScrollReveal loaded)
if (typeof ScrollReveal !== "undefined") {
  const scrollRevealOption = {
    distance: "50px",
    origin: "bottom",
    duration: 1000,
  };

  ScrollReveal().reveal(".header__container p", { ...scrollRevealOption });
  ScrollReveal().reveal(".header__container h1", { ...scrollRevealOption, delay: 500 });
  ScrollReveal().reveal(".about__image img", { ...scrollRevealOption, origin: "left" });
  ScrollReveal().reveal(".about__content .section__subheader", { ...scrollRevealOption, delay: 500 });
  ScrollReveal().reveal(".about__content .section__header", { ...scrollRevealOption, delay: 1000 });
  ScrollReveal().reveal(".about__content .section__description", { ...scrollRevealOption, delay: 1500 });
  ScrollReveal().reveal(".about__btn", { ...scrollRevealOption, delay: 2000 });
  ScrollReveal().reveal(".room__card", { ...scrollRevealOption, interval: 500 });
  ScrollReveal().reveal(".service__list li", { ...scrollRevealOption, interval: 500, origin: "right" });
}

// ===== BOOKING PAGE LOGIC =====

// Price calculator
function calculateNights() {
  const checkIn = document.getElementById("checkIn");
  const checkOut = document.getElementById("checkOut");
  if (!checkIn || !checkOut) return 1;

  const start = new Date(checkIn.value);
  const end = new Date(checkOut.value);
  if (start && end && start < end) {
    return Math.ceil((end - start) / (1000 * 60 * 60 * 24));
  }
  return 1;
}

let roomPrices = {
  standard: 500000,
  deluxe: 750000,
  suite: 1200000,
  oceanview: 1500000,
};

const servicePrices = {
  breakfast: 100000,
  airport: 300000,
  spa: 250000,
  parking: 0,
};

let availableRoomsData = [];

function loadAvailableRooms() {
  const checkIn = document.getElementById("checkIn");
  const checkOut = document.getElementById("checkOut");
  const floorEl = document.getElementById("selectedFloor");
  const roomSelect = document.getElementById("selectedRoom");

  if (!checkIn || !checkOut || !floorEl || !roomSelect) return;

  roomSelect.innerHTML = '<option value="">-- Pilih Kamar Terlebih Dahulu --</option>';

  const checkInVal = checkIn.value;
  const checkOutVal = checkOut.value;
  const floor = floorEl.value;

  if (!checkInVal || !checkOutVal || !floor) {
    roomSelect.disabled = true;
    return;
  }

  fetch(`booking.php?action=getAvailableRooms&check_in=${checkInVal}&check_out=${checkOutVal}`)
    .then((res) => res.json())
    .then((data) => {
      availableRoomsData = data;
      const filtered = data.filter((r) => r.floor == floor);

      if (filtered.length === 0) {
        roomSelect.innerHTML = '<option value="">Tidak ada kamar tersedia di lantai ini</option>';
        roomSelect.disabled = true;
      } else {
        filtered.forEach((room) => {
          const option = document.createElement("option");
          option.value = room.id;
          const price = roomPrices[room.room_type] || 500000;
          option.textContent = `Kamar ${room.room_number} - ${
            room.room_type.charAt(0).toUpperCase() + room.room_type.slice(1)
          } (Rp ${price.toLocaleString("id-ID")})`;
          roomSelect.appendChild(option);
        });
        roomSelect.disabled = false;
      }
    })
    .catch(() => {
      roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
      roomSelect.disabled = true;
    });
}

function updateRoomType() {
  const roomId = document.getElementById("selectedRoom")?.value;
  const roomTypeInput = document.getElementById("roomType");
  if (!roomId || !roomTypeInput) return;

  const selected = availableRoomsData.find((r) => r.id == roomId);
  if (selected) {
    roomTypeInput.value = selected.room_type;
    updatePrice();
  }
}

function updatePrice() {
  const roomTypeInput = document.getElementById("roomType");
  if (!roomTypeInput || !roomTypeInput.value) return;

  const nights = calculateNights();
  const roomPrice = roomPrices[roomTypeInput.value] || 500000;
  const roomSubtotal = roomPrice * nights;

  let servicesTotal = 0;
  document.querySelectorAll('input[name="services[]"]:checked').forEach((cb) => {
    if (cb.value === "breakfast") {
      servicesTotal += servicePrices["breakfast"] * nights;
    } else {
      servicesTotal += servicePrices[cb.value] || 0;
    }
  });

  const total = roomSubtotal + servicesTotal;

  const roomPriceEl = document.getElementById("roomPrice");
  const nightsEl = document.getElementById("nights");
  const subtotalEl = document.getElementById("subtotal");
  const servicesTotalEl = document.getElementById("servicesTotal");
  const totalPriceEl = document.getElementById("totalPrice");
  const totalPriceInput = document.getElementById("totalPriceInput");

  if (roomPriceEl) roomPriceEl.textContent = "Rp " + roomPrice.toLocaleString("id-ID");
  if (nightsEl) nightsEl.textContent = nights;
  if (subtotalEl) subtotalEl.textContent = "Rp " + roomSubtotal.toLocaleString("id-ID");
  if (servicesTotalEl) servicesTotalEl.textContent = "Rp " + servicesTotal.toLocaleString("id-ID");
  if (totalPriceEl) totalPriceEl.textContent = "Rp " + total.toLocaleString("id-ID");
  if (totalPriceInput) totalPriceInput.value = total;
}

// Init booking page
document.addEventListener("DOMContentLoaded", function () {
  const bookingForm = document.getElementById("bookingForm");
  if (!bookingForm) return;

  // Load prices from database
  fetch("booking.php?action=getPrices")
    .then((res) => res.json())
    .then((data) => { roomPrices = data; })
    .catch(() => {});

  // Set min date
  const today = new Date().toISOString().split("T")[0];
  const checkInEl = document.getElementById("checkIn");
  const checkOutEl = document.getElementById("checkOut");
  const checkOutError = document.getElementById("checkOutError");

  if (checkInEl) checkInEl.setAttribute("min", today);

  if (checkInEl) {
    checkInEl.addEventListener("change", function () {
      if (!this.value) return;

      const minCheckOut = new Date(this.value);
      minCheckOut.setDate(minCheckOut.getDate() + 1);
      const minStr = minCheckOut.toISOString().split("T")[0];
      if (checkOutEl) checkOutEl.setAttribute("min", minStr);

      if (checkOutEl && checkOutEl.value && checkOutEl.value <= this.value) {
        checkOutEl.value = "";
        if (checkOutError) checkOutError.textContent = "⚠ Check-out harus minimal 1 hari setelah check-in";
      } else {
        if (checkOutError) checkOutError.textContent = "";
      }

      loadAvailableRooms();
      updatePrice();
    });
  }

  if (checkOutEl) {
    checkOutEl.addEventListener("change", function () {
      const checkInVal = checkInEl?.value;
      if (checkInVal && this.value <= checkInVal) {
        this.value = "";
        if (checkOutError) checkOutError.textContent = "⚠ Check-out harus minimal 1 hari setelah check-in";
      } else {
        if (checkOutError) checkOutError.textContent = "";
        loadAvailableRooms();
        updatePrice();
      }
    });
  }

  // Floor change
  const floorEl = document.getElementById("selectedFloor");
  if (floorEl) floorEl.addEventListener("change", loadAvailableRooms);

  // Room change
  const roomEl = document.getElementById("selectedRoom");
  if (roomEl) roomEl.addEventListener("change", function () {
    updateRoomType();
    updatePrice();
  });

  // Services change
  document.querySelectorAll('input[name="services[]"]').forEach((cb) => {
    cb.addEventListener("change", updatePrice);
  });

  // Form submission feedback
  bookingForm.addEventListener("submit", function () {
    const btn = this.querySelector('button[type="submit"]');
    if (btn) {
      btn.textContent = "Memproses...";
      btn.disabled = true;
    }
  });
});
