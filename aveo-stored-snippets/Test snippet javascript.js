document.addEventListener("DOMContentLoaded", function() {
            var button = document.getElementById("test-btn");
            var hiddenText = document.querySelector(".hidden-text");

            button.addEventListener("click", function() {
                if (hiddenText.style.display === "none") {
                    hiddenText.style.display = "block";
                } else {
                    hiddenText.style.display = "none";
                }
            });
        });