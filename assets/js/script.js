document.addEventListener("DOMContentLoaded", function () {

    const menuBtn =
        document.getElementById("mobileMenuBtn");

    const mobileMenu =
        document.getElementById("mobileMenu");


    if (menuBtn && mobileMenu) {

        menuBtn.addEventListener(
            "click",
            function () {

                mobileMenu.classList.toggle(
                    "active"
                );

            }
        );

    }

});