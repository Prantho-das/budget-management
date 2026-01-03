document.addEventListener("livewire:initialized", () => {
    Livewire.on("delete-confirmation", (id) => {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("deleteConfirmed", {
                    id: id,
                });
            }
        });
    });
});

// table sticky on scroll

// $(window).scroll(function () {
//     var navbar = $(".unitoffice-entry-table .table-responsive thead");
//     if ($(window).scrollTop() >= 150) {
//         navbar.addClass("sticky");
//     } else {
//         navbar.removeClass("sticky");
//     }
// });
// $(document).ready(function () {
//     var $tableContainer = $(".unitoffice-entry-table .table-responsive");
//     var $thead = $tableContainer.find("thead");

//     $tableContainer.on("scroll", function () {
//         if ($(this).scrollTop() >= 150) {
//             $thead.addClass("sticky");
//         } else {
//             $thead.removeClass("sticky");
//         }
//     });
// });

// $(window).scroll(function () {
//     var navbar = $(".floating-budget-card");
//     if ($(window).scrollTop() >= 50) {
//         navbar.addClass("sticky");
//     } else {
//         navbar.removeClass("sticky");
//     }
// });
