document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("barang-search");
    const searchForm = document.getElementById("barang-search-form");
    const cards = document.querySelectorAll(".barang-card");
    const statusButtons = document.querySelectorAll(".filter-btn");
    const categoryButtons = document.querySelectorAll(".category-btn");
    const countText = document.getElementById("barang-count");

    if (!searchInput || !searchForm || !countText) {
        return;
    }

    let selectedStatus = "semua";
    let selectedCategory = "semua";

    function filterBarang() {

        const keyword = searchInput.value.toLowerCase().trim();
        let total = 0;

        cards.forEach(function (card) {

            const text = card.textContent.toLowerCase();

            const status = card.dataset.status;
            const category = card.dataset.category;
            const matchSearch = !keyword || text.includes(keyword);
            const matchStatus = selectedStatus === "semua" || status === selectedStatus;
            const matchCategory = selectedCategory === "semua" ||
                category === selectedCategory ||
                (selectedCategory === "tas & dompet" &&
                 (category === "tas" || category === "dompet"));
            const show = matchSearch && matchStatus && matchCategory;

            card.style.display = show ? "" : "none";

            if (show) {
                total++;
            }
        });

        countText.textContent =
            total + " barang ditemukan";
    }

    searchForm.addEventListener("submit", function (event) {
        event.preventDefault();
        filterBarang();
    });

    searchInput.addEventListener("input", function () {
        filterBarang();
    });

    statusButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            statusButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            button.classList.add("active");

            selectedStatus = button.dataset.status;

            filterBarang();
        });

    });

    categoryButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            categoryButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            button.classList.add("active");

            selectedCategory = button.dataset.category;

            filterBarang();
        });

    });

    filterBarang();

});