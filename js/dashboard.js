document.addEventListener("DOMContentLoaded", function () {
    const shell = document.querySelector(".dashboard-shell");
    const sidebarToggle = document.querySelector(".sidebar-toggle");
    const sidebarBackdrop = document.querySelector(".sidebar-backdrop");
    const sidebarLinks = document.querySelectorAll(".sidebar-nav a");
    const searchInput = document.getElementById("dashboard-search");
    const mobileSidebar = window.matchMedia("(max-width: 900px)");

    function setSidebarState(isOpen) {
        if (!shell || !sidebarToggle) {
            return;
        }

        if (mobileSidebar.matches) {
            shell.classList.toggle("sidebar-open", isOpen);
            shell.classList.remove("sidebar-collapsed");
        } else {
            shell.classList.remove("sidebar-open");
            shell.classList.toggle("sidebar-collapsed", !isOpen);
        }

        sidebarToggle.setAttribute("aria-expanded", String(isOpen));
    }

    if (shell && sidebarToggle) {
        setSidebarState(false);

        sidebarToggle.addEventListener("click", function () {
            const isOpen = mobileSidebar.matches
                ? !shell.classList.contains("sidebar-open")
                : shell.classList.contains("sidebar-collapsed");
            setSidebarState(isOpen);
        });

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener("click", function () {
                setSidebarState(false);
            });
        }

        sidebarLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                sidebarLinks.forEach(function (sidebarLink) {
                    sidebarLink.classList.remove("active");
                });
                link.classList.add("active");

                if (mobileSidebar.matches) {
                    setSidebarState(false);
                }
            });
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && shell.classList.contains("sidebar-open")) {
                setSidebarState(false);
                sidebarToggle.focus();
            }
        });

        mobileSidebar.addEventListener("change", function () {
            setSidebarState(false);
        });
    }

    if (searchInput) {
        const searchableRows = document.querySelectorAll(".dashboard-searchable tbody tr");

        searchInput.addEventListener("input", function () {
            const keyword = searchInput.value.trim().toLocaleLowerCase();

            searchableRows.forEach(function (row) {
                row.hidden = keyword !== "" && !row.textContent.toLocaleLowerCase().includes(keyword);
            });
        });
    }
});
