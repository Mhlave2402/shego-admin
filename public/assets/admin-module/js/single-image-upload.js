document.querySelectorAll(".upload-file__input").forEach(function (input) {
    input.addEventListener("change", function (event) {
        var file = event.target.files[0];
        var card = event.target.closest(".upload-file");
        var textbox = card.querySelector(".upload-file__textbox");
        var imgElement = card.querySelector(".upload-file__img__img");

        var prevSrc = textbox.querySelector("img").src;

        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imgElement.src = e.target.result;

                $(card).find(".remove-img-icon").removeClass("d-none");
                textbox.style.display = "none";
                imgElement.style.display = "block";
            };
            reader.readAsDataURL(file);
        }

        // Remove image
        $(card)
            .find(".remove-img-icon")
            .on("click", function () {
                $(card).find(".upload-file__input").val("");
                $(card).find(".upload-file__img__img").attr("src", "");
                textbox.querySelector("img").src = prevSrc;
                textbox.style.display = "block";
                imgElement.style.display = "none";
                $(card).find(".remove-img-icon").addClass("d-none");
            });
    });
});

document.querySelectorAll("form").forEach(function (form) {
    form.addEventListener("reset", function () {
        setTimeout(function () {
            form.querySelectorAll(".upload-file").forEach(function (card) {
                const input = card.querySelector(".upload-file__input");
                const previewImg = card.querySelector(".upload-file__img__img");
                const textbox = card.querySelector(".upload-file__textbox");
                const removeIcon = card.querySelector(".remove-img-icon");

                input.value = "";

                if (previewImg.dataset.original && previewImg.dataset.original.trim() !== "") {
                    previewImg.src = previewImg.dataset.original;
                    previewImg.style.display = "block";
                    textbox.style.display = "none";
                } else {
                    previewImg.src = "";
                    previewImg.style.display = "none";
                    textbox.style.display = "block";
                }

                removeIcon.classList.add("d-none");
            });
        }, 0);
    });
});


