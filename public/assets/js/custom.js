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

$(window).scroll(function () {
    var navbar = $(".unitoffice-entry-table .table-responsive thead");
    if ($(window).scrollTop() >= 250) {
        navbar.addClass("sticky");
    } else {
        navbar.removeClass("sticky");
    }
});
