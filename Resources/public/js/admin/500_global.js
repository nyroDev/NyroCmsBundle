import Sortable from "sortablejs";

(function () {
    const contentTree = document.getElementById("contentTree");
    const templateClose = document.querySelector("template#closeTpl");
    const templateConfirm = document.querySelector("template#deleteConfirmTpl");

    document.body.addEventListener("click", function (e) {
        const dialogLink = e.target.closest(".dialogLink");
        if (dialogLink) {
            e.preventDefault();
            const dialog = document.createElement("nyro-cms-dialog");

            dialog.appendChild(templateClose.content.cloneNode(true));

            dialog.loadUrl(dialogLink.href);

            document.body.appendChild(dialog);
            dialog.open();
            return;
        }

        const deleteConfirm = e.target.closest(".delete, .confirmLink");
        if (deleteConfirm) {
            e.preventDefault();
            const confirmText = deleteConfirm.classList.contains("confirmLink") ? deleteConfirm.dataset.confirmtxt : deleteConfirm.dataset.deletetxt;
            const confirmBtnText = deleteConfirm.dataset.confirmbtntxt;

            const dialog = document.createElement("nyro-cms-dialog");

            dialog.appendChild(templateClose.content.cloneNode(true));

            const content = templateConfirm.content.cloneNode(true);

            if (confirmText) {
                content.querySelector("p").innerHTML = confirmText;
            }
            if (confirmBtnText) {
                content.querySelector(".confirm").innerHTML = confirmBtnText;
            }

            content.querySelector(".actions").addEventListener("click", (e) => {
                const confirm = e.target.closest(".confirm");
                if (!confirm) {
                    return;
                }
                e.preventDefault();
                document.location.href = deleteConfirm.href;
            });

            dialog.appendChild(content);
            dialog.classList.add("nyroCmsDialogConfirm");
            document.body.appendChild(dialog);
            dialog.open();
        }
    });

    if (contentTree) {
        const contentTreeSubmit = contentTree.querySelector('button[type="submit"]');
        contentTree.addEventListener("click", function (e) {
            const expandReduceAll = e.target.closest(".expandAll, .reduceAll");
            if (expandReduceAll) {
                e.preventDefault();
                contentTree.classList.toggle("expandedAll");
                const shouldExpand = contentTree.classList.contains("expandedAll");
                contentTree.querySelectorAll(".expandToggle").forEach((toggle) => {
                    toggle.checked = shouldExpand;
                });
            }
        });

        contentTree.querySelectorAll("ul").forEach((ul) => {
            Sortable.create(ul, {
                group: "contentTree",
                handle: ".dragHandle",
                animation: 150,
                onEnd: (e) => {
                    contentTreeSubmit.classList.remove("disabled");
                },
            });
        });
    }
})();
