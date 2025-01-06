// Thêm người dùng
$("#addUserForm").submit(function (e) {
    e.preventDefault();
    $.post("add_user.php", $(this).serialize(), function (response) {
        if (response.status === "success") {
            alert("Thêm người dùng thành công!");
            location.reload();
        } else {
            alert(response.message || "Có lỗi xảy ra khi thêm người dùng.");
        }
    }, "json").fail(function () {
        alert("Không thể kết nối tới máy chủ.");
    });
});

$(document).ready(function () {
    function showLoading(buttonId, loadingId, show = true) {
        $(buttonId).prop("disabled", show);
        $(loadingId).toggleClass("d-none", !show);
    }

    function showError(errorId, message) {
        $(errorId).removeClass("d-none").text(message);
    }

//     function openEditUserModal(userData) {
//         $("#editUserId").val(userData.id);
//         $("#editUsername").val(userData.username);
//         $("#editEmail").val(userData.email);
//         $("#editPoints").val(userData.points);
//         $("#editTrustPoints").val(userData.trust_points);
//         $("#editRole").val(userData.role);
//         $("#editFullname").val(userData.fullname);
//         $("#editGender").val(userData.gender);
//         $("#editBankAccount").val(userData.bank_account);
//         $("#editCardHolderName").val(userData.card_holder_name);
//         $("#editBankName").val(userData.bank_name);
//         $("#currentAvatarPreview").attr("src", userData.avatar ? userData.avatar : "default-avatar.png");

//         $("#editUserModal").modal("show");
//     }

//     $("#editUserForm").submit(async function (e) {
//         e.preventDefault();
//         showLoading("#editUserForm button[type=submit]", "#editUserLoading", true);
//         $("#editUserError").addClass("d-none");

//         const formData = new FormData(this);

//         try {
//             const response = await $.ajax({
//                 url: "edit_user.php",
//                 method: "POST",
//                 data: formData,
//                 processData: false,
//                 contentType: false,
//             });

//             if (response.trim() === "success") {
//                 alert("Cập nhật người dùng thành công!");
//                 $("#editUserModal").modal("hide");
//                 location.reload();
//             } else {
//                 showError("#editUserError", "Có lỗi xảy ra: " + response);
//             }
//         } catch (error) {
//             showError("#editUserError", "Lỗi kết nối: " + error);
//         } finally {
//             showLoading("#editUserForm button[type=submit]", "#editUserLoading", false);
//         }
//     });

//     $("#editAvatar").change(function () {
//         const file = this.files[0];
//         if (file) {
//             const reader = new FileReader();
//             reader.onload = e => $("#currentAvatarPreview").attr("src", e.target.result);
//             reader.readAsDataURL(file);
//         }
//     });
 });


// Xóa người dùng
$("#deleteUserForm").submit(function (e) {
    e.preventDefault();
    $.post("delete_user.php", $(this).serialize(), function (response) {
        if (response.status === "success") {
            alert("Xóa người dùng thành công!");
            location.reload();
        } else {
            alert(response.message || "Có lỗi xảy ra khi xóa người dùng.");
        }
    }, "json").fail(function () {
        alert("Không thể kết nối tới máy chủ.");
    });
});

// Tải ID vào form xóa
function loadDeleteForm(userId) {
    if (userId) {
        $("#deleteUserId").val(userId);
        $("#deleteUserModal").modal("show");
    } else {
        alert("Không tìm thấy ID người dùng.");
    }
}

// Xem trước avatar khi chọn file mới
$("#editAvatar").change(function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $("#currentAvatarPreview").attr("src", e.target.result);
        };
        reader.readAsDataURL(file);
    }
});
//Modal tim kiem
$(document).ready(function () {
    $("#searchUser, #filterRole").on("input change", function () {
        const search = $("#searchUser").val().toLowerCase();
        const roleFilter = $("#filterRole").val();

        $("#userTable tr").each(function () {
            const username = $(this).find("td:nth-child(2)").text().toLowerCase();
            const role = $(this).find("td:nth-child(4)").text();

            if (username.includes(search) && (roleFilter === "" || role === roleFilter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
