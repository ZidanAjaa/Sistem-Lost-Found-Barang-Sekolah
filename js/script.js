document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.querySelector(".search-box input");
    const searchForm = document.querySelector(".search-box");
    const cards = document.querySelectorAll(".barang-card");
    const statusButtons = document.querySelectorAll(".filter-btn");
    const categoryButtons = document.querySelectorAll(".category-btn");
    const countText = document.querySelector(".section-header p");

    let selectedStatus = "Semua";
    let selectedCategory = "Semua";

    function filterBarang() {

        const keyword = searchInput.value.toLowerCase().trim();
        let total = 0;

        cards.forEach(function (card) {

            const text = card.textContent.toLowerCase();

            const category = card
                .querySelector(".category-label")
                .textContent
                .trim();

            const badge = card.querySelector(".status-badge");

            const itemStatus = badge.classList.contains("status-found")
                ? "Ditemukan"
                : "Hilang";

            const matchSearch =
                !keyword || text.includes(keyword);

            const matchStatus =
                selectedStatus === "Semua" ||
                itemStatus === selectedStatus;

            let matchCategory = true;

            if (selectedCategory !== "Semua") {

                if (selectedCategory === "Tas & Dompet") {

                    matchCategory =
                        category === "Tas" ||
                        category === "Dompet";

                } else {

                    matchCategory =
                        category.toLowerCase() ===
                        selectedCategory.toLowerCase();

                }
            }

            const show =
                matchSearch &&
                matchStatus &&
                matchCategory;

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

            selectedStatus =
                button.textContent.trim();

            filterBarang();
        });

    });

    categoryButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            categoryButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            button.classList.add("active");

            selectedCategory =
                button.textContent.trim();

            filterBarang();
        });

    });

    filterBarang();

});